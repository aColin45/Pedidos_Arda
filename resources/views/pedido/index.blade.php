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
                        {{-- =============================================== --}}
                        {{-- ||       INICIA FORMULARIO DE FILTROS        || --}}
                        {{-- =============================================== --}}
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
                                            @foreach(range($currentYear, $currentYear - 5) as $y) {{-- Últimos 6 años --}}
                                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Barra de Búsqueda (Texto) --}}
                                    <div class="col-md-5">
                                        <input name="texto" type="text" class="form-control form-control-sm"
                                            value="{{ $texto ?? '' }}" placeholder="Buscar por Usuario/Agente/Cliente">
                                    </div>

                                    {{-- Botón Buscar/Filtrar --}}
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-secondary btn-sm w-100">
                                            <i class="fas fa-search me-1"></i> Filtrar / Buscar
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Botón Exportar (Solo Admin) --}}
                            @role('admin')
                            <div class="text-end mb-3">
                                {{-- Pasamos los filtros actuales a la ruta de exportación --}}
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
                        {{-- =============================================== --}}
                        {{-- ||       TERMINA FORMULARIO DE FILTROS       || --}}
                        {{-- =============================================== --}}


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
                                        <th style="width: 100px">Flete Pagado</th>
                                        <th style="width: 80px">Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($registros)<=0)
                                        @role('admin')
                                            {{-- Admin ahora tiene 9 columnas --}}
                                            <tr><td colspan="9">No hay registros que coincidan con la búsqueda/filtros.</td></tr>
                                        @else
                                            {{-- Agente ahora tiene 8 columnas (sin Opciones) --}}
                                            <tr><td colspan="8">No hay registros que coincidan con la búsqueda/filtros.</td></tr>
                                        @endrole
                                    @else
                                        @foreach($registros as $reg)
                                        <tr class="align-middle">
                                            <td> {{-- Celda Opciones --}}
                                                @php
                                                $esAgenteYPuedeCancelar = auth()->user()->hasRole('agente-ventas') && $reg->estado == 'pendiente';
                                                $esAdminYPuedeActuar = auth()->user()->can('pedido-anulate') && in_array($reg->estado, ['pendiente', 'enviado']);
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
                                                    'enviado' => 'bg-success',
                                                    'anulado' => 'bg-danger',
                                                    'cancelado' => 'bg-secondary',
                                                    'entregado' => 'bg-primary',
                                                    'cotizacion' => 'bg-info', // Añadido por si acaso
                                                ];
                                                @endphp
                                                <span class="badge {{ $colores[$reg->estado] ?? 'bg-dark' }}">
                                                    {{ ucfirst($reg->estado) }}
                                                </span>
                                            </td>
                                            <td> {{-- Celda Flete Pagado --}}
                                                @if($reg->flete_pagado)
                                                <span class="badge bg-success">Sí</span>
                                                @else
                                                <span class="badge bg-danger">No</span>
                                                @endif
                                            </td>
                                            <td> {{-- Celda Detalles --}}
                                                <button class="btn btn-sm btn-primary" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#detalles-{{ $reg->id }}">
                                                    Ver detalles
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="detalles-{{ $reg->id }}">
                                             {{-- Colspan ajustado --}}
                                            <td colspan="9">
                                                <div class="p-2"> {{-- Añadir padding para que no se pegue al borde --}}
                                                    <h6 class="mb-2">Detalles del Pedido #{{ $reg->id }}</h6>
                                                    {{-- Mostrar comentarios si existen --}}
                                                    @if($reg->comentarios)
                                                        <p class="mb-2 fst-italic"><strong>Comentarios:</strong> {{ $reg->comentarios }}</p>
                                                    @endif
                                                    <table class="table table-sm table-striped mb-0"> {{-- Quitar margen inferior --}}
                                                        <thead>
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
                                                                            class="img-fluid rounded"
                                                                            style="width: 50px; height: 50px; object-fit: cover;" {{-- Tamaño reducido --}}
                                                                            alt="{{ $detalle->producto->nombre}}">
                                                                    @else
                                                                        <span class="text-muted">N/A</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $detalle->cantidad}}</td>
                                                                <td>${{ number_format($detalle->precio, 2) }}</td>
                                                                <td>{{ $detalle->inner ?? 'N/A'}}</td>
                                                                <td>${{ number_format($detalle->subtotal ?? ($detalle->cantidad * $detalle->precio), 2) }}</td>
                                                            </tr>
                                                            @empty
                                                            <tr><td colspan="6" class="text-center">No se encontraron detalles para este pedido.</td></tr>
                                                            @endforelse
                                                            {{-- Fila Resumen Totales --}}
                                                            <tr>
                                                                <td colspan="6" class="text-end pt-3">
                                                                    <small class="text-muted d-block">Subtotal Bruto: ${{ number_format($reg->subtotal ?? 0, 2) }}</small>
                                                                    <strong class="text-danger d-block">Descuento ({{ number_format(($reg->descuento_aplicado / ($reg->subtotal ?: 1)) * 100, 2) ?? '0.00'}}%): -${{ number_format($reg->descuento_aplicado ?? 0, 2) }}</strong>
                                                                    <small class="text-muted d-block">IVA (16%): +${{ number_format($reg->iva ?? 0, 2) }}</small>
                                                                    <strong class="d-block">TOTAL: ${{ number_format($reg->total, 2) }}</strong>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                        {{-- Incluir el modal de estado DENTRO del loop --}}
                                        @include('pedido.state')
                                        @endforeach
                                    @endif {{-- Fin del @if(count...) / @else --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                         {{-- Paginación incluye filtros --}}
                        {{$registros->appends(['texto' => $texto ?? '', 'month' => request('month'), 'year' => request('year')])->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection