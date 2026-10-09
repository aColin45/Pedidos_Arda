@extends('plantilla.app')

@section('contenido')
<div class="container-fluid">
    {{-- Título y Botón Principal --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 border-left-primary pl-3">Gestión de Devoluciones</h1>
        <a href="{{ route('devoluciones.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle fa-sm text-white-50"></i> Nuevo Formato
        </a>
    </div>

    {{-- Tarjeta Principal --}}
    <div class="card shadow mb-4 border-bottom-primary">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Solicitudes</h6>
        </div>
        <div class="card-body">
            
            {{-- Buscador Estilizado --}}
            <form action="{{ route('devoluciones.index') }}" method="GET" class="mb-4">
                <div class="input-group shadow-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-primary text-white border-primary"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" name="texto" class="form-control border-primary" placeholder="Buscar por Folio, Cliente, Agente o Código..." value="{{ request('texto') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary border-primary">Buscar</button>
                        {{-- Botón para limpiar la búsqueda --}}
                        @if(request('texto'))
                            <a href="{{ route('devoluciones.index') }}" class="btn btn-danger" title="Limpiar búsqueda">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th class="text-center" width="5%">#</th>
                            <th width="10%">Fecha</th>
                            <th width="25%">Cliente</th>
                            <th width="15%">Factura a la que pertenece</th>
                            <th width="20%">Motivo</th>
                            <th class="text-right" width="10%">Total</th>
                            <th class="text-center" width="10%">Estado</th>
                            <th class="text-center" width="5%">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $dev)
                        <tr>
                            <td class="text-center font-weight-bold text-secondary">{{ $dev->id }}</td>
                            <td>
                                <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                                {{ $dev->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="font-weight-bold text-primary">{{ $dev->cliente->nombre ?? 'N/A' }}</div>
                                <div class="small text-gray-500">{{ $dev->cliente->agente->name ?? 'Sin Agente' }}</div>
                            </td>
                            <td>
                                {{-- Forzamos fondo blanco y texto oscuro para que sea legible --}}
                                <span class="badge border shadow-sm" style="background-color: #ffffff !important; color: #4e73df !important; font-size: 0.9em;">
                                    {{ $dev->factura_extraida }}
                                </span>
                            </td>
                            <td><small>{{ Str::limit($dev->motivo_extraido, 25) }}</small></td>
                            <td class="text-right font-weight-bold">${{ number_format($dev->total, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $estilo = '';
                                    $texto = '';
                                    
                                    switch($dev->estado) {
                                        case 'devolucion_pendiente': 
                                            $estilo = 'background-color: #f6c23e !important; color: #333 !important;'; 
                                            $texto = 'Pendiente'; break;
                                        case 'devolucion_aprobada': 
                                            $estilo = 'background-color: #36b9cc !important; color: #fff !important;'; 
                                            $texto = 'En Proceso'; break;
                                        case 'devolucion_finalizada': 
                                            $estilo = 'background-color: #1cc88a !important; color: #fff !important;'; 
                                            $texto = 'Finalizada'; break;
                                        case 'devolucion_rechazada': 
                                            $estilo = 'background-color: #e74a3b !important; color: #fff !important;'; 
                                            $texto = 'Rechazada'; break;
                                        case 'devolucion_cancelada': 
                                            $estilo = 'background-color: #858796 !important; color: #fff !important;'; 
                                            $texto = 'Cancelada'; break;
                                        default:
                                            $estilo = 'background-color: #858796 !important; color: #fff !important;';
                                            $texto = $dev->estado;
                                    }
                                @endphp
                                <span class="badge badge-pill px-3 py-2 shadow-sm" style="{{ $estilo }} font-size: 0.85em; opacity: 1;">
                                    {{ $texto }}
                                </span>

                                {{-- ======================================================== --}}
                                {{-- || NUEVO: HUELLA DE RASTREO (Tabla de devoluciones)   || --}}
                                {{-- ======================================================== --}}
                                @if($dev->validador_id && $dev->estado != 'devolucion_pendiente')
                                    @php
                                        $accionTexto = 'Actualizó';
                                        if ($dev->estado == 'devolucion_aprobada') $accionTexto = 'Aprobó';
                                        elseif ($dev->estado == 'devolucion_rechazada') $accionTexto = 'Rechazó';
                                        elseif ($dev->estado == 'devolucion_finalizada') $accionTexto = 'Finalizó';
                                        elseif ($dev->estado == 'devolucion_cancelada') $accionTexto = 'Canceló';
                                    @endphp
                                    <div class="mt-2 text-left bg-light p-1 border rounded" style="font-size: 0.70rem; line-height: 1.2;">
                                        <span class="text-muted d-block text-truncate">
                                            <i class="fas fa-user-check"></i> {{ $accionTexto }}: <strong>{{ $dev->validador->name ?? 'Admin' }}</strong>
                                        </span>
                                        <span class="text-muted d-block mt-1">
                                            <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($dev->fecha_validacion)->format('d/m/y H:i') }}
                                        </span>
                                        @if($dev->estado == 'devolucion_rechazada' && $dev->motivo_rechazo)
                                            <div class="text-danger mt-1 font-weight-bold" style="white-space: pre-wrap;">
                                                Motivo: {{ $dev->motivo_rechazo }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('devoluciones.show', $dev->id) }}" class="btn btn-sm btn-circle btn-info shadow-sm" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-gray-500">
                                <i class="fas fa-inbox fa-3x mb-3 text-gray-300"></i><br>
                                No se encontraron devoluciones.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                {{-- withQueryString() limpia y mantiene los filtros de la URL correctamente --}}
                {{ $registros->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection