<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Prospecto</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .header { background-color: #2c3e50; color: white; padding: 15px; text-align: center; font-size: 18px; font-weight: bold; }
        .section-title { background-color: #ecf0f1; border-left: 4px solid #3498db; padding: 5px 10px; margin-top: 15px; font-weight: bold; font-size: 14px;}
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
        .label { font-weight: bold; background-color: #f9f9f9; width: 30%; color: #555;}
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #7f8c8d; }
    </style>
</head>
<body>
    {{-- ENCABEZADO CON LOGO Y EMPRESA --}}
    <div style="text-align: center; margin-bottom: 20px;">
        @if(isset($logoBase64))
            <img src="{{ $logoBase64 }}" alt="Logo ARDA" style="height: 60px; margin-bottom: 10px;">
        @endif
        <h2 style="margin: 0; color: #2c3e50; font-size: 20px;">GRUPO INDUSTRIAL ARDA S.A. DE C.V.</h2>
    </div>

    <div class="header">
        FICHA TÉCNICA DE PROSPECTO: {{ mb_strtoupper($registro->razon_social) }}
    </div>

    <div class="section-title">Datos del Agente y Estatus</div>
    <table>
        <tr>
            <td class="label">Agente de Ventas:</td>
            <td>{{ $registro->agente->name ?? 'N/A' }}</td>
            <td class="label">Fecha de Registro:</td>
            <td>{{ $registro->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Estatus Actual:</td>
            <td colspan="3" style="font-weight: bold; color: #2980b9;">
                {{ mb_strtoupper(str_replace('_', ' ', $registro->estatus)) }}
            </td>
        </tr>
        {{-- NUEVO: MOSTRAR EL MOTIVO SOLO SI EXISTE Y ESTÁ DESCARTADO --}}
        @if(strtolower(trim($registro->estatus)) == 'descartado' && !empty($registro->motivo_descarte))
        <tr>
            <td class="label" style="color: #c0392b;">Motivo de Rechazo:</td>
            <td colspan="3" style="color: #c0392b;">{{ $registro->motivo_descarte }}</td>
        </tr>
        @endif
    </table>

    <div class="section-title">Datos del Negocio</div>
    <table>
        <tr><td class="label">Razón Social:</td><td colspan="3">{{ $registro->razon_social }}</td></tr>
        <tr>
            <td class="label">Nombre Comercial:</td><td>{{ $registro->nombre_comercial ?? 'N/A' }}</td>
            <td class="label">RFC:</td><td>{{ mb_strtoupper($registro->rfc ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="label">Giro / Tipo:</td><td>{{ $registro->giro_negocio }}</td>
            <td class="label">Ubicación:</td><td>{{ $registro->ciudad }}, {{ $registro->estado }}</td>
        </tr>
    </table>

    <div class="section-title">Datos del Contacto</div>
    <table>
        <tr>
            <td class="label">Nombre:</td><td>{{ $registro->contacto_nombre }}</td>
            <td class="label">Puesto / Depto:</td><td>{{ $registro->contacto_puesto }} ({{ $registro->contacto_departamento ?? 'N/A' }})</td>
        </tr>
        <tr>
            <td class="label">Teléfono / Celular:</td><td>{{ $registro->telefono ?? 'N/A' }}</td>
            <td class="label">Email:</td><td>{{ $registro->email ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Información Comercial y Competencia</div>
    <table>
        <tr>
            <td class="label">Origen del Prospecto:</td><td>{{ $registro->origen_prospecto }}</td>
            <td class="label">Condición Deseada:</td><td>{{ $registro->condicion_compra ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Potencial / Frecuencia:</td><td colspan="3">{{ $registro->potencial_compra ?? 'N/A' }} ({{ $registro->frecuencia_compra ?? 'N/A' }})</td>
        </tr>
        <tr>
            <td class="label">Marcas Actuales:</td><td colspan="3">{{ $registro->marcas_actuales ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Interés en ARDA</div>
    <table>
        <tr><td class="label">Líneas de Interés:</td><td>{{ is_array($registro->lineas_interes) ? implode(', ', $registro->lineas_interes) : 'N/A' }}</td></tr>
        <tr><td class="label">Productos Específicos:</td><td>{{ $registro->productos_especificos ?? 'N/A' }}</td></tr>
    </table>

    <div class="footer">
        Generado por Sistema ARDA - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>