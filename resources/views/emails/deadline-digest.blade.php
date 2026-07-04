@php
    $renderBadge = function ($tipoRegola) {
        $map = [
            'personalizzata' => ['label' => 'Regola Specifica',  'bg' => '#ede9fe', 'fg' => '#7c3aed'],
            'globale'        => ['label' => 'Regola Globale',    'bg' => '#f3f4f6', 'fg' => '#6b7280'],
            'sollecito'      => ['label' => 'Sollecito Scaduto', 'bg' => '#fee2e2', 'fg' => '#dc2626'],
        ];
        $b = $map[$tipoRegola] ?? ['label' => 'Regola di Sistema', 'bg' => '#e0f2fe', 'fg' => '#0284c7'];
        return '<span style="display:inline-block; background-color:' . $b['bg'] . '; color:' . $b['fg'] . '; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; padding:3px 9px; border-radius:12px;">' . $b['label'] . '</span>';
    };

    $sections = [
        'urgente' => ['label' => '🚨 Scadenze Urgenti / Scadute', 'bg' => '#dc2626', 'chipBg' => '#fee2e2', 'chipFg' => '#b91c1c'],
        'breve'   => ['label' => '⚠️ Scadenze a Breve Termine',   'bg' => '#f59e0b', 'chipBg' => '#fef3c7', 'chipFg' => '#b45309'],
        'avviso'  => ['label' => 'ℹ️ Prossime Scadenze',          'bg' => '#3b82f6', 'chipBg' => '#dbeafe', 'chipFg' => '#1d4ed8'],
    ];
@endphp
<!DOCTYPE html>
<html lang="it" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Riepilogo Scadenze</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <style>table {border-collapse: collapse;} .fallback-font {font-family: Arial, sans-serif;}</style>
    <![endif]-->
    <style>
        :root { color-scheme: light dark; supported-color-schemes: light dark; }
        * { box-sizing: border-box; }
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin:0; padding:0; width:100% !important; height:100% !important; font-family:-apple-system, BlinkMacSystemFont,"Inter","Segoe UI",Roboto,Helvetica,Arial,sans-serif; -webkit-font-smoothing:antialiased; }

        @media (prefers-color-scheme: dark) {
            .wrapper-bg { background-color: #0a0f1a !important; }
            .main-container { background-color: #121826 !important; }
            .content-td { background-color: #121826 !important; }
            .intro-text { color: #b6c0d1 !important; }
            .section-box { border-color: #232b3d !important; }
            .item-row { border-bottom-color: #232b3d !important; }
            .item-title-text { color: #f1f5f9 !important; }
            .item-subtitle-text { color: #93a1b7 !important; }
            .item-date-value { color: #e5e9f0 !important; }
            .footer-td { background-color: #0d1220 !important; color:#6b7690 !important; border-top-color:#232b3d !important; }
            .footer-td a { color: #93a1b7 !important; }
        }

        @media screen and (max-width: 600px) {
            .wrapper-td { padding: 0 !important; }
            .main-container { width:100% !important; border-radius:0 !important; }
            .header-banner { padding: 32px 24px 44px 24px !important; }
            .content-td { padding-left: 20px !important; padding-right: 20px !important; }
            .footer-td { padding-left: 20px !important; padding-right: 20px !important; }
            .item-row td { display:block !important; width:100% !important; padding-bottom:4px !important; }
            .item-date-cell { text-align:left !important; width:100% !important; padding-top:8px !important; }
            .btn-table { width: 100% !important; }
            .btn-link { display:block !important; }
        }
    </style>
</head>
<body class="wrapper-bg" style="margin:0; padding:0; background-color:#eef1f6;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        Il tuo riepilogo scadenze Expir0 è pronto: urgenti, a breve termine e prossime avvisi.
        &#8203;&#847; &#8203;&#847; &#8203;&#847; &#8203;&#847; &#8203;&#847;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="wrapper-bg" bgcolor="#eef1f6" style="background-color:#eef1f6;">
        <tr>
            <td align="center" class="wrapper-td" style="padding: 40px 16px;">

                <table role="presentation" width="620" cellpadding="0" cellspacing="0" class="main-container" bgcolor="#ffffff" style="width:620px; max-width:620px; background-color:#ffffff; border-radius:18px; overflow:hidden;">

                    <!-- HEADER BANNER (brand ciano-verde, coerente con gli altri template) -->
                    <tr>
                        <td class="header-banner" align="center" bgcolor="#14b8a6" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); padding: 40px 40px 52px 40px;">
                            <!--[if mso]>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" bgcolor="#14b8a6" style="padding: 40px 40px 52px 40px;">
                            <![endif]-->
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td align="center" bgcolor="#ffffff" style="background-color:#ffffff; width:104px; height:104px; border-radius:52px; padding:4px; box-shadow: 0 12px 26px -6px rgba(6,30,25,0.4);">
                                        <!--[if mso]>
                                        <table role="presentation" width="96" height="96"><tr><td align="center" valign="middle" bgcolor="#14b8a6" style="border-radius:48px;">
                                        <![endif]-->
                                        <table role="presentation" width="96" height="96" cellpadding="0" cellspacing="0" bgcolor="#14b8a6" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); border-radius:48px;">
                                            <tr>
                                                <td align="center" valign="middle" style="padding:4px;">
                                                    <!-- Contenitore circolare ingrandito a 120x120px -->
                                                    <table role="presentation" width="120" height="120" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="background-color:#ffffff; border-radius:60px; width:120px; height:120px;">
                                                        <tr>
                                                            <td align="center" valign="middle">
                                                                <!-- Logo ingrandito a 90px -->
                                                                <img src="{{ $message->embed(public_path('images/logo/logo_email_push.png')) }}"
                                                                    alt="Expir0" width="120"
                                                                    style="width:120px; height:auto; vertical-align:middle; display:inline-block; border:0;">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!--[if mso]>
                                        </td></tr></table>
                                        <![endif]-->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top:16px;">
                                        <span style="color:#ffffff; font-size:18px; font-weight:700; letter-spacing:-0.2px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                                            Riepilogo Scadenze
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            <!--[if mso]>
                            </td></tr></table>
                            <![endif]-->
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td class="content-td" style="padding: 32px 36px 40px 36px; background-color:#ffffff;">

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="intro-text" style="font-size:15px; line-height:1.65; color:#475569; padding-bottom:28px;">
                                        Ciao,<br>
                                        ecco il tuo riepilogo automatico. I seguenti avvisi sono stati generati in base alle tue <strong style="color:#0d9488;">regole di notifica personalizzate</strong> o alle impostazioni globali del tuo account.
                                    </td>
                                </tr>
                            </table>

                            @foreach($sections as $key => $sec)
                                @if(!empty($notifiche[$key]))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="section-box" style="border:1px solid #eef1f6; border-radius:12px; overflow:hidden; margin-bottom:22px;">
                                    <tr>
                                        <td align="center" bgcolor="{{ $sec['bg'] }}" style="background-color:{{ $sec['bg'] }}; padding: 13px 20px;">
                                            <span style="color:#ffffff; font-weight:700; font-size:13.5px; text-transform:uppercase; letter-spacing:0.5px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                                                {{ $sec['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 20px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                @foreach($notifiche[$key] as $item)
                                                <tr class="item-row" style="border-bottom:1px solid #f3f4f6;">
                                                    <td style="padding:16px 0; vertical-align:top;">
                                                        <div class="item-title-text" style="font-weight:700; color:#111827; font-size:15px; margin-bottom:5px;">
                                                            {{ $item['tipo'] }}
                                                            &nbsp;{!! $renderBadge($item['tipo_regola'] ?? 'sistema') !!}
                                                        </div>
                                                        <div class="item-subtitle-text" style="color:#6b7280; font-size:13.5px; margin-bottom:4px;">{{ $item['soggetto'] }}</div>
                                                        @if($item['importo'] !== '-')
                                                        <div style="font-weight:600; color:#111827; font-size:14px;">{{ $item['importo'] }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="item-date-cell" align="right" style="padding:16px 0; vertical-align:top; width:120px;">
                                                        <div class="item-date-value" style="font-weight:700; color:#111827; font-size:14.5px;">{{ $item['data'] }}</div>
                                                        <div style="font-size:12.5px; font-weight:600; margin-top:5px; display:inline-block; padding:3px 9px; border-radius:12px; background-color:{{ $sec['chipBg'] }}; color:{{ $sec['chipFg'] }};">
                                                            @if($key === 'urgente')
                                                                {{ $item['giorni'] < 0 ? 'Scaduto da ' . abs($item['giorni']) . ' gg' : '-' . $item['giorni'] . ' giorni' }}
                                                            @else
                                                                -{{ $item['giorni'] }} giorni
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                @endif
                            @endforeach

                            <!-- BULLETPROOF BUTTON -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="btn-table" style="margin: 26px 0 4px;">
                                <tr>
                                    <td align="center">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ config('app.url') }}/deadlines" style="height:50px;v-text-anchor:middle;width:230px;" arcsize="50%" stroke="f" fillcolor="#14b8a6">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">Accedi al Portale</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-->
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td bgcolor="#14b8a6" align="center" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); border-radius:26px; box-shadow: 0 10px 22px -6px rgba(16,185,129,0.45);">
                                                    <a href="{{ config('app.url') }}/deadlines" class="btn-link" style="display:inline-block; padding:15px 38px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; letter-spacing:-0.1px;">
                                                        Accedi al Portale →
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td class="footer-td" align="center" bgcolor="#f0fdf9" style="padding: 26px 40px; font-size:12px; line-height:1.6; color:#5c8a7d; background-color:#f0fdf9; border-top:3px solid #10b981;">
                            Questo è un riepilogo automatico generato dal tuo assistente scadenze.<br>
                            <a href="{{ config('app.url') }}/profile" style="color:#0d9488; text-decoration:none; font-weight:600;">Modifica le tue regole di notifica</a>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>