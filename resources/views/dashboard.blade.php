<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
            <p class="text-sm text-gray-600">
                {{ now()->locale('it')->isoFormat('dddd, D MMMM YYYY') }}
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

            <!-- SEMAFORO STATO GENERALE - Aggiungi dopo l'alert urgenze -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <!-- Rosso - Urgenti -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 {{ $scadenzeUrgenti > 0 ? 'border-red-500' : 'border-gray-300' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">🔴 Urgenti (0-10gg)</p>
                            <p class="text-3xl font-black {{ $scadenzeUrgenti > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                {{ $scadenzeUrgenti }}
                            </p>
                        </div>
                        <div class="w-16 h-16 rounded-full flex items-center justify-center {{ $scadenzeUrgenti > 0 ? 'bg-red-100 animate-pulse' : 'bg-gray-100' }}">
                            <span class="text-3xl">{{ $scadenzeUrgenti > 0 ? '🔴' : '✓' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Giallo - In arrivo -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 {{ ($scadenzeImminenti - $scadenzeUrgenti) > 0 ? 'border-yellow-500' : 'border-gray-300' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">🟡 In Arrivo (11-30gg)</p>
                            <p class="text-3xl font-black {{ ($scadenzeImminenti - $scadenzeUrgenti) > 0 ? 'text-yellow-600' : 'text-gray-400' }}">
                                {{ $scadenzeImminenti}}
                            </p>
                        </div>
                        <div class="w-16 h-16 rounded-full flex items-center justify-center {{ ($scadenzeImminenti - $scadenzeUrgenti) > 0 ? 'bg-yellow-100' : 'bg-gray-100' }}">
                            <span class="text-3xl">{{ ($scadenzeImminenti) > 0 ? '🟡' : '✓' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Verde - Tutto ok -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 {{ $scadenzeImminenti == 0 ? 'border-green-500' : 'border-gray-300' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">🟢 Tutto OK (31+gg)</p>
                            <p class="text-3xl font-black {{ $scadenzeImminenti == 0 ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $totaleScadenze - $scadenzeImminenti - $scadenzeUrgenti }}
                            </p>
                        </div>
                        <div class="w-16 h-16 rounded-full flex items-center justify-center {{ $scadenzeImminenti == 0 ? 'bg-green-100' : 'bg-gray-100' }}">
                            <span class="text-3xl">{{ $scadenzeImminenti == 0 ? '🟢' : '⏳' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ALERT SCADENZE URGENTI -->
            @if($scadenzeUrgenti > 0)
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="font-bold text-red-800">Attenzione!</h3>
                        <p class="text-sm text-red-700">Hai <strong>{{ $scadenzeUrgenti }}</strong> scadenze urgenti da pagare!</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- CARDS STATISTICHE -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Card Budget Mensile -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">💰 Budget Mensile Consigliato</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Accantona ogni mese</p>
                        <p class="text-2xl font-black text-blue-600">€{{ number_format($budgetMensileConsigliato, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Speso questo mese</p>
                        <p class="text-2xl font-black text-gray-900">€{{ number_format($spesoQuestoMese, 2, ',', '.') }}</p>
                    </div>
                    @if($spesoQuestoMese > $budgetMensileConsigliato)
                        <p class="text-xs text-red-600 font-medium">⚠️ Hai superato il budget!</p>
                    @else
                        <p class="text-xs text-green-600 font-medium">✓ Budget rispettato</p>
                    @endif
                </div>
            </div>
                
                <!-- Card Veicoli -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-black transform transition hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium mb-1">Veicoli</p>
                            <p class="text-4xl font-black">{{ $totaleVeicoli }}</p>
                            <p class="text-blue-100 text-xs mt-2">Totale registrati</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card Scadenze Imminenti -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-black transform transition hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium mb-1">Scadenze Imminenti</p>
                            <p class="text-4xl font-black">{{ $scadenzeImminenti }}</p>
                            <p class="text-orange-100 text-xs mt-2">Prossimi 30 giorni</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card Costi Annuali -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-black transform transition hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium mb-1">Costi {{ now()->year }}</p>
                            <p class="text-4xl font-black">€{{ number_format($costiAnnuali, 0, ',', '.') }}</p>
                            <p class="text-green-100 text-xs mt-2">Totale anno corrente</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card Spesa Media -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-black transform transition hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium mb-1">Media per Veicolo</p>
                            <p class="text-4xl font-black">€{{ number_format($spesaMediaVeicolo, 0, ',', '.') }}</p>
                            <p class="text-purple-100 text-xs mt-2">Spesa annuale media</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEZIONE PRINCIPALE: 2 COLONNE -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- COLONNA SINISTRA: PROSSIME SCADENZE (2/3) -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                            <h3 class="text-xl font-bold text-black flex items-center">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="ml-1">Prossime Scadenze (30 giorni)</span>
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            @if($prossimaScadenze->count() > 0)
                                <div class="space-y-3">
                                    @foreach($prossimaScadenze as $scadenza)
                                    <div class="flex items-center justify-between p-4 rounded-lg border-2 transition hover:shadow-md {{ $scadenza['urgente'] ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-gray-50' }}">
                                        <div class="flex items-center space-x-4 flex-1">
                                            <!-- Icona Tipo -->
                                            <div class="flex-shrink-0">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $scadenza['tipo'] === 'bollo' ? 'bg-yellow-100' : ($scadenza['tipo'] === 'assicurazione' ? 'bg-blue-100' : ($scadenza['tipo'] === 'patente' ? 'bg-purple-100' : 'bg-orange-100')) }}">
                                                    @if($scadenza['tipo'] === 'bollo')
                                                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    @elseif($scadenza['tipo'] === 'assicurazione')
                                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                        </svg>
                                                    @elseif($scadenza['tipo'] === 'patente')
                                                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                                        </svg>
                                                    @else
                                                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Info Scadenza -->
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-gray-900">
                                                        @if($scadenza['vehicle_targa'])
                                                            {{ $scadenza['vehicle_targa'] }}
                                                            <span class="text-sm text-gray-500">{{ $scadenza['vehicle_marca'] }}</span>
                                                        @else
                                                            {{ $scadenza['user_name'] }}
                                                        @endif
                                                    </span>
                                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $scadenza['tipo'] === 'bollo' ? 'bg-yellow-100 text-yellow-800' : ($scadenza['tipo'] === 'assicurazione' ? 'bg-blue-100 text-blue-800' : ($scadenza['tipo'] === 'patente' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800')) }}">
                                                        {{ ucfirst($scadenza['tipo']) }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
                                                    <span>📅 {{ $scadenza['data_scadenza_formatted'] }}</span>
                                                    <span class="font-semibold">€{{ $scadenza['importo_formatted'] }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Badge Giorni -->
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $scadenza['urgente'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $scadenza['giorni_rimanenti'] }} gg
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="{{ route('deadlines.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold text-sm">
                                        Vedi tutte le scadenze →
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Nessuna scadenza nei prossimi 30 giorni</p>
                                    <p class="text-sm text-gray-400 mt-1">Tutto sotto controllo! 🎉</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- COLONNA DESTRA: RIEPILOGO (1/3) -->
                <div class="space-y-6">
                    
                    <!-- Card Situazione Pagamenti -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                            <h3 class="text-lg font-bold text-black flex items-center">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="ml-1">Situazione {{ now()->year }}</span>
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-600">Già pagato</span>
                                        <span class="text-lg font-bold text-green-600">€{{ number_format($pagatoQuestAnno, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $costiAnnuali > 0 ? ($pagatoQuestAnno / $costiAnnuali * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-600">Da pagare</span>
                                        <span class="text-lg font-bold text-orange-600">€{{ number_format($daPagareQuestAnno, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $costiAnnuali > 0 ? ($daPagareQuestAnno / $costiAnnuali * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                                
                                <div class="pt-4 border-t border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-gray-700">Totale anno</span>
                                        <span class="text-xl font-black text-gray-900">€{{ number_format($costiAnnuali, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Revisioni in Scadenza -->
                    @if($revisioniInScadenza->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-4">
                            <h3 class="text-lg font-bold text-white flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Revisioni in Scadenza
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($revisioniInScadenza as $revisione)
                                <div class="flex items-center justify-between p-3 rounded-lg {{ $revisione['urgente'] ? 'bg-red-50 border border-red-200' : 'bg-gray-50' }}">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $revisione['vehicle_targa'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $revisione['vehicle_marca'] }}</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $revisione['data_prossima'] }}</p>
                                    </div>
                                    <span class="text-sm font-bold {{ $revisione['urgente'] ? 'text-red-600' : 'text-gray-600' }}">
                                        {{ $revisione['giorni_rimanenti'] }} gg
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Quick Links -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
                            <h3 class="text-lg font-bold text-black">🔗 Quick Links</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2">
                                <a href="{{ route('deadlines.index') }}" class="block px-4 py-3 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium transition">
                                    📅 Gestione Scadenze
                                </a>
                                <a href="{{ route('vehicles.index') }}" class="block px-4 py-3 rounded-lg bg-green-50 hover:bg-green-100 text-green-700 font-medium transition">
                                    🚗 I Miei Veicoli
                                </a>
                                <a href="{{ route('revisioni.index') }}" class="block px-4 py-3 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 font-medium transition">
                                    🔧 Revisioni
                                </a>
                                <a href="{{ route('patenti.index') }}" class="block px-4 py-3 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium transition">
                                    🪪 Patenti
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRAFICO SPESE PER TIPO -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-black flex items-center">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="ml-1">Distribuzione Spese {{ now()->year }}</span>
                    </h3>
                </div>
                <div class="p-6">
                    <canvas id="speseChart" height="80"></canvas>
                </div>
            </div>

            <!-- CALENDARIO SCADENZE MESE -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-black flex items-center">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="ml-1">Calendario {{ now()->locale('it')->isoFormat('MMMM YYYY') }}</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div id="calendar"></div>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- FullCalendar -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/it.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // ========== GRAFICO SPESE ==========
            const ctx = document.getElementById('speseChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Bollo', 'Assicurazione', 'Patente', 'Revisione'],
                    datasets: [{
                        label: 'Spesa €',
                        data: [
                            {{ $spesePerTipo['bollo'] ?? 0 }},
                            {{ $spesePerTipo['assicurazione'] ?? 0 }},
                            {{ $spesePerTipo['patente'] ?? 0 }},
                            {{ $spesePerTipo['revisione'] ?? 0 }}
                        ],
                        backgroundColor: [
                            'rgba(234, 179, 8, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(147, 51, 234, 0.8)',
                            'rgba(249, 115, 22, 0.8)'
                        ],
                        borderColor: [
                            'rgb(234, 179, 8)',
                            'rgb(59, 130, 246)',
                            'rgb(147, 51, 234)',
                            'rgb(249, 115, 22)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '€' + value.toLocaleString('it-IT');
                                }
                            }
                        }
                    }
                }
            });

            // ========== CALENDARIO ==========
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'it',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                events: @json($scadenzeMese),
                eventClick: function(info) {
                    alert('Scadenza: ' + info.event.title + '\nData: ' + info.event.start.toLocaleDateString('it-IT') + '\nImporto: €' + info.event.extendedProps.importo);
                },
                eventContent: function(arg) {
                    return {
                        html: '<div class="fc-event-main-frame"><div class="fc-event-title-container"><div class="fc-event-title fc-sticky">' + arg.event.title + '</div></div></div>'
                    };
                }
            });
            calendar.render();
        });
    </script>

    <style>
        /* FullCalendar custom style */
        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
            font-size: 0.75rem;
        }
        
        .fc-daygrid-event {
            white-space: normal;
        }
        
        .fc-day-today {
            background-color: #eff6ff !important;
        }
    </style>
</x-app-layout>