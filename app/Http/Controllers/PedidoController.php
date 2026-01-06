<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PedidoController extends Controller
{
    const IVA_RATE = 0.16;

    public function index(Request $request){
        $user = auth()->user();
        $texto = $request->input('texto');
        $month = $request->input('month');
        $year = $request->input('year');

        $query = Pedido::with('agente', 'cliente', 'detalles.producto')
                    ->where('estado', '!=', 'cotizacion')
                    ->orderBy('id', 'desc');

        if ($user->hasRole('admin')) {
            // Admin ve todo
        } elseif ($user->hasRole('agente-ventas')) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        if (!empty($texto)) {
            $query->where(function ($q) use ($texto) {
                $q->whereHas('agente', function ($subQ) use ($texto) {
                    $subQ->where('name', 'like', "%{$texto}%");
                })->orWhereHas('cliente', function ($subQ) use ($texto) {
                    $subQ->where('nombre', 'like', "%{$texto}%")
                          ->orWhere('codigo', 'like', "%{$texto}%");
                });
            });
        }

        if (!empty($month)) {
            $query->whereMonth('created_at', $month);
        }
        if (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        $registros = $query->paginate(10);
        return view('pedido.index', compact('registros', 'texto', 'month', 'year'));
    }

    private function calculateFinalAmounts(array $carrito, float $descuentoCliente)
    {
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

            if ($aplicaIva) {
                $subtotalNetoGravable += $subtotalLineaNeto;
            } else {
                $subtotalNetoExento += $subtotalLineaNeto;
            }
        }

        $montoDescuentoTotal = $subtotalBruto * (floatval($descuentoCliente) / 100);
        $montoIVA = $subtotalNetoGravable * self::IVA_RATE;
        $totalFinal = $subtotalNetoGravable + $subtotalNetoExento + $montoIVA;

        return [
            'subtotal' => $subtotalBruto,
            'monto_descuento' => $montoDescuentoTotal,
            'monto_iva' => $montoIVA,
            'total_final' => $totalFinal,
        ];
    }

    public function realizar(Request $request){
        $carrito = session()->get('carrito', []);
        if (empty($carrito)) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'comentarios' => 'nullable|string|max:1000',
            'flete_pagado' => 'nullable|boolean',
        ], [
            'cliente_id.required' => 'Debe seleccionar un cliente para el pedido.'
        ]);

        $clienteId = $validatedData['cliente_id'];
        $cliente = Cliente::findOrFail($clienteId);
        $user = Auth::user();

        if (!$user->hasRole('admin') && $cliente->codigo !== 'GENERAL' && $cliente->user_id != $user->id) {
             return redirect()->back()->with('error', 'El cliente seleccionado no le pertenece.');
        }

        $descuentoCliente = $cliente->descuento ?? 0.00;
        $calculos = $this->calculateFinalAmounts($carrito, $descuentoCliente);

        DB::beginTransaction();
        try {
            $fletePagado = $request->has('flete_pagado');
            $estadoInicial = ($cliente->codigo === 'GENERAL') ? 'cotizacion' : 'pendiente';

            $pedido = Pedido::create([
                'user_id' => $user->id,
                'cliente_id' => $clienteId,
                'total' => $calculos['total_final'],
                'subtotal' => $calculos['subtotal'],
                'descuento_aplicado' => $calculos['monto_descuento'],
                'iva' => $calculos['monto_iva'],
                'estado' => $estadoInicial,
                'comentarios' => $validatedData['comentarios'],
                'flete_pagado' => $fletePagado
            ]);

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
                     'aplica_iva' => $item['aplica_iva'] ?? true,
                     'subtotal' => $subtotalLinea,
                 ]);
             }

            session()->forget(['carrito', 'current_client_id', 'current_client_name']);
            DB::commit();

            $mensaje = ($estadoInicial === 'cotizacion')
                        ? 'Cotización generada (registrada con estado Cotización).'
                        : 'Pedido realizado correctamente para el cliente: ' . $cliente->nombre . '.';

            return redirect()->route('perfil.pedidos')->with('mensaje', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
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

        // 1. Verificar si es Cotización
        if ($pedido->estado === 'cotizacion') {
             abort(403, 'No se puede cambiar el estado de una cotización.');
        }

        // 2. Estados Permitidos (INCLUYENDO LOS NUEVOS)
        $estadosPermitidos = [
            'parcialmente_surtido', 
            'enviado_completo', 
            'anulado', 
            'cancelado', 
            'entregado'
        ];
        
        if (!in_array($estadoNuevo, $estadosPermitidos)) {
            abort(403, 'Estado no válido: ' . $estadoNuevo);
        }

        // ========================================================
        // 3. LÓGICA DE TRANSICIONES (Reglas de Negocio)
        // ========================================================

        // A. CANCELAR (Solo si está pendiente)
        if ($estadoNuevo === 'cancelado') {
            if ($pedido->estado !== 'pendiente') {
                 abort(403, 'Solo se pueden cancelar pedidos que estén Pendientes.');
            }
            if (!$user->can('pedido-cancel')) {
                 abort(403, 'No tiene permiso para cancelar pedidos');
            }
        }

        // B. PARCIALMENTE SURTIDO (Nuevo estado intermedio)
        elseif ($estadoNuevo === 'parcialmente_surtido') {
            if (!$user->can('pedido-anulate')) { 
                 abort(403, 'No tiene permiso para gestionar almacén.');
            }
            // Solo puede venir de PENDIENTE
            if ($pedido->estado !== 'pendiente') {
                abort(403, 'Para marcar como parcialmente surtido, el pedido debe estar Pendiente.');
            }
        }

        // C. ENVIADO COMPLETO (Sustituye a 'enviado')
        elseif ($estadoNuevo === 'enviado_completo') {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para realizar esta acción.');
            }
            // Puede venir de PENDIENTE o de PARCIALMENTE SURTIDO
            // (Nota: agregamos 'enviado' antiguo por compatibilidad si es necesario)
            if (!in_array($pedido->estado, ['pendiente', 'parcialmente_surtido'])) {
                 abort(403, 'Solo se pueden enviar pedidos pendientes o parcialmente surtidos.');
            }
        }

        // D. ANULADO
        elseif ($estadoNuevo === 'anulado') {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para anular.');
            }
            // Aceptamos 'enviado' (legacy) o 'enviado_completo'
            if (!in_array($pedido->estado, ['enviado', 'enviado_completo'])) {
                 abort(403, 'Solo se pueden anular pedidos que ya han sido Enviados.');
            }
        }

        // E. ENTREGADO
        elseif ($estadoNuevo === 'entregado') {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para finalizar pedidos.');
            }
            // Solo se entregan los que ya se enviaron
            if (!in_array($pedido->estado, ['enviado', 'enviado_completo'])) {
                 abort(403, 'Solo se pueden marcar como entregados los pedidos enviados.');
            }
        }

        // Guardar cambios
        $pedido->estado = $estadoNuevo;
        $pedido->save();

        $nombreEstado = ucwords(str_replace('_', ' ', $estadoNuevo));
        return redirect()->back()->with('mensaje', 'El estado del pedido fue actualizado a: ' . $nombreEstado);
    }

    // =========================================================================
    // NUEVA FUNCIÓN: ACTUALIZAR GUÍAS DE RASTREO (SIN TOCAR DB)
    // =========================================================================
    public function updateGuia(Request $request, $id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            
            // Validamos qué estamos guardando y limpiamos espacios
            $tipo = $request->input('tipo'); // 'guia_parcial' o 'guia_completa'
            $valor = trim($request->input('valor')); 

            // Obtenemos el comentario actual (o vacío si es null)
            $comentarioActual = $pedido->comentarios ?? '';

            if ($tipo === 'guia_parcial') {
                // 1. Borramos cualquier guía parcial vieja usando Expresiones Regulares
                $comentarioActual = preg_replace('/\|GP:(.*?)\|/', '', $comentarioActual);
                
                // 2. Si el usuario escribió algo, lo agregamos con el formato especial
                if (!empty($valor)) {
                    $comentarioActual .= " |GP:$valor|";
                }
            } elseif ($tipo === 'guia_completa') {
                // 1. Borramos guía completa vieja
                $comentarioActual = preg_replace('/\|GC:(.*?)\|/', '', $comentarioActual);
                
                // 2. Agregamos la nueva
                if (!empty($valor)) {
                    $comentarioActual .= " |GC:$valor|";
                }
            }

            // Guardamos el resultado en la columna 'comentarios' real
            $pedido->comentarios = trim($comentarioActual);
            $pedido->save();

            return response()->json(['success' => true, 'message' => 'Guía actualizada correctamente.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // GENERAR PDF DE UN PEDIDO YA GUARDADO
    // =========================================================================
    public function generarPdfPedido($id)
    {
        $pedido = Pedido::with(['cliente', 'agente', 'detalles.producto'])->findOrFail($id);
        $user = Auth::user();

        // Seguridad: Verificar que el usuario pueda ver este pedido
        // (Si es admin ve todo, si es agente solo sus pedidos o los de sus clientes)
        if (!$user->hasRole('admin')) {
             // Si el pedido no es del agente Y el cliente tampoco es del agente...
             if ($pedido->user_id != $user->id && $pedido->cliente->user_id != $user->id) {
                 abort(403, 'No tiene permiso para ver este pedido.');
             }
        }

        // Procesar Logo (Misma lógica segura que usamos en Cotización)
        $logoBase64 = null;
        try {
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); 
            $pathLogo = $docRoot . '/assets/img/LOGO.png'; // Ruta servidor
            if (!file_exists($pathLogo)) $pathLogo = public_path('assets/img/LOGO.png'); // Ruta local
            
            if (file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $data = file_get_contents($pathLogo);
                if ($data !== false) {
                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        } catch (\Exception $e) {}

        $data = [
            'pedido' => $pedido,
            'logoBase64' => $logoBase64,
        ];

        // Usamos una vista nueva 'pdf.pedido' (la crearemos abajo)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.pedido', $data);
        $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'isRemoteEnabled' => true]);
        
        return $pdf->stream('Pedido-#' . $pedido->id . '.pdf');
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