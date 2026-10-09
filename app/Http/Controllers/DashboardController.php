<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\PedidoDetallesExport; 

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function exportarPedidosExcel(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Acción no autorizada.');
        }

        $month = $request->query('month');
        $year = $request->query('year');
        $texto = $request->query('texto');

        $fecha = now()->format('d-m-Y_H-i');
        $fileName = "reporte_pedidos_arda_{$fecha}.xlsx";

        return Excel::download(new PedidoDetallesExport($month, $year, $texto), $fileName);
    }
    
    public function index()
    {
        $user = Auth::user();
        $data = [];

        $estadosQueSuman = [
            'pendiente', 'aprobado', 'parcialmente_surtido', 'enviado', 'enviado_completo', 'completado', 'entregado', 'finalizado'
        ];
        $estadosIgnorados = ['cotizacion', 'Cotizacion', 'COTIZACION', 'rechazado', 'cancelado', 'anulado', 'No Procede'];

        if ($user->hasRole('admin')) {
            // ==========================
            // KPIs PARA EL ADMIN
            // ==========================
            $data['totalPedidos'] = Pedido::where('is_cotizacion', 0)->whereNotIn('estado', $estadosIgnorados)->count();
            $data['totalVentas'] = Pedido::where('is_cotizacion', 0)->whereIn('estado', $estadosQueSuman)->sum('total');
            $data['totalAgentes'] = User::role('agente-ventas')->count();
            $data['totalClientes'] = Cliente::count();
            
            $cotizacionesQuery = Pedido::where('is_cotizacion', 1)->whereIn('estado', ['cotizacion', 'Cotizacion'])->whereDate('fecha_vencimiento', '>=', Carbon::now()->toDateString());
            $data['totalCotizacionesMonto'] = (clone $cotizacionesQuery)->sum('total');
            $data['totalCotizacionesCount'] = (clone $cotizacionesQuery)->count();
            $data['totalDevoluciones'] = Pedido::whereIn('estado', ['devolucion_pendiente', 'devolucion_aprobada'])->count();
            $data['pedidosPendientesAdmin'] = Pedido::where('is_cotizacion', 0)->where('estado', 'pendiente')->count();
            $data['ultimosPedidos'] = Pedido::with('agente', 'cliente')->where('is_cotizacion', 0)->whereNotIn('estado', $estadosIgnorados)->orderBy('created_at', 'desc')->take(5)->get();

            // ==========================
            // LÓGICA DE METAS - ADMIN
            // ==========================
            $agentesConMetas = User::where('meta_mensual_base', '>', 0)->get();
            $rendimientoAgentes = collect();
            $metaGlobalEmpresa = 0;
            $ventasGlobalesMesActual = 0;

            foreach($agentesConMetas as $agenteMeta) {
                $metaRealMes = floatval($agenteMeta->meta_mensual_base) + floatval($agenteMeta->saldo_meta_acumulado);
                if($metaRealMes < 0) $metaRealMes = 0;

                $ventasEsteMes = Pedido::where('is_cotizacion', 0)->where('user_id', $agenteMeta->id)->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->whereIn('estado', $estadosQueSuman)->sum('total');
                $porcentaje = $metaRealMes > 0 ? ($ventasEsteMes / $metaRealMes) * 100 : ($ventasEsteMes > 0 ? 100 : 0);

                $rendimientoAgentes->push([
                    'nombre' => $agenteMeta->name,
                    'meta' => $metaRealMes,
                    'ventas' => $ventasEsteMes,
                    'faltante' => max(0, $metaRealMes - $ventasEsteMes),
                    'porcentaje' => round($porcentaje, 1)
                ]);
                $metaGlobalEmpresa += $metaRealMes;
                $ventasGlobalesMesActual += $ventasEsteMes;
            }

            $data['rendimientoAgentes'] = $rendimientoAgentes->sortByDesc('porcentaje');
            $data['metaGlobalEmpresa'] = $metaGlobalEmpresa;
            $data['ventasGlobalesMesActual'] = $ventasGlobalesMesActual;
            $data['porcentajeGlobalEmpresa'] = $metaGlobalEmpresa > 0 ? ($ventasGlobalesMesActual / $metaGlobalEmpresa) * 100 : 0;

            // === NUEVO: HISTORIAL MENSUAL PARA EL ADMIN (Toda la historia) ===
            $ventasHistoricas = Pedido::select(
                'users.name as agente_nombre',
                DB::raw('DATE_FORMAT(pedidos.created_at, "%Y-%m") as mes_codigo'),
                DB::raw('SUM(pedidos.total) as total_venta')
            )
            ->join('users', 'pedidos.user_id', '=', 'users.id')
            ->where('pedidos.is_cotizacion', 0)
            ->whereIn('pedidos.estado', $estadosQueSuman)
            ->groupBy('users.id', 'users.name', 'mes_codigo')
            ->orderBy('mes_codigo', 'desc')
            ->get();

            $data['ventasMensualesAdmin'] = $ventasHistoricas->groupBy('agente_nombre');
            
            // Extraemos todos los meses únicos que existen en la base de datos (ordenados del más nuevo al más viejo)
            $mesesUnicos = $ventasHistoricas->pluck('mes_codigo')->unique()->sortDesc();
            $columnasMeses = [];
            foreach ($mesesUnicos as $codigo) {
                $fecha = Carbon::createFromFormat('Y-m', $codigo);
                $columnasMeses[$codigo] = ucfirst($fecha->translatedFormat('F Y'));
            }
            $data['columnasMeses'] = $columnasMeses;

            // ==========================
            // GRÁFICOS PARA EL ADMIN
            // ==========================
            // Quitado el límite de 12 meses
            $ventasGlobales = Pedido::select(DB::raw('SUM(total) as total_venta'), DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes_id'), DB::raw('DATE_FORMAT(created_at, "%b %Y") as mes_label'))->where('is_cotizacion', 0)->whereIn('estado', $estadosQueSuman)->groupBy('mes_id', 'mes_label')->orderBy('mes_id')->get();
            $data['chartVentasGlobales_labels_js'] = $ventasGlobales->pluck('mes_label')->toJson();
            $data['chartVentasGlobales_data_js'] = $ventasGlobales->pluck('total_venta')->toJson();

            $estadosString = "'" . implode("','", $estadosQueSuman) . "'";
            $todosLosAgentes = Pedido::select('users.name as agente_nombre', DB::raw('COUNT(pedidos.id) as total_pedidos'), DB::raw('SUM(pedidos.total) as venta_bruta'), DB::raw("SUM(CASE WHEN pedidos.estado IN ($estadosString) THEN pedidos.total ELSE 0 END) as venta_neta"), DB::raw("SUM(CASE WHEN pedidos.estado NOT IN ($estadosString) THEN pedidos.total ELSE 0 END) as venta_perdida"))->join('users', 'pedidos.user_id', '=', 'users.id')->where('pedidos.is_cotizacion', 0)->whereNotIn('pedidos.estado', $estadosIgnorados)->groupBy('users.id', 'users.name')->havingRaw('venta_bruta > 0')->orderBy('venta_neta', 'desc')->get();
            $data['todosLosAgentes'] = $todosLosAgentes;
            
            $topAgentes = $todosLosAgentes->take(10);
            $data['chartTopAgentes_labels_js'] = $topAgentes->pluck('agente_nombre')->toJson();
            $data['chartTopAgentes_data_js'] = $topAgentes->pluck('venta_neta')->toJson();

        } elseif ($user->hasRole('agente-ventas')) {
            // ==========================
            // KPIs PARA EL AGENTE
            // ==========================
            $data['misPedidos'] = Pedido::where('is_cotizacion', 0)->where('user_id', $user->id)->whereNotIn('estado', $estadosIgnorados)->count();
            $data['misVentas'] = Pedido::where('is_cotizacion', 0)->where('user_id', $user->id)->whereIn('estado', $estadosQueSuman)->sum('total');
            $data['misClientes'] = Cliente::where('user_id', $user->id)->count();
            $data['misPedidosPendientes'] = Pedido::where('is_cotizacion', 0)->where('user_id', $user->id)->where('estado', 'pendiente')->count();
            
            $misCotizaciones = Pedido::where('is_cotizacion', 1)->where('user_id', $user->id)->whereIn('estado', ['cotizacion', 'Cotizacion'])->whereDate('fecha_vencimiento', '>=', Carbon::now()->toDateString());
            $data['misCotizacionesMonto'] = (clone $misCotizaciones)->sum('total');
            $data['misCotizacionesCount'] = (clone $misCotizaciones)->count();
            $data['misDevoluciones'] = Pedido::where('user_id', $user->id)->whereIn('estado', ['devolucion_pendiente', 'devolucion_aprobada'])->count();
            $data['ultimosPedidos'] = Pedido::with('cliente')->where('is_cotizacion', 0)->where('user_id', $user->id)->whereNotIn('estado', $estadosIgnorados)->orderBy('created_at', 'desc')->take(5)->get();

            // ==========================
            // LÓGICA DE METAS - AGENTE
            // ==========================
            $miMetaBase = floatval($user->meta_mensual_base);
            $miSaldoAcumulado = floatval($user->saldo_meta_acumulado);
            
            $data['miMetaMesActual'] = $miMetaBase + $miSaldoAcumulado;
            if($data['miMetaMesActual'] < 0) $data['miMetaMesActual'] = 0;

            $data['misVentasEsteMes'] = Pedido::where('is_cotizacion', 0)
                                       ->where('user_id', $user->id)
                                       ->whereMonth('created_at', Carbon::now()->month)
                                       ->whereYear('created_at', Carbon::now()->year)
                                       ->whereIn('estado', $estadosQueSuman)
                                       ->sum('total');
                                       
            $data['miFaltanteMeta'] = max(0, $data['miMetaMesActual'] - $data['misVentasEsteMes']);
            $data['miPorcentajeMeta'] = $data['miMetaMesActual'] > 0 ? ($data['misVentasEsteMes'] / $data['miMetaMesActual']) * 100 : ($data['misVentasEsteMes'] > 0 ? 100 : 0);
            
            if($data['miPorcentajeMeta'] >= 100) $data['colorMeta'] = '#1cc88a'; // Verde
            elseif($data['miPorcentajeMeta'] >= 75) $data['colorMeta'] = '#f6c23e'; // Amarillo
            else $data['colorMeta'] = '#e74a3b'; // Rojo

            // ==========================
            // GRÁFICOS PARA EL AGENTE
            // ==========================
            // Quitado el límite de 12 meses
            $misVentasActual = Pedido::select(DB::raw('SUM(total) as total_venta'), DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes_id'), DB::raw('DATE_FORMAT(created_at, "%b %Y") as mes_label'))->where('is_cotizacion', 0)->where('user_id', $user->id)->whereIn('estado', $estadosQueSuman)->groupBy('mes_id', 'mes_label')->orderBy('mes_id')->get();
            $data['chartMiTendencia_labels_js'] = $misVentasActual->pluck('mes_label')->toJson();
            $data['chartMiTendencia_data_actual_js'] = $misVentasActual->pluck('total_venta')->toJson();
            
            // Tabla Histórica para el Agente (Usando los datos de arriba)
            $data['misVentasTabla'] = $misVentasActual->sortByDesc('mes_id');

            $misTopClientes = Pedido::select('clientes.nombre as cliente_nombre', DB::raw('SUM(pedidos.total) as total_venta'))->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')->where('pedidos.is_cotizacion', 0)->where('pedidos.user_id', $user->id)->whereIn('pedidos.estado', $estadosQueSuman)->whereMonth('pedidos.created_at', Carbon::now()->month)->groupBy('clientes.id', 'clientes.nombre')->orderBy('total_venta', 'desc')->limit(5)->get();
            $data['chartMisClientes_labels_js'] = $misTopClientes->pluck('cliente_nombre')->toJson();
            $data['chartMisClientes_data_js'] = $misTopClientes->pluck('total_venta')->toJson();
        }

        return view('dashboard', $data);
    }
}