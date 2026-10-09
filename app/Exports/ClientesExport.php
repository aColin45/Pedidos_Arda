<?php

namespace App\Exports;

use App\Models\Cliente;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClientesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $texto;

    // Recibimos el texto de búsqueda para exportar solo lo que el usuario filtró (o todo si está vacío)
    public function __construct($texto = null)
    {
        $this->texto = $texto;
    }

    public function query()
    {
        // Preparamos la consulta incluyendo al Agente (user)
        $query = Cliente::query()->with('agente');

        if (!empty($this->texto)) {
            $query->where(function($q) {
                $q->where('nombre', 'like', "%{$this->texto}%")
                  ->orWhere('codigo', 'like', "%{$this->texto}%")
                  ->orWhereHas('agente', function ($subQ) {
                      $subQ->where('name', 'like', "%{$this->texto}%");
                  });
            });
        }

        return $query->orderBy('nombre', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'Nombre Cliente',
            'Lista de Precios',     
            'Teléfono',
            'Email',
            'Dirección',
            'Estado (Región)',      // <--- NUEVA COLUMNA AQUÍ
            'Descuento (%)',
            'Agente Asignado',
            'Estatus',              // <--- RENOMBRADO (Antes decía Estado)
            'Fecha Registro'
        ];
    }

    public function map($cliente): array
    {
        return [
            $cliente->id,
            $cliente->codigo,
            $cliente->nombre,
            $cliente->contacto,
            $cliente->telefono,
            $cliente->email,
            $cliente->direccion,
            $cliente->estado ?? 'N/A', // <--- AQUÍ MANDAMOS EL ESTADO (NUEVO LEON, MICHOACAN, ETC)
            $cliente->descuento . '%',
            $cliente->agente->name ?? 'Sin Asignar', 
            $cliente->activo ? 'Activo' : 'Inactivo',
            $cliente->created_at->format('d/m/Y'),
        ];
    }
}