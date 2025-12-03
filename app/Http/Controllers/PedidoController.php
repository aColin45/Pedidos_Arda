<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate; // Gate no se usa directamente aquí, pero lo dejamos por si acaso
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Importar Carbon para fechas

class PedidoController extends Controller
{
    // IVA fijo al 16%
    const IVA_RATE = 0.16;

    /**
     * Muestra la lista de pedidos, filtrada por rol, texto, mes y año.
     * Excluye las cotizaciones por defecto.
     */
    public function index(Request $request){
        $user = auth()->user();
        $texto = $request->input('texto');
        $month = $request->input('month');
        $year = $request->input('year');

        // Consulta base con relaciones cargadas
        $query = Pedido::with('agente', 'cliente', 'detalles.producto')
                    // EXCLUIR COTIZACIONES POR DEFECTO
                    ->where('estado', '!=', 'cotizacion')
                    ->orderBy('id', 'desc');

        // Filtrar por ROL
        if ($user->hasRole('admin')) {
            // Admin ve todo (excepto cotizaciones) - No necesita filtro adicional
        } elseif ($user->hasRole('agente-ventas')) {
            // Agente ve solo los pedidos que ÉL creó (user_id)
            $query->where('user_id', $user->id);
        } else {
            // Otro rol (ej. cliente) solo ve los suyos
            // OJO: Si tienes clientes como Users, necesitarías otra lógica aquí,
            // quizás basada en cliente_id si hay relación User<->Cliente
            $query->where('user_id', $user->id); // Asumiendo que el User es el creador
        }

        // Filtrar por TEXTO (Agente o Cliente)
        if (!empty($texto)) {
            $query->where(function ($q) use ($texto) {
                $q->whereHas('agente', function ($subQ) use ($texto) {
                    $subQ->where('name', 'like', "%{$texto}%");
                })->orWhereHas('cliente', function ($subQ) use ($texto) {
                    $subQ->where('nombre', 'like', "%{$texto}%")
                         ->orWhere('codigo', 'like', "%{$texto}%"); // Añadir búsqueda por código cliente
                });
            });
        }

        // Filtrar por FECHA (Mes y/o Año)
        if (!empty($month)) {
            $query->whereMonth('created_at', $month);
        }
        if (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Obtener resultados paginados
        $registros = $query->paginate(10);

        // Pasar datos a la vista
        return view('pedido.index', compact('registros', 'texto', 'month', 'year'));
    }

    /**
     * Calcula los montos finales aplicando descuento e IVA por producto.
     * Esta función ahora reside aquí para ser usada al guardar el pedido.
     */
    private function calculateFinalAmounts(array $carrito, float $descuentoCliente)
    {
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

        // Calcular Monto Descuento Total (Informativo, el descuento ya se aplicó por línea)
        $montoDescuentoTotal = $subtotalBruto * (floatval($descuentoCliente) / 100);

        // Calcular IVA ÚNICAMENTE sobre el subtotal neto gravable
        $montoIVA = $subtotalNetoGravable * self::IVA_RATE;

        // Calcular Total Final
        $totalFinal = $subtotalNetoGravable + $subtotalNetoExento + $montoIVA;

        return [
            'subtotal' => $subtotalBruto,          // Subtotal antes de descuento e IVA
            'monto_descuento' => $montoDescuentoTotal, // Descuento total aplicado
            'monto_iva' => $montoIVA,              // IVA total calculado
            'total_final' => $totalFinal,          // Total a pagar
            // Podríamos devolver también los netos si los necesitáramos guardar
            // 'subtotal_neto_gravable' => $subtotalNetoGravable,
            // 'subtotal_neto_exento' => $subtotalNetoExento,
        ];
    }

    /**
     * Guarda un nuevo pedido o cotización en la base de datos.
     */
    public function realizar(Request $request){
        $carrito = session()->get('carrito', []);
        if (empty($carrito)) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        // Validación de datos del formulario
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'comentarios' => 'nullable|string|max:1000',
            'flete_pagado' => 'nullable|boolean', // Acepta 0 o 1 si se envía
        ], [
            'cliente_id.required' => 'Debe seleccionar un cliente para el pedido.'
        ]);

        $clienteId = $validatedData['cliente_id'];
        $cliente = Cliente::findOrFail($clienteId);
        $user = Auth::user();

        // Validación de Pertenencia (Admin puede para cualquiera, Agente solo los suyos o General)
        if (!$user->hasRole('admin') && $cliente->codigo !== 'GENERAL' && $cliente->user_id != $user->id) {
             return redirect()->back()->with('error', 'El cliente seleccionado no le pertenece.');
        }

        // Obtener descuento y recalcular montos finales (con lógica de IVA por producto)
        $descuentoCliente = $cliente->descuento ?? 0.00;
        // ¡Usar la función interna para calcular!
        $calculos = $this->calculateFinalAmounts($carrito, $descuentoCliente);

        DB::beginTransaction();
        try {
            // Determinar si el flete está pagado (checkbox)
            $fletePagado = $request->has('flete_pagado');

            // Determinar el estado inicial (pedido o cotización)
            $estadoInicial = ($cliente->codigo === 'GENERAL') ? 'cotizacion' : 'pendiente';

            // Crear el registro del Pedido
            $pedido = Pedido::create([
                'user_id' => $user->id,
                'cliente_id' => $clienteId,
                'total' => $calculos['total_final'],
                'subtotal' => $calculos['subtotal'], // Guardamos subtotal bruto
                'descuento_aplicado' => $calculos['monto_descuento'], // Guardamos descuento total
                'iva' => $calculos['monto_iva'], // Guardamos IVA total
                'estado' => $estadoInicial,
                'comentarios' => $validatedData['comentarios'],
                'flete_pagado' => $fletePagado
            ]);

            // Crear los registros de PedidoDetalle
            foreach ($carrito as $productoId => $item) {
                 $precio = $item['precio'] ?? 0;
                 $cantidad = $item['cantidad'] ?? 0;
                 $subtotalLinea = $precio * $cantidad;
                 PedidoDetalle::create([
                     'pedido_id' => $pedido->id,
                     'producto_id' => $productoId,
                     'cantidad' => $cantidad,
                     'precio' => $precio,
                     'inner' => $item['inner'] ?? 1,
                     // Asegúrate de guardar aplica_iva aquí si tu tabla PedidoDetalle la tiene
                     'aplica_iva' => $item['aplica_iva'] ?? true,
                     'subtotal' => $subtotalLinea,
                 ]);
             }

            // Limpiar sesión y confirmar transacción
            session()->forget(['carrito', 'current_client_id', 'current_client_name']);
            DB::commit();

            // Mensaje de éxito
            $mensaje = ($estadoInicial === 'cotizacion')
                       ? 'Cotización generada (registrada con estado Cotización).'
                       : 'Pedido realizado correctamente para el cliente: ' . $cliente->nombre . '.';

            return redirect()->route('perfil.pedidos')->with('mensaje', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error al guardar pedido: '.$e->getMessage()); // Opcional: Loggear error
            // dd($e); // Para depurar errores inesperados
            return redirect()->back()->with('error', 'Hubo un error al procesar el pedido/cotización. Intente de nuevo.');
        }
    }

    /**
     * Cambia el estado de un pedido existente.
     */
    public function cambiarEstado(Request $request, $id){
        $pedido = Pedido::findOrFail($id);
        $estadoNuevo = $request->input('estado');
        $user = auth()->user();

        // 1. Verificar si es Cotización (no se puede cambiar)
        if ($pedido->estado === 'cotizacion') {
             abort(403, 'No se puede cambiar el estado de una cotización.');
        }

        // 2. Estados Permitidos (incluye 'entregado')
        $estadosPermitidos = ['enviado', 'anulado', 'cancelado', 'entregado'];
        if (!in_array($estadoNuevo, $estadosPermitidos)) {
            abort(403, 'Estado no válido');
        }

        // 3. Validar Permisos y Transiciones
        // Cancelar (Agente o Admin, solo desde Pendiente)
        if ($estadoNuevo === 'cancelado') {
            if ($pedido->estado !== 'pendiente') {
                 abort(403, 'Solo se pueden cancelar pedidos pendientes.');
            }
            if (!$user->can('pedido-cancel')) {
                 abort(403, 'No tiene permiso para cancelar pedidos');
            }
        }
        // Enviar, Anular o Entregar (SOLO ADMIN con permiso 'pedido-anulate')
        elseif (in_array($estadoNuevo, ['enviado', 'anulado', 'entregado'])) {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para realizar esta acción.');
            }
            // Validar transiciones específicas
            if ($estadoNuevo === 'enviado' && $pedido->estado !== 'pendiente') {
                 abort(403, 'Solo se pueden enviar pedidos pendientes.');
            }
            if ($estadoNuevo === 'anulado' && $pedido->estado !== 'enviado') {
                 abort(403, 'Solo se pueden anular pedidos enviados.');
            }
            if ($estadoNuevo === 'entregado' && $pedido->estado !== 'enviado') {
                 abort(403, 'Solo se pueden marcar como entregados los pedidos enviados.');
            }
        }

        // Si todo es válido, cambiar estado
        $pedido->estado = $estadoNuevo;
        $pedido->save();

        return redirect()->back()->with('mensaje', 'El estado del pedido fue actualizado a "' . ucfirst($estadoNuevo) . '"');
    }

     /**
     * Elimina permanentemente un pedido (si se tiene la función y la ruta).
     * Nota: Se mantiene aquí por si se reactiva, pero existen posibles riesgos.
     */
    // public function destroy(Pedido $pedido) // Usando Route Model Binding
    // {
    //     $this->authorize('pedido-delete'); // Asume permiso 'pedido-delete'
    //     $user = auth()->user();

    //     // Lógica de Negocio (Agente no borra enviados)
    //     if ($pedido->estado == 'enviado' && !$user->hasRole('admin')) {
    //          return redirect()->back()->with('error', 'No puedes eliminar un pedido que ya fue enviado.');
    //     }

    //     try {
    //         // Detalles se borran en cascada (onDelete('cascade'))
    //         $pedido->delete();
    //         return redirect()->route('perfil.pedidos')->with('mensaje', 'Pedido #' . $pedido->id . ' eliminado permanentemente.');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Error al eliminar el pedido.');
    //     }
    // }
}