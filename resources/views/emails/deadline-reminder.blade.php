@php
    // --- LOGICA DI PRESENTAZIONE IN BASE ALL'URGENZA (INVARIATA) ---
    $color = '#4f46e5'; // Colore base (Indigo)
    $icon = 'ℹ️';
    $title = 'Promemoria Scadenza';

    $tipoRegolaTesto = 'alle impostazioni di sistema';
    $ruleBadgeClass = 'badge-system';
    $ruleBadgeLabel = 'Regola di Sistema';
    if (isset($rule_type)) {
        if ($rule_type === 'personalizzata') {
            $tipoRegolaTesto = 'a una regola specifica per questa scadenza';
            $ruleBadgeClass = 'badge-custom';
            $ruleBadgeLabel = 'Regola Specifica';
        } elseif ($rule_type === 'globale') {
            $tipoRegolaTesto = 'alle tue impostazioni di notifica globali';
            $ruleBadgeClass = 'badge-global';
            $ruleBadgeLabel = 'Regola Globale';
        } elseif ($rule_type === 'sollecito') {
            $tipoRegolaTesto = 'a un sollecito per scadenza superata';
            $ruleBadgeClass = 'badge-sollecito';
            $ruleBadgeLabel = 'Sollecito Scaduto';
        }
    }

    $message = "Gentile Utente,<br><br>ricevi questo avviso in base <strong>{$tipoRegolaTesto}</strong>. Ti ricordiamo che la seguente scadenza si sta avvicinando. Ti consigliamo di preparare la documentazione necessaria o procedere al pagamento.";
    $isUrgent = false;

    if ($step === 'year') {
        $color = '#3b82f6';
        $title = 'Avviso di Scadenza Annuale';
        $message = 'Gentile Utente,<br><br>Ti informiamo preventivamente che il seguente documento o servizio giungerà a scadenza durante l\'anno in corso. Ti invitiamo a prenderne nota per pianificare le relative pratiche di rinnovo.';
    } elseif ($step === '30') {
        $color = '#f59e0b';
        $icon = '🔔';
        $title = 'Scadenza a 30 giorni';
        $message = "Gentile Utente,<br><br>ricevi questo avviso in base <strong>{$tipoRegolaTesto}</strong>. Ti ricordiamo che manca circa un mese alla scadenza indicata di seguito. Ti consigliamo di preparare la documentazione necessaria o procedere al pagamento nei prossimi giorni.";
    } elseif ($step === '15') {
        $color = '#ea580c';
        $icon = '⚠️';
        $title = 'Scadenza a 15 giorni';
        $message = "Gentile Utente,<br><br>ricevi questo avviso in base <strong>{$tipoRegolaTesto}</strong>. Attenzione: mancano solo 15 giorni alla scadenza. Ti invitiamo a regolarizzare la tua posizione al più presto per evitare sanzioni o interruzioni del servizio.";
    } elseif ($step === '7' || $step === 'urgente') {
        $color = '#dc2626';
        $icon = '🚨';
        $title = 'AVVISO URGENTE: Scadenza Imminente';
        $message = "Gentile Utente,<br><br>ricevi questo avviso in base <strong>{$tipoRegolaTesto}</strong>.<br><br><strong>La scadenza indicata è imminente.</strong> È richiesto un intervento immediato per evitare la decadenza dei termini legali o contrattuali. Si prega di provvedere il prima possibile.";
        $isUrgent = true;
    }

    // --- FORMATTAZIONE DEI DATI (INVARIATA) ---
    $soggetto = 'N/D';
    if (in_array($deadline->tipo, ['patente', 'carta_identita', 'tesserino_sanitario'])) {
        $soggetto = $deadline->user ? $deadline->user->name : 'Membro della Famiglia';
    } elseif (in_array($deadline->tipo, ['bollo', 'assicurazione', 'revisione'])) {
        if ($deadline->vehicle) {
            $soggetto = strtoupper($deadline->vehicle->marca . ' ' . $deadline->vehicle->modello . ' (' . $deadline->vehicle->targa . ')');
        } else {
            $soggetto = 'Veicolo';
        }
    } elseif ($deadline->tipo === 'spesa_abbonamento') {
        $soggetto = $deadline->subscription ? $deadline->subscription->nome : 'Spesa/Abbonamento';
    }

    $tipoFormattato = ucwords(str_replace('_', ' ', $deadline->tipo));
    $dataScadenza = \Carbon\Carbon::parse($deadline->data_scadenza)->format('d/m/Y');
    $importoFormattato = $deadline->importo > 0 ? '€ ' . number_format($deadline->importo, 2, ',', '.') : 'Da verificare';
@endphp
<!DOCTYPE html>
<html lang="it" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $title }}</title>
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
            .title-text { color: #f1f5f9 !important; }
            .message-text { color: #b6c0d1 !important; }
            .message-text strong { color: #e2e8f0 !important; }
            .details-box-td { background-color: #19212f !important; border-color: #232b3d !important; }
            .details-table td { border-bottom-color: #232b3d !important; }
            .label-text { color: #8892a6 !important; }
            .value-text { color: #e5e9f0 !important; }
            .footer-td { background-color: #0d1220 !important; color:#6b7690 !important; border-top-color:#232b3d !important; }
            .footer-links a { color: #93a1b7 !important; }
            .note-text em { color: #9aa7bd !important; }
        }

        @media screen and (max-width: 600px) {
            .wrapper-td { padding: 0 !important; }
            .main-container { width:100% !important; border-radius:0 !important; }
            .header-banner { padding: 32px 24px 44px 24px !important; }
            .content-td { padding-left: 22px !important; padding-right: 22px !important; }
            .footer-td { padding-left: 22px !important; padding-right: 22px !important; }
            .title-text { font-size: 19px !important; }
            .alert-banner-text { font-size: 14px !important; }
            .btn-table { width: 100% !important; }
            .btn-link { display:block !important; }
            .details-table .label { display:block; width:100% !important; padding-bottom:2px !important; }
            .details-table .value { display:block; width:100% !important; text-align:left !important; padding-top:0 !important; }
        }
    </style>
</head>
<body class="wrapper-bg" style="margin:0; padding:0; background-color:#eef1f6;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        {{ $icon }} {{ $title }} — {{ $tipoFormattato }} in scadenza il {{ $dataScadenza }}.
        &#8203;&#847; &#8203;&#847; &#8203;&#847; &#8203;&#847; &#8203;&#847;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="wrapper-bg" bgcolor="#eef1f6" style="background-color:#eef1f6;">
        <tr>
            <td align="center" class="wrapper-td" style="padding: 40px 16px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="main-container" bgcolor="#ffffff" style="width:600px; max-width:600px; background-color:#ffffff; border-radius:18px; overflow:hidden;">

                    <!-- HEADER BANNER (brand fisso, riconoscibile in ogni email) -->
                    <tr>
                        <td class="header-banner" align="center" bgcolor="#14b8a6" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); padding: 36px 40px 46px 40px;">
                            <!--[if mso]>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" bgcolor="#14b8a6" style="padding: 36px 40px 46px 40px;">
                            <![endif]-->
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td align="center" bgcolor="#ffffff" style="background-color:#ffffff; width:96px; height:96px; border-radius:48px; padding:4px; box-shadow: 0 10px 22px -6px rgba(6,30,25,0.4);">
                                        <!--[if mso]>
                                        <table role="presentation" width="88" height="88"><tr><td align="center" valign="middle" bgcolor="#14b8a6" style="border-radius:44px;">
                                        <![endif]-->
                                        <table role="presentation" width="88" height="88" cellpadding="0" cellspacing="0" bgcolor="#14b8a6" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); border-radius:44px;">
                                            <tr>
                                                <td align="center" valign="middle" style="padding:4px;">
                                                    <table role="presentation" width="80" height="80" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="background-color:#ffffff; border-radius:40px;">
                                                        <tr>
                                                            <td align="center" valign="middle">
                                                                <img src="{{ asset('images/logo/logo_email_push.png') }}"
                                                                     alt="Expir0" width="56"
                                                                     style="width:56px; height:auto; vertical-align:middle; display:inline-block; border:0;">
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
                            </table>
                            <!--[if mso]>
                            </td></tr></table>
                            <![endif]-->
                        </td>
                    </tr>

                    <!-- ALERT BANNER: colore di urgenza, INVARIATO rispetto alla logica originale -->
                    <tr>
                        <td align="center" bgcolor="{{ $color }}" style="background-color:{{ $color }}; padding: 16px 20px;">
                            <span class="alert-banner-text" style="color:#ffffff; font-weight:700; font-size:15px; letter-spacing:0.5px; text-transform:uppercase; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                                {{ $icon }} {{ $title }}
                            </span>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td class="content-td" style="padding: 32px 40px 40px 40px; background-color:#ffffff;">

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @if(isset($rule_type))
                                <tr>
                                    <td align="center" style="padding-bottom: 18px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                @php
                                                    $badgeColors = [
                                                        'badge-custom'     => ['bg' => '#ede9fe', 'fg' => '#7c3aed'],
                                                        'badge-global'     => ['bg' => '#f3f4f6', 'fg' => '#6b7280'],
                                                        'badge-system'     => ['bg' => '#e0f2fe', 'fg' => '#0284c7'],
                                                        'badge-sollecito'  => ['bg' => '#fee2e2', 'fg' => '#dc2626'],
                                                    ];
                                                    $bc = $badgeColors[$ruleBadgeClass] ?? $badgeColors['badge-system'];
                                                @endphp
                                                <td bgcolor="{{ $bc['bg'] }}" style="background-color:{{ $bc['bg'] }}; border-radius:20px; padding: 6px 14px;">
                                                    <span style="color:{{ $bc['fg'] }}; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                                                        {{ $ruleBadgeLabel }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="title-text" align="center" style="font-size:22px; font-weight:700; color:#0f172a; letter-spacing:-0.3px; line-height:1.3; padding-bottom: 20px;">
                                        {{ $tipoFormattato }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="message-text" style="font-size:15px; line-height:1.65; color:#475569; padding-bottom: 4px;">
                                        {!! $message !!}
                                    </td>
                                </tr>
                            </table>

                            <!-- DETAILS BOX -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="details-box-td" bgcolor="#f8fafc" style="background-color:#f8fafc; border:1px solid #eef1f6; border-radius:10px; margin-top:24px;">
                                <tr>
                                    <td style="padding: 6px 22px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="details-table">
                                            <tr>
                                                <td class="label label-text" style="padding:14px 0; font-size:14px; font-weight:600; color:#6b7280; border-bottom:1px solid #eef1f6; width:45%;">Tipo Scadenza</td>
                                                <td class="value value-text" style="padding:14px 0; font-size:14px; font-weight:700; color:#111827; text-align:right; border-bottom:1px solid #eef1f6;">{{ $tipoFormattato }}</td>
                                            </tr>
                                            <tr>
                                                <td class="label label-text" style="padding:14px 0; font-size:14px; font-weight:600; color:#6b7280; border-bottom:1px solid #eef1f6;">Intestatario / Rif.</td>
                                                <td class="value value-text" style="padding:14px 0; font-size:14px; font-weight:700; color:#111827; text-align:right; border-bottom:1px solid #eef1f6;">{{ $soggetto }}</td>
                                            </tr>
                                            <tr>
                                                <td class="label label-text" style="padding:14px 0; font-size:14px; font-weight:600; color:#6b7280; border-bottom:1px solid #eef1f6;">Data di Scadenza</td>
                                                <td class="value" style="padding:14px 0; font-size:16px; font-weight:700; color:{{ $color }}; text-align:right; border-bottom:1px solid #eef1f6;">{{ $dataScadenza }}</td>
                                            </tr>
                                            <tr>
                                                <td class="label label-text" style="padding:14px 0; font-size:14px; font-weight:600; color:#6b7280; {{ $deadline->note ? 'border-bottom:1px solid #eef1f6;' : '' }}">Importo Previsto</td>
                                                <td class="value value-text" style="padding:14px 0; font-size:14px; font-weight:700; color:#111827; text-align:right; {{ $deadline->note ? 'border-bottom:1px solid #eef1f6;' : '' }}">{{ $importoFormattato }}</td>
                                            </tr>
                                            @if($deadline->note)
                                            <tr>
                                                <td colspan="2" class="note-text" style="padding:14px 0; font-size:13.5px; color:#6b7280;">
                                                    <em>{{ $deadline->note }}</em>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="message-text" align="center" style="font-size:13.5px; color:#64748b; padding-top:22px;">
                                        Ricordati di accedere al portale e impostare lo stato su <strong style="color:#0d9488;">"Pagato / Rinnovato"</strong> una volta completata l'operazione.
                                    </td>
                                </tr>
                            </table>

                            <!-- BULLETPROOF BUTTON -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="btn-table" style="margin: 28px 0 4px;">
                                <tr>
                                    <td align="center">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ config('app.url') }}/deadlines" style="height:50px;v-text-anchor:middle;width:250px;" arcsize="50%" stroke="f" fillcolor="#14b8a6">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">Accedi alla Dashboard</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-->
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td bgcolor="#14b8a6" align="center" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); border-radius:26px; box-shadow: 0 10px 22px -6px rgba(16,185,129,0.45);">
                                                    <a href="{{ config('app.url') }}/deadlines" class="btn-link" style="display:inline-block; padding:15px 38px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; letter-spacing:-0.1px;">
                                                        Accedi alla Dashboard →
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
                            Questo è un messaggio automatico dal sistema di gestione scadenze.<br>
                            Si prega di non rispondere a questa email.
                            <div class="footer-links" style="margin-top:12px;">
                                <a href="{{ config('app.url') }}" style="color:#0d9488; text-decoration:none; margin:0 8px; font-weight:600;">Vai al sito</a>·
                                <a href="{{ config('app.url') }}/profile" style="color:#0d9488; text-decoration:none; margin:0 8px; font-weight:600;">Gestisci Notifiche</a>
                            </div>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>