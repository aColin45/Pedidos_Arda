@extends('autenticacion.app')
@section('titulo', 'ARDA - Login')
@section('contenido')
<div class="card card-outline card-primary">
    <div class="card-header text-center"> {{-- Centrar contenido del header --}}
        <a href="/" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover d-block">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="Logo ARDA" style="max-height: 60px; margin-bottom: 5px;">
            <h1 class="mb-0 h1"><b>Pedidos</b> ARDA</h1> {{-- Usar h1 para consistencia --}}
        </a>
    </div>
    <div class="card-body login-card-body">
        <p class="login-box-msg">Ingrese sus credenciales</p>

        {{-- Mensajes de Error/Éxito --}}
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{session('error')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(Session::has('mensaje'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{Session::get('mensaje')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
        </div>
        @endif

        <form action="{{route('login.post')}}" method="post">
            @csrf

            {{-- Campo Email --}}
            <div class="input-group mb-3 has-validation"> {{-- Usar mb-3 para espaciado consistente y has-validation --}}
                <div class="form-floating flex-grow-1"> {{-- flex-grow-1 para que ocupe espacio --}}
                    {{-- Corregido: solo un atributo value --}}
                    <input id="email" type="email" name="email" value="{{old('email')}}"
                           class="form-control @error('email') is-invalid @enderror" placeholder="Email" required />
                    <label for="email">Email</label>
                </div>
                <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                @error('email')
                    {{-- Mensaje de error asociado al input group --}}
                    <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>

            {{-- =============================================== --}}
            {{-- ||       CONTRASEÑA CON OJO      || --}}
            {{-- =============================================== --}}
            {{-- Campo Password con Ojo --}}
            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    {{-- Añadido ID 'password' --}}
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" placeholder="Password" required />
                    <label for="password">Password</label>
                </div>
                <div class="input-group-text">
                    {{-- Icono del Ojo --}}
                    <span class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer;"></span>
                </div>
                @error('password')
                     <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>
            {{-- =============================================== --}}


            {{-- Enlace Recuperar Contraseña --}}
            <div class="row mb-3">
                <div class="col-12 text-end">
                     {{-- Span innecesario --}}
                    <a href="{{route('password.request')}}" class="text-primary fw-bold">¿Olvidaste tu password?</a>
                </div>
            </div>

            <div class="row">
                <div class="col-12"> {{-- Ocupar todo el ancho para centrar mejor el botón --}}
                    <div class="d-grid gap-2 col-4 mx-auto"> {{-- Botón más pequeño y centrado --}}
                        <button type="submit" class="btn btn-primary">Acceder</button>
                    </div>
                </div>
            </div>
            </form>
    </div>
    </div>

{{-- Script para el ojo (Asegúrate que este script esté cargado en tu plantilla autenticacion.app) --}}
{{-- Si no está, puedes añadirlo aquí con @push('scripts') --}}
{{-- @push('scripts')
<script>

</script>
@endpush --}}
@endsection