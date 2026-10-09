@extends('plantilla.app')
@section('contenido')
<div class="app-content">
    <div class="container-fluid mt-4">
        <form action="{{ isset($registro) ? route('prospectos.update', $registro->id) : route('prospectos.store') }}" method="POST" id="formProspecto">
            @csrf
            @if(isset($registro)) @method('PUT') @endif

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="font-weight-bold text-primary mb-0">
                    <i class="fas fa-user-plus mr-2"></i> {{ isset($registro) ? 'Editar Prospecto' : 'Registrar Nuevo Prospecto' }}
                </h3>
                <div>
                    {{-- NUEVO BOTÓN DE EXPORTACIÓN INDIVIDUAL --}}
                    @if(isset($registro))
                        <a href="{{ route('prospectos.pdf', $registro->id) }}" target="_blank" class="btn btn-danger shadow-sm mr-2 font-weight-bold">
                            <i class="fas fa-file-pdf mr-1"></i> Ficha PDF
                        </a>
                    @endif
                    <a href="{{ route('prospectos.index') }}" class="btn btn-secondary shadow-sm mr-2"><i class="fas fa-times mr-1"></i>Cancelar</a>
                    <button type="submit" class="btn btn-success shadow-sm font-weight-bold"><i class="fas fa-save mr-1"></i>Guardar Prospecto</button>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger shadow-sm">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            {{-- PANEL DE ESTATUS HASTA ARRIBA (MÁS VISIBLE) --}}
            <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #f6c23e;">
                <div class="card-body bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-md-end"><label class="form-label font-weight-bold text-dark mb-0">Etapa actual del prospecto:</label></div>
                        <div class="col-md-9">
                            <select name="estatus" id="selector_estatus" class="form-select form-control font-weight-bold" style="font-size: 1.1rem;">
                                <option value="nuevo" {{ old('estatus', $registro->estatus ?? '') == 'nuevo' ? 'selected' : '' }}>🔵 Nuevo Prospecto</option>
                                <option value="en_seguimiento" {{ old('estatus', $registro->estatus ?? '') == 'en_seguimiento' ? 'selected' : '' }}>🟡 En Seguimiento Activo</option>
                                <option value="convertido" {{ old('estatus', $registro->estatus ?? '') == 'convertido' ? 'selected' : '' }}>🟢 Venta Realizada (Convertir a Cliente)</option>
                                <option value="descartado" {{ old('estatus', $registro->estatus ?? '') == 'descartado' ? 'selected' : '' }}>⚫ Descartado / No viable</option>
                            </select>
                        </div>
                    </div>
                    {{-- NUEVO: CAMPO DE MOTIVO DE DESCARTE (OCULTO POR DEFECTO) --}}
                    <div class="row align-items-center mt-3" id="campo_motivo_descarte" style="display: none;">
                        <div class="col-md-3 text-md-end"><label class="form-label font-weight-bold text-danger mb-0">Motivo / Observaciones del Rechazo:</label></div>
                        <div class="col-md-9">
                            <textarea name="motivo_descarte" class="form-control" rows="2" placeholder="Escribe aquí por qué no es viable este prospecto...">{{ old('motivo_descarte', $registro->motivo_descarte ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- COLUMNA IZQUIERDA --}}
                <div class="col-lg-7">
                    
                    {{-- 1. DATOS GENERALES --}}
                    <div class="card shadow-sm border-top-primary mb-4">
                        <div class="card-header bg-white"><h6 class="font-weight-bold text-primary mb-0"><i class="fas fa-building mr-2"></i>Datos Generales del Negocio</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label font-weight-bold">Nombre o Razón Social <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="razon_social" value="{{ old('razon_social', $registro->razon_social ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre Comercial</label>
                                    <input type="text" class="form-control" name="nombre_comercial" value="{{ old('nombre_comercial', $registro->nombre_comercial ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">RFC</label>
                                    <input type="text" class="form-control text-uppercase" name="rfc" value="{{ old('rfc', $registro->rfc ?? '') }}" maxlength="13">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label font-weight-bold">Giro / Tipo de Negocio <span class="text-danger">*</span></label>
                                    <select name="giro_negocio" class="form-select form-control" required>
                                        <option value="">-- Seleccionar --</option>
                                        @foreach(['Ferretería', 'Plomería', 'Eléctrico', 'Distribuidor', 'Mayorista', 'Casa de materiales', 'Constructor', 'Industria', 'Autoservicio', 'Gobierno', 'Ecommerce', 'Otro'] as $giro)
                                            <option value="{{ $giro }}" {{ old('giro_negocio', $registro->giro_negocio ?? '') == $giro ? 'selected' : '' }}>{{ $giro }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. CONTACTO PRINCIPAL --}}
                    <div class="card shadow-sm border-top-info mb-4">
                        <div class="card-header bg-white"><h6 class="font-weight-bold text-info mb-0"><i class="fas fa-id-badge mr-2"></i>Datos del Contacto</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label font-weight-bold">Nombre del Contacto <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="contacto_nombre" value="{{ old('contacto_nombre', $registro->contacto_nombre ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Puesto / Cargo <span class="text-danger">*</span></label>
                                    <select name="contacto_puesto" class="form-select form-control" required>
                                        <option value="">-- Seleccionar --</option>
                                        @foreach(['Dueño', 'Compras', 'Gerente', 'Director', 'Ventas', 'Almacén', 'Administración', 'Cuentas por Pagar', 'Otro'] as $puesto)
                                            <option value="{{ $puesto }}" {{ old('contacto_puesto', $registro->contacto_puesto ?? '') == $puesto ? 'selected' : '' }}>{{ $puesto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    {{-- DEPARTAMENTO AHORA ES SELECT --}}
                                    <label class="form-label">Departamento</label>
                                    <select name="contacto_departamento" class="form-select form-control">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach(['Dirección', 'Ventas', 'Compras', 'Almacén / Logística', 'Pagos / Cobranza', 'Otro'] as $depto)
                                            <option value="{{ $depto }}" {{ old('contacto_departamento', $registro->contacto_departamento ?? '') == $depto ? 'selected' : '' }}>{{ $depto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Teléfono Directo</label>
                                    <input type="text" class="form-control" name="telefono" value="{{ old('telefono', $registro->telefono ?? '') }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email', $registro->email ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. UBICACIÓN --}}
                    <div class="card shadow-sm border-top-secondary mb-4">
                        <div class="card-header bg-white"><h6 class="font-weight-bold text-secondary mb-0"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación Geográfica</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Estado <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="estado" value="{{ old('estado', $registro->estado ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Municipio / Delegación <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="municipio" value="{{ old('municipio', $registro->municipio ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" name="ciudad" value="{{ old('ciudad', $registro->ciudad ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Código Postal</label>
                                    <input type="text" class="form-control" name="cp" value="{{ old('cp', $registro->cp ?? '') }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Colonia</label>
                                    <input type="text" class="form-control" name="colonia" value="{{ old('colonia', $registro->colonia ?? '') }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Calle y Número</label>
                                    <input type="text" class="form-control" name="calle_numero" value="{{ old('calle_numero', $registro->calle_numero ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- COLUMNA DERECHA --}}
                <div class="col-lg-5">
                    
                    {{-- 4. INFORMACIÓN ESTRATÉGICA --}}
                    <div class="card shadow-sm border-top-warning mb-4">
                        <div class="card-header bg-white"><h6 class="font-weight-bold text-warning mb-0"><i class="fas fa-chart-pie mr-2"></i>Información Comercial</h6></div>
                        <div class="card-body bg-light">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">¿Cómo se obtuvo el prospecto? <span class="text-danger">*</span></label>
                                <select name="origen_prospecto" class="form-select form-control" required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach(['Visita en campo', 'Llamada', 'WhatsApp', 'Correo', 'Página web', 'Redes sociales', 'Referencia de cliente', 'Expo', 'Referencia de otro agente', 'Referencia de otra empresa', 'Otro'] as $origen)
                                        <option value="{{ $origen }}" {{ old('origen_prospecto', $registro->origen_prospecto ?? '') == $origen ? 'selected' : '' }}>{{ $origen }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Fecha de Primer Contacto <span class="text-danger">*</span></label>
                                @php
                                    $fechaInicial = isset($registro) && $registro->fecha_primer_contacto ? $registro->fecha_primer_contacto->format('Y-m-d') : date('Y-m-d');
                                @endphp
                                <input type="date" class="form-control" name="fecha_primer_contacto" value="{{ old('fecha_primer_contacto', $fechaInicial) }}" required>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">¿Actualmente compra productos similares?</label>
                                <select name="compra_similares" class="form-select form-control">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Sí" {{ old('compra_similares', $registro->compra_similares ?? '') == 'Sí' ? 'selected' : '' }}>Sí</option>
                                    <option value="No" {{ old('compra_similares', $registro->compra_similares ?? '') == 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Desconoce" {{ old('compra_similares', $registro->compra_similares ?? '') == 'Desconoce' ? 'selected' : '' }}>Desconoce</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">¿Qué marcas maneja actualmente?</label>
                                <textarea name="marcas_actuales" class="form-control" rows="2">{{ old('marcas_actuales', $registro->marcas_actuales ?? '') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">¿Quién(es) son sus principales proveedores?</label>
                                <textarea name="proveedores_actuales" class="form-control" rows="2">{{ old('proveedores_actuales', $registro->proveedores_actuales ?? '') }}</textarea>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">Potencial estimado de compra mensual</label>
                                <select name="potencial_compra" class="form-select form-control">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach(['Menos de $10,000 mensuales', '$10,000 – $30,000', '$30,001 – $50,000', '$50,001 – $100,000', '$100,001 – $250,000', 'Más de $250,000', 'Por determinar'] as $potencial)
                                        <option value="{{ $potencial }}" {{ old('potencial_compra', $registro->potencial_compra ?? '') == $potencial ? 'selected' : '' }}>{{ $potencial }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Frecuencia estimada de compra</label>
                                <select name="frecuencia_compra" class="form-select form-control">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach(['Semanal', 'Quincenal', 'Mensual', 'Bimestral', 'Trimestral', 'Ocasional', 'Por determinar'] as $frecuencia)
                                        <option value="{{ $frecuencia }}" {{ old('frecuencia_compra', $registro->frecuencia_compra ?? '') == $frecuencia ? 'selected' : '' }}>{{ $frecuencia }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Condición de compra deseada</label>
                                <select name="condicion_compra" class="form-select form-control">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Contado" {{ old('condicion_compra', $registro->condicion_compra ?? '') == 'Contado' ? 'selected' : '' }}>Contado</option>
                                    <option value="Crédito" {{ old('condicion_compra', $registro->condicion_compra ?? '') == 'Crédito' ? 'selected' : '' }}>Crédito</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- 5. PRODUCTOS DE INTERÉS --}}
                    <div class="card shadow-sm border-top-success mb-4">
                        <div class="card-header bg-white"><h6 class="font-weight-bold text-success mb-0"><i class="fas fa-boxes mr-2"></i>Interés en Productos ARDA</h6></div>
                        <div class="card-body">
                            <label class="form-label font-weight-bold text-dark mb-3">Líneas de Interés (Seleccione todas las que apliquen):</label>
                            <div class="row">
                                @php
                                    $lineasOpciones = ['Mangueras', 'Abrazaderas', 'Cople y conexiones', 'Extensiones eléctricas', 'Cajas y tapas multiducto', 'Adhesivos / FIXY', 'Reguladores', 'Manguera para gas', 'Manguera para el hogar', 'Mangueras industriales', 'Mangueras de aire', 'Otro'];
                                    $lineasGuardadas = old('lineas_interes', isset($registro) && $registro->lineas_interes ? $registro->lineas_interes : []);
                                @endphp
                                @foreach($lineasOpciones as $index => $linea)
                                    <div class="col-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="lineas_interes[]" id="linea_{{ $index }}" value="{{ $linea }}" {{ in_array($linea, $lineasGuardadas) ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-normal" for="linea_{{ $index }}">{{ $linea }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 mb-3">
                                <label class="form-label font-weight-bold">Productos específicos de interés:</label>
                                <textarea name="productos_especificos" class="form-control" rows="2" placeholder="Ej. Extensión doméstica de 5m, Abrazadera sin fin...">{{ old('productos_especificos', $registro->productos_especificos ?? '') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Cantidad aproximada / presentación requerida:</label>
                                <textarea name="cantidad_presentacion" class="form-control" rows="2" placeholder="Ej. 50 pzas semanales, cajas master...">{{ old('cantidad_presentacion', $registro->cantidad_presentacion ?? '') }}</textarea>
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
    document.getElementById('navProspectos').classList.add('active'); 

    // Lógica para mostrar/ocultar el motivo de descarte
    const selectEstatus = document.getElementById('selector_estatus');
    const campoMotivo = document.getElementById('campo_motivo_descarte');

    function toggleMotivoDescarte() {
        if (selectEstatus.value === 'descartado') {
            campoMotivo.style.display = 'flex';
        } else {
            campoMotivo.style.display = 'none';
        }
    }

    // Ejecutar al cargar la página y cuando cambie el selector
    toggleMotivoDescarte();
    selectEstatus.addEventListener('change', toggleMotivoDescarte);
</script>
@endpush