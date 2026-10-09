@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid">

        {{-- Encabezado --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold"><i class="fas fa-user-shield text-primary mr-2"></i>Control de Roles y Permisos</h1>
                <p class="text-muted small mb-0">Gestione los niveles de acceso y los módulos permitidos para cada tipo de usuario.</p>
            </div>
        </div>

        {{-- Alertas --}}
        @if(Session::has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ Session::get('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        {{-- Tarjeta de Listado --}}
        <div class="card shadow border-0" style="border-radius: 12px; border-top: 4px solid #4e73df !important;">
            <div class="card-header bg-white py-3 d-flex flex-column flex-md-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary mb-3 mb-md-0"><i class="fas fa-list-ul mr-2"></i>Roles del Sistema</h6>
                
                {{-- Buscador y Botón Nuevo --}}
                <form action="{{ route('roles.index') }}" method="GET" class="d-flex w-100 w-md-auto">
                    <div class="input-group shadow-sm">
                        <input type="text" name="texto" class="form-control border-end-0" placeholder="Buscar rol..." value="{{ $texto }}">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        @can('rol-create')
                            <a href="{{ route('roles.create') }}" class="btn btn-success fw-bold ms-2 rounded"><i class="fas fa-plus mr-1"></i> Nuevo Rol</a>
                        @endcan
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-gray-700">
                            <tr>
                                <th class="border-0 pl-4 text-center" width="5%">ID</th>
                                <th class="border-0" width="15%">Nombre del Rol</th>
                                <th class="border-0" width="65%">Permisos Asignados</th>
                                <th class="border-0 text-center pr-4" width="15%">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($registros) <= 0)
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-2x mb-3 d-block opacity-50"></i>
                                        No hay registros que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            @else
                                @foreach ($registros as $reg)
                                <tr>
                                    <td class="pl-4 text-center text-muted font-weight-bold">{{ $reg->id }}</td>
                                    <td>
                                        <span class="badge bg-dark px-3 py-2 text-uppercase shadow-sm" style="font-size: 0.85rem;">
                                            {{ $reg->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 mt-1 mb-1">
                                            @forelse($reg->permissions as $permiso)
                                                @php
                                                    $modulo = explode('-', $permiso->name)[0];
                                                    $colorBadge = match($modulo) {
                                                        'user', 'rol' => 'bg-info text-white',
                                                        'pedido', 'cotizacion' => 'bg-success text-white',
                                                        'producto', 'inventario' => 'bg-warning text-dark',
                                                        'cliente' => 'bg-primary text-white',
                                                        default => 'bg-secondary text-white'
                                                    };
                                                @endphp
                                                <span class="badge {{ $colorBadge }} shadow-sm border" style="font-size: 0.75rem;">
                                                    {{ $permiso->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted small fst-italic">Sin permisos asignados</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="text-center pr-4">
                                        <div class="d-inline-flex shadow-sm rounded">
                                            @can('rol-edit')
                                            <a href="{{ route('roles.edit', $reg->id) }}" class="btn btn-sm btn-outline-primary" title="Editar Rol">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            
                                            @can('rol-delete')
                                            <form action="{{ route('roles.destroy', $reg->id) }}" method="POST" class="d-inline form-confirmar" 
                                                  data-title="¿Eliminar Rol?" 
                                                  data-text="Se eliminará permanentemente el rol '{{$reg->name}}'. Esta acción no se puede deshacer." 
                                                  data-icon="warning" 
                                                  data-color="#dc3545" 
                                                  data-btn-text="Sí, eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-end" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" title="Eliminar Rol">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 border-0">
                <div class="d-flex justify-content-center">
                    {{ $registros->appends(["texto" => $texto])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('mnuSeguridad').classList.add('menu-open');
    document.getElementById('itemRole').classList.add('active');
</script>
@endpush