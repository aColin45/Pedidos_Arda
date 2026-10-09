@extends('web.app')

@section('header')
@endsection

@section('contenido')

{{-- ======================================================= --}}
{{-- 1. ZONA DE BÚSQUEDA Y FILTROS                           --}}
{{-- ======================================================= --}}
<div class="container px-4 px-lg-5 mt-4">
    <div class="bg-white p-3 p-md-4 rounded-3 shadow-sm border text-center">
        <form method="GET" action="{{route('web.index')}}" class="m-0">
            <div class="row g-3 justify-content-center align-items-center">
                
                {{-- Barra de búsqueda (Ajustada a col-md-5) --}}
                <div class="col-md-5">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0 shadow-none" id="searchInput" placeholder="¿Qué producto buscas?" aria-label="Buscar productos" name="search" value="{{request('search')}}">
                        <button class="btn btn-primary px-4" type="submit" id="searchButton">Buscar</button>
                    </div>
                </div>

                {{-- Selector de ordenación (Ajustado a col-md-4) --}}
                <div class="col-md-4">
                    <div class="input-group input-group-lg">
                        <label class="input-group-text bg-light text-muted" for="sortSelect">
                            <i class="bi bi-filter-left me-1"></i> Ordenar
                        </label>
                        <select class="form-select shadow-none" id="sortSelect" name="sort" onchange="this.form.submit()">
                            <option value="priceAsc" {{ request('sort', 'priceAsc') == 'priceAsc' ? 'selected' : '' }}>Menor a mayor precio</option>
                            <option value="priceDesc" {{ request('sort') == 'priceDesc' ? 'selected' : '' }}>Mayor a menor precio</option>
                        </select>
                    </div>
                </div>

                {{-- NUEVO: Botón Filtro "Mis Exclusivos" (col-md-3) --}}
                @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin') || auth()->user()->productosEspeciales->count() > 0))
                <div class="col-md-3 text-center text-md-end">
                    <div class="btn-group w-100 shadow-sm" role="group">
                        <input type="checkbox" class="btn-check" name="solo_exclusivos" id="btnExclusivos" value="1" onchange="this.form.submit()" {{ request('solo_exclusivos') == '1' ? 'checked' : '' }}>
                        <label class="btn {{ request('solo_exclusivos') == '1' ? 'btn-warning border-warning' : 'btn-outline-secondary bg-white' }} text-dark fw-bold d-flex align-items-center justify-content-center" for="btnExclusivos" style="height: calc(1.5em + 1rem + 2px);">
                            <i class="fas fa-star text-warning me-2" style="-webkit-text-stroke: 1px #856404;"></i> 
                            {{ request('solo_exclusivos') == '1' ? 'Viendo Exclusivos' : 'Mis Exclusivos' }}
                        </label>
                    </div>
                </div>
                @endif

            </div>
        </form>
    </div>
</div>

{{-- ======================================================= --}}
{{-- 2. GRID DE PRODUCTOS                                    --}}
{{-- ======================================================= --}}
<section class="py-5 bg-light mt-4">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
            
            @forelse($productos as $producto)
            <div class="col mb-5">
                <div class="card h-100 border-0 shadow-sm product-card">
                    
                    {{-- Etiqueta flotante de IVA (Opcional visual) --}}
                    @if(!$producto->aplica_iva)
                        <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                            <span class="badge bg-dark shadow-sm">Sin IVA</span>
                        </div>
                    @endif

                    {{-- Imagen del Producto --}}
                    <a href="{{route('web.show', $producto->id)}}" class="overflow-hidden rounded-top">
                        <img class="card-img-top product-img transition-transform"
                             src="{{ $producto->imagen ? asset('uploads/productos/'. $producto->imagen) : asset('assets/img/placeholder.png') }}"
                             alt="{{$producto->nombre}}" />
                    </a>
                    
                    {{-- Detalles del Producto --}}
                    <div class="card-body p-4 d-flex flex-column">
                        
                        {{-- Código del Producto --}}
                        <small class="text-muted fw-bold mb-1" style="font-size: 0.75rem;">
                            CÓDIGO: {{ $producto->codigo ?? 'N/A' }}
                        </small>
                        
                        {{-- Título completo con Medalla de Exclusivo --}}
                        <a href="{{route('web.show', $producto->id)}}" class="text-dark text-decoration-none mb-2">
                            <h6 class="fw-bold product-title text-uppercase mb-0" title="{{$producto->nombre}}">
                                {{$producto->nombre}}
                                
                                @if(isset($producto->es_especial) && $producto->es_especial == 1)
                                    <span class="badge bg-warning text-dark ms-1 align-middle shadow-sm" style="font-size: 0.65rem; font-weight: bold;" title="Producto Exclusivo para tu perfil">
                                        <i class="fas fa-star me-1"></i>Exclusivo
                                    </span>
                                @endif
                            </h6>
                        </a>
                        
                        {{-- Precio destacado --}}
                        @php
                            $esPrecioEspecial = isset($preciosEspeciales[$producto->id]);
                            $precioFinal = $esPrecioEspecial ? $preciosEspeciales[$producto->id] : $producto->precio;
                            
                            // Cálculo del Inner Final
                            $esInnerEspecial = isset($innersEspeciales[$producto->id]);
                            $innerFinal = $esInnerEspecial ? $innersEspeciales[$producto->id] : ($producto->inner ?? 1);
                        @endphp
                        
                        <div class="mb-3 mt-auto">
                            @if($esPrecioEspecial)
                                <span class="text-muted text-decoration-line-through me-1" style="font-size: 0.9rem;">${{number_format($producto->precio, 2)}}</span>
                                <span class="fs-4 fw-bolder text-success">${{number_format($precioFinal, 2)}}</span>
                            @else
                                <span class="fs-4 fw-bolder text-primary-arda">${{number_format($precioFinal, 2)}}</span>
                            @endif
                            <span class="text-muted" style="font-size: 0.8rem;">MXN</span>
                        </div>
                        
                        {{-- Badge de Inner --}}
                        <div>
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <i class="bi bi-box-seam me-1 text-muted"></i> Inner: {{ $innerFinal }} pzas
                            </span>
                        </div>
                    </div>
                    
                    {{-- Acciones del Producto --}}
                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                        <form action="{{ route('carrito.agregar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                            
                            @php
                                $esAdmin = auth()->check() && auth()->user()->hasRole('admin');
                                // Aquí usamos $innerFinal para la validación matemática del input
                                $step = $esAdmin ? 1 : $innerFinal;
                                $min  = $esAdmin ? 1 : $innerFinal;
                            @endphp

                            {{-- Input Group: Cantidad + Botón Agregar --}}
                            <div class="input-group mb-2 shadow-sm rounded">
                                <input type="number" 
                                    name="cantidad" 
                                    class="form-control text-center fw-bold border-secondary-subtle input-cantidad" 
                                    min="{{ $min }}" 
                                    step="{{ $step }}" 
                                    value="{{ $innerFinal }}" 
                                    required>
                                
                                <button type="submit" class="btn btn-primary fw-bold px-3" title="Agregar al carrito">
                                    <i class="bi bi-cart-plus fs-5"></i>
                                </button>
                            </div>
                            
                            @if(!$esAdmin)
                                <div class="text-center mb-3">
                                    {{-- Aquí mostramos el texto con el inner correcto --}}
                                    <small class="text-muted" style="font-size: 0.7rem;">Múltiplos de {{ $innerFinal }}</small>
                                </div>
                            @else
                                <div class="mb-3"></div>
                            @endif

                            {{-- Botón de detalles secundario --}}
                            <a class="btn btn-outline-dark w-100 fw-bold" href="{{route('web.show', $producto->id)}}">
                                Ver detalles
                            </a>
                        </form>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12 py-5">
                    <div class="text-center text-muted">
                        <i class="bi bi-search display-1 mb-3 opacity-50"></i>
                        <h4>No encontramos lo que buscas</h4>
                        <p>Intenta con otros términos o contacta a un agente para una cotización especial.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div class="d-flex justify-content-center mt-5">
             {{ $productos->appends(request()->query())->links() }}
        </div>
    </div>
</section>
@endsection

@push('estilos')
<style>
    /* Efecto hover suave para la tarjeta completa */
    .product-card {
        transition: all 0.3s ease;
        border-radius: 12px;
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    
    /* Control estricto de la imagen */
    .product-img {
        height: 220px;
        width: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .product-card:hover .product-img {
        transform: scale(1.05);
    }

    /* Mostrar título completo de manera natural */
    .product-title {
        line-height: 1.4;
    }

    /* Color corporativo forzado para el precio */
    .text-primary-arda { color: #142667 !important; }
    
    /* Pequeño ajuste para que el input number no muestre las flechitas tan invasivas */
    input[type="number"]::-webkit-inner-spin-button, 
    input[type="number"]::-webkit-outer-spin-button { opacity: 1; }
</style>
@endpush

{{-- ======================================================= --}}
{{-- SCRIPT DE VALIDACIÓN INTELIGENTE DE CANTIDADES          --}}
{{-- ======================================================= --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Interceptamos el envío de todos los botones "Agregar"
        const formsAgregar = document.querySelectorAll('form[action*="carrito"]');
        
        formsAgregar.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); 
                
                const input = this.querySelector('input[name="cantidad"]');
                if (!input) {
                    this.submit(); 
                    return;
                }

                let step = parseInt(input.getAttribute('step')) || 1;
                let min = parseInt(input.getAttribute('min')) || 1;
                let value = parseInt(input.value) || min;
                
                // 2. Verificamos si rompe la regla matemática
                if (value < min || value % step !== 0) {
                    
                    let rounded = Math.round(value / step) * step;
                    input.value = rounded < min ? min : rounded;
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cantidad Ajustada',
                            text: `Este producto solo se vende en múltiplos de ${step}. Hemos corregido la cantidad a ${input.value}.`,
                            confirmButtonColor: '#142667'
                        });
                    }
                } else {
                    this.submit();
                }
            });
        });

        // 4. Mantenemos el auto-corrector visual si el cliente da clic fuera de la caja
        const inputsCantidad = document.querySelectorAll('input[name="cantidad"]');
        inputsCantidad.forEach(input => {
            input.addEventListener('blur', function() {
                let step = parseInt(this.getAttribute('step')) || 1;
                let min = parseInt(this.getAttribute('min')) || 1;
                let value = parseInt(this.value) || min;
                
                if (value < min || value % step !== 0) {
                    let rounded = Math.round(value / step) * step;
                    this.value = rounded < min ? min : rounded;
                }
            });
        });
    });
</script>
@endpush