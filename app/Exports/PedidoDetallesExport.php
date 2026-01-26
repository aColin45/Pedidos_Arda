<?php

namespace App\Exports;

use App\Models\PedidoDetalle;
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
     */
    public function __construct($month = null, $year = null, $texto = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->texto = $texto;
    }

    /**
     * Define la consulta base para obtener los detalles de pedido.
     */
    public function query()
    {
        $query = PedidoDetalle::query()
            ->with([
                'pedido.cliente', 
                'pedido.agente',  
                'producto'        
            ]);

        // Aplica los filtros usando whereHas sobre la relación 'pedido'
        $query->whereHas('pedido', function ($q) {
            if (!empty($this->month)) {
                $q->whereMonth('created_at', $this->month);
            }
            if (!empty($this->year)) {
                $q->whereYear('created_at', $this->year);
            }
            if (!empty($this->texto)) {
                $q->where(function($subQuery) {
                     $subQuery->whereHas('agente', function ($agentQuery) {
                         $agentQuery->where('name', 'like', "%{$this->texto}%");
                     })->orWhereHas('cliente', function ($clientQuery) {
                         $clientQuery->where('nombre', 'like', "%{$this->texto}%");
                     });
                });
            }
        });

        $query->orderBy('pedido_id', 'desc');

        return $query;
    }

    /**
     * Define los encabezados de las columnas para el archivo Excel.
     */
    public function headings(): array
    {
        return [
            'ID Alterno',       // ID Pedido
            'Fecha Documento',     // Fecha Pedido
            'Estado Pedido',
            'Razón Social',            // Cliente
            'Código Cliente',
            'Agente Creador',
            'Precio Lista',      // El precio original
            'Inner',
            'Detalle - Cantidad',        // Cantidad
            'Detalle - Clave',       // Clave Producto
            'Nombre Producto',
            'Detalle - Precio Unitario',   // El precio ya con descuento   (Precio Unitario)
            'Total Pedido',
            'Detalle - Impuesto',     // IVA 16% o IVA 0%          (Tipo Impuesto)
            'Comentarios Pedido',
            'Flete Pagado', 
        ];
    }

    /**
     * Mapea los datos de cada detalle de pedido al formato deseado.
     */
    public function map($detalle): array
    {
        // --- 1. CÁLCULOS ---
        
        // Cálculo del porcentaje
        $subtotalPedido = $detalle->pedido->subtotal ?? 0;
        $descuentoMonto = $detalle->pedido->descuento_aplicado ?? 0;
        $porcentajeDesc = 0;

        if ($subtotalPedido > 0 && $descuentoMonto > 0) {
            $porcentajeDesc = ($descuentoMonto / $subtotalPedido) * 100;
        }
        $porcentajeDesc = round($porcentajeDesc);

        // Cálculo de precios
        $precioLista = $detalle->precio; 
        $precioUnitarioNeto = $precioLista - ($precioLista * ($porcentajeDesc / 100));

        // Determinar Impuesto
        $aplicaIva = true; 
        if ($detalle->producto) {
            $aplicaIva = $detalle->producto->aplica_iva;
        } else {
            $aplicaIva = $detalle->aplica_iva;
        }
        
        // --- CAMBIO SOLICITADO: IVA 0% ---
        $tipoImpuesto = $aplicaIva ? 'IVA 16%' : 'IVA 0%';


        // --- 2. RETORNO DE DATOS ---
        return [
            $detalle->pedido->id ?? 'N/A',
            $detalle->pedido->created_at ? $detalle->pedido->created_at->format('d-m-Y') : 'N/A',
            ucfirst($detalle->pedido->estado ?? 'N/A'),
            $detalle->pedido->cliente->nombre ?? 'N/A',
            $detalle->pedido->cliente->codigo ?? 'N/A',
            $detalle->pedido->agente->name ?? 'N/A',
            
            $precioLista,           
            $detalle->inner ?? 1,   
            $detalle->cantidad,     
            $detalle->producto->codigo ?? 'N/A', 
            $detalle->producto->nombre ?? 'N/A', 
            $precioUnitarioNeto,    
            
            $detalle->pedido->total ?? 'N/A', 
            
            $tipoImpuesto, // Aquí saldrá "IVA 16%" o "IVA 0%"
            $detalle->pedido->comentarios ?? '',
            ($detalle->pedido->flete_pagado ?? false) ? 'Sí' : 'No',
        ];
    }
}