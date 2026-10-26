@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Dashboard Operativo</h1>
                <p class="text-muted small mb-0">Resumen general de tu actividad y acceso rápido a módulos clave.</p>
            </div>
            <a href="{{ route('perfil.pedidos') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-list fa-sm text-white-50 mr-1"></i> Ver todos los pedidos
            </a>
        </div>

        {{-- ================================================================ --}}
        {{-- ||       TARJETAS DE ACCESO RÁPIDO (KPIs REDISEÑADOS)         || --}}
        {{-- ================================================================ --}}
        <div class="row">
            @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
            {{-- --- VISTA ADMIN --- --}}
            <div class="col-xl-3 col-md-6 mb-4"><div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #1abc9c !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Ventas Totales</div><div class="h4 mb-0 font-weight-bold text-gray-800">${{ number_format($totalVentas, 2) }}</div></div><div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #27ae60 !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Pedidos Totales</div><div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalPedidos }}</div></div><div class="col-auto"><i class="fas fa-shopping-cart fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><a href="{{ route('cotizaciones.index') }}" class="text-decoration-none"><div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #f6c23e !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Cotizaciones Activas</div><div class="h4 mb-0 font-weight-bold text-gray-800">${{ number_format($totalCotizacionesMonto, 2) }}</div></div><div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-warning" style="opacity: 0.8;"></i></div></div></div></div></a></div>
            <div class="col-xl-3 col-md-6 mb-4"><a href="{{ route('devoluciones.index') }}" class="text-decoration-none"><div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #e74a3b !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Devoluciones (Pendientes)</div><div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalDevoluciones }}</div></div><div class="col-auto"><i class="fas fa-undo fa-2x text-danger" style="opacity: 0.8;"></i></div></div></div></div></a></div>
            @elseif(auth()->user()->hasRole('agente-ventas'))
            {{-- --- VISTA AGENTE --- --}}
            <div class="col-xl-3 col-md-6 mb-4"><div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #1abc9c !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Mis Ventas Totales</div><div class="h4 mb-0 font-weight-bold text-gray-800">${{ number_format($misVentas, 2) }}</div></div><div class="col-auto"><i class="fas fa-award fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #27ae60 !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Pedidos Realizados</div><div class="h4 mb-0 font-weight-bold text-gray-800">{{ $misPedidos }}</div></div><div class="col-auto"><i class="fas fa-shopping-basket fa-2x text-gray-300"></i></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><a href="{{ route('cotizaciones.index') }}" class="text-decoration-none"><div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #f6c23e !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Mis Cotizaciones Activas</div><div class="h4 mb-0 font-weight-bold text-gray-800">${{ number_format($misCotizacionesMonto, 2) }}</div></div><div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-warning" style="opacity: 0.8;"></i></div></div></div></div></a></div>
            <div class="col-xl-3 col-md-6 mb-4"><a href="{{ route('devoluciones.index') }}" class="text-decoration-none"><div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #e74a3b !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Mis Devoluciones</div><div class="h4 mb-0 font-weight-bold text-gray-800">{{ $misDevoluciones }}</div></div><div class="col-auto"><i class="fas fa-undo fa-2x text-danger" style="opacity: 0.8;"></i></div></div></div></div></a></div>
            @endif
        </div>

        {{-- TABLA DE ÚLTIMOS PEDIDOS --}}
        <div class="row mt-2 mb-5">
            <div class="col-12">
                <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-clock mr-2"></i>Pedidos Recientes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="bg-light text-gray-700">
                                    <tr>
                                        <th class="border-0 pl-4">ID</th>
                                        <th class="border-0">Cliente</th>
                                        @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
                                        <th class="border-0">Agente</th>
                                        @endif
                                        <th class="border-0">Fecha</th>
                                        <th class="border-0 text-center">Estado</th>
                                        <th class="border-0 text-right pr-4">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ultimosPedidos as $pedido)
                                    <tr>
                                        <td class="pl-4 font-weight-bold text-gray-600">#{{ $pedido->id }}</td>
                                        <td><div class="font-weight-bold text-dark">{{ $pedido->cliente->nombre ?? 'N/A' }}</div><span class="badge badge-light border text-secondary">{{ $pedido->cliente->codigo ?? 'N/A' }}</span></td>
                                        @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
                                        <td><div class="small text-gray-800"><i class="fas fa-user-circle text-gray-400 mr-1"></i> {{ $pedido->agente->name ?? 'N/A' }}</div></td>
                                        @endif
                                        <td><div class="small text-gray-600"><i class="far fa-calendar-alt mr-1"></i>{{ $pedido->created_at->format('d/m/Y') }}</div></td>
                                        <td class="text-center">
                                            @php $estado = strtolower($pedido->estado); @endphp
                                            @if($estado == 'pendiente') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #f6c23e !important; color: #333 !important;">Pendiente</span>
                                            @elseif($estado == 'enviado') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #36b9cc !important; color: #fff !important;">Enviado</span>
                                            @elseif(in_array($estado, ['completado', 'entregado', 'finalizado', 'envio completado'])) <span class="badge badge-pill bg-success text-white px-3 py-1 shadow-sm">Completado</span>
                                            @else <span class="badge badge-pill bg-dark text-white px-3 py-1 shadow-sm">{{ ucfirst($pedido->estado) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right pr-4 font-weight-bold text-success">${{ number_format($pedido->total, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="{{ auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']) ? '6' : '5' }}" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-3 text-gray-300 d-block"></i> No hay pedidos recientes.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection