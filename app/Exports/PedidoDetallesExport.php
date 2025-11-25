<?php

namespace App\Exports;

use App\Models\PedidoDetalle; // Asegúrate que el namespace del modelo sea correcto
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PedidoDetallesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    // Propiedades para guardar los filtros
    protected $month;
    protected $year;
    protected $texto;

    /**
     * Constructor para recibir los filtros desde el controlador.
     * Pueden ser null si no se aplican.
     */
    public function __construct($month = null, $year = null, $texto = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->texto = $texto;
    }

    /**
     * Define la consulta base para obtener los detalles de pedido.
     * Aplica los filtros recibidos en el constructor.
     */
    public function query()
    {
        // Inicia la consulta con las relaciones necesarias cargadas (eager loading)
        $query = PedidoDetalle::query()
            ->with([
                'pedido.cliente', // Carga el cliente asociado al pedido
                'pedido.agente',  // Carga el agente asociado al pedido
                'producto'        // Carga el producto de este detalle
            ]);

        // Aplica los filtros usando whereHas sobre la relación 'pedido'
        $query->whereHas('pedido', function ($q) {
            // Filtrar por mes si se proporcionó
            if (!empty($this->month)) {
                $q->whereMonth('created_at', $this->month);
            }
            // Filtrar por año si se proporcionó
            if (!empty($this->year)) {
                $q->whereYear('created_at', $this->year);
            }
            // Filtrar por texto si se proporcionó (busca en nombre de agente o cliente)
            if (!empty($this->texto)) {
                // Usamos una subconsulta para agrupar las condiciones OR
                $q->where(function($subQuery) {
                     $subQuery->whereHas('agente', function ($agentQuery) {
                         $agentQuery->where('name', 'like', "%{$this->texto}%");
                     })->orWhereHas('cliente', function ($clientQuery) {
                         $clientQuery->where('nombre', 'like', "%{$this->texto}%");
                     });
                });
            }
        });

        // Ordena los resultados por el ID del pedido (descendente)
        $query->orderBy('pedido_id', 'desc');

        // Retorna la consulta construida
        return $query;
    }

    /**
     * Define los encabezados de las columnas para el archivo Excel.
     */
    public function headings(): array
    {
        // Devuelve un array con los nombres de las columnas
        return [
            'ID Pedido',
            'Fecha Pedido',
            'Estado Pedido',
            'Cliente',
            'Código Cliente',
            'Agente Creador',
            'Código Producto',
            'Nombre Producto',
            'Cantidad',
            'Precio Unitario',
            'Inner',
            'Subtotal Línea',
            'Total Pedido',
            'Comentarios Pedido',
            'Flete Pagado', 
        ];
    }

    /**
     * Mapea los datos de cada detalle de pedido al formato deseado para cada fila del Excel.
     * @param PedidoDetalle $detalle La instancia del modelo PedidoDetalle.
     */
    public function map($detalle): array
    {
        // Devuelve un array con los valores para cada columna, en el mismo orden que headings()
        return [
            $detalle->pedido->id ?? 'N/A',
            // Formatear la fecha
            $detalle->pedido->created_at ? $detalle->pedido->created_at->format('d-m-Y') : 'N/A',
            // Poner en mayúscula la primera letra del estado
            ucfirst($detalle->pedido->estado ?? 'N/A'),
            $detalle->pedido->cliente->nombre ?? 'N/A',
            $detalle->pedido->cliente->codigo ?? 'N/A',
            $detalle->pedido->agente->name ?? 'N/A',
            $detalle->producto->codigo ?? 'N/A',
            $detalle->producto->nombre ?? 'N/A',
            $detalle->cantidad,
            $detalle->precio,
            $detalle->inner ?? 1, // Usar 1 si inner es null
            $detalle->subtotal,
            $detalle->pedido->total ?? 'N/A',
            $detalle->pedido->comentarios ?? '', // Dejar vacío si no hay comentarios
            // Convertir el valor booleano de flete_pagado a 'Sí' o 'No'
            ($detalle->pedido->flete_pagado ?? false) ? 'Sí' : 'No',
        ];
    }
}