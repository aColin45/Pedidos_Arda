@extends('autenticacion.app')
@section('titulo', 'ARDA - Cambiar Password')
@section('contenido')
<div class="card card-outline card-primary">
    <div class="card-header text-center"> {{-- Centrar contenido --}}
        <a href="/" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover d-block">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="Logo ARDA" style="max-height: 60px; margin-bottom: 5px;">
            <h1 class="mb-0 h1"><b>Pedidos</b> ARDA</h1> {{-- Usar h1 --}}
        </a>
    </div>
    <div class="card-body login-card-body"> {{-- Usar login-card-body si es el mismo estilo --}}
        <p class="login-box-msg">Establecer nueva contraseña</p>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{session('error')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
         @if (session('status')) {{-- Mensaje de éxito después de enviar el link --}}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{route('password.update')}}" method="post">
            @csrf
            {{-- Token de restablecimiento (oculto) --}}
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Campo Email (requerido por Laravel para identificar al usuario) --}}
            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    {{-- Usar request('email') para rellenar el email del enlace --}}
                    <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}"
                           class="form-control @error('email') is-invalid @enderror" placeholder="Email" required readonly /> {{-- readonly es opcional pero ayuda --}}
                    <label for="email">Email</label>
                </div>
                <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                @error('email')
                <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>

            {{-- =============================================== --}}
            {{-- ||       CAMPO NUEVA CONTRASEÑA CON OJO      || --}}
            {{-- =============================================== --}}
            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    {{-- ID 'password' --}}
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" placeholder="Nueva Contraseña" required />
                    <label for="password">Nueva Contraseña</label>
                </div>
                <div class="input-group-text">
                    {{-- Icono Ojo --}}
                    <span class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer;"></span>
                </div>
                 @error('password')
                     <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>
            {{-- =============================================== --}}

            {{-- =============================================== --}}
            {{-- ||   CAMPO CONFIRMAR CONTRASEÑA CON OJO    || --}}
            {{-- =============================================== --}}
            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    {{-- ID 'password_confirmation' --}}
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-control" placeholder="Confirme su nueva contraseña" required />
                    <label for="password_confirmation">Confirme su nueva contraseña</label>
                </div>
                <div class="input-group-text">
                     {{-- Icono Ojo con ID diferente --}}
                    <span class="bi bi-eye-slash" id="togglePasswordConfirmation" style="cursor: pointer;"></span>
                </div>
                {{-- No se necesita @error aquí, la validación 'confirmed' lo maneja --}}
            </div>
            {{-- =============================================== --}}

            <div class="row mt-4"> {{-- Añadido margen --}}
                <div class="col-12"> {{-- Ocupar todo el ancho --}}
                    <div class="d-grid gap-2 col-6 mx-auto"> {{-- Botón centrado y un poco más ancho --}}
                        <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                    </div>
                </div>
            </div>
            </form>
    </div>
    </div>

{{-- Script para el ojo (Asegúrate que esté cargado en autenticacion.app) --}}
{{-- @push('scripts')
<script>
    // ... (El código JS que te di antes, que maneja ambos IDs) ...
</script>
@endpush --}}
@endsection