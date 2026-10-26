@extends('plantilla.app')

@section('contenido')
<div class="app-content">
    <div class="container-fluid mt-4">
        
        {{-- ======================================================== --}}
        {{-- TARJETAS KPI COMO FILTROS RÁPIDOS INTERACTIVOS (5 COLUMNAS) --}}
        {{-- ======================================================== --}}
        <div class="row mb-4">
            {{-- 1. TODOS --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('prospectos.index', ['estatus' => '', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ empty($estatusFiltro) ? 'bg-primary text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #4e73df !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ empty($estatusFiltro) ? 'text-white' : 'text-primary' }}" style="font-size: 0.70rem;">Todos</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['total'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-users fa-2x {{ empty($estatusFiltro) ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 2. NUEVOS --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('prospectos.index', ['estatus' => 'nuevo', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estatusFiltro == 'nuevo' ? 'bg-info text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #0dcaf0 !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estatusFiltro == 'nuevo' ? 'text-white' : 'text-info' }}" style="font-size: 0.70rem;">Nuevos</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['nuevos'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-star fa-2x {{ $estatusFiltro == 'nuevo' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 3. EN SEGUIMIENTO --}}
            <div class="col-xl col-md-4 col-sm-6 mb-2">
                <a href="{{ route('prospectos.index', ['estatus' => 'en_seguimiento', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estatusFiltro == 'en_seguimiento' ? 'bg-warning text-dark shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #f6c23e !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estatusFiltro == 'en_seguimiento' ? 'text-dark' : 'text-warning' }}" style="font-size: 0.70rem;">En Seguimiento</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['en_seguimiento'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-sync-alt fa-2x {{ $estatusFiltro == 'en_seguimiento' ? 'text-dark' : 'text-gray-300' }}" style="{{ $estatusFiltro == 'en_seguimiento' ? 'opacity: 0.5;' : '' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 4. VENTAS (POR CONVERTIR) --}}
            <div class="col-xl col-md-6 col-sm-6 mb-2">
                <a href="{{ route('prospectos.index', ['estatus' => 'convertido', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estatusFiltro == 'convertido' ? 'bg-success text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #1cc88a !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estatusFiltro == 'convertido' ? 'text-white' : 'text-success' }}" style="font-size: 0.70rem;">Ventas (Por Convertir)</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['convertidos'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-check-circle fa-2x {{ $estatusFiltro == 'convertido' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            {{-- 5. DESCARTADOS --}}
            <div class="col-xl col-md-6 col-sm-6 mb-2">
                <a href="{{ route('prospectos.index', ['estatus' => 'descartado', 'agente_id' => $agenteFiltro]) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 transition-hover {{ $estatusFiltro == 'descartado' ? 'bg-danger text-white shadow' : 'bg-white' }}" style="border-radius: 12px; border-top: 4px solid #e74a3b !important;">
                        <div class="card-body py-3">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $estatusFiltro == 'descartado' ? 'text-white' : 'text-danger' }}" style="font-size: 0.70rem;">Descartados</div>
                                    <div class="h4 mb-0 font-weight-bold">{{ $kpis['descartados'] }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-times-circle fa-2x {{ $estatusFiltro == 'descartado' ? 'text-white-50' : 'text-gray-300' }}"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
            <div class="card-header py-3 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                <h3 class="card-title font-weight-bold text-primary mb-3 mb-md-0">
                    <i class="fas fa-list mr-2"></i> Directorio de Prospectos
                </h3>
                
                {{-- BOTONES CON SEPARACIÓN (gap-2) --}}
                <div class="d-flex gap-2">
                    @can('prospecto-delete')
                    <a href="{{ route('prospectos.estadisticas') }}" class="btn btn-info text-white font-weight-bold shadow-sm" title="Ver Analíticas Comerciales">
                        <i class="fas fa-chart-pie mr-1"></i> Dashboard Analítico
                    </a>
                    @endcan
                    <a href="{{ route('prospectos.exportar') }}" class="btn btn-success font-weight-bold shadow-sm" title="Descargar reporte a Excel">
                        <i class="fas fa-file-excel mr-1"></i> Excel
                    </a>
                    <a href="{{ route('prospectos.create') }}" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-plus"></i> Nuevo
                    </a>
                </div>
            </div>
            
            <div class="card-body bg-light border-bottom">
                {{-- BARRA DE BÚSQUEDA Y FILTROS AVANZADOS --}}
                <form action="{{ route('prospectos.index') }}" method="get">
                    <div class="row g-2">
                        {{-- Filtro Estatus Oculto --}}
                        <input type="hidden" name="estatus" value="{{ $estatusFiltro }}">

                        @hasanyrole('admin|super-admin|superadmin')
                        <div class="col-md-3">
                            <select name="agente_id" class="form-select shadow-sm font-weight-bold text-primary" onchange="this.form.submit()">
                                <option value="">👤 Todos los Agentes</option>
                                @foreach($agentes as $ag)
                                    <option value="{{ $ag->id }}" {{ $agenteFiltro == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endhasanyrole

                        <div class="col-md-{{ auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']) ? '7' : '10' }}">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
                                <input name="texto" type="text" class="form-control border-start-0" value="{{ $texto }}" placeholder="Buscar por Razón Social, Nombre Comercial, RFC...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm font-weight-bold"><i class="fas fa-search mr-1"></i> Buscar</button>
                        </div>
                    </div>
                </form>
            </div>

            @if(Session::has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm m-3">
                <i class="fas fa-check-circle mr-1"></i> {{ Session::get('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-white text-gray-700">
                        <tr>
                            <th class="border-bottom-0 pl-3">ID</th>
                            @can('prospecto-delete')
                            <th class="border-bottom-0">Agente</th>
                            @endcan
                            <th class="border-bottom-0">Razón Social / Comercial</th>
                            <th class="border-bottom-0">Contacto Principal</th>
                            <th class="border-bottom-0">Origen</th>
                            <th class="border-bottom-0 text-center">Estatus</th>
                            <th class="border-bottom-0 text-center">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $reg)
                        <tr>
                            <td class="pl-3 font-weight-bold text-secondary">#{{ $reg->id }}</td>
                            @can('prospecto-delete')
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mr-2" style="width: 30px; height: 30px;"><i class="fas fa-user text-primary"></i></div>
                                    <span class="font-weight-bold text-dark">{{ $reg->agente->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            @endcan

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
                                    $colorOrigen = 'badge-light border text-secondary';
                                    if($origenLimpio == 'expo') $colorOrigen = 'bg-primary text-white';
                                    elseif(str_contains($origenLimpio, 'referencia')) $colorOrigen = 'bg-info text-dark';
                                    elseif(in_array($origenLimpio, ['llamada', 'whatsapp', 'correo'])) $colorOrigen = 'bg-success text-white';
                                    elseif($origenLimpio == 'visita en campo') $colorOrigen = 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $colorOrigen }} px-2 py-1 shadow-sm">{{ $reg->origen_prospecto }}</span>
                            </td>
                            <td class="text-center">
                                @php $estatusLimpio = strtolower(trim($reg->estatus)); @endphp

                                @if(in_array($estatusLimpio, ['nuevo', 'nuevo prospecto'])) 
                                    <span class="badge shadow-sm px-3 py-1" style="background-color: #0dcaf0; color: #000;">Nuevo Prospecto</span>
                                @elseif(in_array($estatusLimpio, ['en_seguimiento', 'en seguimiento activo'])) 
                                    <span class="badge shadow-sm px-3 py-1" style="background-color: #ffc107; color: #000;">En Seguimiento</span>
                                @elseif(in_array($estatusLimpio, ['convertido']) || str_contains($estatusLimpio, 'venta realizada')) 
                                    <span class="badge shadow-sm px-3 py-1" style="background-color: #198754; color: #fff;"><i class="fas fa-check-circle mr-1"></i>Venta Realizada</span>
                                @elseif(str_contains($estatusLimpio, 'descartado')) 
                                    <span class="badge shadow-sm px-3 py-1" style="background-color: #e74a3b; color: #fff;">Descartado</span>
                                    @if(!empty($reg->motivo_descarte))
                                        <div class="mt-2 shadow-sm text-start" style="max-width: 200px; margin: 0 auto; background-color: #fff0f0; border-left: 3px solid #dc3545; padding: 6px 8px; border-radius: 4px;">
                                            <div class="text-danger font-weight-bold" style="font-size: 0.70rem; margin-bottom: 2px;"><i class="fas fa-exclamation-circle mr-1"></i>Motivo:</div>
                                            <div class="text-dark" style="font-size: 0.75rem; white-space: normal;">{{ $reg->motivo_descarte }}</div>
                                        </div>
                                    @endif
                                @else 
                                    <span class="badge shadow-sm px-3 py-1 bg-dark">{{ ucfirst($reg->estatus ?? 'Sin Estatus') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                
                                {{-- BOTÓN MÁGICO PARA CREAR CLIENTE --}}
                                @if($estatusLimpio == 'convertido' || str_contains($estatusLimpio, 'venta realizada'))
                                    @can('cliente-create')
                                    <button type="button" class="btn btn-sm btn-success shadow-sm font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalConvertir-{{ $reg->id }}" title="Crear Cliente Oficial">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                    @endcan
                                @endif

                                <button type="button" class="btn btn-sm btn-warning shadow-sm text-dark font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalEstatus-{{ $reg->id }}" title="Cambiar Estatus">
                                    <i class="fas fa-sync-alt"></i>
                                </button>

                                <a href="{{ route('prospectos.edit', $reg->id) }}" class="btn btn-sm btn-info shadow-sm text-white" title="Editar Detalles">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @can('prospecto-delete')
                                <form action="{{ route('prospectos.destroy', $reg->id) }}" method="POST" class="d-inline form-confirmar"
                                    data-title="¿Eliminar Prospecto?" data-text="Se eliminará a '{{ $reg->razon_social }}'." data-icon="warning" data-color="#dc3545" data-btn-text="Sí, eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>

                        {{-- MODAL CAMBIO DE ESTATUS --}}
                        <div class="modal fade" id="modalEstatus-{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                                    <form action="{{ route('prospectos.cambiar_estado', $reg->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-light" style="border-radius: 12px 12px 0 0;">
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
                                            <div class="mt-3" id="motivo-{{ $reg->id }}" style="display: {{ $reg->estatus == 'descartado' ? 'block' : 'none' }};">
                                                <label class="form-label text-danger font-weight-bold small mb-1">Motivo / Observaciones del Rechazo:</label>
                                                <textarea name="motivo_descarte" class="form-control form-control-sm" rows="2" placeholder="Escribe por qué no es viable...">{{ $reg->motivo_descarte }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 bg-light" style="border-radius: 0 0 12px 12px;">
                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold"><i class="fas fa-save mr-1"></i> Guardar Estatus</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL CONVERTIR A CLIENTE (Por Permiso) --}}
                        @can('cliente-create')
                        <div class="modal fade" id="modalConvertir-{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                                    <form action="{{ route('prospectos.convertir_cliente', $reg->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header text-white" style="background-color: #1cc88a; border-radius: 12px 12px 0 0;">
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
                                        <div class="modal-footer border-top-0 bg-white" style="border-radius: 0 0 12px 12px;">
                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success font-weight-bold rounded-pill px-4"><i class="fas fa-check-circle mr-1"></i> Guardar y Convertir</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endcan

                        @empty
                        <tr><td colspan="{{ auth()->user()->can('prospecto-delete') ? '7' : '6' }}" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 text-gray-300 d-block"></i> No tienes prospectos en este rubro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0" style="border-radius: 0 0 12px 12px;">
                {{ $registros->appends(["texto" => $texto, "estatus" => $estatusFiltro, "agente_id" => $agenteFiltro])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('estilos')
<style>
    .transition-hover { transition: all 0.2s ease-in-out; cursor: pointer; }
    .transition-hover:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,.1)!important; }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('navProspectos').classList.add('active'); 

    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.select-estatus-modal');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target');
                const cajaMotivo = document.getElementById(targetId);
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