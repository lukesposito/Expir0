<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Scadenze
        </h2>
    </x-slot>

    <details class="bg-indigo-50 p-5 rounded-xl mb-8 border-2 border-indigo-200 shadow-sm mt-4">
        <summary class="font-black text-indigo-800 cursor-pointer text-xl flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-2xl"></i>
            Guida passo-passo: Come si usa questa lista? (Clicca per leggere)
        </summary>
        <div class="mt-5 text-slate-700 leading-relaxed space-y-6 text-base">
            
            <p class="font-medium text-lg bg-white p-3 rounded-lg border border-indigo-100">
                📅 <strong>Questa è la tua Agenda Elettronica.</strong> Qui dentro c'è l'elenco completo di <strong>TUTTE</strong> le tue scadenze, ordinate in automatico da quella più vicina a quella più lontana. 
            </p>

            <div>
                <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">🔍 1. La barra di ricerca (in alto)</h4>
                <p>È la casella bianca dove c'è scritto "Cerca per tipo, targa...". Ti serve se vuoi trovare subito qualcosa senza scorrere tutta la lista. Prova a scriverci dentro "Bollo" oppure la targa della tua auto (es. "AB123CD") o il nome di tuo figlio: la lista sotto si aggiornerà all'istante facendoti vedere solo quello che hai cercato!</p>
            </div>

            <div>
                <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">📋 2. Come leggere la lista</h4>
                <p class="mb-2">A seconda che tu stia usando il telefono o il computer, l'elenco si adatta per essere sempre leggibile:</p>
                <ul class="space-y-3 ml-2">
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-500 mt-1"><i class="fa-solid fa-desktop"></i></span>
                        <div><strong>Se sei al computer:</strong> Vedrai una tabella classica. Ogni riga è una scadenza. Leggendo da sinistra verso destra troverai: il veicolo o la persona, che cos'è, quando scade, quanto costa, e quanti giorni mancano.</div>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-500 mt-1"><i class="fa-solid fa-mobile-screen"></i></span>
                        <div><strong>Se sei col telefono:</strong> Vedrai dei "riquadri" colorati uno sopra l'altro. Il colore cambia in base al tipo di documento (es. giallo per i bolli, verde per le revisioni).</div>
                    </li>
                </ul>
                <p class="mt-3 bg-red-50 p-2 rounded text-red-800 text-sm font-semibold border border-red-200">
                    <i class="fa-solid fa-triangle-exclamation"></i> Attenzione al colore dei giorni! Se i giorni mancanti sono scritti in ROSSO, significa che manca meno di un mese (o che è già scaduto!).
                </p>
            </div>

            <div>
                <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">✅ 3. Il tasto magico: L'interruttore "Stato"</h4>
                <p>Hai appena pagato il bollo in posta o rinnovato la carta d'identità in comune? Bravissimo!</p>
                <p class="mt-1">Cerca quella voce nella lista e <strong>clicca sull'interruttore a scorrimento</strong> (quello con la pallina che va da destra a sinistra). Diventerà verde con la scritta "PAGATO" o "RINNOVATA". In questo modo, l'importo verrà tolto dai soldi "da pagare" nella tua Dashboard principale e tu avrai la mente libera!</p>
            </div>

            <div>
                <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">🗑️ 4. Il pulsante rosso "Elimina"</h4>
                <p>Serve <strong>SOLO</strong> se hai fatto un errore. Ad esempio, se hai inserito una scadenza doppia o sbagliata per sbaglio.</p>
                <p class="mt-1 bg-amber-50 p-2 rounded text-amber-800 text-sm border border-amber-200">
                    <strong>Importante:</strong> Se elimini una scadenza da qui, cancellerai per sempre anche il documento a cui è collegata! Se hai venduto un'auto, non usare questo tasto: vai invece nella pagina "Veicoli" ed elimina l'auto intera da lì.
                </p>
            </div>

        </div>
    </details>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4" 
             x-data="deadlinesManager()"
             x-init="init()">

            <div class="mb-6">
                <div class="mobile-only-search">
                    <div class="relative">
                        <input type="text" 
                               x-model="searchQuery"
                               placeholder="Cerca tipo, targa, persona, spesa..." 
                               class="w-full pl-4 pr-10 py-3.5 bg-white border-2 border-gray-200 rounded-xl focus:border-black-500 focus:ring-4 focus:ring-black-100 transition text-base shadow-sm">
                        <div x-show="searchQuery" 
                             @click="searchQuery = ''"
                             class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1" x-show="searchQuery">
                        <span x-text="filteredDeadlines.length"></span> risultati trovati
                    </p>
                </div>
                
                <div class="desktop-only-search">
                    <div class="bg-white rounded-xl shadow-md p-4">
                        <div class="relative">
                            <input type="text" 
                                   x-model="searchQuery"
                                   placeholder="Cerca per tipo, targa, marca, persona o nome spesa..." 
                                   class="w-full pl-4 pr-10 py-3 bg-gray-50 border-2 border-gray-200 rounded-lg focus:border-black-500 focus:bg-white focus:ring-4 focus:ring-black-100 transition">
                            <div x-show="searchQuery" 
                                 @click="searchQuery = ''"
                                 class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3" x-show="searchQuery">
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold" x-text="filteredDeadlines.length"></span> risultati trovati
                            </p>
                            <button @click="searchQuery = ''" 
                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Cancella ricerca
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="desktop-only bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Soggetto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scadenza</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Importo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giorni</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stato</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(d, index) in filteredDeadlines" :key="d.id || index">
                                <tr class="hover:bg-gray-50 transition" :class="{ 'bg-red-50': d.giorni_rimanenti <= 10 }">
                                    <td class="px-6 py-4">
                                        <template x-if="d.tipo === 'patente' && d.user_name">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900" x-text="d.user_name"></div>
                                                <div class="text-sm text-gray-500">Patente</div>
                                            </div>
                                        </template>
                                        <template x-if="['bollo', 'assicurazione', 'revisione'].includes(d.tipo) && d.vehicle_targa">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 font-mono uppercase" x-text="d.vehicle_marca + ' ' + d.vehicle_modello"></div>
                                                <div class="text-sm text-gray-500 capitalize"><span x-text="d.vehicle_targa"></span></div>
                                            </div>
                                        </template>
                                        <template x-if="['carta_identita', 'tesserino_sanitario'].includes(d.tipo) && d.user_name">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900" x-text="d.user_name"></div>
                                                <div class="text-sm text-gray-500 capitalize" x-text="d.tipo.replace('_', ' ')"></div>
                                            </div>
                                        </template>
                                        <template x-if="d.tipo === 'spesa_abbonamento'">
                                            <div>
                                                <div class="text-sm font-bold text-violet-700" x-text="d.subscription_nome || 'Spesa'"></div>
                                                <div class="text-sm text-gray-500" x-text="(d.user_name !== 'N/D' ? d.user_name : (d.vehicle_targa || 'Famiglia'))"></div>
                                            </div>
                                        </template>
                                        
                                        <template x-if="!d.vehicle_targa && !d.user_name && d.tipo !== 'spesa_abbonamento'">
                                            <div class="text-sm text-gray-500">N/D</div>
                                        </template>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': d.tipo === 'bollo',
                                                'bg-orange-100 text-orange-800': d.tipo === 'assicurazione',
                                                'bg-pink-100 text-pink-800': d.tipo === 'patente',
                                                'bg-green-100 text-green-800': d.tipo === 'revisione',
                                                'bg-stone-100 text-stone-800': d.tipo === 'carta_identita',
                                                'bg-teal-100 text-teal-800': d.tipo === 'tesserino_sanitario',
                                                'bg-violet-100 text-violet-800': d.tipo === 'spesa_abbonamento'
                                            }">
                                            <span x-text="d.tipo === 'spesa_abbonamento' ? 'Spesa/Abbonamento' : d.tipo.replace('_', ' ').charAt(0).toUpperCase() + d.tipo.replace('_', ' ').slice(1)"></span>
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-sm text-gray-700 font-semibold" x-text="d.data_scadenza_formatted"></td>
                                    
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900" x-text="['carta_identita', 'tesserino_sanitario'].includes(d.tipo) ? 'N/D' : ('€' + d.importo_formatted)"></td>
                                    
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold" 
                                              :class="(d.tipo === 'patente' && d.scade_quest_anno) || d.giorni_rimanenti <= 30 ? 'text-red-600' : 'text-green-600'">
                                            <span x-show="d.tipo === 'patente' && d.scade_quest_anno" class="block text-xs uppercase text-red-600 mb-1">Scade quest'anno</span>
                                            <span x-text="d.giorni_rimanenti"></span> gg
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-center gap-3">
                                            <label class="switch switch-lg">
                                                <input type="checkbox" :checked="d.pagato" @change="togglePagato(d)">
                                                <span class="slider"></span>
                                            </label>
                                            
                                            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold shadow-sm"
                                                :class="d.pagato ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : 'bg-gradient-to-r from-red-500 to-red-600 text-white'">
                                                <span x-text="['carta_identita', 'tesserino_sanitario'].includes(d.tipo) ? (d.pagato ? '✓ RINNOVATA' : '✗ NON RINNOVATA') : (d.pagato ? '✓ PAGATO' : '✗ NON PAGATO')"></span>
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-5 text-center">
                                        <button @click="openDeleteModal(d)" class="btn-table-action btn-table-delete">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Elimina
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="filteredDeadlines.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="text-gray-500 font-medium">Nessuna scadenza trovata</p>
                                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mobile-only space-y-4 px-4">
                <template x-for="(d, index) in filteredDeadlines" :key="d.id || index">
                    <div class="bg-white rounded-2xl shadow-xl border overflow-hidden"
                         :class="d.giorni_rimanenti <= 10 ? 'border-red-300' : 'border-gray-200'">
                        
                        <div class="px-5 py-5 relative"
                             :style="`background: linear-gradient(135deg, ${
                                d.tipo === 'bollo' ? '#eab308, #ca8a04' :         
                                d.tipo === 'assicurazione' ? '#ee7300, #fa6400' : 
                                d.tipo === 'revisione' ? '#22c55e, #16a34a' :
                                d.tipo === 'carta_identita' ? '#a8a29e, #78716c' :
                                d.tipo === 'tesserino_sanitario' ? '#14b8a6, #0f766e' :
                                d.tipo === 'patente' ? '#ec4899, #db2777' :
                                d.tipo === 'spesa_abbonamento' ? '#8b5cf6, #6d28d9' : 
                                '#cbd5e1, #94a3b8'
                             })`">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full -ml-12 -mb-12"></div>
                            
                            <div class="relative flex items-start justify-between">
                                <div class="flex-1">
                                    <template x-if="d.tipo === 'patente' && d.user_name">
                                        <div>
                                            <h3 class="text-2xl font-black text-white tracking-tight mb-1">Patente</h3>
                                            <p class="text-base text-white opacity-90" x-text="d.user_name"></p>
                                        </div>
                                    </template>
                                    <template x-if="['bollo', 'assicurazione', 'revisione'].includes(d.tipo) && d.vehicle_targa">
                                        <div>
                                            <h3 class="text-2xl font-black text-white tracking-tight mb-1 uppercase font-mono" x-text="d.vehicle_marca + ' ' + d.vehicle_modello"></h3>
                                            <p class="text-base text-white opacity-90 capitalize"><span x-text="d.vehicle_targa"></span></p>
                                        </div>
                                    </template>
                                    <template x-if="d.tipo === 'carta_identita' && d.user_name">
                                        <div>
                                            <h3 class="text-2xl font-black text-white tracking-tight mb-1">Carta d'Identità</h3>
                                            <p class="text-base text-white opacity-90" x-text="d.user_name"></p>
                                        </div>
                                    </template>
                                    <template x-if="d.tipo === 'tesserino_sanitario' && d.user_name">
                                        <div>
                                            <h3 class="text-2xl font-black text-white tracking-tight mb-1">Tesserino Sanitario</h3>
                                            <p class="text-base text-white opacity-90" x-text="d.user_name"></p>
                                        </div>
                                    </template>
                                    <template x-if="d.tipo === 'spesa_abbonamento'">
                                        <div>
                                            <h3 class="text-2xl font-black text-white tracking-tight mb-1" x-text="d.subscription_nome || 'Spesa'"></h3>
                                            <p class="text-base text-white opacity-90" x-text="(d.user_name !== 'N/D' ? d.user_name : (d.vehicle_targa || 'Famiglia'))"></p>
                                        </div>
                                    </template>
                                </div>
                                <div class="ml-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white bg-opacity-20 text-white uppercase text-center text-[10px]">
                                        <span x-text="d.tipo === 'spesa_abbonamento' ? 'Spesa / Abbonamento' : d.tipo.replace('_', ' ')"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6 bg-gradient-to-b from-gray-50 to-white">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500">Scadenza</p>
                                            <p class="text-base font-semibold text-gray-900" x-text="d.data_scadenza_formatted"></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-medium text-gray-500">Tra</p>
                                        <p class="text-2xl font-black" 
                                           :class="(d.tipo === 'patente' && d.scade_quest_anno) || d.giorni_rimanenti <= 30 ? 'text-red-600' : 'text-green-600'">
                                            <span x-text="d.giorni_rimanenti"></span> <span class="text-sm">gg</span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center bg-gray-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-xs font-medium text-gray-500">Importo</p>
                                        <template x-if="['carta_identita', 'tesserino_sanitario'].includes(d.tipo)">
                                            <p class="text-xl font-bold text-gray-900">N/D</p>
                                        </template>
                                        <template x-if="!['carta_identita', 'tesserino_sanitario'].includes(d.tipo)">
                                            <p class="text-xl font-bold text-gray-900">€<span x-text="d.importo_formatted"></span></p>
                                        </template>
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 mt-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Stato</p>
                                            <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold shadow-md"
                                                :class="d.pagato ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : 'bg-gradient-to-r from-red-500 to-red-600 text-white'">
                                                <span x-text="['carta_identita', 'tesserino_sanitario'].includes(d.tipo) ? (d.pagato ? '✓ RINNOVATA' : '✗ NON RINNOVATA') : (d.pagato ? '✓ PAGATO' : '✗ NON PAGATO')"></span>
                                            </span>
                                        </div>
                                        <div>
                                            <label class="switch switch-lg">
                                                <input type="checkbox" :checked="d.pagato" @change="togglePagato(d)">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <template x-if="d.note">
                                    <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                                        <p class="font-medium text-blue-900 mb-1">📝 Note</p>
                                        <p x-text="d.note"></p>
                                    </div>
                                </template>
                            </div>
                            
                            <div class="mt-6 pt-5 border-t border-gray-200 flex justify-center pb-2">
                                <button @click="openDeleteModal(d)"
                                        class="btn-card-action btn-card-delete btn-card-compact w-full max-w-xs">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Elimina Scadenza</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div x-show="filteredDeadlines.length === 0" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">Nessuna scadenza trovata</p>
                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                </div>
            </div>

            <div x-show="showDeleteModal" x-cloak @keydown.escape.window="closeDeleteModal()" class="modal-overlay" :class="{ 'modal-active': showDeleteModal && !isClosingDelete, 'modal-out': isClosingDelete }">
                <div @click="closeDeleteModal()" class="modal-backdrop"></div>
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="p-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Elimina Scadenza</h3>
                            <p class="text-sm text-gray-600 text-center mb-4">
                                Sei sicuro di voler eliminare questa scadenza di tipo 
                                <strong class="text-gray-900 capitalize" x-text="deleteDeadline.tipo ? (deleteDeadline.tipo === 'spesa_abbonamento' ? 'Spesa/Abbonamento' : deleteDeadline.tipo.replace('_', ' ')) : ''"></strong>?
                                <br>
                                <span class="text-xs text-red-500 block mt-2">Attenzione: questo eliminerà anche il record originale collegato.</span>
                            </p>
                        </div>
                        
                        <form @submit.prevent="submitDelete(`/deadlines/${deleteDeadline.id}`)" class="px-6 pb-6">
                            @csrf
                            <div class="flex justify-center space-x-3 mb-4">
                                <button type="button" @click="closeDeleteModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-danger" :disabled="isSubmitting">Elimina</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-show="showSuccessModal" x-cloak @keydown.escape.window="closeSuccessModal()" class="modal-overlay" :class="{ 'modal-active': showSuccessModal && !isClosingSuccess, 'modal-out': isClosingSuccess }">
                <div @click="closeSuccessModal()" class="modal-backdrop"></div>
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="p-8 text-center">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full mb-6"
                                 :class="{ 'bg-green-100': successType === 'success', 'bg-blue-100': successType === 'info', 'bg-red-100': successType === 'error' }">
                                <svg x-show="successType === 'success'" class="h-10 w-10 text-green-600 animate-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="successType === 'info'" class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <svg x-show="successType === 'error'" class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3"
                                :class="{ 'text-green-900': successType === 'success', 'text-blue-900': successType === 'info', 'text-red-900': successType === 'error' }">
                                <span x-show="successType === 'success'">Operazione completata!</span>
                                <span x-show="successType === 'info'">Informazione</span>
                                <span x-show="successType === 'error'">Errore</span>
                            </h3>
                            <p class="text-gray-600 text-lg mb-6" x-text="successMessage"></p>
                            <button @click="closeSuccessModal()" class="px-6 py-2.5 rounded-lg font-semibold text-white shadow-lg transition transform hover:scale-105"
                                    :class="successType === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function deadlinesManager() {
            return {
                showDeleteModal: false, showSuccessModal: false,
                isClosingDelete: false, isClosingSuccess: false,
                successMessage: '', successType: 'success', isSubmitting: false,
                
                deleteDeadline: {},
                searchQuery: '',
                
                deadlines: @json($deadlines),

                init() {
                    @if(session('success')) this.showSuccess("{{ session('success') }}", 'success'); @endif
                    @if(session('error')) this.showSuccess("{{ session('error') }}", 'error'); @endif
                },

                get filteredDeadlines() {
                    if (!this.searchQuery) return this.deadlines;
                    const query = this.searchQuery.toLowerCase();
                    return this.deadlines.filter(d => {
                        const tipo = d.tipo ? d.tipo.toLowerCase() : '';
                        const targa = d.vehicle_targa ? d.vehicle_targa.toLowerCase() : '';
                        const marca = d.vehicle_marca ? d.vehicle_marca.toLowerCase() : '';
                        const modello = d.vehicle_modello ? d.vehicle_modello.toLowerCase() : '';
                        const persona = d.user_name ? d.user_name.toLowerCase() : '';
                        const subNome = d.subscription_nome ? d.subscription_nome.toLowerCase() : '';
                        const subCat = d.subscription_categoria ? d.subscription_categoria.toLowerCase() : '';
                        
                        return tipo.includes(query) || 
                               targa.includes(query) || 
                               marca.includes(query) || 
                               modello.includes(query) || 
                               persona.includes(query) ||
                               subNome.includes(query) ||
                               subCat.includes(query);
                    });
                },

                openDeleteModal(d) { this.deleteDeadline = d; this.showDeleteModal = true; },
                closeDeleteModal() { this.isClosingDelete = true; setTimeout(() => { this.showDeleteModal = false; this.isClosingDelete = false; }, 500); },
                
                closeSuccessModal() { this.isClosingSuccess = true; setTimeout(() => { this.showSuccessModal = false; this.isClosingSuccess = false; }, 500); },
                showSuccess(message, type = 'success') {
                    this.successMessage = message; this.successType = type; this.showSuccessModal = true;
                    setTimeout(() => { if(this.showSuccessModal) this.closeSuccessModal(); }, 3000);
                },

                async refreshData() {
                    try {
                        const res = await fetch('{{ route("deadlines.index") }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.deadlines = data.deadlines;
                        }
                    } catch (error) {
                        console.error('Errore aggiornamento dati:', error);
                    }
                },

                async submitDelete(url) {
                    this.isSubmitting = true;
                    try {
                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: { 
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest', 
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.closeDeleteModal();
                            await this.refreshData();
                            this.showSuccess(data.message || 'Scadenza eliminata con successo!', 'success');
                        } else {
                            this.closeDeleteModal();
                            this.showSuccess(data.message || "Impossibile eliminare", 'error');
                        }
                    } catch (error) {
                        this.closeDeleteModal();
                        this.showSuccess('Errore di connessione', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async togglePagato(deadline) {
                    deadline.pagato = !deadline.pagato;
                    try {
                        const response = await fetch(`/deadlines/${deadline.id}/toggle-pagato`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            this.refreshData();
                        } else {
                            deadline.pagato = !deadline.pagato;
                            this.showSuccess('Errore durante l\'aggiornamento', 'error');
                        }
                    } catch (error) {
                        deadline.pagato = !deadline.pagato;
                        console.error('Errore Toggle:', error);
                        this.showSuccess('Errore di connessione', 'error');
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .desktop-only { display: block; }
        .mobile-only { display: none; }
        @media (max-width: 1023px) { .desktop-only { display: none; } .mobile-only { display: block; } }
        .mobile-only-search { display: block; }
        .desktop-only-search { display: none; }
        @media (min-width: 1024px) { .mobile-only-search { display: none !important; } .desktop-only-search { display: block !important; } }

        .btn-table-action { display: inline-flex !important; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s ease; white-space: nowrap; }
        .btn-table-delete { background-color: #ef4444 !important; color: white !important; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2); }
        .btn-table-delete:hover { background-color: #dc2626 !important; box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3); transform: translateY(-1px); }
        .btn-table-delete svg { transition: transform 0.2s ease; stroke: white !important; }
        .btn-table-delete:hover svg { transform: scale(1.1); }
        .btn-table-action:active { transform: translateY(0); }

        .btn-card-compact { padding: 0.625rem 1.25rem !important; font-size: 0.875rem; min-width: auto; flex: 0 0 auto; }
        .btn-card-action { font-weight: 600; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; }
        .btn-card-action:active { transform: translateY(0); }
        .btn-card-delete { background-color: #ef4444 !important; color: white !important; }
        .btn-card-delete:hover { background-color: #dc2626 !important; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4); transform: translateY(-2px); }
        .btn-card-delete svg { transition: transform 0.2s ease; stroke: white !important; }
        .btn-card-delete:hover svg { transform: scale(1.1); }

        input[type="checkbox"].sr-only:checked + div { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; }
        input[type="checkbox"].sr-only + div { background: #d1d5db; transition: all 0.3s ease; }
        input[type="checkbox"].sr-only + div::after { box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        input:checked + .slider { background-color: #10b981; }
        input:focus + .slider { box-shadow: 0 0 1px #10b981; }
        input:checked + .slider:before { transform: translateX(26px); }
        .switch-lg { width: 60px; height: 30px; }
        .switch-lg .slider:before { height: 24px; width: 24px; }
        .switch-lg input:checked + .slider:before { transform: translateX(30px); }
        .slider:hover { opacity: 0.9; }

        @keyframes blowUpModal { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0); }
        .modal-active .modal-backdrop { animation: fadeIn 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-backdrop { animation: fadeOut 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }

        .modal-container { position: relative; width: 90%; max-width: 500px; margin: 0 auto; pointer-events: auto; }
        .modal-content-wrapper { background: white; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; }
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }

        @keyframes fadeIn { 0% { background: rgba(0, 0, 0, 0); } 100% { background: rgba(0, 0, 0, 0.7); } }
        @keyframes fadeOut { 0% { background: rgba(0, 0, 0, 0.7); } 100% { background: rgba(0, 0, 0, 0); } }

        @keyframes checkmark { 0% { stroke-dashoffset: 50; stroke-dasharray: 50; } 100% { stroke-dashoffset: 0; stroke-dasharray: 50; } }
        .animate-check { stroke-dashoffset: 50; stroke-dasharray: 50; animation: checkmark 0.6s ease-in-out forwards; }
        @keyframes pulse-success { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
        .modal-active .modal-content-wrapper svg { animation: pulse-success 0.6s ease-in-out; }

        .btn-secondary { padding: 0.625rem 1.25rem; background: #f3f4f6; color: #374151; font-weight: 500; font-size: 0.875rem; border: none; border-radius: 0.5rem; cursor: pointer; transition: all 0.15s; }
        .btn-secondary:hover { background: #e5e7eb; }
        .btn-danger { padding: 0.625rem 1.5rem; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; font-weight: 600; font-size: 0.875rem; border: none; border-radius: 0.5rem; cursor: pointer; transition: all 0.15s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .btn-danger:hover { background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); transform: scale(1.05); }
        .btn-danger:disabled { opacity: 0.7; cursor: not-allowed; }
    </style>
</x-app-layout>