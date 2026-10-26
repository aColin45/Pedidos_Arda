<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use App\Models\Producto;

class UserController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('user-list'); 
        $texto = $request->input('texto');
        
        $query = User::with('roles')
                    ->where(function($q) use ($texto) {
                        $q->where('name', 'like', "%{$texto}%")
                          ->orWhere('email', 'like', "%{$texto}%");
                    });

        // BLINDAJE: Si el usuario actual NO es super-admin, ocultamos a todos los usuarios super-admin
        if (!auth()->user()->hasRole('super-admin')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super-admin');
            });
        }

        $registros = $query->orderBy('id', 'desc')->paginate(10);
        return view('usuario.index', compact('registros', 'texto'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('user-create'); 
        
        // BLINDAJE: Solo el super-admin puede ver la opción de asignar el rol super-admin
        if (auth()->user()->hasRole('super-admin')) {
            $roles = Role::all();
        } else {
            $roles = Role::where('name', '!=', 'super-admin')->get();
        }
        
        $productosCatalogo = Producto::orderBy('es_especial', 'desc')->orderBy('nombre')->get();
        
        return view('usuario.action', compact('roles', 'productosCatalogo')); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $this->authorize('user-create'); 
        $registro=new User();
        $registro->name=$request->input('name');
        $registro->email=$request->input('email');
        $registro->password=Hash::make($request->input('password'));
        $registro->activo=$request->input('activo');
        
        // --- NUEVO: GUARDAR METAS ---
        if (auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin'])) {
            $registro->meta_mensual_base = $request->input('meta_mensual_base', 0);
            $registro->saldo_meta_acumulado = $request->input('saldo_meta_acumulado', 0);
        }
        
        $registro->save();

        // BLINDAJE FINAL: Prevenir escalada de privilegios
        $rolSolicitado = $request->input('role');
        if ($rolSolicitado === 'super-admin' && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Acceso Denegado. No puedes crear un Super Administrador.');
        }

        $registro->assignRole($request->input('role'));

        // --- NUEVO: ASIGNAR PRODUCTOS ESPECIALES AL CREAR (CON PRECIO E INNER PIVOTE) ---
        if ($request->has('productos_especiales')) {
            $syncData = [];
            foreach ($request->productos_especiales as $producto_id) {
                // Capturamos el precio y el inner que el admin escribió
                $precioEspecial = $request->input("precios_especiales.{$producto_id}");
                $innerEspecial = $request->input("inners_especiales.{$producto_id}");
                
                // Lo guardamos estructurado para la tabla pivote
                $syncData[$producto_id] = [
                    'precio_especial' => $precioEspecial,
                    'inner_especial' => $innerEspecial
                ];
            }
            $registro->productosEspeciales()->sync($syncData);
        }

        return redirect()->route('usuarios.index')->with('mensaje', 'Registro '.$registro->name. '  agregado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->authorize('user-edit'); 
        $registro = User::with('productosEspeciales')->findOrFail($id); 

        // BLINDAJE DE SEGURIDAD: Evitar que un admin intente editar la URL para editar al super-admin
        if ($registro->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'No tienes permiso para editar a un Super Administrador.');
        }
        
        // BLINDAJE: Filtrar roles disponibles
        if (auth()->user()->hasRole('super-admin')) {
            $roles = Role::all();
        } else {
            $roles = Role::where('name', '!=', 'super-admin')->get();
        }
        
        $productosCatalogo = Producto::orderBy('es_especial', 'desc')->orderBy('nombre')->get();
        
        return view('usuario.action', compact('registro','roles', 'productosCatalogo')); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, $id)
    {
        $this->authorize('user-edit'); 
        $registro=User::findOrFail($id);
        $registro->name=$request->input('name');
        $registro->email=$request->input('email');
        if ($request->filled('password')) {
            $registro->password=Hash::make($request->input('password'));
        }
        $registro->activo=$request->input('activo');
        
        // --- NUEVO: GUARDAR METAS ---
        if (auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin'])) {
            $registro->meta_mensual_base = $request->input('meta_mensual_base', 0);
            $registro->saldo_meta_acumulado = $request->input('saldo_meta_acumulado', 0);
        }
        
        $registro->save();

        // BLINDAJE FINAL: Prevenir escalada de privilegios al actualizar
        $rolSolicitado = $request->input('role');
        if ($rolSolicitado === 'super-admin' && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Acceso Denegado. No puedes asignar el rol de Super Administrador.');
        }

        $registro->syncRoles([$request->input('role')]);

        // --- NUEVO: ACTUALIZAR PRODUCTOS ESPECIALES AL EDITAR (CON PRECIO E INNER PIVOTE) ---
        if ($request->has('productos_especiales')) {
            $syncData = [];
            foreach ($request->productos_especiales as $producto_id) {
                // Capturamos el precio y el inner que el admin escribió
                $precioEspecial = $request->input("precios_especiales.{$producto_id}");
                $innerEspecial = $request->input("inners_especiales.{$producto_id}");
                
                // Lo guardamos estructurado para la tabla pivote
                $syncData[$producto_id] = [
                    'precio_especial' => $precioEspecial,
                    'inner_especial' => $innerEspecial
                ];
            }
            $registro->productosEspeciales()->sync($syncData);
        } else {
            $registro->productosEspeciales()->detach(); 
        }

        return redirect()->route('usuarios.index')->with('mensaje', 'Registro '.$registro->name. '  actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
  {
      $this->authorize('user-delete');
      $registro = User::findOrFail($id);

      if ($registro->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
          abort(403, 'No tienes permiso para eliminar a un Super Administrador.');
      }

      $registro->delete();

      return redirect()->route('usuarios.index')->with('mensaje', $registro->name. ' eliminado correctamente.');
  }

  public function toggleStatus(User $usuario){
      $this->authorize('user-activate'); 

      if ($usuario->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
          abort(403, 'No tienes permiso para desactivar a un Super Administrador.');
      }

      $usuario->activo = !$usuario->activo;
      $usuario->save();
      return redirect()->route('usuarios.index')->with('mensaje', 'Estado del usuario actualizado correctamente.');
  }
}