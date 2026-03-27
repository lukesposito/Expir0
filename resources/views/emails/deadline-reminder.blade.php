@component('mail::message')
# Ciao {{ $user->name }},

Ti informiamo che ci sono **{{ $deadlines->count() }} scadenze imminenti** registrate nel sistema che richiedono la tua attenzione nei prossimi giorni.

@foreach($deadlines as $deadline)
@component('mail::panel')
### {{ strtoupper(str_replace('_', ' ', $deadline->tipo)) }} {{ $deadline->vehicle ? ' - ' . $deadline->vehicle->targa : ($deadline->user ? ' - ' . $deadline->user_name : '') }}
**Data Scadenza:** {{ \Carbon\Carbon::parse($deadline->data_scadenza)->format('d/m/Y') }}  
@if($deadline->importo > 0)
**Importo Previsto:** € {{ number_format($deadline->importo, 2, ',', '.') }}  
@endif
@if($deadline->note)
**Note:** {{ $deadline->note }}  
@endif
@endcomponent
@endforeach

Ti invitiamo a verificare e regolarizzare queste posizioni il prima possibile per evitare sanzioni. Una volta effettuato il pagamento, ricordati di aggiornare lo stato nel gestionale.

@component('mail::button', ['url' => route('deadlines.index'), 'color' => 'error'])
Gestisci Scadenze Ora
@endcomponent

Grazie per aver utilizzato **{{ config('app.name') }}**.<br>
*Il Team Amministrativo*
@endcomponent