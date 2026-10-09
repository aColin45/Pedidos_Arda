@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid">
        
        {{-- Encabezado --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Gestión de Altas de Clientes</h1>
                <p class="text-muted small mb-0">Control de expedientes, revisión de documentos y apertura de crédito</p>
            </div>
            
            <div>
                <a href="{{ route('altas.pdf_blanco') }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm rounded-pill px-3 font-weight-bold mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Formato en Blanco
                </a>
                
                {{-- BOTÓN MODIFICADO: Ahora abre el Modal en lugar de ir directo --}}
                <button type="button" class="btn btn-sm btn-primary shadow-sm rounded-pill px-3 font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalTipoAlta">
                    <i class="fas fa-plus mr-1"></i> Nueva Alta
                </button>
            </div>
        </div>

        {{-- FORMULARIO DE BÚSQUEDA CON BOTÓN DE LIMPIAR --}}
            <form action="{{ route('altas.index') }}" method="GET" class="mb-4 mt-3">
                <div class="input-group shadow-sm">
                    <span class="input-group-text text-white border-0" style="background-color: #0d6efd;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="texto" class="form-control border-primary border-end-0" 
                        placeholder="Buscar por Folio, Razón Social, RFC o Agente..." 
                        value="{{ request('texto') }}">
                    
                    {{-- Botón 'X' para limpiar la búsqueda --}}
                    @if(request('texto'))
                        <a href="{{ route('altas.index') }}" class="btn btn-light border-primary border-start-0 text-danger px-3" title="Limpiar filtro">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                    
                    <button class="btn btn-warning fw-bold text-dark border-0 px-4" type="submit">
                        Buscar
                    </button>
                </div>
            </form>

        @if(Session::has('mensaje'))
            <div class="alert alert-success shadow-sm border-0"><i class="fas fa-check-circle mr-2"></i>{{ Session::get('mensaje') }}</div>
        @endif

        {{-- Tabla Principal --}}
        <div class="card shadow border-0" style="border-radius: 12px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="border-0 pl-4">Folio</th>
                                <th class="border-0">Razón Social</th>
                                <th class="border-0">RFC</th>
                                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                                <th class="border-0"><i class="fas fa-user-tie mr-1"></i> Agente</th>
                                @endif
                                
                                {{-- COLUMNA MODIFICADA: Trazabilidad en lugar de solo Registro --}}
                                <th class="border-0"><i class="far fa-clock mr-1"></i> Fechas Clave / Trazabilidad</th>
                                
                                <th class="border-0 text-center">Estado</th>
                                <th class="border-0 text-center pr-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($altas as $alta)
                            <tr>
                                <td class="pl-4 font-weight-bold text-secondary">#{{ $alta->id }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ mb_strtoupper($alta->razon_social) }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $alta->rfc }}</span></td>
                                
                                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                                <td>{{ $alta->agente->name ?? 'N/A' }}</td>
                                @endif
                                
                                {{-- CELDAS DE TRAZABILIDAD (Diseño de Timeline Vertical) --}}
                                <td>
                                    <div class="d-flex flex-column gap-2" style="font-size: 0.85rem;">
                                        
                                        {{-- 1. Fecha de Creación --}}
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-secondary border me-2 d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; border-radius: 50%;">
                                                <i class="fas fa-file-signature"></i>
                                            </span>
                                            <div style="line-height: 1.2;">
                                                <span class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Creado</span><br>
                                                <strong class="text-dark">{{ $alta->created_at->format('d/m/Y h:i A') }}</strong>
                                            </div>
                                        </div>
                                        
                                        {{-- 2. Última Revisión --}}
                                        @if(in_array($alta->estado_alta, ['aprobado', 'completado', 'rechazado', 'en_revision']))
                                            <div class="d-flex align-items-center mt-1">
                                                @php
                                                    $iconoRev = 'fa-search';
                                                    $colorRev = 'bg-info text-white';
                                                    if($alta->estado_alta == 'aprobado' || $alta->estado_alta == 'completado') { $iconoRev = 'fa-user-check'; $colorRev = 'bg-success text-white'; }
                                                    elseif($alta->estado_alta == 'rechazado') { $iconoRev = 'fa-times'; $colorRev = 'bg-danger text-white'; }
                                                @endphp
                                                <span class="badge {{ $colorRev }} me-2 d-flex justify-content-center align-items-center shadow-sm" style="width: 28px; height: 28px; border-radius: 50%;">
                                                    <i class="fas {{ $iconoRev }}"></i>
                                                </span>
                                                <div style="line-height: 1.2;">
                                                    <span class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Última Revisión</span><br>
                                                    <strong class="text-dark">{{ $alta->updated_at->format('d/m/Y h:i A') }}</strong>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- 3. Alta en DB --}}
                                        @if($alta->estado_alta == 'completado')
                                            <div class="d-flex align-items-center mt-1">
                                                <span class="badge bg-primary text-white me-2 d-flex justify-content-center align-items-center shadow-sm" style="width: 28px; height: 28px; border-radius: 50%;">
                                                    <i class="fas fa-check-double"></i>
                                                </span>
                                                <div style="line-height: 1.2;">
                                                    <span class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Registro del Cliente</span><br>
                                                    <strong class="text-primary">{{ \Carbon\Carbon::parse($alta->fecha_otorgamiento)->format('d/m/Y') }}</strong>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </td>

                                <td class="text-center">
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
                                    <span class="badge {{ $badgeClass }} px-3 py-2 shadow-sm" style="font-size: 0.85em;">
                                        {{ $textoEstado }}
                                    </span>
                                </td>
                                <td class="text-center pr-4">
                                    <a href="{{ route('altas.show', $alta->id) }}" class="btn btn-sm btn-info shadow-sm rounded-pill px-3" title="Abrir Expediente">
                                        <i class="fas fa-folder-open mr-1"></i> Expediente
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin')) ? 7 : 6 }}" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-gray-300 d-block"></i>
                                    No hay altas registradas en el sistema.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3 rounded-bottom">
                {{ $altas->links() }}
            </div>
        </div>

    </div>
</div>

{{-- NUEVO MODAL: Selección de Tipo de Alta --}}
<div class="modal fade" id="modalTipoAlta" tabindex="-1" aria-labelledby="modalTipoAltaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalTipoAltaLabel">Seleccione el Tipo de Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('altas.create', ['tipo' => 'contado']) }}" class="btn btn-outline-success w-100 p-3 h-100 d-flex flex-column justify-content-center align-items-center">
                            <i class="fas fa-money-bill-wave fs-1 mb-2"></i>
                            <span class="fw-bold">De Contado</span>
                            <small class="text-muted mt-1" style="font-size:0.7rem; white-space:normal;">Trámite rápido. Solo datos básicos y Constancia Fiscal.</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('altas.create', ['tipo' => 'credito']) }}" class="btn btn-outline-primary w-100 p-3 h-100 d-flex flex-column justify-content-center align-items-center">
                            <i class="fas fa-hand-holding-usd fs-1 mb-2"></i>
                            <span class="fw-bold">De Crédito</span>
                            <small class="text-muted mt-1" style="font-size:0.7rem; white-space:normal;">Proceso completo. Requiere referencias y expediente total.</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection