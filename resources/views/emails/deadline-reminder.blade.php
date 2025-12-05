@component('mail::message')
# Scadenza imminente

@if($deadline->tipo === 'patente' && $deadline->user_id)
La **Patente** di **{{ $deadline->user->name }}** scadrà il **{{ $deadline->data_scadenza->format('d/m/Y') }}**.
@elseif($deadline->vehicle_id)
Il **{{ ucfirst($deadline->tipo) }}** del veicolo **{{ $deadline->vehicle->targa }}** ({{ $deadline->vehicle->marca }} {{ $deadline->vehicle->modello }}) scadrà il **{{ $deadline->data_scadenza->format('d/m/Y') }}**.
@endif

@if($deadline->importo)
**Importo:** €{{ number_format($deadline->importo, 2, ',', '.') }}
@endif

@if($deadline->note)
**Note:** {{ $deadline->note }}
@endif

@component('mail::button', ['url' => route('deadlines.index')])
Vai al pannello
@endcomponent

Grazie,<br>
{{ config('app.name') }}
@endcomponent