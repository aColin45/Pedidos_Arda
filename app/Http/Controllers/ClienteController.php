<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClienteController extends Controller
{
    use AuthorizesRequests;

    // El constructor fue omitido por la solución del error de middleware.

    public function index(Request $request)
    {
        // Parámetros de la solicitud
        $texto = $request->get('texto');
        $sort = $request->get('sort'); // Captura el parámetro de ordenación

        // 1. Inicializar query con la relación 'agente'
        $query = Cliente::with('agente');
        
        // 2. Lógica de visualización: Restringir a clientes asignados si es Agente
        if (auth()->user()->hasRole('agente-ventas') && !auth()->user()->hasRole('admin')) {
             $query->where('user_id', auth()->id());
        }

        $texto = $request->get('texto');
        
        // 3. Lógica de BÚSQUEDA MULTICAMPO
        if ($texto) {
            // Utilizamos where(function) para agrupar las condiciones OR
            $query->where(function ($q) use ($texto) {
                
                // Buscar por Nombre de Cliente
                $q->where('nombre', 'like', "%{$texto}%")
                  
                  // O buscar por Código de Cliente
                  ->orWhere('codigo', 'like', "%{$texto}%")
                  
                  // O buscar por Nombre del Agente Asignado (Relación 'agente')
                  ->orWhereHas('agente', function ($qAgente) use ($texto) {
                      $qAgente->where('name', 'like', "%{$texto}%");
                  });
            });
        }

        // 4. Lógica de ORDENACIÓN (Por defecto, ordenar por Nombre ascendente)
        switch ($sort) {
            case 'codigoAsc':
                $query->orderBy('codigo', 'asc');
                break;
            case 'codigoDesc':
                $query->orderBy('codigo', 'desc');
                break;
            default:
                $query->orderBy('nombre', 'asc'); // Orden por nombre A-Z por defecto
                break;
        }
            
        $clientes = $query->orderBy('nombre', 'asc')->paginate(10);
            
        return view('cliente.index', compact('clientes', 'texto', 'sort'));
    }

    public function create()
    {
        // Obtener solo usuarios con el rol 'agente-ventas' para la asignación
        $agentes = User::role('agente-ventas')->get();
        return view('cliente.action', compact('agentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:50|unique:clientes,codigo',
            'email' => 'nullable|email|unique:clientes,email',
            'user_id' => 'nullable|exists:users,id',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'activo' => 'required|boolean'
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes.index')->with('mensaje', 'Cliente creado exitosamente.');
    }

    public function edit(Cliente $cliente)
    {
        // Verificar autorización
        if (auth()->user()->hasRole('agente-ventas') && $cliente->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar este cliente.');
        }

        $agentes = User::role('agente-ventas')->get();
        return view('cliente.action', compact('cliente', 'agentes'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        // Verificar autorización
        if (auth()->user()->hasRole('agente-ventas') && $cliente->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para actualizar este cliente.');
        }
        
        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:50|unique:clientes,codigo,' . $cliente->id,
            'email' => 'nullable|email|unique:clientes,email,'.$cliente->id,
            'user_id' => 'nullable|exists:users,id',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'activo' => 'required|boolean'
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes.index')->with('mensaje', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('mensaje', 'Cliente eliminado.');
    }

    public function toggleStatus(Cliente $cliente)
    {
        $this->authorize('cliente-edit');
        
        $cliente->activo = !$cliente->activo; 
        $cliente->save();

        $estado = $cliente->activo ? 'activado' : 'inhabilitado';
        return redirect()->route('clientes.index')->with('mensaje', "Cliente {$cliente->nombre} ha sido {$estado} correctamente.");
    }
}