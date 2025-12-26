<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use PDF; 
use Illuminate\Support\Str; // <--- IMPORTANTE: Necesario para limpiar el nombre del archivo

class CarritoController extends Controller
{
    const IVA_RATE = 0.16;

    // --- FUNCIONES DEL CARRITO (Agregar, Sumar, Restar, Actualizar, Eliminar, Vaciar) ---
    // Se mantienen idénticas a la lógica original.

    public function agregar(Request $request){
        $producto = Producto::findOrFail($request->producto_id);
        $inner = $producto->inner ?: 1;
        $cantidad = $request->cantidad;
        if ($cantidad < $inner) return redirect()->back()->withErrors(['cantidad' => 'La cantidad mínima es ' . $inner . '.']);
        if ($cantidad % $inner !== 0) return redirect()->back()->withErrors(['cantidad' => 'La cantidad debe ser un múltiplo de ' . $inner . '.']);
        
        $carrito = session()->get('carrito', []);
        if (isset($carrito[$producto->id])) {
            $carrito[$producto->id]['cantidad'] += $cantidad;
        } else {
            $carrito[$producto->id] = [
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
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

    public function mostrar(){
        $carrito = session('carrito', []);
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
    // GENERAR PDF DE COTIZACIÓN
    // =========================================================================
    public function generarPdfCotizacion(Request $request)
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        // 1. Obtener Cliente
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

        // 2. Procesar Logo a Base64
        $logoBase64 = null;
        try {
            $pathLogo = public_path('assets/img/logo.png');
            if (file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $data = file_get_contents($pathLogo);
                if ($data !== false) {
                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        } catch (\Exception $e) {}

        // 3. Lógica de Descuento
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

        // 4. Cálculos Matemáticos
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

        // --- CAPTURAR COMENTARIOS ---
        // Aquí tomamos lo que viene del formulario
        $comentarios = $request->input('comentarios_pdf'); 

        $data = [
            'carrito' => $carrito,
            'cliente' => $cliente,
            'fecha' => now(),
            'descuento_porcentaje' => $descuentoAplicar,
            'subtotal_bruto' => $subtotalBruto,
            'monto_descuento' => $montoDescuentoTotal,
            'subtotal_gravable' => $subtotalNetoGravable,
            'subtotal_exento' => $subtotalNetoExento,
            'monto_iva' => $montoIva,
            'total_final' => $totalFinal,
            'usuario' => Auth::user(),
            'logoBase64' => $logoBase64,
            'comentarios' => $comentarios // <-- Pasamos la variable a la vista
        ];

        // --- CORRECCIÓN AQUÍ: Usamos PDF:: (Alias Global en Mayúsculas) ---
        $pdf = PDF::loadView('pdf.cotizacion', $data);
        $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'isRemoteEnabled' => true]);
        
        $nombreLimpio = Str::slug($cliente->nombre ?? 'Cliente', '-');
        return $pdf->stream('Cotizacion-' . $nombreLimpio . '.pdf');
    }
}