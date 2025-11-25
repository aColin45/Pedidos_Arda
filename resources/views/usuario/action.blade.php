@extends('plantilla.app')
@section('contenido')
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ isset($registro) ? 'Editar Usuario' : 'Nuevo Usuario' }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ isset($registro) ? route('usuarios.update', $registro->id) : route('usuarios.store')}}" method="POST" id="formRegistroUsuario">
                            @csrf
                            @if(isset($registro))
                                @method('PUT')
                            @endif
                            
                            {{-- PRIMERA FILA: Nombre, Email, Activo --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="name" class="form-label">Nombre</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{old('name', $registro->name ??'')}}" required>
                                       @error('name')
                                            <small class="text-danger">{{$message}}</small>
                                       @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{old('email', $registro->email ??'')}}" required>
                                       @error('email')
                                            <small class="text-danger">{{$message}}</small>
                                       @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="activo" class="form-label">Activo</label>
                                    <select class="form-select @error('activo') is-invalid @enderror" id="activo" name="activo">
                                        <option value="1" {{ old('activo', $registro->activo ?? '1') == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('activo', $registro->activo ?? '1') == '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                       @error('activo')
                                            <small class="text-danger">{{$message}}</small>
                                       @enderror
                                </div>
                            </div>
                            
                            {{-- SEGUNDA FILA: Contraseñas y Rol --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    {{-- El campo password es requerido SOLO en creación, o si se modifica --}}
                                    <label for="password" class="form-label">Password</label>
                                    <input type="text" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" value="{{old('password')}}" 
                                       @if(!isset($registro)) required @endif>
                                       @error('password')
                                            <small class="text-danger">{{$message}}</small>
                                       @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirme el password</label>
                                    <input type="text" class="form-control @error('password_confirmation') is-invalid @enderror"
                                       id="password_confirmation" name="password_confirmation" value="{{old('password_confirmation')}}">
                                       @error('password_confirmation')
                                            <small class="text-danger">{{$message}}</small>
                                       @enderror
                                </div> 
                                <div class="col-md-4 mb-3">
                                    <label for="role" class="form-label">Rol</label>
                                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="">-- Seleccionar Rol --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" 
                                                {{ (isset($registro) && $registro->hasRole($role->name)) || old('role') == $role->name ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                         <small class="text-danger">{{$message}}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            {{-- Botones --}}
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary me-md-2"
                                    onclick="window.location.href='{{route('usuarios.index')}}'">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer clearfix">

                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    document.getElementById('mnuSeguridad').classList.add('menu-open');
    document.getElementById('itemUsuario').classList.add('active');
</script>
@endpush