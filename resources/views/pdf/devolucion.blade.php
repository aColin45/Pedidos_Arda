<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato Devolución #{{ $dev->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 0; }
        .header-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .logo { max-width: 150px; }
        .title { font-size: 18px; font-weight: bold; text-align: right; color: #333; }
        
        .info-box { width: 100%; margin-bottom: 20px; border: 1px solid #000; padding: 5px; }
        .info-row { margin-bottom: 5px; }
        .label { font-weight: bold; width: 100px; display: inline-block; }
        
        .products-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        .products-table th { background-color: #eee; border: 1px solid #000; padding: 5px; text-align: center; }
        .products-table td { border: 1px solid #000; padding: 5px; }
        
        /* Nuevo estilo para la caja de comentarios */
        .comments-box { width: 100%; border: 1px dashed #666; padding: 10px; margin-bottom: 30px; font-size: 11px; }
        .comments-title { font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }

        .signatures { margin-top: 50px; width: 100%; text-align: center; }
        .sig-box { width: 30%; display: inline-block; border-top: 1px solid #000; margin: 0 1.5%; padding-top: 5px; }
        
        .footer-note { margin-top: 30px; border: 2px solid #000; padding: 10px; font-weight: bold; text-align: center; background: #f9f9f9; }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <table class="header-table">
        <tr>
            <td width="30%">
                @if($logo)
                    <img src="{{ $logo }}" class="logo">
                @else
                    <h2>ARDA</h2>
                @endif
            </td>
            <td width="70%" class="title">
                FORMATO DE DEVOLUCIONES<br>
                <span style="font-size: 14px; color: #555;">Folio: {{ $dev->id }}</span>
            </td>
        </tr>
    </table>

    {{-- INFORMACIÓN GENERAL --}}
    <div class="info-box">
        <div class="info-row"><span class="label">Cliente:</span> {{ $dev->cliente->nombre }}</div>
        <div class="info-row"><span class="label">Nº Cliente:</span> {{ $dev->cliente->codigo }}</div>
        <div class="info-row"><span class="label">Agente:</span> {{ $dev->cliente->agente->name ?? 'N/A' }}</div>
        <div class="info-row"><span class="label">Fecha:</span> {{ $fecha->format('d/m/Y') }}</div>
    </div>

    {{-- TABLA DE PRODUCTOS (Estilo Excel) --}}
    <table class="products-table">
        <thead>
            <tr>
                <th>Motivo de la Devolución</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unit. (Antes de IVA)</th>
                <th>Factura a la que pertenece</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dev->detalles as $item)
            <tr>
                {{-- En el Excel el motivo va por fila, aquí lo repetimos o usamos el general --}}
                <td>{{ $motivo }}</td>
                <td style="text-align: center;">{{ $item->producto->codigo }}</td>
                <td>{{ $item->producto->nombre }}</td>
                <td style="text-align: center;">{{ $item->cantidad }}</td>
                <td style="text-align: right;">${{ number_format($item->precio, 2) }}</td>
                <td style="text-align: center;">{{ $factura }}</td>
            </tr>
            @endforeach
            {{-- Fila de Totales --}}
            <tr style="font-weight: bold; background-color: #eee;">
                <td colspan="3" style="text-align: right;">TOTAL:</td>
                <td style="text-align: center;">{{ $dev->detalles->sum('cantidad') }}</td>
                <td style="text-align: right;">${{ number_format($dev->total, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- NUEVA SECCIÓN DE COMENTARIOS / OBSERVACIONES --}}
    @php
        // Extraemos solo la observación final, quitando [FACTURA: XXX] [MOTIVO: XXX]
        $comentarioLimpio = $dev->comentarios;
        if(strpos($comentarioLimpio, '| Obs: ') !== false) {
            $partes = explode('| Obs: ', $comentarioLimpio);
            $comentarioLimpio = $partes[1] ?? 'Sin observaciones.';
        }
    @endphp
    
    <div class="comments-box">
        <div class="comments-title">Observaciones Adicionales:</div>
        <div>{{ $comentarioLimpio }}</div>
    </div>

    <br><br>

    {{-- SECCIÓN DE FIRMAS --}}
    <div class="signatures">
        <div class="sig-box">
            ENTREGÓ (CLIENTE)<br><br><br>
        </div>
        <div class="sig-box">
            RECIBIÓ (ARDA)<br><br><br>
        </div>
        <div class="sig-box">
            AUTORIZÓ<br><br><br>
        </div>
    </div>

    {{-- NOTA AL PIE (Cuadro del Excel) --}}
    <div class="footer-note">
        Recuadro para ser llenado por el personal de Bodega que recibe:<br>
        (FAVOR DE PONER FIRMA, NOMBRE Y FECHA MUY LEGIBLE)
        <br><br><br>
    </div>

</body>
</html>