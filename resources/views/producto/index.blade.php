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
                                        placeholder="Buscar por Código o Nombre del Producto">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-secondary mr-1"><i class="fas fa-search"></i>
                                            Buscar</button>
                                            
                                        {{-- Botón de Exportar a Excel --}}
                                        <a href="{{ route('productos.exportar', ['texto' => $texto]) }}" class="btn btn-success mr-1">
                                            <i class="fas fa-file-excel mr-1"></i> Exportar
                                        </a>

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
                                    @forelse($registros as $reg)
                                    <tr class="align-middle">
                                        @role('admin')
                                        <td>
                                            <div class="d-inline-flex">
                                                @can('producto-edit')
                                                <a href="{{route('productos.edit', $reg->id)}}"
                                                    class="btn btn-info btn-sm me-1" title="Editar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                @endcan
                                                
                                                {{-- BOTÓN ELIMINAR CON SWEETALERT2 --}}
                                                @can('producto-delete')
                                                <form action="{{route('productos.destroy', $reg->id)}}" method="POST" class="d-inline form-confirmar"
                                                      data-title="¿Eliminar Producto?" 
                                                      data-text="Se eliminará permanentemente el producto '{{$reg->nombre}}'. Esta acción no se puede deshacer." 
                                                      data-icon="warning" 
                                                      data-color="#dc3545" 
                                                      data-btn-text="Sí, eliminar">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                        @endrole

                                        <td>{{$reg->id}}</td>
                                        <td>{{$reg->codigo}}</td>
                                        <td>
                                            {{ $reg->nombre }}
                                            @if($reg->es_especial == 1)
                                                <span class="badge bg-warning text-dark ms-2 shadow-sm" style="font-size: 0.7rem;" title="Producto Exclusivo">
                                                    <i class="fas fa-star"></i> Especial
                                                </span>
                                            @endif
                                        </td>
                                        {{-- Formatear precio como moneda --}}
                                        <td>
                                            @php
                                                $esPrecioEspecial = isset($preciosEspeciales[$reg->id]);
                                                $precioFinal = $esPrecioEspecial ? $preciosEspeciales[$reg->id] : $reg->precio;
                                            @endphp
                                            
                                            @if($esPrecioEspecial)
                                                <span class="text-muted text-decoration-line-through" style="font-size: 0.8rem;">${{ number_format($reg->precio, 2) }}</span>
                                                <br>
                                                <span class="text-success fw-bold">${{ number_format($precioFinal, 2) }}</span>
                                            @else
                                                ${{ number_format($precioFinal, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if($reg->imagen)
                                            <img src="{{ asset('uploads/productos/' . $reg->imagen) }}"
                                                alt="{{ $reg->nombre }}" style="width: 50px; height: 50px; object-fit: cover;" class="rounded shadow-sm">
                                            @else
                                            <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        
                                        {{-- Columna Inner Final --}}
                                        <td>
                                            @php
                                                $esInnerEspecial = isset($innersEspeciales[$reg->id]);
                                                $innerFinal = $esInnerEspecial ? $innersEspeciales[$reg->id] : ($reg->inner ?? 1);
                                            @endphp
                                            
                                            @if($esInnerEspecial)
                                                <span class="text-muted text-decoration-line-through" style="font-size: 0.8rem;">{{ $reg->inner ?? 1 }}</span>
                                                <br>
                                                <span class="text-primary fw-bold">{{ $innerFinal }}</span>
                                            @else
                                                {{ $innerFinal }}
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    @role('admin')
                                        <tr><td colspan="7" class="text-center py-3">No hay productos registrados que coincidan con la búsqueda.</td></tr>
                                    @else
                                        <tr><td colspan="6" class="text-center py-3">No hay productos registrados que coincidan con la búsqueda.</td></tr>
                                    @endrole
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
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
document.getElementById('navProductos').classList.add('active'); 
</script>
@endpush