@extends('web.app')

{{-- Incluir el header si esta página debe mostrar el banner --}}
@section('header')
@endsection

@section('contenido')

{{-- Formulario de Búsqueda y Ordenación --}}
<form method="GET" action="{{route('web.index')}}">
    <div class="container px-4 px-lg-5 mt-4">
        <div class="row">
            {{-- Barra de búsqueda --}}
            <div class="col-md-8 mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar productos..."
                           aria-label="Buscar productos" name="search" value="{{request('search')}}">
                    <button class="btn btn-primary" type="submit" id="searchButton">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
            {{-- Selector de ordenación --}}
            <div class="col-md-4 mb-3">
                <div class="input-group">
                    <label class="input-group-text" for="sortSelect">Ordenar por:</label>
                    {{-- Añadir onchange="this.form.submit()" para que ordene al seleccionar --}}
                    <select class="form-select" id="sortSelect" name="sort" onchange="this.form.submit()">
                        {{-- <option value="">Relevancia</option> --}} {{-- Podrías añadir una opción por defecto --}}
                        <option value="priceAsc" {{ request('sort', 'priceAsc') == 'priceAsc' ? 'selected' : '' }}>Precio: menor a mayor</option> {{-- Marcar una por defecto --}}
                        <option value="priceDesc" {{ request('sort') == 'priceDesc' ? 'selected' : '' }}>Precio: mayor a menor</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

<section class="py-5">
    <div class="container px-4 px-lg-5 mt-1">
        {{-- Grid Responsivo para Productos --}}
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
            @forelse($productos as $producto)
            <div class="col mb-5">
                <div class="card h-100 shadow-sm"> {{-- Añadida sombra sutil --}}
                    {{-- Enlace a la vista de detalle del producto --}}
                    <a href="{{route('web.show', $producto->id)}}">
                        {{-- Manejo de imagen nula con placeholder --}}
                        <img class="card-img-top"
                             src="{{ $producto->imagen ? asset('uploads/productos/'. $producto->imagen) : asset('assets/img/placeholder.png') }}"
                             alt="{{$producto->nombre}}"
                             style="height: 200px; object-fit: cover;" /> {{-- Altura fija y object-fit --}}
                    </a>
                    <div class="card-body p-4">
                        <div class="text-center">
                            {{-- Enlace también en el nombre --}}
                            <h5 class="fw-bolder">
                                <a href="{{route('web.show', $producto->id)}}" class="text-dark text-decoration-none stretched-link">
                                    {{$producto->nombre}}
                                </a>
                            </h5>
                            <span class="text-muted fs-5">${{number_format($producto->precio, 2)}}</span>
                             {{-- Mostrar si no aplica IVA --}}
                             @if(!$producto->aplica_iva)
                                 <small class="d-block text-secondary">(No aplica IVA)</small>
                             @endif
                        </div>
                    </div>
                    <div class="card-footer p-3 pt-0 border-top-0 bg-transparent"> {{-- Reducido padding --}}
                        {{-- El botón "Ver producto" ya está enlazado arriba, podríamos poner "Añadir al carrito" aquí --}}
                        {{-- O mantener "Ver producto" si prefieres --}}
                        <div class="text-center">
                            <a class="btn btn-primary mt-auto" href="{{route('web.show', $producto->id)}}">
                                Ver producto
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted">No se encontraron productos que coincidan con la búsqueda.</p>
                </div>
            @endforelse
        </div>

        {{-- Paginación Centrada --}}
        <div class="d-flex justify-content-center mt-4">
             {{-- Asegurarse que appends incluya todos los filtros activos --}}
             {{ $productos->appends(request()->query())->links() }}
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{-- Script para que el select de ordenar envíe el formulario (alternativa al onchange) --}}
{{-- <script>
    document.getElementById('sortSelect')?.addEventListener('change', function() {
        this.form.submit();
    });
</script> --}}
@endpush