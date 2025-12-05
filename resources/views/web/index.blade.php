@extends('web.app')

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
                    <select class="form-select" id="sortSelect" name="sort" onchange="this.form.submit()">
                        <option value="priceAsc" {{ request('sort', 'priceAsc') == 'priceAsc' ? 'selected' : '' }}>Precio: menor a mayor</option>
                        <option value="priceDesc" {{ request('sort') == 'priceDesc' ? 'selected' : '' }}>Precio: mayor a menor</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Sección de Productos -->
<section class="py-5">
    <div class="container px-4 px-lg-5 mt-1">
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
            @forelse($productos as $producto)
            <div class="col mb-5">
                <div class="card h-100 shadow-sm">
                    <!-- Imagen del Producto -->
                    <a href="{{route('web.show', $producto->id)}}">
                        <img class="card-img-top"
                             src="{{ $producto->imagen ? asset('uploads/productos/'. $producto->imagen) : asset('assets/img/placeholder.png') }}"
                             alt="{{$producto->nombre}}"
                             style="height: 200px; object-fit: cover;" />
                    </a>
                    
                    <!-- Detalles del Producto -->
                    <div class="card-body p-4">
                        <div class="text-center">
                            <h5 class="fw-bolder">
                                <a href="{{route('web.show', $producto->id)}}" class="text-dark text-decoration-none">
                                    {{$producto->nombre}}
                                </a>
                            </h5>
                            <span class="text-muted fs-5">${{number_format($producto->precio, 2)}}</span>
                            
                             @if(!$producto->aplica_iva)
                                 <small class="d-block text-success mt-1">(No aplica IVA)</small>
                             @endif

                             {{-- Mostrar Inner --}}
                                <small class="d-block text-info mt-1 fw-bold">Inner: {{ $producto->inner }} pzas</small>
                        </div>
                    </div>
                    
                    <!-- Acciones del Producto -->
                    <div class="card-footer p-3 pt-0 border-top-0 bg-transparent">
                        
                        {{-- Formulario para agregar al carrito directamente --}}
                        <form action="{{ route('carrito.agregar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                            
                            {{-- Input de cantidad (oculto o visible si quieres que elijan cantidad) --}}
                            {{-- Por defecto agregamos 1 inner --}}
                            <input type="hidden" name="cantidad" value="{{ $producto->inner ?? 1 }}">

                            <div class="d-grid gap-2">
                                {{-- Botón Agregar al Carrito --}}
                                <button type="submit" class="btn btn-outline-dark mt-auto">
                                    <i class="bi bi-cart-plus"></i> Agregar
                                </button>
                                
                                {{-- Botón Ver Producto --}}
                                <a class="btn btn-primary mt-auto" href="{{route('web.show', $producto->id)}}">
                                    Ver producto
                                </a>
                            </div>
                        </form>

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