@extends('plantilla.app')
@section('contenido')
<div class="app-content">
    <div class="container-fluid mt-4">
        <form action="{{ isset($registro) ? route('visitas.update', $registro->id) : route('visitas.store') }}" method="POST">
            @csrf
            @if(isset($registro)) @method('PUT') @endif

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="font-weight-bold text-primary mb-0">
                    <i class="fas fa-route mr-2"></i> {{ isset($registro) ? 'Editar Reporte de Visita' : 'Nuevo Reporte de Visita' }}
                </h3>
                <div>
                    @if(isset($registro))
                        <a href="{{ route('visitas.pdf', $registro->id) }}" target="_blank" class="btn btn-danger shadow-sm mr-2 font-weight-bold">
                            <i class="fas fa-file-pdf mr-1"></i> Ficha PDF
                        </a>
                    @endif
                    <a href="{{ route('visitas.index') }}" class="btn btn-secondary shadow-sm mr-2"><i class="fas fa-times mr-1"></i>Cancelar</a>
                    <button type="submit" class="btn btn-success shadow-sm font-weight-bold"><i class="fas fa-save mr-1"></i>Guardar Reporte</button>
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
                {{-- COLUMNA IZQUIERDA --}}
                <div class="col-lg-5">
                    <div class="card shadow-sm border-top-primary mb-4">
                        <div class="card-header bg-white"><h6 class="font-weight-bold text-primary mb-0">1. Datos Generales de la Visita</h6></div>
                        <div class="card-body">
                            
                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Fecha y hora de la visita <span class="text-danger">*</span></label>
                                @php
                                    $fechaInicial = isset($registro) && $registro->fecha_visita ? date('Y-m-d\TH:i', strtotime($registro->fecha_visita)) : date('Y-m-d\TH:i');
                                @endphp
                                <input type="datetime-local" class="form-control" name="fecha_visita" value="{{ old('fecha_visita', $fechaInicial) }}" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Modo de contacto <span class="text-danger">*</span></label>
                                <select name="modo_contacto" class="form-select form-control" required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach(['Visita en campo', 'Llamada', 'WhatsApp', 'Correo', 'Página web', 'Redes sociales', 'Expo', 'Otro'] as $modo)
                                        <option value="{{ $modo }}" {{ old('modo_contacto', $registro->modo_contacto ?? '') == $modo ? 'selected' : '' }}>{{ $modo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4 bg-light p-3 rounded border">
                                <label class="form-label font-weight-bold">¿A quién visitaste? <span class="text-danger">*</span></label>
                                <div class="d-flex mb-2 mt-1">
                                    <div class="custom-control custom-radio mr-4">
                                        <input type="radio" id="tipoProspecto" name="es_prospecto" class="custom-control-input" value="1" {{ (isset($registro) && $registro->es_prospecto) || old('es_prospecto') == '1' || !isset($registro) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tipoProspecto">Prospecto Nuevo</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="tipoCliente" name="es_prospecto" class="custom-control-input" value="0" {{ (isset($registro) && !$registro->es_prospecto) || old('es_prospecto') == '0' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tipoCliente">Cliente Actual</label>
                                    </div>
                                </div>

                                {{-- Selects Dinámicos --}}
                                <div id="divProspectos">
                                    <select name="prospecto_id" id="prospecto_id" class="form-select form-control">
                                        <option value="">-- Seleccionar Prospecto --</option>
                                        @foreach($prospectos as $pros)
                                            <option value="{{ $pros->id }}" {{ old('prospecto_id', $registro->prospecto_id ?? '') == $pros->id ? 'selected' : '' }}>{{ $pros->razon_social }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="divClientes" style="display: none;">
                                    <select name="cliente_id" id="cliente_id" class="form-select form-control">
                                        <option value="">-- Seleccionar Cliente --</option>
                                        @foreach($clientes as $cli)
                                            <option value="{{ $cli->id }}" {{ old('cliente_id', $registro->cliente_id ?? '') == $cli->id ? 'selected' : '' }}>{{ $cli->codigo }} - {{ $cli->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Persona que atendió</label>
                                    <input type="text" class="form-control" name="persona_atendio" value="{{ old('persona_atendio', $registro->persona_atendio ?? '') }}" placeholder="Nombre...">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cargo</label>
                                    <select class="form-select form-control" name="cargo_atendio">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach(['Dueño', 'Compras', 'Mostrador', 'Almacén', 'Otro'] as $cargo)
                                            <option value="{{ $cargo }}" {{ old('cargo_atendio', $registro->cargo_atendio ?? '') == $cargo ? 'selected' : '' }}>{{ $cargo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA --}}
                <div class="col-lg-7">
                    <div class="card shadow-sm border-top-info mb-4">
                        <div class="card-header bg-white"><h6 class="font-weight-bold text-info mb-0">2. Diagnóstico y Resultados</h6></div>
                        <div class="card-body">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Objetivo Principal <span class="text-danger">*</span></label>
                                    <select name="objetivo" class="form-select form-control" required>
                                        <option value="">-- Seleccionar --</option>
                                        @foreach(['Presentación de ARDA', 'Entrega de Catálogo/Muestras', 'Negociación de Precios', 'Seguimiento de Cotización', 'Levantamiento de Pedido', 'Cobranza', 'Visita de Cortesía', 'Otro'] as $obj)
                                            <option value="{{ $obj }}" {{ old('objetivo', $registro->objetivo ?? '') == $obj ? 'selected' : '' }}>{{ $obj }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Resultado de la Visita <span class="text-danger">*</span></label>
                                    <select name="resultado" id="selector_resultado" class="form-select form-control" required>
                                        <option value="">-- Seleccionar --</option>
                                        @foreach(['Venta / Pedido', 'Cotización', 'Revisarán catálogo', 'No interesado (Rechazo)', 'Cita Reprogramada', 'Otro'] as $res)
                                            <option value="{{ $res }}" {{ old('resultado', $registro->resultado ?? '') == $res ? 'selected' : '' }}>{{ $res }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- NUEVO: CAMPO DE MOTIVO DE RECHAZO --}}
                            <div class="row mb-3" id="campo_motivo_rechazo" style="display: none;">
                                <div class="col-md-12">
                                    <label class="form-label font-weight-bold text-danger">Motivo / Observaciones del Rechazo:</label>
                                    <textarea name="motivo_rechazo" class="form-control" rows="2" placeholder="Especifica por qué no está interesado...">{{ old('motivo_rechazo', $registro->motivo_rechazo ?? '') }}</textarea>
                                </div>
                            </div>

                            {{-- CHECKBOXES DE FAMILIAS (En lugar de texto libre) --}}
                            <div class="mb-3 p-3 bg-light rounded border">
                                <label class="form-label font-weight-bold text-dark mb-2">Líneas / Familias de Mayor Interés:</label>
                                <div class="row">
                                    @php
                                        $lineasOpciones = ['Mangueras', 'Abrazaderas', 'Cople y conexiones', 'Extensiones eléctricas', 'Cajas y tapas multiducto', 'Adhesivos / FIXY', 'Reguladores'];
                                        
                                        // Manejo inteligente: si viene de la vista previa (old) es un array, si viene de la base de datos es string.
                                        $viejasLineas = old('lineas_interes');
                                        
                                        if(is_array($viejasLineas)) {
                                            $lineasGuardadas = $viejasLineas;
                                        } else {
                                            $lineasString = $registro->lineas_interes ?? '';
                                            $lineasGuardadas = $lineasString ? array_map('trim', explode(',', $lineasString)) : [];
                                        }
                                    @endphp
                                    @foreach($lineasOpciones as $index => $linea)
                                        <div class="col-4 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="lineas_interes[]" id="linea_{{ $index }}" value="{{ $linea }}" {{ in_array($linea, $lineasGuardadas) ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-normal small" for="linea_{{ $index }}">{{ $linea }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-2">
                                    <input type="text" class="form-control form-control-sm" name="productos_especificos" value="{{ old('productos_especificos', $registro->productos_especificos ?? '') }}" placeholder="Especificar modelo o medida (Ej. Abrazadera sin fin 1/2)...">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Marcas de la competencia detectadas</label>
                                <input type="text" class="form-control" name="marcas_competencia" value="{{ old('marcas_competencia', $registro->marcas_competencia ?? '') }}" placeholder="Ej. Surtek, Truper, Iusa...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Necesidades o Problemas detectados</label>
                                <textarea name="necesidades_problemas" class="form-control" rows="2" placeholder="Ej. Tienen problemas con los envíos de su actual proveedor...">{{ old('necesidades_problemas', $registro->necesidades_problemas ?? '') }}</textarea>
                            </div>

                            <div class="row bg-white border rounded p-3 mx-0 shadow-sm mt-4">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold text-success">Monto Cotiz./Pedido ($)</label>
                                    <input type="number" step="0.01" class="form-control font-weight-bold text-success" name="monto_pedido" value="{{ old('monto_pedido', $registro->monto_pedido ?? '0.00') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold text-danger">Próximo Contacto</label>
                                    <input type="date" class="form-control border-danger" name="proximo_contacto" value="{{ old('proximo_contacto', isset($registro) && $registro->proximo_contacto ? date('Y-m-d', strtotime($registro->proximo_contacto)) : '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Acuerdos / Observaciones</label>
                                    <input type="text" class="form-control" name="compromisos" value="{{ old('compromisos', $registro->compromisos ?? '') }}" placeholder="Ej. Enviar ficha técnica...">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. LÓGICA DE PROSPECTO VS CLIENTE ---
        const radioProspecto = document.getElementById('tipoProspecto');
        const radioCliente = document.getElementById('tipoCliente');
        const divProspectos = document.getElementById('divProspectos');
        const divClientes = document.getElementById('divClientes');

        function toggleSelects() {
            if(radioProspecto.checked) {
                divProspectos.style.display = 'block';
                divClientes.style.display = 'none';
                document.getElementById('cliente_id').value = '';
            } else {
                divProspectos.style.display = 'none';
                divClientes.style.display = 'block';
                document.getElementById('prospecto_id').value = '';
            }
        }

        radioProspecto.addEventListener('change', toggleSelects);
        radioCliente.addEventListener('change', toggleSelects);
        toggleSelects(); 

        // --- 2. NUEVA LÓGICA: MOSTRAR/OCULTAR MOTIVO DE RECHAZO ---
        const selectResultado = document.getElementById('selector_resultado');
        const campoMotivoRechazo = document.getElementById('campo_motivo_rechazo');

        function toggleMotivoRechazo() {
            if (selectResultado.value === 'No interesado (Rechazo)') {
                campoMotivoRechazo.style.display = 'flex';
            } else {
                campoMotivoRechazo.style.display = 'none';
            }
        }
        
        if(selectResultado){
            toggleMotivoRechazo();
            selectResultado.addEventListener('change', toggleMotivoRechazo);
        }
    });
</script>
@endpush