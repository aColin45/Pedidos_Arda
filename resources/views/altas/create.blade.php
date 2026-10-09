@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid">
        {{-- Encabezado --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Alta de Nuevo Cliente</h1>
                <p class="text-muted small mb-0">Paso 1: Captura de datos generales {{ $tipo == 'credito' ? 'y referencias comerciales' : '' }}</p>
            </div>
            <a href="{{ route('altas.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Regresar
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger shadow-sm border-0" style="border-left: 4px solid #dc3545 !important;">
                <h6 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Por favor, corrija los siguientes errores:</h6>
                <ul class="mb-0 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('altas.store') }}" method="POST">
            @csrf
            
            {{-- NUEVO: CAMPO OCULTO PARA EL TIPO DE ALTA Y ALERTA VISUAL --}}
            <input type="hidden" name="tipo_alta" value="{{ $tipo }}">

            @if($tipo == 'contado')
                <div class="alert alert-success shadow-sm border-0 mb-4 py-3" style="border-left: 4px solid #198754 !important;">
                    <h6 class="font-weight-bold mb-1"><i class="fas fa-bolt mr-2 text-success"></i> Modo Rápido: Alta de Contado</h6>
                    <span class="small">En esta modalidad, únicamente el <strong>Nombre/Razón Social</strong> y el <strong>Correo Electrónico</strong> son obligatorios para continuar.</span>
                </div>
            @endif
            
            {{-- 1. DATOS GENERALES Y DE FACTURACIÓN --}}
            <div class="card shadow mb-4 border-0" style="border-radius: 10px;">
                <div class="card-header py-3 bg-white border-bottom-primary" style="border-radius: 10px 10px 0 0;">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-building mr-2"></i>Datos Generales y de Facturación</h6>
                </div>
                <div class="card-body bg-light">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label font-weight-bold small text-muted">Nombre o Razón Social *</label>
                            <input type="text" name="razon_social" class="form-control force-uppercase" value="{{ old('razon_social') }}" required>
                        </div>
                        
                        {{-- RFC CONDICIONADO --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold small text-muted">R.F.C. @if($tipo == 'credito') * @endif</label>
                            <input type="text" name="rfc" class="form-control force-uppercase" value="{{ old('rfc') }}" @if($tipo == 'credito') required @endif maxlength="20">
                        </div>
                        
                        {{-- RÉGIMEN FISCAL CON DATALIST --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold small text-muted">Régimen Fiscal</label>
                            <input type="text" list="regimenes" name="regimen_fiscal" class="form-control force-uppercase" value="{{ old('regimen_fiscal') }}" placeholder="Escriba o seleccione...">
                            <datalist id="regimenes">
                                <option value="601 - GENERAL DE LEY PERSONAS MORALES"></option>
                                <option value="603 - PERSONAS MORALES CON FINES NO LUCRATIVOS"></option>
                                <option value="606 - ARRENDAMIENTO"></option>
                                <option value="612 - PERSONAS FÍSICAS CON ACTIVIDADES EMPRESARIALES Y PROFESIONALES"></option>
                                <option value="616 - SIN OBLIGACIONES FISCALES"></option>
                                <option value="625 - RÉGIMEN DE LAS ACTIVIDADES EMPRESARIALES CON INGRESOS A TRAVÉS DE PLATAFORMAS TECNOLÓGICAS"></option>
                                <option value="626 - RÉGIMEN SIMPLIFICADO DE CONFIANZA (RESICO)"></option>
                            </datalist>
                        </div>

                        {{-- USO CFDI CON DATALIST --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold small text-muted">Uso CFDI</label>
                            <input type="text" list="usos_cfdi" name="uso_cfdi" class="form-control force-uppercase" value="{{ old('uso_cfdi') }}" placeholder="Escriba o seleccione...">
                            <datalist id="usos_cfdi">
                                <option value="G01 - ADQUISICIÓN DE MERCANCÍAS"></option>
                                <option value="G02 - DEVOLUCIONES, DESCUENTOS O BONIFICACIONES"></option>
                                <option value="G03 - GASTOS EN GENERAL"></option>
                                <option value="I04 - EQUIPO DE COMPUTO Y ACCESORIOS"></option>
                                <option value="I08 - OTRA MAQUINARIA Y EQUIPO"></option>
                                <option value="P01 - POR DEFINIR"></option>
                            </datalist>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. CUENTAS POR PAGAR --}}
            <div class="card shadow mb-4 border-0" style="border-radius: 10px;">
                <div class="card-header py-3 bg-white border-bottom-success">
                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-hand-holding-usd mr-2"></i>Contacto Cuentas por Pagar</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold small text-muted">Nombre Contacto</label>
                            <input type="text" name="contacto_pagos" class="form-control force-uppercase" value="{{ old('contacto_pagos') }}">
                        </div>
                        
                        {{-- CORREO AHORA ES OBLIGATORIO PARA AMBOS CASOS --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold small text-muted">Correo Electrónico *</label>
                            <input type="email" name="email_pagos" class="form-control" value="{{ old('email_pagos') }}" placeholder="ejemplo@correo.com" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold small text-muted">Teléfono y Extensión</label>
                            <input type="text" name="telefono_pagos" class="form-control" value="{{ old('telefono_pagos') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. DOMICILIOS --}}
            <div class="row">
                {{-- Domicilio Fiscal --}}
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100 border-0" style="border-radius: 10px;">
                        <div class="card-header py-3 bg-white border-bottom-info">
                            <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-map-marked-alt mr-2"></i>Domicilio Fiscal</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold small text-muted">Calle y Número</label>
                                <input type="text" name="calle_fiscal" class="form-control force-uppercase" value="{{ old('calle_fiscal') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold small text-muted">Colonia</label>
                                <input type="text" name="colonia_fiscal" class="form-control force-uppercase" value="{{ old('colonia_fiscal') }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Municipio / Delegación</label>
                                    <input type="text" name="municipio_fiscal" class="form-control force-uppercase" value="{{ old('municipio_fiscal') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Estado</label>
                                    <input type="text" name="estado_fiscal" class="form-control force-uppercase" value="{{ old('estado_fiscal') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">C.P.</label>
                                    <input type="text" name="cp_fiscal" class="form-control" value="{{ old('cp_fiscal') }}">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Teléfono Fiscal</label>
                                    <input type="text" name="telefono_fiscal" class="form-control" value="{{ old('telefono_fiscal') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Correo Electrónico (1)</label>
                                    <input type="email" name="email1_fiscal" class="form-control" value="{{ old('email1_fiscal') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Correo Electrónico (2)</label>
                                    <input type="email" name="email2_fiscal" class="form-control" value="{{ old('email2_fiscal') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Domicilio Entrega --}}
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100 border-0" style="border-radius: 10px;">
                        <div class="card-header py-3 bg-white border-bottom-warning">
                            <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-truck-loading mr-2"></i>Domicilio de Entrega (Si es diferente)</h6>
                        </div>
                        <div class="card-body bg-light">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold small text-muted">Persona autorizada para recibir mercancía</label>
                                <input type="text" name="persona_recibe" class="form-control force-uppercase" value="{{ old('persona_recibe') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold small text-muted">Calle y Número</label>
                                <input type="text" name="calle_entrega" class="form-control force-uppercase" value="{{ old('calle_entrega') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold small text-muted">Colonia</label>
                                <input type="text" name="colonia_entrega" class="form-control force-uppercase" value="{{ old('colonia_entrega') }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Municipio / Delegación</label>
                                    <input type="text" name="municipio_entrega" class="form-control force-uppercase" value="{{ old('municipio_entrega') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">Estado</label>
                                    <input type="text" name="estado_entrega" class="form-control force-uppercase" value="{{ old('estado_entrega') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold small text-muted">C.P.</label>
                                    <input type="text" name="cp_entrega" class="form-control" value="{{ old('cp_entrega') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. REFERENCIAS COMERCIALES (SOLO PARA CRÉDITO) --}}
            @if($tipo == 'credito')
            <div class="card shadow mb-4 border-0" style="border-radius: 10px; border-left: 4px solid #858796;">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-users mr-2"></i>Referencias Comerciales (Obligatorias)</h6>
                    <span class="badge bg-danger text-white">Requerido 5 de 5</span>
                </div>
                <div class="card-body">
                    <p class="small text-danger mb-4"><i class="fas fa-info-circle mr-1"></i> Recuerde: Deben ser correos corporativos y números de teléfono fijos de la empresa (No celulares, no agentes de ventas).</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="bg-light text-muted small text-center">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">Nombre o Razón Social *</th>
                                    <th width="30%">Correo de Contacto *</th>
                                    <th width="30%">Teléfono(s) *</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 5; $i++)
                                <tr>
                                    <td class="text-center font-weight-bold bg-light">{{ $i + 1 }}</td>
                                    <td>
                                        <input type="text" name="referencias[{{$i}}][nombre]" class="form-control form-control-sm force-uppercase" value="{{ old('referencias.'.$i.'.nombre') }}" placeholder="Empresa..." required>
                                    </td>
                                    <td>
                                        <input type="email" name="referencias[{{$i}}][correo]" class="form-control form-control-sm" value="{{ old('referencias.'.$i.'.correo') }}" placeholder="ejemplo@empresa.com" required>
                                    </td>
                                    <td>
                                        <input type="text" name="referencias[{{$i}}][telefono]" class="form-control form-control-sm" value="{{ old('referencias.'.$i.'.telefono') }}" placeholder="Tel. Fijo" required>
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- BOTÓN GUARDAR --}}
            <div class="card shadow mb-5 border-0">
                <div class="card-body text-end bg-light" style="border-radius: 10px;">
                    <p class="small text-muted mb-2 text-start"><i class="fas fa-exclamation-circle mr-1"></i> Al guardar, se generará el borrador del formato y podrá descargarlo para firma, así como proceder a la subida de los documentos adjuntos requeridos.</p>
                    <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm font-weight-bold">
                        <i class="fas fa-save mr-2"></i> Guardar Alta y Continuar
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
    /* CSS para que el usuario vea la mayúscula instantáneamente al tipear */
    .force-uppercase {
        text-transform: uppercase;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Seleccionamos todos los inputs con la clase force-uppercase
        const uppercaseInputs = document.querySelectorAll('.force-uppercase');
        
        uppercaseInputs.forEach(function(input) {
            // Evento para transformar el valor real antes de enviarlo al servidor
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        });
    });
</script>
@endpush