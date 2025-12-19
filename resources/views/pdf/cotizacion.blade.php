<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización</title>
    <style>
        /* CONFIGURACIÓN GENERAL */
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        
        /* ENCABEZADO */
        .header { width: 100%; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-width: 180px; height: auto; display: block; }
        .company-info { text-align: right; font-size: 10px; line-height: 1.4; vertical-align: top; }
        
        /* INFO CLIENTE */
        .client-info { background-color: #f4f4f4; padding: 10px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ddd; }
        
        /* TABLA DE PRODUCTOS */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #0056b3; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { border-bottom: 1px solid #ddd; padding: 8px; font-size: 11px; }
        
        /* COMENTARIOS */
        .comments-section { border: 1px solid #ddd; background-color: #fffde7; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-size: 11px; }

        /* UTILIDADES */
        .text-right { text-align: right; }
        .badge { background: #eee; padding: 2px 4px; border-radius: 3px; font-size: 9px; color: #555; }
        
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
                    <h2 style="color: #0056b3; margin: 0;">GRUPO INDUSTRIAL ARDA</h2>
                @endif
                <span style="font-size: 14px; font-weight: bold; color: #555; display:block; margin-top:10px;">COTIZACIÓN</span>
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

    {{-- 2. INFORMACIÓN DEL CLIENTE --}}
    <div class="client-info">
        <table style="margin:0; width: 100%;">
            <tr>
                <td style="border:none; width: 60%; vertical-align: top;">
                    <strong>CLIENTE:</strong> {{ $cliente->nombre }}<br>
                    <strong>CÓDIGO:</strong> {{ $cliente->codigo }}<br>
                    @if($cliente->email) <strong>EMAIL:</strong> {{ $cliente->email }}<br> @endif
                    @if($cliente->telefono) <strong>TEL:</strong> {{ $cliente->telefono }} @endif
                </td>
                <td style="border:none; width: 40%; text-align: right; vertical-align: top;">
                    <strong>FECHA:</strong> {{ $fecha->format('d/m/Y') }}<br>
                    {{-- CAMBIO DE HORA A FORMATO AM/PM --}}
                    {{-- <strong>HORA:</strong> {{ $fecha->format('h:i A') }}<br> --}}
                    <strong>AGENTE:</strong> {{ $usuario->name ?? 'Ventas' }}<br>
                    <strong>VIGENCIA:</strong> 5 días hábiles
                </td>
            </tr>
        </table>
    </div>

    {{-- 3. TABLA DE PRODUCTOS --}}
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
            @foreach($carrito as $item)
            <tr>
                <td>{{ $item['codigo'] }}</td>
                <td>
                    {{ $item['nombre'] }}
                    @if(!($item['aplica_iva'] ?? true)) 
                        <br><span class="badge">*Exento IVA</span> 
                    @endif
                </td>
                <td class="text-right">${{ number_format($item['precio'], 2) }}</td>
                <td class="text-right">{{ $item['cantidad'] }}</td>
                <td class="text-right">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- NUEVA SECCIÓN: COMENTARIOS --}}
    @if(isset($comentarios) && !empty($comentarios))
    <div class="comments-section">
        <strong>OBSERVACIONES / COMENTARIOS:</strong><br>
        {{ $comentarios }}
    </div>
    @endif

    {{-- 4. TOTALES --}}
    <div class="totals">
        <table style="width: 100%;">
            <tr>
                <td class="text-right"><strong>Subtotal Bruto:</strong></td>
                <td class="text-right">${{ number_format($subtotal_bruto, 2) }}</td>
            </tr>
            @if($descuento_porcentaje > 0)
            <tr>
                <td class="text-right" style="color: #d9534f;">Descuento ({{ $descuento_porcentaje }}%):</td>
                <td class="text-right" style="color: #d9534f;">-${{ number_format($monto_descuento, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="text-right">Subtotal Neto:</td>
                <td class="text-right">${{ number_format($subtotal_gravable + $subtotal_exento, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right">IVA (16%):</td>
                <td class="text-right">${{ number_format($monto_iva, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right" style="font-size: 14px; border-top: 2px solid #333; padding-top: 5px;"><strong>TOTAL:</strong></td>
                <td class="text-right" style="font-size: 14px; border-top: 2px solid #333; padding-top: 5px;"><strong>${{ number_format($total_final, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    {{-- 5. NOTAS AL PIE --}}
    <div style="margin-top: 50px; font-size: 10px; color: #555;">
        <p><strong>Términos y Condiciones:</strong></p>
        <ul style="padding-left: 20px;">
            <li>Precios en Moneda Nacional (MXN) sujetos a cambio sin previo aviso.</li>
            <li>Esta cotización es informativa y no representa una reserva de inventario.</li>
            <li>Tiempo de entrega sujeto a disponibilidad en almacén al momento de la compra.</li>
            <li>Para realizar su pedido, favor de contactar a su agente de ventas.</li>
        </ul>
    </div>

    {{-- 6. PIE DE PÁGINA --}}
    <div class="footer">
        Grupo Industrial ARDA S.A de C.V. Todos los derechos reservados. | Pedidos - ARDA<br>
        https://agentes.arda.com.mx/
    </div>
</body>
</html>