<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pedidos - ARDA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{-- =============================================== --}}
    {{-- ||       FAVICON / ICONO DE LA PESTAÑA        || --}}
    {{-- =============================================== --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/img/favicons/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    {{-- =============================================== --}}
    <meta name="title" content="Sistema | https://arda.com.mx/" />
    <meta name="author" content="GIArda" />
    <meta name="description" content="Pedidos - ARDA" />
    <meta name="keywords" content="Sistema, GIArda" />
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css" integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{asset('css/adminlte.css')}}" />
    <link rel="stylesheet" href="{{asset('css/custom.css')}}" />
    
    {{-- =============================================== --}}
    {{-- SWEETALERT2 CSS                                 --}}
    {{-- =============================================== --}}
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        /* ======================================================== */
        /* CORRECCIÓN RESPONSIVE PARA ADMINLTE 4 (Desactivar Grid)  */
        /* ======================================================== */
        @media (max-width: 991.98px) {
            /* 1. Rompemos el CSS Grid para que no aplaste el contenido */
            .app-wrapper {
                display: flex !important;
                flex-direction: column !important;
            }
            
            /* 2. El contenido principal recupera todo su espacio */
            .app-main {
                width: 100% !important;
                min-width: 100% !important;
                flex: 1 !important;
                margin-left: 0 !important;
            }
            
            /* 3. El menú lateral se convierte en una ventana flotante (Offcanvas) oculta a la izquierda */
            .app-sidebar {
                position: fixed !important;
                top: 0;
                bottom: 0;
                left: -300px !important; /* Totalmente escondido por defecto */
                width: 280px !important;
                z-index: 1050 !important;
                transition: left 0.3s ease-in-out !important;
                box-shadow: none !important;
            }
            
            /* 4. Cuando AdminLTE detecta el clic en la hamburguesa, lo desliza hacia adentro */
            body.sidebar-open .app-sidebar {
                left: 0 !important;
                box-shadow: 0 0 20px rgba(0,0,0,0.5) !important;
            }
            
            /* 5. Capa gris oscura de fondo */
            .sidebar-overlay {
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.4);
                z-index: 1040;
                display: none; /* Oculta por defecto */
            }
            
            /* Mostramos la capa gris al abrir el menú */
            body.sidebar-open .sidebar-overlay {
                display: block;
            }
        }
    </style>
    @stack('estilos')
 </head>

 <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        @include('plantilla.header')
        @include('plantilla.menu')
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    {{-- ALERTA DE ENCUESTA OBLIGATORIA (SOLO PARA AGENTES) (OCULTA)
                    @if(Auth::check() && Auth::user()->hasRole('agente-ventas') && !Auth::user()->haContestadoEncuesta())
                        <div class="alert alert-warning shadow-sm mx-4 mt-4 mb-4 border-left-warning d-flex align-items-center justify-content-between" role="alert" style="background-color: #fffde7;">
                            <div>
                                <h5 class="font-weight-bold text-warning mb-1"><i class="fas fa-exclamation-triangle mr-2"></i> ¡Acción Requerida!</h5>
                                <p class="mb-0 text-dark">Para continuar ofreciendo el mejor servicio, es obligatorio que completes nuestra breve encuesta de mercado.</p>
                            </div>
                            <div>
                                <a href="{{ url('encuesta/responder') }}" class="btn btn-warning font-weight-bold shadow-sm">
                                    Comenzar Encuesta <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                    --}}
                </div>
            </div>
            @yield('contenido')
        </main>
        
        {{-- Footer Centrado y con Link Amarillo --}}
        <footer class="main-footer text-center">
             <strong>
                 Copyright &copy; {{ date('Y') }}&nbsp; 
                 <a href="https://arda.com.mx/" class="text-decoration-none footer-link-arda" target="_blank">
                     Grupo Industrial ARDA S.A de C.V.
                 </a>
             </strong>
             Todos los derechos reservados. | Pedidos - ARDA
         </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="{{asset('js/adminlte.js')}}"></script>
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
    
    {{-- SCRIPT PARA MOSTRAR/OCULTAR CONTRASEÑA --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const setupPasswordToggle = (inputId, toggleId) => {
                const passwordInput = document.getElementById(inputId);
                const togglePasswordIcon = document.getElementById(toggleId);

                if (passwordInput && togglePasswordIcon) {
                    togglePasswordIcon.addEventListener('click', function (e) {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        this.classList.toggle('bi-eye');
                        this.classList.toggle('bi-eye-slash');
                    });
                }
            };
            setupPasswordToggle('password', 'togglePassword');
            setupPasswordToggle('password_confirmation', 'togglePasswordConfirmation');
            setupPasswordToggle('current_password', 'toggleCurrentPassword'); 
        });
    </script>

    {{-- =============================================== --}}
    {{-- SWEETALERT2 JS Y LÓGICA GLOBAL                  --}}
    {{-- =============================================== --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Usamos delegación de eventos al body entero
            document.body.addEventListener('submit', function (e) {
                // Verificamos si el formulario que se intenta enviar tiene la clase 'form-confirmar'
                if (e.target && e.target.classList.contains('form-confirmar')) {
                    e.preventDefault(); // Detenemos el envío nativo/automático al instante
                    
                    const form = e.target;
                    
                    // Leemos la configuración que le pasamos a cada botón
                    const titulo = form.getAttribute('data-title') || '¿Estás seguro?';
                    const texto = form.getAttribute('data-text') || 'Esta acción no se puede deshacer.';
                    const icono = form.getAttribute('data-icon') || 'warning';
                    const colorBoton = form.getAttribute('data-color') || '#d33';
                    const textoBoton = form.getAttribute('data-btn-text') || 'Sí, confirmar';

                    Swal.fire({
                        title: titulo,
                        text: texto,
                        icon: icono,
                        showCancelButton: true,
                        confirmButtonColor: colorBoton,
                        cancelButtonColor: '#858796',
                        confirmButtonText: textoBoton,
                        cancelButtonText: 'Cancelar',
                        backdrop: `rgba(0,0,0,0.5)` // Fondo elegante semi-transparente
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Pantalla de carga mientras procesa
                            Swal.fire({
                                title: 'Procesando...',
                                text: 'Por favor espera un momento.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });
                            form.submit(); // Enviamos el formulario de forma segura
                        }
                    });
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* ======================================================= */
            /* FIX ANTI-CONGELAMIENTO PARA MODALES EN TABLAS           */
            /* ======================================================= */
            // Esto mueve todos los modales al nivel raíz (body) 
            // para evitar que el fondo gris (backdrop) bloquee la pantalla.
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                document.body.appendChild(modal);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>