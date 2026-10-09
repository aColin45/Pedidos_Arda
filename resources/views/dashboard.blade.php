@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid">

        {{-- Encabezado Estilizado del Dashboard --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Dashboard</h1>
                <p class="text-muted small mb-0">Resumen general de actividad, métricas y analíticos clave</p>
            </div>
            <a href="{{ route('perfil.pedidos') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-list fa-sm text-white-50 mr-1"></i> Ver todos los pedidos
            </a>
        </div>

        {{-- ========================================================================= --}}
        {{-- ||       NUEVO MÓDULO: SALA DE GUERRA DE METAS (WAR ROOM)              || --}}
        {{-- ========================================================================= --}}
        
        {{-- VISTA AGENTE (O ADMIN CON META): Panel Estelar de Progreso --}}
        @if(isset($miMetaMesActual) && $miMetaMesActual > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow border-0" style="border-radius: 12px; border-left: 6px solid {{ $colorMeta ?? '#3498db' }};">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="font-weight-bold text-dark mb-1">
                                    <i class="fas fa-rocket mr-2" style="color: {{ $colorMeta ?? '#3498db' }};"></i> 
                                    Mi Meta de Ventas ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})
                                </h4>
                                <p class="text-muted small mb-3">Progreso de tu cuota mensual. ¡Sigue así, tú puedes!</p>
                                
                                <div class="progress shadow-sm mb-2" style="height: 30px; border-radius: 15px; background-color: #e9ecef;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated font-weight-bold" 
                                         role="progressbar" 
                                         style="width: {{ $miPorcentajeMeta > 100 ? 100 : $miPorcentajeMeta }}%; background-color: {{ $colorMeta ?? '#3498db' }}; font-size: 1.1rem;" 
                                         aria-valuenow="{{ $miPorcentajeMeta }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($miPorcentajeMeta, 1) }}%
                                    </div>
                                </div>
                                
                                @if($miPorcentajeMeta >= 100)
                                    <span class="text-success font-weight-bold small"><i class="fas fa-trophy mr-1 text-warning"></i> ¡Felicidades! Meta superada. Tienes un superávit de <strong>${{ number_format($misVentasEsteMes - $miMetaMesActual, 2) }}</strong> a tu favor.</span>
                                @else
                                    <span class="text-danger font-weight-bold small"><i class="fas fa-fire mr-1 text-danger"></i> ¡Acelera el paso! Te faltan <strong>${{ number_format($miFaltanteMeta, 2) }}</strong> para alcanzar el éxito este mes.</span>
                                @endif
                            </div>
                            <div class="col-md-4 mt-4 mt-md-0" style="border-left: 2px dashed #e3e6f0; padding-left: 20px;">
                                <div class="text-xs font-weight-bold text-uppercase mb-1 text-center" style="color: {{ $colorMeta ?? '#3498db' }};">Ventas / Meta Total</div>
                                <div class="h2 mb-2 font-weight-bold text-dark text-center">
                                    ${{ number_format($misVentasEsteMes, 2) }}
                                </div>
                                
                                {{-- CAJA DE DESGLOSE DE META (NUEVO) --}}
                                <div class="bg-light p-3 rounded border shadow-sm">
                                    <h6 class="font-weight-bold text-gray-700 mb-2 border-bottom pb-1" style="font-size: 0.85rem;"><i class="fas fa-calculator mr-1"></i> ¿Cómo se calcula mi meta?</h6>
                                    
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span class="text-muted">Meta Fija Base:</span>
                                        <span class="font-weight-bold text-dark">${{ number_format(auth()->user()->meta_mensual_base, 2) }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.85rem;">
                                        <span class="text-muted">Arrastre mes anterior:</span>
                                        @if(auth()->user()->saldo_meta_acumulado > 0)
                                            <span class="font-weight-bold text-danger" title="Faltante que no se logró vender el mes pasado">+${{ number_format(auth()->user()->saldo_meta_acumulado, 2) }}</span>
                                        @elseif(auth()->user()->saldo_meta_acumulado < 0)
                                            <span class="font-weight-bold text-success" title="Superávit a tu favor por vender más el mes pasado">-${{ number_format(abs(auth()->user()->saldo_meta_acumulado), 2) }}</span>
                                        @else
                                            <span class="font-weight-bold text-muted">$0.00</span>
                                        @endif
                                    </div>
                                    
                                    <div class="d-flex justify-content-between border-top pt-2" style="font-size: 0.9rem;">
                                        <span class="font-weight-bold" style="color: {{ $colorMeta ?? '#3498db' }};">Objetivo Total:</span>
                                        <span class="font-weight-bold" style="color: {{ $colorMeta ?? '#3498db' }};">${{ number_format($miMetaMesActual, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- VISTA ADMIN: Radar de Metas Global y Ranking --}}
        @if(auth()->user()->hasRole('admin') && isset($rendimientoAgentes) && $rendimientoAgentes->count() > 0)
        <div class="row mb-4">
            <div class="col-12 mb-3">
                <h4 class="font-weight-bold text-gray-800 mb-0"><i class="fas fa-crosshairs text-danger mr-2"></i>Radar de Metas ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</h4>
                <p class="small text-muted">Monitoreo en tiempo real del desempeño de ventas de la empresa y los agentes.</p>
            </div>
            
            {{-- Barra Global Empresa --}}
            <div class="col-12 mb-4">
                <div class="card shadow border-0 bg-dark text-white" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center text-md-left mb-3 mb-md-0">
                                <h5 class="font-weight-bold mb-1 text-warning"><i class="fas fa-globe-americas mr-2"></i>Meta Global ARDA</h5>
                                <h3 class="mb-0 font-weight-bold">${{ number_format($ventasGlobalesMesActual, 2) }} <br><span class="h6 text-gray-400">Objetivo: ${{ number_format($metaGlobalEmpresa, 2) }}</span></h3>
                            </div>
                            <div class="col-md-8">
                                @php
                                    $colorGlobal = $porcentajeGlobalEmpresa >= 100 ? 'bg-success' : ($porcentajeGlobalEmpresa >= 75 ? 'bg-warning' : 'bg-danger');
                                @endphp
                                <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                    <span>Progreso Global</span>
                                    <span>{{ number_format($porcentajeGlobalEmpresa, 1) }}%</span>
                                </div>
                                <div class="progress mb-2 shadow" style="height: 30px; border-radius: 15px; background-color: rgba(255,255,255,0.1);">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $colorGlobal }} font-weight-bold" 
                                         role="progressbar" 
                                         style="width: {{ $porcentajeGlobalEmpresa > 100 ? 100 : $porcentajeGlobalEmpresa }}%; font-size: 1.1rem;" 
                                         aria-valuenow="{{ $porcentajeGlobalEmpresa }}">
                                        {{ number_format($porcentajeGlobalEmpresa, 1) }}%
                                    </div>
                                </div>
                                <span class="small text-gray-400"><i class="fas fa-info-circle mr-1"></i> Suma total de las metas individuales vs ventas aprobadas del mes.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slider Horizontal de Agentes --}}
            <div class="col-12">
                <h6 class="font-weight-bold text-gray-700 mb-3"><i class="fas fa-users mr-2"></i>Desempeño Individual de Asesores</h6>
                
                {{-- Contenedor con scroll horizontal para que no rompa la pantalla si hay muchos agentes --}}
                <div class="d-flex flex-nowrap pb-3" style="overflow-x: auto; gap: 15px; scroll-behavior: smooth;">
                    @foreach($rendimientoAgentes as $ag)
                        @php
                            $colorAgente = $ag['porcentaje'] >= 100 ? '#1cc88a' : ($ag['porcentaje'] >= 75 ? '#f6c23e' : '#e74a3b');
                            $iconoAgente = $ag['porcentaje'] >= 100 ? 'fa-trophy text-warning' : ($ag['porcentaje'] >= 75 ? 'fa-fire text-warning' : 'fa-exclamation-circle text-danger');
                        @endphp
                        
                        <div class="card shadow-sm border-0 flex-shrink-0" style="width: 300px; border-radius: 10px; border-bottom: 5px solid {{ $colorAgente }};">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold text-dark mb-0 text-truncate" style="max-width: 85%;" title="{{ $ag['nombre'] }}">
                                        <i class="fas fa-user-tie text-gray-400 mr-2"></i>{{ $ag['nombre'] }}
                                    </h6>
                                    <i class="fas {{ $iconoAgente }} fa-lg"></i>
                                </div>
                                
                                <div class="mb-3 mt-3">
                                    <div class="d-flex justify-content-between text-xs font-weight-bold text-uppercase text-muted mb-1">
                                        <span>Progreso</span>
                                        <span style="color: {{ $colorAgente }};">{{ number_format($ag['porcentaje'], 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px; background-color: #eaecf4;">
                                        <div class="progress-bar progress-bar-striped" role="progressbar" style="width: {{ $ag['porcentaje'] > 100 ? 100 : $ag['porcentaje'] }}%; background-color: {{ $colorAgente }};"></div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-end text-sm border-top pt-2">
                                    <div>
                                        <small class="d-block text-muted">Vendido</small>
                                        <span class="text-dark font-weight-bold fs-6">${{ number_format($ag['ventas'], 0) }}</span>
                                    </div>
                                    <div class="text-right">
                                        <small class="d-block text-muted">Meta Real</small>
                                        <span class="text-muted font-weight-bold">${{ number_format($ag['meta'], 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- ||       TABLAS HISTÓRICAS MENSUALES (Para ver meses pasados)          || --}}
        {{-- ========================================================================= --}}
        <div class="row mb-4">
            
            {{-- VISTA ADMIN: Tabla Comparativa de Asesores por Mes --}}
            @if(auth()->user()->hasRole('admin') && isset($ventasMensualesAdmin))
            <div class="col-12">
                <div class="card shadow border-0" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <div>
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-2"></i>Desglose Histórico de Ventas por Mes</h6>
                            <span class="small text-muted">Rendimiento evaluado contra la Meta Mensual a partir de Septiembre 2026.</span>
                        </div>
                        {{-- Simbología Admin --}}
                        <div class="d-none d-md-flex gap-2 small mt-2 mt-md-0">
                            <span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check mr-1"></i>100%+</span>
                            <span class="badge bg-warning text-dark px-2 py-1 mx-1"><i class="fas fa-minus mr-1"></i>75%-99%</span>
                            <span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-arrow-down mr-1"></i><75%</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="bg-light text-gray-700">
                                    <tr>
                                        <th class="border-0 pl-4 sticky-left bg-light" style="position: sticky; left: 0; z-index: 1; border-right: 1px solid #e3e6f0;">Asesor / Meta Base</th>
                                        @foreach($columnasMeses as $codigo => $nombreMes)
                                            <th class="border-0 text-center px-4">{{ $nombreMes }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ventasMensualesAdmin as $agente => $ventas)
                                        @php
                                            // Consultamos rápido la meta base de este agente
                                            $metaAgenteAdmin = \App\Models\User::where('name', $agente)->value('meta_mensual_base') ?? 0;
                                        @endphp
                                        <tr>
                                            <td class="pl-4 font-weight-bold text-dark sticky-left bg-white" style="position: sticky; left: 0; border-right: 1px solid #e3e6f0;">
                                                <i class="fas fa-user-circle text-gray-400 mr-2"></i>{{ $agente }}
                                                @if($metaAgenteAdmin > 0)
                                                    <div class="small text-muted font-weight-normal mt-1 pl-4">Meta: ${{ number_format($metaAgenteAdmin, 0) }}</div>
                                                @endif
                                            </td>
                                            @foreach($columnasMeses as $codigo => $nombreMes)
                                                @php
                                                    $ventaMes = $ventas->firstWhere('mes_codigo', $codigo);
                                                    $monto = $ventaMes ? $ventaMes->total_venta : 0;
                                                    
                                                    // Validación de fecha: Solo calcular si es igual o posterior a Septiembre 2026
                                                    $aplicaMeta = ($codigo >= '2026-09');
                                                    $pctAdmin = ($metaAgenteAdmin > 0 && $aplicaMeta) ? ($monto / $metaAgenteAdmin) * 100 : 0;
                                                    $colorAdmin = $pctAdmin >= 100 ? 'success' : ($pctAdmin >= 75 ? 'warning' : 'danger');
                                                    $textColor = $pctAdmin >= 75 && $pctAdmin < 100 ? 'text-dark' : 'text-white';
                                                @endphp
                                                <td class="text-center px-4" style="border-left: 1px dashed #f1f3f8;">
                                                    @if($monto > 0)
                                                        <div class="font-weight-bold text-dark mb-1">${{ number_format($monto, 2) }}</div>
                                                        @if($metaAgenteAdmin > 0 && $aplicaMeta)
                                                            <span class="badge bg-{{ $colorAdmin }} {{ $textColor }} shadow-sm px-2">{{ number_format($pctAdmin, 1) }}%</span>
                                                        @elseif(!$aplicaMeta)
                                                            <span class="text-muted" style="font-size: 0.75rem;">N/A</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No hay datos históricos de ventas aún.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- VISTA AGENTE (O ADMIN CON METAS): Mi propio historial --}}
            @if(isset($misVentasTabla))
            <div class="col-xl-7 col-lg-8">
                <div class="card shadow border-0" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <div>
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-check mr-2"></i>Mis Ventas Históricas</h6>
                            <span class="small text-muted">Rendimiento evaluado a partir de Septiembre 2026.</span>
                        </div>
                        {{-- Simbología Agente --}}
                        <div class="d-none d-sm-flex gap-2 small mt-2 mt-sm-0">
                            <span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check mr-1"></i>100%+</span>
                            <span class="badge bg-warning text-dark px-2 py-1 mx-1"><i class="fas fa-minus mr-1"></i>75%-99%</span>
                            <span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-arrow-down mr-1"></i><75%</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="bg-light text-gray-700 sticky-top">
                                    <tr>
                                        <th class="border-0 pl-4">Mes</th>
                                        <th class="border-0 text-right">Meta Base</th>
                                        <th class="border-0 text-right">Total Vendido</th>
                                        <th class="border-0 text-center pr-4">Rendimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($misVentasTabla as $venta)
                                        @php
                                            $metaBaseUser = auth()->user()->meta_mensual_base ?? 0;
                                            
                                            // Validación de fecha para el agente
                                            $aplicaMeta = ($venta->mes_id >= '2026-09');
                                            $pctHist = ($metaBaseUser > 0 && $aplicaMeta) ? ($venta->total_venta / $metaBaseUser) * 100 : 0;
                                            $colorBadge = $pctHist >= 100 ? 'bg-success' : ($pctHist >= 75 ? 'bg-warning' : 'bg-danger');
                                            $textBadgeColor = $pctHist >= 75 && $pctHist < 100 ? 'text-dark' : 'text-white';
                                        @endphp
                                        <tr>
                                            <td class="pl-4 font-weight-bold text-dark text-capitalize">
                                                <i class="far fa-calendar-alt text-gray-400 mr-2"></i>
                                                {{ \Carbon\Carbon::createFromFormat('Y-m', $venta->mes_id)->translatedFormat('F Y') }}
                                            </td>
                                            <td class="text-right text-muted">
                                                {{ $aplicaMeta ? '$'.number_format($metaBaseUser, 2) : 'N/A' }}
                                            </td>
                                            <td class="text-right text-success font-weight-bold">
                                                ${{ number_format($venta->total_venta, 2) }}
                                            </td>
                                            <td class="text-center pr-4">
                                                @if($metaBaseUser > 0 && $aplicaMeta)
                                                    <span class="badge {{ $colorBadge }} {{ $textBadgeColor }} px-2 py-1 shadow-sm" style="font-size: 0.85rem;">
                                                        {{ number_format($pctHist, 1) }}%
                                                    </span>
                                                @elseif(!$aplicaMeta)
                                                    <span class="text-muted small">N/A</span>
                                                @else
                                                    <span class="text-muted small">Sin Meta</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No hay datos de ventas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- ||       SECCIÓN DE GRÁFICOS DINÁMICOS POR ROL                          || --}}
        {{-- ========================================================================= --}}
        <div class="row">

            @if(auth()->user()->hasRole('admin'))
            {{-- --- 📊 VISTA DE GRÁFICOS ADMIN --- --}}
            
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-0 d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-chart-line text-info mr-2"></i>Tendencia Global de Ventas Histórica</h6>
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
                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-chart-bar text-success mr-2"></i>Top 10 Agentes (Mes Actual)</h6>
                        {{-- Botón "Ver Todos" Elegante --}}
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTodosAgentes">
                            <i class="fas fa-expand-arrows-alt mr-1"></i> Ver Todos
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar" style="position: relative; height: 350px;">
                            <canvas id="chartAdminTopAgentes"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            @elseif(auth()->user()->hasRole('agente-ventas'))
            {{-- --- 📊 VISTA DE GRÁFICOS AGENTE --- --}}
            
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-0 d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-history text-info mr-2"></i>Mi Tendencia de Ventas Histórica</h6>
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
                        <h6 class="m-0 font-weight-bold text-gray-700"><i class="fas fa-award text-primary mr-2"></i>Mis Top 5 Clientes Este Mes</h6>
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

        {{-- ================================================================ --}}
        {{-- ||       SECCIÓN DE TARJETAS DE ESTADÍSTICAS (KPIs REDISEÑADOS) || --}}
        {{-- ================================================================ --}}
        <div class="row">

            @if(auth()->user()->hasRole('admin'))
            {{-- --- VISTA DE ADMIN KPIs --- --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #1abc9c !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Ventas Totales</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">${{ number_format($totalVentas, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300" style="opacity: 0.6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #27ae60 !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Pedidos Totales</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalPedidos }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300" style="opacity: 0.6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #e67e22 !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Agentes Activos</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalAgentes }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users-cog fa-2x text-gray-300" style="opacity: 0.6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #3498db !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Clientes Registrados</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalClientes }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300" style="opacity: 0.6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @elseif(auth()->user()->hasRole('agente-ventas'))
            {{-- --- VISTA DE AGENTE KPIs --- --}}
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #1abc9c !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Mis Ventas Totales</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">${{ number_format($misVentas, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-award fa-2x text-gray-300" style="opacity: 0.6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #27ae60 !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Pedidos Realizados</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $misPedidos }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-basket fa-2x text-gray-300" style="opacity: 0.6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #e67e22 !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Pedidos Pendientes</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $misPedidosPendientes }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-circle fa-2x text-gray-300" style="opacity: 0.6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; transition: transform 0.2s; border-top: 4px solid #3498db !important;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Clientes Asignados</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $misClientes }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300" style="opacity: 0.6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ======================================================== --}}
            {{-- SEGUNDA FILA DE TARJETAS (MÉTRICAS NUEVAS) ADMIN         --}}
            {{-- ======================================================== --}}
            @if(auth()->user()->hasRole('admin'))
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; border-top: 4px solid #f6c23e !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Cotizaciones Activas</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">${{ number_format($totalCotizacionesMonto, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice-dollar fa-2x text-warning" style="opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; border-top: 4px solid #f6c23e !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Cotizaciones Totales</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalCotizacionesCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-copy fa-2x text-warning" style="opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; border-top: 4px solid #e74a3b !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Devoluciones en Proceso</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalDevoluciones }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-undo fa-2x text-danger" style="opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; border-top: 4px solid #36b9cc !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Pedidos por Aprobar</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $pedidosPendientesAdmin }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-check fa-2x text-info" style="opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ======================================================== --}}
            {{-- SEGUNDA FILA DE TARJETAS (MÉTRICAS NUEVAS) AGENTE        --}}
            {{-- ======================================================== --}}
            @if(auth()->user()->hasRole('agente-ventas'))
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; border-top: 4px solid #f6c23e !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Mis Cotizaciones Activas</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">${{ number_format($misCotizacionesMonto, 2) }} <small class="text-muted">({{ $misCotizacionesCount }})</small></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice-dollar fa-2x text-warning" style="opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; border-top: 4px solid #e74a3b !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Mis Devoluciones (Proceso)</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $misDevoluciones }} docs</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-undo fa-2x text-danger" style="opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 border-0" style="border-radius: 12px; border-top: 4px solid #36b9cc !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1">Pedidos Recientes Aprobados</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">Ver Historial <i class="fas fa-arrow-right ml-1" style="font-size: 0.7em"></i></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-double fa-2x text-info" style="opacity: 0.8;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ================================================================ --}}
        {{-- ||       TABLA DE ÚLTIMOS PEDIDOS                             || --}}
        {{-- ================================================================ --}}
        <div class="row mt-2 mb-5">
            <div class="col-12">
                <div class="card shadow mb-4 border-0" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between pb-1" style="border-radius: 12px 12px 0 0;">
                        <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-clock mr-2"></i>Actividad Reciente</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="bg-light text-gray-700">
                                    <tr>
                                        <th class="border-0 pl-4">ID</th>
                                        <th class="border-0">Cliente</th>
                                        <th class="border-0">Código Cliente</th>
                                        @if(auth()->user()->hasRole('admin'))
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
                                        <td><div class="font-weight-bold text-dark">{{ $pedido->cliente->nombre ?? 'N/A' }}</div></td>
                                        <td><span class="badge badge-light border text-secondary">{{ $pedido->cliente->codigo ?? 'N/A' }}</span></td>
                                        @if(auth()->user()->hasRole('admin'))
                                        <td><div class="small text-gray-800"><i class="fas fa-user-circle text-gray-400 mr-1"></i> {{ $pedido->agente->name ?? 'N/A' }}</div></td>
                                        @endif
                                        <td><div class="small text-gray-600"><i class="far fa-calendar-alt mr-1"></i>{{ $pedido->created_at->format('d/m/Y') }}</div></td>
                                        <td class="text-center">
                                            @php $estado = strtolower($pedido->estado); @endphp
                                            @if($estado == 'pendiente') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #f6c23e !important; color: #333 !important;">Pendiente</span>
                                            @elseif($estado == 'enviado') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #36b9cc !important; color: #fff !important;">Enviado</span>
                                            @elseif(in_array($estado, ['completado', 'entregado', 'finalizado', 'envio completado'])) <span class="badge badge-pill bg-success text-white px-3 py-1 shadow-sm">Completado</span>
                                            @elseif($estado == 'cancelado') <span class="badge badge-pill bg-secondary text-white px-3 py-1 shadow-sm">Cancelado</span>
                                            @elseif($estado == 'anulado') <span class="badge badge-pill bg-danger text-white px-3 py-1 shadow-sm">Anulado</span>
                                            @elseif($estado == 'devolucion_pendiente') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #f6c23e !important; color: #333 !important;">Dev. Pendiente</span>
                                            @elseif($estado == 'devolucion_aprobada') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #36b9cc !important; color: #fff !important;">En Proceso (Dev)</span>
                                            @elseif($estado == 'devolucion_finalizada') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #1cc88a !important; color: #fff !important;">Dev. Finalizada</span>
                                            @elseif($estado == 'devolucion_rechazada') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #e74a3b !important; color: #fff !important;">Dev. Rechazada</span>
                                            @elseif($estado == 'devolucion_cancelada') <span class="badge badge-pill px-3 py-1 shadow-sm" style="background-color: #858796 !important; color: #fff !important;">Dev. Cancelada</span>
                                            @else <span class="badge badge-pill bg-dark text-white px-3 py-1 shadow-sm">{{ ucfirst($pedido->estado) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right pr-4 font-weight-bold text-success">${{ number_format($pedido->total, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="{{ auth()->user()->hasRole('admin') ? '7' : '6' }}" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-3 text-gray-300 d-block"></i> No hay pedidos recientes.</td></tr>
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

{{-- ========================================================================= --}}
{{-- || MODAL: RENDIMIENTO TRANSPARENTE DE AGENTES (SOLO ADMIN)             || --}}
{{-- ========================================================================= --}}
@if(auth()->user()->hasRole('admin') && isset($todosLosAgentes))
<div class="modal fade" id="modalTodosAgentes" tabindex="-1" aria-labelledby="modalTodosAgentesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom-0 pb-1" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" id="modalTodosAgentesLabel">
                    <i class="fas fa-balance-scale text-primary mr-2"></i>Desglose Financiero de Agentes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body p-0">
                <div class="alert alert-info mx-3 mt-2 mb-0 py-2 small shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-info-circle mr-1"></i> <strong>Transparencia Total:</strong> Aquí puedes ver todo el dinero registrado por el agente, las pérdidas (cancelaciones o devoluciones) y el monto real (Ventas Netas) que impacta en el Dashboard.
                </div>

                <table class="table table-hover align-middle mb-0 mt-3">
                    <thead class="bg-white text-gray-600 sticky-top shadow-sm pb-1" style="font-size: 0.85rem;">
                        <tr>
                            <th class="border-0 pl-4 text-center" width="5%">#</th> 
                            <th class="border-0" width="30%">Agente</th>
                            <th class="border-0 text-center" width="10%">Pedidos</th> 
                            <th class="border-0 text-right text-secondary" width="18%">Total Registrado (Bruto)</th>
                            <th class="border-0 text-right text-danger" width="18%">Cancelaciones / Devoluciones</th>
                            <th class="border-0 text-right pr-4 text-success font-weight-bold" width="19%">Ventas Netas (Real)</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.9rem;">
                        @php 
                            $maxVenta = $todosLosAgentes->max('venta_neta') > 0 ? $todosLosAgentes->max('venta_neta') : 1; 
                        @endphp
                        @foreach($todosLosAgentes as $index => $agente)
                            <tr>
                                <td class="pl-4 font-weight-bold text-gray-400 text-center">{{ $index + 1 }}</td> 
                                <td class="font-weight-bold text-dark">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle text-gray-300 fa-lg mr-2"></i>{{ $agente->agente_nombre }}
                                    </div>
                                    @php 
                                        $porcentaje = ($agente->venta_neta / $maxVenta) * 100; 
                                        $colorBarra = $index == 0 ? 'bg-warning' : ($index == 1 ? 'bg-secondary' : ($index == 2 ? 'bg-orange' : 'bg-success'));
                                    @endphp
                                    <div class="progress shadow-sm mt-1" style="height: 4px; border-radius: 10px; width: 85%;">
                                        <div class="progress-bar {{ $colorBarra }}" role="progressbar" style="width: {{ $porcentaje }}%;" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
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
            <div class="modal-footer border-top-0 bg-light" style="border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<style>
    .bg-orange { background-color: #fd7e14 !important; }
    .table-hover tbody tr:hover { background-color: #f8f9fc !important; }
</style>
@endif

@endsection

{{-- ========================================================================= --}}
{{-- ||       CÓDIGO JS PARA DIBUJAR LOS GRÁFICOS                           || --}}
{{-- ========================================================================= --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        Chart.defaults.font.family = "'Figtree', 'Nunito', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
        Chart.defaults.color = '#7f8c8d';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(44, 62, 80, 0.9)';
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.tooltip.displayColors = false;

        @if(auth()->user()->hasRole('admin'))
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
                            borderColor: '#1abc9c', 
                            backgroundColor: gradient1,
                            borderWidth: 3,
                            tension: 0.4, 
                            fill: true, 
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#1abc9c',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }, x: { border: {display: false}, grid: { display: false } } } }
                });
            }

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
                    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }, y: { border: {display: false}, grid: { display: false } } } }
                });
            }

        @elseif(auth()->user()->hasRole('agente-ventas'))
            const canvasAgente1 = document.getElementById('chartAgenteMiTendencia');
            if (canvasAgente1) {
                const ctxA1 = canvasAgente1.getContext('2d');
                let gradientA1 = ctxA1.createLinearGradient(0, 0, 0, 350);
                gradientA1.addColorStop(0, 'rgba(52, 152, 219, 0.4)');
                gradientA1.addColorStop(1, 'rgba(52, 152, 219, 0.0)');

                new Chart(ctxA1, {
                    type: 'line',
                    data: {
                        labels: {!! $chartMiTendencia_labels_js ?? '[]' !!},
                        datasets: [{
                            label: 'Mis Ventas MXN',
                            data: {!! $chartMiTendencia_data_actual_js ?? '[]' !!},
                            borderColor: '#3498db', 
                            backgroundColor: gradientA1,
                            borderWidth: 3,
                            tension: 0.4, 
                            fill: true, 
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3498db',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }, x: { border: {display: false}, grid: { display: false } } } }
                });
            }

            const canvasAgente2 = document.getElementById('chartAgenteMisTopClientes');
            if (canvasAgente2) {
                const ctxA2 = canvasAgente2.getContext('2d');
                let gradientA2 = ctxA2.createLinearGradient(0, 0, 400, 0);
                gradientA2.addColorStop(0, '#8e44ad');
                gradientA2.addColorStop(1, '#9b59b6');

                new Chart(ctxA2, {
                    type: 'bar',
                    data: {
                        labels: {!! $chartMisClientes_labels_js ?? '[]' !!},
                        datasets: [{
                            label: 'Ventas MXN',
                            data: {!! $chartMisClientes_data_js ?? '[]' !!},
                            backgroundColor: gradientA2, 
                            borderRadius: 6,
                            barPercentage: 0.7
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(value) { return '$' + value.toLocaleString(); } } }, y: { border: {display: false}, grid: { display: false } } } }
                });
            }
        @endif
    });
</script>
@endpush