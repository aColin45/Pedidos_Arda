<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AltaCliente;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\HistorialAlta;
use App\Models\AltaReferencia;

class AltaClienteController extends Controller
{
    // Mostrar listado general de altas
    public function index(Request $request){
    $texto = $request->input('texto');
    $user = auth()->user();

    $query = AltaCliente::with('agente')->orderBy('id', 'desc'); // Ajusta tu modelo

    if (!$user->hasRole('admin')) {
        $query->where('user_id', $user->id);
    }

    // APLICAR EL FILTRO DE BÚSQUEDA
    if (!empty($texto)) {
        $query->where(function($q) use ($texto) {
            $q->where('id', 'like', "%{$texto}%") // Para el Folio
              ->orWhere('razon_social', 'like', "%{$texto}%")
              ->orWhere('rfc', 'like', "%{$texto}%")
              // Eliminamos la línea de 'estado' para evitar el crash de SQL
              ->orWhereHas('agente', function($q2) use ($texto) {
                  $q2->where('name', 'like', "%{$texto}%");
              });
        });
    }

    $altas = $query->paginate(10);
    return view('altas.index', compact('altas', 'texto'));
    }

    // Mostrar el formulario en blanco para el agente
    public function create(Request $request)
    {
        $tipo = $request->input('tipo', 'credito'); // Por defecto crédito
        return view('altas.create', compact('tipo'));
    }

    // Guardar los datos de texto (Paso 1 del Agente)
    public function store(Request $request)
    {
        $tipo = $request->input('tipo_alta', 'credito');

        // REGLAS BÁSICAS (Aplicables a todos: Contado y Crédito)
        $rules = [
            'razon_social' => 'required|string|max:255',
            'email_pagos' => 'required|email|max:255', // El correo que pediste obligatorio
        ];

        // REGLAS ESTRICTAS (Solo si es Crédito)
        if ($tipo === 'credito') {
            $rules['rfc'] = 'required|string|max:20';
            $rules['referencias'] = 'required|array|size:5';
            $rules['referencias.*.nombre'] = 'required|string|max:255';
            $rules['referencias.*.correo'] = 'required|email|max:255';
            $rules['referencias.*.telefono'] = 'required|string|max:50';
        }

        $request->validate($rules, [
            'referencias.*.nombre.required' => 'El nombre de la referencia comercial es obligatorio.',
            'referencias.*.correo.required' => 'El correo de la referencia comercial es obligatorio.',
            'referencias.*.telefono.required' => 'El teléfono de la referencia comercial es obligatorio.',
        ]);

        $alta = AltaCliente::create([
            'user_id' => Auth::id(),
            'tipo_alta' => $tipo, // --- NUEVO: GUARDAMOS EL TIPO DE ALTA ---
            
            // Forzamos mayúsculas
            'razon_social' => mb_strtoupper($request->razon_social),
            'rfc' => mb_strtoupper($request->rfc ?? ''),
            'regimen_fiscal' => mb_strtoupper($request->regimen_fiscal ?? ''),
            'uso_cfdi' => mb_strtoupper($request->uso_cfdi ?? ''),
            'contacto_pagos' => mb_strtoupper($request->contacto_pagos ?? ''),
            
            // Los correos SIEMPRE van en minúsculas
            'email_pagos' => strtolower($request->email_pagos),
            'telefono_pagos' => $request->telefono_pagos, 
            
            // Domicilios
            'calle_fiscal' => mb_strtoupper($request->calle_fiscal ?? ''),
            'colonia_fiscal' => mb_strtoupper($request->colonia_fiscal ?? ''),
            'municipio_fiscal' => mb_strtoupper($request->municipio_fiscal ?? ''),
            'estado_fiscal' => mb_strtoupper($request->estado_fiscal ?? ''),
            'cp_fiscal' => $request->cp_fiscal,
            'telefono_fiscal' => $request->telefono_fiscal,
            'email1_fiscal' => strtolower($request->email1_fiscal ?? ''),
            'email2_fiscal' => strtolower($request->email2_fiscal ?? ''),
            
            'persona_recibe' => mb_strtoupper($request->persona_recibe ?? ''),
            'calle_entrega' => mb_strtoupper($request->calle_entrega ?? ''),
            'colonia_entrega' => mb_strtoupper($request->colonia_entrega ?? ''),
            'municipio_entrega' => mb_strtoupper($request->municipio_entrega ?? ''),
            'estado_entrega' => mb_strtoupper($request->estado_entrega ?? ''),
            'cp_entrega' => $request->cp_entrega,
            
            'estado_alta' => 'borrador'
        ]);

        // --- SOLO GUARDAR REFERENCIAS SI ES CRÉDITO ---
        if ($tipo === 'credito' && $request->has('referencias')) {
            $referenciasFormateadas = array_map(function($ref) {
                return [
                    'nombre' => mb_strtoupper($ref['nombre']),
                    'correo' => strtolower($ref['correo']),
                    'telefono' => $ref['telefono'] 
                ];
            }, $request->referencias);

            foreach ($referenciasFormateadas as $ref) {
                $alta->referencias()->create([
                    'nombre' => $ref['nombre'],
                    'correo' => $ref['correo'],
                    'telefono' => $ref['telefono'],
                    'estado_referencia' => 'pendiente'
                ]);
            }
        }

        return redirect()->route('altas.show', $alta->id)->with('mensaje', 'Datos generales guardados. Ahora proceda a subir los documentos requeridos.');
    }

    // Mostrar el Expediente (Subida de archivos)
    public function show($id)
    {
        // Añadimos 'referencias' a la carga
        $alta = AltaCliente::with(['documentos', 'referencias'])->findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($alta->user_id != Auth::id()) abort(403, 'Acceso no autorizado.');
        }

        $requisitos = [
            'constancia_fiscal' => 'Constancia de Situación Fiscal (Actualizada)',
            'ine_cliente' => 'Identificación Oficial Cliente (Ambos lados)',
            'comprobante_domicilio' => 'Comprobante de Domicilio (Menor a 3 meses)',
            'opinion_cumplimiento' => 'Opinión de Cumplimiento (Actualizada)',
            'acta_constitutiva' => 'Acta Constitutiva (Carátula, Socios, Capital, Apoderado)',
            'poder_notarial' => 'Poder Notarial (Si no está en el Acta)',
            'ine_representante' => 'INE/Pasaporte Representante Legal',
            'formato_firmado' => 'Formato de Alta Firmado'
        ];

        $documentosSubidos = $alta->documentos->pluck('estado_documento', 'tipo_documento')->toArray();
        $archivosListos = $alta->documentos->whereIn('estado_documento', ['pendiente', 'aprobado'])->count();

        return view('altas.show', compact('alta', 'requisitos', 'documentosSubidos', 'archivosListos'));
    }

    public function testRapido()
    {
        $alta = AltaCliente::create([
            'user_id' => Auth::id() ?? 1,
            'razon_social' => 'EMPRESA DE PRUEBAS RÁPIDAS S.A. DE C.V.',
            'rfc' => 'PRUE999999XXX',
            'regimen_fiscal' => 'General de Ley Personas Morales',
            'uso_cfdi' => 'G03 Gastos en General',
            'contacto_pagos' => 'Juan Pérez Prueba',
            'email_pagos' => 'pagos@prueba.com',
            'telefono_pagos' => '722 123 4567 Ext 12',
            'calle_fiscal' => 'Avenida Falsa 123',
            'colonia_fiscal' => 'Colonia Inventada',
            'municipio_fiscal' => 'Toluca',
            'estado_fiscal' => 'Estado de México',
            'cp_fiscal' => '50000',
            'referencias_comerciales' => [
                ['nombre' => 'Proveedor Falso 1', 'correo' => 'p1@test.com', 'telefono' => '1111111111'],
                ['nombre' => 'Proveedor Falso 2', 'correo' => 'p2@test.com', 'telefono' => '2222222222'],
                ['nombre' => 'Proveedor Falso 3', 'correo' => 'p3@test.com', 'telefono' => '3333333333'],
                ['nombre' => 'Proveedor Falso 4', 'correo' => 'p4@test.com', 'telefono' => '4444444444'],
                ['nombre' => 'Proveedor Falso 5', 'correo' => 'p5@test.com', 'telefono' => '5555555555'],
            ],
            'estado_alta' => 'borrador'
        ]);

        return redirect()->route('altas.show', $alta->id)->with('mensaje', '¡Bypass exitoso! Alta de prueba generada en 1 segundo.');
    }

    // Subir un documento individual
    public function uploadDocumento(Request $request, $id)
    {
        $request->validate([
            'tipo_documento' => 'required|string',
            'archivo' => 'required|file|max:5120', 
        ]);

        $alta = AltaCliente::findOrFail($id);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $fileName = $alta->id . '_' . $request->tipo_documento . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/altas'), $fileName);

            $documento = $alta->documentos()->where('tipo_documento', $request->tipo_documento)->first();
            
            if ($documento) {
                $documento->update([
                    'archivo_ruta' => 'uploads/altas/' . $fileName,
                    'estado_documento' => 'pendiente', 
                    'motivo_rechazo' => null
                ]);
            } else {
                $alta->documentos()->create([
                    'tipo_documento' => $request->tipo_documento,
                    'archivo_ruta' => 'uploads/altas/' . $fileName,
                    'estado_documento' => 'pendiente'
                ]);
            }
        }

        return redirect()->back()->with('mensaje', 'Documento subido correctamente.');
    }

    // LÓGICA MODIFICADA: Enviar a Revisión (Verificando SOLO obligatorios según el tipo)
    public function enviarRevision($id)
    {
        $alta = AltaCliente::findOrFail($id);
        
        // CONDICIONAMOS LOS DOCUMENTOS OBLIGATORIOS
        if ($alta->tipo_alta == 'contado') {
            // De contado exigimos SOLO 1: Constancia Fiscal
            $obligatorios = ['constancia_fiscal'];
        } else {
            // De crédito exigimos los 4 estándar
            $obligatorios = [
                'constancia_fiscal', 
                'ine_cliente', 
                'comprobante_domicilio', 
                'formato_firmado'
            ];
        }
        
        $subidosObligatorios = $alta->documentos()->whereIn('tipo_documento', $obligatorios)->count();

        if($subidosObligatorios < count($obligatorios)) {
            return redirect()->back()->withErrors('Faltan documentos obligatorios por subir. Por favor complete los requeridos.');
        }

        $alta->estado_alta = 'en_revision';
        $alta->save();

        // --- REGISTRAR HISTORIAL ---
        HistorialAlta::create([
            'alta_cliente_id' => $alta->id,
            'user_id' => Auth::id(),
            'accion' => 'Enviado a Revisión',
            'detalles' => 'El agente envió los documentos obligatorios para auditoría.'
        ]);

        return redirect()->route('altas.show', $alta->id)->with('mensaje', '¡Alta enviada a validación exitosamente! El administrador revisará los documentos.');
    }

    // =========================================================
    // Generar el Formato PDF en Blanco
    // =========================================================
    public function pdfEnBlanco()
    {
        // Instancia en memoria, NO se guarda en la base de datos
        $alta = new \App\Models\AltaCliente();
        
        // Le inyectamos el agente actual logueado
        $alta->agente = Auth::user();
        
        // Llenamos con espacios en blanco para que la vista del PDF no marque error
        $alta->id = '_____'; // Línea para el folio
        $alta->created_at = \Carbon\Carbon::now();
        $alta->razon_social = '';
        $alta->rfc = '';
        $alta->regimen_fiscal = '';
        $alta->uso_cfdi = '';
        $alta->contacto_pagos = '';
        $alta->email_pagos = '';
        $alta->telefono_pagos = '';
        $alta->calle_fiscal = '';
        $alta->colonia_fiscal = '';
        $alta->municipio_fiscal = '';
        $alta->estado_fiscal = '';
        $alta->cp_fiscal = '';
        $alta->telefono_fiscal = '';
        $alta->email1_fiscal = '';
        $alta->email2_fiscal = '';
        $alta->persona_recibe = '';
        $alta->calle_entrega = '';
        $alta->colonia_entrega = '';
        $alta->municipio_entrega = '';
        $alta->estado_entrega = '';
        $alta->cp_entrega = '';
        
        // 5 Referencias vacías
        $alta->referencias_comerciales = [
            ['nombre' => '', 'correo' => '', 'telefono' => ''],
            ['nombre' => '', 'correo' => '', 'telefono' => ''],
            ['nombre' => '', 'correo' => '', 'telefono' => ''],
            ['nombre' => '', 'correo' => '', 'telefono' => ''],
            ['nombre' => '', 'correo' => '', 'telefono' => ''],
        ];

        // Logo
        $logoBase64 = null; 
        $rutaLogo = public_path('assets/img/LOGO.png');
        if (file_exists($rutaLogo)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($rutaLogo));
        }

        $pdf = Pdf::loadView('pdf.alta_cliente', compact('alta', 'logoBase64'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Formato_Alta_Cliente_Blanco.pdf');
    }

    public function pdf($id)
    {
        $alta = AltaCliente::with('agente')->findOrFail($id);

        $logoBase64 = null; 
        $rutaLogo = public_path('assets/img/LOGO.png');
        if (file_exists($rutaLogo)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($rutaLogo));
        }

        $pdf = Pdf::loadView('pdf.alta_cliente', compact('alta', 'logoBase64'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Formato_Alta_Cliente_' . $alta->id . '.pdf');
    }

    // LÓGICA MODIFICADA: El Admin evalúa un documento (Aprobar / Rechazar)
    public function evaluarDocumento(Request $request, $alta_id, $doc_id)
    {
        $request->validate([
            'estado' => 'required|in:aprobado,rechazado',
            'motivo' => 'nullable|string|max:500'
        ]);

        $documento = \App\Models\AltaDocumento::where('alta_id', $alta_id)->findOrFail($doc_id);
        $alta = AltaCliente::findOrFail($alta_id);

        $documento->estado_documento = $request->estado;
        $documento->motivo_rechazo = ($request->estado === 'rechazado') ? $request->motivo : null;
        $documento->save();

        // --- REEMPLAZO INTELIGENTE: LLAMADA A LA NUEVA FUNCIÓN DE TRAZABILIDAD GENERAL ---
        $this->actualizarEstadoGeneralAlta($alta);

        // --- REGISTRAR HISTORIAL ---
        $nombreDoc = ucwords(str_replace('_', ' ', $documento->tipo_documento));
        $accionTexto = $request->estado == 'aprobado' ? 'Documento Aprobado' : 'Documento Rechazado';
        $detallesTexto = $request->estado == 'aprobado' ? "Se aprobó: $nombreDoc" : "Se rechazó: $nombreDoc. Motivo: " . $request->motivo;

        HistorialAlta::create([
            'alta_cliente_id' => $alta->id,
            'user_id' => Auth::id(),
            'accion' => $accionTexto,
            'detalles' => $detallesTexto
        ]);

        // Si al evaluar el documento (y revisar las referencias) el expediente se aprobó por completo:
        if ($alta->estado_alta == 'aprobado') {
            HistorialAlta::create([
                'alta_cliente_id' => $alta->id,
                'user_id' => Auth::id(),
                'accion' => 'Expediente Aprobado',
                'detalles' => 'Se validaron todos los documentos obligatorios y referencias comerciales.'
            ]);
        }

        return redirect()->back()->with('mensaje', 'Documento evaluado correctamente.');
    }

    // =======================================================
    // CONVERTIR ALTA A CLIENTE OFICIAL
    // =======================================================
    public function convertirCliente(Request $request, $id)
    {
        $alta = \App\Models\AltaCliente::findOrFail($id);

        // 1. Validar que vengan los datos obligatorios del formulario
        $request->validate([
            'numero_cliente' => 'required|string|unique:clientes,codigo',
            'descuento' => 'required|numeric',
            'monto_credito' => 'nullable|numeric',
            'dias_credito' => 'nullable|integer',
            'fecha_otorgamiento' => 'required|date',
            'referencia_bancaria' => 'nullable|string',
        ], [
            'numero_cliente.unique' => 'Ese Código / Número de Cliente ya existe en el sistema.'
        ]);

        // --- NUEVO: VERIFICACIÓN DE CORREO DUPLICADO ---
        $correoAValidar = $alta->email_pagos ?? '';
        if (!empty($correoAValidar)) {
            $existeCorreo = \App\Models\Cliente::where('email', $correoAValidar)->exists();
            if ($existeCorreo) {
                return redirect()->back()->withErrors("No se puede autorizar: El correo '$correoAValidar' ya está registrado a otro cliente en el sistema. Los correos deben ser únicos.");
            }
        }
        // -----------------------------------------------

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // 2. Crear el Cliente en el módulo de Clientes
            $cliente = new \App\Models\Cliente();
            
            // Heredar el Agente de Ventas original que creó el Alta
            $cliente->user_id = $alta->user_id; 

            // Datos generales
            $cliente->codigo = mb_strtoupper($request->numero_cliente);
            $cliente->nombre = $alta->razon_social;
            
            // Corrección de correos y teléfonos
            $cliente->email = $alta->email_pagos ?? ''; 
            $cliente->telefono = $alta->telefono_pagos ?? $alta->telefono_fiscal ?? '';
            
            // ¡CORRECCIÓN CLAVE! Usar los nombres reales (_fiscal) para no perder la dirección
            $calle = $alta->calle_fiscal ?? '';
            $colonia = $alta->colonia_fiscal ?? '';
            $municipio = $alta->municipio_fiscal ?? '';
            $cp = $alta->cp_fiscal ?? '';
            $cliente->direccion = trim("$calle, $colonia, $municipio, C.P. $cp");
            
            // El estado sí existe en 'clientes' (Vital para los aumentos regionales)
            $cliente->estado = $alta->estado_fiscal ?? '';
            
            // ¡CORRECCIÓN CLAVE! Forzamos formato numérico para que guarde el descuento exacto
            $cliente->descuento = floatval($request->input('descuento', 0));
            $cliente->monto_credito = floatval($request->input('monto_credito', 0));
            $cliente->dias_credito = intval($request->input('dias_credito', 0));
            
            $cliente->fecha_otorgamiento = $request->fecha_otorgamiento;
            $cliente->referencia_bancaria = $request->referencia_bancaria;
            $cliente->activo = 1;
            
            $cliente->save();

            // 3. Actualizar el registro del Alta para cerrarlo
            $alta->estado_alta = 'completado';
            
            // Guardar el reflejo financiero para la pantalla verde de éxito
            $alta->numero_cliente = $cliente->codigo; 
            $alta->descuento = $cliente->descuento;
            $alta->monto_credito = $cliente->monto_credito;
            $alta->dias_credito = $cliente->dias_credito;
            $alta->fecha_otorgamiento = $cliente->fecha_otorgamiento;
            $alta->referencia_bancaria = $cliente->referencia_bancaria;
            $alta->save();

            // 4. Escribir en la bitácora del Alta
            \App\Models\HistorialAlta::create([
                'alta_cliente_id' => $alta->id,
                'user_id' => auth()->id(),
                'accion' => 'Cliente Oficialmente Creado',
                'detalles' => 'Se aprobó el alta y se asignó el código: ' . $cliente->codigo,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('altas.show', $alta->id)->with('mensaje', '¡Alta autorizada y Cliente creado con éxito!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            // Si hay un error de base de datos, lo mostramos para saber exactamente qué falló
            return redirect()->back()->withErrors('Error interno al crear el cliente: ' . $e->getMessage());
        }
    }

    public function cancelarAlta(Request $request, $id)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:1000'
        ]);

        $alta = AltaCliente::findOrFail($id);
        $alta->estado_alta = 'cancelado'; // Usamos cancelado para el rechazo total
        $alta->observaciones_generales = $request->motivo_cancelacion;
        $alta->save();

        HistorialAlta::create([
            'alta_cliente_id' => $alta->id,
            'user_id' => Auth::id(),
            'accion' => 'Alta Cancelada/Rechazada',
            'detalles' => 'Motivo: ' . $request->motivo_cancelacion
        ]);

        return redirect()->route('altas.index')->with('mensaje', 'El alta ha sido rechazada y cancelada en su totalidad.');
    }

    private function actualizarEstadoGeneralAlta($alta)
    {
        $todosDocs = $alta->documentos;
        $rechazadosDocs = $todosDocs->where('estado_documento', 'rechazado')->count();

        // === LÓGICA PARA CLIENTES DE CONTADO ===
        if ($alta->tipo_alta == 'contado') {
            // Solo la Constancia Fiscal es obligatoria
            $obligatorios = ['constancia_fiscal'];
            $aprobadosObligatorios = $todosDocs->whereIn('tipo_documento', $obligatorios)->where('estado_documento', 'aprobado')->count();

            // En contado NO hay referencias, solo validamos los documentos
            if ($aprobadosObligatorios == count($obligatorios)) {
                $alta->estado_alta = 'aprobado'; 
            } elseif ($rechazadosDocs > 0) {
                // Si hay documentos rechazados
                $alta->estado_alta = 'rechazado'; 
            } else {
                $alta->estado_alta = 'en_revision'; 
            }
        }
        
        // === LÓGICA PARA CLIENTES DE CRÉDITO ===
        else {
            $obligatorios = ['constancia_fiscal', 'ine_cliente', 'comprobante_domicilio', 'formato_firmado'];
            $todasRefs = $alta->referencias;
            
            $rechazadasRefs = $todasRefs->where('estado_referencia', 'rechazado')->count();
            $aprobadasRefs = $todasRefs->where('estado_referencia', 'aprobado')->count();
            
            $aprobadosObligatorios = $todosDocs->whereIn('tipo_documento', $obligatorios)->where('estado_documento', 'aprobado')->count();

            // LA MAGIA DE "3 DE 5": Si tiene todos sus docs y AL MENOS 3 referencias, se aprueba todo.
            if ($aprobadosObligatorios == count($obligatorios) && $aprobadasRefs >= 3) {
                $alta->estado_alta = 'aprobado'; 
            } elseif ($rechazadosDocs > 0 || $rechazadasRefs > 0) {
                // Si no logró las 3 y tiene rechazos, se va a corrección
                $alta->estado_alta = 'rechazado'; 
            } else {
                $alta->estado_alta = 'en_revision'; 
            }
        }
        
        $alta->save();
    }

    // El Admin evalúa una referencia comercial (Aprobar / Rechazar)
    public function evaluarReferencia(Request $request, $alta_id, $ref_id)
    {
        $request->validate([
            'estado' => 'required|in:aprobado,rechazado',
            'motivo' => 'nullable|string|max:500'
        ]);

        $referencia = AltaReferencia::where('alta_cliente_id', $alta_id)->findOrFail($ref_id);
        $alta = AltaCliente::findOrFail($alta_id);

        $referencia->estado_referencia = $request->estado;
        $referencia->motivo_rechazo = ($request->estado === 'rechazado') ? $request->motivo : null;
        $referencia->save();

        // Recalculamos el estado global del expediente
        $this->actualizarEstadoGeneralAlta($alta);

        // REGISTRAR HISTORIAL
        $accionTexto = $request->estado == 'aprobado' ? 'Referencia Aprobada' : 'Referencia Rechazada';
        $detallesTexto = $request->estado == 'aprobado' ? "Se validó la referencia: " . $referencia->nombre : "Se rechazó la referencia: " . $referencia->nombre . ". Motivo: " . $request->motivo;

        HistorialAlta::create([
            'alta_cliente_id' => $alta->id,
            'user_id' => Auth::id(),
            'accion' => $accionTexto,
            'detalles' => $detallesTexto
        ]);

        return redirect()->back()->with('mensaje', 'Referencia comercial evaluada correctamente.');
    }

    // El Agente corrige una referencia comercial rechazada
    public function corregirReferencia(Request $request, $alta_id, $ref_id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'telefono' => 'required|string|max:50',
        ]);

        $referencia = AltaReferencia::where('alta_cliente_id', $alta_id)->findOrFail($ref_id);
        $alta = AltaCliente::findOrFail($alta_id);

        $referencia->update([
            'nombre' => mb_strtoupper($request->nombre),
            'correo' => strtolower($request->correo),
            'telefono' => $request->telefono,
            'estado_referencia' => 'pendiente', // Regresa a revisión
            'motivo_rechazo' => null
        ]);

        // REEMPLAZO INTELIGENTE: Que el sistema calcule automáticamente si ya todo está 
        // listo para revisión o si aún quedan otras referencias rechazadas por solventar.
        $this->actualizarEstadoGeneralAlta($alta);

        // REGISTRAR HISTORIAL
        HistorialAlta::create([
            'alta_cliente_id' => $alta->id,
            'user_id' => Auth::id(),
            'accion' => 'Referencia Corregida',
            'detalles' => 'El agente actualizó los datos de la referencia: ' . $referencia->nombre
        ]);

        return redirect()->back()->with('mensaje', 'Referencia comercial actualizada correctamente.');
    }
}