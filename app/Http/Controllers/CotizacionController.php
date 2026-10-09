<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CotizacionController extends Controller
{
    public function index(Request $request) // <-- IMPORTANTE: Agregar Request $request
    {
        $texto = $request->input('texto');

        // Traemos los registros que sean cotizaciones o que hayan sido marcados como No Procede/Cancelados
        $query = Pedido::with(['cliente', 'agente'])
                       ->where('is_cotizacion', 1);

        // LÓGICA DE VISIBILIDAD BLINDADA (Historial permanente)
        $query->where(function($q) {
            // 1. Mostrar las activas que NO han vencido
            $q->where(function($sub) {
                $sub->whereIn('estado', ['cotizacion', 'Cotizacion'])
                    ->where('fecha_vencimiento', '>=', Carbon::now()->toDateString());
            })
            // 2. Mostrar SIEMPRE el historial de las cerradas (sin importar la fecha)
            ->orWhereIn('estado', ['cancelado', 'Cancelado', 'No Procede', 'caducada', 'Cerrada', 'Convertida a Pedido']);
        });

        // Seguridad por roles: Agentes solo ven las suyas
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            $query->where('user_id', Auth::id());
        }
        
        // --- NUEVO: APLICAR EL FILTRO DE BÚSQUEDA ---
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
        return view('cotizaciones.index', compact('cotizaciones', 'texto')); // <-- Retornar $texto
    }

    // Carga los productos al carrito para editarlos
    public function editar($id)
    {
        $cotizacion = Pedido::with('detalles.producto')->findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
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