<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\PedidoDetallesExport; 

class DashboardController extends Controller
{
public function exportarPedidosExcel(Request $request)
{
    // Doble chequeo de seguridad (aunque la ruta ya lo tiene)
    if (!auth()->user()->hasRole('admin')) {
        abort(403, 'Acción no autorizada.');
    }

    // Obtenemos los filtros de la URL (query parameters)
    $month = $request->query('month');
    $year = $request->query('year');
    $texto = $request->query('texto');

    $fecha = now()->format('d-m-Y_H-i');
    $fileName = "reporte_pedidos_arda_{$fecha}.xlsx";

    // Pasamos los filtros (pueden ser null) al constructor del Export
    return Excel::download(new PedidoDetallesExport($month, $year, $texto), $fileName);
}
    
    public function index()
{
    $user = Auth::user();
    $data = [];

    if ($user->hasRole('admin')) {
        // ==========================
        // LÓGICA PARA EL ADMIN
        // ==========================
        // El Admin ve todo
        $data['totalPedidos'] = Pedido::count();
        $data['totalVentas'] = Pedido::whereIn('estado', ['pendiente', 'enviado'])->sum('total');
        $data['totalAgentes'] = User::role('agente-ventas')->count();
        $data['totalClientes'] = Cliente::count();
        $data['ultimosPedidos'] = Pedido::with('agente', 'cliente')
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get();

    } elseif ($user->hasRole('agente-ventas')) {
        // ==========================
        // LÓGICA PARA EL AGENTE
        // ==========================
        // El Agente ve solo lo suyo
        $queryPedidos = Pedido::where('user_id', $user->id);

        $data['misPedidos'] = $queryPedidos->count();
        $data['misVentas'] = $queryPedidos->whereIn('estado', ['pendiente', 'enviado'])->sum('total');
        $data['misClientes'] = Cliente::where('user_id', $user->id)->count();
        $data['misPedidosPendientes'] = Pedido::where('user_id', $user->id)
                                              ->where('estado', 'pendiente')
                                              ->count();
        $data['ultimosPedidos'] = Pedido::where('user_id', $user->id)
                                        ->with('cliente')
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get();
    }

    // Enviamos los datos a la vista 'dashboard'
    return view('dashboard', $data);
}
}
