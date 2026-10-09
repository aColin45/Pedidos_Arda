<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Usar Request general
use App\Models\Producto;
// use App\Http\Requests\ProductoRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; // Importar File para manejo de archivos
use App\Models\User;
use App\Notifications\AlertaSistema;
use Illuminate\Support\Facades\Notification;

class ProductoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // Tu autorización actual (si la tienes configurada así)
        $this->authorize('producto-list'); 
        
        $texto = $request->input('texto');
        
        // Iniciamos la consulta
        $query = Producto::query();

        // =========================================================
        // CANDADO DE SEGURIDAD PARA EL PANEL DE ADMINISTRACIÓN
        // =========================================================
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('superadmin')) {
            $agenteId = auth()->id();
            
            // Si es un agente de ventas, solo carga los normales (0) + los especiales que tiene asignados
            $query->where(function($q) use ($agenteId) {
                $q->where('es_especial', 0)
                  ->orWhereHas('usuariosPermitidos', function($subQ) use ($agenteId) {
                      $subQ->where('user_id', $agenteId);
                  });
            });
        }
        // =========================================================

        // Tu lógica de búsqueda original (ajusta los campos si usas otros diferentes)
        if (!empty($texto)) {
            $query->where(function($q) use ($texto) {
                $q->where('nombre', 'like', "%{$texto}%")
                  ->orWhere('codigo', 'like', "%{$texto}%");
            });
        }

        // Ordenamos y paginamos (el 10 u otro número que ya tuvieras)
        $registros = $query->orderBy('id', 'desc')->paginate(10);
        
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
        
        return view('producto.index', compact('registros', 'texto', 'preciosEspeciales', 'innersEspeciales'));
    }

    public function create()
    {
        $this->authorize('producto-create');
        // Pasamos una variable 'registro' vacía para consistencia con el form
        $registro = new Producto([
            'aplica_iva' => true,
            'inner' => 1
        ]);
        return view('producto.action', compact('registro'));
    }

    public function store(Request $request)
    {
        $this->authorize('producto-create');

        // VALIDACIÓN DIRECTA EN EL CONTROLADOR
        $validatedData = $request->validate([
            'codigo' => 'required|string|max:255|unique:productos,codigo',
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'aplica_iva' => 'required|boolean',
            'descripcion' => 'nullable|string',
            'especificaciones' => 'nullable|string', 
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'inner' => 'required|integer|min:1',
            // Agregamos la validación opcional para el checkbox
            'es_especial' => 'nullable|boolean',
        ]);

        $registro = new Producto();
        $registro->codigo = $request->input('codigo');
        $registro->nombre = $request->input('nombre');
        $registro->precio = $request->input('precio');
        $registro->aplica_iva = $request->input('aplica_iva');
        $registro->descripcion = $request->input('descripcion');
        $registro->especificaciones = $request->input('especificaciones');
        $registro->inner = $request->input('inner');
        
        // --- NUEVO: ASIGNAR SI ES ESPECIAL ---
        $registro->es_especial = $request->has('es_especial') ? 1 : 0;

        // Manejo de Imagen
        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $sufijo = strtolower(Str::random(2));
            $nombreImagen = $sufijo . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/productos'), $nombreImagen);
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

    public function update(Request $request, $id)
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
            'especificaciones' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'inner' => 'required|integer|min:1',
            // Agregamos la validación opcional para el checkbox
            'es_especial' => 'nullable|boolean',
        ]);

        // === 1. GUARDAMOS EL PRECIO ANTERIOR ANTES DE REASIGNARLO ===
        $precioAnterior = $registro->precio;

        // Asignar valores desde el request
        $registro->codigo = $request->input('codigo');
        $registro->nombre = $request->input('nombre');
        $registro->precio = $request->input('precio');
        $registro->aplica_iva = $request->input('aplica_iva');
        $registro->descripcion = $request->input('descripcion');
        $registro->especificaciones = $request->input('especificaciones');
        $registro->inner = $request->input('inner');
        
        // --- NUEVO: ASIGNAR SI ES ESPECIAL ---
        $registro->es_especial = $request->has('es_especial') ? 1 : 0;

        // Manejo de Imagen
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

        // === 2. VERIFICAMOS SI EL PRECIO CAMBIÓ Y LANZAMOS LA ALERTA ===
        if ($precioAnterior != $registro->precio) {
            // Obtenemos a todos los agentes de ventas
            $agentes = User::role('agente-ventas')->get();
            
            // Enviamos la notificación
            Notification::send($agentes, new AlertaSistema(
                'Actualización de Precio', 
                "El producto '{$registro->codigo} - {$registro->nombre}' ha cambiado su precio a $" . number_format($registro->precio, 2),
                'fas fa-tags',       // Icono de etiqueta
                'text-warning'       // Color de la alerta
            ));
        }

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

    public function exportar(Request $request) {
        // 1. Capturamos el texto del buscador si es que el usuario filtró algo en la tabla
        $texto = $request->get('texto');

        // 2. Construimos la misma consulta que usa tu vista index
        $query = \App\Models\Producto::query();

        if (!empty($texto)) {
            $query->where('codigo', 'like', "%{$texto}%")
                  ->orWhere('nombre', 'like', "%{$texto}%");
        }

        $productos = $query->orderBy('id', 'desc')->get();

        // 3. Nombre del archivo descargable
        $fileName = 'Catalogo_Productos_ARDA_' . date('d-m-Y') . '.xls';

        $headers = [
            "Content-type"        => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($productos, $texto) {
            if (ob_get_level() > 0) {
                ob_clean();
            }

            // Estructura XML nativa de Excel
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

            // Estilos de diseño (Mismo color azul corporativo)
            echo ' <Styles>' . "\n";
            echo '  <Style ss:ID="Default" ss:Name="Normal">' . "\n";
            echo '   <Alignment ss:Vertical="Center"/>' . "\n";
            echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="Titulo">' . "\n";
            echo '   <Font ss:FontName="Calibri" ss:Size="16" ss:Bold="1" ss:Color="#1F497D"/>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="Subtitulo">' . "\n";
            echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Italic="1" ss:Color="#595959"/>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="Header">' . "\n";
            echo '   <Interior ss:Color="#1F497D" ss:Pattern="Solid"/>' . "\n";
            echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
            echo '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
            echo '   <Borders>' . "\n";
            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFBFBF"/>' . "\n";
            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFBFBF"/>' . "\n";
            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFBFBF"/>' . "\n";
            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFBFBF"/>' . "\n";
            echo '   </Borders>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="CeldaNormal">' . "\n";
            echo '   <Alignment ss:Vertical="Center"/>' . "\n";
            echo '   <Borders>' . "\n";
            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '   </Borders>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="CeldaZebra">' . "\n";
            echo '   <Interior ss:Color="#F2F5F8" ss:Pattern="Solid"/>' . "\n";
            echo '   <Alignment ss:Vertical="Center"/>' . "\n";
            echo '   <Borders>' . "\n";
            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '   </Borders>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="Precio">' . "\n";
            echo '   <NumberFormat ss:Format="$#,##0.00"/>' . "\n";
            echo '   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>' . "\n";
            echo '   <Borders>' . "\n";
            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '   </Borders>' . "\n";
            echo '  </Style>' . "\n";
            echo ' </Styles>' . "\n";

            // Creación de la pestaña única
            echo ' <Worksheet ss:Name="Productos ARDA">' . "\n";
            echo '  <Table>' . "\n";
            echo '   <Column ss:Width="50"/>' . "\n";  // ID
            echo '   <Column ss:Width="120"/>' . "\n"; // Código
            echo '   <Column ss:Width="300"/>' . "\n"; // Nombre
            echo '   <Column ss:Width="100"/>' . "\n"; // Precio
            echo '   <Column ss:Width="80"/>' . "\n";  // Inner

            echo '   <Row ss:Height="25"><Cell ss:StyleID="Titulo"><Data ss:Type="String">GRUPO INDUSTRIAL ARDA S.A. DE C.V.</Data></Cell></Row>' . "\n";
            
            $subTexto = !empty($texto) ? "Catálogo filtrado por búsqueda: '" . $texto . "'" : "Catálogo completo de productos";
            echo '   <Row ss:Height="20"><Cell ss:StyleID="Subtitulo"><Data ss:Type="String">' . htmlspecialchars($subTexto, ENT_QUOTES, 'UTF-8') . '</Data></Cell></Row>' . "\n";
            echo '   <Row/>' . "\n";

            // Encabezados
            echo '   <Row ss:Height="25">' . "\n";
            echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">ID</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Código</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Nombre del Producto</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Precio</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Inner</Data></Cell>' . "\n";
            echo '   </Row>' . "\n";

            // Volcado de datos de los productos
            foreach ($productos as $index => $reg) {
                $style = ($index % 2 == 0) ? 'CeldaNormal' : 'CeldaZebra';

                echo '   <Row ss:Height="22">' . "\n";
                echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="Number">'.$reg->id.'</Data></Cell>' . "\n";
                echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($reg->codigo, ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($reg->nombre, ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                
                // Formateamos como número para que la celda de tipo Precio aplique la moneda perfectamente
                echo '    <Cell ss:StyleID="Precio"><Data ss:Type="Number">'.$reg->precio.'</Data></Cell>' . "\n";
                echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="Number">'.($reg->inner ?? 1).'</Data></Cell>' . "\n";
                echo '   </Row>' . "\n";
            }

            echo '  </Table>' . "\n";
            echo ' </Worksheet>' . "\n";
            echo '</Workbook>' . "\n";
            exit();
        };

        return response()->stream($callback, 200, $headers);
    }
}