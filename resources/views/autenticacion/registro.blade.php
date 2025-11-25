@extends('autenticacion.app')
@section('titulo', 'ARDA - Registro')
@section('contenido')
<div class="card card-outline card-primary">
    <div class="card-header text-center"> {{-- Centrar contenido --}}
        <a href="/" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover d-block">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="Logo ARDA" style="max-height: 60px; margin-bottom: 5px;">
            <h1 class="mb-0 h1"><b>Pedidos</b> ARDA</h1> {{-- Usar h1 --}}
        </a>
    </div>
    <div class="card-body login-card-body"> {{-- Cambiar register-card-body por login-card-body si es el mismo estilo --}}
        <p class="login-box-msg">Registro de nuevo usuario</p>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{session('error')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form action="{{route('registro.store')}}" method="post">
            @csrf

            {{-- Campo Nombre --}}
            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    {{-- Cambiado ID a 'name' y tipo a 'text' --}}
                    <input id="name" type="text" name="name" value="{{old('name')}}"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Nombre completo" required />
                    <label for="name">Nombre Completo</label>
                </div>
                <div class="input-group-text"><span class="bi bi-person-fill"></span></div> {{-- Icono persona --}}
                @error('name')
                <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>

            {{-- Campo Email --}}
            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                     {{-- Cambiado ID a 'email' --}}
                    <input id="email" type="email" name="email" value="{{old('email')}}"
                           class="form-control @error('email') is-invalid @enderror" placeholder="Email" required />
                    <label for="email">Email</label>
                </div>
                <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                @error('email')
                <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>

            {{-- =============================================== --}}
            {{-- ||       CAMPO CONTRASEÑA CON OJO          || --}}
            {{-- =============================================== --}}
            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    {{-- ID 'password' --}}
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" placeholder="Contraseña" required />
                    <label for="password">Contraseña</label>
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
                           class="form-control" placeholder="Confirme su contraseña" required />
                    <label for="password_confirmation">Confirme su contraseña</label>
                </div>
                <div class="input-group-text">
                     {{-- Icono Ojo con ID diferente --}}
                    <span class="bi bi-eye-slash" id="togglePasswordConfirmation" style="cursor: pointer;"></span>
                </div>
                {{-- No se necesita @error aquí normalmente, la validación 'confirmed' lo maneja --}}
            </div>
            {{-- =============================================== --}}


            <div class="row mt-4"> {{-- Añadido margen superior --}}
                <div class="col-6">
                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            Registrar
                        </button>
                    </div>
                </div>
            </div>
            </form>
    </div>
    </div>

{{-- Script para el ojo (Debe estar cargado en autenticacion.app) --}}
{{-- @push('scripts')
<script>
    // ... (El código JS que te di antes, que maneja ambos IDs) ...
</script>
@endpush --}}
@endsection