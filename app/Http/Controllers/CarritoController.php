<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    // IVA fijo al 16%
    const IVA_RATE = 0.16;

    /**
     * Agrega un producto al carrito o actualiza su cantidad.
     */
    public function agregar(Request $request){
        $producto = Producto::findOrFail($request->producto_id);
        $inner = $producto->inner ?: 1;
        $cantidad = $request->cantidad;

        // Validaciones de cantidad (mínima y múltiplo)
        if ($cantidad < $inner) {
            return redirect()->back()->withErrors(['cantidad' => 'La cantidad mínima es ' . $inner . ' unidades.']);
        }
        if ($cantidad % $inner !== 0) {
            return redirect()->back()->withErrors(['cantidad' => 'La cantidad debe ser un múltiplo de ' . $inner . '.']);
        }

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$producto->id])) {
            // Si el producto ya está, suma la cantidad
            $nueva_cantidad = $carrito[$producto->id]['cantidad'] + $cantidad;
            // Doble chequeo de múltiplo (por si acaso)
            if ($nueva_cantidad % $inner !== 0) {
                 return redirect()->back()->with('error', 'Error al sumar las cantidades. La suma total debe ser un múltiplo de ' . $inner . '.');
            }
            $carrito[$producto->id]['cantidad'] = $nueva_cantidad;
        } else {
            // Si es un producto nuevo, lo agrega al carrito
            $carrito[$producto->id] = [
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'aplica_iva' => $producto->aplica_iva, // Guardar si aplica IVA <-- Dato clave guardado en sesión
                'imagen' => $producto->imagen,
                'cantidad' => $cantidad,
                'inner' => $inner,
            ];
        }

        session()->put('carrito', $carrito);
        return redirect()->back()->with('mensaje', 'Producto agregado al carrito');
    }

    /**
     * Muestra la vista del carrito con los cálculos correctos de IVA y la lista de clientes adecuada.
     */
    public function mostrar(){
        $carrito = session('carrito', []);
        $clienteId = session('current_client_id'); // Usamos el ID de la sesión si existe
        $descuentoCliente = 0;
        $cliente = null;
        $agente = Auth::user(); // Obtener usuario logueado

        // Obtener el cliente "General" una sola vez
        $clienteGeneral = Cliente::where('codigo', 'GENERAL')->first(); // Búscalo por su código único

        $clientesParaSelector = collect(); // Empezar con una colección vacía

        // Determinar qué clientes mostrar en el selector
        if ($agente) { // Asegurarse de que hay un usuario logueado
            if ($agente->hasRole('admin')) {
                // ADMIN: Muestra TODOS los clientes ACTIVOS excepto el general (para evitar duplicados)
                $clientesParaSelector = Cliente::where('codigo', '!=', 'GENERAL')
                                              ->where('activo', true) // Solo activos
                                              ->orderBy('nombre')
                                              ->get();
            } else {
                // AGENTE: Muestra SUS clientes asignados que estén ACTIVOS
                $clientesParaSelector = $agente->clientes()
                                              ->where('activo', true) // Solo activos
                                              ->orderBy('nombre')
                                              ->get();
            }

            // Añadir el Cliente General AL PRINCIPIO de la lista si existe y está activo
            if ($clienteGeneral && $clienteGeneral->activo) {
                $clientesParaSelector->prepend($clienteGeneral);
            }

            // Obtener descuento del cliente seleccionado en sesión (si existe)
            if ($clienteId) {
                // Buscar primero en la colección que ya tenemos (incluye al general)
                $cliente = $clientesParaSelector->firstWhere('id', $clienteId);
                if ($cliente) {
                     $descuentoCliente = $cliente->descuento ?? 0;
                } else {
                     // Si no estaba en la lista (ej. inactivo), resetea la sesión
                     session()->forget(['current_client_id', 'current_client_name']);
                     $clienteId = null; // Anula ID para que no intente usarlo después
                }
            }
        } // Fin if ($agente)


        // --- Lógica de Cálculo (con IVA por producto) ---
        $subtotalBruto = 0;
        $subtotalNetoGravable = 0; // Subtotal neto de productos CON IVA
        $subtotalNetoExento = 0;   // Subtotal neto de productos SIN IVA

        foreach ($carrito as $item) {
            $precio = $item['precio'] ?? 0;
            $cantidad = $item['cantidad'] ?? 0;
            $aplicaIva = $item['aplica_iva'] ?? true; // Asumir true si falta

            $subtotalLineaBruto = $precio * $cantidad;
            $subtotalBruto += $subtotalLineaBruto;

            $montoDescuentoLinea = $subtotalLineaBruto * (floatval($descuentoCliente) / 100);
            $subtotalLineaNeto = $subtotalLineaBruto - $montoDescuentoLinea;

            if ($aplicaIva) {
                $subtotalNetoGravable += $subtotalLineaNeto;
            } else {
                $subtotalNetoExento += $subtotalLineaNeto;
            }
        }

        $montoDescuento = $subtotalBruto * (floatval($descuentoCliente) / 100);
        $montoIVA = $subtotalNetoGravable * self::IVA_RATE;
        $totalFinal = $subtotalNetoGravable + $subtotalNetoExento + $montoIVA;
        // --- FIN LÓGICA DE CÁLCULO ---

        // Pasamos la colección unificada de clientes a la vista
        return view('web.pedido', compact(
            'carrito',
            'subtotalBruto',
            'descuentoCliente',
            'montoDescuento',
            'subtotalNetoGravable',
            'subtotalNetoExento',
            'montoIVA',
            'totalFinal',
            'clientesParaSelector' // <-- Nombre de variable actualizado
        ));
    }

    /**
     * Suma la cantidad mínima (inner) a un producto en el carrito.
     */
    public function sumar(Request $request){
        $productoId = $request->producto_id;
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$productoId])) {
            $inner = $carrito[$productoId]['inner'] ?? 1;
            $carrito[$productoId]['cantidad'] += $inner;
            session()->put('carrito', $carrito);
        }
        return redirect()->back()->with('mensaje', 'Cantidad actualizada en el carrito');
    }

    /**
     * Resta la cantidad mínima (inner) a un producto en el carrito.
     * Si llega a la cantidad mínima, lo elimina.
     */
    public function restar(Request $request){
        $productoId = $request->producto_id;
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$productoId])) {
            $inner = $carrito[$productoId]['inner'] ?? 1;
            $cantidad_actual = $carrito[$productoId]['cantidad'];

            if ($cantidad_actual > $inner) {
                $carrito[$productoId]['cantidad'] -= $inner;
                session()->put('carrito', $carrito);
                return redirect()->back()->with('mensaje', 'Cantidad actualizada en el carrito');
            } else {
                // Si la cantidad es igual o menor al inner, lo eliminamos
                unset($carrito[$productoId]);
                session()->put('carrito', $carrito);
                return redirect()->back()->with('mensaje', 'Producto eliminado (se alcanzó la cantidad mínima de ' . $inner . ')');
            }
        }
        return redirect()->back();
    }

    /**
     * NUEVA FUNCIÓN: Actualiza la cantidad manualmente validando el Inner.
     * Esta función se llama desde el JavaScript de la vista.
     */
    public function actualizar($producto_id, $cantidad)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$producto_id])) {
            // Obtener el inner guardado en la sesión
            $inner = $carrito[$producto_id]['inner'] ?? 1;

            // Validación de seguridad (Backend)
            if ($cantidad < 1) {
                 return redirect()->back()->with('error', "La cantidad debe ser mayor a 0.");
            }
            
            // Validación de Múltiplo
            if ($cantidad % $inner != 0) {
                return redirect()->back()->with('error', "La cantidad debe ser múltiplo de $inner (Ej: $inner, " . ($inner*2) . "...).");
            }

            // Si pasa las validaciones, actualizamos
            $carrito[$producto_id]['cantidad'] = $cantidad;
            session()->put('carrito', $carrito);
            
            return redirect()->back()->with('mensaje', 'Cantidad actualizada manualmente.');
        }

        return redirect()->back()->with('error', 'Producto no encontrado en el carrito.');
    }


    /**
     * Elimina un producto completamente del carrito.
     */
    public function eliminar($id){
        $carrito = session()->get('carrito');
        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }
        return redirect()->back()->with('mensaje', 'Producto eliminado');
    }

    /**
     * Vacía completamente el carrito y limpia la sesión del cliente actual.
     */
    public function vaciar(){
        session()->forget('carrito');
        session()->forget(['current_client_id', 'current_client_name']);
        return redirect()->back()->with('mensaje', 'Carrito vaciado');
    }
}