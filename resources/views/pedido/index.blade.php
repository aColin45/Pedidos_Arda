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
                        {{-- FORMULARIO DE FILTROS --}}
                        <div>
                            <form action="{{route('perfil.pedidos')}}" method="get" class="mb-3">
                                <div class="row g-2 align-items-center">
                                    {{-- Selector de Mes --}}
                                    <div class="col-md-3">
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

                                    {{-- Barra de Búsqueda --}}
                                    <div class="col-md-5">
                                        <input name="texto" type="text" class="form-control form-control-sm"
                                            value="{{ $texto ?? '' }}" placeholder="Buscar por Usuario/Agente/Cliente">
                                    </div>

                                    {{-- Botón Buscar --}}
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-secondary btn-sm w-100">
                                            <i class="fas fa-search me-1"></i> Filtrar / Buscar
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Botón Exportar --}}
                            @role('admin')
                            <div class="text-end mb-3">
                                <a href="{{ route('dashboard.exportar.pedidos', [
                                            'month' => request('month'),
                                            'year' => request('year'),
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
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Agente Creador</th>
                                        <th style="width: 80px">Total</th>
                                        <th style="width: 80px">Estado</th>
                                        {{-- COLUMNA NUEVA PARA GUÍAS --}}
                                        <th style="width: 220px">Guía de Envío</th> 
                                        <th style="width: 100px">Flete Pagado</th>
                                        <th style="width: 80px">Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($registros)<=0)
                                        <tr><td colspan="10">No hay registros que coincidan.</td></tr> {{-- Ajustado colspan --}}
                                    @else
                                        @foreach($registros as $reg)
                                        <tr class="align-middle">
                                            <td> {{-- Celda Opciones --}}
                                                @php
                                                $esAgenteYPuedeCancelar = auth()->user()->hasRole('agente-ventas') && $reg->estado == 'pendiente';
                                                
                                                $esAdminYPuedeActuar = auth()->user()->can('pedido-anulate') && 
                                                    in_array($reg->estado, ['pendiente', 'parcialmente_surtido', 'enviado', 'enviado_completo']);
                                                @endphp

                                                @if( $esAgenteYPuedeCancelar || $esAdminYPuedeActuar )
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#modal-estado-{{$reg->id}}"><i
                                                        class="bi bi-arrow-repeat"></i>
                                                </button>
                                                @endif
                                            </td>
                                            <td>{{$reg->id}}</td>
                                            <td>{{$reg->created_at->format('d/m/Y')}}</td>
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
                                                $colores = [
                                                    'pendiente' => 'bg-warning',
                                                    'parcialmente_surtido' => 'bg-info text-dark',
                                                    'enviado' => 'bg-success',
                                                    'enviado_completo' => 'bg-success',
                                                    'anulado' => 'bg-danger',
                                                    'cancelado' => 'bg-secondary',
                                                    'entregado' => 'bg-primary',
                                                    'cotizacion' => 'bg-dark',
                                                ];
                                                @endphp
                                                <span class="badge {{ $colores[$reg->estado] ?? 'bg-dark' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $reg->estado)) }}
                                                </span>
                                            </td>
                                            
                                            {{-- ============================================= --}}
                                            {{-- ||     CELDA NUEVA: INPUTS DE GUÍAS        || --}}
                                            {{-- ============================================= --}}
                                            <td>
                                                @php
                                                    $estado = strtolower($reg->estado);
                                                    // Determinar qué input mostrar según el estado (con guiones bajos)
                                                    $showParcial = in_array($estado, ['parcialmente_surtido', 'enviado_completo', 'entregado', 'finalizado']);
                                                    $showCompleta = in_array($estado, ['enviado_completo', 'entregado', 'finalizado']);
                                                    // ¿Es admin? (Solo admin edita guías)
                                                    $isAdmin = auth()->user()->hasRole('admin');
                                                @endphp

                                                @if($isAdmin)
                                                    @if($showParcial)
                                                        <div class="input-group input-group-sm mb-1">
                                                            <span class="input-group-text bg-light" title="Guía Parcial">
                                                                <i class="fas fa-shipping-fast text-secondary"></i>
                                                            </span>
                                                            <input type="text" class="form-control tracking-input"
                                                                data-pedido-id="{{ $reg->id }}"
                                                                data-tipo="guia_parcial"
                                                                value="{{ $reg->guia_parcial }}" 
                                                                placeholder="Guía Parcial...">
                                                            <button class="btn btn-outline-secondary btn-save-tracking" type="button">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if($showCompleta)
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-light" title="Guía Completa">
                                                                <i class="fas fa-truck text-success"></i>
                                                            </span>
                                                            <input type="text" class="form-control tracking-input"
                                                                data-pedido-id="{{ $reg->id }}"
                                                                data-tipo="guia_completa"
                                                                value="{{ $reg->guia_completa }}"
                                                                placeholder="Guía Completa...">
                                                            <button class="btn btn-outline-success btn-save-tracking" type="button">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if(!$showParcial && !$showCompleta)
                                                        <small class="text-muted">No aplica</small>
                                                    @endif
                                                @else
                                                    {{-- SI NO ES ADMIN, SOLO MUESTRA EL TEXTO --}}
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
                                            <td>
                                                <button class="btn btn-sm btn-primary" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#detalles-{{ $reg->id }}">
                                                    Ver detalles
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="detalles-{{ $reg->id }}">
                                            <td colspan="10"> {{-- Ajustado colspan --}}
                                                <div class="p-3 bg-light border-bottom"> {{-- Ajuste visual padding/bg --}}
                                                    
                                                    {{-- =========================================== --}}
                                                    {{-- || ENCABEZADO DETALLES CON BOTÓN PDF     || --}}
                                                    {{-- =========================================== --}}
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0 fw-bold text-primary">Detalles del Pedido #{{ $reg->id }}</h6>
                                                        
                                                        {{-- BOTÓN PDF PEDIDO (Nuevo) --}}
                                                        <a href="{{ route('pedidos.pdf', $reg->id) }}" target="_blank" class="btn btn-danger btn-sm shadow-sm">
                                                            <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                                                        </a>
                                                    </div>
                                                    
                                                    {{-- ============================================= --}}
                                                    {{-- ||     INFORMACIÓN DE RASTREO (DETALLES)   || --}}
                                                    {{-- ============================================= --}}
                                                    @if($reg->guia_parcial || $reg->guia_completa)
                                                        <div class="alert alert-white border mb-3 py-2 shadow-sm">
                                                            <h6 class="text-info fw-bold mb-2 small text-uppercase"><i class="fas fa-shipping-fast me-2"></i>Información de Envío:</h6>
                                                            @if($reg->guia_parcial)
                                                                <div class="mb-1">
                                                                    <strong class="text-muted">Guía Parcial:</strong> 
                                                                    <span class="font-monospace ms-2 bg-light px-2 rounded">{{ $reg->guia_parcial }}</span>
                                                                </div>
                                                            @endif
                                                            @if($reg->guia_completa)
                                                                <div>
                                                                    <strong class="text-success">Guía Completa:</strong> 
                                                                    <span class="font-monospace ms-2 fw-bold bg-success text-white px-2 rounded">{{ $reg->guia_completa }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    {{-- COMENTARIOS (Usando la versión limpia para no mostrar códigos) --}}
                                                    @if($reg->comentarios_limpios)
                                                        <div class="alert alert-warning py-2 mb-3 small">
                                                            <i class="fas fa-comment-alt me-1"></i> <strong>Comentarios:</strong> {{ $reg->comentarios_limpios }}
                                                        </div>
                                                    @endif

                                                    <table class="table table-sm table-striped mb-0 bg-white shadow-sm">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 30%">Producto</th>
                                                                <th style="width: 10%">Imagen</th>
                                                                <th style="width: 10%">Cantidad</th>
                                                                <th style="width: 10%">Precio Unitario</th>
                                                                <th style="width: 10%">Inner</th>
                                                                <th style="width: 10%">Subtotal Línea</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($reg->detalles as $detalle)
                                                            <tr>
                                                                <td>{{ $detalle->producto->nombre ?? 'Producto no encontrado' }}</td>
                                                                <td>
                                                                    @if($detalle->producto && $detalle->producto->imagen)
                                                                        <img src="{{ asset('uploads/productos/' . $detalle->producto->imagen ) }}"
                                                                            class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                                    @else
                                                                        <span class="text-muted small">Sin img</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $detalle->cantidad}}</td>
                                                                <td>${{ number_format($detalle->precio, 2) }}</td>
                                                                <td>{{ $detalle->inner ?? 'N/A'}}</td>
                                                                <td>${{ number_format($detalle->subtotal ?? ($detalle->cantidad * $detalle->precio), 2) }}</td>
                                                            </tr>
                                                            @empty
                                                            <tr><td colspan="6" class="text-center">No hay detalles.</td></tr>
                                                            @endforelse
                                                            <tr>
                                                                <td colspan="6" class="text-end pt-3 bg-white">
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
                        {{$registros->appends(['texto' => $texto ?? '', 'month' => request('month'), 'year' => request('year')])->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================= --}}
{{-- ||             SCRIPT AJAX PARA GUÍAS      || --}}
{{-- ============================================= --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Detectar clic en botón guardar
        $('.btn-save-tracking').click(function() {
            var btn = $(this);
            var input = btn.prev('.tracking-input'); // El input hermano anterior
            var pedidoId = input.data('pedido-id');
            var tipoGuia = input.data('tipo');
            var valorGuia = input.val();
            var iconOriginal = btn.html();

            // Deshabilitar botón y mostrar spinner
            btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

            $.ajax({
                url: '/pedidos/' + pedidoId + '/update-guia', // Ruta definida en web.php
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}', // Token de seguridad Laravel
                    tipo: tipoGuia,
                    valor: valorGuia
                },
                success: function(response) {
                    if(response.success) {
                        // Éxito: Mostrar palomita verde
                        btn.html('<i class="fas fa-check"></i>')
                           .removeClass('btn-outline-secondary btn-outline-success')
                           .addClass('btn-success');
                        
                        // Volver a estado normal después de 2 segundos
                        setTimeout(function(){
                            var claseOriginal = (tipoGuia == 'guia_completa') ? 'btn-outline-success' : 'btn-outline-secondary';
                            btn.html('<i class="fas fa-save"></i>')
                               .prop('disabled', false)
                               .removeClass('btn-success')
                               .addClass(claseOriginal);
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
</script>
@endsection