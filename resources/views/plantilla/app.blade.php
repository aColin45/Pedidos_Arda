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
    {{-- <link rel="mask-icon" href="{{ asset('assets/img/favicons/safari-pinned-tab.svg') }}" color="#5bbad5"> --}}
    <link rel="shortcut icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    {{-- <meta name="msapplication-config" content="{{ asset('assets/img/favicons/browserconfig.xml') }}"> --}}
    <meta name="theme-color" content="#ffffff">
    {{-- =============================================== --}}
    <meta name="title" content="Sistema | https://arda.com.mx/" />
    <meta name="author" content="GIArda" />
    <meta name="description" content="Pedidos - ARDA" />
    <meta name="keywords" content="Sistema, GIArda" />
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> {{-- CDN --}}

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
        crossorigin="anonymous"
    />
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
        integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
        crossorigin="anonymous"
    />
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
        crossorigin="anonymous"
    />
    <link rel="stylesheet" href="{{asset('css/adminlte.css')}}" />
    <link rel="stylesheet" href="{{asset('css/custom.css')}}" />
    @stack('estilos')
 </head>
 <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        @include('plantilla.header')
        @include('plantilla.menu')
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                   {{-- Puedes poner breadcrumbs o títulos aquí si quieres --}}
                </div>
                </div>
            @yield('contenido')
            </main>
        {{-- Footer Centrado y con Link Amarillo --}}
        <footer class="main-footer text-center">
             <strong>
                 Copyright &copy; {{ date('Y') }}&nbsp; {{-- Año dinámico --}}
                 <a href="https://arda.com.mx/" class="text-decoration-none footer-link-arda" target="_blank">
                     Grupo Industrial ARDA S.A de C.V.
                 </a>
             </strong>
             Todos los derechos reservados. | Pedidos - ARDA
         </footer>
        </div>
    <script
        src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
        integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
        crossorigin="anonymous"
    ></script>
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"
    ></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"
    ></script>
    <script src="{{asset('js/adminlte.js')}}"></script>
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light', // o 'os-theme-dark' si se prefiere
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
    {{-- ======================================================= --}}
    {{-- ||       SCRIPT PARA MOSTRAR/OCULTAR CONTRASEÑA      || --}}
    {{-- ======================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para configurar el mostrar/ocultar para un campo de contraseña e ícono dados
            const setupPasswordToggle = (inputId, toggleId) => {
                const passwordInput = document.getElementById(inputId);
                const togglePasswordIcon = document.getElementById(toggleId);

                if (passwordInput && togglePasswordIcon) {
                    togglePasswordIcon.addEventListener('click', function (e) {
                        // Cambiar el atributo 'type' del input
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);

                        // Cambiar el icono del ojo (Bootstrap Icons)
                        this.classList.toggle('bi-eye');
                        this.classList.toggle('bi-eye-slash');
                    });
                }
            };

            // Configurar para el campo principal de contraseña (ID 'password')
            setupPasswordToggle('password', 'togglePassword');

            // Configurar para el campo de confirmación de contraseña (ID 'password_confirmation')
            setupPasswordToggle('password_confirmation', 'togglePasswordConfirmation');

            // Configurar para el campo de contraseña actual en perfil (si existe y tiene este ID)
            setupPasswordToggle('current_password', 'toggleCurrentPassword'); // Asumiendo estos IDs
        });
    </script>
    {{-- ======================================================= --}}

    @stack('scripts') {{-- Lugar para scripts específicos de cada vista --}}
 </body>
 </html>