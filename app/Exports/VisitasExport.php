<?php

namespace App\Exports;

use App\Models\Visita;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;

class VisitasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        $user = Auth::user();
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            return Visita::with(['agente', 'cliente', 'prospecto'])->orderBy('fecha_visita', 'desc')->get();
        }
        return Visita::with(['cliente', 'prospecto'])->where('user_id', $user->id)->orderBy('fecha_visita', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Fecha Visita', 'Modo Contacto', 'Agente', 'Tipo', 'Negocio Visitado', 'Atendió', 'Cargo', 
            'Objetivo', 'Resultado', 'Motivo de Rechazo', 'Familias Interés', 'Productos Específicos', 
            'Marcas Competencia', 'Necesidades Detectadas', 'Monto ($)', 'Próximo Contacto', 'Acuerdos'
        ];
    }

    public function map($visita): array
    {
        $negocio = $visita->es_prospecto ? ($visita->prospecto->razon_social ?? 'N/A') : ($visita->cliente->nombre ?? 'N/A');
        $tipo = $visita->es_prospecto ? 'Prospecto' : 'Cliente';

        return [
            $visita->id,
            $visita->fecha_visita ? \Carbon\Carbon::parse($visita->fecha_visita)->format('d/m/Y H:i') : '',
            $visita->modo_contacto,
            $visita->agente->name ?? 'N/A',
            $tipo,
            $negocio,
            $visita->atendio_nombre,
            $visita->atendio_cargo,
            $visita->objetivo_visita,
            $visita->resultado_visita,
            $visita->motivo_rechazo,
            $visita->lineas_rotacion,
            $visita->productos_especificos,
            $visita->marcas_competencia,
            $visita->necesidades_detectadas,
            $visita->monto_generado,
            $visita->fecha_proximo_seguimiento ? \Carbon\Carbon::parse($visita->fecha_proximo_seguimiento)->format('d/m/Y') : '',
            $visita->acuerdos
        ];
    }
}