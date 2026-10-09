<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Cliente</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; padding-bottom: 40px; }
        
        .header { width: 100%; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 15px; }
        .logo { max-width: 150px; height: auto; }
        .company-info { text-align: right; font-size: 10px; line-height: 1.3; }
        
        .title { text-align: center; font-size: 16px; font-weight: bold; color: #0056b3; margin: 10px 0 15px 0; letter-spacing: 1px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background-color: #f2f2f2; color: #333; padding: 6px; text-align: left; font-size: 10px; border: 1px solid #ccc; text-transform: uppercase; }
        td { border: 1px solid #ccc; padding: 6px; font-size: 10px; }
        
        .section-title { background-color: #0056b3; color: white; padding: 5px; font-weight: bold; font-size: 11px; margin-bottom: 5px; text-transform: uppercase; }
        
        .signature-box { text-align: center; margin-top: 40px; padding-top: 10px; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px; font-weight: bold; }
        
        .requirements { background-color: #f9f9f9; border: 1px dashed #999; padding: 10px; margin-top: 20px; font-size: 9px; page-break-inside: avoid; }
        .requirements ul { margin-top: 5px; padding-left: 20px; margin-bottom: 0; }
        .requirements li { margin-bottom: 3px; }

        /* Estilo fijo para el pie de página */
        .footer {
            position: fixed;
            bottom: -15px; 
            left: 0px; 
            right: 0px;
            height: 30px; 
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    {{-- PIE DE PÁGINA FIJO --}}
    <div class="footer">
        Grupo Industrial ARDA S.A de C.V. Todos los derechos reservados. | Pedidos - ARDA<br>
        https://agentes.arda.com.mx/
    </div>

    {{-- ENCABEZADO TIPO ARDA --}}
    <table class="header" style="border: none; margin-bottom: 0;">
        <tr>
            <td style="border: none; width: 50%;">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo" alt="Grupo Arda">
                @else
                    <h2 style="color: #0056b3; margin: 0;">GRUPO INDUSTRIAL ARDA</h2>
                @endif
            </td>
            <td style="border: none; width: 50%;" class="company-info">
                <strong>GRUPO INDUSTRIAL ARDA S.A. de C.V.</strong><br>
                RFC: GIA170620163<br>
                Avenida de la Industria 166 Col. Moctezuma 2da. Sección<br>
                Delegación Venustiano Carranza C.P. 15530<br>
                Tel: (728) 282-4148 Ext. 107<br>
                creditoycobranza@arda.com.mx
            </td>
        </tr>
    </table>

    <div class="title">FORMATO ALTA DE CLIENTE NUEVO</div>

    {{-- DATOS GENERALES --}}
    <table>
        <tr>
            <th style="width: 25%;">NOMBRE O RAZÓN SOCIAL</th>
            <td style="width: 45%;"><strong>{{ mb_strtoupper($alta->razon_social) }}</strong></td>
            <th style="width: 10%;">R.F.C.</th>
            <td style="width: 20%;"><strong>{{ mb_strtoupper($alta->rfc) }}</strong></td>
        </tr>
        <tr>
            <th>RÉGIMEN FISCAL</th>
            <td>{{ mb_strtoupper($alta->regimen_fiscal ?? '') }}</td>
            <th>USO CFDI</th>
            <td>{{ mb_strtoupper($alta->uso_cfdi ?? '') }}</td>
        </tr>
        <tr>
            <th>CONTACTO CTAS. POR PAGAR</th>
            <td>{{ mb_strtoupper($alta->contacto_pagos ?? '') }}</td>
            <th>CORREO / TEL</th>
            <td>
                {{ $alta->email_pagos }} 
                @if($alta->email_pagos && $alta->telefono_pagos) <br> @endif
                {{ $alta->telefono_pagos }}
            </td>
        </tr>
    </table>

    {{-- DOMICILIOS --}}
    <div class="section-title">DOMICILIO FISCAL</div>
    <table>
        <tr>
            <th style="width: 15%;">CALLE Y NÚMERO</th>
            <td style="width: 35%;">{{ mb_strtoupper($alta->calle_fiscal ?? '') }}</td>
            <th style="width: 15%;">COLONIA</th>
            <td style="width: 35%;">{{ mb_strtoupper($alta->colonia_fiscal ?? '') }}</td>
        </tr>
        <tr>
            <th>DELEGACIÓN / MPIO.</th>
            <td>{{ mb_strtoupper($alta->municipio_fiscal ?? '') }}</td>
            <th>ESTADO Y C.P.</th>
            <td>
                {{ mb_strtoupper($alta->estado_fiscal ?? '') }} 
                @if($alta->cp_fiscal) | C.P. {{ $alta->cp_fiscal }} @endif
            </td>
        </tr>
        <tr>
            <th>TELÉFONO FISCAL</th>
            <td>{{ $alta->telefono_fiscal ?? '' }}</td>
            <th>CORREOS ELECTRÓNICOS</th>
            <td>
                @if($alta->email1_fiscal) 1. {{ $alta->email1_fiscal }} <br> @endif
                @if($alta->email2_fiscal) 2. {{ $alta->email2_fiscal }} @endif
            </td>
        </tr>
    </table>

    <div class="section-title">DOMICILIO DE ENTREGA (SI ES DIFERENTE)</div>
    <table>
        <tr>
            <th style="width: 25%;">PERSONA QUE RECIBE</th>
            <td colspan="3">{{ mb_strtoupper($alta->persona_recibe ?? '') }}</td>
        </tr>
        <tr>
            <th style="width: 25%;">CALLE Y NÚMERO</th>
            <td style="width: 25%;">{{ mb_strtoupper($alta->calle_entrega ?? '') }}</td>
            <th style="width: 25%;">COLONIA</th>
            <td style="width: 25%;">{{ mb_strtoupper($alta->colonia_entrega ?? '') }}</td>
        </tr>
        <tr>
            <th>DELEGACIÓN / MPIO.</th>
            <td>{{ mb_strtoupper($alta->municipio_entrega ?? '') }}</td>
            <th>ESTADO Y C.P.</th>
            <td>
                {{ mb_strtoupper($alta->estado_entrega ?? '') }} 
                @if($alta->cp_entrega) | C.P. {{ $alta->cp_entrega }} @endif
            </td>
        </tr>
    </table>

    {{-- AGENTE DE VENTAS (AUTOMÁTICO) --}}
    <div class="section-title">DATOS DEL AGENTE DE VENTAS (Asignado Automáticamente)</div>
    <table>
        <tr>
            <th style="width: 25%;">NOMBRE DEL AGENTE</th>
            <td style="width: 35%;"><strong>{{ mb_strtoupper($alta->agente->name ?? '') }}</strong></td>
            <th style="width: 15%;">CORREO AGENTE</th>
            <td style="width: 25%;">{{ strtolower($alta->agente->email ?? '') }}</td>
        </tr>
    </table>

    {{-- REFERENCIAS COMERCIALES --}}
    <div class="section-title">REFERENCIAS COMERCIALES</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">NOMBRE O RAZÓN SOCIAL</th>
                <th style="width: 30%;">CORREO DE CONTACTO</th>
                <th style="width: 30%;">TELÉFONOS (NO CELULARES / NO AGENTES)</th>
            </tr>
        </thead>
        <tbody>
            @if(is_array($alta->referencias_comerciales))
                @foreach($alta->referencias_comerciales as $ref)
                <tr>
                    <td>{{ mb_strtoupper($ref['nombre'] ?? '') }}</td>
                    <td>{{ strtolower($ref['correo'] ?? '') }}</td>
                    <td>{{ $ref['telefono'] ?? '' }}</td>
                </tr>
                @endforeach
            @else
                <tr><td colspan="3" style="text-align: center;">Sin referencias capturadas.</td></tr>
            @endif
        </tbody>
    </table>

    {{-- FIRMAS --}}
    <table style="border: none; margin-top: 50px;">
        <tr>
            <td style="border: none; width: 100%;">
                <div class="signature-box">
                    <div class="signature-line">NOMBRE Y FIRMA DEL CLIENTE Y/O REPRESENTANTE LEGAL</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- REQUISITOS --}}
    <div class="requirements">
        <strong>DOCUMENTOS REQUERIDOS:</strong>
        <ul>
            <li>CONSTANCIA DE SITUACION FISCAL ACTUALIZADA</li>
            <li>IDENTIFICACION OFICIAL CLIENTE (INE/PASAPORTE) AMBOS LADOS</li>
            <li>COMPROBANTE DE DOMICILIO (NO MAYOR A 3 MESES)</li>
            <li>OPINION DE CUMPLIMIENTO ACTUALIZADA</li>
            <li>ACTA CONSTITUTIVA, SOLO LAS SIGUIENTES PAGINAS (DATOS DE LA EMPRESA, SOCIOS, CAPITAL SOCIAL, ADMINISTRADOR UNICO PARA PLEITOS Y COBRANZAS O REPRESENTANTE LEGAL)</li>
            <li>PODER NOTARIAL DEL REPRESENTANTE LEGAL O ADMINISTRADOR UNICO PARA PLEITOS Y COBRANZAS EN CASO DE NO ESTAR INCLUIDA EN EL ACTA CONSTITUTIVA</li>
            <li>IDENTIFICACION OFICIAL DEL REPRESENTANTE LEGAL O ADMINISTRADOR UNICO PARA PLEITOS Y COBRANZAS (INE, PASAPORTE) AMBOS LADOS</li>
            <li>LLENADO FORMATO CONTENIENDO 5 REFERENCIAS COMERCIALES CON CORREO Y NUMERO TELEFONICO FIJO DE LA EMPRESA (NO CELULARES / NO AGENTES VTAS)</li>
        </ul>
        <div style="margin-top: 8px; color: #d9534f;">
            <strong>NOTA:</strong> EL FORMATO DEBE SER FIRMADO UNICAMENTE POR EL DUEÑO O ADMINISTRADOR UNICO O REPRESENTANTE LEGAL.
        </div>
    </div>

</body>
</html>