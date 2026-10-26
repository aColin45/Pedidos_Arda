<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CotizacionController extends Controller
{
    public function index(Request $request) 
    {
        $texto = $request->input('texto');
        $estadoFiltro = $request->input('estado');     // Para las tarjetas
        $agenteFiltro = $request->input('agente_id');  // Para el selector

        $query = Pedido::with(['cliente', 'agente'])->where('is_cotizacion', 1);

        // Seguridad por roles: Agentes solo ven las suyas
        if (!Auth::user()->hasAnyRole(['admin', 'super-admin', 'superadmin'])) {
            $query->where('user_id', Auth::id());
        }

        // ==========================================
        // APLICAR FILTROS INTERACTIVOS
        // ==========================================
        // 1. Filtro por Estado (Tarjetas)
        if (!empty($estadoFiltro)) {
            if ($estadoFiltro == 'activa') {
                // Cotizaciones activas y no vencidas
                $query->whereIn('estado', ['cotizacion', 'Cotizacion'])
                      ->where('fecha_vencimiento', '>=', Carbon::now()->toDateString());
            } elseif ($estadoFiltro == 'convertida') {
                $query->whereIn('estado', ['Convertida a Pedido']);
            } elseif ($estadoFiltro == 'rechazada') {
                $query->whereIn('estado', ['No Procede', 'cancelado', 'Cancelado']);
            } elseif ($estadoFiltro == 'caducada') {
                $query->whereIn('estado', ['caducada']);
            }
        } else {
            // Lógica original cuando no hay filtro seleccionado
            $query->where(function($q) {
                $q->where(function($sub) {
                    $sub->whereIn('estado', ['cotizacion', 'Cotizacion'])
                        ->where('fecha_vencimiento', '>=', Carbon::now()->toDateString());
                })
                ->orWhereIn('estado', ['cancelado', 'Cancelado', 'No Procede', 'caducada', 'Cerrada', 'Convertida a Pedido']);
            });
        }

        // 2. Filtro por Agente
        if (!empty($agenteFiltro) && Auth::user()->hasAnyRole(['admin', 'super-admin', 'superadmin'])) {
            $query->where('user_id', $agenteFiltro);
        }

        // 3. Búsqueda de texto
        if (!empty($texto)) {
            $query->where(function($q) use ($texto) {
                $q->where('id', 'like', "%{$texto}%")
                  ->orWhere('estado', 'like', "%{$texto}%")
                  ->orWhereHas('cliente', function($q2) use ($texto) {
                      $q2->where('nombre', 'like', "%{$texto}%");
                  })
                  ->orWhereHas('agente', function($q3) use ($texto) {
                      $q3->where('name', 'like', "%{$texto}%");
                  });
            });
        }
        
        $cotizaciones = $query->orderBy('id', 'desc')->paginate(15);

        // ==========================================
        // CALCULAR KPIs PARA LAS 5 TARJETAS
        // ==========================================
        $baseQuery = Pedido::where('is_cotizacion', 1);
        if (!Auth::user()->hasAnyRole(['admin', 'super-admin', 'superadmin'])) {
            $baseQuery->where('user_id', Auth::id());
        }

        $kpis = [
            'total' => (clone $baseQuery)->count(),
            'activas' => (clone $baseQuery)->whereIn('estado', ['cotizacion', 'Cotizacion'])->where('fecha_vencimiento', '>=', Carbon::now()->toDateString())->count(),
            'convertidas' => (clone $baseQuery)->where('estado', 'Convertida a Pedido')->count(),
            'rechazadas' => (clone $baseQuery)->whereIn('estado', ['No Procede', 'cancelado', 'Cancelado'])->count(),
            'caducadas' => (clone $baseQuery)->where('estado', 'caducada')->count(),
        ];

        // ==========================================
        // SELECTOR DE AGENTES DINÁMICO
        // ==========================================
        $agentes = [];
        if (Auth::user()->hasAnyRole(['admin', 'super-admin', 'superadmin'])) {
            $agentesIds = Pedido::where('is_cotizacion', 1)->select('user_id')->distinct()->pluck('user_id');
            $agentes = \App\Models\User::whereIn('id', $agentesIds)->where('activo', 1)->get();
        }

        return view('cotizaciones.index', compact('cotizaciones', 'texto', 'estadoFiltro', 'agenteFiltro', 'kpis', 'agentes'));
    }

    // Carga los productos al carrito para editarlos
    public function editar($id)
    {
        $cotizacion = Pedido::with('detalles.producto')->findOrFail($id);
        
        if (!Auth::user()->hasAnyRole(['admin', 'super-admin', 'superadmin'])) {
            if ($cotizacion->user_id != Auth::id()) {
                abort(403, 'No tienes permiso para editar esta cotización.');
            }
        }

        $carrito = [];
        foreach ($cotizacion->detalles as $det) {
            $carrito[$det->producto_id] = [
                'nombre' => $det->producto->nombre ?? 'Producto',
                
                // --- AGREGAMOS LOS DATOS FALTANTES QUE EXIGE TU VISTA DEL CARRITO ---
                'codigo' => $det->producto->codigo ?? 'N/A',
                'imagen' => $det->producto->imagen ?? null, 
                // --------------------------------------------------------------------
                
                'precio' => $det->precio,
                'cantidad' => $det->cantidad,
                'inner' => $det->inner,
                'aplica_iva' => $det->producto->aplica_iva ?? true,
            ];
        }

        session()->put('carrito', $carrito);
        session()->put('current_client_id', $cotizacion->cliente_id);
        session()->put('current_client_name', $cotizacion->cliente->nombre ?? 'Cliente Cotización');
        session()->put('cotizacion_id', $cotizacion->id); // Le avisa al PedidoController que es edición

        return redirect()->route('carrito.mostrar')->with('mensaje', 'Cotización cargada al carrito. Modifique lo necesario y procese el pedido.');
    }

    // Convierte clonando para generar un Pedido Nuevo con ID reciente
    public function convertir(Request $request, $id)
    {
        // 1. Validamos la dirección (Ahora es opcional)
        $request->validate([
            'direccion_entrega' => 'nullable|string'
        ]);

        $cotizacionVieja = Pedido::with('detalles')->findOrFail($id);
        
        // 2. Verificación de seguridad (No permitir clientes GENERAL)
        if ($cotizacionVieja->cliente->codigo === 'GENERAL') {
            return redirect()->back()->with('error', 'No puede convertir a pedido a un cliente GENERAL. Edite la cotización y asigne un cliente real.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 3. CLONAMOS LA COTIZACIÓN ORIGINAL (Se crea un registro totalmente nuevo)
            $nuevoPedido = $cotizacionVieja->replicate();
            
            // 4. Transformamos los datos del nuevo clon para que sea un pedido oficial
            $nuevoPedido->is_cotizacion = 0;
            $nuevoPedido->estado = 'pendiente';
            $nuevoPedido->fecha_vencimiento = null; // Ya es pedido, no caduca
            $nuevoPedido->direccion_entrega = trim($request->input('direccion_entrega'));
            $nuevoPedido->created_at = Carbon::now(); // Forzamos la fecha/hora actual
            $nuevoPedido->updated_at = Carbon::now();
            
            // Guardamos el nuevo pedido (Aquí se le asigna el nuevo ID de la base de datos)
            $nuevoPedido->save();

            // 5. CLONAMOS LOS DETALLES (PRODUCTOS) PARA EL NUEVO PEDIDO
            foreach ($cotizacionVieja->detalles as $detalleViejo) {
                $nuevoDetalle = $detalleViejo->replicate();
                $nuevoDetalle->pedido_id = $nuevoPedido->id; // Lo enganchamos al nuevo ID
                $nuevoDetalle->save();
            }

            // 6. ¿Qué hacemos con la cotización vieja?
            // Opción recomendada para historial: La marcamos como "Convertida" o "Cerrada"
            $cotizacionVieja->estado = 'Convertida a Pedido'; // O 'Cerrada'
            $cotizacionVieja->comentarios = trim($cotizacionVieja->comentarios . " | Convertida al Pedido #{$nuevoPedido->id}");
            $cotizacionVieja->save();

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('perfil.pedidos')->with('mensaje', '¡Cotización convertida exitosamente! Se generó el Pedido #' . $nuevoPedido->id);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al intentar convertir la cotización a pedido.');
        }
    }

    // Cancelar (Para el agente)
    public function cancelar($id)
    {
        $cotizacion = Pedido::findOrFail($id);
        $cotizacion->estado = 'cancelado';
        $cotizacion->save();
        return redirect()->back()->with('mensaje', 'Cotización cancelada por el Agente.');
    }

    // No Procede (Para el Administrador)
    public function rechazar(Request $request, $id)
    {
        $request->validate(['motivo' => 'required|string|max:255']);
        
        $cotizacion = Pedido::findOrFail($id);
        $cotizacion->estado = 'No Procede';
        $cotizacion->motivo_rechazo = $request->motivo;
        $cotizacion->save();

        return redirect()->back()->with('mensaje', 'Cotización marcada como "No Procede".');
    }
}