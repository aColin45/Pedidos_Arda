<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Cliente;
use App\Models\HistorialPedido;
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
        $estado = $request->input('estado'); // <-- NUEVO FILTRO

        $query = Pedido::with('agente', 'cliente', 'detalles.producto', 'historial.usuario')
                    ->where('is_cotizacion', 0) 
                    ->orderBy('id', 'desc');

        if ($user->hasRole('admin')) {
            // Admin ve todo
        } else {
            // Agentes ven solo lo suyo
            $query->where('user_id', $user->id);
        }

        // --- FILTROS DE BÚSQUEDA ---
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
        if (!empty($estado)) {
            $query->where('estado', $estado); // <-- APLICAR NUEVO FILTRO
        }

        // --- CALCULAR PEDIDOS PENDIENTES PARA LA TARJETA ---
        $pendientesQuery = Pedido::where('is_cotizacion', 0)->where('estado', 'pendiente');
        if (!$user->hasRole('admin')) {
            $pendientesQuery->where('user_id', $user->id);
        }
        $pendientesCount = $pendientesQuery->count();

        $registros = $query->paginate(10);
        
        // Retornar las variables a la vista
        return view('pedido.index', compact('registros', 'texto', 'month', 'year', 'estado', 'pendientesCount'));
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

        // REDONDEO EN LOS TOTALES GLOBALES
        $montoDescuentoTotal = round($subtotalBruto * (floatval($descuentoCliente) / 100), 2);
        $montoIVA = round($subtotalNetoGravable * self::IVA_RATE, 2);
        $totalFinal = round($subtotalNetoGravable + $subtotalNetoExento + $montoIVA, 2);

        return [
            'subtotal' => $subtotalBruto,
            'monto_descuento' => $montoDescuentoTotal,
            'monto_iva' => $montoIVA,
            'total_final' => $totalFinal,
        ];
    }

    // =========================================================================
    // FUNCIÓN REALIZAR PEDIDO (SOPORTA CREACIÓN Y EDICIÓN DE COTIZACIONES)
    // =========================================================================
    public function realizar(Request $request){
        $carrito = session()->get('carrito', []);
        if (empty($carrito)) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        // ==========================================================
        // 1. DETECTAMOS LA ACCIÓN PARA SABER SI EXIGIMOS LA DIRECCIÓN
        // ==========================================================
        $accion = $request->input('accion_guardado', 'pedido'); // 'pedido' por defecto
        $clienteTemp = Cliente::find($request->input('cliente_id'));
        $esCotizacion = ($accion === 'cotizacion' || ($clienteTemp && $clienteTemp->codigo === 'GENERAL'));

        // ==========================================================
        // 2. VALIDACIÓN (Dirección ahora es opcional para todo)
        // ==========================================================
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'comentarios' => 'nullable|string|max:1000',
            'flete_pagado' => 'nullable|boolean',
            'direccion_entrega' => 'nullable|string',
        ], [
            'cliente_id.required' => 'Debe seleccionar un cliente para el pedido.'
        ]);

        $clienteId = $validatedData['cliente_id'];
        $cliente = Cliente::findOrFail($clienteId);
        $user = Auth::user();

        if (!$user->hasRole('admin') && $cliente->codigo !== 'GENERAL' && $cliente->user_id != $user->id) {
             return redirect()->back()->with('error', 'El cliente seleccionado no le pertenece.');
        }

        // =======================================================
        // CORRECCIÓN: Respetar el descuento manual del select
        // =======================================================
        $descuentoCliente = $cliente->descuento ?? 0.00;
        
        $esMostrador = str_contains(strtoupper($cliente->nombre), 'VENTAS DE MOSTRADOR') || str_contains(strtoupper($cliente->nombre), 'VENTAS MOSTRADOR');
        
        // Si es Cliente General o Ventas de Mostrador, permitimos aplicar el descuento dinámico
        if (($cliente->codigo === 'GENERAL' || $esMostrador) && $request->filled('descuento_manual')) {
            $descuentoCliente = floatval($request->input('descuento_manual'));
        }

        $calculos = $this->calculateFinalAmounts($carrito, $descuentoCliente);

        // Verificamos si estamos editando una cotización previamente cargada
        $cotizacionId = session()->get('cotizacion_id');

        DB::beginTransaction();
        try {
            // Variables iniciales según el botón presionado
            if ($esCotizacion) {
                $estadoInicial = 'cotizacion';
                $isCotizacion = 1;
                $fechaVencimiento = \Carbon\Carbon::now()->addDays(6)->toDateString(); // Caduca en 6 días
            } else {
                $estadoInicial = 'pendiente';
                $isCotizacion = 0;
                $fechaVencimiento = null; // Los pedidos no caducan
            }
            
            $comentariosFinales = $validatedData['comentarios'] ?? '';
            if ($request->has('flete_pagado') && $request->flete_pagado == '1') {
                $comentariosFinales .= " |FP:1|"; 
            }

            // =======================================================
            // LÓGICA BLINDADA: BUSCAR, ACTUALIZAR O CREAR
            // =======================================================
            $registroPrevio = null;
            if ($cotizacionId) {
                $registroPrevio = Pedido::find($cotizacionId); 
            }

            $pedido = null;

            if ($registroPrevio) {
                // 1. ¿ESTAMOS CONVIRTIENDO UNA COTIZACIÓN A PEDIDO DESDE EL CARRITO?
                if ($registroPrevio->is_cotizacion == 1 && $isCotizacion == 0) {
                    
                    // Creamos el NUEVO Pedido oficial (con fecha y hora de hoy)
                    $pedido = Pedido::create([
                        'user_id' => $user->id,
                        'cliente_id' => $clienteId,
                        'total' => $calculos['total_final'],
                        'subtotal' => $calculos['subtotal'],
                        'descuento_aplicado' => $calculos['monto_descuento'],
                        'iva' => $calculos['monto_iva'],
                        'estado' => $estadoInicial,
                        'is_cotizacion' => $isCotizacion,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'comentarios' => trim($comentariosFinales),
                        'direccion_entrega' => $validatedData['direccion_entrega'] ?? null,
                        'created_at' => \Carbon\Carbon::now(), // Forzamos fecha actual
                        'updated_at' => \Carbon\Carbon::now(),
                    ]);

                    // Marcamos la cotización original para que no desaparezca del historial
                    $registroPrevio->estado = 'Convertida a Pedido';
                    $registroPrevio->comentarios = trim($registroPrevio->comentarios . " | Convertida al Pedido #{$pedido->id} tras edición.");
                    $registroPrevio->save();

                    $mensaje = 'Cotización procesada. Se generó el Pedido Oficial #' . $pedido->id;
                } 
                // 2. SOLO ESTAMOS ACTUALIZANDO (Cotización -> Cotización  O  Pedido -> Pedido)
                else {
                    $registroPrevio->update([
                        'cliente_id' => $clienteId,
                        'total' => $calculos['total_final'],
                        'subtotal' => $calculos['subtotal'],
                        'descuento_aplicado' => $calculos['monto_descuento'],
                        'iva' => $calculos['monto_iva'],
                        'estado' => $estadoInicial,
                        'is_cotizacion' => $isCotizacion,
                        'fecha_vencimiento' => ($isCotizacion) ? $registroPrevio->fecha_vencimiento : null,
                        'comentarios' => trim($comentariosFinales),
                        'direccion_entrega' => $validatedData['direccion_entrega'] ?? null,
                    ]);
                    
                    // Borramos los detalles viejos para insertar los nuevos del carrito
                    $registroPrevio->detalles()->delete();
                    $pedido = $registroPrevio;
                    $mensaje = ($isCotizacion) ? 'Cotización actualizada correctamente.' : 'Pedido actualizado y guardado en Pendientes.';
                }
            } else {
                // 3. ESTAMOS CREANDO UN PEDIDO O COTIZACIÓN COMPLETAMENTE NUEVO
                $pedido = Pedido::create([
                    'user_id' => $user->id,
                    'cliente_id' => $clienteId,
                    'total' => $calculos['total_final'],
                    'subtotal' => $calculos['subtotal'],
                    'descuento_aplicado' => $calculos['monto_descuento'],
                    'iva' => $calculos['monto_iva'],
                    'estado' => $estadoInicial,
                    'is_cotizacion' => $isCotizacion,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'comentarios' => trim($comentariosFinales),
                    'direccion_entrega' => $validatedData['direccion_entrega'] ?? null,
                ]);
                $mensaje = ($isCotizacion) 
                            ? 'Cotización guardada en el sistema (Vigencia de 6 días).' 
                            : 'Pedido realizado correctamente para el cliente: ' . $cliente->nombre . '.';
            }

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
                     'subtotal' => $subtotalLinea,
                 ]);
            }

            // Limpiamos la sesión
            session()->forget(['carrito', 'current_client_id', 'current_client_name', 'cotizacion_id']);
            DB::commit();

            if ($isCotizacion) {
                return redirect()->route('cotizaciones.index')->with('mensaje', $mensaje);
            } else {
                return redirect()->route('perfil.pedidos')->with('mensaje', $mensaje);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Hubo un error al procesar el pedido/cotización. Intente de nuevo.');
        }
    }

    public function cambiarEstado(Request $request, $id){
        $pedido = Pedido::findOrFail($id);
        $estadoNuevo = strtolower($request->input('estado')); 
        $estadoAnterior = strtolower($pedido->estado);
        $user = auth()->user();

        if ($estadoAnterior === 'cotizacion') {
             abort(403, 'No se puede cambiar el estado de una cotización desde esta pantalla.');
        }

        $estadosPermitidos = [
            'aprobado',
            'rechazado',
            'parcialmente_surtido', 
            'enviado_completo', 
            'anulado', 
            'cancelado', 
            'entregado'
        ];
        
        if (!in_array($estadoNuevo, $estadosPermitidos)) {
            abort(403, 'Estado no válido: ' . $estadoNuevo);
        }

        // ==========================================================
        // LÓGICA DE TRANSICIONES Y PERMISOS
        // ==========================================================
        
        // 1. ACEPTAR / RECHAZAR (Permite rescatar pedidos rechazados y revertir aprobados)
        if (in_array($estadoNuevo, ['aprobado', 'rechazado'])) {
            if (!$user->can('pedido-anulate')) { 
                 abort(403, 'No tiene permiso para aprobar o rechazar pedidos.');
            }
            
            // AGREGAMOS 'aprobado' A LA LISTA DE ESTADOS PERMITIDOS
            if (!in_array($estadoAnterior, ['pendiente', 'rechazado', 'aprobado'])) {
                abort(403, 'Solo se pueden aprobar o rechazar pedidos que estén Pendientes, Rechazados o Aprobados.');
            }

            if ($estadoNuevo === 'rechazado') {
                $request->validate(['motivo_rechazo' => 'required|string|max:1000'], [
                    'motivo_rechazo.required' => 'Debe escribir el motivo del rechazo.'
                ]);
                $pedido->motivo_rechazo = trim($request->input('motivo_rechazo'));
            } 
            elseif ($estadoNuevo === 'aprobado') {
                $pedido->motivo_rechazo = null;
            }
        }

        // 2. CANCELAR (LÓGICA AMPLIADA PARA ADMITIR PEDIDOS APROBADOS Y PARCIALES)
        elseif ($estadoNuevo === 'cancelado') {
            // Permitir cancelar si está Pendiente, Aprobado o Parcialmente Surtido
            if (!in_array($estadoAnterior, ['pendiente', 'aprobado', 'parcialmente_surtido'])) {
                 abort(403, 'No se puede cancelar un pedido que ya se encuentra en estado: ' . ucfirst($estadoAnterior));
            }
            
            // Si el pedido ya está Aprobado o Parcial, requiere forzosamente permiso de Admin/Logística
            if (in_array($estadoAnterior, ['aprobado', 'parcialmente_surtido'])) {
                if (!$user->can('pedido-anulate')) {
                     abort(403, 'No tiene permisos de administración para cancelar un pedido que ya fue aprobado.');
                }
            } else {
                // Si el pedido seguía Pendiente, el vendedor con su permiso normal puede cancelarlo
                if (!$user->can('pedido-cancel') && !$user->can('pedido-anulate')) {
                     abort(403, 'No tiene permiso para cancelar pedidos.');
                }
            }

            // Si escribieron un motivo de cancelación en el modal, lo guardamos en la base de datos
            if ($request->filled('motivo_rechazo')) {
                $pedido->motivo_rechazo = trim($request->input('motivo_rechazo'));
            }
        }

        // 3. PARCIALMENTE SURTIDO (Solo Admin/Logística a pedidos aprobados)
        elseif ($estadoNuevo === 'parcialmente_surtido') {
            if (!$user->can('pedido-anulate')) { 
                 abort(403, 'No tiene permiso para gestionar almacén.');
            }
            if ($estadoAnterior !== 'aprobado') {
                abort(403, 'Para comenzar a surtir, el pedido debe haber sido Aprobado.');
            }
        }

        // 4. ENVIADO COMPLETO (Solo Admin/Logística a aprobados o parciales)
        elseif ($estadoNuevo === 'enviado_completo') {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para realizar esta acción.');
            }
            if (!in_array($estadoAnterior, ['aprobado', 'parcialmente_surtido'])) {
                 abort(403, 'Solo se pueden enviar pedidos Aprobados o Parcialmente Surtidos.');
            }
        }

        // 5. ANULAR O ENTREGAR (Solo Admin a enviados)
        elseif (in_array($estadoNuevo, ['anulado', 'entregado'])) {
            if (!$user->can('pedido-anulate')) {
                 abort(403, 'No tiene permiso para realizar esta acción.');
            }
            if (!in_array($estadoAnterior, ['enviado', 'enviado_completo'])) {
                 abort(403, 'Esta acción solo es válida para pedidos que ya fueron Enviados.');
            }
        }

        // =======================================================
        // GUARDADO FINAL DE LA HUELLA PARA CUALQUIER ESTADO
        // =======================================================
        
        $pedido->validador_id = $user->id; 
        $pedido->fecha_validacion = \Carbon\Carbon::now(); 
        $pedido->estado = $estadoNuevo;
        $pedido->save();

        // INSERCIÓN EN LA BITÁCORA
        HistorialPedido::create([
            'pedido_id' => $pedido->id,
            'user_id' => $user->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'comentario' => $request->filled('motivo_rechazo') ? trim($request->input('motivo_rechazo')) : null,
        ]);

        $nombreEstado = ucwords(str_replace('_', ' ', $estadoNuevo));
        return redirect()->back()->with('mensaje', 'El pedido fue actualizado a: ' . $nombreEstado);
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

            return response()->json(['success' => true, 'message' => 'GuÃ­a actualizada correctamente.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    // GENERAR PDF DE UN PEDIDO O COTIZACIÓN YA GUARDADO
    public function generarPdfPedido($id)
    {
        $pedido = Pedido::with(['cliente', 'agente', 'detalles.producto'])->findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
             if ($pedido->user_id != $user->id && $pedido->cliente->user_id != $user->id) {
                 abort(403, 'No tiene permiso para ver este documento.');
             }
        }

        $logoBase64 = null;
        try {
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); 
            $pathLogo = $docRoot . '/assets/img/LOGO.png'; 
            if (!file_exists($pathLogo)) $pathLogo = public_path('assets/img/LOGO.png');
            
            if (file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $dataLogo = file_get_contents($pathLogo);
                if ($dataLogo !== false) {
                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($dataLogo);
                }
            }
        } catch (\Exception $e) {}

        // Reconstruimos el carrito para la vista de cotización
        $carritoVirtual = [];
        foreach ($pedido->detalles as $det) {
            $carritoVirtual[$det->producto_id] = [
                'nombre' => $det->producto->nombre ?? 'Producto',
                'codigo' => $det->producto->codigo ?? 'N/A',
                'cantidad' => $det->cantidad,
                'precio' => $det->precio,
                'aplica_iva' => $det->producto->aplica_iva ?? true,
                'inner' => $det->inner,
                'imagen' => $det->producto->imagen ?? null,
            ];
        }

        // =========================================================
        // CALCULAMOS LAS VARIABLES FINANCIERAS PARA LA VISTA
        // =========================================================
        $subtotal_bruto = $pedido->subtotal;
        $monto_descuento = $pedido->descuento_aplicado;
        
        // Evitamos división por cero al calcular el porcentaje
        $descuento_porcentaje_real = ($subtotal_bruto > 0) ? ($monto_descuento / $subtotal_bruto) : 0;
        $descuento_porcentaje_vista = ($subtotal_bruto > 0) ? round($descuento_porcentaje_real * 100) : 0;
        
        $subtotal_neto = $subtotal_bruto - $monto_descuento;
        $monto_iva = $pedido->iva;
        $total_final = $pedido->total;

        // --- NUEVO: Desglose de Gravable vs Exento para el PDF ---
        $subtotal_gravable = 0;
        $subtotal_exento = 0;

        foreach ($pedido->detalles as $det) {
            $aplica_iva = $det->producto->aplica_iva ?? true;
            $subtotalLineaBruto = $det->precio * $det->cantidad;
            $subtotalLineaNeto = $subtotalLineaBruto - ($subtotalLineaBruto * $descuento_porcentaje_real);

            if ($aplica_iva) {
                $subtotal_gravable += $subtotalLineaNeto;
            } else {
                $subtotal_exento += $subtotalLineaNeto;
            }
        }

        $data = [
            'pedido' => $pedido,
            'logoBase64' => $logoBase64,
            
            // --- VARIABLES DE INFORMACIÓN ---
            'cliente' => $pedido->cliente,
            'usuario' => $pedido->agente,
            'fecha' => $pedido->created_at,
            'carrito' => $carritoVirtual, 
            
            // --- VARIABLES FINANCIERAS (Totales) ---
            'subtotal_bruto' => $subtotal_bruto,
            'descuento_porcentaje' => $descuento_porcentaje_vista,
            'monto_descuento' => $monto_descuento,
            'subtotal_neto' => $subtotal_neto,
            'subtotal_gravable' => $subtotal_gravable, // <-- Agregado
            'subtotal_exento' => $subtotal_exento,     // <-- Agregado
            'monto_iva' => $monto_iva,
            'total' => $total_final, 
            'total_final' => $total_final,
        ];

        if ($pedido->is_cotizacion == 1) {
            $vista = 'pdf.cotizacion'; 
            $nombreArchivo = 'Cotizacion-#' . $pedido->id . '.pdf';
        } else {
            $vista = 'pdf.pedido'; 
            $nombreArchivo = 'Pedido-#' . $pedido->id . '.pdf';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($vista, $data);
        $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'isRemoteEnabled' => true]);
        
        return $pdf->stream($nombreArchivo);
    }

    // =========================================================================
    // CARGAR UN PEDIDO AL CARRITO PARA EDITARLO (PENDIENTES O RECHAZADOS)
    // =========================================================================
    public function editar($id)
    {
        $pedido = Pedido::with('detalles.producto')->findOrFail($id);
        $user = Auth::user();

        // 1. Validar que solo se puedan editar si están Pendientes o Rechazados
        if (!in_array(strtolower($pedido->estado), ['pendiente', 'rechazado'])) {
            return redirect()->back()->with('error', 'Solo se pueden editar pedidos en estado Pendiente o Rechazado.');
        }

        // 2. Validar que sea el dueño del pedido o un administrador
        if (!$user->hasRole('admin') && !$user->hasRole('superadmin')) {
            if ($pedido->user_id != $user->id) {
                abort(403, 'No tienes permiso para editar este pedido.');
            }
        }

        // 3. Reconstruir el carrito en la sesión
        $carrito = [];
        foreach ($pedido->detalles as $det) {
            $carrito[$det->producto_id] = [
                'nombre' => $det->producto->nombre ?? 'Producto',
                'codigo' => $det->producto->codigo ?? 'N/A',
                'imagen' => $det->producto->imagen ?? null,
                'precio' => $det->precio,
                'cantidad' => $det->cantidad,
                'inner' => $det->inner,
                'aplica_iva' => $det->producto->aplica_iva ?? true,
            ];
        }

        session()->put('carrito', $carrito);
        session()->put('current_client_id', $pedido->cliente_id);
        session()->put('current_client_name', $pedido->cliente->nombre ?? 'Cliente');
        
        // ¡El truco de magia! Usamos la misma variable que ya lee tu función realizar()
        session()->put('cotizacion_id', $pedido->id); 

        return redirect()->route('carrito.mostrar')->with('mensaje', 'Pedido cargado al carrito. Haz tus correcciones y vuelve a procesarlo.');
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