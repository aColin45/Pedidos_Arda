@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid mt-4">
        
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold"><i class="fas fa-chart-line text-primary mr-2"></i> Analíticos de Prospección</h1>
                <p class="text-muted small mb-0 mt-1">Monitoreo del desempeño del equipo comercial y embudo de conversión.</p>
            </div>
            <a href="{{ route('prospectos.index') }}" class="btn btn-sm btn-secondary shadow-sm font-weight-bold">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Regresar a la lista
            </a>
        </div>

        {{-- TARJETAS RESUMEN (KPIs) INTERACTIVAS --}}
        <div class="row mb-4">
            <div class="col-xl col-md-4 col-sm-6 mb-3">
                <a href="{{ route('prospectos.index', ['estatus' => '']) }}" class="text-decoration-none">
                    <div class="card shadow h-100 py-2 border-0 transition-hover" style="border-radius: 12px; border-top: 4px solid #4e73df !important; background: linear-gradient(180deg, #ffffff 0%, #f8f9fc 100%);">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="font-size: 0.65rem;">Todos los Prospectos</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $totalProspectos }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl col-md-4 col-sm-6 mb-3">
                <a href="{{ route('prospectos.index', ['estatus' => 'nuevo']) }}" class="text-decoration-none">
                    <div class="card shadow h-100 py-2 border-0 transition-hover" style="border-radius: 12px; border-top: 4px solid #0dcaf0 !important; background: linear-gradient(180deg, #ffffff 0%, #e8fcf3 100%);">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1" style="font-size: 0.65rem;">Nuevos Registros</div>
                                    @php $nuevos = ($prospectosPorEstatus['nuevo'] ?? 0) + ($prospectosPorEstatus['nuevo prospecto'] ?? 0); @endphp
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $nuevos }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-star fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl col-md-4 col-sm-6 mb-3">
                <a href="{{ route('prospectos.index', ['estatus' => 'en_seguimiento']) }}" class="text-decoration-none">
                    <div class="card shadow h-100 py-2 border-0 transition-hover" style="border-radius: 12px; border-top: 4px solid #f6c23e !important; background: linear-gradient(180deg, #ffffff 0%, #fffcf5 100%);">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1" style="font-size: 0.65rem;">En Seguimiento</div>
                                    @php $activos = ($prospectosPorEstatus['en_seguimiento'] ?? 0) + ($prospectosPorEstatus['en seguimiento activo'] ?? 0); @endphp
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $activos }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-sync-alt fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl col-md-6 col-sm-6 mb-3">
                <a href="{{ route('prospectos.index', ['estatus' => 'convertido']) }}" class="text-decoration-none">
                    <div class="card shadow h-100 py-2 border-0 transition-hover" style="border-radius: 12px; border-top: 4px solid #1cc88a !important; background: linear-gradient(180deg, #ffffff 0%, #e8fcf3 100%);">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.65rem;">Ventas (Por Convertir)</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $prospectosPorEstatus['convertido'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl col-md-6 col-sm-6 mb-3">
                <a href="{{ route('prospectos.index', ['estatus' => 'descartado']) }}" class="text-decoration-none">
                    <div class="card shadow h-100 py-2 border-0 transition-hover" style="border-radius: 12px; border-top: 4px solid #e74a3b !important; background: linear-gradient(180deg, #ffffff 0%, #fcedeb 100%);">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1" style="font-size: 0.65rem;">Descartados</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $prospectosPorEstatus['descartado'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- ZONA DE GRÁFICOS --}}
        <div class="row">
            
            {{-- GRÁFICO 1: RANKING DE CAPTACIÓN --}}
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-bottom-0">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-trophy text-warning mr-2"></i> Ranking: Propsectos por Agente</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar" style="position: relative; height: 320px;">
                            <canvas id="chartRankingAgentes"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GRÁFICO 2: FUENTES DE ORIGEN (DONA) --}}
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-bottom-0">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bullhorn text-info mr-2"></i> Orígenes de Prospección</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2" style="position: relative; height: 280px;">
                            <canvas id="chartOrigenes"></canvas>
                        </div>
                        <div class="mt-4 text-center small text-muted">
                            <i class="fas fa-info-circle mr-1"></i> ¿De dónde vienen los mejores prospectos?
                        </div>
                    </div>
                </div>
            </div>

            {{-- GRÁFICO 3: DESEMPEÑO DE CONVERSIÓN --}}
            <div class="col-12 mb-4">
                <div class="card shadow border-0" style="border-radius: 12px;">
                    <div class="card-header py-3 bg-white border-bottom-0">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-balance-scale text-success mr-2"></i> Efectividad de Cierre (Ganados vs Perdidos)</h6>
                        <small class="text-muted">Muestra cuántos prospectos logró convertir cada agente contra cuántos tuvo que descartar.</small>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar" style="position: relative; height: 350px;">
                            <canvas id="chartEfectividad"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('estilos')
<style>
    .transition-hover { 
        transition: all 0.2s ease-in-out; 
        cursor: pointer; 
    }
    .transition-hover:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 15px 25px rgba(0,0,0,.15)!important; 
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Configuración Global elegante
        Chart.defaults.font.family = "'Nunito', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
        Chart.defaults.color = '#858796';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(25, 32, 40, 0.9)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        // 1. GRÁFICA DE RANKING (BARRAS HORIZONTALES)
        const ctxRanking = document.getElementById('chartRankingAgentes');
        new Chart(ctxRanking, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labelsAgentes) !!},
                datasets: [{
                    label: 'Total Prospectos Registrados',
                    data: {!! json_encode($dataAgentes) !!},
                    backgroundColor: 'rgba(78, 115, 223, 0.85)',
                    hoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                    borderColor: '#4e73df',
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // La hace horizontal
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true },
                    y: { grid: { display: false } }
                }
            }
        });

        // 2. GRÁFICA DE ORÍGENES (DONA)
        const ctxOrigenes = document.getElementById('chartOrigenes');
        new Chart(ctxOrigenes, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($labelsOrigenes) !!},
                datasets: [{
                    data: {!! json_encode($dataOrigenes) !!},
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#fd7e14', '#20c997'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                    borderWidth: 2
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%', // Grosor de la dona
                plugins: {
                    legend: { display: false },
                }
            }
        });

        // 3. GRÁFICA DE EFECTIVIDAD (BARRAS APILADAS)
        const ctxEfectividad = document.getElementById('chartEfectividad');
        new Chart(ctxEfectividad, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labelsConversion) !!},
                datasets: [
                    {
                        label: 'Ganados (Convertidos)',
                        data: {!! json_encode($dataGanados) !!},
                        backgroundColor: '#1cc88a',
                        borderRadius: 4,
                        barPercentage: 0.5
                    },
                    {
                        label: 'Perdidos (Descartados)',
                        data: {!! json_encode($dataPerdidos) !!},
                        backgroundColor: '#e74a3b',
                        borderRadius: 4,
                        barPercentage: 0.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false } // Muestra ganados y perdidos juntos al hacer hover
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true }
                }
            }
        });

    });
</script>
@endpush