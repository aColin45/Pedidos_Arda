@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ isset($cliente) ? 'Editar Cliente' : 'Nuevo Cliente' }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($cliente) ? route('clientes.update', $cliente->id) : route('clientes.store') }}"
                  method="POST">
                @csrf
                @if(isset($cliente))
                    @method('PUT')
                @endif

                <div class="row">
                    {{-- Campo Nombre --}}
                    <div class="col-md-6 mb-3">
                        <label for="nombre">Nombre del Cliente</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $cliente->nombre ?? '') }}" required>
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Campo de Código --}}
                    <div class="col-md-6 mb-3">
                        <label for="codigo">Código</label>
                        <input type="text" name="codigo" id="codigo"
                               class="form-control @error('codigo') is-invalid @enderror"
                               value="{{ old('codigo', $cliente->codigo ?? '') }}">
                        @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div> 

                <div class="row"> 
                    {{-- Campo Email --}}
                    <div class="col-md-6 mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $cliente->email ?? '') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Campo Teléfono --}}
                    <div class="col-md-6 mb-3">
                        <label for="telefono">Teléfono</label>
                        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                               value="{{ old('telefono', $cliente->telefono ?? '') }}">
                        @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div> 

                <div class="row">
                    {{-- Campo Contacto (Lista de Precios) --}}
                    <div class="col-md-6 mb-3"> 
                        <label for="contacto">Lista de Precios</label>  
                        <input type="text" name="contacto" class="form-control @error('contacto') is-invalid @enderror"
                               value="{{ old('contacto', $cliente->contacto ?? '') }}">
                        @error('contacto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- NUEVO: Campo Estado (Para los precios) --}}
                    <div class="col-md-6 mb-3">
                        <label for="estado">Estado <small class="text-muted">(Para Zona de Precios, Ej: MICHOACAN)</small></label>
                        <input type="text" name="estado" class="form-control @error('estado') is-invalid @enderror"
                               value="{{ old('estado', $cliente->estado ?? '') }}">
                        @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div> 

                {{-- NUEVO: Fila completa para la Dirección --}}
                <div class="row">
                    <div class="col-md-12 mb-3"> 
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" 
                               class="form-control @error('direccion') is-invalid @enderror"
                               value="{{ old('direccion', $cliente->direccion ?? '') }}" 
                               placeholder="Calle, Número, Colonia, Municipio/Ciudad, C.P.">
                        @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Campo de Asignación de Agente (SOLO PARA ADMIN) --}}
                    @if(Auth::user()->hasRole('admin'))
                    <div class="col-md-6 mb-3">
                        <label for="user_id">Asignar Agente de Ventas</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required> 
                            @foreach($agentes as $agente)
                            <option value="{{ $agente->id }}"
                                {{ old('user_id', $cliente->user_id ?? '') == $agente->id ? 'selected' : '' }}>
                                {{ $agente->name }} ({{ $agente->email }})
                            </option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @else
                        {{-- Si no es admin, incluimos el user_id del agente logueado de forma oculta --}}
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                    @endif

                    {{-- Campo Descuento --}}
                    <div class="col-md-6 mb-3">
                        <label for="descuento">Descuento (%)</label>
                        <select name="descuento" class="form-select @error('descuento') is-invalid @enderror" required>
                            <option value="0.00"
                                {{ old('descuento', $cliente->descuento ?? 0.00) == 0.00 ? 'selected' : '' }}>0% (Sin Descuento)</option>
                            @php
                            $opcionesDescuento = [32.00, 34.00, 36.00, 40.00];
                            @endphp
                            @foreach($opcionesDescuento as $opcion)
                            <option value="{{ number_format($opcion, 2, '.', '') }}" 
                                {{ old('descuento', $cliente->descuento ?? 0.00) == $opcion ? 'selected' : '' }}>
                                {{ number_format($opcion, 0) }}% 
                            </option>
                            @endforeach
                        </select>
                        @error('descuento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- NUEVO BLOQUE: CONDICIONES DE CRÉDITO Y PAGOS (SOLO ADMIN) --}}
                {{-- ============================================================ --}}
                @if(Auth::user()->hasRole('admin'))
                <div class="row mt-2 mb-3 pb-3 border-bottom">
                    <div class="col-12 mb-2">
                        <h6 class="text-success font-weight-bold"><i class="fas fa-money-check-alt mr-1"></i> Condiciones de Crédito y Pagos</h6>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label text-muted small mb-1">Monto Crédito ($)</label>
                        <input type="number" step="0.01" name="monto_credito" class="form-control" value="{{ old('monto_credito', isset($cliente) ? $cliente->monto_credito : '0.00') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label text-muted small mb-1">Días de Crédito</label>
                        <input type="number" name="dias_credito" class="form-control" value="{{ old('dias_credito', isset($cliente) ? $cliente->dias_credito : '0') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label text-muted small mb-1">Otorgado el</label>
                        <input type="date" name="fecha_otorgamiento" class="form-control" value="{{ old('fecha_otorgamiento', isset($cliente) && $cliente->fecha_otorgamiento ? \Carbon\Carbon::parse($cliente->fecha_otorgamiento)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label text-muted small mb-1">Referencia Bancaria</label>
                        <input type="text" name="referencia_bancaria" class="form-control" value="{{ old('referencia_bancaria', isset($cliente) ? $cliente->referencia_bancaria : '') }}">
                    </div>
                </div>
                @endif
                {{-- ============================================================ --}}

                <div class="row">
                    {{-- Campo Activo --}}
                    @if(Auth::user()->hasRole('admin'))
                        <div class="col-md-6 mb-3">
                            <label for="activo">Estado del Cliente</label>
                            <select name="activo" class="form-select @error('activo') is-invalid @enderror" required>
                                <option value="1" {{ old('activo', $cliente->activo ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo', $cliente->activo ?? 1) == 0 ? 'selected' : '' }}>Inhabilitado</option>
                            </select>
                            @error('activo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @else
                        <input type="hidden" name="activo" value="1">
                    @endif
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection