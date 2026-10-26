@extends('plantilla.app')
@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid mt-4">
        
        <div class="mb-4 mt-2">
            <a href="{{ route('usuarios.index') }}" class="text-decoration-none text-muted font-weight-bold">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Usuarios
            </a>
        </div>

        <div class="card shadow border-0" style="border-radius: 12px; border-top: 4px solid #4e73df !important;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h4 class="card-title font-weight-bold text-primary mb-0">
                    <i class="fas fa-user-{{ isset($registro) ? 'edit' : 'plus' }} mr-2"></i>
                    {{ isset($registro) ? 'Editar Usuario: ' . $registro->name : 'Crear Nuevo Usuario' }}
                </h4>
            </div>
            
            <div class="card-body bg-light" style="border-radius: 0 0 12px 12px;">
                <form action="{{ isset($registro) ? route('usuarios.update', $registro->id) : route('usuarios.store')}}" method="POST" id="formRegistroUsuario">
                    @csrf
                    @if(isset($registro))
                        @method('PUT')
                    @endif
                    
                    {{-- 1. INFORMACIÓN BÁSICA --}}
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-id-card text-secondary mr-2"></i> Información Personal y Rol</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="name" class="form-label font-weight-bold small text-muted"> Nombre Completo <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-primary border-end-0"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control border-start-0 @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{old('name', $registro->name ??'')}}" required>
                                </div>
                                @error('name')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="email" class="form-label font-weight-bold small text-muted"> Correo Electrónico <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-primary border-end-0"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{old('email', $registro->email ??'')}}" required>
                                </div>
                                @error('email')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="role" class="form-label font-weight-bold small text-muted"> Asignar Rol <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select shadow-sm @error('role') is-invalid @enderror" required>
                                    <option value="">-- Elegir --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" 
                                            {{ (isset($registro) && $registro->hasRole($role->name)) || old('role') == $role->name ? 'selected' : '' }}>
                                            {{ strtoupper($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="activo" class="form-label font-weight-bold small text-muted"> Estado del Acceso</label>
                                <select class="form-select shadow-sm @error('activo') is-invalid @enderror" id="activo" name="activo">
                                    <option value="1" {{ old('activo', $registro->activo ?? '1') == '1' ? 'selected' : '' }}>🟢 Activo</option>
                                    <option value="0" {{ old('activo', $registro->activo ?? '1') == '0' ? 'selected' : '' }}>🔴 Inactivo</option>
                                </select>
                                @error('activo')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                            </div>
                        </div>
                    </div>
                    
                    {{-- 2. CREDENCIALES DE SEGURIDAD --}}
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-lock text-secondary mr-2"></i> Seguridad de la Cuenta</h6>
                        @if(isset($registro))
                            <p class="small text-muted mb-3"><i class="fas fa-info-circle mr-1"></i> Deja los campos en blanco si no deseas cambiar la contraseña de este usuario.</p>
                        @endif
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label font-weight-bold small text-muted"> Contraseña @if(!isset($registro)) <span class="text-danger">*</span> @endif</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-dark border-end-0"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror"
                                       id="password" name="password" placeholder="••••••••" @if(!isset($registro)) required @endif>
                                    <span class="input-group-text bg-white border-start-0" style="cursor: pointer;">
                                        <i class="bi bi-eye-slash text-muted" id="togglePassword"></i>
                                    </span>
                                </div>
                                @error('password')<small class="text-danger mt-1 d-block">{{$message}}</small>@enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label font-weight-bold small text-muted"> Confirme la contraseña</label>
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

                    {{-- 3. SECCIÓN DE METAS DE VENTA (SÓLO PARA ADMIN) --}}
                    @hasanyrole('admin|super-admin|superadmin')
                    <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #f6c23e !important; border-radius: 12px;">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h6 class="font-weight-bold text-warning mb-0"><i class="fas fa-bullseye mr-2"></i> Configuración de Metas de Ventas (Solo Agentes)</h6>
                        </div>
                        <div class="card-body bg-light" style="border-radius: 0 0 12px 12px;">
                            <p class="small text-muted mb-3"> Establezca la meta mensual base. El saldo acumulado se calculará automáticamente mes a mes, pero puede ajustarlo manualmente si es necesario.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold small text-dark"> Meta Mensual Base ($)</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white text-success font-weight-bold">$</span>
                                        <input type="number" step="0.01" class="form-control font-weight-bold text-success" name="meta_mensual_base" value="{{ old('meta_mensual_base', $registro->meta_mensual_base ?? '0.00') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold small text-dark"> Saldo Acumulado (Meses Anteriores)</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white text-secondary font-weight-bold">$</span>
                                        <input type="number" step="0.01" class="form-control" name="saldo_meta_acumulado" value="{{ old('saldo_meta_acumulado', $registro->saldo_meta_acumulado ?? '0.00') }}" placeholder="Positivo = Debe, Negativo = Superávit">
                                    </div>
                                    <small class="text-danger mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Si el agente debe dinero de la meta, escriba en positivo (ej. 20000). Si sobró a favor, en negativo (ej. -5000).</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endhasanyrole

                    {{-- 4. SELECTOR DE PRODUCTOS DEL CATÁLOGO --}}
                    @if(isset($productosCatalogo) && $productosCatalogo->count() > 0)
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                            <h6 class="fw-bold text-primary mb-0"><i class="fas fa-tags me-2"></i>Productos Exclusivos y Precios Especiales</h6>
                        </div>
                        <p class="text-muted small mb-3"> Seleccione los productos que tendrán reglas especiales para este usuario.</p>
                        
                        {{-- BUSCADOR RÁPIDO --}}
                        <div class="input-group mb-4 shadow-sm" style="max-width: 500px;">
                            <span class="input-group-text bg-light text-primary border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" id="buscadorProductos" class="form-control border-start-0 bg-light" placeholder="Buscar por código o nombre del producto...">
                        </div>
                        
                        <div class="row g-3" id="contenedorProductos" style="max-height: 450px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;">
                            @foreach($productosCatalogo as $prod)
                                @php
                                    $tieneProducto = isset($registro) && $registro->productosEspeciales->contains($prod->id);
                                    $precioPivot = $tieneProducto ? $registro->productosEspeciales->find($prod->id)->pivot->precio_especial : '';
                                    $innerPivot = $tieneProducto ? $registro->productosEspeciales->find($prod->id)->pivot->inner_especial : '';
                                @endphp
                                <div class="col-md-6 col-lg-4 item-producto">
                                    <div class="form-check border rounded p-3 bg-white h-100 shadow-sm transition-hover {{ $prod->es_especial ? 'border-warning' : '' }}">
                                        {{-- Checkbox del producto --}}
                                        <input class="form-check-input ms-1 me-2 mt-1" 
                                            type="checkbox" 
                                            name="productos_especiales[]" 
                                            value="{{ $prod->id }}" 
                                            id="prod_{{ $prod->id }}"
                                            {{ $tieneProducto ? 'checked' : '' }}
                                            onchange="document.getElementById('precio_{{ $prod->id }}').disabled = !this.checked; document.getElementById('inner_{{ $prod->id }}').disabled = !this.checked; if(!this.checked){ document.getElementById('precio_{{ $prod->id }}').value=''; document.getElementById('inner_{{ $prod->id }}').value=''; }">
                                        
                                        <label class="form-check-label w-100 text-buscador" style="cursor:pointer; font-size: 0.85rem;" for="prod_{{ $prod->id }}">
                                            <strong class="text-dark">{{ $prod->codigo }}</strong>
                                            @if($prod->es_especial) 
                                                <i class="fas fa-star text-warning float-end" title="Producto Invisible/Exclusivo"></i> 
                                            @endif
                                            <br>
                                            <span class="text-muted" style="line-height: 1.2; display:block; margin-top: 3px; height: 32px; overflow: hidden;">{{ Str::limit($prod->nombre, 45) }}</span>
                                        </label>

                                        {{-- Inputs para Precio e Inner especial --}}
                                        <div class="mt-2 pt-2 border-top">
                                            <div class="row g-2">
                                                {{-- Columna Precio --}}
                                                <div class="col-6">
                                                    <label class="text-muted mb-1 font-weight-bold" style="font-size: 0.65rem; display:block;">Precio (Base: ${{ number_format($prod->precio, 2) }})</label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-light fw-bold text-success">$</span>
                                                        <input type="number" step="0.01" min="0" 
                                                            class="form-control text-success fw-bold px-1" 
                                                            name="precios_especiales[{{ $prod->id }}]" 
                                                            id="precio_{{ $prod->id }}"
                                                            placeholder="P. Esp."
                                                            value="{{ $precioPivot }}"
                                                            {{ $tieneProducto ? '' : 'disabled' }}>
                                                    </div>
                                                </div>
                                                
                                                {{-- Columna Inner --}}
                                                <div class="col-6">
                                                    <label class="text-muted mb-1 font-weight-bold" style="font-size: 0.65rem; display:block;">Inner (Base: {{ $prod->inner ?? 1 }})</label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-light text-primary"><i class="fas fa-box-open"></i></span>
                                                        <input type="number" step="1" min="1" 
                                                            class="form-control text-primary fw-bold px-1" 
                                                            name="inners_especiales[{{ $prod->id }}]" 
                                                            id="inner_{{ $prod->id }}"
                                                            placeholder="Inn. Esp."
                                                            value="{{ $innerPivot }}"
                                                            {{ $tieneProducto ? '' : 'disabled' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    {{-- 5. BOTONES DE ACCIÓN --}}
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-2">
                        <button type="button" class="btn btn-secondary shadow-sm rounded-pill px-4 me-md-2"
                            onclick="window.location.href='{{route('usuarios.index')}}'"><i class="fas fa-times mr-1"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary shadow-sm rounded-pill px-4 font-weight-bold"><i class="fas fa-save mr-1"></i> {{ isset($registro) ? 'Actualizar Usuario' : 'Crear Usuario' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('estilos')
<style>
    .transition-hover {
        transition: all 0.2s ease-in-out;
    }
    .transition-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    /* Estilizar la barra de scroll del contenedor de productos */
    #contenedorProductos::-webkit-scrollbar {
        width: 8px;
    }
    #contenedorProductos::-webkit-scrollbar-track {
        background: #f1f1f1; 
        border-radius: 10px;
    }
    #contenedorProductos::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 10px;
    }
    #contenedorProductos::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('mnuSeguridad').classList.add('menu-open');
    document.getElementById('itemUsuario').classList.add('active');

    // Script para el buscador rápido de productos en el formulario
    const buscador = document.getElementById('buscadorProductos');
    if(buscador) {
        buscador.addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let items = document.querySelectorAll('.item-producto');
            
            items.forEach(function(item) {
                let texto = item.querySelector('.text-buscador').innerText.toLowerCase();
                if(texto.includes(filtro)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
</script>
@endpush