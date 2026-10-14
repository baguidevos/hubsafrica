<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'Notification Hub Africa' }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0f172a; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 24px 12px;">
        <tr>
            <td align="center">
                <!-- Wrapper Container -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2F3284 0%, #1e2167 100%); background-color: #2F3284; padding: 28px 32px; text-align: left;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td valign="middle" align="left">
                                        @if(isset($sae) && $sae)
                                            <div style="font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                                                {{ $sae->name }}
                                            </div>
                                            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: #F8B81D; margin-top: 3px;">
                                                Accompagnement opéré via Hub Africa
                                            </div>
                                        @else
                                            <div style="font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px;">
                                                HUB <span style="color: #F8B81D;">AFRICA</span>
                                            </div>
                                            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: #cbd5e1; margin-top: 2px;">
                                                Plateforme Panafricaine d'Accompagnement
                                            </div>
                                        @endif
                                    </td>
                                    @if(isset($badgeText))
                                    <td valign="middle" align="right">
                                        <span style="display: inline-block; background-color: rgba(248, 184, 29, 0.15); border: 1px solid #F8B81D; color: #F8B81D; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase;">
                                            {{ $badgeText }}
                                        </span>
                                    </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Bar d'accent doré -->
                    <tr>
                        <td height="4" style="background-color: #F8B81D; line-height: 4px; font-size: 4px;">&nbsp;</td>
                    </tr>

                    <!-- Contenu Principal -->
                    <tr>
                        <td style="padding: 36px 32px; line-height: 1.6; color: #334155; font-size: 15px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 32px; text-align: center;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #64748b; font-weight: 500;">
                                Besoin d'assistance ? Contactez-nous à
                                <a href="mailto:{{ config('hubafrica.contact.email', 'teamhubafrica@gmail.com') }}" style="color: #2F3284; text-decoration: underline; font-weight: 600;">
                                    {{ config('hubafrica.contact.email', 'teamhubafrica@gmail.com') }}
                                </a>
                                @if(config('hubafrica.contact.phone'))
                                    &bull; <span>{{ config('hubafrica.contact.phone') }}</span>
                                @endif
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                Hub Africa &bull; Lomé, Togo &bull; Propulsé pour dynamiser l'écosystème entrepreneurial africain
                            </p>
                            <p style="margin: 8px 0 0 0; font-size: 10px; color: #cbd5e1;">
                                Cet email automatique a été transmis en accord avec votre compte ou candidature sur la plateforme.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
