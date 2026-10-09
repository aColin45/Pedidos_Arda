{{-- Determina qué layout extender basado en la ruta actual --}}
@extends(request()->routeIs('contacto.index.panel') ? 'plantilla.app' : 'web.app')

@section('contenido')
<div class="{{ request()->routeIs('contacto.index.panel') ? 'app-content' : '' }} bg-light" style="min-height: 80vh;">
    <div class="container py-5">
        
        {{-- Encabezado de la sección --}}
        <div class="text-center mb-5">
            <h2 class="font-weight-bold text-dark mb-2">Directorio de Contacto</h2>
            <p class="text-muted">Estamos aquí para ayudarte. Contáctanos directamente.</p>
        </div>

        <div class="row justify-content-center g-4">

            {{-- ======================================================== --}}
            {{-- TARJETA 1: MARISOL MUNGUIA (MARKETING)                   --}}
            {{-- ======================================================== --}}
            <div class="col-md-6 col-lg-5">
                <div class="card h-100 shadow-sm contact-card">
                    <div class="card-body p-4 text-center">
                        
                        {{-- Icono Principal Centrado en Círculo --}}
                        <div class="icon-wrapper bg-soft-primary mb-4">
                            <i class="fas fa-bullhorn fa-2x text-primary-arda"></i>
                        </div>
                        
                        <h4 class="font-weight-bold mb-1">Marisol Munguia</h4>
                        <span class="badge bg-white text-secondary mb-4 px-3 py-2 border shadow-sm">Marketing y Ventas</span>
                        
                        {{-- Caja de Datos de Contacto --}}
                        <div class="text-start bg-light rounded-3 p-3">
                            <ul class="list-unstyled m-0">
                                
                                {{-- Teléfono --}}
                                <li class="d-flex align-items-center mb-3">
                                    <div class="contact-icon bg-white shadow-sm me-3">
                                        <i class="fas fa-phone-alt text-secondary"></i>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Teléfono Oficina</small>
                                        <span class="text-dark fw-bold">(728) 282-4148 <span class="text-primary-arda">Ext. 110</span></span>
                                    </div>
                                </li>

                                {{-- Correo --}}
                                <li class="d-flex align-items-center">
                                    <div class="contact-icon bg-white shadow-sm me-3">
                                        <i class="fas fa-envelope text-secondary"></i>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Correo Electrónico</small>
                                        <a href="mailto:marisol.munguia@arda.com.mx" class="text-decoration-none text-primary-arda fw-bold" style="word-break: break-all;">marisol.munguia@arda.com.mx</a>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TARJETA 2: ROCIO DAVILA (FACTURACIÓN Y ALMACÉN)          --}}
            {{-- ======================================================== --}}
            <div class="col-md-6 col-lg-5">
                <div class="card h-100 shadow-sm contact-card">
                    <div class="card-body p-4 text-center">
                        
                        {{-- Icono Principal Centrado en Círculo --}}
                        <div class="icon-wrapper bg-soft-success mb-4">
                            <i class="fas fa-file-invoice-dollar fa-2x text-success"></i>
                        </div>
                        
                        <h4 class="font-weight-bold mb-1">Rocio Davila</h4>
                        <span class="badge bg-white text-secondary mb-4 px-3 py-2 border shadow-sm">Facturación y Almacenes</span>
                        
                        {{-- Caja de Datos de Contacto --}}
                        <div class="text-start bg-light rounded-3 p-3">
                            <ul class="list-unstyled m-0">
                                
                                {{-- Teléfono --}}
                                <li class="d-flex align-items-center mb-3">
                                    <div class="contact-icon bg-white shadow-sm me-3">
                                        <i class="fas fa-phone-alt text-secondary"></i>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Teléfono Oficina</small>
                                        <span class="text-dark fw-bold">(728) 282-4148 <span class="text-success">Ext. 116</span></span>
                                    </div>
                                </li>

                                {{-- Celular --}}
                                <li class="d-flex align-items-center mb-3">
                                    <div class="contact-icon bg-white shadow-sm me-3">
                                        <i class="fas fa-mobile-alt text-secondary"></i>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Celular Directo</small>
                                        <span class="text-dark fw-bold">+52 55 2980 8313</span>
                                    </div>
                                </li>

                                {{-- Correo --}}
                                <li class="d-flex align-items-center">
                                    <div class="contact-icon bg-white shadow-sm me-3">
                                        <i class="fas fa-envelope text-secondary"></i>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted" style="font-size: 0.7rem; text-transform: uppercase;">Correo Electrónico</small>
                                        <a href="mailto:rdavila@arda.com.mx" class="text-decoration-none text-success fw-bold" style="word-break: break-all;">rdavila@arda.com.mx</a>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div> {{-- Fin .row --}}
    </div> {{-- Fin .container --}}
</div> {{-- Fin wrapper --}}
@endsection

@push('estilos')
<style>
    /* Efecto de elevación elegante para las tarjetas */
    .contact-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .contact-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
    }
    
    /* Círculo grande para centrar el icono principal */
    .icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    /* Círculo pequeño para los iconos de la lista de datos */
    .contact-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0; /* Evita que el icono se deforme si el texto es largo */
    }

    /* Colores translúcidos suaves de fondo para los iconos */
    .bg-soft-primary { background-color: rgba(20, 38, 103, 0.08); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    
    /* Color Corporativo ARDA */
    .text-primary-arda { color: #142667 !important; }
</style>
@endpush