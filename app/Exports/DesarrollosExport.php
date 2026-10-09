<?php

namespace App\Exports;

use App\Models\SolicitudDesarrollo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;

class DesarrollosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        $user = Auth::user();
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            return SolicitudDesarrollo::with('agente')->orderBy('id', 'desc')->get();
        }
        return SolicitudDesarrollo::with('agente')->where('user_id', $user->id)->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Fecha Solicitud', 'Agente', 'Tipo de Acción', 'Línea Afectada', 
            'Producto Específico', 'Descripción / Característica', 'Justificación', 
            'Origen', 'Cliente Referencia', 'Estatus'
        ];
    }

    public function map($registro): array
    {
        return [
            $registro->id,
            $registro->created_at ? $registro->created_at->format('d/m/Y') : '',
            $registro->agente->name ?? 'N/A',
            $registro->tipo_accion, 
            $registro->linea_producto,
            $registro->producto_especifico,
            $registro->descripcion,
            $registro->justificacion,
            $registro->origen_necesidad,
            $registro->cliente_referencia,
            strtoupper(str_replace('_', ' ', $registro->estatus))
        ];
    }
}