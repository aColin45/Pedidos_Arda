<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('titulo', 'ARDA - Autenticación')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
    <meta name="theme-color" content="#ffffff">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
    <link rel="stylesheet" href="{{asset('css/adminlte.css')}}" />
    
    <style>
        /* FONDO ELEGANTE Y TARJETAS MODERNAS */
        body.login-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .login-box {
            width: 450px;
            max-width: 90%;
        }
        .auth-card {
            border-radius: 1.2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
            border: none !important;
            overflow: hidden;
        }
        .btn-auth {
            border-radius: 50rem;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
    </style>
    @stack('estilos')
</head>

 {{-- Clases para centrar el contenido en páginas de autenticación --}}
 <body class="login-page">
    <div class="login-box">
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