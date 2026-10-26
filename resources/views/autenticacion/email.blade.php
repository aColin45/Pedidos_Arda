@extends('autenticacion.app')
@section('titulo', 'ARDA - Recuperar Password')
@section('contenido')
<div class="card auth-card">
    <div class="card-header text-center bg-white border-0 pt-4 pb-0">
        <a href="/" class="text-decoration-none d-block mb-3">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="Logo ARDA" style="max-height: 70px;">
        </a>
        <h4 class="font-weight-bold text-dark mb-0">Recuperar Contraseña</h4>
        <p class="text-muted small mt-1">Ingresa tu email para recibir las instrucciones</p>
    </div>
    
    <div class="card-body px-4 px-md-5 pb-5 pt-3">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{session('error')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('status') || Session::has('mensaje'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') ?? Session::get('mensaje') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form action="{{route('password.send-link')}}" method="post">
            @csrf
            
            <div class="input-group mb-4 has-validation shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="form-floating flex-grow-1">
                    <input id="loginEmail" type="email" name="email" value="{{old('email')}}"
                           class="form-control border-0 @error('email') is-invalid @enderror" placeholder="Email" required />
                    <label for="loginEmail" class="text-muted"><i class="fas fa-envelope mr-2"></i> Correo Electrónico</label>
                </div>
            </div>
            @error('email')
                <div class="text-danger small mb-3 ml-2" style="margin-top: -15px;">{{ $message }}</div>
            @enderror

            <div class="row mt-2">
                <div class="col-5">
                    <a href="{{ route('login') }}" class="btn btn-light btn-auth w-100 text-secondary border shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
                <div class="col-7">
                    <button type="submit" class="btn btn-primary btn-auth w-100 shadow-sm px-2">
                        <i class="fas fa-paper-plane mr-1"></i> Enviar Enlace
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection