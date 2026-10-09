@extends('plantilla.app')

@section('contenido')
<div class="app-content">
    <div class="container-fluid mt-4">
        <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
            <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                <h3 class="card-title font-weight-bold text-primary mb-0">
                    <i class="fas fa-users-cog mr-2"></i> Gestión de Prospectos
                </h3>
            </div>
            <div class="card-body">
                
                {{-- BARRA DE BÚSQUEDA Y BOTONES --}}
                <div>
                    <form action="{{ route('prospectos.index') }}" method="get">
                        <div class="input-group mb-3 shadow-sm">
                            <input name="texto" type="text" class="form-control border-right-0" value="{{ $texto }}"
                                placeholder="Buscar por Razón Social, Nombre Comercial, RFC o Contacto...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                                
                                {{-- BOTÓN EXCEL GENERAL --}}
                                <a href="{{ route('prospectos.exportar') }}" class="btn btn-success ml-2 font-weight-bold" title="Descargar reporte a Excel">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>

                                <a href="{{ route('prospectos.create') }}" class="btn btn-primary ml-2 font-weight-bold">
                                    <i class="fas fa-plus"></i> Nuevo
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                @if(Session::has('mensaje'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm">
                    <i class="fas fa-check-circle mr-1"></i> {{ Session::get('mensaje') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light text-gray-700">
                            <tr>
                                <th class="border-0 pl-3">ID</th> <!-- ESTA LÍNEA ES NUEVA -->
                                @role('admin|superadmin')
                                <th class="border-0">Agente</th>
                                @endrole
                                <th class="border-0">Razón Social / Comercial</th>
                                <th class="border-0">Contacto Principal</th>
                                <th class="border-0">Origen</th>
                                <th class="border-0 text-center">Estatus</th>
                                <th class="border-0 text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $reg)
                            <tr>
                                <td class="pl-3 font-weight-bold text-secondary">#{{ $reg->id }}</td> <!-- ESTA LÍNEA ES NUEVA -->
                                @role('admin|superadmin')
                                <td><div class="text-dark font-weight-bold"><i class="fas fa-user-circle text-gray-400 mr-1"></i> {{ $reg->agente->name ?? 'N/A' }}</div></td>
                                @endrole

                                <td>
                                    <div class="font-weight-bold text-dark">{{ $reg->razon_social }}</div>
                                    @if($reg->nombre_comercial)
                                        <small class="text-muted"><i class="fas fa-store text-gray-400 mr-1"></i>{{ $reg->nombre_comercial }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark">{{ $reg->contacto_nombre }}</div>
                                    <small class="text-muted">{{ $reg->contacto_puesto }} - <i class="fas fa-phone mr-1"></i>{{ $reg->telefono ?? $reg->celular ?? 'Sin Tel.' }}</small>
                                </td>
                                <td>
                                    @php 
                                        $origenLimpio = strtolower(trim($reg->origen_prospecto)); 
                                        $colorOrigen = 'badge-light border text-secondary'; // Por defecto
                                        
                                        if($origenLimpio == 'expo') {
                                            $colorOrigen = 'bg-primary text-white';
                                        } elseif(str_contains($origenLimpio, 'referencia')) {
                                            $colorOrigen = 'bg-info text-dark';
                                        } elseif($origenLimpio == 'llamada' || $origenLimpio == 'whatsapp' || $origenLimpio == 'correo') {
                                            $colorOrigen = 'bg-success text-white';
                                        } elseif($origenLimpio == 'visita en campo') {
                                            $colorOrigen = 'bg-warning text-dark';
                                        }
                                    @endphp
                                    <span class="badge {{ $colorOrigen }} px-2 py-1 shadow-sm">{{ $reg->origen_prospecto }}</span>
                                </td>
                                <td class="text-center">
                                    @php $estatusLimpio = strtolower(trim($reg->estatus)); @endphp

                                    {{-- COLORES A PRUEBA DE FALLOS CSS --}}
                                    @if($estatusLimpio == 'nuevo' || $estatusLimpio == 'nuevo prospecto') 
                                        <span class="badge shadow-sm px-3 py-1" style="background-color: #0dcaf0; color: #000;">Nuevo Prospecto</span>
                                    @elseif($estatusLimpio == 'en_seguimiento' || $estatusLimpio == 'en seguimiento activo') 
                                        <span class="badge shadow-sm px-3 py-1" style="background-color: #ffc107; color: #000;">En Seguimiento</span>
                                    @elseif($estatusLimpio == 'convertido' || str_contains($estatusLimpio, 'venta realizada')) 
                                        <span class="badge shadow-sm px-3 py-1" style="background-color: #198754; color: #fff;">Venta Realizada (Convertir a Cliente)</span>
                                    @elseif($estatusLimpio == 'descartado' || str_contains($estatusLimpio, 'descartado')) 
                                        <span class="badge shadow-sm px-3 py-1" style="background-color: #6c757d; color: #fff;">Descartado</span>
                                        
                                        {{-- NUEVO: MOSTRAR EL MOTIVO DE DESCARTE DEBAJO DE LA ETIQUETA CON ESTILO CORREGIDO --}}
                                        @if(!empty($reg->motivo_descarte))
                                            <div class="mt-2 shadow-sm" style="max-width: 200px; margin: 0 auto; background-color: #fff0f0; border-left: 3px solid #dc3545; padding: 6px 8px; border-radius: 4px; text-align: left !important;">
                                                <div class="text-danger font-weight-bold" style="font-size: 0.70rem; margin-bottom: 2px;">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>Motivo:
                                                </div>
                                                <div class="text-dark" style="font-size: 0.75rem; white-space: normal; word-wrap: break-word;">
                                                    {{ $reg->motivo_descarte }}
                                                </div>
                                            </div>
                                        @endif

                                    @else 
                                        <span class="badge shadow-sm px-3 py-1" style="background-color: #212529; color: #fff;">{{ ucfirst($reg->estatus ?? 'Sin Estatus') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    
                                    {{-- BOTÓN MÁGICO PARA CREAR CLIENTE (Solo Admin) --}}
                                    @if($estatusLimpio == 'convertido' || str_contains($estatusLimpio, 'venta realizada'))
                                        @role('admin|superadmin')
                                        <button type="button" class="btn btn-sm btn-success shadow-sm font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalConvertir-{{ $reg->id }}" title="Crear Cliente Oficial">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                        @endrole
                                    @endif

                                    {{-- Botón Modal de Estatus --}}
                                    <button type="button" class="btn btn-sm btn-warning shadow-sm text-dark font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalEstatus-{{ $reg->id }}" title="Cambiar Estatus">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>

                                    <a href="{{ route('prospectos.edit', $reg->id) }}" class="btn btn-sm btn-info shadow-sm" title="Editar Detalles">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @role('admin|superadmin')
                                    <form action="{{ route('prospectos.destroy', $reg->id) }}" method="POST" class="d-inline form-confirmar"
                                          data-title="¿Eliminar Prospecto?" data-text="Se eliminará a '{{ $reg->razon_social }}'." data-icon="warning" data-color="#dc3545" data-btn-text="Sí, eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                    @endrole
                                </td>
                            </tr>

                            {{-- MODAL CAMBIO DE ESTATUS --}}
                            <div class="modal fade" id="modalEstatus-{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('prospectos.cambiar_estado', $reg->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title font-weight-bold"><i class="fas fa-sync-alt text-warning mr-2"></i>Actualizar Estatus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p>Selecciona la nueva etapa para <strong>{{ $reg->razon_social }}</strong>:</p>
                                                
                                                <select name="estatus" class="form-select form-control font-weight-bold select-estatus-modal" data-target="motivo-{{ $reg->id }}">
                                                    <option value="nuevo" {{ $reg->estatus == 'nuevo' ? 'selected' : '' }}>🔵 Nuevo Prospecto</option>
                                                    <option value="en_seguimiento" {{ $reg->estatus == 'en_seguimiento' ? 'selected' : '' }}>🟡 En Seguimiento Activo</option>
                                                    <option value="convertido" {{ str_contains(strtolower($reg->estatus), 'convertido') || str_contains(strtolower($reg->estatus), 'realizada') ? 'selected' : '' }}>🟢 Venta Realizada (Convertir a Cliente)</option>
                                                    <option value="descartado" {{ $reg->estatus == 'descartado' ? 'selected' : '' }}>⚫ Descartado / No viable</option>
                                                </select>

                                                {{-- CAMPO DE MOTIVO OCULTO POR DEFECTO --}}
                                                <div class="mt-3" id="motivo-{{ $reg->id }}" style="display: {{ $reg->estatus == 'descartado' ? 'block' : 'none' }};">
                                                    <label class="form-label text-danger font-weight-bold small mb-1">Motivo / Observaciones del Rechazo:</label>
                                                    <textarea name="motivo_descarte" class="form-control form-control-sm" rows="2" placeholder="Escribe por qué no es viable...">{{ $reg->motivo_descarte }}</textarea>
                                                </div>

                                            </div>
                                            <div class="modal-footer border-top-0">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Estatus</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL CONVERTIR A CLIENTE (Solo Admin) --}}
                            @role('admin|superadmin')
                            <div class="modal fade" id="modalConvertir-{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <form action="{{ route('prospectos.convertir_cliente', $reg->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header text-white" style="background-color: #198754;">
                                                <h5 class="modal-title font-weight-bold"><i class="fas fa-user-check mr-2"></i>Crear Cliente Oficial</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start bg-light">
                                                <p class="small text-muted mb-3">Toda la información general del prospecto <strong>{{ $reg->razon_social }}</strong> se copiará automáticamente. Completa los datos comerciales para finalizar el alta.</p>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label font-weight-bold">Código de Cliente <span class="text-danger">*</span></label>
                                                        <input type="text" name="codigo" class="form-control text-uppercase" placeholder="Ej. C00461" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label font-weight-bold">Descuento (%) <span class="text-danger">*</span></label>
                                                        <select name="descuento" class="form-select form-control" required>
                                                            <option value="0.00">0% (Sin Descuento)</option>
                                                            <option value="32.00">32%</option>
                                                            <option value="34.00">34%</option>
                                                            <option value="36.00">36%</option>
                                                            <option value="40.00">40%</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label font-weight-bold">Lista de Precios</label>
                                                        <input type="text" name="contacto" class="form-control" placeholder="Especificar lista de precios...">
                                                    </div>
                                                </div>

                                                <h6 class="text-success font-weight-bold mt-2 border-bottom pb-2"><i class="fas fa-money-check-alt mr-1"></i> Condiciones de Crédito y Pagos</h6>
                                                <div class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <label class="form-label text-muted small mb-1">Monto Crédito ($)</label>
                                                        <input type="number" step="0.01" name="monto_credito" class="form-control" value="0.00">
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="form-label text-muted small mb-1">Días de Crédito</label>
                                                        <input type="number" name="dias_credito" class="form-control" value="0">
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="form-label text-muted small mb-1">Otorgado el</label>
                                                        <input type="date" name="fecha_otorgamiento" class="form-control">
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="form-label text-muted small mb-1">Referencia Bancaria</label>
                                                        <input type="text" name="referencia_bancaria" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Guardar y Convertir</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endrole

                            @empty
                            <tr><td colspan="{{ auth()->user()->hasRole('admin') ? '6' : '5' }}" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-3 text-gray-300 d-block"></i> No tienes prospectos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0" style="border-radius: 0 0 12px 12px;">
                {{ $registros->appends(["texto" => $texto])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('navProspectos').classList.add('active'); 

    // Lógica para mostrar el motivo de descarte en TODOS los modales de la tabla
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.select-estatus-modal');
        
        selects.forEach(select => {
            select.addEventListener('change', function() {
                // Obtener el ID de la caja de texto correspondiente
                const targetId = this.getAttribute('data-target');
                const cajaMotivo = document.getElementById(targetId);
                
                // Mostrar u ocultar dependiendo del valor
                if(this.value === 'descartado') {
                    cajaMotivo.style.display = 'block';
                } else {
                    cajaMotivo.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush