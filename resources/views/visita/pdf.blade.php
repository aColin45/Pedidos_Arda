<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Visita</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .header { background-color: #2980b9; color: white; padding: 10px; text-align: center; font-size: 16px; font-weight: bold; }
        .section-title { background-color: #ecf0f1; border-left: 4px solid #e67e22; padding: 5px 10px; margin-top: 15px; font-weight: bold; font-size: 13px;}
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
        .label { font-weight: bold; background-color: #f9f9f9; width: 25%; color: #555;}
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #7f8c8d; }
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
        REPORTE OFICIAL DE VISITA #{{ $registro->id }}
    </div>

    <div class="section-title">Información General</div>
    <table>
        <tr>
            <td class="label">Agente de Ventas:</td>
            <td>{{ $registro->agente->name ?? 'N/A' }}</td>
            <td class="label">Fecha y Hora:</td>
            <td>{{ \Carbon\Carbon::parse($registro->fecha_visita)->format('d/m/Y H:i A') }}</td>
        </tr>
        <tr>
            <td class="label">Negocio Visitado:</td>
            <td colspan="3" style="font-weight: bold;">
                @if($registro->es_prospecto)
                    [PROSPECTO] {{ $registro->prospecto->razon_social ?? 'N/A' }}
                @else
                    [CLIENTE] {{ $registro->cliente->nombre ?? 'N/A' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Modo de Contacto:</td>
            <td colspan="3">{{ $registro->modo_contacto ?? 'No especificado' }}</td>
        </tr>
        <tr>
            <td class="label">Atendido Por:</td>
            <td colspan="3">{{ $registro->persona_atendio ?? 'N/A' }} ({{ $registro->cargo_atendio ?? 'N/A' }})</td>
        </tr>
    </table>

    <div class="section-title">Desarrollo de la Visita</div>
    <table>
        <tr>
            <td class="label">Objetivo Principal:</td>
            <td>{{ $registro->objetivo }}</td>
            <td class="label">Resultado Final:</td>
            <td style="font-weight: bold; color: #27ae60;">{{ mb_strtoupper($registro->resultado) }}</td>
        </tr>
        
        {{-- NUEVO: FILA INDEPENDIENTE PARA EL MOTIVO --}}
        @if($registro->resultado_visita == 'No interesado (Rechazo)' && !empty($registro->motivo_rechazo))
        <tr>
            <td class="label" style="color: #c0392b;">Motivo de Rechazo:</td>
            <td colspan="3" style="color: #c0392b; font-weight: bold;">{{ $registro->motivo_rechazo }}</td>
        </tr>
        @endif

        <tr>
            <td class="label">Familias de Interés:</td>
            <td colspan="3">{{ $registro->lineas_interes ?? 'No especificadas' }}</td>
        </tr>

        <tr>
            <td class="label">Producto Específico:</td>
            <td colspan="3">{{ $registro->productos_especificos ?? 'No especificado' }}</td>
        </tr>
        <tr>
            <td class="label">Marcas Competencia:</td>
            <td colspan="3">{{ $registro->marcas_competencia ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Problemas / Necesidades:</td>
            <td colspan="3">{{ $registro->necesidades_problemas ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Cierre y Acuerdos</div>
    <table>
        <tr>
            <td class="label">Monto Cotizado/Vendido:</td>
            <td style="font-weight: bold;">${{ number_format($registro->monto_pedido, 2) }}</td>
            <td class="label">Próximo Contacto:</td>
            <td style="color: #c0392b; font-weight: bold;">{{ $registro->proximo_contacto ? \Carbon\Carbon::parse($registro->proximo_contacto)->format('d/m/Y') : 'Sin Programar' }}</td>
        </tr>
        <tr>
            <td class="label">Acuerdos Generados:</td>
            <td colspan="3">{{ $registro->compromisos ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="footer">
        Generado por Sistema ARDA - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>