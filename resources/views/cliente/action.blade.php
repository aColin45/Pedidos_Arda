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

                    {{-- =================================== --}}
                    {{-- ||    CAMPO DE CÓDIGO      || --}}
                    {{-- =================================== --}}
                    <div class="col-md-6 mb-3">
                        <label for="codigo">Código</label>
                        <input type="text" name="codigo" id="codigo"
                               class="form-control @error('codigo') is-invalid @enderror"
                               {{-- Rellena el valor si existe (para editar) o usa el old input --}}
                               value="{{ old('codigo', $cliente->codigo ?? '') }}">
                        @error('codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- =================================== --}}

                </div> {{-- Cerramos el primer row --}}

                <div class="row"> {{-- Abrimos nuevo row para Email y Teléfono --}}

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

                </div> {{-- Cerramos el row de Email/Teléfono --}}

                <div class="row"> {{-- Abrimos nuevo row para Contacto y Dirección --}}
                    {{-- Campo Contacto --}}
                    <div class="col-md-6 mb-3"> {{-- Cambiado a col-md-6 --}}
                        <label for="contacto">Persona de Contacto</label>
                        <input type="text" name="contacto" class="form-control @error('contacto') is-invalid @enderror"
                               value="{{ old('contacto', $cliente->contacto ?? '') }}">
                        @error('contacto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Campo Dirección --}}
                    <div class="col-md-6 mb-3"> {{-- Cambiado a col-md-6 --}}
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" {{-- Cambiado a input por consistencia --}}
                               class="form-control @error('direccion') is-invalid @enderror"
                               value="{{ old('direccion', $cliente->direccion ?? '') }}">
                        @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div> {{-- Cerramos row de Contacto/Dirección --}}

                <div class="row">
                    {{-- Campo de Asignación de Agente (SOLO PARA ADMIN) --}}
                    @if(Auth::user()->hasRole('admin'))
                    <div class="col-md-6 mb-3">
                        <label for="user_id">Asignar Agente de Ventas</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required> {{-- Agregado required --}}
                            {{-- <option value="">-- Seleccione Agente --</option> --}} {{-- Quitado "Sin Agente" --}}
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
                            <option value="{{ number_format($opcion, 2, '.', '') }}" {{-- Usar '.' como separador decimal --}}
                                {{ old('descuento', $cliente->descuento ?? 0.00) == $opcion ? 'selected' : '' }}>
                                {{ number_format($opcion, 0) }}% {{-- Mostrar sin decimales --}}
                            </option>
                            @endforeach
                        </select>
                        @error('descuento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Campo Activo (Solo visible/editable por Admin) --}}
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
                        {{-- Si es agente, el cliente siempre se crea como activo --}}
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