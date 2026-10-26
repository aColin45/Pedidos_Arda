<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoleController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('rol-list'); 
        $texto = $request->input('texto');
        
        $query = Role::with('permissions')->where('name', 'like', "%{$texto}%");

        // BLINDAJE: Si no eres super-admin, no puedes ver que existe el rol super-admin
        if (!auth()->user()->hasRole('super-admin')) {
            $query->where('name', '!=', 'super-admin');
        }

        $registros = $query->orderBy('id', 'desc')->paginate(10);
        return view('role.index', compact('registros','texto'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('rol-create'); 
        $permissions=Permission::all();
        return view('role.action',compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('rol-create'); 
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
        ]);
        $registro = Role::create(['name' => $request->name]);
        $registro->syncPermissions($request->permissions);
        return redirect()->route('roles.index')->with('mensaje', 'Rol '.$registro->name. ' creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('rol-edit'); 
        $registro = Role::findOrFail($id);

        // BLINDAJE: Evitar que un admin intente editar el rol super-admin cambiando el ID en la URL
        if ($registro->name === 'super-admin' && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Acceso Denegado. No tienes permiso para editar el rol principal de Super Administrador.');
        }

        $permissions = Permission::all();        
        return view('role.action', compact('registro', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('rol-edit'); 
        $registro = Role::findOrFail($id);

        // BLINDAJE: Evitar que un admin intente modificar el rol super-admin
        if ($registro->name === 'super-admin' && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Acceso Denegado. No puedes modificar este rol.');
        }

        $request->validate([
            'name' => 'required|unique:roles,name,' . $registro->id,
            'permissions' => 'required|array',
        ]);

        $registro->update(['name' => $request->name]);
        $registro->syncPermissions($request->permissions);
        return redirect()->route('roles.index')->with('mensaje', 'Registro '.$registro->name. '  actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('rol-delete'); 
        $registro = Role::findOrFail($id);

        // BLINDAJE: Evitar que borren el rol super-admin
        if ($registro->name === 'super-admin') {
            abort(403, 'Operación Crítica Cancelada: No se puede eliminar el rol de Super Administrador del sistema.');
        }

        $registro->delete();
        return redirect()->route('roles.index')->with('mensaje', $registro->name. ' eliminado correctamente.');
    }
}
