<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // Importante para el PDF
use Carbon\Carbon;

class DevolucionController extends Controller
{
    // Listado principal (CON SEGURIDAD POR ROLES)
    public function index(Request $request)
    {
        $user = Auth::user();
        $texto = $request->input('texto');
        
        $query = Pedido::with(['cliente', 'agente'])
            ->where('estado', 'like', 'devolucion_%')
            ->orderBy('created_at', 'desc');

        // SEGURIDAD: Si NO es admin, solo ve sus propios registros
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if (!empty($texto)) {
             $query->where(function($q) use ($texto) {
                 $q->where('id', $texto)
                   ->orWhereHas('cliente', function ($subQ) use ($texto) {
                       $subQ->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                   });
             });
        }

        $registros = $query->paginate(10);
        
        foreach ($registros as $pedido) {
            $pedido->motivo_extraido = $this->extraerDato($pedido->comentarios, 'MOTIVO');
            $pedido->factura_extraida = $this->extraerDato($pedido->comentarios, 'FACTURA');
        }

        return view('devolucion.index', compact('registros', 'texto'));
    }

    // Formulario de creación (CON SEGURIDAD POR AGENTE)
    public function create()
    {
        $user = Auth::user();

        // 1. Iniciamos la consulta de Clientes Activos
        $query = Cliente::with('agente')
                    ->where('activo', true)
                    ->orderBy('nombre');

        // 2. REGLA DE SEGURIDAD:
        // Si NO es admin, forzamos que solo traiga los clientes de este usuario
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $clientes = $query->get();

        // -----------------------------------------------------

        $productos = Producto::orderBy('codigo')->get(); 
        
        $motivos = [
            'Piezas faltantes', 'Metros incompletos', 'Defecto de calidad',
            'Material diferente al solicitado', 'Defecto de empaque', 'Material dañado', 'Otro'
        ];

        return view('devolucion.create', compact('clientes', 'productos', 'motivos'));
    }

    // Guardar (SOLUCIÓN AL ERROR DE NO GUARDADO)
    public function store(Request $request)
    {
        // 1. Validaciones estrictas
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'factura_origen' => 'required|string',
            'motivo_devolucion' => 'required|string',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            // --- LÍMITE DE (3MB) Y PESO PARA LAS FOTOS ---
            'evidencias.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        DB::beginTransaction();
        try {
            $subtotalTotal = 0;
            $detallesParaGuardar = [];

            // --- NUEVO: 1.5 Obtener el porcentaje de descuento del cliente ---
            // Buscamos al cliente en la base de datos para saber cuánto descuento tiene
            $cliente = \App\Models\Cliente::find($request->cliente_id);
            $porcentajeDescuento = $cliente ? ($cliente->descuento / 100) : 0;

            // 2. Procesar productos y calcular totales
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                if(!$producto) continue;

                // --- NUEVO: Aplicar el descuento al precio antes de sumar ---
                $precioBase = $producto->precio; 
                $precioUnitario = $precioBase - ($precioBase * $porcentajeDescuento); // Precio ya rebajado
                
                $subtotalLinea = $precioUnitario * $item['cantidad'];
                $subtotalTotal += $subtotalLinea;

                $detallesParaGuardar[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio' => $precioUnitario,
                    'subtotal' => $subtotalLinea,
                    'inner' => 1,
                    // Usamos una columna virtual si no existe en BD, Laravel la ignorará si no está en $fillable
                    'aplica_iva' => 0 
                ];
            }

            // 3. Preparar Comentarios (Donde guardamos la metadata)
            $comentario  = "[FACTURA: " . $request->factura_origen . "] ";
            $comentario .= "[MOTIVO: " . $request->motivo_devolucion . "] ";
            if($request->comentarios_adicionales) {
                $comentario .= " | Obs: " . $request->comentarios_adicionales;
            }

            // 4. Crear el registro Padre (Pedido tipo devolución)
            $devolucion = Pedido::create([
                'user_id' => Auth::id(), // Usuario que registra (Admin o Agente)
                'cliente_id' => $request->cliente_id,
                'total' => $subtotalTotal,
                'subtotal' => $subtotalTotal,
                'iva' => 0, 
                'descuento_aplicado' => 0,
                'estado' => 'devolucion_pendiente',
                'comentarios' => $comentario
            ]);

            // 5. Guardar Detalles
            foreach ($detallesParaGuardar as $detalle) {
                $detalle['pedido_id'] = $devolucion->id;
                // Intentamos crear solo con los campos seguros
                PedidoDetalle::create([
                    'pedido_id' => $devolucion->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $detalle['precio'],
                    'subtotal' => $detalle['subtotal'],
                    'inner' => 1
                ]);
            }

            // =======================================================
            // 6. GUARDAR FOTOS DE EVIDENCIA (SIN BASE DE DATOS)
            // =======================================================
            if ($request->hasFile('evidencias')) {
                $contador = 1;
                // Definimos dónde se guardarán
                $rutaDestino = public_path('uploads/devoluciones');
                
                // Si la carpeta no existe, la creamos
                if (!file_exists($rutaDestino)) {
                    mkdir($rutaDestino, 0777, true);
                }

                // Recorremos y guardamos cada foto
                foreach ($request->file('evidencias') as $file) {
                    // Nombre: dev_17_1.jpg, dev_17_2.png, etc.
                    $nombreArchivo = 'dev_' . $devolucion->id . '_' . $contador . '.' . $file->getClientOriginalExtension();
                    
                    // Movemos el archivo a la carpeta public/uploads/devoluciones
                    $file->move($rutaDestino, $nombreArchivo);
                    $contador++;
                }
            }
            // =======================================================

            DB::commit();
            return redirect()->route('devoluciones.index')->with('mensaje', 'Devolución guardada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Esto te dirá exactamente por qué falla
            return redirect()->back()->with('error', 'Error crítico: ' . $e->getMessage())->withInput();
        }
    }

    // Ver Detalle
    public function show($id)
    {
        $devolucion = Pedido::with(['cliente.agente', 'detalles.producto'])->findOrFail($id);
        $devolucion->motivo_extraido = $this->extraerDato($devolucion->comentarios, 'MOTIVO');
        $devolucion->factura_extraida = $this->extraerDato($devolucion->comentarios, 'FACTURA');
        
        return view('devolucion.show', compact('devolucion'));
    }

    // Cambiar estado (CON REGLAS DE NEGOCIO Y HUELLA DE RASTREO)
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $devolucion = Pedido::findOrFail($id);

        // Seguridad básica: Verificar propiedad si es agente
        if (!$user->hasRole('admin') && $devolucion->user_id != $user->id) {
            abort(403, 'No autorizado.');
        }

        // LÓGICA DE PERMISOS
        if ($user->hasRole('admin')) {
            // El admin puede poner cualquier estado válido
            $estadosPermitidos = [
                'devolucion_pendiente', 
                'devolucion_aprobada', 
                'devolucion_rechazada', 
                'devolucion_finalizada',
                'devolucion_cancelada' // Nuevo estado para cancelación
            ];
        } else {
            // El agente SOLO puede CANCELAR y solo si está pendiente
            if ($request->nuevo_estado !== 'devolucion_cancelada') {
                return redirect()->back()->with('error', 'Los agentes solo pueden cancelar solicitudes.');
            }
            if ($devolucion->estado !== 'devolucion_pendiente') {
                return redirect()->back()->with('error', 'No se puede cancelar una devolución que ya fue procesada.');
            }
            $estadosPermitidos = ['devolucion_cancelada'];
        }
        
        if(!in_array($request->nuevo_estado, $estadosPermitidos)) {
             return redirect()->back()->with('error', 'Estado no válido o no autorizado.');
        }

        // =======================================================
        // NUEVO: GUARDAR MOTIVO DE RECHAZO SI APLICA
        // =======================================================
        if ($request->nuevo_estado === 'devolucion_rechazada' && $request->has('motivo_rechazo')) {
            $request->validate(['motivo_rechazo' => 'required|string|max:1000'], [
                'motivo_rechazo.required' => 'Debe escribir el motivo del rechazo de esta devolución.'
            ]);
            $devolucion->motivo_rechazo = trim($request->input('motivo_rechazo'));
        }

        // =======================================================
        // NUEVO: GUARDAR LA HUELLA DEL VALIDADOR EN CUALQUIER CAMBIO
        // =======================================================
        $devolucion->validador_id = $user->id; // Guarda SIEMPRE quién hizo el último cambio
        $devolucion->fecha_validacion = Carbon::now(); // Guarda la hora exacta
        $devolucion->estado = $request->nuevo_estado; // Cambia el estado
        
        $devolucion->save();

        return redirect()->back()->with('mensaje', 'Estado de la devolución actualizado correctamente.');
    }

    // AJAX Info Producto
    public function getProductInfo($codigo)
    {
        $producto = Producto::where('codigo', $codigo)->first();
        if ($producto) {
            return response()->json([
                'success' => true,
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio 
            ]);
        }
        return response()->json(['success' => false], 404);
    }

    // ==========================================
    // NUEVO: GENERAR PDF (Formato Excel/Imagen)
    // ==========================================
    public function generarPdf($id)
    {
        $devolucion = Pedido::with(['cliente.agente', 'detalles.producto'])->findOrFail($id);
        
        // Extraer datos ocultos
        $motivo = $this->extraerDato($devolucion->comentarios, 'MOTIVO');
        $factura = $this->extraerDato($devolucion->comentarios, 'FACTURA');

        // Logo
        $logoBase64 = null;
        try {
            $pathLogo = public_path('assets/img/LOGO.png'); // Asegúrate que la ruta sea correcta
            if (file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $data = file_get_contents($pathLogo);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        } catch (\Exception $e) {}

        $data = [
            'dev' => $devolucion,
            'motivo' => $motivo,
            'factura' => $factura,
            'logo' => $logoBase64,
            'fecha' => $devolucion->created_at
        ];

        $pdf = Pdf::loadView('pdf.devolucion', $data);
        $pdf->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'sans-serif']);
        return $pdf->stream('Devolucion-Folio-'.$id.'.pdf');
    }

    private function extraerDato($texto, $key) {
        preg_match('/\[' . $key . ': (.*?)\]/', $texto ?? '', $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }
}