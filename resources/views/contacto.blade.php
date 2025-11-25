{{-- Determina qué layout extender basado en la ruta actual --}}
@extends(request()->routeIs('contacto.index.panel') ? 'plantilla.app' : 'web.app')

@section('contenido')
<div class="{{ request()->routeIs('contacto.index.panel') ? 'app-content' : '' }}"> {{-- Wrapper para el panel --}}
    <div class="container py-5"> {{-- Contenedor principal --}}
        
        <h2 class="mb-4 text-center">Información de Contacto</h2>

        <div class="row justify-content-center g-4"> {{-- Fila centrada con espacio entre tarjetas --}}

            {{-- Tarjeta 1: Marisol Munguia --}}
            <div class="col-md-5"> {{-- Ocupa casi la mitad en pantallas medianas y grandes --}}
                <div class="card h-100 shadow-sm"> {{-- Tarjeta con sombra y altura completa --}}
                    <div class="card-body text-center">
                        <i class="fas fa-bullhorn fa-3x text-primary mb-3"></i> {{-- Icono Marketing --}}
                        <h5 class="card-title">Marisol Munguia</h5>
                        <p class="card-text text-muted">Marketing</p>
                        <hr>
                        <ul class="list-unstyled text-start"> {{-- Lista sin estilo, alineada a la izquierda --}}
                            <li class="mb-2">
                                <i class="fas fa-phone-alt fa-fw me-2 text-secondary"></i>
                                (728) 282-4148 Ext. 110
                            </li>
                            <li>
                                <i class="fas fa-envelope fa-fw me-2 text-secondary"></i>
                                <a href="mailto:marisol.munguia@arda.com.mx" class="text-decoration-none">marisol.munguia@arda.com.mx</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Tarjeta 2: Rocio Davila --}}
            <div class="col-md-5">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                         <i class="fas fa-file-invoice-dollar fa-3x text-success mb-3"></i> {{-- Icono Facturación/Almacén --}}
                        <h5 class="card-title">Rocio Davila</h5>
                        <p class="card-text text-muted">Facturación y Almacenes</p>
                        <hr>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2">
                                <i class="fas fa-phone-alt fa-fw me-2 text-secondary"></i>
                                (728) 282-4148 Ext. 116
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-mobile-alt fa-fw me-2 text-secondary"></i>
                                (+52) 55 2980 8313 {{-- Corrección formato --}}
                            </li>
                            <li>
                                <i class="fas fa-envelope fa-fw me-2 text-secondary"></i>
                                <a href="mailto:rdavila@arda.com.mx" class="text-decoration-none">rdavila@arda.com.mx</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div> {{-- Fin .row --}}
    </div> {{-- Fin .container --}}
</div> {{-- Fin wrapper panel (si aplica) --}}
@endsection

@push('estilos')
{{-- Si Font Awesome no está cargado globalmente, puedes añadirlo aquí --}}
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> --}}
@endpush