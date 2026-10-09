@extends('web.app')
@section('contenido')

<section class="py-5 bg-light" style="min-height: calc(100vh - 200px);">
    <div class="container px-4 px-lg-5 my-4">
        
        {{-- Botón de regreso rápido --}}
        <div class="mb-4">
            <a href="{{ route('web.index') }}" class="text-decoration-none text-muted fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Volver al catálogo
            </a>
        </div>

        {{-- Tarjeta Principal del Producto --}}
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="row g-0">
                
                {{-- Lado Izquierdo: Imagen --}}
                <div class="col-md-6 bg-white d-flex align-items-center justify-content-center p-5 border-end">
                    <img class="img-fluid rounded product-main-img" 
                         src="{{ $producto->imagen ? asset('uploads/productos/'. $producto->imagen) : asset('assets/img/placeholder.png') }}"
                         alt="{{$producto->nombre}}" 
                         style="max-height: 500px; object-fit: contain;" />
                </div>

                {{-- Lado Derecho: Detalles --}}
                <div class="col-md-6 p-5 bg-white">
                    
                    {{-- Encabezado del Producto --}}
                    <div class="mb-2">
                        <span class="badge bg-dark px-3 py-2 text-uppercase letter-spacing-1">Código: {{$producto->codigo}}</span>
                    </div>
                    <h1 class="display-6 fw-bolder text-dark mb-3">{{$producto->nombre}}</h1>
                    
                    <div class="fs-2 mb-4 fw-bold text-primary-arda">
                        @if(isset($precioEspecial))
                            <span class="text-muted text-decoration-line-through fs-4 me-2">${{ number_format($producto->precio, 2) }}</span>
                            <span class="text-success">${{ number_format($precioEspecial, 2) }}</span>
                        @else
                            ${{ number_format($producto->precio, 2) }}
                        @endif
                        <span class="fs-6 text-muted fw-normal">MXN</span>
                        
                        @if(!$producto->aplica_iva)
                            <span class="badge bg-success fs-6 ms-2 align-middle">Sin IVA</span>
                        @endif
                    </div>

                    {{-- Descripción --}}
                    <p class="lead text-secondary mb-4" style="font-size: 1.05rem; line-height: 1.6;">
                        {!! nl2br(e($producto->descripcion)) !!}
                    </p>

                    {{-- Alertas del Sistema --}}
                    @if(session('mensaje'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('mensaje') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger shadow-sm border-0">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Caja de Compra Destacada --}}
                    <div class="bg-light p-4 rounded-4 border mt-4">
                        <form action="{{ route('carrito.agregar') }}" method="POST" id="formAgregarItem">
                            @csrf
                            <input type="hidden" name="producto_id" value="{{ $producto->id }}">

                            @php
                                $innerFinal = isset($innerEspecial) ? $innerEspecial : ($producto->inner ?? 1);
                                $esAdmin = auth()->check() && auth()->user()->hasRole('admin');
                                $step = $esAdmin ? 1 : $innerFinal;
                                $min  = $esAdmin ? 1 : $innerFinal;
                            @endphp

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-dark"><i class="bi bi-box-seam me-2"></i>Cantidad requerida</span>
                                <span class="badge bg-white text-dark border shadow-sm">
                                    Inner: {{ $innerFinal }} pzas
                                </span>
                            </div>

                            <div class="input-group input-group-lg mb-2 shadow-sm">
                                <span class="input-group-text bg-white text-muted border-secondary-subtle">Cant.</span>
                                <input type="number" 
                                       name="cantidad" 
                                       class="form-control text-center fw-bold border-secondary-subtle input-cantidad" 
                                       min="{{ $min }}" 
                                       step="{{ $step }}"
                                       value="{{ $innerFinal }}" 
                                       required />
                                
                                <button class="btn btn-primary px-4 fw-bold flex-shrink-0" type="submit">
                                    <i class="bi bi-cart-plus-fill me-2"></i>Agregar al carrito
                                </button>
                            </div>

                            @if(!$esAdmin && ($innerFinal > 1))
                                <div class="text-start">
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Pedidos exclusivos en múltiplos de {{ $innerFinal }}</small>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            {{-- Fila Inferior: Especificaciones Técnicas --}}
            @if($producto->especificaciones)
            <div class="row g-0 border-top bg-light">
                <div class="col-12 p-5">
                    <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-list-columns-reverse me-2 text-primary-arda"></i>Especificaciones Técnicas</h4>
                    
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <table class="table table-hover mb-0 align-middle">
                            <tbody class="border-top-0">
                                @php
                                    $lineas = explode("\n", $producto->especificaciones);
                                @endphp
                                @foreach($lineas as $linea)
                                    @php
                                        list($caracteristica, $valor) = array_pad(explode('|', $linea, 2), 2, null);
                                    @endphp
                                    @if(trim($caracteristica))
                                    <tr>
                                        <th class="bg-light text-muted fw-bold py-3 px-4 w-25 border-end" style="min-width: 150px;">{{ trim($caracteristica) }}</th>
                                        <td class="py-3 px-4 bg-white text-dark">{{ trim($valor) }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</section>
@endsection

@push('estilos')
<style>
    /* Colores corporativos y ajustes finos */
    .text-primary-arda { color: #142667 !important; }
    .letter-spacing-1 { letter-spacing: 1px; }
    
    /* Efecto de la imagen principal */
    .product-main-img { transition: transform 0.3s ease; }
    .product-main-img:hover { transform: scale(1.02); }

    /* Estilo limpio para el input numérico */
    input[type="number"]::-webkit-inner-spin-button, 
    input[type="number"]::-webkit-outer-spin-button { opacity: 1; }
</style>
@endpush

{{-- Reutilizamos el script blindado para la cantidad --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formAgregar = document.getElementById('formAgregarItem');
        const inputCantidad = formAgregar.querySelector('.input-cantidad');

        if(formAgregar && inputCantidad) {
            // 1. Interceptar envío
            formAgregar.addEventListener('submit', function(e) {
                e.preventDefault(); 
                
                let step = parseInt(inputCantidad.getAttribute('step')) || 1;
                let min = parseInt(inputCantidad.getAttribute('min')) || 1;
                let value = parseInt(inputCantidad.value) || min;
                
                if (value < min || value % step !== 0) {
                    let rounded = Math.round(value / step) * step;
                    inputCantidad.value = rounded < min ? min : rounded;
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cantidad Ajustada',
                            text: `Este producto solo se vende en múltiplos de ${step}. Hemos corregido la cantidad a ${inputCantidad.value}.`,
                            confirmButtonColor: '#142667'
                        });
                    }
                } else {
                    this.submit();
                }
            });

            // 2. Autocorrección al quitar el foco (blur)
            inputCantidad.addEventListener('blur', function() {
                let step = parseInt(this.getAttribute('step')) || 1;
                let min = parseInt(this.getAttribute('min')) || 1;
                let value = parseInt(this.value) || min;
                
                if (value < min || value % step !== 0) {
                    let rounded = Math.round(value / step) * step;
                    this.value = rounded < min ? min : rounded;
                }
            });
        }
    });
</script>
@endpush