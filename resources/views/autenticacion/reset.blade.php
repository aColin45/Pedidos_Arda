@extends('autenticacion.app')
@section('titulo', 'ARDA - Cambiar Password')

@section('contenido')
<div class="card auth-card">
    <div class="card-header text-center bg-white border-0 pt-4 pb-0">
        <a href="/" class="text-decoration-none d-block mb-3">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="Logo ARDA" style="max-height: 70px;">
        </a>
        <h4 class="font-weight-bold text-dark mb-0">Nueva Contraseña</h4>
        <p class="text-muted small mt-1">Establece tu nueva contraseña de acceso</p>
    </div>

    <div class="card-body px-4 px-md-5 pb-5 pt-3">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form action="{{ route('password.update') }}" method="post">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="input-group mb-3 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    {{-- El correo viene por defecto de la solicitud de reinicio --}}
                    <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}"
                           class="form-control border-0 @error('email') is-invalid @enderror" placeholder="Email" required readonly>
                    <label for="email" class="text-muted"><i class="fas fa-envelope mr-2"></i>Correo Electrónico</label>
                </div>
            </div>
            @error('email')
                <div class="text-danger small mb-3 ml-2">{{ $message }}</div>
            @enderror

            <div class="input-group mb-3 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="password" type="password" name="password"
                           class="form-control border-0 @error('password') is-invalid @enderror" placeholder="Nueva Contraseña" required>
                    <label for="password" class="text-muted"><i class="fas fa-lock mr-2"></i>Nueva Contraseña</label>
                </div>
                <div class="input-group-text bg-white border-0">
                    <span class="bi bi-eye-slash text-muted" id="togglePassword" style="cursor:pointer;"></span>
                </div>
            </div>
            @error('password')
                <div class="text-danger small mb-3 ml-2">{{ $message }}</div>
            @enderror

            <div class="input-group mb-4 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-control border-0 @error('password_confirmation') is-invalid @enderror"
                           placeholder="Confirme su nueva contraseña" required>
                    <label for="password_confirmation" class="text-muted"><i class="fas fa-check-double mr-2"></i>Confirme Contraseña</label>
                </div>
                <div class="input-group-text bg-white border-0">
                    <span class="bi bi-eye-slash text-muted" id="togglePasswordConfirmation" style="cursor:pointer;"></span>
                </div>
            </div>
            @error('password_confirmation')
                <div class="text-danger small mb-3 ml-2">{{ $message }}</div>
            @enderror

            <div class="d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-auth shadow-sm">
                    Actualizar Contraseña <i class="fas fa-save ml-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection