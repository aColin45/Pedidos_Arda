@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid mt-4">

        {{-- ======================================================== --}}
        {{-- TARJETAS KPI COMO FILTROS RÁPIDOS INTERACTIVOS (5 COLUMNAS) --}}
        {{-- ======================================================== --}}
        <div class="row mb-4">
            {{-- 1. TOTAL --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('cotizaciones.index', ['estado' => '', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ empty($estadoFiltro) ? 'bg-primary text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #4e73df !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ empty($estadoFiltro) ? 'text-white' : 'text-primary' }}" style="font-size: 0.70rem;">Todas</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['total'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x {{ empty($estadoFiltro) ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 2. ACTIVAS --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('cotizaciones.index', ['estado' => 'activa', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estadoFiltro == 'activa' ? 'bg-info text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #36b9cc !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estadoFiltro == 'activa' ? 'text-white' : 'text-info' }}" style="font-size: 0.70rem;">Vigentes (6 Días)</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['activas'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-clock fa-2x {{ $estadoFiltro == 'activa' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 3. CONVERTIDAS A PEDIDO --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('cotizaciones.index', ['estado' => 'convertida', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estadoFiltro == 'convertida' ? 'bg-success text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #1cc88a !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estadoFiltro == 'convertida' ? 'text-white' : 'text-success' }}" style="font-size: 0.70rem;">Convertidas (Éxito)</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['convertidas'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-exchange-alt fa-2x {{ $estadoFiltro == 'convertida' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 4. RECHAZADAS --}}
            <div class="col-xl col-md-6 col-sm-6 mb-2">
                <a href="{{ route('cotizaciones.index', ['estado' => 'rechazada', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estadoFiltro == 'rechazada' ? 'bg-danger text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #e74a3b !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estadoFiltro == 'rechazada' ? 'text-white' : 'text-danger' }}" style="font-size: 0.70rem;">Canceladas</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['rechazadas'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-ban fa-2x {{ $estadoFiltro == 'rechazada' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 5. CADUCADAS --}}
            <div class="col-xl col-md-6 col-sm-6 mb-2">
                <a href="{{ route('cotizaciones.index', ['estado' => 'caducada', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estadoFiltro == 'caducada' ? 'bg-secondary text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #858796 !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estadoFiltro == 'caducada' ? 'text-white' : 'text-secondary' }}" style="font-size: 0.70rem;">Expiradas</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['caducadas'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-hourglass-end fa-2x {{ $estadoFiltro == 'caducada' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
            <div class="card-header py-3 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                <h3 class="card-title font-weight-bold text-primary mb-3 mb-md-0">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Gestión de Cotizaciones
                </h3>
            </div>
            
            <div class="card-body bg-light border-bottom">
                {{-- BARRA DE BÚSQUEDA Y FILTROS AVANZADOS --}}
                <form action="{{ route('cotizaciones.index') }}" method="GET">
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
                                <input name="texto" type="text" class="form-control border-start-0" value="{{ $texto }}" placeholder="Buscar por Folio, Cliente o Estado...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm font-weight-bold"><i class="fas fa-search mr-1"></i> Buscar</button>
                        </div>
                    </div>
                </form>
            </div>

            @if(Session::has('mensaje'))
                <div class="alert alert-success m-3 shadow-sm border-0"><i class="fas fa-check-circle mr-2"></i>{{ Session::get('mensaje') }}</div>
            @endif
            @if(Session::has('error'))
                <div class="alert alert-danger m-3 shadow-sm border-0"><i class="fas fa-exclamation-triangle mr-2"></i>{{ Session::get('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-white text-muted">
                        <tr>
                            <th class="border-bottom-0 pl-4">ID</th>
                            @hasanyrole('admin|super-admin|superadmin')
                            <th class="border-bottom-0">Agente</th>
                            @endhasanyrole
                            <th class="border-bottom-0">Cliente</th>
                            <th class="border-bottom-0 text-center">Vencimiento</th>
                            <th class="border-bottom-0 text-end">Total</th>
                            <th class="border-bottom-0 text-center">Estado</th>
                            <th class="border-bottom-0 text-center pr-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cotizaciones as $cot)
                            <tr>
                                <td class="pl-4 font-weight-bold text-secondary align-middle">#{{ $cot->id }}</td>
                                
                                @hasanyrole('admin|super-admin|superadmin')
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mr-2" style="width: 28px; height: 28px;"><i class="fas fa-user text-primary" style="font-size: 0.8rem;"></i></div>
                                        <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $cot->agente->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                @endhasanyrole

                                <td class="align-middle">
                                    <span class="font-weight-bold text-dark text-wrap d-block" style="max-width: 250px;">{{ $cot->cliente->nombre ?? 'N/A' }}</span>
                                </td>
                                
                                <td class="text-center align-middle">
                                    @if($cot->fecha_vencimiento)
                                        <div class="text-dark font-weight-bold"><i class="far fa-calendar-alt text-danger mr-1"></i> {{ \Carbon\Carbon::parse($cot->fecha_vencimiento)->format('d/m/Y') }}</div>
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                
                                <td class="text-end align-middle font-weight-bold text-success">${{ number_format($cot->total, 2) }}</td>
                                
                                <td class="text-center align-middle">
                                    @php $estadoLimpio = strtolower(trim($cot->estado)); @endphp

                                    @if(in_array($estadoLimpio, ['cotizacion', 'cotización']))
                                        <span class="badge bg-info text-dark shadow-sm px-3 py-2"><i class="fas fa-clock mr-1"></i> Activa</span>
                                    @elseif($estadoLimpio == 'cancelado')
                                        <span class="badge bg-warning text-dark shadow-sm px-3 py-2"><i class="fas fa-ban mr-1"></i> Cancelada</span>
                                    @elseif($estadoLimpio == 'no procede')
                                        <span class="badge bg-danger text-white shadow-sm px-3 py-2"><i class="fas fa-times-circle mr-1"></i> Rechazada</span>
                                        @if(!empty($cot->motivo_rechazo))
                                            <div class="mt-2 shadow-sm text-start" style="max-width: 200px; margin: 0 auto; background-color: #fff0f0; border-left: 3px solid #dc3545; padding: 6px 8px; border-radius: 4px;">
                                                <div class="text-danger font-weight-bold" style="font-size: 0.70rem; margin-bottom: 2px;"><i class="fas fa-exclamation-circle mr-1"></i>Motivo:</div>
                                                <div class="text-dark" style="font-size: 0.75rem; white-space: normal;">{{ $cot->motivo_rechazo }}</div>
                                            </div>
                                        @endif
                                    @elseif($estadoLimpio == 'caducada')
                                        <span class="badge bg-secondary text-white shadow-sm px-3 py-2"><i class="fas fa-hourglass-end mr-1"></i> Expirada</span>
                                    @elseif($estadoLimpio == 'convertida a pedido')
                                        <span class="badge bg-success text-white shadow-sm px-3 py-2"><i class="fas fa-exchange-alt mr-1"></i> Convertida</span>
                                        @if($cot->comentarios)
                                            <small class="text-muted mt-2 d-block font-weight-bold text-wrap" style="font-size: 0.75rem; max-width: 200px; margin: 0 auto;"><i class="fas fa-share mr-1"></i> {{ $cot->comentarios }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-dark shadow-sm px-3 py-2">{{ ucfirst($cot->estado) }}</span>
                                    @endif
                                </td>
                                
                                <td class="text-center align-middle pr-4">
                                    @if(in_array($estadoLimpio, ['cotizacion', 'cotización']))
                                        <div class="d-flex justify-content-center flex-wrap gap-2">
                                            <a href="{{ route('cotizaciones.pdf', $cot->id) }}" target="_blank" class="btn btn-sm btn-secondary shadow-sm" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
                                            <a href="{{ route('cotizaciones.editar', $cot->id) }}" class="btn btn-sm btn-primary shadow-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                            
                                            <button type="button" class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalConvertir{{ $cot->id }}" title="Convertir a Pedido"><i class="fas fa-check-circle"></i></button>

                                            {{-- Modal Convertir --}}
                                            <div class="modal fade text-left" id="modalConvertir{{ $cot->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                                                        <form action="{{ route('cotizaciones.convertir', $cot->id) }}" method="POST" class="form-confirmar"
                                                              data-title="¿Generar Pedido?" data-text="Se creará un nuevo pedido a partir de esta cotización." data-icon="question" data-color="#198754" data-btn-text="Sí, convertir">
                                                            @csrf
                                                            <div class="modal-header bg-success text-white" style="border-radius: 12px 12px 0 0;">
                                                                <h5 class="modal-title font-weight-bold"><i class="fas fa-truck mr-2"></i> Dirección de Entrega</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body bg-light">
                                                                <div class="alert alert-info small border-0 shadow-sm"><i class="fas fa-info-circle mr-1"></i> Opcional: Especifique dónde se entregará el pedido.</div>
                                                                <textarea name="direccion_entrega" class="form-control shadow-sm" rows="3" placeholder="Calle, Número, Colonia, C.P., Ciudad, Estado...">{{ $cot->direccion_entrega }}</textarea>
                                                            </div>
                                                            <div class="modal-footer border-top-0 bg-white" style="border-radius: 0 0 12px 12px;">
                                                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-success font-weight-bold rounded-pill px-4"><i class="fas fa-save mr-1"></i> Convertir</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <form action="{{ route('cotizaciones.cancelar', $cot->id) }}" method="POST" class="d-inline mb-0 form-confirmar"
                                                  data-title="¿Cancelar Cotización?" data-text="Esta cotización quedará marcada como cancelada." data-icon="warning" data-color="#ffc107" data-btn-text="Sí, cancelar">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning text-dark shadow-sm" title="Cancelar"><i class="fas fa-trash-alt"></i></button>
                                            </form>

                                            @can('cotizacion-delete')
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNoProcede{{ $cot->id }}" title="Marcar como No Procede"><i class="fas fa-times"></i></button>

                                                {{-- Modal No Procede --}}
                                                <div class="modal fade text-left" id="modalNoProcede{{ $cot->id }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                                                            <form action="{{ route('cotizaciones.rechazar', $cot->id) }}" method="POST" class="form-confirmar"
                                                                  data-title="¿Rechazar Cotización?" data-text="Esta cotización se marcará como 'No Procede'." data-icon="warning" data-color="#dc3545" data-btn-text="Sí, rechazar">
                                                                @csrf
                                                                <div class="modal-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                                                    <h5 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Motivo de Rechazo</h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body bg-light">
                                                                    <label class="font-weight-bold small text-muted mb-2">Indique por qué la cotización no procede:</label>
                                                                    <textarea name="motivo" class="form-control shadow-sm" required rows="3" placeholder="Ej. El cliente declinó por el precio..."></textarea>
                                                                </div>
                                                                <div class="modal-footer border-top-0 bg-white" style="border-radius: 0 0 12px 12px;">
                                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-danger font-weight-bold rounded-pill px-4">Rechazar</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1"><i class="fas fa-lock mr-1"></i> Cerrada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']) ? 7 : 6 }}" class="text-center text-muted py-5">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-gray-300 d-block"></i>
                                    No hay cotizaciones activas o en historial con estos filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3 rounded-bottom">
                {{ $cotizaciones->appends(["texto" => $texto, "estado" => $estadoFiltro, "agente_id" => $agenteFiltro])->links() }}
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