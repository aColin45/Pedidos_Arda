<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Almacen;
use App\Models\Producto;
use App\Models\RestriccionInventario;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InventarioImport;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        // 1. LÓGICA DE ALMACENES
        if (auth()->user()->hasPermissionTo('inventario-import') || auth()->user()->hasPermissionTo('inventario-edit')) {
            $almacenes = Almacen::where('activo', 1)->get();
        } else {
            $almacenes = Almacen::where('activo', 1)->whereHas('productos', function($q) {
                $q->where('almacen_producto.cantidad', '>', 0);
            })->get();
        }
        
        // 2. Traemos las restricciones de la base de datos
        // Usamos una consulta general para poder usarlas en el filtro visual, 
        // pero la variable $restricciones_panel solo se manda si tiene permiso de ver el panel
        $todasLasRestricciones = RestriccionInventario::pluck('palabra')->toArray();
        $restricciones = auth()->user()->hasPermissionTo('inventario-config') 
                         ? RestriccionInventario::all() : collect([]);

        // 3. CONSULTA BASE CON SUBCONSULTA
        $query = Producto::with('almacenes')
            ->select('productos.*')
            ->selectRaw('COALESCE((SELECT SUM(cantidad) FROM almacen_producto WHERE almacen_producto.producto_id = productos.id), 0) as stock_calculado');

        // 4. NUEVO: OCULTAR PRODUCTOS RESTRINGIDOS DE LA TABLA
        if (!empty($todasLasRestricciones)) {
            $query->where(function ($q) use ($todasLasRestricciones) {
                foreach ($todasLasRestricciones as $palabra) {
                    // Ocultamos si el nombre o el código contienen la palabra
                    $q->where('productos.codigo', 'NOT LIKE', '%' . $palabra . '%')
                      ->where('productos.nombre', 'NOT LIKE', '%' . $palabra . '%');
                }
            });
        }

        // 5. Filtro por Búsqueda de Texto
        if($request->has('buscar') && $request->buscar != '') {
            $query->where(function($q) use ($request) {
                $q->where('productos.codigo', 'like', '%'.$request->buscar.'%')
                  ->orWhere('productos.nombre', 'like', '%'.$request->buscar.'%');
            });
        }

        // 6. Filtro por Almacén Específico
        if($request->has('filtro_almacen') && $request->filtro_almacen != '') {
            $query->whereHas('almacenes', function($q) use ($request) {
                $q->where('almacenes.id', $request->filtro_almacen)
                  ->where('almacen_producto.cantidad', '>', 0);
            });
        }

        // 7. ORDENAMIENTO 
        if($request->has('orden') && $request->orden != '') {
            if($request->orden == 'mayor') {
                $query->orderBy('stock_calculado', 'DESC');
            } elseif($request->orden == 'menor') {
                $query->orderBy('stock_calculado', 'ASC');
            }
        } else {
            // Orden por defecto
            $query->orderBy('productos.codigo', 'ASC');
        }

        // 8. Paginación
        $productos = $query->paginate(20)->withQueryString();

        return view('inventario.index', compact('almacenes', 'productos', 'restricciones'));
    }

    public function importar(Request $request)
    {
        $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'archivo_excel' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $almacenId = $request->almacen_id;
            
            $coleccion = Excel::toCollection(new InventarioImport, $request->file('archivo_excel'));
            $filas = $coleccion[0]; 
            
            // TRAEMOS LAS PALABRAS RESTRINGIDAS EN UN ARRAY
            $palabrasRestringidas = RestriccionInventario::pluck('palabra')->map(function($item){
                return mb_strtoupper($item); // Convertimos todo a mayúsculas para comparar fácil
            })->toArray();

            $actualizados = 0;
            $noEncontrados = 0;
            $bloqueados = 0; // Contador de productos ignorados

            DB::beginTransaction();

            foreach ($filas as $index => $fila) {
                if ($index == 0) continue;

                $clave = $fila[1] ?? null;
                $nombreProductoExcel = $fila[3] ?? ''; // Asumiendo que en la columna 3 del Excel viene el nombre.
                $cantPresente = $fila[4] ?? 0;

                if ($clave) {
                    
                    // VALIDACIÓN DE RESTRICCIONES (La magia ocurre aquí)
                    $esRestringido = false;
                    foreach($palabrasRestringidas as $palabraMal) {
                        if (str_contains(mb_strtoupper($nombreProductoExcel), $palabraMal) || str_contains(mb_strtoupper($clave), $palabraMal)) {
                            $esRestringido = true;
                            break;
                        }
                    }

                    if ($esRestringido) {
                        $bloqueados++;
                        continue; // Saltamos a la siguiente fila del Excel, ignorando este producto
                    }

                    // Si pasó el filtro, procedemos normal
                    $producto = Producto::where('codigo', $clave)->first();
                    
                    if ($producto) {
                        $producto->almacenes()->syncWithoutDetaching([
                            $almacenId => ['cantidad' => $cantPresente]
                        ]);
                        $actualizados++;
                    } else {
                        $noEncontrados++;
                    }
                }
            }

            DB::commit();
            
            $mensajeExtra = $bloqueados > 0 ? " Se ignoraron $bloqueados productos restringidos." : "";
            return back()->with('mensaje', "¡Sincronización exitosa! Se actualizaron $actualizados productos.$mensajeExtra (Claves omitidas/no encontradas: $noEncontrados)");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el archivo. Asegúrese de subir el formato correcto.');
        }
    }

    public function actualizarManual(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'cantidad' => 'required|numeric'
        ]);

        $producto = Producto::findOrFail($request->producto_id);
        $producto->almacenes()->syncWithoutDetaching([
            $request->almacen_id => ['cantidad' => $request->cantidad]
        ]);
        
        return back()->with('mensaje', 'El stock de ' . $producto->codigo . ' ha sido ajustado manualmente.');
    }

    public function vaciar(Request $request)
    {
        $request->validate([
            'almacen_id_vaciar' => 'required|exists:almacenes,id'
        ]);

        $almacen = Almacen::findOrFail($request->almacen_id_vaciar);
        DB::table('almacen_producto')->where('almacen_id', $almacen->id)->delete();
        
        return back()->with('mensaje', '¡Reversión exitosa! El inventario del almacén "'.$almacen->nombre.'" ha sido vaciado por completo.');
    }

    // ==========================================
    // NUEVAS RUTAS PARA GESTIONAR RESTRICCIONES
    // ==========================================
    public function agregarRestriccion(Request $request)
    {
        $this->authorize('inventario-config');
        $request->validate(['palabra' => 'required|string|unique:restricciones_inventario,palabra']);
        
        RestriccionInventario::create(['palabra' => mb_strtoupper($request->palabra)]);
        return back()->with('mensaje', 'Palabra restringida agregada correctamente.');
    }

    public function eliminarRestriccion($id)
    {
        $this->authorize('inventario-config');
        $restriccion = RestriccionInventario::findOrFail($id);
        $restriccion->delete();
        
        return back()->with('mensaje', 'Restricción eliminada.');
    }
}