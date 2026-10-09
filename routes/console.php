<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// --- LIBRERÍAS NUEVAS PARA EL CRON JOB ---
use Illuminate\Support\Facades\Schedule;
use App\Models\Pedido;
use App\Models\User;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =========================================================================
// CRON JOB 1: CADUCAR COTIZACIONES AUTOMÁTICAMENTE A LOS 10 DÍAS
// =========================================================================
Schedule::call(function () {
    Pedido::where('is_cotizacion', 1)
          ->where('estado', 'cotizacion')
          ->where('fecha_vencimiento', '<', Carbon::now()->toDateString())
          ->update([
              'estado' => 'caducada'
          ]);
})->daily();

// =========================================================================
// CRON JOB 2: CIERRE DE METAS MENSUALES (NUEVO ROBOT)
// Se ejecuta automáticamente el día 1 de cada mes a las 00:01 AM
// =========================================================================
Schedule::call(function () {
    // 1. Obtener el mes que acaba de terminar
    $mesAnterior = Carbon::now()->subMonth();
    $mes = $mesAnterior->month;
    $anio = $mesAnterior->year;

    $estadosQueSuman = [
        'pendiente', 'aprobado', 'parcialmente_surtido', 'enviado', 'enviado_completo', 'completado', 'entregado', 'finalizado'
    ];

    // 2. Traer a todos los agentes que tengan una meta asignada
    $agentes = User::where('meta_mensual_base', '>', 0)->get();

    foreach ($agentes as $agente) {
        $metaBase = floatval($agente->meta_mensual_base);
        $saldoAnterior = floatval($agente->saldo_meta_acumulado);
        
        // Meta real que debían cumplir en el mes que cerró
        $metaDelMesQueCerro = $metaBase + $saldoAnterior;
        if ($metaDelMesQueCerro < 0) {
            $metaDelMesQueCerro = 0;
        }

        // Ventas logradas en ese mes
        $ventasMesQueCerro = Pedido::where('is_cotizacion', 0)
                               ->where('user_id', $agente->id)
                               ->whereMonth('created_at', $mes)
                               ->whereYear('created_at', $anio)
                               ->whereIn('estado', $estadosQueSuman)
                               ->sum('total');

        // Matemáticas: si debía 100 y vendió 80, el nuevo saldo arrastrado es +20 (Déficit).
        $nuevoSaldo = $metaDelMesQueCerro - $ventasMesQueCerro;

        // Actualizamos al agente
        $agente->saldo_meta_acumulado = $nuevoSaldo;
        $agente->save();
    }
})->monthlyOn(1, '00:01')->name('cierre_metas_mensuales');