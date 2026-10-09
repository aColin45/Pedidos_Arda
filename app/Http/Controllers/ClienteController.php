<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Exports\ClientesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\AlertaSistema;

class ClienteController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $texto = $request->get('texto');
        $sort = $request->get('sort'); 

        $query = Cliente::with('agente');
        
        if (auth()->user()->hasRole('agente-ventas') && !auth()->user()->hasRole('admin')) {
             $query->where('user_id', auth()->id());
        }

        $texto = $request->get('texto');
        
        if ($texto) {
            $query->where(function ($q) use ($texto) {
                $q->where('nombre', 'like', "%{$texto}%")
                  ->orWhere('codigo', 'like', "%{$texto}%")
                  ->orWhereHas('agente', function ($qAgente) use ($texto) {
                      $qAgente->where('name', 'like', "%{$texto}%");
                  });
            });
        }

        switch ($sort) {
            case 'codigoAsc':
                $query->orderBy('codigo', 'asc');
                break;
            case 'codigoDesc':
                $query->orderBy('codigo', 'desc');
                break;
            default:
                $query->orderBy('nombre', 'asc'); 
                break;
        }
            
        $clientes = $query->orderBy('nombre', 'asc')->paginate(10);
            
        return view('cliente.index', compact('clientes', 'texto', 'sort'));
    }

    public function create()
    {
        // La nueva consulta (trae agentes y administradores):
        $agentes = User::role(['agente-ventas', 'admin'])->get();
        return view('cliente.action', compact('agentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:50|unique:clientes,codigo',
            'email' => 'nullable|email|unique:clientes,email',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:150',
            'user_id' => 'nullable|exists:users,id',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'activo' => 'required|boolean',
            // --- NUEVOS CAMPOS DE CRÉDITO ---
            'monto_credito' => 'nullable|numeric|min:0',
            'dias_credito' => 'nullable|integer|min:0',
            'fecha_otorgamiento' => 'nullable|date',
            'referencia_bancaria' => 'nullable|string|max:100'
        ]);

        $cliente = Cliente::create($request->all());

        // --- NUEVO: LANZAR ALERTA SI SE ASIGNÓ A UN AGENTE ---
        if ($cliente->user_id) {
            $agente = User::find($cliente->user_id);
            if ($agente) {
                $agente->notify(new AlertaSistema(
                    'Nuevo Cliente Asignado', 
                    'Se te ha asignado a: ' . $cliente->nombre,
                    'fas fa-user-plus', // Ícono
                    'text-success'      // Color verde
                ));
            }
        }

        return redirect()->route('clientes.index')->with('mensaje', 'Cliente creado exitosamente.');
    }

    public function edit(Cliente $cliente)
    {
        if (auth()->user()->hasRole('agente-ventas') && $cliente->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar este cliente.');
        }

        // La nueva consulta (trae agentes y administradores):
        $agentes = User::role(['agente-ventas', 'admin'])->get();
        return view('cliente.action', compact('cliente', 'agentes'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        if (auth()->user()->hasRole('agente-ventas') && $cliente->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para actualizar este cliente.');
        }
        
        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:50|unique:clientes,codigo,' . $cliente->id,
            'email' => 'nullable|email|unique:clientes,email,'.$cliente->id,
            'direccion' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:150',
            'user_id' => 'nullable|exists:users,id',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'activo' => 'required|boolean',
            // --- NUEVOS CAMPOS DE CRÉDITO ---
            'monto_credito' => 'nullable|numeric|min:0',
            'dias_credito' => 'nullable|integer|min:0',
            'fecha_otorgamiento' => 'nullable|date',
            'referencia_bancaria' => 'nullable|string|max:100'
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

    public function exportar(Request $request)
    {
        return Excel::download(new ClientesExport($request->texto), 'clientes.xlsx');
    }
}