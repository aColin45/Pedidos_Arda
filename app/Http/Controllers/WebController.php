<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Cliente; // Necesario para gestionar los clientes
use App\Models\User;    // Necesario para verificar roles

class WebController extends Controller
{
    public function index(Request $request){
        $query = Producto::query();

        // =========================================================
        // LÓGICA DE PRODUCTOS ESPECIALES / EXCLUSIVOS
        // =========================================================
        if (auth()->check() && !auth()->user()->hasRole('admin')) {
            $agenteId = auth()->id();
            
            // Si es agente, ve los normales (0) + los especiales que le asignaron
            $query->where(function($q) use ($agenteId) {
                $q->where('es_especial', 0)
                  ->orWhereHas('usuariosPermitidos', function($subQ) use ($agenteId) {
                      $subQ->where('user_id', $agenteId);
                  });
            });
        }
        elseif (!auth()->check()) {
            // Si no está logueado, solo ve los normales
            $query->where('es_especial', 0);
        }
        // Si es admin, no entra en ningún if y ve todos (es_especial 0 y 1)

        // =========================================================
        // FILTRO RÁPIDO DE "SOLO MIS EXCLUSIVOS"
        // =========================================================
        if ($request->input('solo_exclusivos') == '1') {
            $query->where('es_especial', 1);
        }

        // ==========================================
        //  MODIFICACIÓN DE BÚSQUEDA (Nombre O Código)
        // ==========================================
        if ($request->has('search') && $request->search) {
            $busqueda = $request->search;
            // Usamos una función anónima (closure) para agrupar el OR
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'like', '%' . $busqueda . '%')
                  ->orWhere('codigo', 'like', '%' . $busqueda . '%');
            });
        }

        // Filtro de orden (Ordenar por precio)
        if ($request->has('sort') && $request->sort) {
            switch ($request->sort) {
                case 'priceAsc':
                    $query->orderBy('precio', 'asc');
                    break;
                case 'priceDesc':
                    $query->orderBy('precio', 'desc');
                    break;
                default:
                    $query->orderBy('nombre', 'asc');
                    break;
            }
        }
        
        // Obtener productos filtrados
        $productos = $query->paginate(10);     

        // --- NUEVO: EXTRAER PRECIOS E INNERS ESPECIALES DEL USUARIO LOGUEADO ---
        $preciosEspeciales = [];
        $innersEspeciales = [];
        if (auth()->check()) {
            $pivotData = auth()->user()->productosEspeciales()->get();
            foreach($pivotData as $p) {
                if($p->pivot->precio_especial !== null) $preciosEspeciales[$p->id] = $p->pivot->precio_especial;
                if($p->pivot->inner_especial !== null) $innersEspeciales[$p->id] = $p->pivot->inner_especial;
            }
        }

        return view('web.index', compact('productos', 'preciosEspeciales', 'innersEspeciales'));
    }

    // Método existente para la vista de detalle de producto
    public function show($id){
        $producto = Producto::findOrFail($id);        
        
        // --- NUEVO: EXTRAER PRECIO E INNER ESPECIAL SI EXISTE ---
        $precioEspecial = null;
        $innerEspecial = null;
        if (auth()->check()) {
            $pivot = auth()->user()->productosEspeciales()->where('producto_id', $id)->first();
            if ($pivot) {
                if ($pivot->pivot->precio_especial !== null) $precioEspecial = $pivot->pivot->precio_especial;
                if ($pivot->pivot->inner_especial !== null) $innerEspecial = $pivot->pivot->inner_especial;
            }
        }

        return view('web.item', compact('producto', 'precioEspecial', 'innerEspecial'));
    }

    // =================================================================
    // NUEVOS MÉTODOS PARA EL FLUJO DEL AGENTE DE VENTAS
    // =================================================================

    /**
     * Muestra la lista de clientes disponibles para el Agente logueado.
     */
    public function selectClient()
    {
        // 1. Verificar roles: solo Admin y Agente pueden acceder a esta vista.
        if (!auth()->check() || (!auth()->user()->hasRole('agente-ventas') && !auth()->user()->hasRole('admin'))) {
            abort(403, 'Acceso denegado. Esta función es solo para Agentes de Ventas y Administradores.');
        }

        $agenteId = auth()->id();
        
        // 2. Cargar clientes: El Admin ve todos, el Agente solo ve los suyos.
        if (auth()->user()->hasRole('admin')) {
            // Agregamos with('agente') para optimizar la consulta y enviar la info a la vista
            $clientes = Cliente::with('agente')->where('activo', true)->orderBy('nombre')->get();
        } else {
            // El agente solo ve los clientes donde user_id es su propio ID
            $clientes = Cliente::with('agente')
                              ->where('user_id', $agenteId)
                              ->where('activo', true)
                              ->orderBy('nombre')
                              ->get();
        }

        return view('web.select-client', compact('clientes'));
    }

    /**
     * Inicia el proceso de pedido para el cliente seleccionado, guardando
     * su ID en la sesión y redirigiendo al catálogo.
     */
    public function startOrder(Cliente $cliente)
    {
        // 1. Verificar autorización (seguridad de URL): 
        // Solo Admin o el Agente asignado al cliente pueden iniciar el pedido.
        if (!auth()->user()->hasRole('admin') && $cliente->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para tomar pedidos de este cliente.');
        }
        
        // 2. Limpia el carrito existente para un pedido nuevo y limpio.
        session()->forget('carrito');
        
        // 3. Guarda el ID y nombre del cliente en la sesión.
        session(['current_client_id' => $cliente->id]); 
        session(['current_client_name' => $cliente->nombre]);
        
        // 4. Redirige al índice de la tienda para que el agente empiece a agregar productos.
        return redirect()->route('web.index')->with('mensaje', 'Pedido iniciado para el cliente: ' . $cliente->nombre . '. ¡Comience a agregar productos!');
    }
}