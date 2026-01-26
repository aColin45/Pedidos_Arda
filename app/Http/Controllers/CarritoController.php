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

        $clienteId = session('current_client_id');
        if (!$clienteId) {
            return $precioBase; 
        }

        $cliente = Cliente::find($clienteId);
        if (!$cliente || empty($cliente->direccion)) {
            return $precioBase;
        }

        // IDs: 1, 2, 3, 4, 5, 6 (Mangueras de Succión)
        $productosConAumentoIds = [1, 2, 3, 4, 5, 6]; 

        if (!in_array($producto->id, $productosConAumentoIds)) {
            return $precioBase;
        }

        // REGLAS: Dirección (Mayúsculas) => Multiplicador
        $reglasAumento = [
            'MICHOACAN' => 1.10, // +10%
            'GUERRERO'  => 1.14, // +14%
            'MONTERREY' => 1.14, // +14%
        ];

        $estadoCliente = strtoupper(trim($cliente->direccion));

        if (array_key_exists($estadoCliente, $reglasAumento)) {
            $multiplicador = $reglasAumento[$estadoCliente];
            return $precioBase * $multiplicador;
        }

        return $precioBase;
    }

    // =========================================================================
    // AGREGAR
    // =========================================================================
    public function agregar(Request $request){
        $producto = Producto::findOrFail($request->producto_id);
        $inner = $producto->inner ?: 1;
        $cantidad = $request->cantidad;
        
        if ($cantidad < $inner) return redirect()->back()->withErrors(['cantidad' => 'La cantidad mínima es ' . $inner . '.']);
        if ($cantidad % $inner !== 0) return redirect()->back()->withErrors(['cantidad' => 'La cantidad debe ser un múltiplo de ' . $inner . '.']);
        
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

    // ... (Sumar, Restar, Actualizar, Eliminar, Vaciar IGUALES) ...
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
    // MOSTRAR (MODIFICADA PARA DETECTAR CAMBIO DE CLIENTE)
    // =========================================================================
    public function mostrar(Request $request){ // <-- AHORA RECIBE REQUEST
        
        // 1. SI VIENE UN CLIENTE EN LA URL, ACTUALIZAMOS LA SESIÓN PRIMERO
        if ($request->has('cliente_id')) {
            session(['current_client_id' => $request->cliente_id]);
        }

        $carrito = session('carrito', []);
        
        // 2. RECALCULAR PRECIOS DEL CARRITO SEGÚN EL CLIENTE ACTUAL
        if (!empty($carrito)) {
            $huboCambios = false;
            foreach ($carrito as $id => $item) {
                $productoDB = Producto::find($id);
                if ($productoDB) {
                    $nuevoPrecio = $this->calcularPrecioBaseRegional($productoDB);
                    
                    // Si el precio cambio (por región), actualizamos el carrito
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
            $subtotalLineaBruto = $precio * $cantidad;
            $subtotalBruto += $subtotalLineaBruto;
            $montoDescuentoLinea = $subtotalLineaBruto * (floatval($descuentoCliente) / 100);
            $subtotalLineaNeto = $subtotalLineaBruto - $montoDescuentoLinea;
            if ($aplicaIva) $subtotalNetoGravable += $subtotalLineaNeto;
            else $subtotalNetoExento += $subtotalLineaNeto;
        }

        $montoDescuento = $subtotalBruto * (floatval($descuentoCliente) / 100);
        $montoIVA = $subtotalNetoGravable * self::IVA_RATE;
        $totalFinal = $subtotalNetoGravable + $subtotalNetoExento + $montoIVA;

        return view('web.pedido', compact('carrito', 'subtotalBruto', 'descuentoCliente', 'montoDescuento', 'subtotalNetoGravable', 'subtotalNetoExento', 'montoIVA', 'totalFinal', 'clientesParaSelector'));
    }

    // =========================================================================
    // GENERAR PDF DE COTIZACIÓN (SIN CAMBIOS)
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
        
        if ($cliente->codigo === 'GENERAL') {
            $manual = $request->input('descuento_manual');
            if ($manual !== null && $manual !== '' && is_numeric($manual)) {
                $descuentoAplicar = floatval($manual);
            } else {
                $descuentoAplicar = 40.0; 
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

            $descuentoLinea = $lineaBruto * ($descuentoAplicar / 100);
            $lineaNeto = $lineaBruto - $descuentoLinea;

            if ($aplicaIva) {
                $subtotalNetoGravable += $lineaNeto;
            } else {
                $subtotalNetoExento += $lineaNeto;
            }
        }

        $montoDescuentoTotal = $subtotalBruto * ($descuentoAplicar / 100);
        $montoIva = $subtotalNetoGravable * self::IVA_RATE;
        $totalFinal = $subtotalNetoGravable + $subtotalNetoExento + $montoIva;

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