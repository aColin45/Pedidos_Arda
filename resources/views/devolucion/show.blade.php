@extends('plantilla.app')

@section('contenido')
<div class="container-fluid">
    
    {{-- BARRA SUPERIOR: TÍTULO Y ACCIONES --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <span class="text-primary font-weight-bold">#{{ $devolucion->id }}</span> Detalle de Devolución
        </h1>
        <div>
            <a href="{{ route('devoluciones.index') }}" class="btn btn-secondary shadow-sm mr-2">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Regresar
            </a>
            
            {{-- BOTÓN IMPRIMIR PDF --}}
            <a href="{{ route('devoluciones.pdf', $devolucion->id) }}" target="_blank" class="btn btn-danger shadow-sm">
                <i class="fas fa-file-pdf fa-sm text-white-50"></i> Imprimir Formato
            </a>
        </div>
    </div>

    <div class="row">
        {{-- ======================================================= --}}
        {{-- COLUMNA IZQUIERDA: INFORMACIÓN, PRODUCTOS Y EVIDENCIA --}}
        {{-- ======================================================= --}}
        <div class="col-lg-8">
            
            {{-- Tarjeta de Información General --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white border-bottom-primary">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-1"></i> Datos Generales</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-uppercase text-gray-500 font-weight-bold">Cliente</small>
                            <div class="h5 font-weight-bold text-gray-800">{{ $devolucion->cliente->nombre ?? 'N/A' }}</div>
                            <div class="small text-primary">{{ $devolucion->cliente->codigo ?? '' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-uppercase text-gray-500 font-weight-bold">Agente Responsable</small>
                            <div class="h6 text-gray-800">{{ $devolucion->agente->name ?? 'N/A' }}</div>
                            <div class="small text-gray-500">{{ $devolucion->agente->email ?? '' }}</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-uppercase text-gray-500 font-weight-bold">Factura a la que pertenece</small>
                            <div class="h6 font-weight-bold">{{ $devolucion->factura_extraida }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-uppercase text-gray-500 font-weight-bold">Fecha Registro</small>
                            <div class="h6">{{ $devolucion->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-uppercase text-gray-500 font-weight-bold">Motivo</small>
                            <div class="h6 text-danger">{{ $devolucion->motivo_extraido }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta de Productos --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-boxes mr-1"></i> Items Devueltos</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="10%">Código</th>
                                <th width="32%">Descripción</th>
                                <th width="13%" class="text-right">Precio Lista</th>
                                <th width="10%" class="text-center">Desc.</th>
                                <th width="13%" class="text-right">Precio Neto</th>
                                <th width="8%" class="text-center">Cant.</th>
                                <th width="14%" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devolucion->detalles as $detalle)
                            @php
                                // Calculamos el descuento que se aplicó comparando el precio original vs el cobrado
                                $precioLista = $detalle->producto->precio ?? 0;
                                $precioNeto = $detalle->precio;
                                $porcentajeAplicado = $precioLista > 0 ? round((1 - ($precioNeto / $precioLista)) * 100) : 0;
                            @endphp
                            <tr>
                                <td class="font-weight-bold text-secondary">{{ $detalle->producto->codigo ?? 'N/A' }}</td>
                                <td>{{ $detalle->producto->nombre ?? 'Producto Eliminado' }}</td>
                                
                                {{-- Precio Original (Se tacha si hay descuento) --}}
                                <td class="text-right text-muted">
                                    @if($porcentajeAplicado > 0)
                                        <del>${{ number_format($precioLista, 2) }}</del>
                                    @else
                                        ${{ number_format($precioLista, 2) }}
                                    @endif
                                </td>
                                
                                {{-- Etiqueta Visual de Descuento --}}
                                <td class="text-center align-middle">
                                    @if($porcentajeAplicado > 0)
                                        <span class="text-danger font-weight-bold">-{{ $porcentajeAplicado }}%</span>
                                    @else
                                        <span class="text-muted small">0%</span>
                                    @endif
                                </td>
                                
                                {{-- Precio Real Cobrado --}}
                                <td class="text-right font-weight-bold text-primary">${{ number_format($precioNeto, 2) }}</td>
                                
                                <td class="text-center font-weight-bold bg-light">{{ $detalle->cantidad }}</td>
                                <td class="text-right font-weight-bold text-dark">${{ number_format($detalle->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100">
                                {{-- Colspan ajustado a 6 porque agregamos más columnas --}}
                                <td colspan="6" class="text-right font-weight-bold text-uppercase pt-3">Total Estimado Devolución:</td>
                                <td class="text-right font-weight-bold text-primary h5 m-0 pt-3">${{ number_format($devolucion->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Tarjeta de Evidencia Fotográfica --}}
            @php
                $patron = public_path('uploads/devoluciones/dev_' . $devolucion->id . '_*.*');
                $evidencias = glob($patron); 
            @endphp

            @if(is_array($evidencias) && count($evidencias) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-camera mr-1"></i> Evidencia Fotográfica</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($evidencias as $rutaFisica)
                            @php
                                $nombre = basename($rutaFisica);
                                $rutaWeb = asset('uploads/devoluciones/' . $nombre);
                            @endphp
                            
                            <div class="col-md-4 col-sm-6 mb-4">
                                <a href="{{ $rutaWeb }}" target="_blank" title="Ver imagen completa" style="text-decoration: none; display: block;">
                                    <div class="border rounded shadow-sm mb-2" style="overflow: hidden; height: 160px;">
                                        <img src="{{ $rutaWeb }}" 
                                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" 
                                             onmouseover="this.style.transform='scale(1.08)'" 
                                             onmouseout="this.style.transform='scale(1)'" 
                                             alt="Evidencia">
                                    </div>
                                </a>

                                <a href="{{ $rutaWeb }}" download="Descarga_{{ $nombre }}" class="btn btn-sm btn-outline-primary btn-block shadow-sm">
                                    <i class="fas fa-download mr-1"></i> Descargar
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted"><i class="fas fa-search-plus mr-1"></i>Haz clic en cualquier imagen para ampliarla en una nueva pestaña.</small>
                </div>
            </div>
            @endif

        </div>

        {{-- ======================================================= --}}
        {{-- COLUMNA DERECHA: ESTADO Y EXTRAS                        --}}
        {{-- ======================================================= --}}
        <div class="col-lg-4">
            
            {{-- Tarjeta de Gestión de Estado --}}
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-tasks mr-1"></i> Gestión de Estado</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="text-xs font-weight-bold text-uppercase text-gray-500">Estado Actual</span><br>
                        
                        @php
                            $estiloShow = '';
                            $textoShow = '';
                            
                            switch($devolucion->estado) {
                                case 'devolucion_pendiente': 
                                    $estiloShow = 'background-color: #f6c23e !important; color: #333 !important;'; 
                                    $textoShow = 'PENDIENTE'; 
                                    break;
                                case 'devolucion_aprobada': 
                                    $estiloShow = 'background-color: #36b9cc !important; color: #fff !important;'; 
                                    $textoShow = 'EN PROCESO'; 
                                    break;
                                case 'devolucion_finalizada': 
                                    $estiloShow = 'background-color: #1cc88a !important; color: #fff !important;'; 
                                    $textoShow = 'FINALIZADA'; 
                                    break;
                                case 'devolucion_rechazada': 
                                    $estiloShow = 'background-color: #e74a3b !important; color: #fff !important;'; 
                                    $textoShow = 'RECHAZADA'; 
                                    break;
                                case 'devolucion_cancelada': 
                                    $estiloShow = 'background-color: #858796 !important; color: #fff !important;'; 
                                    $textoShow = 'CANCELADA'; 
                                    break;
                                default:
                                    $estiloShow = 'background-color: #858796 !important; color: #fff !important;';
                                    $textoShow = $devolucion->estado;
                            }
                        @endphp

                        <span class="badge px-3 py-2 mt-2 shadow-sm" style="{{ $estiloShow }} font-size: 1rem; opacity: 1;">
                            {{ $textoShow }}
                        </span>
                    </div>

                    {{-- HUELLA EN LA VISTA DETALLE --}}
                    @if($devolucion->validador_id && $devolucion->estado != 'devolucion_pendiente')
                        <div class="mt-3 text-left bg-light p-2 border rounded small mb-3">
                            <span class="text-muted d-block mb-1"><i class="fas fa-user-check"></i> Modificado por: <strong class="text-dark">{{ $devolucion->validador->name ?? 'Admin' }}</strong></span>
                            <span class="text-muted d-block mb-1"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($devolucion->fecha_validacion)->format('d/m/Y h:i A') }}</span>
                            @if($devolucion->estado == 'devolucion_rechazada' && $devolucion->motivo_rechazo)
                                <div class="text-danger mt-1 font-weight-bold border-top pt-1"><i class="fas fa-exclamation-circle"></i> Motivo: {{ $devolucion->motivo_rechazo }}</div>
                            @endif
                        </div>
                    @endif

                    @if(Auth::user()->hasRole('admin'))
                        <hr>
                        {{-- 1. FORMULARIO ADMIN CON SWEETALERT2 --}}
                        <form action="{{ route('devoluciones.updateStatus', $devolucion->id) }}" method="POST" class="form-confirmar"
                              data-title="¿Actualizar Estado?" 
                              data-text="Se actualizará el estado de esta devolución en el sistema." 
                              data-icon="question" 
                              data-color="#4e73df" 
                              data-btn-text="Sí, actualizar">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">Cambiar Estado:</label>
                                <select name="nuevo_estado" class="form-control mb-2" onchange="mostrarMotivoDev(this)">
                                    <option value="devolucion_pendiente" {{ $devolucion->estado == 'devolucion_pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="devolucion_aprobada" {{ $devolucion->estado == 'devolucion_aprobada' ? 'selected' : '' }}>Aprobar (En Proceso)</option>
                                    <option value="devolucion_finalizada" {{ $devolucion->estado == 'devolucion_finalizada' ? 'selected' : '' }}>Finalizar</option>
                                    <option value="devolucion_rechazada" {{ $devolucion->estado == 'devolucion_rechazada' ? 'selected' : '' }}>Rechazar</option>
                                    <option value="devolucion_cancelada" {{ $devolucion->estado == 'devolucion_cancelada' ? 'selected' : '' }}>Cancelar</option>
                                </select>
                                
                                {{-- CAJA DE MOTIVO DE RECHAZO OCULTA --}}
                                <div class="form-group mt-2" id="divMotivoDev" style="display: none;">
                                    <label class="small font-weight-bold text-danger">Motivo de Rechazo:</label>
                                    <textarea name="motivo_rechazo" id="motivo_rechazo_dev" class="form-control" rows="2" placeholder="Explique el motivo..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block shadow-sm">Actualizar Estado</button>
                        </form>
                    @elseif($devolucion->user_id == Auth::id() && $devolucion->estado == 'devolucion_pendiente')
                        <hr>
                        {{-- 2. FORMULARIO AGENTE CON SWEETALERT2 (Adiós al onsubmit viejo) --}}
                        <form action="{{ route('devoluciones.updateStatus', $devolucion->id) }}" method="POST" class="form-confirmar"
                              data-title="¿Cancelar Solicitud?" 
                              data-text="Esta acción cancelará tu solicitud de devolución." 
                              data-icon="warning" 
                              data-color="#e74a3b" 
                              data-btn-text="Sí, cancelar solicitud">
                            @csrf @method('PUT')
                            <input type="hidden" name="nuevo_estado" value="devolucion_cancelada">
                            <button type="submit" class="btn btn-outline-danger btn-block">Cancelar Solicitud</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Tarjeta de Comentarios --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-comment-alt mr-1"></i> Comentarios / Observaciones</h6>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-light rounded border-left-info">
                        <p class="small text-gray-700 mb-0" style="white-space: pre-wrap;">{{ $devolucion->comentarios ?: 'Sin observaciones adicionales.' }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function mostrarMotivoDev(selectObj) {
        var divMotivo = document.getElementById('divMotivoDev');
        var textarea = document.getElementById('motivo_rechazo_dev');
        
        if (selectObj.value === 'devolucion_rechazada') {
            divMotivo.style.display = 'block';
            textarea.required = true;
        } else {
            divMotivo.style.display = 'none';
            textarea.required = false;
        }
    }
</script>
@endsection