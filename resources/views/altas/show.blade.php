@extends('plantilla.app')

@section('contenido')

{{-- CALCULAMOS LOS DOCUMENTOS OBLIGATORIOS VS OPCIONALES --}}
@php
    $opcionales = [
        'opinion_cumplimiento', 
        'acta_constitutiva', 
        'poder_notarial', 
        'ine_representante'
    ];
    
    // SI ES DE CONTADO, INE, COMPROBANTE Y FORMATO FIRMADO SE VUELVEN OPCIONALES
    if ($alta->tipo_alta == 'contado') {
        array_push($opcionales, 'ine_cliente', 'comprobante_domicilio', 'formato_firmado');
    }
    
    $totalObligatorios = 0;
    $obligatoriosSubidos = 0;

    foreach($requisitos as $llave => $titulo) {
        if(!in_array($llave, $opcionales)) {
            $totalObligatorios++;
            $doc = $alta->documentos->where('tipo_documento', $llave)->first();
            if($doc && $doc->estado_documento != 'rechazado') {
                $obligatoriosSubidos++;
            }
        }
    }
    
    $porcentaje = $totalObligatorios > 0 ? ($obligatoriosSubidos / $totalObligatorios) * 100 : 0;
    $puedeEnviar = $obligatoriosSubidos >= $totalObligatorios;
@endphp

<div class="app-content pb-5">
    <div class="container-fluid">
        
        {{-- Encabezado con Estado --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Expediente de Alta #{{ $alta->id }}</h1>
                <p class="text-muted small mb-0">Cliente: <strong>{{ mb_strtoupper($alta->razon_social) }}</strong> | Tipo: <strong>{{ strtoupper($alta->tipo_alta) }}</strong></p>
            </div>
            <div>
                <a href="{{ route('altas.index') }}" class="btn btn-sm btn-secondary shadow-sm mr-3">
                    <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Regresar
                </a>
                @php
                    $badgeClass = 'bg-secondary';
                    $textoEstado = strtoupper(str_replace('_', ' ', $alta->estado_alta));
                    
                    if($alta->estado_alta == 'borrador') { 
                        $badgeClass = 'bg-secondary text-white'; 
                    }
                    elseif($alta->estado_alta == 'en_revision') { 
                        $badgeClass = 'bg-info text-white'; 
                        $textoEstado = 'EN REVISIÓN'; 
                    }
                    // AQUÍ DISFRAZAMOS EL RECHAZO PARCIAL
                    elseif($alta->estado_alta == 'rechazado') { 
                        $badgeClass = 'bg-warning text-dark'; 
                        $textoEstado = 'CON OBSERVACIONES (VERIFICAR)'; 
                    }
                    elseif($alta->estado_alta == 'aprobado') { 
                        $badgeClass = 'bg-success text-white'; 
                    }
                    // AQUÍ DISFRAZAMOS LA CANCELACIÓN TOTAL
                    elseif($alta->estado_alta == 'cancelado') { 
                        $badgeClass = 'bg-danger text-white'; 
                        $textoEstado = 'RECHAZADO DEFINITIVO'; 
                    }
                    elseif($alta->estado_alta == 'completado') { 
                        $badgeClass = 'bg-primary text-white'; 
                    }
                @endphp
                <span class="badge {{ $badgeClass }} px-3 py-2 fs-6 shadow-sm">
                    {{ $textoEstado }}
                </span>
            </div>
        </div>

        @if(Session::has('mensaje'))
            <div class="alert alert-success shadow-sm border-0"><i class="fas fa-check-circle mr-2"></i>{{ Session::get('mensaje') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger shadow-sm border-0">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Alerta si está cancelado globalmente --}}
        @if($alta->estado_alta == 'cancelado')
            <div class="alert alert-dark shadow-sm border-0 mb-4">
                <h6 class="font-weight-bold mb-1"><i class="fas fa-ban mr-2"></i> Alta Cancelada / Rechazada Definitivamente</h6>
                <p class="small mb-0"><strong>Motivo:</strong> {{ $alta->observaciones_generales }}</p>
            </div>
        @endif

        <div class="row">
            {{-- PANEL IZQUIERDO: INSTRUCCIONES Y PDF --}}
            <div class="col-lg-4 mb-4">
                
                <div class="card shadow border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-primary py-3" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-pdf mr-2"></i>1. Formato de Alta</h6>
                    </div>
                    <div class="card-body text-center bg-light">
                        <i class="fas fa-print fa-4x text-gray-300 mb-3"></i>
                        <p class="small text-muted">Descargue el formato pre-llenado, imprímalo, recabe la firma del cliente y súbalo en la sección de la derecha.</p>
                        <a href="{{ route('altas.pdf', $alta->id) }}" target="_blank" class="btn btn-danger btn-block shadow-sm rounded-pill font-weight-bold">
                            <i class="fas fa-download mr-2"></i> Descargar Formato PDF
                        </a>
                    </div>
                </div>

                {{-- CAJA DE ENVÍO FINAL (Progreso Dinámico) --}}
                <div class="card shadow border-0 mt-4" style="border-radius: 12px;">
                    <div class="card-body text-center">
                        <h6 class="font-weight-bold text-dark mb-3">Progreso del Expediente</h6>
                        <div class="progress mb-3 shadow-sm" style="height: 10px; border-radius: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $porcentaje }}%"></div>
                        </div>
                        <p class="small text-muted mb-3">{{ $obligatoriosSubidos }} de {{ $totalObligatorios }} documentos obligatorios subidos</p>

                        @if($alta->estado_alta == 'borrador' || $alta->estado_alta == 'observaciones')
                            <form action="{{ route('altas.enviar', $alta->id) }}" method="POST" class="form-confirmar" data-title="¿Enviar a Validación?" data-text="El departamento de Administración revisará los documentos." data-icon="info" data-color="#1cc88a" data-btn-text="Sí, enviar">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block shadow-sm rounded-pill font-weight-bold" {{ !$puedeEnviar ? 'disabled' : '' }}>
                                    <i class="fas fa-paper-plane mr-2"></i> Enviar a Validación
                                </button>
                            </form>
                            @if(!$puedeEnviar)
                                <small class="text-danger d-block mt-2"><i class="fas fa-lock mr-1"></i> Faltan documentos obligatorios por subir.</small>
                            @endif
                        @elseif($alta->estado_alta == 'en_revision')
                            <div class="alert alert-info small mb-0"><i class="fas fa-clock mr-1"></i> El expediente está siendo auditado por Administración.</div>
                        @endif

                        {{-- BOTÓN RECHAZAR TODA EL ALTA (SOLO ADMIN) --}}
                        @php $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'); @endphp
                        @if($isAdmin && !in_array($alta->estado_alta, ['completado', 'cancelado']))
                            <hr class="mt-4 mb-3">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-block rounded-pill font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalCancelarAlta">
                                <i class="fas fa-ban mr-1"></i> Rechazar/Cancelar Alta Completa
                            </button>

                            {{-- Modal Cancelar Alta --}}
                            <div class="modal fade text-start" id="modalCancelarAlta" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <form action="{{ route('altas.cancelar', $alta->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-danger text-white">
                                                <h6 class="modal-title font-weight-bold"><i class="fas fa-ban mr-2"></i>Cancelar Alta Definitivamente</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="small text-muted mb-2">Esta acción detendrá y cancelará todo el proceso de alta. Por favor, ingrese el motivo:</p>
                                                <textarea name="motivo_cancelacion" class="form-control" rows="3" required placeholder="Ej. Cliente duplicado, falta de solvencia, fraude..."></textarea>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cerrar</button>
                                                <button type="submit" class="btn btn-danger btn-sm px-3">Confirmar Cancelación</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- LÍNEA DE TIEMPO (HISTORIAL) --}}
                <div class="card shadow border-0 mt-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-info py-3" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-history mr-2"></i>Historial de Movimientos</h6>
                    </div>
                    <div class="card-body px-4 py-3" style="max-height: 400px; overflow-y: auto;">
                        <div class="timeline-wrapper" style="border-left: 2px solid #e3e6f0; margin-left: 10px; padding-left: 20px; position: relative;">
                            
                            @forelse($alta->historial as $hist)
                                @php
                                    // Asignamos colores e iconos según la acción
                                    $icon = 'fa-check'; $color = 'text-success';
                                    if(str_contains($hist->accion, 'Rechazado') || str_contains($hist->accion, 'Cancelada')) { $icon = 'fa-times'; $color = 'text-danger'; }
                                    elseif(str_contains($hist->accion, 'Enviado')) { $icon = 'fa-paper-plane'; $color = 'text-primary'; }
                                    elseif(str_contains($hist->accion, 'Oficialmente')) { $icon = 'fa-user-check'; $color = 'text-success'; }
                                @endphp

                                <div class="mb-4" style="position: relative;">
                                    {{-- El circulito que va sobre la línea --}}
                                    <div class="bg-white border {{ $color }} d-flex justify-content-center align-items-center shadow-sm" style="position: absolute; left: -31px; top: 0; width: 20px; height: 20px; border-radius: 50%; font-size: 0.6rem;">
                                        <i class="fas {{ $icon }}"></i>
                                    </div>
                                    
                                    {{-- Contenido del historial --}}
                                    <div class="small">
                                        <strong class="text-dark d-block">{{ $hist->accion }}</strong>
                                        <span class="text-muted">{{ $hist->detalles }}</span>
                                        <div class="mt-1" style="font-size: 0.75rem;">
                                            <span class="text-primary font-weight-bold"><i class="fas fa-user mr-1"></i>{{ $hist->usuario->name ?? 'Sistema' }}</span> &bull; 
                                            <span class="text-gray-500"><i class="far fa-clock mr-1"></i>{{ $hist->created_at->format('d/m/y h:i A') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="small text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> No hay historial registrado aún.</p>
                            @endforelse

                            {{-- Registro Inicial (Siempre existirá) --}}
                            <div style="position: relative;">
                                <div class="bg-white border text-secondary d-flex justify-content-center align-items-center shadow-sm" style="position: absolute; left: -31px; top: 0; width: 20px; height: 20px; border-radius: 50%; font-size: 0.6rem;">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="small">
                                    <strong class="text-dark d-block">Expediente Creado</strong>
                                    <span class="text-muted">Se capturaron los datos generales del cliente.</span>
                                    <div class="mt-1" style="font-size: 0.75rem;">
                                        <span class="text-primary font-weight-bold"><i class="fas fa-user mr-1"></i>{{ $alta->agente->name ?? 'Sistema' }}</span> &bull; 
                                        <span class="text-gray-500"><i class="far fa-clock mr-1"></i>{{ $alta->created_at->format('d/m/y h:i A') }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                </div>
            </div>

            {{-- PANEL DERECHO: LISTA DE LOS DOCUMENTOS --}}
            <div class="col-lg-8 mb-4">
                <div class="card shadow border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-success py-3" style="border-radius: 12px 12px 0 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-folder-open mr-2"></i>{{ $alta->tipo_alta == 'credito' ? '2' : '1' }}. Documentos Adjuntos Requeridos
                            </h6>
                            <span class="badge bg-danger text-white px-2 py-1 shadow-sm">
                                <i class="fas fa-file-pdf mr-1"></i> Formato PDF
                            </span>
                        </div>
                        <small class="text-danger font-weight-bold mt-2 d-block">
                            <i class="fas fa-exclamation-circle mr-1"></i> Nota: Favor de adjuntar los documentos exclusivamente en formato .PDF
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" style="border-radius: 0 0 12px 12px;">

                            @foreach($requisitos as $llave => $titulo)
                                @php
                                    $docSubido = $alta->documentos->where('tipo_documento', $llave)->first();
                                    $estadoDoc = $docSubido ? $docSubido->estado_documento : null;
                                    $esOpcional = in_array($llave, $opcionales);
                                @endphp

                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div style="width: 50%;">
                                        <h6 class="mb-1 font-weight-bold text-dark" style="font-size: 0.9rem;">
                                            {{ $titulo }}
                                            @if($esOpcional)
                                                <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem;">Opcional</span>
                                            @else
                                                <span class="text-danger ms-1" title="Obligatorio">*</span>
                                            @endif
                                        </h6>
                                        @if($estadoDoc == 'rechazado')
                                            <small class="text-danger font-weight-bold d-block mt-1">
                                                <i class="fas fa-times-circle mr-1"></i> Motivo: {{ $docSubido->motivo_rechazo }}
                                            </small>
                                        @endif
                                        @if($docSubido)
                                            <a href="{{ asset($docSubido->archivo_ruta) }}" target="_blank" class="small text-primary text-decoration-none font-weight-bold">
                                                <i class="fas fa-eye mr-1"></i> Abrir Archivo
                                            </a>
                                        @endif
                                    </div>
                                    
                                    <div class="text-end" style="width: 50%;">
                                        
                                        {{-- ====== VISTA PARA ADMINISTRADOR ====== --}}
                                        @if($isAdmin && $alta->estado_alta != 'borrador' && $alta->estado_alta != 'cancelado')
                                            
                                            @if($estadoDoc == 'pendiente')
                                                <div class="d-flex justify-content-end align-items-center">
                                                    {{-- Botón Aprobar --}}
                                                    <form action="{{ route('altas.evaluar_doc', [$alta->id, $docSubido->id]) }}" method="POST" class="mr-2">
                                                        @csrf
                                                        <input type="hidden" name="estado" value="aprobado">
                                                        <button type="submit" class="btn btn-sm btn-success shadow-sm" title="Aprobar Documento"><i class="fas fa-check"></i> Aprobar</button>
                                                    </form>
                                                    {{-- Botón Rechazar --}}
                                                    <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRechazo{{$docSubido->id}}">
                                                        <i class="fas fa-times"></i> Rechazar
                                                    </button>
                                                </div>

                                                {{-- Modal de Rechazo --}}
                                                <div class="modal fade text-start" id="modalRechazo{{$docSubido->id}}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content border-0 shadow">
                                                            <form action="{{ route('altas.evaluar_doc', [$alta->id, $docSubido->id]) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="estado" value="rechazado">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h6 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Rechazar Documento</h6>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="small text-muted mb-2">Motivo por el que rechaza: <strong>{{ $titulo }}</strong></p>
                                                                    <textarea name="motivo" class="form-control" rows="3" required></textarea>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-danger btn-sm px-3">Confirmar Rechazo</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            @elseif($estadoDoc == 'aprobado')
                                                <span class="badge bg-success px-3 py-2"><i class="fas fa-check-double mr-1"></i> Aprobado</span>
                                            @else
                                                {{-- SI ES RECHAZADO O FALTA (Y EL ADMIN QUIERE SUBIRLO ÉL MISMO) --}}
                                                <div class="d-flex flex-column align-items-end">
                                                    @if($estadoDoc == 'rechazado')
                                                        <span class="badge bg-danger mb-2"><i class="fas fa-times mr-1"></i> Rechazado</span>
                                                    @endif
                                                    <form action="{{ route('altas.upload', $alta->id) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center justify-content-end">
                                                        @csrf
                                                        <input type="hidden" name="tipo_documento" value="{{ $llave }}">
                                                        <input type="file" name="archivo" class="form-control form-control-sm mr-2" style="max-width: 190px;" required accept=".pdf,image/*">
                                                        <button type="submit" class="btn btn-sm btn-primary shadow-sm" title="Subir archivo correcto"><i class="fas fa-upload"></i></button>
                                                    </form>
                                                </div>
                                            @endif

                                        {{-- ====== VISTA PARA AGENTE O SI ESTÁ CANCELADO ====== --}}
                                        @else
                                            @if($alta->estado_alta == 'cancelado')
                                                <span class="badge bg-dark px-3 py-2"><i class="fas fa-ban mr-1"></i> Bloqueado</span>
                                            @elseif(in_array($alta->estado_alta, ['borrador', 'rechazado']))
                                                @if(!$estadoDoc || $estadoDoc == 'rechazado')
                                                    <div class="d-flex flex-column align-items-end">
                                                        @if($estadoDoc == 'rechazado')
                                                            <span class="badge bg-danger mb-2"><i class="fas fa-times mr-1"></i> Rechazado</span>
                                                        @endif
                                                        <form action="{{ route('altas.upload', $alta->id) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center justify-content-end">
                                                            @csrf
                                                            <input type="hidden" name="tipo_documento" value="{{ $llave }}">
                                                            <input type="file" name="archivo" class="form-control form-control-sm mr-2" style="max-width: 190px;" required accept=".pdf,image/*">
                                                            <button type="submit" class="btn btn-sm btn-primary shadow-sm" title="Subir"><i class="fas fa-upload"></i></button>
                                                        </form>
                                                    </div>
                                                @elseif($estadoDoc == 'pendiente')
                                                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-hourglass-half mr-1"></i> Listo para revisión</span>
                                                @elseif($estadoDoc == 'aprobado')
                                                    <span class="badge bg-success px-3 py-2"><i class="fas fa-check-double mr-1"></i> Aprobado</span>
                                                @endif
                                            @else
                                                @if($estadoDoc == 'pendiente')
                                                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-hourglass-half mr-1"></i> En Revisión Admin</span>
                                                @elseif($estadoDoc == 'aprobado')
                                                    <span class="badge bg-success px-3 py-2"><i class="fas fa-check-double mr-1"></i> Aprobado</span>
                                                @endif
                                            @endif
                                        @endif

                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>

            {{-- CONDICIONAL PARA MOSTRAR REFERENCIAS SOLO EN CRÉDITO --}}
            @if($alta->tipo_alta == 'credito')
            {{-- CARD DE VALIDACIÓN DE REFERENCIAS COMERCIALES --}}
                <div class="card shadow mb-4 border-0 mt-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-dark py-3" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-users mr-2"></i>3. Auditoría de Referencias Comerciales
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small font-weight-bold">
                                    <tr>
                                        <th class="border-0 pl-4" style="width: 5%">#</th>
                                        <th class="border-0" style="width: 30%">Razón Social / Empresa</th>
                                        <th class="border-0" style="width: 25%">Contacto / Teléfono</th>
                                        <th class="border-0 text-center" style="width: 15%">Estado</th>
                                        <th class="border-0 text-center pr-4" style="width: 25%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($alta->referencias as $index => $ref)
                                        @php
                                            $badgeRef = 'bg-secondary';
                                            if($ref->estado_referencia == 'pendiente') $badgeRef = 'bg-warning text-dark';
                                            elseif($ref->estado_referencia == 'aprobado') $badgeRef = 'bg-success text-white';
                                            elseif($ref->estado_referencia == 'rechazado') $badgeRef = 'bg-danger text-white';
                                        @endphp
                                        <tr class="align-middle">
                                            <td class="pl-4 font-weight-bold text-secondary">{{ $index + 1 }}</td>
                                            <td>
                                                <span class="font-weight-bold text-dark d-block">{{ $ref->nombre }}</span>
                                                <small class="text-muted"><i class="far fa-envelope mr-1"></i>{{ $ref->correo }}</small>
                                            </td>
                                            <td>
                                                <span class="text-dark d-block small font-weight-bold"><i class="fas fa-phone-alt mr-1 text-gray-400"></i>{{ $ref->telefono }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $badgeRef }} px-2 py-1 shadow-sm small">
                                                    {{ strtoupper($ref->estado_referencia) }}
                                                </span>
                                            </td>
                                            <td class="text-center pr-4">
                                                {{-- ACCIONES PARA EL ADMINISTRADOR (EVALUAR) --}}
                                                @if((auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin')) && in_array($alta->estado_alta, ['en_revision', 'rechazado']))
                                                    @if($ref->estado_referencia == 'pendiente')
                                                        <div class="d-flex justify-content-center gap-2">
                                                            {{-- Botón Aprobar --}}
                                                            <form action="{{ route('altas.evaluar_ref', [$alta->id, $ref->id]) }}" method="POST" class="form-confirmar d-inline" data-title="¿Aprobar Referencia?" data-text="Se marcará esta referencia como válida." data-icon="success" data-color="#28a745" data-btn-text="Sí, aprobar">
                                                                @csrf
                                                                <input type="hidden" name="estado" value="aprobado">
                                                                <button type="submit" class="btn btn-xs btn-success rounded-pill px-2 shadow-sm" title="Aprobar"><i class="fas fa-check"></i></button>
                                                            </form>

                                                            {{-- Botón Rechazar (Despliega el panel de motivo) --}}
                                                            <button type="button" class="btn btn-xs btn-danger rounded-pill px-2 shadow-sm" data-bs-toggle="collapse" data-bs-target="#panel-rechazo-ref-{{ $ref->id }}" title="Rechazar"><i class="fas fa-times"></i></button>
                                                        </div>
                                                    @else
                                                        <small class="text-muted"><i class="fas fa-lock mr-1"></i> Auditado</small>
                                                    @endif
                                                @endif

                                                {{-- ACCIONES PARA EL AGENTE (CORREGIR RECHAZADA) --}}
                                                @if($ref->estado_referencia == 'rechazado' && ($alta->user_id == auth()->id() || $isAdmin) && in_array($alta->estado_alta, ['borrador', 'rechazado', 'en_revision']))
                                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 shadow-sm font-weight-bold" data-bs-toggle="modal" data-bs-target="#modal-corregir-ref-{{ $ref->id }}">
                                                        <i class="fas fa-edit mr-1"></i> Editar
                                                    </button>
                                                @elseif($ref->estado_referencia == 'aprobado')
                                                    <small class="text-success font-weight-bold"><i class="fas fa-check-double mr-1"></i> Aceptada</small>
                                                @elseif($ref->estado_referencia == 'pendiente' && $alta->user_id == auth()->id())
                                                    <small class="text-muted"><i class="fas fa-hourglass-half mr-1"></i> Esperando Auditoría</small>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- PANEL DESPLEGABLE INLINE PARA INGRESO DE MOTIVO DE RECHAZO (ADMIN) --}}
                                        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                                            <tr class="collapse bg-light" id="panel-rechazo-ref-{{ $ref->id }}">
                                                <td colspan="5" class="px-4 py-3 border-bottom shadow-inner">
                                                    <form action="{{ route('altas.evaluar_ref', [$alta->id, $ref->id]) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="estado" value="rechazado">
                                                        <div class="row align-items-end">
                                                            <div class="col-md-9">
                                                                <label class="form-label text-danger font-weight-bold small"><i class="fas fa-exclamation-circle mr-1"></i> Especifique el motivo del rechazo de la referencia:</label>
                                                                <input type="text" name="motivo" class="form-control form-control-sm" placeholder="Ej. El correo corporativo no existe / El número telefónico corresponde a un celular personal..." required>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <button type="submit" class="btn btn-sm btn-danger w-100 font-weight-bold shadow-sm"><i class="fas fa-save mr-1"></i> Confirmar Rechazo</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- CUADRO DE NOTIFICACIÓN DE MOTIVO SI YA ESTÁ RECHAZADA (PARA AMBOS ROLES) --}}
                                        @if($ref->estado_referencia == 'rechazado' && $ref->motivo_rechazo)
                                            <tr class="table-danger">
                                                <td colspan="5" class="px-4 py-2 small" style="border-left: 4px solid #dc3545 !important;">
                                                    <strong class="text-danger"><i class="fas fa-info-circle mr-1"></i> Motivo de Rechazo:</strong> 
                                                    <span class="text-dark font-weight-bold">{{ $ref->motivo_rechazo }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-info-circle mr-1"></i> Este expediente no cuenta con referencias capturadas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- CONVERSIÓN A CLIENTE REAL / RESUMEN FINAL --}}
        @if(($alta->estado_alta == 'aprobado' && $isAdmin) || $alta->estado_alta == 'completado')
        <div class="row mt-2 mb-5">
            <div class="col-12">
                <div class="card shadow border-0" style="border-radius: 12px; border-left: 5px solid #1cc88a;">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="m-0 font-weight-bold text-success"><i class="fas fa-check-circle mr-2"></i>{{ $alta->tipo_alta == 'credito' ? 'Otorgamiento de Crédito y Apertura de Cliente' : 'Apertura de Cliente (Contado)' }}</h5>
                    </div>
                    <div class="card-body bg-light" style="border-radius: 0 0 12px 12px;">
                        
                        {{-- FORMULARIO DE APROBACIÓN (SOLO ADMIN SI AÚN NO SE COMPLETA) --}}
                        @if($alta->estado_alta == 'aprobado' && $isAdmin)
                            <p class="small text-muted mb-3">Todos los documentos obligatorios han sido auditados correctamente. Ingrese las condiciones comerciales para registrar oficialmente a este cliente en el sistema.</p>
                            
                            <form action="{{ route('altas.convertir', $alta->id) }}" method="POST" class="form-confirmar"
                                  data-title="¿Autorizar Cliente?" 
                                  data-text="Se registrará este cliente oficialmente en la base de datos." 
                                  data-icon="question" 
                                  data-color="#1cc88a" 
                                  data-btn-text="Sí, Autorizar">
                                @csrf
                                <div class="row align-items-end">
                                    
                                    {{-- CONDICIONAMOS MONTO Y DÍAS SEGÚN EL TIPO --}}
                                    @if($alta->tipo_alta == 'credito')
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label small font-weight-bold text-dark">Monto Crédito ($) *</label>
                                            <input type="number" step="0.01" name="monto_credito" class="form-control" required placeholder="0.00">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label small font-weight-bold text-dark">Días de Crédito *</label>
                                            <input type="number" name="dias_credito" class="form-control" required placeholder="Ej. 30">
                                        </div>
                                    @else
                                        {{-- SI ES CONTADO, MANDAMOS 0 EN OCULTO --}}
                                        <input type="hidden" name="monto_credito" value="0">
                                        <input type="hidden" name="dias_credito" value="0">
                                        <div class="col-md-4 mb-3">
                                            <div class="alert alert-success border-0 py-2 px-3 mb-0" style="height: 38px; display: flex; align-items: center;">
                                                <i class="fas fa-money-bill-wave me-2 mr-2"></i> Modalidad de Contado (Sin línea de crédito)
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small font-weight-bold text-dark">Descuento (%) *</label>
                                        <select name="descuento" class="form-select" required>
                                            <option value="0">0% (Sin Descuento)</option>
                                            <option value="32">32%</option>
                                            <option value="34">34%</option>
                                            <option value="36">36%</option>
                                            <option value="40">40%</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small font-weight-bold text-dark">Fecha Otorg. *</label>
                                        <input type="date" name="fecha_otorgamiento" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small font-weight-bold text-dark">No. Cliente *</label>
                                        <input type="text" name="numero_cliente" class="form-control" required placeholder="Código">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label small font-weight-bold text-dark">Ref. Bancaria</label>
                                        <input type="text" name="referencia_bancaria" class="form-control" placeholder="Depósitos">
                                    </div>
                                </div>
                                <div class="text-end mt-3 border-top pt-3">
                                    <button type="submit" class="btn btn-success shadow-sm font-weight-bold px-4 py-2">
                                        <i class="fas fa-user-check mr-2"></i>Autorizar Alta y Convertir a Cliente
                                    </button>
                                </div>
                            </form>
                            
                        {{-- RESUMEN FINAL (VISIBLE PARA TODOS SI YA ESTÁ COMPLETADO) --}}
                        @elseif($alta->estado_alta == 'completado')
                            <div class="row text-dark">
                                @if($alta->tipo_alta == 'credito')
                                    <div class="col-md-3 mb-2"><strong>Monto Crédito:</strong> ${{ number_format($alta->monto_credito, 2) }}</div>
                                    <div class="col-md-2 mb-2"><strong>Días Crédito:</strong> {{ $alta->dias_credito }} días</div>
                                @else
                                    <div class="col-md-5 mb-2"><strong class="text-success"><i class="fas fa-money-bill-wave me-1"></i> Cliente de Contado</strong></div>
                                @endif
                                <div class="col-md-2 mb-2"><strong>Descuento:</strong> {{ $alta->descuento }}%</div>
                                <div class="col-md-2 mb-2"><strong>Otorgado el:</strong> {{ \Carbon\Carbon::parse($alta->fecha_otorgamiento)->format('d/m/Y') }}</div>
                                <div class="col-md-3 mb-2"><strong>Referencia Bancaria:</strong> <span class="badge bg-light text-dark border">{{ $alta->referencia_bancaria ?? 'N/A' }}</span></div>
                            </div>
                            <div class="alert alert-success mb-0 border-0 shadow-sm mt-3">
                                <i class="fas fa-info-circle mr-2"></i> Este proceso de alta ha finalizado. El cliente fue registrado en el sistema bajo el número/código <strong>{{ $alta->numero_cliente }}</strong>.
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- MODALES DINÁMICOS DE CORRECCIÓN DE REFERENCIAS COMERCIALES (SOLO PARA AGENTES) --}}
    @foreach($alta->referencias as $ref)
        @if($ref->estado_referencia == 'rechazado' && ($alta->user_id == auth()->id() || $isAdmin))
            <div class="modal fade" id="modal-corregir-ref-{{ $ref->id }}" {{-- data-bs-backdrop="static" --}} tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                        <div class="modal-header bg-warning text-dark" style="border-radius: 14px 14px 0 0;">
                            <h5 class="modal-title font-weight-bold small text-uppercase"><i class="fas fa-edit mr-2"></i>Corregir Referencia Comercial</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('altas.corregir_ref', [$alta->id, $ref->id]) }}" method="POST" class="form-confirmar" data-title="¿Guardar Corrección?" data-text="La referencia volverá a estado pendiente para auditoría." data-icon="question" data-color="#f6c23e" data-btn-text="Sí, actualizar">
                            @csrf
                            @method('PUT')
                            <div class="modal-body bg-light px-4 py-3">
                                
                                <div class="alert alert-danger border-0 small py-2 mb-3">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Rechazado por:</strong> {{ $ref->motivo_rechazo }}
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Nombre o Razón Social de la Empresa *</label>
                                    <input type="text" name="nombre" class="form-control form-control-sm text-uppercase" value="{{ $ref->nombre }}" required style="text-transform: uppercase;">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Correo Electrónico de Contacto *</label>
                                    <input type="email" name="correo" class="form-control form-control-sm" value="{{ $ref->correo }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Teléfono(s) Fijo (No celular) *</label>
                                    <input type="text" name="telefono" class="form-control form-control-sm" value="{{ $ref->telefono }}" required>
                                </div>

                            </div>
                            <div class="modal-footer bg-white border-0 py-2">
                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 font-weight-bold text-dark shadow-sm"><i class="fas fa-save mr-1"></i> Actualizar y Re-enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection