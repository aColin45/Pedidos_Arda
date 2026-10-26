@extends('autenticacion.app')
@section('titulo', 'ARDA - Login')
@section('contenido')
<div class="card auth-card">
    <div class="card-header text-center bg-white border-0 pt-4 pb-0">
        <a href="/" class="text-decoration-none d-block mb-3">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="Logo ARDA" style="max-height: 70px;">
        </a>
        <h3 class="font-weight-bold text-dark mb-0">Bienvenido de nuevo</h3>
        <p class="text-muted small mt-1">Ingresa a tu cuenta para continuar</p>
    </div>
    
    <div class="card-body px-4 px-md-5 pb-5 pt-3">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{session('error')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(Session::has('mensaje'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-info-circle mr-2"></i>{{Session::get('mensaje')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
        </div>
        @endif

        <form action="{{route('login.post')}}" method="post">
            @csrf

            <div class="input-group mb-3 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="email" type="email" name="email" value="{{old('email')}}"
                           class="form-control border-0 @error('email') is-invalid @enderror" placeholder="Email" required />
                    <label for="email" class="text-muted"><i class="fas fa-envelope mr-2"></i> Correo Electrónico</label>
                </div>
            </div>
            @error('email')
                <div class="text-danger small mb-3 ml-2">{{ $message }}</div>
            @enderror

            <div class="input-group mb-3 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="password" type="password" name="password"
                           class="form-control border-0 @error('password') is-invalid @enderror" placeholder="Password" required />
                    <label for="password" class="text-muted"><i class="fas fa-lock mr-2"></i> Contraseña</label>
                </div>
                <div class="input-group-text bg-white border-0">
                    <span class="bi bi-eye-slash text-muted" id="togglePassword" style="cursor: pointer;"></span>
                </div>
            </div>
            @error('password')
                <div class="text-danger small mb-3 ml-2">{{ $message }}</div>
            @enderror

            <div class="text-end mb-4">
                <a href="{{route('password.request')}}" class="text-primary small font-weight-bold text-decoration-none">¿Olvidaste tu contraseña?</a>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-auth shadow-sm">
                    <i class="fas fa-sign-in-alt mr-2"></i> Acceder al Sistema
                </button>
            </div>
        </form>
    </div>
</div>
@endsection