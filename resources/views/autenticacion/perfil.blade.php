@extends('plantilla.app')
@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid mt-4">
        <div class="row justify-content-center"> 
            <div class="col-lg-8 col-xl-7">
                
                <div class="card shadow border-0" style="border-radius: 12px; border-top: 4px solid #4e73df !important;">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h4 class="card-title font-weight-bold text-primary mb-0"><i class="fas fa-user-cog mr-2"></i>Mi Perfil</h4>
                        <p class="text-muted small mb-0 mt-1">Actualiza tu información personal y credenciales de acceso.</p>
                    </div>
                    
                    <div class="card-body bg-light" style="border-radius: 0 0 12px 12px;">
                        @if (session('mensaje'))
                            <div class="alert alert-success border-0 shadow-sm" style="border-radius: 10px;">
                                <i class="fas fa-check-circle mr-2"></i>{{ session('mensaje') }}
                            </div>
                        @endif

                        <form action="{{ route('perfil.update')}}" method="POST" id="formRegistroUsuario">
                            @csrf
                            @method('PUT')                            
                            
                            <div class="bg-white p-4 rounded shadow-sm mb-4">
                                <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-id-card text-secondary mr-2"></i>Información Personal</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label font-weight-bold small text-muted">Nombre Completo</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white text-primary border-end-0"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control border-start-0 @error('name') is-invalid @enderror"
                                             id="name" name="name" value="{{old('name', $registro->name ??'')}}" required>
                                        </div>
                                        @error('name')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label font-weight-bold small text-muted">Correo Electrónico</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white text-primary border-end-0"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                                             id="email" name="email" value="{{old('email',  $registro->email ??'')}}" required>
                                        </div>
                                        @error('email')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded shadow-sm mb-4">
                                <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-lock text-secondary mr-2"></i>Seguridad de la Cuenta</h6>
                                <p class="small text-muted mb-3"><i class="fas fa-info-circle mr-1"></i>Deja los campos de contraseña en blanco si no deseas cambiarla.</p>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label font-weight-bold small text-muted">Nueva Contraseña</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white text-dark border-end-0"><i class="fas fa-key"></i></span>
                                            <input type="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror"
                                             id="password" name="password" placeholder="••••••••">
                                            <span class="input-group-text bg-white border-start-0" style="cursor: pointer;">
                                                <i class="bi bi-eye-slash text-muted" id="togglePassword"></i>
                                            </span>
                                        </div>
                                        @error('password')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label font-weight-bold small text-muted">Confirme su Contraseña</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text bg-white text-dark border-end-0"><i class="fas fa-check-double"></i></span>
                                            <input type="password" class="form-control border-start-0 border-end-0 @error('password_confirmation') is-invalid @enderror"
                                             id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                                            <span class="input-group-text bg-white border-start-0" style="cursor: pointer;">
                                                <i class="bi bi-eye-slash text-muted" id="togglePasswordConfirmation"></i>
                                            </span>
                                        </div>
                                        @error('password_confirmation')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-2">
                                <button type="button" class="btn btn-secondary shadow-sm rounded-pill px-4 me-md-2"
                                    onclick="window.location.href='{{route('dashboard')}}'"><i class="fas fa-times mr-1"></i> Cancelar</button>
                                <button type="submit" class="btn btn-primary shadow-sm rounded-pill px-4 font-weight-bold"><i class="fas fa-save mr-1"></i> Actualizar Perfil</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection