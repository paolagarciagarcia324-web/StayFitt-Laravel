<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recupera tu acceso a StayFit</title>
</head>
<body style="margin:0;padding:0;background:#120e16;font-family:Montserrat,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#120e16;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#1c1622;border-radius:20px;overflow:hidden;border:1px solid rgba(255,255,255,0.08);">
                    <tr>
                        <td style="padding:28px 28px 12px;color:#fff;">
                            <p style="margin:0 0 8px;color:#2ef5e6;font-size:11px;font-weight:800;letter-spacing:0.16em;text-transform:uppercase;">StayFit</p>
                            <h1 style="margin:0 0 16px;font-size:24px;line-height:1.2;">Recupera tu acceso</h1>
                            <p style="margin:0 0 14px;color:rgba(255,255,255,0.82);font-size:14px;line-height:1.55;">Hola, {{ $nombre }}.</p>
                            <p style="margin:0 0 22px;color:rgba(255,255,255,0.82);font-size:14px;line-height:1.55;">Recibimos una solicitud para crear una nueva contraseña. Usa este enlace temporal para continuar:</p>
                            <p style="margin:0 0 22px;text-align:center;">
                                <a href="{{ $url }}" style="display:inline-block;background:#e23a78;color:#fff;text-decoration:none;font-weight:800;font-size:14px;letter-spacing:0.04em;padding:14px 22px;border-radius:999px;">Definir nueva contraseña</a>
                            </p>
                            <p style="margin:0 0 10px;color:rgba(255,255,255,0.62);font-size:12px;line-height:1.5;">El enlace vence en {{ $minutos }} minutos. Si no fuiste tú, ignora este mensaje: tu acceso no cambia.</p>
                            <p style="margin:0;color:rgba(255,255,255,0.42);font-size:11px;line-height:1.5;word-break:break-all;">Si el botón no abre, copia este enlace:<br>{{ $url }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
