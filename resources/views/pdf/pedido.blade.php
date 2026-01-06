<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        /* CONFIGURACIÓN GENERAL */
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        
        /* ENCABEZADO - CAMBIADO A VERDE PARA DIFERENCIAR DE COTIZACIÓN */
        .header { width: 100%; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-bottom: 20px; }
        
        /* LOGO */
        .logo { max-width: 180px; height: auto; display: block; }
        
        /* INFO EMPRESA */
        .company-info { text-align: right; font-size: 10px; line-height: 1.4; vertical-align: top; }
        
        /* INFO CLIENTE */
        .client-info { background-color: #f4f4f4; padding: 10px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ddd; }
        
        /* RASTREO / GUÍAS (NUEVO) */
        .tracking-box { border: 1px solid #17a2b8; background-color: #e3f2fd; padding: 8px; margin-bottom: 15px; border-radius: 4px; font-size: 11px; }

        /* TABLA DE PRODUCTOS - ENCABEZADO VERDE */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #28a745; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { border-bottom: 1px solid #ddd; padding: 8px; font-size: 11px; }
        
        /* COMENTARIOS */
        .comments-section { border: 1px solid #ddd; background-color: #fffde7; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-size: 11px; }

        /* UTILIDADES */
        .text-right { text-align: right; }
        .badge { background: #eee; padding: 2px 4px; border-radius: 3px; font-size: 9px; color: #555; }
        .text-success { color: #28a745; font-weight: bold; }
        
        /* SECCIÓN DE TOTALES */
        .totals { width: 45%; float: right; margin-top: 0px; }
        .totals table tr td { border: none; padding: 3px 5px; }
        
        /* PIE DE PÁGINA */
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
        .clearfix { clear: both; }
    </style>
</head>
<body>

    {{-- 1. ENCABEZADO --}}
    <table class="header">
        <tr>
            <td style="border:none; width: 50%;">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo" alt="Grupo Arda">
                @else
                    <h2 style="color: #28a745; margin: 0;">GRUPO INDUSTRIAL ARDA</h2>
                @endif
                {{-- CAMBIO DE TÍTULO --}}
                <span style="font-size: 14px; font-weight: bold; color: #555; display:block; margin-top:10px;">ORDEN DE COMPRA #{{ $pedido->id }}</span>
            </td>
            <td style="border:none; width: 50%;" class="company-info">
                <strong>GRUPO INDUSTRIAL ARDA S.A. de C.V.</strong><br>
                RFC: GIA170620163<br>
                Circuito de la Industria Norte, Calle #32<br>
                Lerma, Estado de México, CP 52004<br>
                Tel: (728) 282-4148 Ext. 116<br>
                Facturación y Almacenes: rdavila@arda.com.mx
            </td>
        </tr>
    </table>

    {{-- 2. INFORMACIÓN DEL CLIENTE Y PEDIDO --}}
    <div class="client-info">
        <table style="margin:0; width: 100%;">
            <tr>
                <td style="border:none; width: 60%; vertical-align: top;">
                    <strong>CLIENTE:</strong> {{ $pedido->cliente->nombre }}<br>
                    <strong>CÓDIGO:</strong> {{ $pedido->cliente->codigo }}<br>
                    @if($pedido->cliente->email) <strong>EMAIL:</strong> {{ $pedido->cliente->email }}<br> @endif
                    @if($pedido->cliente->telefono) <strong>TEL:</strong> {{ $pedido->cliente->telefono }} @endif
                </td>
                <td style="border:none; width: 40%; text-align: right; vertical-align: top;">
                    <strong>FECHA PEDIDO:</strong> {{ $pedido->created_at->format('d/m/Y h:i A') }}<br>
                    <strong>ESTADO:</strong> {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}<br>
                    <strong>AGENTE:</strong> {{ $pedido->agente->name ?? 'N/A' }}<br>
                    <strong>FLETE PAGADO:</strong> {{ $pedido->flete_pagado ? 'SÍ' : 'NO' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- 3. INFORMACIÓN DE RASTREO (NUEVO BLOQUE) --}}
    @if($pedido->guia_parcial || $pedido->guia_completa)
    <div class="tracking-box">
        <table style="width: 100%; border: none; margin: 0;">
            <tr>
                <td style="border: none; padding: 0;"><strong>INFORMACIÓN DE ENVÍO:</strong></td>
                @if($pedido->guia_parcial)
                    <td style="border: none; padding: 0; text-align: right;">Guía Parcial: <b>{{ $pedido->guia_parcial }}</b></td>
                @endif
                @if($pedido->guia_completa)
                    <td style="border: none; padding: 0; text-align: right;">Guía Completa: <b class="text-success">{{ $pedido->guia_completa }}</b></td>
                @endif
            </tr>
        </table>
    </div>
    @endif

    {{-- 4. TABLA DE PRODUCTOS (Iteramos sobre detalles de BD) --}}
    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 15%">CÓDIGO</th>
                <th style="width: 40%">DESCRIPCIÓN</th>
                <th style="width: 15%" class="text-right">PRECIO UNITARIO</th>
                <th style="width: 10%" class="text-right">CANTIDAD</th>
                <th style="width: 20%" class="text-right">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->codigo ?? 'N/A' }}</td>
                <td>
                    {{ $detalle->producto->nombre ?? 'Producto Eliminado' }}
                    @if(!($detalle->aplica_iva)) 
                        <br><span class="badge">*Exento IVA</span> 
                    @endif
                </td>
                <td class="text-right">${{ number_format($detalle->precio, 2) }}</td>
                <td class="text-right">{{ $detalle->cantidad }}</td>
                <td class="text-right">${{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 5. COMENTARIOS --}}
    @if($pedido->comentarios_limpios)
    <div class="comments-section">
        <strong>OBSERVACIONES / COMENTARIOS:</strong><br>
        {{ $pedido->comentarios_limpios }}
    </div>
    @endif

    {{-- 6. TOTALES --}}
    <div class="totals">
        <table style="width: 100%;">
            <tr>
                <td class="text-right"><strong>Subtotal Bruto:</strong></td>
                <td class="text-right">${{ number_format($pedido->subtotal, 2) }}</td>
            </tr>
            @if($pedido->descuento_aplicado > 0)
            <tr>
                <td class="text-right" style="color: #d9534f;">Descuento:</td>
                <td class="text-right" style="color: #d9534f;">-${{ number_format($pedido->descuento_aplicado, 2) }}</td>
            </tr>
            @endif
            {{-- Puedes agregar subtotal neto si lo guardas o calcularlo aquí --}}
            <tr>
                <td class="text-right">IVA (16%):</td>
                <td class="text-right">${{ number_format($pedido->iva, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right" style="font-size: 14px; border-top: 2px solid #333; padding-top: 5px;"><strong>TOTAL:</strong></td>
                <td class="text-right" style="font-size: 14px; border-top: 2px solid #333; padding-top: 5px;"><strong>${{ number_format($pedido->total, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    {{-- 7. NOTAS AL PIE (Diferentes a cotización) --}}
    <div style="margin-top: 50px; font-size: 10px; color: #555;">
        <p><strong>Información Importante:</strong></p>
        <ul style="padding-left: 20px;">
            <li>Este documento es un comprobante de orden de compra confirmada.</li>
            <li>Para cualquier duda sobre el estatus de su envío, favor de proporcionar el número de pedido: <strong>#{{ $pedido->id }}</strong>.</li>
        </ul>
    </div>

    {{-- 8. PIE DE PÁGINA --}}
    <div class="footer">
        Grupo Industrial ARDA S.A de C.V. Todos los derechos reservados. | Pedidos - ARDA<br>
        https://agentes.arda.com.mx/
    </div>

</body>
</html>