@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid mt-4">
        
        {{-- ======================================================== --}}
        {{-- TARJETAS KPI COMO FILTROS RÁPIDOS INTERACTIVOS (5 COLUMNAS) --}}
        {{-- ======================================================== --}}
        <div class="row mb-4">
            {{-- 1. TODOS --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('altas.index', ['estado' => '', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ empty($estatusFiltro) ? 'bg-primary text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #4e73df !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ empty($estatusFiltro) ? 'text-white' : 'text-primary' }}" style="font-size: 0.70rem;">Total Expedientes</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['total'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-folder-open fa-2x {{ empty($estatusFiltro) ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 2. PENDIENTES DEL AGENTE (Borrador / Con observaciones) --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('altas.index', ['estado' => 'pendientes_agente', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estatusFiltro == 'pendientes_agente' ? 'bg-warning text-dark shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #f6c23e !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estatusFiltro == 'pendientes_agente' ? 'text-dark' : 'text-warning' }}" style="font-size: 0.70rem;">Atención Requerida</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['pendientes_agente'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x {{ $estatusFiltro == 'pendientes_agente' ? 'text-dark' : 'text-gray-300' }}" style="{{ $estatusFiltro == 'pendientes_agente' ? 'opacity: 0.5;' : '' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 3. EN REVISIÓN ADMIN --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('altas.index', ['estado' => 'pendientes_admin', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estatusFiltro == 'pendientes_admin' ? 'bg-info text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #36b9cc !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estatusFiltro == 'pendientes_admin' ? 'text-white' : 'text-info' }}" style="font-size: 0.70rem;">En Revisión (Admin)</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['pendientes_admin'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-search fa-2x {{ $estatusFiltro == 'pendientes_admin' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 4. COMPLETADOS --}}
            <div class="col-xl col-md-6 col-sm-6 mb-2">
                <a href="{{ route('altas.index', ['estado' => 'completado', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estatusFiltro == 'completado' ? 'bg-success text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #1cc88a !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estatusFiltro == 'completado' ? 'text-white' : 'text-success' }}" style="font-size: 0.70rem;">Clientes Oficiales</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['completados'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-check-double fa-2x {{ $estatusFiltro == 'completado' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 5. RECHAZADOS DEFINITIVOS (Cancelados) --}}
            <div class="col-xl col-md-6 col-sm-6 mb-2">
                <a href="{{ route('altas.index', ['estado' => 'cancelado', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estatusFiltro == 'cancelado' ? 'bg-danger text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #e74a3b !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estatusFiltro == 'cancelado' ? 'text-white' : 'text-danger' }}" style="font-size: 0.70rem;">Rechazados Definitivos</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['cancelados'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-ban fa-2x {{ $estatusFiltro == 'cancelado' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
            <div class="card-header py-3 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                <h3 class="card-title font-weight-bold text-primary mb-3 mb-md-0">
                    <i class="fas fa-user-plus mr-2"></i> Gestión de Altas de Clientes
                </h3>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('altas.pdf_blanco') }}" target="_blank" class="btn btn-outline-danger font-weight-bold shadow-sm" title="Imprimir Formato Físico">
                        <i class="fas fa-file-pdf mr-1"></i> Formato en Blanco
                    </a>
                    
                    @can('alta-create')
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTipoAlta">
                        <i class="fas fa-plus mr-1"></i> Nueva Alta
                    </button>
                    @endcan
                </div>
            </div>
            
            <div class="card-body bg-light border-bottom">
                {{-- BARRA DE BÚSQUEDA Y FILTROS AVANZADOS --}}
                <form action="{{ route('altas.index') }}" method="get">
                    <div class="row g-2">
                        {{-- Filtro Estatus Oculto --}}
                        <input type="hidden" name="estado" value="{{ $estatusFiltro }}">

                        @hasanyrole('admin|super-admin|superadmin')
                        <div class="col-md-3">
                            <select name="agente_id" class="form-select shadow-sm font-weight-bold text-primary" onchange="this.form.submit()">
                                <option value="">👤 Todos los Agentes</option>
                                @foreach($agentes as $ag)
                                    <option value="{{ $ag->id }}" {{ $agenteFiltro == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endhasanyrole

                        <div class="col-md-{{ auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']) ? '7' : '10' }}">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
                                <input name="texto" type="text" class="form-control border-start-0" value="{{ $texto }}" placeholder="Buscar por Folio, Razón Social o RFC...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm font-weight-bold"><i class="fas fa-search mr-1"></i> Buscar</button>
                        </div>
                    </div>
                </form>
            </div>

            @if(Session::has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm m-3">
                <i class="fas fa-check-circle mr-1"></i> {{ Session::get('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-white text-muted">
                        <tr>
                            <th class="border-bottom-0 pl-4">Folio</th>
                            <th class="border-bottom-0">Razón Social</th>
                            <th class="border-bottom-0">RFC</th>
                            @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
                            <th class="border-bottom-0"><i class="fas fa-user-tie mr-1"></i> Agente</th>
                            @endif
                            <th class="border-bottom-0"><i class="far fa-clock mr-1"></i> Fechas Clave / Trazabilidad</th>
                            <th class="border-bottom-0 text-center">Estado</th>
                            <th class="border-bottom-0 text-center pr-4">Acciones</th>
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
                            
                            @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mr-2" style="width: 28px; height: 28px;"><i class="fas fa-user text-primary" style="font-size: 0.8rem;"></i></div>
                                    <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $alta->agente->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            @endif
                            
                            {{-- CELDAS DE TRAZABILIDAD --}}
                            <td>
                                <div class="d-flex flex-column gap-2" style="font-size: 0.85rem;">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-light text-secondary border me-2 d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; border-radius: 50%;">
                                            <i class="fas fa-file-signature"></i>
                                        </span>
                                        <div style="line-height: 1.2;">
                                            <span class="text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Creado</span><br>
                                            <strong class="text-dark">{{ $alta->created_at->format('d/m/Y h:i A') }}</strong>
                                        </div>
                                    </div>
                                    
                                    @if(in_array($alta->estado_alta, ['aprobado', 'completado', 'rechazado', 'en_revision']))
                                        <div class="d-flex align-items-center mt-1">
                                            @php
                                                $iconoRev = 'fa-search'; $colorRev = 'bg-info text-white';
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
                                    
                                    if($alta->estado_alta == 'borrador') { $badgeClass = 'bg-secondary text-white'; }
                                    elseif($alta->estado_alta == 'en_revision') { $badgeClass = 'bg-info text-white'; $textoEstado = 'EN REVISIÓN'; }
                                    elseif($alta->estado_alta == 'rechazado') { $badgeClass = 'bg-warning text-dark'; $textoEstado = 'CON OBSERVACIONES'; }
                                    elseif($alta->estado_alta == 'aprobado') { $badgeClass = 'bg-success text-white'; }
                                    elseif($alta->estado_alta == 'cancelado') { $badgeClass = 'bg-danger text-white'; $textoEstado = 'RECHAZADO DEFINITIVO'; }
                                    elseif($alta->estado_alta == 'completado') { $badgeClass = 'bg-primary text-white'; }
                                @endphp
                                
                                <span class="badge {{ $badgeClass }} px-3 py-2 shadow-sm" style="font-size: 0.85em;">
                                    {{ $textoEstado }}
                                </span>

                                {{-- NUEVO: ALERTA DE MOTIVO DE RECHAZO DEFINITIVO --}}
                                @if($alta->estado_alta == 'cancelado' && !empty($alta->observaciones_generales))
                                    <div class="mt-2 shadow-sm text-start" style="max-width: 250px; margin: 0 auto; background-color: #fff0f0; border-left: 3px solid #dc3545; padding: 6px 8px; border-radius: 4px;">
                                        <div class="text-danger font-weight-bold" style="font-size: 0.70rem; margin-bottom: 2px;">
                                            <i class="fas fa-exclamation-circle mr-1"></i>Motivo de Rechazo:
                                        </div>
                                        <div class="text-dark" style="font-size: 0.75rem; white-space: normal; word-wrap: break-word;">
                                            {{ $alta->observaciones_generales }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                            
                            <td class="text-center pr-4">
                                <a href="{{ route('altas.show', $alta->id) }}" class="btn btn-sm btn-info text-white shadow-sm rounded-pill px-3 font-weight-bold" title="Abrir Expediente">
                                    <i class="fas fa-folder-open mr-1"></i> Expediente
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']) ? 7 : 6 }}" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-gray-300 d-block"></i>
                                No hay altas registradas con esos filtros.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3 rounded-bottom">
                {{ $altas->appends(["texto" => $texto ?? '', "estado" => $estatusFiltro, "agente_id" => $agenteFiltro])->links() }}
            </div>
        </div>

    </div>
</div>

{{-- MODAL: Selección de Tipo de Alta --}}
<div class="modal fade" id="modalTipoAlta" tabindex="-1" aria-labelledby="modalTipoAltaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold" id="modalTipoAltaLabel"><i class="fas fa-clipboard-list mr-2"></i>Seleccione el Tipo de Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('altas.create', ['tipo' => 'contado']) }}" class="btn btn-outline-success bg-white w-100 p-4 h-100 d-flex flex-column justify-content-center align-items-center shadow-sm transition-hover" style="border-radius: 10px;">
                            <i class="fas fa-money-bill-wave fs-1 mb-2"></i>
                            <span class="font-weight-bold">De Contado</span>
                            <small class="text-muted mt-2" style="font-size:0.75rem; white-space:normal;">Trámite rápido. Solo datos básicos y Constancia Fiscal.</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('altas.create', ['tipo' => 'credito']) }}" class="btn btn-outline-primary bg-white w-100 p-4 h-100 d-flex flex-column justify-content-center align-items-center shadow-sm transition-hover" style="border-radius: 10px;">
                            <i class="fas fa-hand-holding-usd fs-1 mb-2"></i>
                            <span class="font-weight-bold">De Crédito</span>
                            <small class="text-muted mt-2" style="font-size:0.75rem; white-space:normal;">Proceso completo. Requiere referencias y expediente total.</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('estilos')
<style>
    .transition-hover { transition: all 0.2s ease-in-out; cursor: pointer; }
    .transition-hover:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,.1)!important; }
</style>
@endpush