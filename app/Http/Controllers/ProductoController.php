<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Usar Request general
use App\Models\Producto;
// use App\Http\Requests\ProductoRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; // Importar File para manejo de archivos

class ProductoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('producto-list');
        $texto=$request->input('texto');
        $query=Producto::query(); // Iniciar query

        if ($texto) {
            $query->where('nombre', 'like',"%{$texto}%")
                  ->orWhere('codigo', 'like',"%{$texto}%");
        }

        $registros = $query->orderBy('id', 'desc')->paginate(10);

        return view('producto.index', compact('registros','texto'));
    }

    public function create()
    {
        $this->authorize('producto-create');
        // Pasamos una variable 'registro' vacía para consistencia con el form
        // con los valores por defecto
        $registro = new Producto([
            'aplica_iva' => true,
            'inner' => 1
        ]);
        return view('producto.action', compact('registro'));
    }

    public function store(Request $request) // Cambiado a Request general
    {
        $this->authorize('producto-create');

        // VALIDACIÓN DIRECTA EN EL CONTROLADOR
        $validatedData = $request->validate([
            'codigo' => 'required|string|max:255|unique:productos,codigo',
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'aplica_iva' => 'required|boolean',
            'descripcion' => 'nullable|string',
            'especificaciones' => 'nullable|string', // <-- ¡NUEVA VALIDACIÓN!
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'inner' => 'required|integer|min:1',
        ]);

        $registro = new Producto();
        $registro->codigo = $request->input('codigo'); // Usar $request->input para coincidir con tu estilo
        $registro->nombre = $request->input('nombre');
        $registro->precio = $request->input('precio');
        $registro->aplica_iva = $request->input('aplica_iva'); // Se obtiene 0 o 1 del form
        $registro->descripcion = $request->input('descripcion');
        $registro->especificaciones = $request->input('especificaciones'); // <-- ¡NUEVO CAMPO!
        $registro->inner = $request->input('inner');

        // Manejo de Imagen
        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $sufijo = strtolower(Str::random(2));
            $nombreImagen = $sufijo . '-' . time() . '.' . $image->getClientOriginalExtension(); // Nombre único
            $image->move(public_path('uploads/productos'), $nombreImagen); // Usar public_path()
            $registro->imagen = $nombreImagen;
        }

        $registro->save();
        return redirect()->route('productos.index')->with('mensaje', 'Producto '.$registro->nombre. ' agregado correctamente');
    }

    public function show(string $id)
    {
        // Redirigir a edit
        return redirect()->route('productos.edit', $id);
    }

    public function edit(string $id)
    {
        $this->authorize('producto-edit');
        $registro=Producto::findOrFail($id);
        return view('producto.action', compact('registro'));
    }

    public function update(Request $request, $id) // Cambiado a Request general
    {
        $this->authorize('producto-edit');
        $registro=Producto::findOrFail($id);

        // VALIDACIÓN DIRECTA EN EL CONTROLADOR
        $validatedData = $request->validate([
             // Ignorar ID actual en unique
            'codigo' => 'required|string|max:255|unique:productos,codigo,' . $registro->id,
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'aplica_iva' => 'required|boolean',
            'descripcion' => 'nullable|string',
            'especificaciones' => 'nullable|string', // <-- ¡NUEVA VALIDACIÓN!
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'inner' => 'required|integer|min:1',
        ]);

        // Asignar valores desde el request
        $registro->codigo = $request->input('codigo');
        $registro->nombre = $request->input('nombre');
        $registro->precio = $request->input('precio');
        $registro->aplica_iva = $request->input('aplica_iva');
        $registro->descripcion = $request->input('descripcion');
        $registro->especificaciones = $request->input('especificaciones'); // <-- ¡NUEVO CAMPO!
        $registro->inner = $request->input('inner');

        // Manejo de Imagen (con borrado de la antigua si se sube una nueva)
        if ($request->hasFile('imagen')) {
            // Borrar imagen antigua si existe
            $old_image_path = public_path('uploads/productos/' . $registro->imagen);
            if ($registro->imagen && File::exists($old_image_path)) {
                 File::delete($old_image_path);
            }

            // Subir nueva imagen
            $image = $request->file('imagen');
            $sufijo = strtolower(Str::random(2));
            $nombreImagen = $sufijo . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/productos'), $nombreImagen);
            $registro->imagen = $nombreImagen;
        }

        $registro->save();

        return redirect()->route('productos.index')->with('mensaje', 'Producto '.$registro->nombre. ' actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $this->authorize('producto-delete');
        $registro=Producto::findOrFail($id);

        // Borrar imagen si existe antes de eliminar el registro
        $old_image_path = public_path('uploads/productos/' . $registro->imagen);
        if ($registro->imagen && File::exists($old_image_path)) {
             File::delete($old_image_path);
        }

        $registro->delete();
        return redirect()->route('productos.index')->with('mensaje', 'Producto '.$registro->nombre. ' eliminado correctamente.');
    }
}