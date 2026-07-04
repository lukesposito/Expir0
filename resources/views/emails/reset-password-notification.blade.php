{{-- resources/views/emails/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="it" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Reimposta la tua password</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <style>
        table {border-collapse: collapse;}
        .fallback-font {font-family: Arial, sans-serif;}
    </style>
    <![endif]-->
    <style>
        /* Miglioria opzionale: se il client strippa questo blocco, l'email
           resta comunque colorata e leggibile grazie agli stili inline sotto. */
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
            .info-box-td { background-color: #19212f !important; }
            .info-box-text { color: #9aa7bd !important; }
            .info-box-text strong { color: #c9d3e3 !important; }
            .link-box-td { border-top-color: #232b3d !important; color:#6b7690 !important; }
            .info-box-td { border-left-color: #10b981 !important; }
            .footer-td { background-color: #0d1220 !important; color:#6b7690 !important; }
            .footer-links a { color: #93a1b7 !important; }
        }

        @media screen and (max-width: 600px) {
            .wrapper-td { padding: 0 !important; }
            .main-container { width:100% !important; border-radius:0 !important; }
            .header-banner { padding: 32px 24px 44px 24px !important; }
            .content-td { padding-left: 24px !important; padding-right: 24px !important; }
            .footer-td { padding-left: 24px !important; padding-right: 24px !important; }
            .title-text { font-size: 20px !important; }
            .btn-table { width: 100% !important; }
            .btn-link { display:block !important; }
        }
    </style>
</head>
<body class="wrapper-bg" style="margin:0; padding:0; background-color:#eef1f6;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        Reimposta la password del tuo account Expir0. Il link scadrà tra {{ $count }} minuti.
        &#8203;&#847; &#8203;&#847; &#8203;&#847; &#8203;&#847; &#8203;&#847;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="wrapper-bg" bgcolor="#eef1f6" style="background-color:#eef1f6;">
        <tr>
            <td align="center" class="wrapper-td" style="padding: 40px 16px;">

                <table role="presentation" width="560" cellpadding="0" cellspacing="0" class="main-container" bgcolor="#ffffff" style="width:560px; max-width:560px; background-color:#ffffff; border-radius:18px; overflow:hidden;">

                    <!-- HEADER BANNER -->
                    <tr>
                        <td class="header-banner" align="center" bgcolor="#14b8a6" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); padding: 44px 40px 56px 40px;">
                            <!--[if mso]>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" bgcolor="#14b8a6" style="padding: 44px 40px 56px 40px;">
                            <![endif]-->
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td align="center" bgcolor="#ffffff" style="background-color:#ffffff; width:116px; height:116px; border-radius:58px; padding:4px; box-shadow: 0 12px 28px -6px rgba(6,30,25,0.4);">
                                        <!--[if mso]>
                                        <table role="presentation" width="108" height="108"><tr><td align="center" valign="middle" bgcolor="#14b8a6" style="border-radius:54px;">
                                        <![endif]-->
                                        <table role="presentation" width="108" height="108" cellpadding="0" cellspacing="0" bgcolor="#14b8a6" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); border-radius:54px;">
                                            <tr>
                                                <td align="center" valign="middle" style="padding:5px;">
                                                    <table role="presentation" width="98" height="98" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="background-color:#ffffff; border-radius:49px;">
                                                        <tr>
                                                            <td align="center" valign="middle">
                                                                <img src="{{ $message->embed(public_path('images/logo/logo_email_push.png')) }}"
                                                                     alt="Expir0"
                                                                     width="70"
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
                            </table>
                            <!--[if mso]>
                            </td></tr></table>
                            <![endif]-->
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td class="content-td" style="padding: 0 40px 40px 40px; background-color:#ffffff;">

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-top: 28px; padding-bottom: 18px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td bgcolor="#14b8a6" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); border-radius:20px; padding: 8px 16px;">
                                                    <span style="color:#ffffff; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                                                        🔒 Sicurezza Account
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom: 16px;">
                                        <h1 class="title-text" style="margin:0; font-size:23px; font-weight:700; color:#0f172a; letter-spacing:-0.3px; line-height:1.3;">
                                            Richiesta di ripristino password
                                        </h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="message-text" style="font-size:15px; line-height:1.65; color:#475569; padding-bottom: 8px;">
                                        Ciao,<br><br>
                                        Abbiamo ricevuto una richiesta per reimpostare la password del tuo account su <strong style="color:#0d9488;">Expir0</strong>. Per completare l'operazione e scegliere una nuova chiave d'accesso, tocca il pulsante qui sotto.
                                    </td>
                                </tr>
                            </table>

                            <!-- BULLETPROOF BUTTON -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="btn-table" style="margin: 32px 0;">
                                <tr>
                                    <td align="center">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $actionUrl }}" style="height:52px;v-text-anchor:middle;width:260px;" arcsize="50%" stroke="f" fillcolor="#14b8a6">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">Reimposta Password</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-->
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td bgcolor="#14b8a6" align="center" style="background-color:#14b8a6; background-image: linear-gradient(135deg, #22d3ee 0%, #10b981 100%); border-radius:28px; box-shadow: 0 10px 22px -6px rgba(16,185,129,0.45);">
                                                    <a href="{{ $actionUrl }}" class="btn-link" style="display:inline-block; padding:16px 42px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; letter-spacing:-0.1px;">
                                                        Reimposta Password →
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                            <!-- INFO BOX -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="info-box-td" bgcolor="#f0fdf9" style="background-color:#f0fdf9; border-radius:10px; border-left:4px solid #10b981;">
                                <tr>
                                    <td style="padding: 18px 20px;">
                                        <p class="info-box-text" style="margin:0; font-size:13.5px; line-height:1.6; color:#0f6455;">
                                            <strong style="color:#0b4a3f;">⏱ Nota di sicurezza:</strong> Questo link scadrà automaticamente tra <strong style="color:#0b4a3f;">{{ $count }} minuti</strong>. Se non hai richiesto tu questa modifica, ignora pure questo messaggio: i tuoi dati di accesso correnti rimarranno al sicuro.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- LINK FALLBACK -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="link-box-td" style="margin-top:28px; padding-top:22px; border-top:1px solid #eef1f6; font-size:12px; line-height:1.6; color:#94a3b8; word-break:break-word;">
                                <tr>
                                    <td>
                                        Se il pulsante non funziona, copia e incolla questo link nel browser:<br>
                                        <a href="{{ $actionUrl }}" style="color:#0d9488; text-decoration:none;">{{ $actionUrl }}</a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td class="footer-td" align="center" bgcolor="#f0fdf9" style="padding: 26px 40px; font-size:12px; line-height:1.6; color:#5c8a7d; background-color:#f0fdf9; border-top:3px solid #10b981;">
                            Questo è un messaggio automatico generato dai sistemi di sicurezza Expir0.<br>
                            Ti preghiamo di non rispondere direttamente a questo indirizzo e-mail.
                            <div class="footer-links" style="margin-top:12px;">
                                <a href="{{ config('app.url') }}" style="color:#0d9488; text-decoration:none; margin:0 8px; font-weight:600;">Sito Web</a>·
                                <a href="{{ config('app.url') }}/privacy" style="color:#0d9488; text-decoration:none; margin:0 8px; font-weight:600;">Privacy Policy</a>
                            </div>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>