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
        $texto=$request->input('texto');
        $registros=User::with('roles')
                    ->where('name', 'like',"%{$texto}%")
                    ->orWhere('email', 'like',"%{$texto}%")
                    ->orderBy('id', 'desc')
                    ->paginate(10);
        return view('usuario.index', compact('registros','texto'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('user-create'); 
        $roles=Role::all();
        
        // --- NUEVO: MANDAR LOS PRODUCTOS ESPECIALES A LA VISTA PARA USUARIOS NUEVOS ---
        // Ahora mandamos TODOS los productos, para poder asignarles precios especiales a cualquiera
        $productosCatalogo = Producto::orderBy('es_especial', 'desc')->orderBy('nombre')->get();
        
        // Corregido: Se quitó $registro del compact porque en create() aún no existe
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
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin')) {
            $registro->meta_mensual_base = $request->input('meta_mensual_base', 0);
            $registro->saldo_meta_acumulado = $request->input('saldo_meta_acumulado', 0);
        }
        
        $registro->save();

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
        $roles=Role::all();
        $registro=User::with('productosEspeciales')->findOrFail($id); 
        
        // --- NUEVO: MANDAR LOS PRODUCTOS ESPECIALES A LA VISTA ---
        // Ahora mandamos TODOS los productos, para poder asignarles precios especiales a cualquiera
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
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin')) {
            $registro->meta_mensual_base = $request->input('meta_mensual_base', 0);
            $registro->saldo_meta_acumulado = $request->input('saldo_meta_acumulado', 0);
        }
        
        $registro->save();

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
        $registro=User::findOrFail($id);
        $registro->delete();

        return redirect()->route('usuarios.index')->with('mensaje', $registro->name. ' eliminado correctamente.');
    }

    public function toggleStatus(User $usuario){
        $this->authorize('user-activate'); 
        $usuario->activo=!$usuario->activo;
        $usuario->save();
        return redirect()->route('usuarios.index')->with('mensaje', 'Estado del usuario actualizado correctamente.');
    }
}