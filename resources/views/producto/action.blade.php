@extends('plantilla.app')
@section('contenido')
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        {{-- Usar $registro->id para determinar si es editar o nuevo --}}
                        <h3 class="card-title">
                            {{ isset($registro) && $registro->id ? 'Editar Producto' : 'Nuevo Producto' }}</h3>
                    </div>
                    <div class="card-body">
                        {{-- ======================================================= --}}
                        {{-- ||       INICIA CORRECCIÓN FORM ACTION Y METHOD      || --}}
                        {{-- ======================================================= --}}
                        <form {{-- Condición corregida: checar si $registro existe Y tiene ID --}}
                            action="{{ (isset($registro) && $registro->id) ? route('productos.update', $registro->id) : route('productos.store')}}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            {{-- Método PUT solo si estamos editando (registro con ID) --}}
                            @if(isset($registro) && $registro->id)
                            @method('PUT')
                            @endif
                            {{-- ======================================================= --}}
                            {{-- ||       TERMINA CORRECCIÓN FORM ACTION Y METHOD     || --}}
                            {{-- ======================================================= --}}

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="codigo" class="form-label">Código</label>
                                    <input type="text" class="form-control @error('codigo') is-invalid @enderror"
                                        id="codigo" name="codigo" value="{{old('codigo', $registro->codigo ??'')}}"
                                        required>
                                    @error('codigo')
                                    <small class="text-danger">{{$message}}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                        id="nombre" name="nombre" value="{{old('nombre', $registro->nombre ??'')}}"
                                        required>
                                    @error('nombre')
                                    <small class="text-danger">{{$message}}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="precio" class="form-label">Precio</label>
                                    <input type="number" step="0.01"
                                        class="form-control @error('precio') is-invalid @enderror" id="precio"
                                        name="precio" value="{{old('precio', $registro->precio ??'')}}" required
                                        min="0">
                                    @error('precio')
                                    <small class="text-danger">{{$message}}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row align-items-center">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check pt-4">
                                        <input type="hidden" name="aplica_iva" value="0">
                                        <input class="form-check-input" type="checkbox" value="1" id="aplica_iva"
                                            name="aplica_iva"
                                            {{ old('aplica_iva', isset($registro) ? $registro->aplica_iva : true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="aplica_iva">
                                            ¿Aplica IVA (16%)?
                                        </label>
                                        @error('aplica_iva')
                                        <div class="d-block invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="inner" class="form-label">Inner (Unidad de Empaque)</label>
                                    <input type="number" class="form-control @error('inner') is-invalid @enderror"
                                        id="inner" name="inner" value="{{ old('inner', $registro->inner ?? 1) }}"
                                        min="1" required>
                                    @error('inner')
                                    <small class="text-danger">{{$message}}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="imagen" class="form-label">Imagen</label>
                                    <input type="file" class="form-control @error('imagen') is-invalid @enderror"
                                        id="imagen" name="imagen" accept="image/*">
                                    @error('imagen')
                                    <small class="text-danger">{{$message}}</small>
                                    @enderror
                                    @if(isset($registro) && $registro->imagen)
                                    <div class="mt-2 text-center">
                                        <img src="{{ asset('uploads/productos/' . $registro->imagen) }}"
                                            alt="Imagen actual"
                                            style="max-width: 100px; height: auto; border-radius: 4px;">
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea name="descripcion"
                                        class="form-control @error('descripcion') is-invalid @enderror" id="descripcion"
                                        rows="3">{{ old('descripcion', $registro->descripcion ?? '') }}</textarea>
                                    @error('descripcion')
                                    <small class="text-danger">{{$message}}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="especificaciones" class="form-label">Especificaciones Técnicas</label>
                                    <textarea name="especificaciones"
                                        class="form-control @error('especificaciones') is-invalid @enderror"
                                        id="especificaciones"
                                        rows="5">{{ old('especificaciones', $registro->especificaciones ?? '') }}</textarea>
                                    <small class="form-text text-muted">
                                        Escribe cada especificación en una línea nueva. Separa la Característica y el
                                        Valor con un símbolo de pipe `|`.
                                        <br>
                                        <strong>Ejemplo:</strong><br>
                                        Capa interior|PVC color negro.<br>
                                        Capa intermedia|N/A<br>
                                        Presión de trabajo|60 PSI
                                    </small>
                                    @error('especificaciones')
                                    <small class="text-danger">{{$message}}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                                <a href="{{route('productos.index')}}" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
// IDs correctos para el menú
document.getElementById('mnuAlmacen').classList.add('menu-open');
document.getElementById('navProductos').classList.add('active'); // Asumiendo ID 'navProductos'
</script>
@endpush