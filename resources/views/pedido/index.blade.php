@extends('plantilla.app')
@section('contenido')
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Pedidos</h3>
                    </div>
                    <div class="card-body">
                        
                        {{-- ========================================== --}}
                        {{-- TARJETA DE PEDIDOS PENDIENTES (CLICABLE)   --}}
                        {{-- ========================================== --}}
                        <div class="row mb-4">
                            <div class="col-md-4 col-sm-6">
                                <a href="{{ route('perfil.pedidos', ['estado' => 'pendiente']) }}" class="text-decoration-none">
                                    <div class="card shadow-sm border-0 bg-warning text-dark" 
                                         style="border-left: 5px solid #d39e00 !important; transition: transform 0.2s;" 
                                         onmouseover="this.style.transform='translateY(-5px)'" 
                                         onmouseout="this.style.transform='translateY(0)'">
                                        <div class="card-body d-flex align-items-center p-3">
                                            <div class="fs-1 me-3 opacity-75">
                                                <i class="fas fa-hand-pointer"></i> <!-- Cambié el ícono para que invite al clic -->
                                            </div>
                                            <div>
                                                <h3 class="card-title mb-0 fw-bold">{{ $pendientesCount }}</h3>
                                                <p class="card-text mb-0 fw-semibold"> Pedidos Pendientes por Aprobar (Clic para ver)</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        {{-- ========================================== --}}
                        {{-- FORMULARIO DE FILTROS ACTUALIZADO          --}}
                        {{-- ========================================== --}}
                        <div>
                            <form action="{{route('perfil.pedidos')}}" method="get" class="mb-3">
                                <div class="row g-2 align-items-center stack-movil">
                                    
                                    {{-- NUEVO: Selector de Estado --}}
                                    <div class="col-md-2">
                                        <select name="estado" class="form-select form-select-sm">
                                            <option value="">-- Todos los Estados --</option>
                                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                                            <option value="aprobado" {{ request('estado') == 'aprobado' ? 'selected' : '' }}>Aprobados</option>
                                            <option value="parcialmente_surtido" {{ request('estado') == 'parcialmente_surtido' ? 'selected' : '' }}>Parcialmente Surtidos</option>
                                            <option value="enviado_completo" {{ request('estado') == 'enviado_completo' ? 'selected' : '' }}>Enviados</option>
                                            <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregados</option>
                                            <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelados / Anulados</option>
                                        </select>
                                    </div>

                                    {{-- Selector de Mes --}}
                                    <div class="col-md-2">
                                        <select name="month" class="form-select form-select-sm">
                                            <option value="">-- Todos los Meses --</option>
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Selector de Año --}}
                                    <div class="col-md-2">
                                        @php $currentYear = now()->year; @endphp
                                        <select name="year" class="form-select form-select-sm">
                                            <option value="">-- Todos los Años --</option>
                                            @foreach(range($currentYear, $currentYear - 5) as $y) 
                                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Barra de Búsqueda (Ajustada a col-md-4) --}}
                                    <div class="col-md-4">
                                        <input name="texto" type="text" class="form-control form-control-sm"
                                            value="{{ $texto ?? '' }}" placeholder="Buscar Usuario/Agente/Cliente">
                                    </div>

                                    {{-- Botón Buscar --}}
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-secondary btn-sm w-100 fw-bold">
                                            <i class="fas fa-search me-1"></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Botón Exportar (Mantenido intacto) --}}
                            @role('admin')
                            <div class="text-end mb-3">
                                <a href="{{ route('dashboard.exportar.pedidos', [
                                            'month' => request('month'),
                                            'year' => request('year'),
                                            'estado' => request('estado'), 
                                            'texto' => $texto ?? ''
                                            ]) }}"
                                   class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel me-1"></i> Exportar Resultados a Excel
                                </a>
                            </div>
                            @endrole
                        </div>

                        @if(Session::has('mensaje'))
                        <div class="alert alert-info alert-dismissible fade show mt-2">
                            {{Session::get('mensaje')}}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
                        </div>
                        @endif

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 100px">Opciones</th>
                                        <th style="width: 20px">ID</th>
                                        <th><i class="far fa-calendar-alt me-1"></i> Fecha y Hora</th>
                                        <th>Cliente</th>
                                        <th>Agente Creador</th>
                                        <th style="width: 80px">Total</th>
                                        <th style="width: 80px">Estado</th>
                                        <th style="width: 220px">Guía de Envío</th> 
                                        <th style="width: 100px">Flete Pagado</th>
                                        <th style="width: 80px">Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($registros) <= 0)
                                        <tr><td colspan="10" class="text-center">No hay registros que coincidan.</td></tr>
                                    @else
                                        @foreach($registros as $reg)
                                        <tr class="align-middle">
                                            <td> {{-- Celda Opciones --}}
                                                @php
                                                $esAgenteYPuedeCancelar = auth()->user()->hasRole('agente-ventas') && $reg->estado == 'pendiente';
                                                $esAdminYPuedeActuar = auth()->user()->can('pedido-anulate') && 
                                                    in_array($reg->estado, ['pendiente', 'aprobado', 'parcialmente_surtido', 'enviado', 'enviado_completo', 'rechazado']);
                                                @endphp

                                                @if( $esAgenteYPuedeCancelar || $esAdminYPuedeActuar )
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modal-estado-{{$reg->id}}">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                                @endif
                                            </td>
                                            <td>{{$reg->id}}</td>
                                            
                                            {{-- CELDA DE FECHA Y HORA MODIFICADA --}}
                                            <td class="text-nowrap">
                                                <span class="d-block font-weight-bold text-dark">{{ $reg->created_at->format('d/m/Y') }}</span>
                                                <span class="small text-muted"><i class="far fa-clock"></i> {{ $reg->created_at->format('h:i A') }}</span>
                                            </td>

                                            <td>{{ $reg->cliente->nombre ?? 'N/A' }}</td>
                                            <td>
                                                {{ $reg->agente->name ?? 'N/A' }}
                                                @if ($reg->cliente && $reg->cliente->user_id != $reg->user_id)
                                                <small class="text-muted d-block">
                                                    (Agente asignado: {{ $reg->cliente->agente->name ?? '?' }})
                                                </small>
                                                @endif
                                            </td>
                                            <td>${{ number_format($reg->total, 2) }}</td>
                                            <td> {{-- Celda Estado --}}
                                                @php
                                                    $estado = strtolower($reg->estado);
                                                    $estilo = '';
                                                    $texto = ucfirst(str_replace('_', ' ', $estado));

                                                    if ($estado == 'pendiente') {
                                                        $estilo = 'background-color: #ffc107 !important; color: #333 !important;'; 
                                                        $texto = 'Pendiente';
                                                    } elseif ($estado == 'aprobado') {
                                                        $estilo = 'background-color: #20c997 !important; color: #fff !important;'; 
                                                        $texto = 'Aprobado';
                                                    } elseif ($estado == 'rechazado') {
                                                        $estilo = 'background-color: #dc3545 !important; color: #fff !important;'; 
                                                        $texto = 'Rechazado';
                                                    } elseif ($estado == 'enviado' || $estado == 'enviado_completo') {
                                                        $estilo = 'background-color: #17a2b8 !important; color: #fff !important;'; 
                                                        $texto = 'Enviado';
                                                    } elseif (in_array($estado, ['completado', 'entregado', 'finalizado'])) {
                                                        $estilo = 'background-color: #28a745 !important; color: #fff !important;'; 
                                                        $texto = 'Completado';
                                                    } elseif ($estado == 'parcialmente_surtido') {
                                                        $estilo = 'background-color: #fd7e14 !important; color: #fff !important;'; 
                                                        $texto = 'Parcial';
                                                    } elseif ($estado == 'cancelado') {
                                                        $estilo = 'background-color: #6c757d !important; color: #fff !important;'; 
                                                        $texto = 'Cancelado';
                                                    } elseif ($estado == 'anulado') {
                                                        $estilo = 'background-color: #dc3545 !important; color: #fff !important;'; 
                                                        $texto = 'Anulado';
                                                    } elseif ($estado == 'cotizacion') {
                                                        $estilo = 'background-color: #007bff !important; color: #fff !important;'; 
                                                        $texto = 'Cotización';
                                                    } elseif ($estado == 'devolucion_pendiente') {
                                                        $estilo = 'background-color: #f6c23e !important; color: #333 !important;'; 
                                                        $texto = 'Dev. Pendiente';
                                                    } elseif ($estado == 'devolucion_aprobada') {
                                                        $estilo = 'background-color: #36b9cc !important; color: #fff !important;'; 
                                                        $texto = 'En Proceso (Dev)';
                                                    } elseif ($estado == 'devolucion_finalizada') {
                                                        $estilo = 'background-color: #1cc88a !important; color: #fff !important;'; 
                                                        $texto = 'Dev. Finalizada';
                                                    } elseif ($estado == 'devolucion_rechazada') {
                                                        $estilo = 'background-color: #e74a3b !important; color: #fff !important;'; 
                                                        $texto = 'Dev. Rechazada';
                                                    } elseif ($estado == 'devolucion_cancelada') {
                                                        $estilo = 'background-color: #858796 !important; color: #fff !important;'; 
                                                        $texto = 'Dev. Cancelada';
                                                    } else {
                                                        $estilo = 'background-color: #343a40 !important; color: #fff !important;';
                                                    }
                                                @endphp

                                                <span class="badge badge-pill shadow-sm px-3 py-2" style="{{ $estilo }} font-size: 0.85em; opacity: 1;">
                                                    {{ $texto }}
                                                </span>
                                                
                                                @if($reg->validador_id && $estado != 'pendiente' && $estado != 'cotizacion')
                                                    @php
                                                        $accionTexto = 'Actualizó'; 
                                                        if ($estado == 'aprobado') $accionTexto = 'Autorizó';
                                                        elseif ($estado == 'rechazado') $accionTexto = 'Rechazó';
                                                        elseif ($estado == 'parcialmente_surtido') $accionTexto = 'Surtió (Parcial)';
                                                        elseif ($estado == 'enviado' || $estado == 'enviado_completo') $accionTexto = 'Envió';
                                                        elseif (in_array($estado, ['entregado', 'completado', 'finalizado'])) $accionTexto = 'Entregó';
                                                        elseif ($estado == 'cancelado' || $estado == 'anulado') $accionTexto = 'Canceló';
                                                        elseif ($estado == 'devolucion_aprobada') $accionTexto = 'Aprobó Dev.';
                                                        elseif ($estado == 'devolucion_rechazada') $accionTexto = 'Rechazó Dev.';
                                                        elseif ($estado == 'devolucion_finalizada') $accionTexto = 'Completó Dev.';
                                                        elseif ($estado == 'devolucion_cancelada') $accionTexto = 'Canceló Dev.';
                                                    @endphp
                                                    <div class="mt-2 text-start bg-light p-1 border rounded" style="font-size: 0.75rem; line-height: 1.3;">
                                                        <span class="text-muted d-block">
                                                            <i class="bi bi-person-check-fill"></i> 
                                                            {{ $accionTexto }}: 
                                                            <strong class="text-dark">{{ $reg->validador->name ?? 'Admin' }}</strong>
                                                        </span>
                                                        <span class="text-muted d-block mt-1">
                                                            <i class="bi bi-clock-history"></i> {{ \Carbon\Carbon::parse($reg->fecha_validacion)->format('d/m/Y h:i A') }}
                                                        </span>
                                                        @if(in_array($estado, ['rechazado', 'devolucion_rechazada']) && $reg->motivo_rechazo)
                                                            <div class="text-danger mt-1 text-wrap" style="max-width: 150px; font-weight: 500;">
                                                                <i class="bi bi-info-circle-fill"></i> Motivo: {{ $reg->motivo_rechazo }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @php
                                                    $showParcial = in_array($estado, ['parcialmente_surtido', 'enviado_completo', 'entregado', 'finalizado']);
                                                    $showCompleta = in_array($estado, ['enviado_completo', 'entregado', 'finalizado']);
                                                    $isAdmin = auth()->user()->hasRole('admin');
                                                @endphp

                                                @if($isAdmin)
                                                    @if($showParcial)
                                                        <div class="input-group input-group-sm mb-1">
                                                            <span class="input-group-text bg-light" title="Guía Parcial">
                                                                <i class="fas fa-shipping-fast text-secondary"></i>
                                                            </span>
                                                            <input type="text" class="form-control tracking-input" data-pedido-id="{{ $reg->id }}" data-tipo="guia_parcial" value="{{ $reg->guia_parcial }}" placeholder="Guía Parcial...">
                                                            <button class="btn btn-outline-secondary btn-save-tracking" type="button"><i class="fas fa-save"></i></button>
                                                        </div>
                                                    @endif
                                                    @if($showCompleta)
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-light" title="Guía Completa">
                                                                <i class="fas fa-truck text-success"></i>
                                                            </span>
                                                            <input type="text" class="form-control tracking-input" data-pedido-id="{{ $reg->id }}" data-tipo="guia_completa" value="{{ $reg->guia_completa }}" placeholder="Guía Completa...">
                                                            <button class="btn btn-outline-success btn-save-tracking" type="button"><i class="fas fa-save"></i></button>
                                                        </div>
                                                    @endif
                                                    @if(!$showParcial && !$showCompleta)
                                                        <small class="text-muted">No aplica</small>
                                                    @endif
                                                @else
                                                    @if($reg->guia_parcial)
                                                        <span class="badge bg-secondary d-block mb-1">Parcial: {{ $reg->guia_parcial }}</span>
                                                    @endif
                                                    @if($reg->guia_completa)
                                                        <span class="badge bg-success d-block">Completa: {{ $reg->guia_completa }}</span>
                                                    @endif
                                                    @if(!$reg->guia_parcial && !$reg->guia_completa)
                                                        <small class="text-muted">-</small>
                                                    @endif
                                                @endif
                                            </td>

                                            <td>
                                                @if($reg->flete_pagado)
                                                <span class="badge bg-success">Sí</span>
                                                @else
                                                <span class="badge bg-danger">No</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{-- BOTÓN DE EDITAR (Solo Pendientes o Rechazados) --}}
                                                @if(in_array(strtolower($reg->estado), ['pendiente', 'rechazado']))
                                                    @if(auth()->user()->hasRole('admin') || auth()->user()->id == $reg->user_id)
                                                        <a href="{{ route('pedidos.editar', $reg->id) }}" class="btn btn-sm btn-warning w-100 shadow-sm mb-2 text-dark fw-bold">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </a>
                                                    @endif
                                                @endif

                                                {{-- BOTÓN ORIGINAL DE VER DETALLES --}}
                                                <button class="btn btn-sm btn-primary w-100 shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#detalles-{{ $reg->id }}">
                                                    Ver detalles
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="detalles-{{ $reg->id }}">
                                            <td colspan="10">
                                                <div class="p-3 bg-light border-bottom">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0 fw-bold text-primary">Detalles del Pedido #{{ $reg->id }}</h6>
                                                        <a href="{{ route('pedidos.pdf', $reg->id) }}" target="_blank" class="btn btn-danger btn-sm shadow-sm">
                                                            <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                                                        </a>
                                                    </div>
                                                    
                                                    @if($reg->guia_parcial || $reg->guia_completa)
                                                        <div class="alert alert-white border mb-3 py-2 shadow-sm">
                                                            <h6 class="text-info fw-bold mb-2 small text-uppercase"><i class="fas fa-shipping-fast me-2"></i>Información de Envío:</h6>
                                                            @if($reg->guia_parcial)
                                                                <div class="mb-1"><strong class="text-muted">Guía Parcial:</strong> <span class="font-monospace ms-2 bg-light px-2 rounded">{{ $reg->guia_parcial }}</span></div>
                                                            @endif
                                                            @if($reg->guia_completa)
                                                                <div><strong class="text-success">Guía Completa:</strong> <span class="font-monospace ms-2 fw-bold bg-success text-white px-2 rounded">{{ $reg->guia_completa }}</span></div>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if($reg->direccion_entrega)
                                                        <div class="alert alert-info py-2 mb-2 small shadow-sm border-0" style="border-left: 4px solid #17a2b8 !important;">
                                                            <i class="fas fa-map-marker-alt text-danger me-2"></i> <strong>Dirección de Entrega:</strong> <span class="text-dark">{{ $reg->direccion_entrega }}</span>
                                                        </div>
                                                    @endif

                                                    @if($reg->comentarios_limpios)
                                                        <div class="alert alert-warning py-2 mb-3 small shadow-sm border-0" style="border-left: 4px solid #ffc107 !important;">
                                                            <i class="fas fa-comment-alt me-1"></i> <strong>Comentarios:</strong> {{ $reg->comentarios_limpios }}
                                                        </div>
                                                    @endif

                                                    <!-- ========================================== -->
                                                    <!-- INICIO DE BITÁCORA / LÍNEA DE TIEMPO       -->
                                                    <!-- ========================================== -->
                                                    @if($reg->historial && $reg->historial->count() > 0)
                                                    <div class="mb-3 p-3 bg-white border rounded shadow-sm">
                                                        <h6 class="text-secondary fw-bold border-bottom pb-2 mb-3">
                                                            <i class="fas fa-history me-1"></i> Historial de Movimientos
                                                        </h6>
                                                        <div style="max-height: 160px; overflow-y: auto;">
                                                            <ul class="list-group list-group-flush small">
                                                                @foreach($reg->historial as $movimiento)
                                                                    <li class="list-group-item bg-transparent px-0 py-2 border-bottom-dashed">
                                                                        <div class="d-flex w-100 justify-content-between">
                                                                            <div>
                                                                                <i class="bi bi-record-circle-fill text-primary me-2"></i>
                                                                                <strong>{{ $movimiento->usuario->name ?? 'Sistema' }}</strong> cambió el estado a 
                                                                                <span class="badge bg-dark mx-1">{{ strtoupper(str_replace('_', ' ', $movimiento->estado_nuevo)) }}</span>
                                                                            </div>
                                                                            <span class="text-muted" style="font-size: 0.8rem;">
                                                                                {{ $movimiento->created_at->format('d/m/Y h:i A') }}
                                                                            </span>
                                                                        </div>
                                                                        @if($movimiento->comentario)
                                                                            <div class="text-danger mt-1 ms-4" style="font-style: italic;">
                                                                                <i class="bi bi-chat-left-text text-danger me-1"></i> "{{ $movimiento->comentario }}"
                                                                            </div>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <!-- ========================================== -->
                                                    <!-- FIN DE BITÁCORA                            -->
                                                    <!-- ========================================== -->

                                                    <table class="table table-sm table-striped mb-0 bg-white shadow-sm">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 30%">Producto</th>
                                                                <th style="width: 10%">Imagen</th>
                                                                <th style="width: 10%">Cantidad</th>
                                                                <th style="width: 15%">Precio Lista</th>
                                                                <th style="width: 15%">Precio c/ Desc.</th>
                                                                <th style="width: 5%">Inner</th>
                                                                <th style="width: 15%">Subtotal Línea</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $pct_desc_vista = $reg->subtotal > 0 ? ($reg->descuento_aplicado / $reg->subtotal) : 0;
                                                            @endphp
                                                            @forelse($reg->detalles as $detalle)
                                                            @php
                                                                $precio_lista_vista = $detalle->precio;
                                                                
                                                                // APLICAMOS LA MISMA LEY DEL REDONDEO EN EL DISEÑO VISUAL
                                                                $descuento_unitario = round($precio_lista_vista * $pct_desc_vista, 2);
                                                                $precio_desc_vista = $precio_lista_vista - $descuento_unitario;
                                                                
                                                                // Ahora la multiplicación dará exacta: 8.45 * 3000 = 25350
                                                                $subtotal_linea_vista = $precio_desc_vista * $detalle->cantidad;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $detalle->producto->nombre ?? 'Producto no encontrado' }}</td>
                                                                <td>
                                                                    @if($detalle->producto && $detalle->producto->imagen)
                                                                        <img src="{{ asset('uploads/productos/' . $detalle->producto->imagen ) }}" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                                    @else
                                                                        <span class="text-muted small">Sin img</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $detalle->cantidad}}</td>
                                                                <td><span class="text-muted text-decoration-line-through">${{ number_format($precio_lista_vista, 2) }}</span></td>
                                                                <td><span class="text-success fw-bold">${{ number_format($precio_desc_vista, 2) }}</span></td>
                                                                <td>{{ $detalle->inner ?? 'N/A'}}</td>
                                                                <td>${{ number_format($subtotal_linea_vista, 2) }}</td>
                                                            </tr>
                                                            @empty
                                                            <tr><td colspan="7" class="text-center">No hay detalles.</td></tr>
                                                            @endforelse
                                                            <tr>
                                                                <td colspan="7" class="text-end pt-3 bg-white">
                                                                    <div style="max-width: 250px; margin-left: auto;">
                                                                        <div class="d-flex justify-content-between mb-1">
                                                                            <span class="text-muted small">Subtotal:</span>
                                                                            <span>${{ number_format($reg->subtotal ?? 0, 2) }}</span>
                                                                        </div>
                                                                        @if($reg->descuento_aplicado > 0)
                                                                        <div class="d-flex justify-content-between mb-1 text-danger">
                                                                            <span class="small">Descuento:</span>
                                                                            <span>-${{ number_format($reg->descuento_aplicado ?? 0, 2) }}</span>
                                                                        </div>
                                                                        @endif
                                                                        <div class="d-flex justify-content-between mb-1 text-muted">
                                                                            <span class="small">IVA (16%):</span>
                                                                            <span>+${{ number_format($reg->iva ?? 0, 2) }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-between border-top pt-2 mt-1">
                                                                            <strong class="text-dark">TOTAL:</strong>
                                                                            <strong class="fs-6">${{ number_format($reg->total, 2) }}</strong>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                </div>
                                            </td>
                                        </tr>
                                        @include('pedido.state')
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $registros->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.btn-save-tracking').click(function() {
            var btn = $(this);
            var input = btn.prev('.tracking-input'); 
            var pedidoId = input.data('pedido-id');
            var tipoGuia = input.data('tipo');
            var valorGuia = input.val();
            var iconOriginal = btn.html();

            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: '/pedidos/' + pedidoId + '/update-guia', 
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tipo: tipoGuia,
                    valor: valorGuia
                },
                success: function(response) {
                    if(response.success) {
                        btn.html('<i class="fas fa-check"></i>').removeClass('btn-outline-secondary btn-outline-success').addClass('btn-success');
                        setTimeout(function(){
                            var claseOriginal = (tipoGuia == 'guia_completa') ? 'btn-outline-success' : 'btn-outline-secondary';
                            btn.html('<i class="fas fa-save"></i>').prop('disabled', false).removeClass('btn-success').addClass(claseOriginal);
                        }, 2000);
                    }
                },
                error: function() {
                    alert('Error al guardar. Intente nuevamente.');
                    btn.html(iconOriginal).prop('disabled', false);
                }
            });
        });
    });

    function mostrarMotivo(selectObj, idPedido) {
        var divMotivo = document.getElementById('divMotivo-' + idPedido);
        var textarea = document.getElementById('motivo_rechazo-' + idPedido);
        
        // Agregamos 'cancelado' y 'anulado' para que también pidan motivo
        if (selectObj.value === 'rechazado' || selectObj.value === 'cancelado' || selectObj.value === 'anulado') {
            divMotivo.style.display = 'block';
            textarea.required = true; 
        } else {
            divMotivo.style.display = 'none';
            textarea.required = false; 
        }
    }
</script>
@endsection