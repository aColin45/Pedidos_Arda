<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Desarrollo</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .header { background-color: #2c3e50; color: white; padding: 10px; text-align: center; font-size: 16px; font-weight: bold; }
        .section-title { background-color: #ecf0f1; border-left: 4px solid #f1c40f; padding: 5px 10px; margin-top: 15px; font-weight: bold; font-size: 13px;}
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
        .label { font-weight: bold; background-color: #f9f9f9; width: 25%; color: #555;}
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #7f8c8d; }
        .badge-status { padding: 5px 10px; font-weight: bold; border-radius: 4px; text-transform: uppercase; }
    </style>
</head>
<body>

    <div style="text-align: center; margin-bottom: 20px;">
        @if(isset($logoBase64) && $logoBase64 != null)
            <img src="{{ $logoBase64 }}" style="width: 120px; height: auto; margin-bottom: 10px;">
        @endif
        <h2 style="margin: 0; color: #2c3e50; font-size: 20px;">GRUPO INDUSTRIAL ARDA S.A. DE C.V.</h2>
    </div>

    <div class="header">
        SOLICITUD DE DESARROLLO / PRODUCTO NUEVO #{{ $registro->id }}
    </div>

    <div class="section-title">Datos Administrativos</div>
    <table>
        <tr>
            <td class="label">Solicitante (Agente):</td>
            <td>{{ $registro->agente->name ?? 'N/A' }}</td>
            <td class="label">Fecha de Solicitud:</td>
            <td>{{ $registro->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Estado Actual:</td>
            <td colspan="3">
                @php $est = strtolower($registro->estatus ?? 'Pendiente'); @endphp
                @if(str_contains($est, 'aprobado')) <span style="color: #27ae60; font-weight: bold;">{{ strtoupper($registro->estatus) }}</span>
                @elseif(str_contains($est, 'rechazado')) <span style="color: #c0392b; font-weight: bold;">{{ strtoupper($registro->estatus) }}</span>
                @else <span style="color: #f39c12; font-weight: bold;">{{ strtoupper($registro->estatus) }}</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">Especificaciones de la Solicitud</div>
    <table>
        <tr>
            <td class="label">Tipo de Acción:</td>
            <td colspan="3" style="font-weight: bold; color: #2980b9;">{{ $registro->tipo_accion }}</td>
        </tr>
        <tr>
            <td class="label">Línea Afectada:</td>
            <td>{{ $registro->linea_producto ?? 'N/A' }}</td>
            <td class="label">Producto Específico:</td>
            <td>{{ $registro->producto_especifico ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Descripción Detallada:</td>
            <td colspan="3">{{ $registro->descripcion }}</td>
        </tr>
        <tr>
            <td class="label">Justificación Comercial:</td>
            <td colspan="3">{{ $registro->justificacion }}</td>
        </tr>
    </table>

    <div class="section-title">Datos Comerciales</div>
    <table>
        <tr>
            <td class="label">Origen de la Necesidad:</td>
            <td>{{ $registro->origen_necesidad }}</td>
            <td class="label">Cliente Referencia:</td>
            <td>{{ $registro->cliente_referencia ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="footer">
        Generado por Sistema ARDA - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>