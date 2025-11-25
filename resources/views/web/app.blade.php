<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="ARDA - Grupo Industrial" />
    <meta name="author" content="GIArda" />
    <meta name="description" content="Tienda | https://arda.com.mx/" />
    <meta name="keywords" content="Tienda, GIArda" />
    {{-- =============================================== --}}
    {{-- ||       FAVICON / ICONO DE LA PESTAÑA        || --}}
    {{-- =============================================== --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/img/favicons/site.webmanifest') }}">
    {{-- <link rel="mask-icon" href="{{ asset('assets/img/favicons/safari-pinned-tab.svg') }}" color="#5bbad5"> --}}
    <link rel="shortcut icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
    <meta name="msapplication-TileColor" content="#ffffff"> {{-- Cambio de color --}}
    {{-- <meta name="msapplication-config" content="{{ asset('assets/img/favicons/browserconfig.xml') }}"> --}}
    <meta name="theme-color" content="#ffffff"> {{-- Cambio de color --}}
    {{-- =============================================== --}}
    <title>@yield('titulo', 'ARDA - Grupo Industrial')</title>
    <!-- Favicon-->
    <!-- <link rel="icon" type="image/x-icon" href="assets/favicon.ico" /> -->
    <!-- Bootstrap icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" ...>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="{{asset('css/styles.css')}}" rel="stylesheet" />
    <link href="{{asset('css/custom.css')}}" rel="stylesheet" />
    @stack('estilos')
</head>

<body class="store-theme d-flex flex-column min-vh-100">
    <!-- Navigation-->
    @include('web.partials.nav')
    <!-- Header-->
    @if(View::hasSection('header'))
    @include('web.partials.header')
    @endif
    <!-- Search and Filter Section -->
    <main class="flex-grow-1">
        @yield('contenido')
    </main>
    <!-- Footer-->
    @include('web.partials.footer')
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="{{asset('js/scripts.js')}}"></script>
    @stack('scripts')
</body>

</html>