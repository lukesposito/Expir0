@php
    // --- LOGICA DI PRESENTAZIONE IN BASE ALL'URGENZA ---
    $color = '#4f46e5'; // Colore base (Indigo)
    $icon = 'ℹ️';
    $title = 'Promemoria Scadenza';
    $message = '';
    $isUrgent = false;

    if ($step === 'year') {
        $color = '#3b82f6'; // Blu aziendale
        $title = 'Avviso di Scadenza Annuale';
        $message = 'Gentile Utente,<br><br>Ti informiamo preventivamente che il seguente documento o servizio giungerà a scadenza durante l\'anno in corso. Ti invitiamo a prenderne nota per pianificare le relative pratiche di rinnovo.';
    } elseif ($step === '30') {
        $color = '#f59e0b'; // Ambra
        $icon = '🔔';
        $title = 'Scadenza a 30 giorni';
        $message = 'Gentile Utente,<br><br>Ti ricordiamo che manca circa un mese alla scadenza indicata di seguito. Ti consigliamo di preparare la documentazione necessaria o procedere al pagamento nei prossimi giorni.';
    } elseif ($step === '15') {
        $color = '#ea580c'; // Arancione
        $icon = '⚠️';
        $title = 'Scadenza a 15 giorni';
        $message = 'Gentile Utente,<br><br>Attenzione: mancano solo 15 giorni alla scadenza. Ti invitiamo a regolarizzare la tua posizione al più presto per evitare sanzioni o interruzioni del servizio.';
    } elseif ($step === '7') {
        $color = '#dc2626'; // Rosso intenso
        $icon = '🚨';
        $title = 'AVVISO URGENTE: Scadenza a 7 giorni';
        $message = 'Gentile Utente,<br><br><strong>La scadenza indicata è imminente.</strong> È richiesto un intervento immediato per evitare la decadenza dei termini legali o contrattuali. Si prega di provvedere il prima possibile.';
        $isUrgent = true;
    }

    // --- FORMATTAZIONE DEI DATI ---
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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; color: #374151; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f3f4f6; padding: 40px 0; }
        .main-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .alert-banner { padding: 16px 20px; text-align: center; color: #ffffff; font-weight: bold; font-size: 16px; letter-spacing: 0.5px; text-transform: uppercase; }
        .content { padding: 30px 40px; }
        .message { font-size: 15px; line-height: 1.6; color: #4b5563; margin-bottom: 30px; }
        .details-box { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 30px; }
        .details-table { width: 100%; border-collapse: collapse; }
        .details-table td { padding: 10px 0; font-size: 15px; border-bottom: 1px solid #e5e7eb; }
        .details-table tr:last-child td { border-bottom: none; }
        .label { font-weight: 600; color: #6b7280; width: 40%; }
        .value { font-weight: 700; color: #111827; text-align: right; }
        .value-highlight { color: {{ $color }}; font-size: 16px; }
        .btn-container { text-align: center; margin: 30px 0 10px; }
        .btn { display: inline-block; background-color: #111827; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: 600; font-size: 15px; transition: background-color 0.2s; }
        .btn:hover { background-color: #374151; }
        .footer { text-align: center; padding: 30px; font-size: 12px; color: #9ca3af; background-color: #f9fafb; border-top: 1px solid #f3f4f6; }
        .footer-links { margin-top: 10px; }
        .footer-links a { color: #6b7280; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-container">
            
            <div class="header" style="text-align: center; padding: 30px 20px; background-color: #ffffff; border-bottom: 1px solid #f3f4f6;">
                <img src="{{ config('app.url') }}/images/logo.png" alt="Expir0 Logo" style="max-height: 65px; width: auto; margin: 0 auto 10px auto; display: block; border: 0;">
                <div style="font-size: 26px; font-weight: 900; color: #111827; letter-spacing: -0.5px; font-family: sans-serif;">
                    Expir<span style="color: #4f46e5;">0</span>
                </div>
            </div>

            <div class="alert-banner" style="background-color: {{ $color }};">
                {{ $icon }} {{ $title }}
            </div>

            <div class="content">
                <div class="message">
                    {!! $message !!}
                </div>

                <div class="details-box">
                    <table class="details-table">
                        <tr>
                            <td class="label">Tipo Scadenza</td>
                            <td class="value">{{ $tipoFormattato }}</td>
                        </tr>
                        <tr>
                            <td class="label">Intestatario / Rif.</td>
                            <td class="value">{{ $soggetto }}</td>
                        </tr>
                        <tr>
                            <td class="label">Data di Scadenza</td>
                            <td class="value value-highlight" style="{{ $isUrgent ? 'color: #dc2626;' : '' }}">
                                {{ $dataScadenza }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Importo Previsto</td>
                            <td class="value">{{ $importoFormattato }}</td>
                        </tr>
                        
                        @if($deadline->note)
                        <tr>
                            <td class="label" style="vertical-align: top;">Note Aggiuntive</td>
                            <td class="value" style="font-weight: 400; text-align: left; padding-left: 15px;">
                                <em>{{ $deadline->note }}</em>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>

                <div class="message" style="font-size: 14px; text-align: center; margin-bottom: 0;">
                    Ricordati di accedere al portale e impostare lo stato su <strong>"Pagato / Rinnovato"</strong> una volta completata l'operazione.
                </div>

                <div class="btn-container">
                    <a href="{{ config('app.url') }}/deadlines" class="btn">
                        Accedi alla Dashboard
                    </a>
                </div>
            </div>

            <div class="footer">
                Questo è un messaggio automatico dal sistema di gestione scadenze.<br>
                Si prega di non rispondere a questa email.
                <div class="footer-links">
                    <a href="{{ config('app.url') }}">Vai al sito</a> | <a href="{{ config('app.url') }}/profile">Gestisci Notifiche</a>
                </div>
            </div>

        </div>
    </div>
</body>
</html>