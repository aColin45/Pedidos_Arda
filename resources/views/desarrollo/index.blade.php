@extends('plantilla.app')

@section('contenido')
<div class="app-content">
    <div class="container-fluid mt-4">
        <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
            <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                <h3 class="card-title font-weight-bold text-primary mb-0">
                    <i class="fas fa-lightbulb mr-2"></i> Solicitudes de Nuevos Productos y Desarrollos
                </h3>
            </div>
            <div class="card-body">
                
                {{-- BARRA DE BÚSQUEDA Y BOTONES --}}
                <div>
                    <form action="{{ route('desarrollos.index') }}" method="get">
                        <div class="input-group mb-3 shadow-sm">
                            <input name="texto" type="text" class="form-control border-right-0" value="{{ $texto ?? '' }}"
                                placeholder="Buscar en la descripción o producto relacionado...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                                
                                <a href="{{ route('desarrollos.exportar') }}" class="btn btn-success ml-2 font-weight-bold" title="Descargar reporte a Excel">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>

                                <a href="{{ route('desarrollos.create') }}" class="btn btn-primary ml-2 font-weight-bold">
                                    <i class="fas fa-plus"></i> Nueva Solicitud
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
                                <th class="border-0">Fecha</th>
                                @role('admin|superadmin')
                                <th class="border-0">Agente</th>
                                @endrole
                                <th class="border-0">Tipo de Solicitud</th>
                                <th class="border-0" style="max-width: 250px;">Descripción General</th>
                                <th class="border-0 text-center">Estatus</th>
                                <th class="border-0 text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $reg)
                            <tr>
                                <td class="pl-3 font-weight-bold text-secondary">#{{ $reg->id }}</td>
                                
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $reg->created_at->format('d/m/Y') }}</div>
                                </td>

                                @role('admin|superadmin')
                                <td><div class="text-dark font-weight-bold"><i class="fas fa-user-circle text-gray-400 mr-1"></i> {{ $reg->agente->name ?? 'N/A' }}</div></td>
                                @endrole

                                <td>
                                    @if(str_contains(strtolower($reg->tipo_accion), 'nuevo'))
                                        <span class="badge bg-primary px-2 py-1"><i class="fas fa-star mr-1"></i> Producto Nuevo</span>
                                    @else
                                        <span class="badge bg-info text-dark px-2 py-1"><i class="fas fa-tools mr-1"></i> Modificación</span>
                                    @endif
                                </td>

                                <td class="text-wrap" style="max-width: 250px;">
                                    <div class="small font-weight-bold text-dark">{{ $reg->producto_especifico ?? $reg->linea_producto }}</div>
                                    <div class="small text-muted text-truncate" title="{{ $reg->descripcion }}">{{ $reg->descripcion }}</div>
                                </td>

                                <td class="text-center">
                                    @php $est = strtolower($reg->estatus ?? ''); @endphp
                                    @if(str_contains($est, 'aprobado')) 
                                        <span class="badge bg-success shadow-sm px-3 py-1">Aprobado / Desarrollo</span>
                                    @elseif(str_contains($est, 'rechazado')) 
                                        <span class="badge bg-danger shadow-sm px-3 py-1">Rechazado / No Viable</span>
                                    @else 
                                        <span class="badge bg-warning text-dark shadow-sm px-3 py-1">Pendiente / Revisión</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    {{-- Botón Modal de Estatus (Solo Admin) --}}
                                    @role('admin|superadmin')
                                    <button type="button" class="btn btn-sm btn-warning shadow-sm text-dark font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalEstatus-{{ $reg->id }}" title="Evaluar Solicitud">
                                        <i class="fas fa-clipboard-check"></i>
                                    </button>
                                    @endrole

                                    {{-- Botón PDF Individual --}}
                                    <a href="{{ route('desarrollos.pdf', $reg->id) }}" target="_blank" class="btn btn-sm btn-danger shadow-sm" title="Descargar Ficha PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    <a href="{{ route('desarrollos.edit', $reg->id) }}" class="btn btn-sm btn-info shadow-sm" title="Editar Detalles">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @role('admin|superadmin')
                                    <form action="{{ route('desarrollos.destroy', $reg->id) }}" method="POST" class="d-inline form-confirmar"
                                          data-title="¿Eliminar Solicitud?" data-text="Se borrará permanentemente." data-icon="warning" data-color="#dc3545" data-btn-text="Sí, eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                    @endrole
                                </td>
                            </tr>

                            {{-- MODAL EVALUACIÓN DE ESTATUS (Admin) --}}
                            @role('admin|superadmin')
                            <div class="modal fade" id="modalEstatus-{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('desarrollos.cambiar_estado', $reg->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title font-weight-bold"><i class="fas fa-clipboard-check mr-2"></i>Panel de Evaluación (Admin)</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p>Resolución de la Solicitud #{{ $reg->id }}:</p>
                                                <select name="estatus" class="form-select form-control font-weight-bold">
                                                    <option value="Pendiente / En Revisión" {{ $est == 'pendiente / en revisión' ? 'selected' : '' }}>🟡 Pendiente / En Revisión</option>
                                                    <option value="Aprobado / En Desarrollo" {{ $est == 'aprobado / en desarrollo' ? 'selected' : '' }}>🟢 Aprobado / En Desarrollo</option>
                                                    <option value="Rechazado / No Viable" {{ $est == 'rechazado / no viable' ? 'selected' : '' }}>🔴 Rechazado / No Viable</option>
                                                </select>
                                            </div>
                                            <div class="modal-footer border-top-0">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-dark"><i class="fas fa-save mr-1"></i> Guardar Resolución</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endrole

                            @empty
                            <tr><td colspan="{{ auth()->user()->hasRole('admin') ? '7' : '6' }}" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-3 text-gray-300 d-block"></i> No hay solicitudes registradas.</td></tr>
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