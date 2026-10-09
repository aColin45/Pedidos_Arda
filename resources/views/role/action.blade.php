@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid">

        <div class="mb-4 mt-2">
            <a href="{{ route('roles.index') }}" class="text-decoration-none text-muted fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Volver a Roles
            </a>
        </div>

        <div class="card shadow border-0" style="border-radius: 12px; border-top: 4px solid {{ isset($registro) ? '#f6c23e' : '#1cc88a' }} !important;">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-{{ isset($registro) ? 'warning' : 'success' }}">
                    <i class="fas fa-{{ isset($registro) ? 'user-edit' : 'user-plus' }} mr-2"></i>
                    {{ isset($registro) ? 'Editar Rol: ' . $registro->name : 'Crear Nuevo Rol' }}
                </h6>
            </div>
            
            <form action="{{ isset($registro) ? route('roles.update', $registro->id) : route('roles.store') }}" method="POST" id="formRegistroUsuario">
                @csrf
                @if(isset($registro))
                    @method('PUT')
                @endif
                
                <div class="card-body bg-light p-4">
                    
                    {{-- Nombre del Rol --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="font-weight-bold text-dark mb-1">Nombre del Rol</label>
                            <input type="text" class="form-control form-control-lg fw-bold shadow-sm @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $registro->name ?? '') }}" required>
                            @error('name')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <hr class="mb-4">
                    <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-check-double text-success mr-2"></i>Asignación de Permisos</h5>
                    <p class="small text-muted mb-4">Seleccione las capacidades que tendrá este rol dentro de cada módulo del sistema.</p>

                    @error('permissions')
                        <div class="alert alert-danger shadow-sm border-0"><i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}</div>
                    @enderror

                    {{-- Agrupación dinámica por módulo --}}
                    @php
                        $permisosAgrupados = $permissions->groupBy(function($item) {
                            return ucfirst(explode('-', $item->name)[0]);
                        });
                    @endphp

                    <div class="row">
                        @foreach($permisosAgrupados as $modulo => $permisosModulo)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-dark text-white py-2">
                                        <div class="form-check m-0 d-flex align-items-center">
                                            <input class="form-check-input check-modulo me-2" type="checkbox" id="mod-{{ $modulo }}">
                                            <label class="form-check-label font-weight-bold m-0 w-100" for="mod-{{ $modulo }}" style="cursor: pointer;">
                                                Módulo {{ $modulo }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="d-flex flex-column gap-2 mt-2">
                                            @foreach($permisosModulo as $permiso)
                                                <div class="form-check custom-control custom-checkbox">
                                                    <input type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permiso->name }}" 
                                                           class="form-check-input perm-{{ $modulo }} custom-control-input" 
                                                           id="permiso_{{ $permiso->id }}"
                                                           {{ isset($registro) && $registro->permissions->contains('name', $permiso->name) ? 'checked' : '' }}>
                                                    <label class="form-check-label custom-control-label text-dark" for="permiso_{{ $permiso->id }}" style="cursor: pointer;">
                                                        {{ ucfirst(str_replace('-', ' ', $permiso->name)) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
                <div class="card-footer bg-white border-0 py-3 text-end rounded-bottom">
                    <button type="button" class="btn btn-secondary shadow-sm px-4 me-2" onclick="window.location.href='{{route('roles.index')}}'">Cancelar</button>
                    <button type="submit" class="btn btn-{{ isset($registro) ? 'warning' : 'success' }} fw-bold px-5 shadow-sm">
                        <i class="fas fa-save mr-2"></i>{{ isset($registro) ? 'Actualizar Rol' : 'Crear Rol' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('mnuSeguridad').classList.add('menu-open');
    document.getElementById('itemRole').classList.add('active');

    document.addEventListener('DOMContentLoaded', function() {
        const modulos = document.querySelectorAll('.check-modulo');
        
        modulos.forEach(moduloCheck => {
            moduloCheck.addEventListener('change', function() {
                const moduloNombre = this.id.replace('mod-', '');
                const permisosDelModulo = document.querySelectorAll('.perm-' + moduloNombre);
                
                permisosDelModulo.forEach(permiso => {
                    permiso.checked = this.checked;
                });
            });
        });
    });
</script>
@endpush