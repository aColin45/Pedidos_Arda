@extends('plantilla.app')

@section('contenido')
<div class="app-content">
    <div class="container-fluid mt-4">
        <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
            <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                <h3 class="card-title font-weight-bold text-primary mb-0">
                    <i class="fas fa-route mr-2"></i> Bitácora de Visitas y Seguimiento
                </h3>
            </div>
            <div class="card-body">
                
                {{-- BARRA DE BÚSQUEDA Y BOTONES --}}
                <div>
                    <form action="{{ route('visitas.index') }}" method="get">
                        <div class="input-group mb-3 shadow-sm">
                            <input name="texto" type="text" class="form-control border-right-0" value="{{ $texto ?? '' }}"
                                placeholder="Buscar por cliente, prospecto, objetivo o resultado...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                                
                                <a href="{{ route('visitas.exportar') }}" class="btn btn-success ml-2 font-weight-bold" title="Descargar reporte a Excel">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>

                                <a href="{{ route('visitas.create') }}" class="btn btn-primary ml-2 font-weight-bold">
                                    <i class="fas fa-plus"></i> Registrar Visita
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                @if(Session::has('mensaje'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm">
                    <i class="fas fa-check-circle mr-1"></i> {{ Session::get('mensaje') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light text-gray-700">
                            <tr>
                                <th class="border-0 pl-3">ID</th>
                                <th class="border-0">Fecha y Hora</th>
                                @role('admin|superadmin')
                                <th class="border-0">Agente</th>
                                @endrole
                                <th class="border-0">Negocio Visitado</th>
                                <th class="border-0">Objetivo / Resultado</th>
                                <th class="border-0">Próximo Contacto</th>
                                <th class="border-0 text-center">Opciones</th>
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

                                @role('admin|superadmin')
                                <td><div class="text-dark font-weight-bold"><i class="fas fa-user-circle text-gray-400 mr-1"></i> {{ $reg->agente->name ?? 'N/A' }}</div></td>
                                @endrole

                                <td>
                                    @if($reg->es_prospecto)
                                        <span class="badge bg-info text-dark mb-1">Prospecto</span><br>
                                        <span class="font-weight-bold text-dark">{{ $reg->prospecto->razon_social ?? 'N/A' }}</span>
                                    @else
                                        <span class="badge bg-success mb-1">Cliente</span><br>
                                        <span class="font-weight-bold text-dark">{{ $reg->cliente->nombre ?? 'N/A' }}</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="small font-weight-bold text-primary mb-1">Obj: {{ $reg->objetivo_visita }}</div>
                                    <div class="small text-dark">
                                        @if($reg->resultado_visita == 'Venta / Pedido') <span class="badge bg-success"><i class="fas fa-dollar-sign mr-1"></i> Venta</span>
                                        @elseif($reg->resultado_visita == 'Cotización') <span class="badge bg-warning text-dark"><i class="fas fa-file-invoice-dollar mr-1"></i> Cotización</span>
                                        @elseif($reg->resultado_visita == 'No interesado (Rechazo)') <span class="badge bg-danger text-white"><i class="fas fa-times-circle mr-1"></i> Rechazo</span>
                                        @else <span class="badge bg-secondary">{{ $reg->resultado_visita }}</span>
                                        @endif
                                    </div>
                                    
                                    {{-- ALERTA DE MOTIVO DE RECHAZO --}}
                                    @if($reg->resultado_visita == 'No interesado (Rechazo)' && !empty($reg->motivo_rechazo))
                                        <div class="mt-2 shadow-sm" style="max-width: 200px; background-color: #fff0f0; border-left: 3px solid #dc3545; padding: 6px 8px; border-radius: 4px; text-align: left !important;">
                                            <div class="text-danger font-weight-bold" style="font-size: 0.70rem; margin-bottom: 2px;">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Motivo:
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
                                    {{-- Botón PDF Individual --}}
                                    <a href="{{ route('visitas.pdf', $reg->id) }}" target="_blank" class="btn btn-sm btn-danger shadow-sm" title="Descargar Ficha PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    <a href="{{ route('visitas.edit', $reg->id) }}" class="btn btn-sm btn-info shadow-sm" title="Editar Detalles">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @role('admin|superadmin')
                                    <form action="{{ route('visitas.destroy', $reg->id) }}" method="POST" class="d-inline form-confirmar"
                                          data-title="¿Eliminar Visita?" data-text="Se borrará el reporte de esta visita." data-icon="warning" data-color="#dc3545" data-btn-text="Sí, eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                    @endrole
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="{{ auth()->user()->hasRole('admin') ? '7' : '6' }}" class="text-center py-4 text-muted"><i class="fas fa-calendar-times fa-2x mb-3 text-gray-300 d-block"></i> No hay visitas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0" style="border-radius: 0 0 12px 12px;">
                {{ $registros->appends(["texto" => $texto ?? ''])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection