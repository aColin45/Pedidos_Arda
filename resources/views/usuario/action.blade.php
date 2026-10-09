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

                                {{-- SECCIÓN DE METAS DE VENTA (SÓLO VISIBLE/EDITABLE PARA ADMIN) --}}
                                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                                <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #f6c23e;">
                                    <div class="card-header bg-white pb-0">
                                        <h6 class="font-weight-bold text-warning"><i class="fas fa-bullseye mr-2"></i>Configuración de Metas de Ventas</h6>
                                    </div>
                                    <div class="card-body bg-light">
                                        <p class="small text-muted mb-3">Establezca la meta mensual base. El saldo acumulado se calculará automáticamente mes a mes, pero puede ajustarlo manualmente si es necesario.</p>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label font-weight-bold">Meta Mensual Base ($)</label>
                                                {{-- CAMBIO AQUÍ: Usamos $registro en lugar de $usuario --}}
                                                <input type="number" step="0.01" class="form-control font-weight-bold text-primary" name="meta_mensual_base" value="{{ old('meta_mensual_base', $registro->meta_mensual_base ?? '0.00') }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label font-weight-bold">Saldo Acumulado de Meses Anteriores ($)</label>
                                                {{-- CAMBIO AQUÍ: Usamos $registro en lugar de $usuario --}}
                                                <input type="number" step="0.01" class="form-control" name="saldo_meta_acumulado" value="{{ old('saldo_meta_acumulado', $registro->saldo_meta_acumulado ?? '0.00') }}" placeholder="Positivo = Debe, Negativo = Superávit">
                                                <small class="text-danger">Si el agente debe dinero de la meta, escriba en positivo (ej. 20000). Si sobró a favor, en negativo (ej. -5000).</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                            </div>

                            {{-- ========================================== --}}
                            {{-- SELECTOR DE PRODUCTOS DEL CATÁLOGO CON PRECIO E INNER ESPECIAL --}}
                            {{-- ========================================== --}}
                            @if(isset($productosCatalogo) && $productosCatalogo->count() > 0)
                            <div class="col-12 mt-4 mb-3">
                                <div class="card shadow-sm border-0 bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold text-primary mb-0">
                                                <i class="fas fa-tags me-2 text-primary"></i>Asignación de Productos Exclusivos y Reglas Especiales
                                            </h6>
                                        </div>
                                        <p class="text-muted small mb-3">Seleccione los productos que tendrán reglas especiales para este usuario (visibilidad exclusiva, precio diferente o inner personalizado).</p>
                                        
                                        {{-- BUSCADOR RÁPIDO --}}
                                        <div class="input-group mb-3 shadow-sm">
                                            <span class="input-group-text bg-white text-primary border-end-0"><i class="fas fa-search"></i></span>
                                            <input type="text" id="buscadorProductos" class="form-control border-start-0" placeholder="Buscar rápido por código o nombre del producto...">
                                        </div>
                                        
                                        <div class="row g-3" id="contenedorProductos" style="max-height: 450px; overflow-y: auto; overflow-x: hidden;">
                                            @foreach($productosCatalogo as $prod)
                                                @php
                                                    $tieneProducto = isset($registro) && $registro->productosEspeciales->contains($prod->id);
                                                    $precioPivot = $tieneProducto ? $registro->productosEspeciales->find($prod->id)->pivot->precio_especial : '';
                                                    $innerPivot = $tieneProducto ? $registro->productosEspeciales->find($prod->id)->pivot->inner_especial : '';
                                                @endphp
                                                <div class="col-md-6 col-lg-4 item-producto">
                                                    <div class="form-check border rounded p-3 bg-white h-100 shadow-sm {{ $prod->es_especial ? 'border-warning' : '' }}">
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
                                                            <span class="text-muted" style="line-height: 1.1; display:block; margin-top: 3px;">{{ Str::limit($prod->nombre, 40) }}</span>
                                                        </label>

                                                        {{-- Inputs para Precio e Inner especial --}}
                                                        <div class="mt-2 pt-2 border-top">
                                                            <div class="row g-2">
                                                                {{-- Columna Precio --}}
                                                                <div class="col-6">
                                                                    <label class="text-muted mb-1" style="font-size: 0.65rem; display:block;">Precio (Base: ${{ number_format($prod->precio, 2) }})</label>
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
                                                                    <label class="text-muted mb-1" style="font-size: 0.65rem; display:block;">Inner (Base: {{ $prod->inner ?? 1 }})</label>
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
                                </div>
                            </div>
                            @endif
                            
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