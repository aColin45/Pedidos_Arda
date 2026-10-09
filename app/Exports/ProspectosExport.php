<?php

namespace App\Exports;

use App\Models\Prospecto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;

class ProspectosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        $user = Auth::user();
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            return Prospecto::with('agente')->orderBy('id', 'desc')->get();
        }
        return Prospecto::where('user_id', $user->id)->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Agente', 'Razón Social', 'Nombre Comercial', 'RFC', 'Giro', 
            'Contacto', 'Puesto', 'Teléfono', 'Celular', 'Email', 
            'Estado Geográfico', 'Origen', 'Fecha Primer Contacto', 'Estatus', 
            'Motivo de Descarte'
        ];
    }

    public function map($prospecto): array
    {
        return [
            $prospecto->id,
            $prospecto->agente->name ?? 'N/A',
            $prospecto->razon_social,
            $prospecto->nombre_comercial,
            $prospecto->rfc,
            $prospecto->giro_negocio,
            $prospecto->contacto_nombre,
            $prospecto->contacto_puesto,
            $prospecto->telefono,
            $prospecto->celular,
            $prospecto->email,
            $prospecto->estado,
            $prospecto->origen_prospecto,
            $prospecto->fecha_primer_contacto ? $prospecto->fecha_primer_contacto->format('d/m/Y') : '',
            strtoupper(str_replace('_', ' ', $prospecto->estatus)),
            $prospecto->motivo_descarte // <-- IMPRIMIMOS EL MOTIVO
        ];
    }
}