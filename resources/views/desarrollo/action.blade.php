@extends('plantilla.app')
@section('contenido')
<div class="app-content">
    <div class="container-fluid mt-4">
        <form action="{{ isset($registro) ? route('desarrollos.update', $registro->id) : route('desarrollos.store') }}" method="POST">
            @csrf
            @if(isset($registro)) @method('PUT') @endif

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="font-weight-bold text-primary mb-0">
                    <i class="fas fa-lightbulb mr-2"></i> {{ isset($registro) ? 'Editar Solicitud de Desarrollo' : 'Nueva Solicitud de Desarrollo' }}
                </h3>
                <div>
                    @if(isset($registro))
                        <a href="{{ route('desarrollos.pdf', $registro->id) }}" target="_blank" class="btn btn-danger shadow-sm mr-2 font-weight-bold">
                            <i class="fas fa-file-pdf mr-1"></i> Ficha PDF
                        </a>
                    @endif
                    <a href="{{ route('desarrollos.index') }}" class="btn btn-secondary shadow-sm mr-2"><i class="fas fa-times mr-1"></i>Cancelar</a>
                    <button type="submit" class="btn btn-success shadow-sm font-weight-bold"><i class="fas fa-save mr-1"></i>Guardar Solicitud</button>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger shadow-sm">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                {{-- COLUMNA IZQUIERDA: DATOS DE LA SOLICITUD --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom-primary"><h6 class="font-weight-bold text-primary mb-0">Datos de la Solicitud</h6></div>
                        <div class="card-body">
                            
                            <div class="mb-4 bg-light p-3 rounded border">
                                <label class="form-label font-weight-bold text-primary">1. ¿Qué tipo de acción necesitas? <span class="text-danger">*</span></label>
                                <div class="d-flex mb-2 mt-1">
                                    <div class="custom-control custom-radio mr-4">
                                        <input type="radio" id="tipoNuevo" name="tipo_accion" class="custom-control-input" value="Solicitud de producto completamente nuevo" {{ old('tipo_accion', $registro->tipo_accion ?? '') == 'Solicitud de producto completamente nuevo' || !isset($registro) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tipoNuevo">Solicitud de producto completamente nuevo</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="tipoMod" name="tipo_accion" class="custom-control-input" value="Modificación / variante de producto existente" {{ old('tipo_accion', $registro->tipo_accion ?? '') == 'Modificación / variante de producto existente' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tipoMod">Modificación / variante de producto existente</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Línea de producto afectada</label>
                                    <select name="linea_producto" class="form-select form-control">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach(['Mangueras', 'Abrazaderas', 'Cople y conexiones', 'Extensiones eléctricas', 'Cajas y tapas multiducto', 'Adhesivos / FIXY', 'Reguladores', 'Otro'] as $linea)
                                            <option value="{{ $linea }}" {{ old('linea_producto', $registro->linea_producto ?? '') == $linea ? 'selected' : '' }}>{{ $linea }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Producto específico relacionado</label>
                                    <input type="text" class="form-control" name="producto_especifico" value="{{ old('producto_especifico', $registro->producto_especifico ?? '') }}" placeholder="Ej. Abrazadera sin fin 1/2 pulgada">
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <label class="form-label font-weight-bold">¿Qué producto o característica solicitan exactamente? <span class="text-danger">*</span></label>
                                <textarea name="descripcion" class="form-control" rows="3" required placeholder="Describe a detalle lo que se necesita...">{{ old('descripcion', $registro->descripcion ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">¿Por qué lo solicitan? (Justificación / Volumen de venta esperado) <span class="text-danger">*</span></label>
                                <textarea name="justificacion" class="form-control" rows="3" required placeholder="Explica el beneficio comercial para ARDA...">{{ old('justificacion', $registro->justificacion ?? '') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">¿Quién originó esta necesidad? <span class="text-danger">*</span></label>
                                    <select name="origen_necesidad" class="form-select form-control" required>
                                        <option value="">-- Seleccionar --</option>
                                        <option value="Cliente" {{ old('origen_necesidad', $registro->origen_necesidad ?? '') == 'Cliente' ? 'selected' : '' }}>Cliente</option>
                                        <option value="Prospecto" {{ old('origen_necesidad', $registro->origen_necesidad ?? '') == 'Prospecto' ? 'selected' : '' }}>Prospecto</option>
                                        <option value="Iniciativa Propia del Agente" {{ old('origen_necesidad', $registro->origen_necesidad ?? '') == 'Iniciativa Propia del Agente' ? 'selected' : '' }}>Iniciativa Propia del Agente</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Cliente de Referencia <small class="text-muted">(Opcional)</small></label>
                                    <input type="text" class="form-control" name="cliente_referencia" value="{{ old('cliente_referencia', $registro->cliente_referencia ?? '') }}" placeholder="Nombre del cliente o prospecto...">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: PANEL DE ADMINISTRACIÓN --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4 bg-dark">
                        <div class="card-header bg-dark text-white border-bottom-0"><h6 class="font-weight-bold mb-0"><i class="fas fa-tools mr-2"></i>Panel de Evaluación (Admin)</h6></div>
                        <div class="card-body bg-light" style="border-radius: 0 0 4px 4px;">
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold text-dark">Resolución de la Solicitud:</label>
                                @role('admin|superadmin')
                                    <select name="estatus" class="form-select form-control font-weight-bold">
                                        @php $est = old('estatus', $registro->estatus ?? 'Pendiente / En Revisión'); @endphp
                                        <option value="Pendiente / En Revisión" {{ $est == 'Pendiente / En Revisión' ? 'selected' : '' }}>🟡 Pendiente / En Revisión</option>
                                        <option value="Aprobado / En Desarrollo" {{ $est == 'Aprobado / En Desarrollo' ? 'selected' : '' }}>🟢 Aprobado / En Desarrollo</option>
                                        <option value="Rechazado / No Viable" {{ $est == 'Rechazado / No Viable' ? 'selected' : '' }}>🔴 Rechazado / No Viable</option>
                                    </select>
                                @else
                                    {{-- Vista de solo lectura para el Agente --}}
                                    <input type="hidden" name="estatus" value="{{ $registro->estatus ?? 'Pendiente / En Revisión' }}">
                                    @php $est = strtolower($registro->estatus ?? 'Pendiente / En Revisión'); @endphp
                                    <div class="alert mb-0 text-center font-weight-bold border-0 shadow-sm
                                        @if(str_contains($est, 'aprobado')) alert-success 
                                        @elseif(str_contains($est, 'rechazado')) alert-danger 
                                        @else alert-warning text-dark @endif">
                                        {{ $registro->estatus ?? 'Pendiente / En Revisión' }}
                                    </div>
                                    <small class="text-muted mt-2 d-block text-center">* Solo un administrador puede cambiar este estado.</small>
                                @endrole
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection