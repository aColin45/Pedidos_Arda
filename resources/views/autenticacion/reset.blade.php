@extends('autenticacion.app')
@section('titulo', 'ARDA - Cambiar Password')

@section('contenido')
<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <a href="/" class="link-dark d-block">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="Logo ARDA" style="max-height:60px;margin-bottom:5px;">
            <h1 class="mb-0 h1"><b>Pedidos</b> ARDA</h1>
        </a>
    </div>

    <div class="card-body login-card-body">
        <p class="login-box-msg">Establecer nueva contraseña</p>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form action="{{ route('password.update') }}" method="post">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    <input id="email" type="email" name="email"
                           value="{{ old('email', request('email')) }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="Email" required>
                    <label for="email">Email</label>
                </div>
                <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                @error('email')
                <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Nueva Contraseña" required>
                    <label for="password">Nueva Contraseña</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-eye-slash" id="togglePassword" style="cursor:pointer;"></span>
                </div>
                @error('password')
                <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group mb-3 has-validation">
                <div class="form-floating flex-grow-1">
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-control @error('password_confirmation') is-invalid @enderror"
                           placeholder="Confirme su nueva contraseña" required>
                    <label for="password_confirmation">Confirme su nueva contraseña</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-eye-slash" id="togglePasswordConfirmation" style="cursor:pointer;"></span>
                </div>
                @error('password_confirmation')
                <div class="invalid-feedback w-100">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-grid gap-2 col-6 mx-auto">
                        <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- NO NECESITAS @push('scripts') AQUÍ PORQUE YA ESTÁ EN app.blade.php --}}
@endsection