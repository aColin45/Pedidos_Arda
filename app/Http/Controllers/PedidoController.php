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

    // =========================================================================
    // FUNCIÓN REALIZAR PEDIDO (CORREGIDA PARA NO TOCAR DB)
    // =========================================================================
    public function realizar(Request $request){
        $carrito = session()->get('carrito', []);
        if (empty($carrito)) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'comentarios' => 'nullable|string|max:1000',
            'flete_pagado' => 'nullable|boolean', // Solo validamos, no guardamos directo
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
            $estadoInicial = ($cliente->codigo === 'GENERAL') ? 'cotizacion' : 'pendiente';
            
            // LÓGICA SEGURA: Si flete pagado es true, lo metemos al comentario
            $comentariosFinales = $validatedData['comentarios'] ?? '';
            if ($request->has('flete_pagado') && $request->flete_pagado == '1') {
                $comentariosFinales .= " |FP:1|"; 
            }

            $pedido = Pedido::create([
                'user_id' => $user->id,
                'cliente_id' => $clienteId,
                'total' => $calculos['total_final'],
                'subtotal' => $calculos['subtotal'],
                'descuento_aplicado' => $calculos['monto_descuento'],
                'iva' => $calculos['monto_iva'],
                'estado' => $estadoInicial,
                'comentarios' => trim($comentariosFinales),
                // 'flete_pagado' => Eliminado para evitar error SQL
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
                     // 'aplica_iva' => Eliminado para evitar error SQL (se lee del producto)
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
            // Descomenta la siguiente línea si quieres ver el error exacto en pantalla para depurar
            // dd($e->getMessage()); 
            return redirect()->back()->with('error', 'Hubo un error al procesar el pedido/cotización. Intente de nuevo.');
        }
    }

    public function cambiarEstado(Request $request, $id){
        $pedido = Pedido::findOrFail($id);
        $estadoNuevo = $request->input('estado'); 
        $user = auth()->user();

        if ($pedido->estado === 'cotizacion') {
             abort(403, 'No se puede cambiar el estado de una cotización.');
        }

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

        // LÓGICA DE TRANSICIONES
        if ($estadoNuevo === 'cancelado') {
            if ($pedido->estado !== 'pendiente') {
                 abort(403, 'Solo se pueden cancelar pedidos que estén Pendientes.');
            }
            if (!$user->can('pedido-cancel')) {
                 abort(403, 'No tiene permiso para cancelar pedidos');
            }
        }
        elseif ($estadoNuevo === 'parcialmente_surtido') {
            if (!$user->can('pedido-anulate')) { 
                 abort(403, 'No tiene permiso para gestionar almacén.');
            }
            if ($pedido->estado !== 'pendiente') {
                abort(403, 'Para marcar como parcialmente surtido, el pedido debe estar Pendiente.');
            }
        }
        elseif ($estadoNuevo === 'enviado_completo') {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para realizar esta acción.');
            }
            if (!in_array($pedido->estado, ['pendiente', 'parcialmente_surtido'])) {
                 abort(403, 'Solo se pueden enviar pedidos pendientes o parcialmente surtidos.');
            }
        }
        elseif ($estadoNuevo === 'anulado') {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para anular.');
            }
            if (!in_array($pedido->estado, ['enviado', 'enviado_completo'])) {
                 abort(403, 'Solo se pueden anular pedidos que ya han sido Enviados.');
            }
        }
        elseif ($estadoNuevo === 'entregado') {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para finalizar pedidos.');
            }
            if (!in_array($pedido->estado, ['enviado', 'enviado_completo'])) {
                 abort(403, 'Solo se pueden marcar como entregados los pedidos enviados.');
            }
        }

        $pedido->estado = $estadoNuevo;
        $pedido->save();

        $nombreEstado = ucwords(str_replace('_', ' ', $estadoNuevo));
        return redirect()->back()->with('mensaje', 'El estado del pedido fue actualizado a: ' . $nombreEstado);
    }

    public function updateGuia(Request $request, $id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            $tipo = $request->input('tipo'); 
            $valor = trim($request->input('valor')); 

            $comentarioActual = $pedido->comentarios ?? '';

            if ($tipo === 'guia_parcial') {
                $comentarioActual = preg_replace('/\|GP:(.*?)\|/', '', $comentarioActual);
                if (!empty($valor)) {
                    $comentarioActual .= " |GP:$valor|";
                }
            } elseif ($tipo === 'guia_completa') {
                $comentarioActual = preg_replace('/\|GC:(.*?)\|/', '', $comentarioActual);
                if (!empty($valor)) {
                    $comentarioActual .= " |GC:$valor|";
                }
            }

            $pedido->comentarios = trim($comentarioActual);
            $pedido->save();

            return response()->json(['success' => true, 'message' => 'Guía actualizada correctamente.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // GENERAR PDF DE UN PEDIDO YA GUARDADO
    public function generarPdfPedido($id)
    {
        $pedido = Pedido::with(['cliente', 'agente', 'detalles.producto'])->findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
             if ($pedido->user_id != $user->id && $pedido->cliente->user_id != $user->id) {
                 abort(403, 'No tiene permiso para ver este pedido.');
             }
        }

        $logoBase64 = null;
        try {
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); 
            $pathLogo = $docRoot . '/assets/img/LOGO.png'; 
            if (!file_exists($pathLogo)) $pathLogo = public_path('assets/img/LOGO.png');
            
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