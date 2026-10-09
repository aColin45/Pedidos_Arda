@extends('plantilla.app')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-top-primary">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold text-primary mb-0">
                <i class="fas fa-file-invoice-dollar mr-2"></i> Gestión de Cotizaciones (Vigencia de 6 días)
            </h3>
        </div>
        <div class="card-body p-0">
            @if(Session::has('mensaje'))
                <div class="alert alert-success m-3">{{ Session::get('mensaje') }}</div>
            @endif
            @if(Session::has('error'))
                <div class="alert alert-danger m-3">{{ Session::get('error') }}</div>
            @endif

            {{-- FORMULARIO DE BÚSQUEDA CON BOTÓN DE LIMPIAR --}}
            <form action="{{ route('cotizaciones.index') }}" method="GET" class="mb-4">
                <div class="input-group shadow-sm">
                    <span class="input-group-text text-white border-0" style="background-color: #0d6efd;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="texto" class="form-control border-primary border-end-0" 
                        placeholder="Buscar por ID, Cliente, Agente o Estado..." 
                        value="{{ request('texto') }}">
                    
                    {{-- Botón 'X' para limpiar la búsqueda --}}
                    @if(request('texto'))
                        <a href="{{ route('cotizaciones.index') }}" class="btn btn-light border-primary border-start-0 text-danger px-3" title="Limpiar filtro">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                    
                    <button class="btn btn-warning fw-bold text-dark border-0 px-4" type="submit">
                        Buscar
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Agente</th>
                            <th>Cliente</th>
                            <th>Vencimiento</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cotizaciones as $cot)
                            <tr>
                                <td class="align-middle">#{{ $cot->id }}</td>
                                <td class="align-middle">{{ $cot->agente->name ?? 'N/A' }}</td>
                                <td class="align-middle">{{ $cot->cliente->nombre ?? 'N/A' }}</td>
                                <td class="align-middle">
                                    @if($cot->fecha_vencimiento)
                                        <i class="far fa-calendar-alt text-muted"></i> 
                                        {{ \Carbon\Carbon::parse($cot->fecha_vencimiento)->format('d/m/Y') }}
                                    @else
                                        ---
                                    @endif
                                </td>
                                <td class="align-middle font-weight-bold text-success">${{ number_format($cot->total, 2) }}</td>
                                <td class="align-middle">
                                    {{-- MEJORA VISUAL DEL ESTADO MULTI-CASO --}}
                                    @php
                                        $estadoLimpio = strtolower(trim($cot->estado));
                                    @endphp

                                    @if($estadoLimpio == 'cotizacion')
                                        <span class="badge bg-info text-dark shadow-sm" style="font-size: 0.85em; padding: 6px 10px;">
                                            <i class="fas fa-clock mr-1"></i> Activa
                                        </span>
                                    @elseif($estadoLimpio == 'cancelado')
                                        <span class="badge bg-warning text-dark shadow-sm" style="font-size: 0.85em; padding: 6px 10px;">
                                            <i class="fas fa-ban mr-1"></i> Cancelada
                                        </span>
                                    @elseif($estadoLimpio == 'no procede')
                                        <span class="badge bg-danger text-white shadow-sm" style="font-size: 0.85em; padding: 6px 10px;">
                                            <i class="fas fa-times-circle mr-1"></i> Rechazada
                                        </span>
                                        <br><small class="text-muted mt-1 d-block">Motivo: {{ $cot->motivo_rechazo ?? 'No especificado' }}</small>
                                    @elseif($estadoLimpio == 'caducada')
                                        <span class="badge bg-secondary text-white shadow-sm" style="font-size: 0.85em; padding: 6px 10px;">
                                            <i class="fas fa-hourglass-end mr-1"></i> Expirada
                                        </span>
                                    {{-- ========================================================= --}}
                                    {{-- NUEVO ESTADO: CONVERTIDA A PEDIDO CON SU COMENTARIO     --}}
                                    {{-- ========================================================= --}}
                                    @elseif($estadoLimpio == 'convertida a pedido')
                                        <span class="badge bg-dark text-white shadow-sm" style="font-size: 0.85em; padding: 6px 10px;">
                                            <i class="fas fa-exchange-alt mr-1"></i> Convertida
                                        </span>
                                        @if($cot->comentarios)
                                            <small class="text-muted mt-1 d-block font-weight-bold" style="font-size: 0.75rem;">
                                                <i class="fas fa-share mr-1"></i> {{ $cot->comentarios }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-dark shadow-sm">{{ ucfirst($cot->estado) }}</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($cot->estado == 'cotizacion')
                                        <div class="d-flex justify-content-center align-items-center flex-wrap" style="gap: 8px;">
                                            
                                            {{-- Descargar PDF --}}
                                            <a href="{{ route('cotizaciones.pdf', $cot->id) }}" target="_blank" class="btn btn-sm btn-secondary shadow-sm" title="Descargar PDF">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>

                                            {{-- Editar --}}
                                            <a href="{{ route('cotizaciones.editar', $cot->id) }}" class="btn btn-sm btn-primary shadow-sm" title="Editar Cantidades/Productos">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            
                                            {{-- ======================================================= --}}
                                            {{-- || BOTÓN: CONVERTIR A PEDIDO (Abre Modal)            || --}}
                                            {{-- ======================================================= --}}
                                            <button type="button" class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalConvertir{{ $cot->id }}" title="Convertir a Pedido">
                                                <i class="fas fa-check-circle"></i> Convertir a Pedido
                                            </button>

                                            <div class="modal fade text-left" id="modalConvertir{{ $cot->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        {{-- AGREGAMOS CLASE form-confirmar AL FORMULARIO DEL MODAL --}}
                                                        <form action="{{ route('cotizaciones.convertir', $cot->id) }}" method="POST" class="form-confirmar"
                                                              data-title="¿Generar Pedido?" 
                                                              data-text="Se creará un nuevo pedido a partir de esta cotización." 
                                                              data-icon="question" 
                                                              data-color="#198754" 
                                                              data-btn-text="Sí, convertir">
                                                            @csrf
                                                            <div class="modal-header bg-success text-white">
                                                                <h5 class="modal-title"><i class="fas fa-truck mr-2"></i> Dirección de Entrega</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-info small">
                                                                    <i class="fas fa-info-circle mr-1"></i> Puede indicar la dirección de entrega ahora, o dejar el espacio en blanco si aún no cuenta con el dato.
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="font-weight-bold text-dark">Dirección Completa (Opcional):</label>
                                                                    <textarea name="direccion_entrega" class="form-control" rows="3" placeholder="Calle, Número, Colonia, C.P., Ciudad, Estado...">{{ $cot->direccion_entrega }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-success">Confirmar Conversión</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Cancelar Cotización (Agente) --}}
                                            <form action="{{ route('cotizaciones.cancelar', $cot->id) }}" method="POST" class="d-inline mb-0 form-confirmar"
                                                  data-title="¿Cancelar Cotización?" 
                                                  data-text="Esta cotización quedará marcada como cancelada." 
                                                  data-icon="warning" 
                                                  data-color="#ffc107" 
                                                  data-btn-text="Sí, cancelar">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning text-dark shadow-sm" title="Cancelar">
                                                    <i class="fas fa-trash-alt"></i> Cancelar
                                                </button>
                                            </form>

                                            {{-- ======================================================= --}}
                                            {{-- || ADMIN: NO PROCEDE (Abre Modal de Rechazo)         || --}}
                                            {{-- ======================================================= --}}
                                            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin'))
                                                {{-- CORRECCIÓN: data-bs-target en lugar de data-target --}}
                                                <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNoProcede{{ $cot->id }}" title="Marcar como No Procede">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>

                                                <div class="modal fade text-left" id="modalNoProcede{{ $cot->id }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            {{-- AGREGAMOS CLASE form-confirmar AL FORMULARIO DEL MODAL --}}
                                                            <form action="{{ route('cotizaciones.rechazar', $cot->id) }}" method="POST" class="form-confirmar"
                                                                  data-title="¿Rechazar Cotización?" 
                                                                  data-text="Esta cotización se marcará como 'No Procede'." 
                                                                  data-icon="warning" 
                                                                  data-color="#dc3545" 
                                                                  data-btn-text="Sí, rechazar">
                                                                @csrf
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Motivo de Rechazo</h5>
                                                                    {{-- CORRECCIÓN: data-bs-dismiss en lugar de data-dismiss --}}
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="form-group">
                                                                        <label class="font-weight-bold">Indique por qué la cotización no procede:</label>
                                                                        <textarea name="motivo" class="form-control" required rows="3" placeholder="Ej. El cliente declinó por el precio..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                    <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1"><i class="fas fa-lock mr-1"></i> Cerrada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-light"></i><br>
                                    No hay cotizaciones activas o en historial.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $cotizaciones->links() }}
            </div>
        </div>
    </div>
</div>
@endsection