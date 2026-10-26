@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid mt-4">

        {{-- ======================================================== --}}
        {{-- TARJETAS KPI COMO FILTROS RÁPIDOS INTERACTIVOS (5 COLUMNAS) --}}
        {{-- ======================================================== --}}
        <div class="row mb-4">
            {{-- 1. TOTAL DEVOLUCIONES --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('devoluciones.index', ['estado' => '', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ empty($estadoFiltro) ? 'bg-primary text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #4e73df !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ empty($estadoFiltro) ? 'text-white' : 'text-primary' }}" style="font-size: 0.70rem;">Total Solicitudes</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['total'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-undo-alt fa-2x {{ empty($estadoFiltro) ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 2. PENDIENTES DE REVISIÓN --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('devoluciones.index', ['estado' => 'devolucion_pendiente', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estadoFiltro == 'devolucion_pendiente' ? 'bg-warning text-dark shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #f6c23e !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estadoFiltro == 'devolucion_pendiente' ? 'text-dark' : 'text-warning' }}" style="font-size: 0.70rem;">Pendientes</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['pendientes'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-clock fa-2x {{ $estadoFiltro == 'devolucion_pendiente' ? 'text-dark' : 'text-gray-300' }}" style="{{ $estadoFiltro == 'devolucion_pendiente' ? 'opacity: 0.5;' : '' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 3. EN PROCESO DE ALMACÉN --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('devoluciones.index', ['estado' => 'devolucion_aprobada', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estadoFiltro == 'devolucion_aprobada' ? 'bg-info text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #36b9cc !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estadoFiltro == 'devolucion_aprobada' ? 'text-white' : 'text-info' }}" style="font-size: 0.70rem;">En Proceso Almacén</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['en_proceso'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-boxes fa-2x {{ $estadoFiltro == 'devolucion_aprobada' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 4. FINALIZADAS (NOTA DE CRÉDITO) --}}
            <div class="col-xl col-md-6 col-sm-6 mb-2">
                <a href="{{ route('devoluciones.index', ['estado' => 'devolucion_finalizada', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estadoFiltro == 'devolucion_finalizada' ? 'bg-success text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #1cc88a !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estadoFiltro == 'devolucion_finalizada' ? 'text-white' : 'text-success' }}" style="font-size: 0.70rem;">Finalizadas</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['finalizadas'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-check-double fa-2x {{ $estadoFiltro == 'devolucion_finalizada' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 5. CANCELADAS / RECHAZADAS --}}
            <div class="col-xl col-md-6 col-sm-6 mb-2">
                <a href="{{ route('devoluciones.index', ['estado' => 'canceladas_rechazadas', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estadoFiltro == 'canceladas_rechazadas' ? 'bg-danger text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #e74a3b !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estadoFiltro == 'canceladas_rechazadas' ? 'text-white' : 'text-danger' }}" style="font-size: 0.70rem;">Canceladas / Rechazadas</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['canceladas_rechazadas'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-ban fa-2x {{ $estadoFiltro == 'canceladas_rechazadas' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
            <div class="card-header py-3 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                <h3 class="card-title font-weight-bold text-primary mb-3 mb-md-0">
                    <i class="fas fa-undo-alt mr-2"></i> Gestión de Devoluciones
                </h3>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('devoluciones.create') }}" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-plus-circle mr-1"></i> Nuevo Formato
                    </a>
                </div>
            </div>
            
            <div class="card-body bg-light border-bottom">
                {{-- BARRA DE BÚSQUEDA Y FILTROS AVANZADOS --}}
                <form action="{{ route('devoluciones.index') }}" method="GET">
                    <div class="row g-2">
                        {{-- Filtro Estatus Oculto --}}
                        <input type="hidden" name="estado" value="{{ $estadoFiltro }}">

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
                                <input name="texto" type="text" class="form-control border-start-0" value="{{ $texto }}" placeholder="Buscar por Folio, Cliente, Factura o Agente...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm font-weight-bold"><i class="fas fa-search mr-1"></i> Buscar</button>
                        </div>
                    </div>
                </form>
            </div>

            @if(Session::has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm m-3 border-0">
                <i class="fas fa-check-circle mr-1"></i> {{ Session::get('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-white text-muted">
                        <tr>
                            <th class="text-center border-bottom-0 pl-4" width="5%">#</th>
                            <th class="border-bottom-0" width="10%">Fecha</th>
                            <th class="border-bottom-0" width="25%">Cliente</th>
                            <th class="border-bottom-0" width="15%">Factura a la que pertenece</th>
                            <th class="border-bottom-0" width="20%">Motivo</th>
                            <th class="border-bottom-0 text-right" width="10%">Total</th>
                            <th class="border-bottom-0 text-center" width="10%">Estado</th>
                            <th class="border-bottom-0 text-center pr-4" width="5%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $dev)
                        <tr>
                            <td class="text-center font-weight-bold text-secondary pl-4 align-middle">{{ $dev->id }}</td>
                            <td class="align-middle">
                                <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                                {{ $dev->created_at->format('d/m/Y') }}
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark text-wrap" style="max-width: 250px;">{{ $dev->cliente->nombre ?? 'N/A' }}</div>
                                <div class="small text-gray-500"><i class="fas fa-user-tie mr-1 text-gray-400"></i>{{ $dev->agente->name ?? 'Sin Agente' }}</div>
                            </td>
                            <td class="align-middle">
                                <span class="badge border shadow-sm px-2 py-1" style="background-color: #ffffff !important; color: #4e73df !important; font-size: 0.85em;">
                                    <i class="fas fa-file-invoice mr-1"></i> {{ $dev->factura_extraida }}
                                </span>
                            </td>
                            <td class="align-middle">
                                <span class="text-dark small d-block text-wrap" style="max-width: 200px;">{{ Str::limit($dev->motivo_extraido, 35) }}</span>
                            </td>
                            <td class="text-right font-weight-bold text-success align-middle">${{ number_format($dev->total, 2) }}</td>
                            <td class="text-center align-middle">
                                @php
                                    $estilo = '';
                                    $textoEstatus = '';
                                    
                                    switch($dev->estado) {
                                        case 'devolucion_pendiente': 
                                            $estilo = 'background-color: #f6c23e !important; color: #333 !important;'; 
                                            $textoEstatus = 'Pendiente'; break;
                                        case 'devolucion_aprobada': 
                                            $estilo = 'background-color: #36b9cc !important; color: #fff !important;'; 
                                            $textoEstatus = 'En Proceso'; break;
                                        case 'devolucion_finalizada': 
                                            $estilo = 'background-color: #1cc88a !important; color: #fff !important;'; 
                                            $textoEstatus = 'Finalizada'; break;
                                        case 'devolucion_rechazada': 
                                            $estilo = 'background-color: #e74a3b !important; color: #fff !important;'; 
                                            $textoEstatus = 'Rechazada'; break;
                                        case 'devolucion_cancelada': 
                                            $estilo = 'background-color: #858796 !important; color: #fff !important;'; 
                                            $textoEstatus = 'Cancelada'; break;
                                        default:
                                            $estilo = 'background-color: #858796 !important; color: #fff !important;';
                                            $textoEstatus = $dev->estado;
                                    }
                                @endphp
                                <span class="badge px-3 py-2 shadow-sm" style="{{ $estilo }} font-size: 0.85em; opacity: 1;">
                                    {{ $textoEstatus }}
                                </span>

                                {{-- HUELLA DE RASTREO --}}
                                @if($dev->validador_id && $dev->estado != 'devolucion_pendiente')
                                    @php
                                        $accionTexto = 'Actualizó';
                                        if ($dev->estado == 'devolucion_aprobada') $accionTexto = 'Aprobó';
                                        elseif ($dev->estado == 'devolucion_rechazada') $accionTexto = 'Rechazó';
                                        elseif ($dev->estado == 'devolucion_finalizada') $accionTexto = 'Finalizó';
                                        elseif ($dev->estado == 'devolucion_cancelada') $accionTexto = 'Canceló';
                                    @endphp
                                    <div class="mt-2 text-start shadow-sm" style="max-width: 180px; margin: 0 auto; background-color: #f8f9fa; border-left: 3px solid #6c757d; padding: 4px 6px; border-radius: 4px; font-size: 0.65rem;">
                                        <span class="text-muted d-block text-truncate">
                                            <i class="fas fa-user-check"></i> {{ $accionTexto }}: <strong>{{ $dev->validador->name ?? 'Admin' }}</strong>
                                        </span>
                                        <span class="text-muted d-block mt-1">
                                            <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($dev->fecha_validacion)->format('d/m/y H:i') }}
                                        </span>
                                    </div>
                                    @if($dev->estado == 'devolucion_rechazada' && $dev->motivo_rechazo)
                                        <div class="mt-1 shadow-sm text-start" style="max-width: 180px; margin: 0 auto; background-color: #fff0f0; border-left: 3px solid #dc3545; padding: 4px 6px; border-radius: 4px;">
                                            <div class="text-danger font-weight-bold" style="font-size: 0.65rem; margin-bottom: 1px;"><i class="fas fa-exclamation-circle mr-1"></i>Motivo:</div>
                                            <div class="text-dark" style="font-size: 0.70rem; white-space: normal; word-wrap: break-word;">{{ $dev->motivo_rechazo }}</div>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center align-middle pr-4">
                                <a href="{{ route('devoluciones.show', $dev->id) }}" class="btn btn-sm btn-info shadow-sm text-white rounded-pill px-3 font-weight-bold" title="Ver Detalles">
                                    <i class="fas fa-eye mr-1"></i> Abrir
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-undo-alt fa-3x mb-3 text-gray-300 d-block"></i>
                                No se encontraron devoluciones con esos filtros.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white border-0 py-3 rounded-bottom">
                {{ $registros->appends(["texto" => $texto, "estado" => $estadoFiltro, "agente_id" => $agenteFiltro])->links() }}
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