<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf; 

class CarritoController extends Controller
{
    const IVA_RATE = 0.16;

    // =========================================================================
    //  FUNCIÓN PRIVADA PARA CALCULAR AUMENTOS REGIONALES
    // =========================================================================
    private function calcularPrecioBaseRegional(Producto $producto)
    {
        $precioBase = $producto->precio;

        // === NUEVO: VERIFICAR SI EL AGENTE TIENE UN PRECIO PIVOTE ESPECIAL ===
        if (auth()->check()) {
            $prodEspecial = auth()->user()->productosEspeciales()->where('producto_id', $producto->id)->first();
            if ($prodEspecial && $prodEspecial->pivot->precio_especial !== null) {
                // Sobrescribimos el precio base original con su precio único
                $precioBase = $prodEspecial->pivot->precio_especial;
            }
        }
        // =====================================================================

        $clienteId = session('current_client_id');
        if (!$clienteId) {
            return $precioBase; 
        }

        $cliente = Cliente::find($clienteId);
        
        // CAMBIO: Ahora verificamos que el cliente exista y tenga un ESTADO asignado
        if (!$cliente || empty($cliente->estado)) {
            return $precioBase;
        }

        // IDs: 1, 2, 3, 4, 5, 6 (Mangueras de Succión)
        $productosConAumentoIds = [1, 2, 3, 4, 5, 6]; 

        if (!in_array($producto->id, $productosConAumentoIds)) {
            return $precioBase;
        }

        // REGLAS: Estado (Mayúsculas) => Multiplicador
        $reglasAumento = [
            'MICHOACAN' => 1.10, // +10%
            'GUERRERO'  => 1.14, // +14%
            'NUEVO LEON' => 1.14, // +14%
        ];

        // CAMBIO: Ahora leemos el campo 'estado' en lugar de 'direccion'
        $estadoCliente = strtoupper(trim($cliente->estado));

        if (array_key_exists($estadoCliente, $reglasAumento)) {
            $multiplicador = $reglasAumento[$estadoCliente];
            return $precioBase * $multiplicador;
        }

        return $precioBase;
    }

    // =========================================================================
    // AGREGAR (MODIFICADO PARA PERMITIR LIBERTAD AL ADMIN)
    // =========================================================================
    public function agregar(Request $request){
        $producto = Producto::findOrFail($request->producto_id);
        
        // === NUEVO: VERIFICAR SI HAY UN INNER PIVOTE ESPECIAL ===
        $inner = $producto->inner ?: 1;
        if (auth()->check()) {
            $prodEspecial = auth()->user()->productosEspeciales()->where('producto_id', $producto->id)->first();
            if ($prodEspecial && $prodEspecial->pivot->inner_especial !== null) {
                $inner = $prodEspecial->pivot->inner_especial;
            }
        }
        // =========================================================

        $cantidad = $request->cantidad;
        
        // --- INICIO CAMBIO: Validar Inner SOLO si NO es Admin ---
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            if ($cantidad < $inner) {
                return redirect()->back()->withErrors(['cantidad' => 'La cantidad mínima es ' . $inner . '.']);
            }
            if ($cantidad % $inner !== 0) {
                return redirect()->back()->withErrors(['cantidad' => 'La cantidad debe ser un múltiplo de ' . $inner . '.']);
            }
        }
        // --- FIN CAMBIO ---
        
        $precioAUsar = $this->calcularPrecioBaseRegional($producto);

        $carrito = session()->get('carrito', []);
        
        if (isset($carrito[$producto->id])) {
            $carrito[$producto->id]['cantidad'] += $cantidad;
        } else {
            $carrito[$producto->id] = [
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'precio' => $precioAUsar, 
                'aplica_iva' => $producto->aplica_iva,
                'imagen' => $producto->imagen,
                'cantidad' => $cantidad,
                'inner' => $inner,
            ];
        }
        session()->put('carrito', $carrito);
        return redirect()->back()->with('mensaje', 'Producto agregado');
    }

    public function sumar(Request $request){
        $carrito = session()->get('carrito', []);
        if (isset($carrito[$request->producto_id])) {
            $carrito[$request->producto_id]['cantidad'] += $carrito[$request->producto_id]['inner'] ?? 1;
            session()->put('carrito', $carrito);
        }
        return redirect()->back();
    }
    public function restar(Request $request){
        $id = $request->producto_id;
        $carrito = session()->get('carrito', []);
        if (isset($carrito[$id])) {
            if ($carrito[$id]['cantidad'] > ($carrito[$id]['inner'] ?? 1)) {
                $carrito[$id]['cantidad'] -= $carrito[$id]['inner'] ?? 1;
            } else {
                unset($carrito[$id]);
            }
            session()->put('carrito', $carrito);
        }
        return redirect()->back();
    }
    public function actualizar($id, $cant){
        $carrito = session()->get('carrito', []);
        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] = $cant;
            session()->put('carrito', $carrito);
        }
        return redirect()->back();
    }
    public function eliminar($id){
        $carrito = session()->get('carrito');
        unset($carrito[$id]);
        session()->put('carrito', $carrito);
        return redirect()->back();
    }
    public function vaciar(){
        session()->forget('carrito');
        session()->forget(['current_client_id', 'current_client_name']);
        return redirect()->back();
    }

    // =========================================================================
    // MOSTRAR (MODIFICADA PARA DETECTAR CAMBIO DE CLIENTE Y REDONDEO EXACTO)
    // =========================================================================
    public function mostrar(Request $request){
        
        if ($request->has('cliente_id')) {
            session(['current_client_id' => $request->cliente_id]);
        }

        $carrito = session('carrito', []);
        
        if (!empty($carrito)) {
            $huboCambios = false;
            foreach ($carrito as $id => $item) {
                $productoDB = Producto::find($id);
                if ($productoDB) {
                    $nuevoPrecio = $this->calcularPrecioBaseRegional($productoDB);
                    
                    if ($carrito[$id]['precio'] != $nuevoPrecio) {
                        $carrito[$id]['precio'] = $nuevoPrecio;
                        $huboCambios = true;
                    }
                }
            }
            if ($huboCambios) {
                session()->put('carrito', $carrito);
            }
        }

        $clienteId = session('current_client_id');
        $descuentoCliente = 0;
        $agente = Auth::user();
        $clienteGeneral = Cliente::where('codigo', 'GENERAL')->first();
        $clientesParaSelector = collect();

        if ($agente) {
            if ($agente->hasRole('admin')) {
                $clientesParaSelector = Cliente::where('codigo', '!=', 'GENERAL')->where('activo', true)->orderBy('nombre')->get();
            } else {
                $clientesParaSelector = $agente->clientes()->where('activo', true)->orderBy('nombre')->get();
            }
            if ($clienteGeneral && $clienteGeneral->activo) {
                $clientesParaSelector->prepend($clienteGeneral);
            }
            if ($clienteId) {
                $cliente = $clientesParaSelector->firstWhere('id', $clienteId);
                if ($cliente) $descuentoCliente = $cliente->descuento ?? 0;
            }
        }

        $subtotalBruto = 0;
        $subtotalNetoGravable = 0;
        $subtotalNetoExento = 0;

        foreach ($carrito as $item) {
            $precio = $item['precio'] ?? 0;
            $cantidad = $item['cantidad'] ?? 0;
            $aplicaIva = $item['aplica_iva'] ?? true;

            // 1. Redondeamos el descuento por UNA sola pieza (Ej: 14.09 * 40% = 5.64)
            $descuentoUnitario = round($precio * (floatval($descuentoCliente) / 100), 2);
            
            // 2. Sacamos el precio neto unitario exacto (Ej: 14.09 - 5.64 = 8.45 cerrado)
            $precioNetoUnitario = $precio - $descuentoUnitario; 

            // 3. Multiplicamos por el volumen (Ej: 8.45 * 3000 = 25,350.00 exactos)
            $subtotalLineaNeto = $precioNetoUnitario * $cantidad;
            
            // Guardamos el bruto para el reporte visual
            $subtotalLineaBruto = $precio * $cantidad;
            $subtotalBruto += $subtotalLineaBruto;

            if ($aplicaIva) {
                $subtotalNetoGravable += $subtotalLineaNeto;
            } else {
                $subtotalNetoExento += $subtotalLineaNeto;
            }
        }

        // REDONDEO EN LOS TOTALES GLOBALES PARA EVITAR DESFASES DE CENTAVOS
        $montoDescuento = round($subtotalBruto * (floatval($descuentoCliente) / 100), 2);
        $montoIVA = round($subtotalNetoGravable * self::IVA_RATE, 2);
        $totalFinal = round($subtotalNetoGravable + $subtotalNetoExento + $montoIVA, 2);

        return view('web.pedido', compact('carrito', 'subtotalBruto', 'descuentoCliente', 'montoDescuento', 'subtotalNetoGravable', 'subtotalNetoExento', 'montoIVA', 'totalFinal', 'clientesParaSelector'));
    }

    // =========================================================================
    // GENERAR PDF DE COTIZACIÓN (CON REDONDEO EXACTO)
    // =========================================================================
    public function generarPdfCotizacion(Request $request)
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        $clienteId = $request->input('cliente_id');
        if (!$clienteId) {
            $clienteId = session('current_client_id');
        }
        
        if (!$clienteId) {
             return redirect()->back()->with('error', 'Selecciona un cliente primero.');
        }

        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado.');
        }

        $logoBase64 = null;
        try {
            $pathLogo = public_path('assets/img/LOGO.png');
            if (!file_exists($pathLogo)) {
                $pathLogo = base_path('../public_html/assets/img/LOGO.png');
            }
            if (!file_exists($pathLogo)) {
                $pathLogo = 'assets/img/LOGO.png';
            }

            if (file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $data = file_get_contents($pathLogo);
                if ($data !== false) {
                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        } catch (\Exception $e) {}

        $descuentoAplicar = 0.0;
        $esMostrador = str_contains(strtoupper($cliente->nombre), 'VENTAS DE MOSTRADOR') || str_contains(strtoupper($cliente->nombre), 'VENTAS MOSTRADOR');
        
        if ($cliente->codigo === 'GENERAL' || $esMostrador) {
            $manual = $request->input('descuento_manual');
            if ($manual !== null && $manual !== '' && is_numeric($manual)) {
                $descuentoAplicar = floatval($manual);
            } else {
                // Si falla algo, toma el 40% para General, o el de la base de datos para Mostrador
                $descuentoAplicar = ($cliente->codigo === 'GENERAL') ? 40.0 : floatval($cliente->descuento ?? 0); 
            }
        } else {
            $descuentoAplicar = floatval($cliente->descuento ?? 0);
        }

        $subtotalBruto = 0;
        $subtotalNetoGravable = 0;
        $subtotalNetoExento = 0;

        foreach ($carrito as $item) {
            $precio = floatval($item['precio']);
            $cantidad = intval($item['cantidad']);
            $aplicaIva = $item['aplica_iva'] ?? true;

            $lineaBruto = $precio * $cantidad;
            $subtotalBruto += $lineaBruto;

            // APLICACIÓN DEL REDONDEO EXACTO A 2 DECIMALES EN LA LÍNEA DEL PDF
            $descuentoLinea = round($lineaBruto * ($descuentoAplicar / 100), 2);
            $lineaNeto = $lineaBruto - $descuentoLinea;

            if ($aplicaIva) {
                $subtotalNetoGravable += $lineaNeto;
            } else {
                $subtotalNetoExento += $lineaNeto;
            }
        }

        // REDONDEO EN LOS TOTALES GLOBALES DEL PDF
        $montoDescuentoTotal = round($subtotalBruto * ($descuentoAplicar / 100), 2);
        $montoIva = round($subtotalNetoGravable * self::IVA_RATE, 2);
        $totalFinal = round($subtotalNetoGravable + $subtotalNetoExento + $montoIva, 2);

        $comentarios = $request->input('comentarios_pdf'); 

        $data = [
            'carrito' => $carrito,
            'cliente' => $cliente,
            'fecha' => now()->setTimezone('America/Mexico_City'), 
            'descuento_porcentaje' => $descuentoAplicar,
            'subtotal_bruto' => $subtotalBruto,
            'monto_descuento' => $montoDescuentoTotal,
            'subtotal_gravable' => $subtotalNetoGravable,
            'subtotal_exento' => $subtotalNetoExento,
            'monto_iva' => $montoIva,
            'total_final' => $totalFinal,
            'usuario' => Auth::user(),
            'logoBase64' => $logoBase64,
            'comentarios' => $comentarios
        ];

        $pdf = Pdf::loadView('pdf.cotizacion', $data);
        $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'isRemoteEnabled' => true]);
        
        $nombreLimpio = Str::slug($cliente->nombre ?? 'Cliente', '-');
        return $pdf->stream('Cotizacion-' . $nombreLimpio . '.pdf'); 
    }
}