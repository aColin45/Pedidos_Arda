@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Clientes</h3>
                </div>
                <div class="card-body">
                    {{-- Formulario de Búsqueda --}}
                    <div>
                        <form action="{{ route('clientes.index') }}" method="get">
                            <div class="input-group">
                                <input name="texto" type="text" class="form-control" value="{{ $texto ?? '' }}"
                                    placeholder="Buscar por Código, Nombre de Cliente o Agente">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i>
                                        Buscar</button>
                                    @can('cliente-create')
                                    <a href="{{ route('clientes.create') }}" class="btn btn-primary"> Nuevo</a>
                                    @endcan
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Mensajes de Sesión --}}
                    @if(Session::has('mensaje'))
                    <div class="alert alert-info alert-dismissible fade show mt-2">{{ Session::get('mensaje') }}</div>
                    @endif

                    {{-- Lógica de Ordenación --}}
                    @php
                        $currentSort = $sort ?? 'nombreAsc'; 
                        $isAsc = $currentSort == 'codigoAsc';
                        $newSort = $isAsc ? 'codigoDesc' : 'codigoAsc'; 
                        $query = ['texto' => $texto, 'sort' => $newSort]; 
                    @endphp

                    {{-- Tabla de Clientes --}}
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    @role('admin')
                                    <th style="width: 150px">Opciones</th>
                                    @endrole
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    
                                    {{-- COLUMNA CÓDIGO (HACER CLICKEABLE PARA ORDENAR) --}}
                                    <th>
                                        <a href="{{ route('clientes.index', array_merge(request()->query(), ['sort' => $newSort])) }}" class="text-decoration-none text-dark">
                                            Código
                                            @if ($currentSort == 'codigoAsc')
                                                <i class="bi bi-caret-up-fill small"></i>
                                            @elseif ($currentSort == 'codigoDesc')
                                                <i class="bi bi-caret-down-fill small"></i>
                                            @endif
                                        </a>
                                    </th>

                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Agente Asignado</th>
                                    <th>Activo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientes as $cliente)
                                <tr>
                                    @role('admin')
                                    {{-- Columna Opciones: Orden idéntico a Usuarios (Editar -> Eliminar -> Toggle) --}}
                                    <td style="width: 150px;">
                                        <div class="d-inline-flex">
                                            {{-- 1. Botón EDITAR (Azul/Info) --}}
                                            @can('cliente-edit')
                                            <a href="{{ route('clientes.edit', $cliente->id) }}"
                                                class="btn btn-info btn-sm me-1" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            @endcan

                                            {{-- 2. Botón ELIMINAR (Rojo/Danger) --}}
                                            @can('cliente-delete')
                                            <button class="btn btn-danger btn-sm me-1" data-bs-toggle="modal"
                                                    data-bs-target="#modal-eliminar-{{ $cliente->id }}" title="Eliminar">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                            @endcan

                                            {{-- 3. Botón TOGGLE (Amarillo/Verde) --}}
                                            @can('cliente-edit')
                                            <button class="btn {{ $cliente->activo ? 'btn-warning' : 'btn-success' }} btn-sm"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modal-toggle-{{ $cliente->id }}" 
                                                    title="{{ $cliente->activo ? 'Inhabilitar' : 'Activar' }}">
                                                <i class="bi {{$cliente->activo ? 'bi-ban' : 'bi-check-circle'}}"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                    @endrole

                                    <td>{{ $cliente->id }}</td>
                                    <td>{{ $cliente->nombre }}</td>
                                    <td>{{ $cliente->codigo }}</td>
                                    <td>{{ $cliente->email }}</td>
                                    <td>{{ $cliente->telefono }}</td>
                                    <td>
                                        {{ $cliente->agente->name ?? 'SIN AGENTE' }}
                                    </td>
                                    
                                    {{-- Columna Activo: Estilo Badge (Activo/Inactivo) --}}
                                    <td>
                                        <span class="badge bg-{{ $cliente->activo ? 'success' : 'danger' }}">
                                            {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                </tr>

                                {{-- INCLUIR MODALES (Si el usuario es admin para la eliminación y toggle) --}}
                                @role('admin')
                                @include('cliente.delete', ['cliente' => $cliente])
                                @include('cliente.activate', ['cliente' => $cliente])
                                @endrole

                                @empty
                                <tr>
                                    {{-- Determinar el colspan basado en si se muestra la columna Opciones --}}
                                    <td colspan="{{ auth()->user()->hasRole('admin') ? 8 : 7 }}">No hay clientes registrados que coincidan con la búsqueda.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    {{ $clientes->appends(["texto"=>$texto ?? '', "sort"=>$currentSort])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection