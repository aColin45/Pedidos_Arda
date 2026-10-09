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
    <link rel="shortcut icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    {{-- =============================================== --}}
    <title>@yield('titulo', 'ARDA - Grupo Industrial')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    
    <link href="{{asset('css/styles.css')}}" rel="stylesheet" />
    <link href="{{asset('css/custom.css')}}" rel="stylesheet" />
    
    {{-- =============================================== --}}
    {{-- INYECCIÓN SWEETALERT2 (CSS)                     --}}
    {{-- =============================================== --}}
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    
    @stack('estilos')
</head>

<body class="store-theme d-flex flex-column min-vh-100">
    @include('web.partials.nav')
    
    @if(View::hasSection('header'))
    @include('web.partials.header')
    @endif
    
    <main class="flex-grow-1">
        @yield('contenido')
    </main>
    
    @include('web.partials.footer')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('js/scripts.js')}}"></script>

    {{-- =============================================== --}}
    {{-- INYECCIÓN SWEETALERT2 (LÓGICA GLOBAL)           --}}
    {{-- =============================================== --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Detectar cualquier formulario que tenga la clase 'form-confirmar'
            const formsConfirmar = document.querySelectorAll('.form-confirmar');
            
            formsConfirmar.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault(); // Detenemos el envío nativo/automático
                    
                    // Leemos la configuración que le pasemos a cada botón
                    const titulo = this.getAttribute('data-title') || '¿Estás seguro?';
                    const texto = this.getAttribute('data-text') || 'Esta acción no se puede deshacer.';
                    const icono = this.getAttribute('data-icon') || 'warning';
                    const colorBoton = this.getAttribute('data-color') || '#d33';
                    const textoBoton = this.getAttribute('data-btn-text') || 'Sí, confirmar';

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
                            this.submit(); // Enviamos el formulario
                        }
                    });
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>