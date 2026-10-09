<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización</title>
    <style>
        /* CONFIGURACIÓN GENERAL - Letra escalada a 14px */
        body { font-family: sans-serif; font-size: 14px; color: #333; margin: 0; padding: 0; }
        
        /* ENCABEZADO */
        .header { width: 100%; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-width: 180px; height: auto; display: block; }
        
        /* INFO EMPRESA - Subió a 12px */
        .company-info { text-align: right; font-size: 12px; line-height: 1.4; vertical-align: top; }
        
        /* INFO CLIENTE */
        .client-info { background-color: #f4f4f4; padding: 12px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ddd; }
        
        /* TABLA DE PRODUCTOS - Textos y celdas más amplias (13px) */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #0056b3; color: white; padding: 10px 8px; text-align: left; font-size: 13px; }
        td { border-bottom: 1px solid #ddd; padding: 10px 8px; font-size: 13px; }
        
        /* COMENTARIOS */
        .comments-section { border: 1px solid #ddd; background-color: #fffde7; padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 13px; }

        /* UTILIDADES */
        .text-right { text-align: right; }
        .badge { background: #eee; padding: 3px 6px; border-radius: 3px; font-size: 11px; color: #555; }
        
        /* SECCIÓN DE TOTALES */
        .totals { width: 50%; float: right; margin-top: 0px; }
        .totals table tr td { border: none; padding: 4px 5px; }
        
        /* PIE DE PÁGINA - Subió a 11px */
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 11px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
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
                {{-- CAMBIO DE TÍTULO - Subió a 18px --}}
                <span style="font-size: 18px; font-weight: bold; color: #555; display:block; margin-top:12px;">COTIZACIÓN</span>
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
                    @if($cliente->telefono) <strong>TEL:</strong> {{ $cliente->telefono }}<br> @endif
                    
                    {{-- DIRECCIÓN DE ENTREGA --}}
                    @if($pedido->direccion_entrega)
                        <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #ccc;">
                            <strong style="color: #0056b3;">DIRECCIÓN DE ENTREGA:</strong><br>
                            {{ $pedido->direccion_entrega }}
                        </div>
                    @endif
                </td>
                <td style="border:none; width: 40%; text-align: right; vertical-align: top;">
                    <strong>FECHA:</strong> {{ $fecha->format('d/m/Y') }}<br>
                    <strong>AGENTE:</strong> {{ $usuario->name ?? 'Ventas' }}<br>
                    <strong>VIGENCIA:</strong> 6 días
                </td>
            </tr>
        </table>
    </div>

    {{-- 3. TABLA DE PRODUCTOS --}}
    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 15%">CÓDIGO</th>
                <th style="width: 35%">DESCRIPCIÓN</th>
                <th style="width: 15%" class="text-right">PRECIO LISTA</th>
                <th style="width: 15%" class="text-right">PRECIO C/ DESC.</th>
                <th style="width: 5%" class="text-right">CANT</th>
                <th style="width: 15%" class="text-right">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($carrito as $item)
            @php
                $precio_unitario = $item['precio'];
                $precio_desc = $precio_unitario - ($precio_unitario * ($descuento_porcentaje / 100));
                $importe_linea = $precio_desc * $item['cantidad'];
            @endphp
            <tr>
                <td>{{ $item['codigo'] }}</td>
                <td>
                    {{ $item['nombre'] }}
                    @if(!($item['aplica_iva'] ?? true)) 
                        <br><span class="badge">*Exento IVA</span> 
                    @endif
                </td>
                <td class="text-right" style="text-decoration: line-through; color: #888;">${{ number_format($precio_unitario, 2) }}</td>
                <td class="text-right" style="color: #28a745; font-weight: bold;">${{ number_format($precio_desc, 2) }}</td>
                <td class="text-right">{{ $item['cantidad'] }}</td>
                <td class="text-right">${{ number_format($importe_linea, 2) }}</td>
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
                {{-- Totales subieron a 18px --}}
                <td class="text-right" style="font-size: 18px; border-top: 2px solid #333; padding-top: 5px;"><strong>TOTAL:</strong></td>
                <td class="text-right" style="font-size: 18px; border-top: 2px solid #333; padding-top: 5px;"><strong>${{ number_format($total_final, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    {{-- 5. NOTAS AL PIE - Subió a 12px --}}
    <div style="margin-top: 50px; font-size: 12px; color: #555;">
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