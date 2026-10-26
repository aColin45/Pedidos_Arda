@extends('autenticacion.app')
@section('titulo', 'ARDA - Registro')
@section('contenido')
<div class="card auth-card">
    <div class="card-header text-center bg-white border-0 pt-4 pb-0">
        <a href="/" class="text-decoration-none d-block mb-3">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="Logo ARDA" style="max-height: 70px;">
        </a>
        <h4 class="font-weight-bold text-dark mb-0">Crear Cuenta</h4>
        <p class="text-muted small mt-1">Ingresa tus datos para registrarte</p>
    </div>

    <div class="card-body px-4 px-md-5 pb-5 pt-3">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{session('error')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form action="{{route('registro.store')}}" method="post">
            @csrf

            <div class="input-group mb-3 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="name" type="text" name="name" value="{{old('name')}}"
                           class="form-control border-0 @error('name') is-invalid @enderror" placeholder="Nombre completo" required />
                    <label for="name" class="text-muted"><i class="fas fa-user mr-2"></i> Nombre Completo</label>
                </div>
            </div>
            @error('name') <div class="text-danger small mb-3 ml-2">{{ $message }}</div> @enderror

            <div class="input-group mb-3 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="email" type="email" name="email" value="{{old('email')}}"
                           class="form-control border-0 @error('email') is-invalid @enderror" placeholder="Email" required />
                    <label for="email" class="text-muted"><i class="fas fa-envelope mr-2"></i> Correo Electrónico</label>
                </div>
            </div>
            @error('email') <div class="text-danger small mb-3 ml-2">{{ $message }}</div> @enderror

            <div class="input-group mb-3 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="password" type="password" name="password"
                           class="form-control border-0 @error('password') is-invalid @enderror" placeholder="Contraseña" required />
                    <label for="password" class="text-muted"><i class="fas fa-lock mr-2"></i> Contraseña</label>
                </div>
                <div class="input-group-text bg-white border-0">
                    <span class="bi bi-eye-slash text-muted" id="togglePassword" style="cursor: pointer;"></span>
                </div>
            </div>
            @error('password') <div class="text-danger small mb-3 ml-2">{{ $message }}</div> @enderror

            <div class="input-group mb-4 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-control border-0" placeholder="Confirme su contraseña" required />
                    <label for="password_confirmation" class="text-muted"><i class="fas fa-check-circle mr-2"></i> Confirme su contraseña</label>
                </div>
                <div class="input-group-text bg-white border-0">
                    <span class="bi bi-eye-slash text-muted" id="togglePasswordConfirmation" style="cursor: pointer;"></span>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-6">
                    <a href="{{ route('login') }}" class="btn btn-light btn-auth w-100 text-secondary border shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
                <div class="col-6">
                    <button type="submit" class="btn btn-primary btn-auth w-100 shadow-sm">
                        Registrar <i class="fas fa-user-plus ml-1"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection