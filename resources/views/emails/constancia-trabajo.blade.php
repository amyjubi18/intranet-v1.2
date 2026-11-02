<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constancia de Trabajo</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f8;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 20px;">
                <table role="presentation" style="max-width: 600px; width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 30px; text-align: center;">
                            <img src="cid:logo_codecut" alt="Logo CODECYT" style="max-width: 70px; height: auto; display: block; margin: 0 auto 15px;">
                            <h1 style="color: #ffffff; font-size: 24px; margin: 0 0 5px 0;">Constancia de Trabajo</h1>
                            <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 0;">Nueva solicitud de constancia de trabajo desde la Intranet CODECYT</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                                <h2 style="color: #1e40af; font-size: 16px; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #3b82f6;">📋 Datos del Solicitante</h2>
                                <div style="display: flex; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <span style="font-weight: 600; color: #475569; width: 140px; flex-shrink: 0;">Nombre:</span>
                                    <span style="color: #1e293b; font-weight: 500;">{{ $user->name }} {{ $user->lastname }}</span>
                                </div>
                                <div style="display: flex; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <span style="font-weight: 600; color: #475569; width: 140px; flex-shrink: 0;">Cédula:</span>
                                    <span style="color: #1e293b; font-weight: 500;">{{ $user->cedula ?? 'No especificada' }}</span>
                                </div>
                                <div style="display: flex; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <span style="font-weight: 600; color: #475569; width: 140px; flex-shrink: 0;">Correo:</span>
                                    <span style="color: #1e293b; font-weight: 500;">{{ $user->email }}</span>
                                </div>
                                <div style="display: flex; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
                                    <span style="font-weight: 600; color: #475569; width: 140px; flex-shrink: 0;">Departamento:</span>
                                    <span style="color: #1e293b; font-weight: 500;">{{ $user->departamento ?? 'No especificado' }}</span>
                                </div>
                                <div style="display: flex; margin-bottom: 0; padding-bottom: 0;">
                                    <span style="font-weight: 600; color: #475569; width: 140px; flex-shrink: 0;">F. Nacimiento:</span>
                                    <span style="color: #1e293b; font-weight: 500;">
                                        @if($user->fecha_nacimiento)
                                            {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') }}
                                        @else
                                            No especificada
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div style="text-align: center; padding: 10px 0;">
                                <span style="display: inline-block; background-color: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">📧 Enviado desde Intranet CODECYT</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="color: #64748b; font-size: 12px; margin: 0 0 5px 0;">© {{ date('Y') }} CODECYT, S.A.</p>
                            <p style="color: #64748b; font-size: 12px; margin: 0;">Desarrollado por <strong style="color: #1e40af;">GTIC</strong> - Gerencia de Tecnología</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

