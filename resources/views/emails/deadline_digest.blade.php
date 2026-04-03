<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riepilogo Scadenze</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; color: #374151; }
        .wrapper { width: 100%; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header { text-align: center; padding: 30px; background-color: #111827; color: white; }
        .header h1 { margin: 0; font-size: 24px; letter-spacing: -0.5px; }
        .content { padding: 30px; }
        .intro { font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 25px; }
        
        .section { border-radius: 8px; overflow: hidden; margin-bottom: 25px; border: 1px solid #e5e7eb; }
        .section-title { padding: 12px 20px; font-weight: bold; color: white; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .bg-red { background-color: #dc2626; }
        .bg-orange { background-color: #ea580c; }
        .bg-amber { background-color: #f59e0b; }
        .bg-blue { background-color: #3b82f6; }
        
        .item-table { width: 100%; border-collapse: collapse; background: #fafafa; }
        .item-table td { padding: 15px 20px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .item-table tr:last-child td { border-bottom: none; }
        .item-title { font-weight: bold; color: #111827; font-size: 15px; margin-bottom: 4px; }
        .item-subtitle { color: #6b7280; font-size: 13px; }
        .item-date { text-align: right; font-weight: bold; color: #111827; }
        .item-days { font-size: 12px; margin-top: 4px; }

        .btn-container { text-align: center; margin: 40px 0 20px; }
        .btn { display: inline-block; background-color: #4f46e5; color: white !important; text-decoration: none; padding: 14px 30px; border-radius: 6px; font-weight: bold; font-size: 15px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #9ca3af; background-color: #f9fafb; border-top: 1px solid #f3f4f6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Riepilogo Scadenze</h1>
            </div>

            <div class="content">
                <div class="intro">
                    Gentile Utente,<br><br>
                    Ecco il riepilogo aggiornato delle prossime scadenze. Ti preghiamo di verificare le voci riportate di seguito per evitare sanzioni o interruzioni di servizio.
                </div>

                @if(!empty($notifiche['urgente']))
                <div class="section">
                    <div class="section-title bg-red">🚨 Azione Richiesta (Entro 7 giorni)</div>
                    <table class="item-table">
                        @foreach($notifiche['urgente'] as $item)
                        <tr>
                            <td>
                                <div class="item-title">{{ $item['tipo'] }}</div>
                                <div class="item-subtitle">{{ $item['soggetto'] }} • {{ $item['importo'] }}</div>
                            </td>
                            <td class="item-date">
                                {{ $item['data'] }}<br>
                                <div class="item-days" style="color: #dc2626;">Mancano {{ $item['giorni'] }} gg</div>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                @if(!empty($notifiche['breve']))
                <div class="section">
                    <div class="section-title bg-orange">⚠️ Scadenza a Breve (Entro 15 giorni)</div>
                    <table class="item-table">
                        @foreach($notifiche['breve'] as $item)
                        <tr>
                            <td>
                                <div class="item-title">{{ $item['tipo'] }}</div>
                                <div class="item-subtitle">{{ $item['soggetto'] }} • {{ $item['importo'] }}</div>
                            </td>
                            <td class="item-date">
                                {{ $item['data'] }}<br>
                                <div class="item-days" style="color: #ea580c;">Mancano {{ $item['giorni'] }} gg</div>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                @if(!empty($notifiche['avviso']))
                <div class="section">
                    <div class="section-title bg-amber">🔔 In Avvicinamento (Entro 30 giorni)</div>
                    <table class="item-table">
                        @foreach($notifiche['avviso'] as $item)
                        <tr>
                            <td>
                                <div class="item-title">{{ $item['tipo'] }}</div>
                                <div class="item-subtitle">{{ $item['soggetto'] }} • {{ $item['importo'] }}</div>
                            </td>
                            <td class="item-date">
                                {{ $item['data'] }}<br>
                                <div class="item-days" style="color: #d97706;">Mancano {{ $item['giorni'] }} gg</div>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                @if(!empty($notifiche['annuale']))
                <div class="section">
                    <div class="section-title bg-blue">📅 Da pianificare quest'anno</div>
                    <table class="item-table">
                        @foreach($notifiche['annuale'] as $item)
                        <tr>
                            <td>
                                <div class="item-title">{{ $item['tipo'] }}</div>
                                <div class="item-subtitle">{{ $item['soggetto'] }}</div>
                            </td>
                            <td class="item-date">
                                {{ $item['data'] }}
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                <div class="btn-container">
                    <a href="{{ config('app.url') }}/deadlines" class="btn">Accedi al Portale</a>
                </div>
            </div>

            <div class="footer">
                Questo è un riepilogo automatico generato dal sistema.<br>
                Se hai già provveduto al pagamento, accedi al portale per aggiornare lo stato.
            </div>
        </div>
    </div>
</body>
</html>