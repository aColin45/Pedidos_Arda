@extends('plantilla.app')

@section('contenido')
<div class="app-content">
    <div class="container-fluid mt-4">

        {{-- ======================================================== --}}
        {{-- TARJETAS KPI COMO FILTROS RÁPIDOS INTERACTIVOS           --}}
        {{-- ======================================================== --}}
        <div class="row mb-4">
            {{-- 1. TOTAL VISITAS --}}
            <div class="col-xl-3 col-md-6 mb-2">
                <a href="{{ route('visitas.index', ['resultado' => '', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ empty($resultadoFiltro) ? 'bg-primary text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #4e73df !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ empty($resultadoFiltro) ? 'text-white' : 'text-primary' }}" style="font-size: 0.70rem;">Todas las Visitas</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['total'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-route fa-2x {{ empty($resultadoFiltro) ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 2. VENTAS CERRADAS --}}
            <div class="col-xl-3 col-md-6 mb-2">
                <a href="{{ route('visitas.index', ['resultado' => 'Venta / Pedido', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $resultadoFiltro == 'Venta / Pedido' ? 'bg-success text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #1cc88a !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $resultadoFiltro == 'Venta / Pedido' ? 'text-white' : 'text-success' }}" style="font-size: 0.70rem;">Ventas Cerradas</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['ventas'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-dollar-sign fa-2x {{ $resultadoFiltro == 'Venta / Pedido' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 3. COTIZACIONES --}}
            <div class="col-xl-3 col-md-6 mb-2">
                <a href="{{ route('visitas.index', ['resultado' => 'Cotización', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $resultadoFiltro == 'Cotización' ? 'bg-warning text-dark shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #f6c23e !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $resultadoFiltro == 'Cotización' ? 'text-dark' : 'text-warning' }}" style="font-size: 0.70rem;">Cotizaciones Entregadas</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['cotizaciones'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x {{ $resultadoFiltro == 'Cotización' ? 'text-dark' : 'text-gray-300' }}" style="{{ $resultadoFiltro == 'Cotización' ? 'opacity: 0.5;' : '' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 4. RECHAZOS --}}
            <div class="col-xl-3 col-md-6 mb-2">
                <a href="{{ route('visitas.index', ['resultado' => 'No interesado (Rechazo)', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $resultadoFiltro == 'No interesado (Rechazo)' ? 'bg-danger text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #e74a3b !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $resultadoFiltro == 'No interesado (Rechazo)' ? 'text-white' : 'text-danger' }}" style="font-size: 0.70rem;">Rechazos (No Interesados)</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['rechazos'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-times-circle fa-2x {{ $resultadoFiltro == 'No interesado (Rechazo)' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
            <div class="card-header py-3 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                <h3 class="card-title font-weight-bold text-primary mb-3 mb-md-0">
                    <i class="fas fa-route mr-2"></i> Bitácora de Visitas y Seguimiento
                </h3>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('visitas.exportar') }}" class="btn btn-success font-weight-bold shadow-sm" title="Descargar reporte a Excel">
                        <i class="fas fa-file-excel mr-1"></i> Excel
                    </a>
                    @can('visita-create')
                    <a href="{{ route('visitas.create') }}" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Registrar Visita
                    </a>
                    @endcan
                </div>
            </div>
            
            <div class="card-body bg-light border-bottom">
                {{-- BARRA DE BÚSQUEDA Y FILTROS AVANZADOS --}}
                <form action="{{ route('visitas.index') }}" method="get">
                    <div class="row g-2">
                        {{-- Filtro Resultado Oculto --}}
                        <input type="hidden" name="resultado" value="{{ $resultadoFiltro }}">

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
                                <input name="texto" type="text" class="form-control border-start-0" value="{{ $texto ?? '' }}" placeholder="Buscar por cliente, prospecto o detalles de la visita...">
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
                    <thead class="bg-white text-gray-700">
                        <tr>
                            <th class="border-bottom-0 pl-3">ID</th>
                            <th class="border-bottom-0">Fecha y Hora</th>
                            @can('visita-delete')
                            <th class="border-bottom-0">Agente</th>
                            @endcan
                            <th class="border-bottom-0">Negocio Visitado</th>
                            <th class="border-bottom-0">Objetivo / Resultado</th>
                            <th class="border-bottom-0">Próximo Contacto</th>
                            <th class="border-bottom-0 text-center">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $reg)
                        <tr>
                            <td class="pl-3 font-weight-bold text-secondary">#{{ $reg->id }}</td>
                            
                            <td>
                                <div class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($reg->fecha_visita)->format('d/m/Y') }}</div>
                                <small class="text-muted d-block mb-1"><i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($reg->fecha_visita)->format('h:i A') }}</small>
                                @if($reg->modo_contacto)
                                    <span class="badge bg-info text-dark shadow-sm" style="font-size: 0.65rem;">{{ $reg->modo_contacto }}</span>
                                @endif
                            </td>

                            @can('visita-delete')
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mr-2" style="width: 30px; height: 30px;"><i class="fas fa-user text-primary"></i></div>
                                    <span class="font-weight-bold text-dark">{{ $reg->agente->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            @endcan

                            <td>
                                @if($reg->es_prospecto)
                                    <span class="badge bg-info text-dark mb-1">Prospecto</span><br>
                                    <span class="font-weight-bold text-dark text-wrap d-block" style="max-width: 250px;">{{ $reg->prospecto->razon_social ?? 'N/A' }}</span>
                                @else
                                    <span class="badge bg-success mb-1">Cliente Oficial</span><br>
                                    <span class="font-weight-bold text-dark text-wrap d-block" style="max-width: 250px;">{{ $reg->cliente->nombre ?? 'N/A' }}</span>
                                @endif
                            </td>

                            <td>
                                <div class="small font-weight-bold text-primary mb-1">Obj: {{ $reg->objetivo_visita }}</div>
                                <div class="small text-dark">
                                    @if($reg->resultado_visita == 'Venta / Pedido') <span class="badge bg-success shadow-sm"><i class="fas fa-dollar-sign mr-1"></i> Venta Lograda</span>
                                    @elseif($reg->resultado_visita == 'Cotización') <span class="badge bg-warning text-dark shadow-sm"><i class="fas fa-file-invoice-dollar mr-1"></i> Entregó Cotización</span>
                                    @elseif($reg->resultado_visita == 'No interesado (Rechazo)') <span class="badge bg-danger text-white shadow-sm"><i class="fas fa-times-circle mr-1"></i> Rechazo</span>
                                    @else <span class="badge bg-secondary shadow-sm">{{ $reg->resultado_visita }}</span>
                                    @endif
                                </div>
                                
                                {{-- ALERTA DE MOTIVO DE RECHAZO --}}
                                @if($reg->resultado_visita == 'No interesado (Rechazo)' && !empty($reg->motivo_rechazo))
                                    <div class="mt-2 shadow-sm" style="max-width: 220px; background-color: #fff0f0; border-left: 3px solid #dc3545; padding: 6px 8px; border-radius: 4px; text-align: left !important;">
                                        <div class="text-danger font-weight-bold" style="font-size: 0.70rem; margin-bottom: 2px;">
                                            <i class="fas fa-exclamation-circle mr-1"></i>Motivo de Rechazo:
                                        </div>
                                        <div class="text-dark" style="font-size: 0.75rem; white-space: normal; word-wrap: break-word;">
                                            {{ $reg->motivo_rechazo }}
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($reg->fecha_proximo_seguimiento)
                                    <div class="text-dark font-weight-bold"><i class="far fa-calendar-check text-success mr-1"></i> {{ \Carbon\Carbon::parse($reg->fecha_proximo_seguimiento)->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-muted small">Sin programar</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('visitas.pdf', $reg->id) }}" target="_blank" class="btn btn-sm btn-danger shadow-sm" title="Descargar Ficha PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                @can('visita-edit')
                                <a href="{{ route('visitas.edit', $reg->id) }}" class="btn btn-sm btn-info shadow-sm text-white" title="Editar Detalles">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                @can('visita-delete')
                                <form action="{{ route('visitas.destroy', $reg->id) }}" method="POST" class="d-inline form-confirmar"
                                    data-title="¿Eliminar Visita?" data-text="Se borrará el reporte de esta visita." data-icon="warning" data-color="#dc3545" data-btn-text="Sí, eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ auth()->user()->can('visita-delete') ? '7' : '6' }}" class="text-center py-5 text-muted"><i class="fas fa-route fa-3x mb-3 text-gray-300 d-block"></i> No hay visitas registradas con esos filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0" style="border-radius: 0 0 12px 12px;">
                {{ $registros->appends(["texto" => $texto ?? '', "resultado" => $resultadoFiltro, "agente_id" => $agenteFiltro])->links() }}
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