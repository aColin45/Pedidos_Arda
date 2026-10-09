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
            
            // ====================================================================
            // ---> AQUÍ AGREGAMOS EL BLINDAJE PARA IGNORAR COTIZACIONES <---
            // ====================================================================
            $q->where('is_cotizacion', 0)
              ->whereNotIn('estado', ['cotizacion', 'Cotizacion', 'COTIZACION']);
            // ====================================================================

            if (!empty($this->month)) {
                $q->whereMonth('created_at', $this->month);
            }
            if (!empty($this->year)) {
                $q->whereYear('created_at', $this->year);
            }
            if (!empty($this->texto)) {
                $q->where(function ($subQ) {
                    $subQ->where('id', 'like', "%{$this->texto}%") // Buscar por ID de Pedido
                         ->orWhereHas('cliente', function ($clienteQ) {
                             $clienteQ->where('nombre', 'like', "%{$this->texto}%")
                                      ->orWhere('codigo', 'like', "%{$this->texto}%"); // También por código de cliente
                         })
                         ->orWhereHas('agente', function ($agenteQ) {
                             $agenteQ->where('name', 'like', "%{$this->texto}%");
                         })
                         // --- NUEVO: Búsqueda por producto ---
                         ->orWhereHas('detalles.producto', function ($productoQ) {
                             $productoQ->where('codigo', 'like', "%{$this->texto}%")
                                       ->orWhere('nombre', 'like', "%{$this->texto}%");
                         });
                });
            }
        });

        return $query;
    }

    /**
     * Define los encabezados de las columnas en el Excel.
     */
    public function headings(): array
    {
        return [
            'ID Alterno',
            'Fecha Documento',
            'Estado Pedido',
            'Razón Social',
            'Código Cliente',
            'Lista de Precios',  
            'Agente Creador',
            'Precio Lista', 
            'Inner',
            'Detalle - Cantidad',
            'Detalle - Clave',
            'Nombre Producto',
            'Detalle - Precio Unitario', 
            'Total Pedido',
            'Detalle - Impuesto',
            'Comentarios Pedido',
            'Flete Pagado',
        ];
    }

    /**
     * Mapea los datos de cada fila para el Excel.
     */
    public function map($detalle): array
    {
        // Obtener el porcentaje de descuento del cliente (ej. 40)
        $porcentajeDesc = $detalle->pedido->cliente->descuento ?? 0;

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
            $detalle->pedido->cliente->contacto ?? 'N/A',
            $detalle->pedido->agente->name ?? 'N/A',
            
            $precioLista,           
            $detalle->inner ?? 1,   
            $detalle->cantidad,     
            $detalle->producto->codigo ?? 'N/A', 
            $detalle->producto->nombre ?? 'N/A', 
            $precioUnitarioNeto,    
            
            $detalle->pedido->total ?? 0,
            $tipoImpuesto, 
            $detalle->pedido->comentarios ?? '', 
            'No' 
        ];
    }
}