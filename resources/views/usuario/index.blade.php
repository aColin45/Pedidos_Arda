@extends('plantilla.app')
@section('contenido')
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Usuarios</h3>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="{{route('usuarios.index')}}" method="get">
                                <div class="input-group">
                                    <input name="texto" type="text" class="form-control" value="{{$texto}}"
                                        placeholder="Ingrese texto a buscar">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i>
                                            Buscar</button>
                                        @can('user-create')
                                        <a href="{{route('usuarios.create')}}" class="btn btn-primary"> Nuevo</a>
                                        @endcan
                                    </div>
                                </div>
                            </form>
                        </div>
                        @if(Session::has('mensaje'))
                        <div class="alert alert-info alert-dismissible fade show mt-2">
                            {{Session::get('mensaje')}}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
                        </div>
                        @endif
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 150px">Opciones</th>
                                        <th style="width: 20px">ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Activo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($registros)<=0)
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No hay registros que coincidan con la búsqueda</td>
                                        </tr>
                                    @else
                                        @foreach($registros as $reg)
                                            <tr class="align-middle">
                                                <td>
                                                    <div class="d-inline-flex align-items-center">
                                                        
                                                        {{-- 1. BOTÓN EDITAR --}}
                                                        @can('user-edit')
                                                        <a href="{{route('usuarios.edit', $reg->id)}}" class="btn btn-info btn-sm me-1" title="Editar">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>
                                                        @endcan

                                                        {{-- 2. BOTÓN ELIMINAR CON SWEETALERT2 --}}
                                                        @can('user-delete')
                                                        <form action="{{route('usuarios.destroy', $reg->id)}}" method="POST" class="d-inline form-confirmar"
                                                              data-title="¿Eliminar Usuario?" 
                                                              data-text="Se eliminará permanentemente a '{{$reg->name}}'. Esta acción no se puede deshacer." 
                                                              data-icon="warning" 
                                                              data-color="#dc3545" 
                                                              data-btn-text="Sí, eliminar">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm me-1" title="Eliminar">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        </form>
                                                        @endcan
                                                        
                                                        {{-- 3. BOTÓN TOGGLE (ACTIVAR/DESACTIVAR) CON SWEETALERT2 --}}
                                                        @can('user-activate')
                                                        <form action="{{route('usuarios.toggle', $reg->id)}}" method="POST" class="d-inline form-confirmar"
                                                              data-title="{{ $reg->activo ? '¿Desactivar Usuario?' : '¿Activar Usuario?' }}" 
                                                              data-text="{{ $reg->activo ? 'El usuario ya no podrá acceder al sistema.' : 'El usuario volverá a tener acceso.' }}" 
                                                              data-icon="question" 
                                                              data-color="{{ $reg->activo ? '#ffc107' : '#198754' }}" 
                                                              data-btn-text="Sí, {{ $reg->activo ? 'Desactivar' : 'Activar' }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn {{ $reg->activo ? 'btn-warning' : 'btn-success'}} btn-sm"
                                                                    title="{{ $reg->activo ? 'Desactivar' : 'Activar' }}">
                                                                <i class="bi {{$reg->activo ? 'bi-ban' : 'bi-check-circle'}}"></i>
                                                            </button>
                                                        </form>
                                                        @endcan

                                                    </div>
                                                </td>
                                                <td>{{$reg->id}}</td>
                                                <td>{{$reg->name}}</td>
                                                <td>{{$reg->email}}</td>
                                                <td>
                                                    @if($reg->roles->isNotEmpty()) 
                                                        <span class="badge bg-primary">
                                                            {{ $reg->roles->pluck('name')->implode('</span> <span class="badge bg-primary">') }} 
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Sin rol</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $reg->activo ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $reg->activo ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="card-footer clearfix">
                        {{$registros->appends(["texto"=>$texto])}}
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    document.getElementById('mnuSeguridad').classList.add('menu-open');
    document.getElementById('itemUsuario').classList.add('active');
</script>
@endpush