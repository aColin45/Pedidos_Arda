<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('titulo', 'ARDA - Autenticación')</title>
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
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
        crossorigin="anonymous"
    />
    {{-- OverlayScrollbars no es necesario para páginas de login simples --}}
    {{-- <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
        integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
        crossorigin="anonymous"
    /> --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
        crossorigin="anonymous"
    />
    <link rel="stylesheet" href="{{asset('css/adminlte.css')}}" />
    {{-- Añadir custom.css si tienes estilos específicos para login --}}
    {{-- <link rel="stylesheet" href="{{asset('css/custom.css')}}" /> --}}
    @stack('estilos') {{-- Para estilos específicos de login/register/reset --}}
 </head>
 {{-- Clases para centrar el contenido en páginas de autenticación --}}
 <body class="login-page bg-body-secondary">
    <div class="login-box">
        {{-- Aquí se inyectará el contenido de login.blade, registro.blade, etc. --}}
        @yield('contenido')
    </div>
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
                } else {
                    // Opcional: Mostrar advertencia si no se encuentran los elementos
                    // console.warn(`Elemento no encontrado para ${inputId} o ${toggleId}`);
                }
            };

            // Configurar para el campo principal de contraseña (ID 'password')
            setupPasswordToggle('password', 'togglePassword');

            // Configurar para el campo de confirmación de contraseña (ID 'password_confirmation')
            setupPasswordToggle('password_confirmation', 'togglePasswordConfirmation');

            // Configurar para el campo de contraseña actual en perfil (si lo añades con estos IDs)
            // setupPasswordToggle('current_password', 'toggleCurrentPassword');
        });
    </script>
    {{-- ======================================================= --}}

    @stack('scripts') {{-- Para scripts específicos de cada vista --}}

 </body>
 </html>