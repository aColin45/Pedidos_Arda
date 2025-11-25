@extends('plantilla.app') {{-- O 'web.app' si es el caso --}}

@section('contenido')
<div class="app-content">
    <div class="container-fluid">

        <h1 class="mb-4">Dashboard</h1>

        {{-- ================================================================ --}}
        {{-- ||       TARJETAS DE ESTADÍSTICAS (KPIs)                      || --}}
        {{-- ================================================================ --}}

        @if(auth()->user()->hasRole('admin'))
        {{-- ---------------- VISTA DE ADMIN ---------------- --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>${{ number_format($totalVentas, 2) }}</h3>
                        <p>Ventas Totales</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $totalPedidos }}</h3>
                        <p>Pedidos Totales</p>
                    </div>
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalAgentes }}</h3>
                        <p>Agentes Activos</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $totalClientes }}</h3>
                        <p>Clientes Registrados</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-plus"></i></div>
                </div>
            </div>
        </div>

        @elseif(auth()->user()->hasRole('agente-ventas'))
        {{-- ---------------- VISTA DE AGENTE ---------------- --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>${{ number_format($misVentas, 2) }}</h3>
                        <p>Mis Ventas</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $misPedidos }}</h3>
                        <p>Mis Pedidos Realizados</p>
                    </div>
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $misPedidosPendientes }}</h3>
                        <p>Pedidos Pendientes</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $misClientes }}</h3>
                        <p>Mis Clientes Asignados</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
        @endif

        {{-- ============================================= --}}
        {{-- ||       BOTÓN DE EXPORTAR (SOLO ADMIN)    || --}}
        {{-- ============================================= --}}
        <!-- @role('admin')
        <div class="mb-3 text-end"> {{-- Alineado a la derecha --}}
            <a href="{{ route('dashboard.exportar.pedidos') }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Exportar Todos los Pedidos a Excel
            </a>
        </div>
        @endrole -->


        {{-- ================================================================ --}}
        {{-- ||       TABLA DE ÚLTIMOS PEDIDOS (Común para ambos)          || --}}
        {{-- ================================================================ --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Últimos 5 Pedidos</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Código Cliente</th>
                                    @if(auth()->user()->hasRole('admin'))
                                    <th>Agente</th>
                                    @endif
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimosPedidos as $pedido)
                                <tr>
                                    <td>{{ $pedido->id }}</td>
                                    <td>{{ $pedido->cliente->nombre ?? 'N/A' }}</td>
                                    {{-- MOSTRAR CÓDIGO DEL CLIENTE --}}
                                    <td>{{ $pedido->cliente->codigo ?? 'N/A' }}</td>
                                    @if(auth()->user()->hasRole('admin'))
                                    <td>{{ $pedido->agente->name ?? 'N/A' }}</td>
                                    @endif
                                    <td>{{ $pedido->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        {{-- Código de los badges de estado --}}
                                        @if($pedido->estado == 'pendiente') <span
                                            class="badge bg-warning">Pendiente</span>
                                        @elseif($pedido->estado == 'enviado') <span
                                            class="badge bg-success">Enviado</span>
                                        @elseif($pedido->estado == 'cancelado') <span
                                            class="badge bg-secondary">Cancelado</span>
                                        @else <span class="badge bg-danger">Anulado</span>
                                        @endif
                                    </td>
                                    <td>${{ number_format($pedido->total, 2) }}</td>
                                </tr>
                                @empty
                                {{-- Ajustar colspan (+1) --}}
                                @if(auth()->user()->hasRole('admin'))
                                <tr>
                                    <td colspan="7" class="text-center">No hay pedidos recientes.</td>
                                </tr> {{-- Admin: 7 columnas --}}
                                @else
                                <tr>
                                    <td colspan="6" class="text-center">No hay pedidos recientes.</td>
                                </tr> {{-- Agente: 6 columnas --}}
                                @endif
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection