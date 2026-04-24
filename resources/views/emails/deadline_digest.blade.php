<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riepilogo Scadenze</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; color: #374151; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .content { padding: 30px; }
        .intro { font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 25px; }
        
        .section { border-radius: 8px; overflow: hidden; margin-bottom: 25px; border: 1px solid #e5e7eb; }
        .section-title { padding: 12px 20px; font-weight: bold; color: white; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
        .bg-red { background-color: #ef4444; }
        .bg-orange { background-color: #f59e0b; }
        .bg-blue { background-color: #3b82f6; }
        
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table tr { border-bottom: 1px solid #f3f4f6; }
        .item-table tr:last-child { border-bottom: none; }
        .item-table td { padding: 16px 20px; vertical-align: top; }
        .item-title { font-weight: bold; color: #111827; font-size: 16px; margin-bottom: 4px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
        .item-subtitle { color: #6b7280; font-size: 14px; margin-bottom: 6px; }
        .item-date { text-align: right; width: 120px; }
        .item-date-value { font-weight: bold; color: #111827; font-size: 15px; }
        .item-days { font-size: 13px; font-weight: 600; margin-top: 4px; display: inline-block; padding: 2px 8px; border-radius: 12px; }
        .days-red { background-color: #fee2e2; color: #b91c1c; }
        .days-orange { background-color: #fef3c7; color: #b45309; }
        .days-blue { background-color: #dbeafe; color: #1d4ed8; }
        
        .badge { font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-custom { background-color: #ede9fe; color: #7c3aed; border: 1px solid #ddd6fe; }
        .badge-global { background-color: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
        .badge-system { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
        .badge-sollecito { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

        .btn-container { text-align: center; margin-top: 30px; margin-bottom: 10px; }
        .btn { display: inline-block; background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; }
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div style="background-color: #1e1b4b; padding: 25px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 22px;">Gestione Scadenze</h1>
            </div>
            
            <div class="content">
                <div class="intro">
                    Ciao,<br>
                    ecco il tuo riepilogo automatico. I seguenti avvisi sono stati generati in base alle tue <strong>regole di notifica personalizzate</strong> o alle impostazioni globali del tuo account.
                </div>

                @php
                    $renderBadge = function($tipoRegola) {
                        if ($tipoRegola === 'personalizzata') return '<span class="badge badge-custom">Regola Specifica</span>';
                        if ($tipoRegola === 'globale') return '<span class="badge badge-global">Regola Globale</span>';
                        if ($tipoRegola === 'sollecito') return '<span class="badge badge-sollecito">Sollecito Scaduto</span>';
                        return '<span class="badge badge-system">Regola di Sistema</span>';
                    };
                @endphp

                @if(!empty($notifiche['urgente']))
                <div class="section">
                    <div class="section-title bg-red">🚨 Scadenze Urgenti / Scadute</div>
                    <table class="item-table">
                        @foreach($notifiche['urgente'] as $item)
                        <tr>
                            <td>
                                <div class="item-title">
                                    {{ $item['tipo'] }} 
                                    {!! $renderBadge($item['tipo_regola'] ?? 'sistema') !!}
                                </div>
                                <div class="item-subtitle">{{ $item['soggetto'] }}</div>
                                @if($item['importo'] !== '-')
                                <div style="font-weight: 600; color: #111827; font-size: 15px;">{{ $item['importo'] }}</div>
                                @endif
                            </td>
                            <td class="item-date">
                                <div class="item-date-value">{{ $item['data'] }}</div>
                                <div class="item-days days-red">
                                    {{ $item['giorni'] < 0 ? 'Scaduto da ' . abs($item['giorni']) . ' gg' : '-' . $item['giorni'] . ' giorni' }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                @if(!empty($notifiche['breve']))
                <div class="section">
                    <div class="section-title bg-orange">⚠️ Scadenze a Breve Termine</div>
                    <table class="item-table">
                        @foreach($notifiche['breve'] as $item)
                        <tr>
                            <td>
                                <div class="item-title">
                                    {{ $item['tipo'] }}
                                    {!! $renderBadge($item['tipo_regola'] ?? 'sistema') !!}
                                </div>
                                <div class="item-subtitle">{{ $item['soggetto'] }}</div>
                            </td>
                            <td class="item-date">
                                <div class="item-date-value">{{ $item['data'] }}</div>
                                <div class="item-days days-orange">-{{ $item['giorni'] }} giorni</div>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                @if(!empty($notifiche['avviso']))
                <div class="section">
                    <div class="section-title bg-blue">ℹ️ Prossime Scadenze</div>
                    <table class="item-table">
                        @foreach($notifiche['avviso'] as $item)
                        <tr>
                            <td>
                                <div class="item-title">
                                    {{ $item['tipo'] }}
                                    {!! $renderBadge($item['tipo_regola'] ?? 'sistema') !!}
                                </div>
                                <div class="item-subtitle">{{ $item['soggetto'] }}</div>
                            </td>
                            <td class="item-date">
                                <div class="item-date-value">{{ $item['data'] }}</div>
                                <div class="item-days days-blue">-{{ $item['giorni'] }} giorni</div>
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
                Questo è un riepilogo automatico generato dal tuo assistente scadenze.<br>
                <a href="{{ config('app.url') }}/profile" style="color: #6b7280; text-decoration: underline;">Modifica le tue regole di notifica</a>
            </div>
        </div>
    </div>
</body>
</html>