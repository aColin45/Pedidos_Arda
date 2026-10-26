@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold"><i class="fas fa-chart-line text-primary mr-2"></i> Rendimiento y Metas</h1>
                <p class="text-muted small mb-0">Sala de análisis comercial: Evaluación de desempeño, objetivos y crecimiento.</p>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- ||       SALA DE GUERRA DE METAS (WAR ROOM)                            || --}}
        {{-- ========================================================================= --}}
        
        {{-- VISTA AGENTE: Mi progreso Estelar y Competencia --}}
        @if(auth()->user()->hasRole('agente-ventas'))
            @if(isset($miMetaMesActual) && $miMetaMesActual > 0)
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card shadow border-0 h-100" style="border-radius: 12px; border-left: 6px solid {{ $colorMeta ?? '#3498db' }};">
                        <div class="card-body py-4">
                            <h4 class="font-weight-bold text-dark mb-1">
                                <i class="fas fa-rocket mr-2" style="color: {{ $colorMeta ?? '#3498db' }};"></i> 
                                Mi Meta de Ventas ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})
                            </h4>
                            <p class="text-muted small mb-3">Progreso de tu cuota mensual. ¡Apunta a las estrellas!</p>
                            
                            <div class="progress shadow-sm mb-3" style="height: 35px; border-radius: 17px; background-color: #e9ecef;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated font-weight-bold" 
                                     role="progressbar" 
                                     style="width: {{ $miPorcentajeMeta > 100 ? 100 : $miPorcentajeMeta }}%; background-color: {{ $colorMeta ?? '#3498db' }}; font-size: 1.2rem;" 
                                     aria-valuenow="{{ $miPorcentajeMeta }}">
                                    {{ number_format($miPorcentajeMeta, 1) }}%
                                </div>
                            </div>
                            
                            <div class="row text-center mt-4">
                                <div class="col-4 border-right">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">  Vendido al momento</div>
                                    <div class="h4 font-weight-bold text-dark">${{ number_format($misVentasEsteMes, 2) }}</div>
                                </div>
                                <div class="col-4 border-right">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted"> Meta a lograr</div>
                                    <div class="h4 font-weight-bold" style="color: {{ $colorMeta ?? '#3498db' }};">${{ number_format($miMetaMesActual, 2) }}</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">Estado</div>
                                    @if($miPorcentajeMeta >= 100)
                                        <div class="text-success font-weight-bold"><i class="fas fa-trophy mr-1 text-warning"></i> Superávit: +${{ number_format($misVentasEsteMes - $miMetaMesActual, 2) }}</div>
                                    @else
                                        <div class="text-danger font-weight-bold"><i class="fas fa-fire mr-1"></i> Faltan: ${{ number_format($miFaltanteMeta, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- NUEVA GRÁFICA COMPETITIVA: Ranking de Agentes --}}
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                        <div class="card-header py-3 bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-medal text-warning mr-2"></i> Ranking del Equipo (Mes en Curso)</h6>
                            @if(isset($miLugarRanking) && $miLugarRanking > 0)
                                <span class="badge bg-warning text-dark shadow-sm px-2 py-1"><i class="fas fa-star mr-1"></i> Lugar #{{ $miLugarRanking }}</span>
                            @endif
                        </div>
                        <div class="card-body px-3 pb-3 pt-0">
                            <p class="small text-muted mb-2 text-center"> Tu porcentaje de ventas vs el resto del equipo.</p>
                            {{-- Cambiamos chart-pie a chart-bar para que se adapte mejor --}}
                            <div class="chart-bar" style="position: relative; height: 230px;">
                                <canvas id="chartRankingEquipo"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endif

        {{-- VISTA ADMIN: Radar Global --}}
        @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']) && isset($rendimientoAgentes))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow border-0 bg-dark text-white" style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center text-md-left mb-3 mb-md-0 border-right border-secondary">
                                <h5 class="font-weight-bold mb-1 text-warning"><i class="fas fa-globe-americas mr-2"></i> Meta Global ARDA</h5>
                                <h2 class="mb-0 font-weight-bold">${{ number_format($ventasGlobalesMesActual, 2) }}</h2>
                                <span class="h6 text-gray-400">Objetivo Mensual: ${{ number_format($metaGlobalEmpresa, 2) }}</span>
                            </div>
                            <div class="col-md-8 pl-md-4">
                                @php $colorGlobal = $porcentajeGlobalEmpresa >= 100 ? 'bg-success' : ($porcentajeGlobalEmpresa >= 75 ? 'bg-warning' : 'bg-danger'); @endphp
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span class="text-uppercase tracking-wide"> Rendimiento Corporativo</span>
                                    <span class="h5 mb-0">{{ number_format($porcentajeGlobalEmpresa, 1) }}%</span>
                                </div>
                                <div class="progress mb-2 shadow" style="height: 35px; border-radius: 17px; background-color: rgba(255,255,255,0.1);">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $colorGlobal }} font-weight-bold" 
                                         role="progressbar" style="width: {{ $porcentajeGlobalEmpresa > 100 ? 100 : $porcentajeGlobalEmpresa }}%; font-size: 1.2rem;">
                                        {{ number_format($porcentajeGlobalEmpresa, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slider de Agentes --}}
            <div class="col-12 mt-4">
                <h6 class="font-weight-bold text-gray-700 mb-3"><i class="fas fa-users mr-2"></i> Rendimiento Individual (Fuerza de Ventas)</h6>
                <div class="d-flex flex-nowrap pb-3" style="overflow-x: auto; gap: 15px; scroll-behavior: smooth;">
                    @foreach($rendimientoAgentes as $ag)
                        @php
                            $colorAgente = $ag['porcentaje'] >= 100 ? '#1cc88a' : ($ag['porcentaje'] >= 75 ? '#f6c23e' : '#e74a3b');
                            $iconoAgente = $ag['porcentaje'] >= 100 ? 'fa-trophy text-warning' : ($ag['porcentaje'] >= 75 ? 'fa-fire text-warning' : 'fa-exclamation-circle text-danger');
                        @endphp
                        <div class="card shadow-sm border-0 flex-shrink-0" style="width: 320px; border-radius: 10px; border-bottom: 5px solid {{ $colorAgente }};">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold text-dark mb-0 text-truncate" style="max-width: 85%;"><i class="fas fa-user-tie text-gray-400 mr-2"></i> {{ $ag['nombre'] }}</h6>
                                    <i class="fas {{ $iconoAgente }} fa-lg"></i>
                                </div>
                                <div class="progress mt-3 mb-2" style="height: 12px; border-radius: 6px; background-color: #eaecf4;">
                                    <div class="progress-bar progress-bar-striped" role="progressbar" style="width: {{ $ag['porcentaje'] > 100 ? 100 : $ag['porcentaje'] }}%; background-color: {{ $colorAgente }};"></div>
                                </div>
                                <div class="d-flex justify-content-between font-weight-bold mb-3" style="font-size: 0.8rem; color: {{ $colorAgente }};">
                                    <span>Progreso</span><span>{{ number_format($ag['porcentaje'], 1) }}%</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-end text-sm border-top pt-2">
                                    <div><small class="d-block text-muted">Vendido</small><span class="text-dark font-weight-bold fs-6">${{ number_format($ag['ventas'], 0) }}</span></div>
                                    <div class="text-right"><small class="d-block text-muted">Meta Real</small><span class="text-muted font-weight-bold">${{ number_format($ag['meta'], 0) }}</span></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- ||       GRÁFICOS DE ANÁLISIS HISTÓRICO                                || --}}
        {{-- ========================================================================= --}}
        <div class="row">
            @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
            {{-- ADMIN: Tendencia e Impacto --}}
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-0 d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-chart-area text-info mr-2"></i> Evolución Histórica de Ventas (Ingresos)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="position: relative; height: 350px;">
                            <canvas id="chartAdminVentasGlobales"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-0 d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-star text-success mr-2"></i> Top 10 Agentes Históricos</h6>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTodosAgentes"><i class="fas fa-expand-arrows-alt mr-1"></i> Ver Transparencia</button>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar" style="position: relative; height: 350px;">
                            <canvas id="chartAdminTopAgentes"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ADMIN: Tabla Comparativa Mensual + Promedios --}}
            @if(isset($ventasMensualesAdmin))
            <div class="col-12 mb-5">
                <div class="card shadow border-0" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <div class="mb-2 mb-md-0">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table mr-2"></i> Análisis de Consistencia Mensual</h6>
                            <span class="small text-muted">Compara las ventas mes a mes de cada agente y descubre su promedio histórico.</span>
                        </div>
                        
                        {{-- Simbología Explicativa --}}
                        <div class="d-flex align-items-center bg-light px-3 py-2 rounded border shadow-sm">
                            <span class="small font-weight-bold text-muted mr-3">Leyenda de Cumplimiento: </span>
                            <span class="badge bg-success text-white px-2 py-1 mr-2 shadow-sm"><i class="fas fa-arrow-up mr-1"></i> >= 100%</span>
                            <span class="badge bg-warning text-dark px-2 py-1 mr-2 shadow-sm"><i class="fas fa-minus mr-1"></i> 75% - 99%</span>
                            <span class="badge bg-danger text-white px-2 py-1 shadow-sm"><i class="fas fa-arrow-down mr-1"></i> < 75%</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        {{-- Altura incrementada a 750px para aprovechar el espacio --}}
                        <div class="table-responsive" style="max-height: 750px;">
                            <table class="table table-hover table-sm align-middle mb-0 text-nowrap" id="tablaConsistencia">
                                <thead class="text-white" style="background-color: #34495e;">
                                    <tr>
                                        <th class="border-0 pl-3 sticky-left cursor-pointer" style="position: sticky; left: 0; z-index: 1; border-right: 1px solid #2c3e50; background-color: #f8f9fa; min-width: 220px;" onclick="sortTable(0, 'string')">
                                            Agente de Ventas <i class="fas fa-sort text-gray-400 ml-1"></i>
                                        </th>
                                        <th class="border-0 text-center px-3 cursor-pointer" style="background-color: #1abc9c; min-width: 140px;" onclick="sortTable(1, 'number')">
                                            <i class="fas fa-chart-line mr-1"></i> Promedio Mensual <i class="fas fa-sort text-white ml-1"></i>
                                        </th>
                                        @foreach($columnasMeses as $codigo => $nombreMes)
                                            <th class="border-0 text-center px-3" style="min-width: 130px;">{{ $nombreMes }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody style="font-size: 0.85rem;">
                                    @forelse($ventasMensualesAdmin as $agente => $ventas)
                                        @php
                                            $metaBaseAgente = \App\Models\User::where('name', $agente)->value('meta_mensual_base') ?? 0;
                                            $sumaTotal = 0; $mesesConVenta = 0;
                                            foreach($ventas as $v) {
                                                if($v->total_venta > 0) { $sumaTotal += $v->total_venta; $mesesConVenta++; }
                                            }
                                            $promedio = $mesesConVenta > 0 ? ($sumaTotal / $mesesConVenta) : 0;
                                        @endphp
                                        <tr>
                                            {{-- Celda Nombre Agente (CORREGIDA A COLOR CLARO) --}}
                                            <td class="pl-3 py-2 sticky-left shadow-sm" style="position: sticky; left: 0; border-right: 1px solid #e3e6f0; background-color: #f8f9fa;">
                                                <div class="font-weight-bold text-dark mb-0" style="font-size: 0.9rem;">
                                                    <i class="fas fa-user-circle text-primary mr-2"></i> {{ $agente }}
                                                </div>
                                                @if($metaBaseAgente > 0)
                                                    <div class="text-muted mt-1" style="font-size: 0.75rem; padding-left: 24px;">Meta Mensual: ${{ number_format($metaBaseAgente, 0) }}</div>
                                                @endif
                                            </td>
                                            
                                            <td class="text-center px-3 py-2 font-weight-bold" style="background-color: #e8fcf3; color: #1abc9c; border-right: 2px solid #e3e6f0; font-size: 0.9rem;">
                                                ${{ number_format($promedio, 2) }}
                                            </td>

                                            @foreach($columnasMeses as $codigo => $nombreMes)
                                                @php
                                                    $ventaMes = $ventas->firstWhere('mes_codigo', $codigo);
                                                    $monto = $ventaMes ? $ventaMes->total_venta : 0;
                                                    
                                                    $aplicaMeta = ($codigo >= '2026-09');
                                                    $pctMeta = ($metaBaseAgente > 0 && $aplicaMeta) ? ($monto / $metaBaseAgente) * 100 : 0;
                                                    
                                                    $bgCell = '';
                                                    $colorPill = '';
                                                    $iconoFlecha = '';
                                                    if ($monto > 0 && $aplicaMeta && $metaBaseAgente > 0) {
                                                        if ($pctMeta >= 100) { 
                                                            $bgCell = 'background-color: rgba(28, 200, 138, 0.08);'; 
                                                            $colorPill = 'text-success font-weight-bold';
                                                            $iconoFlecha = '<i class="fas fa-arrow-up mr-1"></i>';
                                                        } elseif ($pctMeta >= 75) { 
                                                            $bgCell = 'background-color: rgba(246, 194, 62, 0.08);'; 
                                                            $colorPill = 'text-warning font-weight-bold';
                                                            $iconoFlecha = '<i class="fas fa-minus mr-1"></i>';
                                                        } else { 
                                                            $bgCell = 'background-color: rgba(231, 74, 59, 0.08);'; 
                                                            $colorPill = 'text-danger font-weight-bold';
                                                            $iconoFlecha = '<i class="fas fa-arrow-down mr-1"></i>';
                                                        }
                                                    }
                                                @endphp
                                                <td class="text-center px-3 py-2" style="border-left: 1px dashed #e3e6f0; {{ $bgCell }}">
                                                    @if($monto > 0)
                                                        <div class="font-weight-bold text-dark mb-1" style="font-size: 0.9rem;">${{ number_format($monto, 2) }}</div>
                                                        @if($metaBaseAgente > 0 && $aplicaMeta)
                                                            <div class="{{ $colorPill }}" style="font-size: 0.75rem;">
                                                                {!! $iconoFlecha !!}{{ number_format($pctMeta, 1) }}%
                                                            </div>
                                                        @elseif(!$aplicaMeta)
                                                            <div class="text-muted" style="font-size: 0.7rem;">N/A</div>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No hay datos históricos.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @elseif(auth()->user()->hasRole('agente-ventas'))
            {{-- AGENTE: Sus propias gráficas --}}
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-0 d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-history text-info mr-2"></i>Mi Crecimiento Histórico</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="position: relative; height: 350px;">
                            <canvas id="chartAgenteMiTendencia"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-0 d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-award text-primary mr-2"></i>Mis Mejores Clientes (Mes Actual)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="chart-bar" style="position: relative; height: 350px;">
                            <canvas id="chartAgenteMisTopClientes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL ADMINISTRADOR: TRANSPARENCIA TOTAL --}}
@if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']) && isset($todosLosAgentes))
<div class="modal fade" id="modalTodosAgentes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom-0 pb-1" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800"><i class="fas fa-balance-scale text-primary mr-2"></i>Desglose Financiero de Agentes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="alert alert-info mx-3 mt-2 mb-0 py-2 small shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-info-circle mr-1"></i> <strong>Transparencia Total:</strong> Visualiza el dinero registrado (Bruto), las pérdidas por cancelación/devolución y el monto real (Neto) validado.
                </div>
                <table class="table table-hover align-middle mb-0 mt-3">
                    <thead class="bg-white text-gray-600 sticky-top shadow-sm pb-1" style="font-size: 0.85rem;">
                        <tr>
                            <th class="border-0 pl-4 text-center" width="5%">#</th> 
                            <th class="border-0" width="30%">Agente</th>
                            <th class="border-0 text-center" width="10%">Pedidos</th> 
                            <th class="border-0 text-right text-secondary" width="18%">Venta Bruta</th>
                            <th class="border-0 text-right text-danger" width="18%">Mermas / Devoluciones</th>
                            <th class="border-0 text-right pr-4 text-success font-weight-bold" width="19%">Venta Neta (Real)</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.9rem;">
                        @php $maxVenta = $todosLosAgentes->max('venta_neta') > 0 ? $todosLosAgentes->max('venta_neta') : 1; @endphp
                        @foreach($todosLosAgentes as $index => $agente)
                            <tr>
                                <td class="pl-4 font-weight-bold text-gray-400 text-center">{{ $index + 1 }}</td> 
                                <td class="font-weight-bold text-dark">
                                    <div class="d-flex align-items-center"><i class="fas fa-user-circle text-gray-300 fa-lg mr-2"></i>{{ $agente->agente_nombre }}</div>
                                    @php $porcentaje = ($agente->venta_neta / $maxVenta) * 100; $colorBarra = $index == 0 ? 'bg-warning' : ($index == 1 ? 'bg-secondary' : ($index == 2 ? 'bg-orange' : 'bg-success')); @endphp
                                    <div class="progress shadow-sm mt-1" style="height: 4px; border-radius: 10px; width: 85%;"><div class="progress-bar {{ $colorBarra }}" style="width: {{ $porcentaje }}%;"></div></div>
                                </td>
                                <td class="text-center text-muted">{{ $agente->total_pedidos }}</td>
                                <td class="text-right text-secondary font-weight-bold">${{ number_format($agente->venta_bruta, 2) }}</td>
                                <td class="text-right text-danger font-weight-bold">-${{ number_format($agente->venta_perdida, 2) }}</td>
                                <td class="text-right pr-4 font-weight-bold text-success" style="font-size: 1.05rem;">${{ number_format($agente->venta_neta, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer border-top-0 bg-light" style="border-radius: 0 0 12px 12px;"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>
<style>
    .bg-orange { background-color: #fd7e14 !important; }
    .cursor-pointer {
        cursor: pointer;
        user-select: none; /* Evita que el texto se seleccione al dar múltiples clics rápidos */
    }
    .cursor-pointer:hover {
        opacity: 0.9;
    }
</style>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Nunito', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
        Chart.defaults.color = '#7f8c8d';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(44, 62, 80, 0.9)';
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.tooltip.displayColors = false;

        @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
            // LÍNEA TENDENCIA ADMIN
            const canvasAdmin1 = document.getElementById('chartAdminVentasGlobales');
            if (canvasAdmin1) {
                const ctx1 = canvasAdmin1.getContext('2d');
                let gradient1 = ctx1.createLinearGradient(0, 0, 0, 350);
                gradient1.addColorStop(0, 'rgba(26, 188, 156, 0.4)');
                gradient1.addColorStop(1, 'rgba(26, 188, 156, 0.0)');

                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: {!! $chartVentasGlobales_labels_js ?? '[]' !!},
                        datasets: [{
                            label: 'Ventas MXN',
                            data: {!! $chartVentasGlobales_data_js ?? '[]' !!},
                            borderColor: '#1abc9c', backgroundColor: gradient1, borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: '#ffffff', pointBorderColor: '#1abc9c', pointBorderWidth: 2, pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }, x: { border: {display: false}, grid: { display: false } } } }
                });
            }

            // ============================================
            // BARRAS TOP AGENTES ADMIN (Revertido a dinero)
            // ============================================
            const canvasAdmin2 = document.getElementById('chartAdminTopAgentes');
            if (canvasAdmin2) {
                const ctx2 = canvasAdmin2.getContext('2d');
                let gradient2 = ctx2.createLinearGradient(0, 0, 400, 0);
                gradient2.addColorStop(0, '#27ae60');
                gradient2.addColorStop(1, '#2ecc71');

                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: {!! $chartTopAgentes_labels_js ?? '[]' !!},
                        datasets: [{
                            label: 'Ventas MXN',
                            data: {!! $chartTopAgentes_data_js ?? '[]' !!},
                            backgroundColor: gradient2, 
                            borderRadius: 6,
                            barPercentage: 0.7
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        indexAxis: 'y', // La hace horizontal
                        plugins: { 
                            legend: { display: false },
                            tooltip: { callbacks: { label: function(context) { return '$' + context.raw.toLocaleString(); } } } 
                        }, 
                        scales: { 
                            x: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }, 
                            y: { border: {display: false}, grid: { display: false } } 
                        } 
                    }
                });
            }

        @elseif(auth()->user()->hasRole('agente-ventas'))
            // LÍNEA TENDENCIA AGENTE
            const canvasAgente1 = document.getElementById('chartAgenteMiTendencia');
            if (canvasAgente1) {
                const ctxA1 = canvasAgente1.getContext('2d');
                let gradientA1 = ctxA1.createLinearGradient(0, 0, 0, 350);
                gradientA1.addColorStop(0, 'rgba(52, 152, 219, 0.4)'); gradientA1.addColorStop(1, 'rgba(52, 152, 219, 0.0)');

                new Chart(ctxA1, {
                    type: 'line',
                    data: {
                        labels: {!! $chartMiTendencia_labels_js ?? '[]' !!},
                        datasets: [{
                            label: 'Mis Ventas MXN',
                            data: {!! $chartMiTendencia_data_actual_js ?? '[]' !!},
                            borderColor: '#3498db', backgroundColor: gradientA1, borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: '#ffffff', pointBorderColor: '#3498db', pointBorderWidth: 2, pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }, x: { border: {display: false}, grid: { display: false } } } }
                });
            }

            // BARRAS MIS CLIENTES AGENTE (CORREGIDO: EJE X)
            const canvasAgente2 = document.getElementById('chartAgenteMisTopClientes');
            if (canvasAgente2) {
                const ctxA2 = canvasAgente2.getContext('2d');
                let gradientA2 = ctxA2.createLinearGradient(0, 0, 400, 0);
                gradientA2.addColorStop(0, '#8e44ad'); gradientA2.addColorStop(1, '#9b59b6');

                // Asegurar que las labels sean decodificadas correctamente como arreglo
                let labelsClientes = {!! $chartMisClientes_labels_js ?? '[]' !!};
                // Cortar los nombres si son muy largos para que no rompan la gráfica
                labelsClientes = labelsClientes.map(nombre => nombre.length > 15 ? nombre.substring(0, 15) + '...' : nombre);

                new Chart(ctxA2, {
                    type: 'bar',
                    data: {
                        labels: labelsClientes,
                        datasets: [{
                            label: 'Ventas MXN',
                            data: {!! $chartMisClientes_data_js ?? '[]' !!},
                            backgroundColor: gradientA2, borderRadius: 6, barPercentage: 0.5
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { legend: { display: false } }, 
                        scales: { 
                            y: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }, 
                            // Eje X corregido para mostrar texto
                            x: { border: {display: false}, grid: { display: false }, ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 } } 
                        } 
                    }
                });
            }

            // ============================================
            // RANKING DEL EQUIPO AGENTE (Barras Horizontales)
            // ============================================
            const canvasRankingEquipo = document.getElementById('chartRankingEquipo');
            if (canvasRankingEquipo) {
                new Chart(canvasRankingEquipo, {
                    type: 'bar',
                    data: {
                        labels: {!! $chartRanking_labels_js ?? '[]' !!},
                        datasets: [{
                            label: 'Aportación a la empresa (%)',
                            data: {!! $chartRanking_data_js ?? '[]' !!},
                            backgroundColor: {!! $chartRanking_colors_js ?? '[]' !!},
                            borderRadius: 10, // Bordes redondeados
                            barPercentage: 0.6
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        indexAxis: 'y', // Convertir a barras horizontales
                        plugins: { 
                            legend: { display: false },
                            tooltip: { callbacks: { label: function(context) { return context.raw + '% de las ventas'; } } } 
                        }, 
                        scales: { 
                            // Ocultamos los números del eje X para no saturar, ya que se ve al pasar el ratón
                            x: { display: false, max: 100 }, 
                            y: { border: {display: false}, grid: { display: false }, ticks: { font: { weight: 'bold' } } } 
                        } 
                    }
                });
            }
        @endif
    });

    // ============================================
    // ORDENAMIENTO DE TABLA CONSISTENCIA MENSUAL
    // ============================================
    let sortDirection = {}; // Objeto para llevar registro de la dirección (asc/desc) de cada columna

    function sortTable(columnIndex, type) {
        const table = document.getElementById("tablaConsistencia");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        
        // Si no hay filas de datos reales (ej. "No hay datos históricos"), no hacer nada
        if(rows.length === 0 || rows[0].cells.length === 1) return;

        // Alternar dirección (Ascendente -> Descendente -> Ascendente)
        sortDirection[columnIndex] = !sortDirection[columnIndex]; 
        const isAscending = sortDirection[columnIndex];

        rows.sort((a, b) => {
            let cellA = a.cells[columnIndex].innerText.trim();
            let cellB = b.cells[columnIndex].innerText.trim();

            if (type === 'number') {
                // Limpiar símbolos de moneda y comas para comparar números
                let numA = parseFloat(cellA.replace(/[^0-9.-]+/g,""));
                let numB = parseFloat(cellB.replace(/[^0-9.-]+/g,""));
                
                // Tratar NaN como 0
                numA = isNaN(numA) ? 0 : numA;
                numB = isNaN(numB) ? 0 : numB;

                return isAscending ? numA - numB : numB - numA;
            } else {
                // Comparación de cadenas (String)
                return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
            }
        });

        // Reordenar las filas en el DOM
        rows.forEach(row => tbody.appendChild(row));
    }
</script>
@endpush