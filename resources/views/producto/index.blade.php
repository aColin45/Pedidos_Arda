@extends('plantilla.app')
@section('contenido')
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Productos</h3>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="{{route('productos.index')}}" method="get">
                                <div class="input-group">
                                    <input name="texto" type="text" class="form-control" value="{{$texto}}"
                                        placeholder="Ingrese texto a buscar">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i>
                                            Buscar</button>
                                        {{-- El botón "Nuevo" solo lo ve el admin --}}
                                        @can('producto-create')
                                        <a href="{{route('productos.create')}}" class="btn btn-primary"> Nuevo</a>
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
                                        {{-- Encabezado de Opciones solo para admin --}}
                                        @role('admin')
                                        <th style="width: 150px">Opciones</th>
                                        @endrole
                                        <th style="width: 20px">ID</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Imagen</th>
                                        <th>Inner</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- CAMBIO AQUÍ: Movido el @if/@else afuera del loop --}}
                                    @forelse($registros as $reg)
                                    <tr class="align-middle">
                                        {{-- ======================================================= --}}
                                        {{-- ||       AQUÍ ES DONDE AÑADES LA DIRECTIVA           || --}}
                                        {{-- ======================================================= --}}
                                        @role('admin')
                                        <td>
                                            @can('producto-edit')
                                            <a href="{{route('productos.edit', $reg->id)}}"
                                                class="btn btn-info btn-sm"><i
                                                    class="bi bi-pencil-fill"></i></a>&nbsp;
                                            @endcan
                                            @can('producto-delete')
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modal-eliminar-{{$reg->id}}"><i
                                                    class="bi bi-trash-fill"></i>
                                            </button>
                                            @endcan
                                        </td>
                                        @endrole
                                        {{-- ======================================================= --}}
                                        {{-- ||       AQUÍ TERMINA LA DIRECTIVA                   || --}}
                                        {{-- ======================================================= --}}

                                        <td>{{$reg->id}}</td>
                                        <td>{{$reg->codigo}}</td>
                                        <td>{{$reg->nombre}}</td>
                                        {{-- Formatear precio como moneda --}}
                                        <td>${{ number_format($reg->precio, 2) }}</td>

                                        <td>
                                            @if($reg->imagen)
                                            {{-- Estilo unificado para la imagen --}}
                                            <img src="{{ asset('uploads/productos/' . $reg->imagen) }}"
                                                alt="{{ $reg->nombre }}" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                            <span>N/A</span>
                                            @endif
                                        </td>
                                        
                                        <td>{{$reg->inner ?? 1}}</td> {{-- Mostrar 1 si es nulo --}}
                                    </tr>
                                    {{-- Modales solo para admin --}}
                                    @role('admin')
                                        @can('producto-delete')
                                            @include('producto.delete', ['reg' => $reg]) {{-- Pasar $reg --}}
                                        @endcan
                                    @endrole
                                    @empty
                                    {{-- CORRECCIÓN DEL COLSPAN --}}
                                    @role('admin')
                                        <tr><td colspan="7">No hay productos registrados que coincidan con la búsqueda.</td></tr>
                                    @else
                                        <tr><td colspan="6">No hay productos registrados que coincidan con la búsqueda.</td></tr>
                                    @endrole
                                    @endforelse {{-- Fin del @forelse --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        {{-- Corrección: Pasar $texto correctamente --}}
                        {{$registros->appends(["texto"=>$texto])->links()}}
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
// IDs correctos para el menú
document.getElementById('mnuAlmacen').classList.add('menu-open');
document.getElementById('navProductos').classList.add('active'); // Asumiendo que el ID es 'navProductos'
</script>
@endpush