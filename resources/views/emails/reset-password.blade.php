<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Grupo Industrial ARDA</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 20px auto; border-collapse: collapse;">
        
        {{-- Fila 1: Logo y Datos de Contacto --}}
        <tr>
            <td style="padding: 20px 0;">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                    <tr>
                        {{-- Columna Logo --}}
                        <td width="40%" valign="top" style="padding-right: 20px;">
                            {{-- Asegúrate que la ruta al logo sea correcta --}}
                            <img src="{{ asset('assets/img/CORREO.png') }}" alt="Grupo Industrial ARDA" style="max-width: 100%; height: auto; display: block;">
                        </td>
                        
                        {{-- Columna Datos de Contacto --}}
                        <td width="60%" valign="top" style="font-size: 12px; color: #555;">
                            {{-- <p style="margin: 0 0 5px 0; font-weight: bold; color: #0d284a;">Ricardo Mejia Martinez</p> --}}
                            <p style="margin: 0 0 10px 0; color: #0d284a;">Departamento de Sistemas</p>
                            
                            <p style="margin: 0 0 5px 0;">
                                <span style="color: #0d284a; font-weight: bold;">📍 Dirección:</span> Circuito de la Industria Norte 32, Zona Industrial Lerma, Estado de México, CP. 52000
                            </p>
                            {{-- <p style="margin: 0 0 5px 0;">
                                <span style="color: #0d284a; font-weight: bold;">📞 Teléfono:</span> (728) 282-4148 Ext. 108
                            </p> --}}
                            {{-- <p style="margin: 0 0 5px 0;">
                                <span style="color: #0d284a; font-weight: bold;">📱 Móvil:</span> (+52) 729 1616 939
                            </p> --}}
                            <p style="margin: 0 0 5px 0;">
                                <span style="color: #0d284a; font-weight: bold;">✉️ Email:</span> <a href="mailto:sistemas@arda.com.mx" style="color: #1a0dab; text-decoration: none;">sistemas@arda.com.mx</a>
                            </p>
                            <p style="margin: 0;">
                                <span style="color: #0d284a; font-weight: bold;">🌐 Web:</span> <a href="https://arda.com.mx/" target="_blank" style="color: #1a0dab; text-decoration: none;">arda.com.mx</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Fila 2: Separador --}}
        <tr>
            <td style="border-top: 1px solid #ddd; padding-top: 20px;"></td>
        </tr>

        {{-- Fila 3: Contenido del Correo --}}
        <tr>
            <td style="padding: 20px 0;">
                <h2 style="color: #0d284a; margin-top: 0;">Restablecer Contraseña</h2>
                <p>Has solicitado restablecer tu contraseña para tu cuenta de pedidos en Grupo Industrial ARDA.</p>
                <p>Haz clic en el siguiente botón para establecer una nueva contraseña:</p>
                
                {{-- Botón para restablecer --}}
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ url('/password/reset/'.$token) }}"
                       style="background-color: #fecb00; color: #0d284a; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                       Restablecer Contraseña
                    </a>
                </p>
                
                <p>Este enlace para restablecer la contraseña expirará en {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutos.</p>
                <p>Si no solicitaste un restablecimiento de contraseña, no se requiere ninguna acción adicional.</p>
                <p>Saludos,<br>El equipo de Grupo Industrial ARDA</p>
            </td>
        </tr>

        {{-- Fila 4: Separador --}}
        <tr>
            <td style="border-top: 1px solid #ddd; padding-top: 20px;"></td>
        </tr>

        {{-- Fila 5: Disclaimer --}}
        <tr>
            <td style="padding: 10px 0; font-size: 10px; color: #888; text-align: justify;">
                Este correo electrónico, así como los archivos adjuntos que contenga son confidenciales, 
                y es para uso exclusivo del destinatario al que expresamente se le ha enviado. 
                Si usted no es el destinatario legítimo del mismo, deberá reportarlo al remitente del correo y borrarlo inmediatamente. 
                Cualquier revisión, retransmisión, divulgación, difusión o cualquier otro uso de este correo, 
                por personas o entidades distintas a las del destinatario legítimo, queda estrictamente prohibido. 
                Los derechos de propiedad respecto de la información confidencial y los distintos elementos en él contenidos son 
                propiedad de Grupo Industrial ARDA S.A. de C.V.
                <br><br>
                &copy; {{ date('Y') }} Grupo Industrial Arda SA de CV. Todos los derechos reservados.
            </td>
        </tr>
        
    </table>

</body>
</html>