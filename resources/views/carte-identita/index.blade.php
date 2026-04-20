<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Carte d'Identità
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4" 
             x-data="carteIdentitaManager()"
             x-init="init()">

            <details class="bg-amber-50 p-5 rounded-xl mb-8 border-2 border-amber-200 shadow-sm mt-4">
                <summary class="font-black text-[#6f3404] cursor-pointer text-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-2xl"></i>
                    Guida passo-passo: Come gestisco le Carte d'Identità? (Clicca per leggere)
                </summary>
                <div class="mt-5 text-slate-700 leading-relaxed space-y-6 text-base">
                    
                    <p class="font-medium text-lg bg-white p-3 rounded-lg border border-amber-100">
                        🪪 <strong>Il documento fondamentale!</strong> In questa pagina teniamo traccia delle Carte d'Identità di tutta la famiglia. Dimenticarsi di rinnovarla prima di un viaggio all'estero è un classico: con questo sistema non avrai più brutte sorprese all'aeroporto!
                    </p>

                    <div>
                        <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">➕ 1. Come inserire una nuova Carta d'Identità</h4>
                        <p>Sei appena stato in Comune a fare la nuova tessera elettronica?</p>
                        <ol class="list-decimal ml-5 mt-2 space-y-1">
                            <li>Clicca sul pulsante marrone scuro <strong>"Aggiungi Documento"</strong> qui in alto.</li>
                            <li>Scegli dal menu a tendina il <strong>Membro della Famiglia</strong> (devi aver prima creato la persona nella pagina "Utenti").</li>
                            <li>Digita il <strong>Numero Documento</strong> (lo trovi in alto a destra sulla carta, es: CA12345AA).</li>
                            <li>Inserisci la <strong>Data di Rilascio</strong> e, cosa più importante, la <strong>Data di Scadenza</strong>. (Ricorda: di solito scade sempre il giorno del compleanno!).</li>
                            <li>Scrivi il Comune che te l'ha rilasciata e clicca su Salva.</li>
                        </ol>
                    </div>

                    <div>
                        <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">🔄 2. Come si rinnova la Carta d'Identità?</h4>
                        <p>La carta d'identità scade ogni 10 anni (per gli adulti). Quando vai a rifarla in Comune, ti daranno un documento completamente nuovo, con un <strong>numero diverso</strong> dal precedente.</p>
                        <p class="mt-1 bg-white p-2 rounded text-slate-800 text-sm border border-slate-200">
                            A differenza del bollo, qui puoi scegliere due strade: 
                            <br>1) Cercare la vecchia carta, cliccare su <strong>Modifica</strong> e cambiare il numero e le date.
                            <br>2) Se vuoi conservare lo storico per sicurezza, clicca su <strong>Aggiungi Documento</strong> per inserire la carta nuova, e poi elimina quella vecchia (o lasciala se preferisci). Noi ti consigliamo di usare il tasto Modifica per tenere la schermata più pulita.
                        </p>
                    </div>

                    <div>
                        <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">⚠️ 3. Attenzione ai minorenni!</h4>
                        <p>Ricorda che le regole di scadenza sono diverse per i bambini:</p>
                        <ul class="space-y-1 ml-2 mt-2">
                            <li class="flex items-center gap-2">👶 <strong>Da 0 a 3 anni:</strong> Scade ogni 3 anni.</li>
                            <li class="flex items-center gap-2">👦 <strong>Da 3 a 18 anni:</strong> Scade ogni 5 anni.</li>
                        </ul>
                        <p class="text-sm italic mt-1">L'app ti ricorderà in automatico la scadenza in base alla data che hai inserito, senza farti fare conti strani!</p>
                    </div>

                </div>
            </details>

             <div class="mb-6">
                <div class="mobile-only-search">
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Cerca nome o numero..." 
                               class="w-full pl-4 pr-10 py-3.5 bg-white border-2 border-gray-200 rounded-xl focus:border-amber-900 focus:ring-4 focus:ring-amber-200 transition text-base shadow-sm">
                        <div x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1" x-show="searchQuery"><span x-text="filteredDocs.length"></span> risultati trovati</p>
                </div>
                
                <div class="desktop-only-search">
                    <div class="bg-white rounded-xl shadow-md p-4">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Cerca nome o numero documento..." 
                                   class="w-full pl-4 pr-10 py-3 bg-gray-50 border-2 border-gray-200 rounded-lg focus:border-amber-900 focus:bg-white focus:ring-4 focus:ring-amber-200 transition">
                            <div x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3" x-show="searchQuery">
                            <p class="text-sm text-gray-600"><span class="font-semibold" x-text="filteredDocs.length"></span> risultati trovati</p>
                            <button @click="searchQuery = ''" class="text-sm text-amber-900 hover:text-amber-900 font-medium">Cancella ricerca</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6 flex justify-center lg:justify-start">
                <button @click="openCreateModal()" class="btn-add-custom">
                    <svg class="inline-block h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Aggiungi Documento</span>
                </button>
            </div>

            <div class="mobile-only bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-20">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="selectAll" class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500 shadow-sm">
                        <span class="text-sm font-bold text-gray-700">Seleziona Tutti</span>
                    </label>
                    <span class="text-xs font-bold text-gray-400" x-text="filteredDocs.length + ' risultati'"></span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500 font-medium">Ordina:</span>
                    <select x-model="sortCol" @change="sortAsc = true" class="p-2 flex-1 text-sm font-bold text-slate-700 bg-gray-50 border-gray-200 rounded-lg">
                        <option value="data_scadenza">Scadenza</option>
                        <option value="numero">Numero Documento</option>
                        <option value="user">Persona</option>
                    </select>
                    <button @click="sortAsc = !sortAsc" class="p-2 bg-amber-50 text-amber-900 rounded-lg">
                        <i class="fa-solid" :class="sortAsc ? 'fa-arrow-down-short-wide' : 'fa-arrow-up-wide-short'"></i>
                    </button>
                </div>
            </div>

            <div class="desktop-only bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">
                                    <input type="checkbox" x-model="selectAll" class="w-4 h-4 rounded border-gray-300 text-amber-900 focus:ring-amber-500 cursor-pointer">
                                </th>
                                <th @click="sortBy('user')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition group select-none">
                                    <div class="flex items-center gap-2">Persona
                                        <span x-show="sortCol === 'user'" x-cloak class="text-amber-900"><i class="fa-solid fa-sort-up" x-show="sortAsc"></i><i class="fa-solid fa-sort-down" x-show="!sortAsc"></i></span>
                                        <span x-show="sortCol !== 'user'" x-cloak class="text-gray-300 opacity-0 group-hover:opacity-100"><i class="fa-solid fa-sort"></i></span>
                                    </div>
                                </th>
                                <th @click="sortBy('numero')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition group select-none">
                                    <div class="flex items-center gap-2">Numero / Comune
                                        <span x-show="sortCol === 'numero'" x-cloak class="text-amber-900"><i class="fa-solid fa-sort-up" x-show="sortAsc"></i><i class="fa-solid fa-sort-down" x-show="!sortAsc"></i></span>
                                        <span x-show="sortCol !== 'numero'" x-cloak class="text-gray-300 opacity-0 group-hover:opacity-100"><i class="fa-solid fa-sort"></i></span>
                                    </div>
                                </th>
                                <th @click="sortBy('data_scadenza')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition group select-none">
                                    <div class="flex items-center gap-2">Scadenza
                                        <span x-show="sortCol === 'data_scadenza'" x-cloak class="text-amber-900"><i class="fa-solid fa-sort-up" x-show="sortAsc"></i><i class="fa-solid fa-sort-down" x-show="!sortAsc"></i></span>
                                        <span x-show="sortCol !== 'data_scadenza'" x-cloak class="text-gray-300 opacity-0 group-hover:opacity-100"><i class="fa-solid fa-sort"></i></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giorni</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(doc, index) in filteredDocs" :key="doc.id || index">
                                <tr class="hover:bg-gray-50 transition cursor-pointer" :class="{ 'bg-red-50': calcGiorniRimanenti(doc.data_scadenza) <= 90, 'bg-amber-50': selectedItems.includes(doc.id) }" @click.self="toggleSelection(doc.id)">
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox" :value="doc.id" x-model="selectedItems" class="w-4 h-4 rounded border-gray-300 text-amber-900 focus:ring-amber-500 cursor-pointer">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900" x-text="doc.user ? doc.user.name : ''"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900 uppercase" x-text="doc.numero"></div>
                                        <div class="text-xs text-gray-500" x-text="'Rilascio: ' + (doc.comune_rilascio || 'N/D')"></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-semibold" x-text="formatData(doc.data_scadenza)"></td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold" :class="calcGiorniRimanenti(doc.data_scadenza) <= 90 ? 'text-red-600' : 'text-teal-600'">
                                            <span x-text="calcGiorniRimanenti(doc.data_scadenza)"></span> gg
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" x-text="doc.note || '—'"></td>
                                    <td class="px-6 py-5 space-x-3">
                                        <button @click="openEditModal(doc)" 
                                                class="btn-table-action btn-table-edit">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Modifica
                                        </button>
                                        <button @click="openDeleteModal(doc)" class="btn-table-action btn-table-delete">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Elimina
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="filteredDocs.length === 0" x-cloak>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fa-solid fa-id-card text-4xl text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">Nessun documento trovato</p>
                                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mobile-only space-y-4 px-4 pb-20">
                <template x-for="(doc, index) in filteredDocs" :key="doc.id || index">
                    <div class="bg-white rounded-2xl shadow-xl border overflow-hidden relative" :class="[calcGiorniRimanenti(doc.data_scadenza) <= 90 ? 'border-red-300' : 'border-gray-200', selectedItems.includes(doc.id) ? 'ring-4 ring-amber-400 transform scale-[1.02] shadow-2xl' : '']">
                        
                        <div class="absolute top-4 right-4 z-10 bg-white/40 backdrop-blur-sm p-1 rounded-xl shadow-sm border border-white">
                            <input type="checkbox" :value="doc.id" x-model="selectedItems" class="w-6 h-6 rounded border-gray-300 text-amber-900 focus:ring-amber-500 cursor-pointer shadow-sm">
                        </div>

                        <div class="relative bg-gradient-to-br from-[#a16207] via-[#a16207] to-[#6f3404] px-5 py-6">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full -ml-12 -mb-12"></div>
                            
                            <div class="relative flex items-center pr-12">
                                <div class="flex-1">
                                    <h3 class="text-2xl font-black text-white tracking-tight mb-1.5" x-text="doc.user ? doc.user.name : ''"></h3>
                                    <p class="text-base text-white opacity-90 uppercase tracking-widest font-mono" x-text="doc.numero"></p>
                                </div>
                                <div class="ml-4">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl px-3.5 py-2 border border-white border-opacity-30">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-lg font-bold text-white" x-text="calcGiorniRimanenti(doc.data_scadenza) + ' gg'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6 bg-gradient-to-b from-gray-50 to-white">
                            <div class="space-y-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-teal-100 to-teal-200 rounded-xl flex items-center justify-center">
                                        <i class="fa-solid fa-calendar text-teal-600 text-xl"></i>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Scadenza Documento</p>
                                        <p class="text-lg font-bold" :class="calcGiorniRimanenti(doc.data_scadenza) <= 90 ? 'text-red-600' : 'text-gray-900'" x-text="formatData(doc.data_scadenza)"></p>
                                    </div>
                                </div>

                                <div class="flex items-center bg-gray-100 rounded-lg p-3">
                                    <i class="fa-solid fa-building-columns text-gray-500 mr-3 text-xl"></i>
                                    <div class="flex-1">
                                        <p class="text-xs font-medium text-gray-500">Comune Rilascio</p>
                                        <p class="text-sm font-bold text-gray-900" x-text="doc.comune_rilascio || 'N/D'"></p>
                                    </div>
                                </div>
                                
                                <template x-if="doc.note">
                                    <div class="text-sm text-gray-600 bg-teal-50 p-3 rounded-lg border-l-4 border-teal-400">
                                        <p class="font-medium text-teal-900 mb-1">📝 Note</p>
                                        <p x-text="doc.note"></p>
                                    </div>
                                </template>
                            </div>
                            
                            <div class="mt-6 pt-5 border-t border-gray-200 flex justify-center gap-3 pb-2">
                                <button @click="openEditModal(doc)"
                                        class="btn-card-action btn-card-edit btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Modifica</span>
                                </button>
                                
                                <button @click="openDeleteModal(doc)" class="btn-card-action btn-card-delete btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Elimina</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div x-show="filteredDocs.length === 0" x-cloak class="bg-white rounded-2xl shadow-lg border border-gray-200 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-id-card text-4xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Nessun documento trovato</p>
                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                </div>
            </div>

            <div class="fixed bottom-4 sm:bottom-6 left-0 right-0 z-50 flex justify-center pointer-events-none px-4">
                <div x-show="selectedItems.length > 0" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-300 transform origin-bottom"
                     x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform origin-bottom"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-10 scale-95"
                     class="pointer-events-auto bg-slate-900/95 backdrop-blur-md text-white px-4 sm:px-6 py-3.5 sm:py-4 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] flex items-center gap-3 sm:gap-6 border border-slate-700/50 w-full sm:w-auto max-w-2xl justify-between">
                    
                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        <span class="bg-amber-700 text-white font-black px-3 py-1 rounded-lg text-base sm:text-lg shadow-inner" x-text="selectedItems.length"></span>
                        <span class="font-semibold text-sm sm:text-base hidden xs:block">Selezionati</span>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 flex-1 justify-end">
                        <button @click="showBulkDeleteModal = true" class="bg-red-500 hover:bg-red-400 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl font-bold transition-all active:scale-95 flex items-center justify-center gap-2 shadow-lg flex-1 sm:flex-none" :disabled="isSubmitting">
                            <i class="fa-solid fa-trash-can"></i> <span class="text-sm hidden sm:inline">Elimina Multipla</span><span class="text-sm sm:hidden">Elimina</span>
                        </button>
                        <div class="w-px h-8 bg-slate-700 mx-1 sm:mx-2"></div>
                        <button @click="selectedItems = []" class="text-slate-400 hover:text-white p-2 flex-shrink-0 bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors" title="Annulla selezione">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="showCreateModal" x-cloak @keydown.escape.window="closeCreateModal()" class="modal-overlay" :class="{ 'modal-active': showCreateModal && !isClosingCreate, 'modal-out': isClosingCreate }">
                <div @click="closeCreateModal()" class="modal-backdrop"></div>
                <div class="modal-container modal-container-large">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Nuova Carta d'Identità</h3>
                            <button @click="closeCreateModal()" class="modal-close">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitForm($event, '{{ route('carte-identita.store') }}', 'POST')" class="modal-body">
                            @csrf
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Membro Famiglia *</label>
                                    <select name="user_id" required class="form-input">
                                        <option value="">Seleziona membro...</option>
                                        @foreach($users as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
                                    </select>
                                </div>
                                <div><label class="form-label">Numero Documento *</label><input type="text" name="numero" required class="form-input uppercase" placeholder="CA12345AA"></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="form-label">Data Rilascio</label><input type="date" name="data_rilascio" class="form-input"></div>
                                    <div><label class="form-label">Data Scadenza *</label><input type="date" name="data_scadenza" required class="form-input"></div>
                                </div>
                                <div><label class="form-label">Comune Rilascio</label><input type="text" name="comune_rilascio" class="form-input" placeholder="Es. Roma"></div>
                                <div><label class="form-label">Note</label><textarea name="note" rows="3" class="form-input"></textarea></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" @click="closeCreateModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-primary" :disabled="isSubmitting" x-text="isSubmitting ? 'Salvataggio...' : 'Salva'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-show="showEditModal" x-cloak @keydown.escape.window="closeEditModal()" class="modal-overlay" :class="{ 'modal-active': showEditModal && !isClosingEdit, 'modal-out': isClosingEdit }">
                <div @click="closeEditModal()" class="modal-backdrop"></div>
                <div class="modal-container modal-container-large">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Modifica Documento</h3>
                            <button @click="closeEditModal()" class="modal-close"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        
                        <form @submit.prevent="submitForm($event, `/carte-identita/${editDoc.id}`, 'PUT')" class="modal-body">
                            @csrf
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Membro Famiglia *</label>
                                    <select name="user_id" x-model="editDoc.user_id" required class="form-input">
                                        <option value="">Seleziona membro...</option>
                                        @foreach($users as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
                                    </select>
                                </div>
                                <div><label class="form-label">Numero Documento *</label><input type="text" name="numero" x-model="editDoc.numero" required class="form-input uppercase"></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="form-label">Data Rilascio</label><input type="date" name="data_rilascio" x-model="editDoc.data_rilascio" class="form-input"></div>
                                    <div><label class="form-label">Data Scadenza *</label><input type="date" name="data_scadenza" x-model="editDoc.data_scadenza" required class="form-input"></div>
                                </div>
                                <div><label class="form-label">Comune Rilascio</label><input type="text" name="comune_rilascio" x-model="editDoc.comune_rilascio" class="form-input"></div>
                                <div><label class="form-label">Note</label><textarea name="note" x-model="editDoc.note" rows="3" class="form-input"></textarea></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" @click="closeEditModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-primary" :disabled="isSubmitting" x-text="isSubmitting ? 'Salvataggio...' : 'Salva'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-show="showDeleteModal" x-cloak @keydown.escape.window="closeDeleteModal()" class="modal-overlay" :class="{ 'modal-active': showDeleteModal && !isClosingDelete, 'modal-out': isClosingDelete }">
                <div @click="closeDeleteModal()" class="modal-backdrop"></div>
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="p-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Elimina Documento</h3>
                            <p class="text-sm text-gray-600 text-center mb-1">Sei sicuro di voler eliminare il documento di</p>
                            <p class="text-center mb-4"><strong class="text-gray-900" x-text="deleteDoc.user ? deleteDoc.user.name : ''"></strong>?</p>
                            <p class="text-xs text-red-600 text-center bg-red-50 py-2 px-4 rounded-lg">⚠️ Verrà rimosso anche dal calendario scadenze</p>
                        </div>
                        <form @submit.prevent="submitDelete(`/carte-identita/${deleteDoc.id}`)" class="px-6 pb-6">
                            @csrf
                            <div class="flex justify-center space-x-3 mb-4">
                                <button type="button" @click="closeDeleteModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-danger" :disabled="isSubmitting">Elimina</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-show="showBulkDeleteModal" x-cloak @keydown.escape="closeBulkDeleteModal()" class="modal-overlay" :class="{ 'modal-active': showBulkDeleteModal && !isClosingBulkDelete, 'modal-out': isClosingBulkDelete }">
                <div class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
                <div class="modal-container w-[90%] sm:w-full max-w-md pointer-events-auto mx-auto" @click.stop>
                    <div class="modal-content-wrapper bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-6 text-center pt-8">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4"><i class="fa-solid fa-trash-can text-3xl text-red-500"></i></div>
                            <h3 class="text-2xl font-black text-slate-800 mb-2">Elimina Multipla</h3>
                            <p class="text-sm text-slate-500 mb-6">Stai per eliminare definitivamente <strong class="text-slate-800" x-text="selectedItems.length"></strong> carte d'identità in un colpo solo. Confermi?</p>
                        </div>
                        <div class="px-6 pb-6 flex flex-col sm:flex-row gap-3 border-t border-slate-50 pt-4">
                            <button @click="closeBulkDeleteModal()" class="flex-1 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200">Annulla</button>
                            <button @click="executeBulkDelete()" class="flex-1 py-3 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 shadow-md">Sì, Elimina Tutte</button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="showSuccessModal" x-cloak @keydown.escape.window="closeSuccessModal()" class="modal-overlay" :class="{ 'modal-active': showSuccessModal && !isClosingSuccess, 'modal-out': isClosingSuccess }">
                <div @click="closeSuccessModal()" class="modal-backdrop"></div>
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="p-8 text-center">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full mb-6"
                                 :class="{ 'bg-green-100': successType === 'success', 'bg-red-100': successType === 'error' }">
                                <svg x-show="successType === 'success'" class="h-10 w-10 text-green-600 animate-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="successType === 'error'" class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3"
                                :class="{ 'text-green-900': successType === 'success', 'text-red-900': successType === 'error' }">
                                <span x-show="successType === 'success'">Operazione completata!</span>
                                <span x-show="successType === 'error'">Attenzione!</span>
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
        function carteIdentitaManager() {
            return {
                showCreateModal: false, showEditModal: false, showDeleteModal: false, showSuccessModal: false,
                isClosingCreate: false, isClosingEdit: false, isClosingDelete: false, isClosingSuccess: false,
                successMessage: '', successType: 'success', isSubmitting: false,
                
                editDoc: { user: {} },
                deleteDoc: { user: {} },
                searchQuery: '',
                
                selectedItems: [],
                showBulkDeleteModal: false,
                isClosingBulkDelete: false,
                
                sortCol: 'data_scadenza',
                sortAsc: true,

                documenti: @json($documenti),

                init() {
                    @if(session('success')) this.showSuccess("{{ session('success') }}", 'success'); @endif
                    @if(session('error')) this.showSuccess("{{ session('error') }}", 'error'); @endif
                },

                get selectAll() {
                    return this.filteredDocs.length > 0 && this.selectedItems.length === this.filteredDocs.length;
                },
                set selectAll(value) {
                    if (value) {
                        this.selectedItems = [...this.filteredDocs.map(doc => doc.id)];
                    } else {
                        this.selectedItems = [];
                    }
                },

                toggleSelection(id) {
                    if (this.selectedItems.includes(id)) {
                        this.selectedItems = this.selectedItems.filter(i => i !== id);
                    } else {
                        this.selectedItems.push(id);
                    }
                },

                sortBy(col) {
                    if (this.sortCol === col) {
                        this.sortAsc = !this.sortAsc;
                    } else {
                        this.sortCol = col;
                        this.sortAsc = true;
                    }
                },

                calcGiorniRimanenti(dataScadenza) {
                    if(!dataScadenza) return 0;
                    const today = new Date();
                    today.setHours(0,0,0,0);
                    const scad = new Date(dataScadenza);
                    scad.setHours(0,0,0,0);
                    return Math.ceil((scad - today) / (1000 * 60 * 60 * 24));
                },
                
                formatData(data) {
                    if (!data) return '-';
                    const opts = {day: '2-digit', month: '2-digit', year: 'numeric'};
                    return new Date(data).toLocaleDateString('it-IT', opts);
                },

                get filteredDocs() {
                    let filtered = Array.isArray(this.documenti) ? this.documenti : [];
                    
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(doc => {
                            const name = (doc.user && doc.user.name) ? doc.user.name.toLowerCase() : '';
                            const numero = doc.numero ? doc.numero.toLowerCase() : '';
                            return name.includes(query) || numero.includes(query);
                        });
                    }

                    return filtered.sort((a, b) => {
                        let aVal, bVal;
                        switch (this.sortCol) {
                            case 'data_scadenza': 
                                aVal = a.data_scadenza ? new Date(a.data_scadenza) : new Date(0); 
                                bVal = b.data_scadenza ? new Date(b.data_scadenza) : new Date(0); 
                                break;
                            case 'user': 
                                aVal = a.user?.name || ''; 
                                bVal = b.user?.name || ''; 
                                break;
                            case 'numero': 
                                aVal = a.numero || ''; 
                                bVal = b.numero || ''; 
                                break;
                            default: return 0;
                        }
                        if (aVal < bVal) return this.sortAsc ? -1 : 1;
                        if (aVal > bVal) return this.sortAsc ? 1 : -1;
                        return 0;
                    });
                },

                openCreateModal() { this.showCreateModal = true; },
                closeCreateModal() { this.isClosingCreate = true; setTimeout(() => { this.showCreateModal = false; this.isClosingCreate = false; }, 500); },
                
                openEditModal(doc) { 
                    this.editDoc = JSON.parse(JSON.stringify(doc)); 
                    if(this.editDoc.data_rilascio && this.editDoc.data_rilascio.includes('T')) {
                        this.editDoc.data_rilascio = this.editDoc.data_rilascio.split('T')[0];
                    }
                    if(this.editDoc.data_scadenza && this.editDoc.data_scadenza.includes('T')) {
                        this.editDoc.data_scadenza = this.editDoc.data_scadenza.split('T')[0];
                    }
                    this.showEditModal = true; 
                },
                closeEditModal() { this.isClosingEdit = true; setTimeout(() => { this.showEditModal = false; this.isClosingEdit = false; }, 500); },
                
                openDeleteModal(doc) { this.deleteDoc = doc; this.showDeleteModal = true; },
                closeDeleteModal() { this.isClosingDelete = true; setTimeout(() => { this.showDeleteModal = false; this.isClosingDelete = false; }, 500); },
                
                closeSuccessModal() { this.isClosingSuccess = true; setTimeout(() => { this.showSuccessModal = false; this.isClosingSuccess = false; }, 500); },
                showSuccess(message, type = 'success') {
                    this.successMessage = message; this.successType = type; this.showSuccessModal = true;
                    setTimeout(() => { if(this.showSuccessModal) this.closeSuccessModal(); }, 3000);
                },

                closeBulkDeleteModal() { this.isClosingBulkDelete = true; setTimeout(() => { this.showBulkDeleteModal = false; this.isClosingBulkDelete = false; }, 500); },
                
                async executeBulkDelete() {
                    if (this.selectedItems.length === 0) return;
                    
                    this.isSubmitting = true;
                    this.showBulkDeleteModal = false;
                    try {
                        const promises = this.selectedItems.map(id => 
                            fetch(`/carte-identita/${id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            })
                        );
                        await Promise.all(promises);

                        const count = this.selectedItems.length;
                        this.selectedItems = [];
                        await this.refreshData();
                        this.showSuccess(`Eliminati ${count} documenti con successo!`, 'success');
                    } catch (error) {
                        this.showSuccess('Errore durante l\'eliminazione dei documenti', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async refreshData() {
                    try {
                        const res = await fetch('{{ route("carte-identita.index") }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.documenti = data.documenti;
                        }
                    } catch (error) {
                        console.error('Errore aggiornamento dati:', error);
                    }
                },

                async submitForm(event, url, method) {
                    this.isSubmitting = true;
                    const form = event.target;
                    const formData = new FormData(form);
                    
                    if (method === 'PUT') formData.set('_method', 'PUT');

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });

                        const data = await response.json();

                        if (response.ok || response.status === 200 || response.status === 201) {
                            this.closeCreateModal();
                            this.closeEditModal();
                            if(method === 'POST') form.reset();
                            
                            await this.refreshData();
                            this.showSuccess(data.message || 'Operazione completata con successo!', 'success');
                        } else {
                            this.showSuccess(data.message || 'Errore di validazione dati', 'error');
                        }
                    } catch (error) {
                        this.showSuccess('Errore di connessione al server', 'error');
                    } finally {
                        this.isSubmitting = false;
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
                            this.showSuccess(data.message || 'Documento eliminato con successo!', 'success');
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

        /* Bottone Aggiungi */
        .btn-add-custom {
            background: linear-gradient(135deg, #a16207 0%, #6f3404 100%);
            color: white; font-weight: 600; font-size: 1rem; padding: 0.875rem 1.75rem; border-radius: 0.75rem; border: none; cursor: pointer;
            box-shadow: 0 4px 14px -2px rgba(37, 99, 235, 0.4); transition: all 0.25s ease; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap;
        }
        .btn-add-custom:hover { background: linear-gradient(135deg, #6f3404 0%, rgb(46, 27, 2) 100%); box-shadow: 0 8px 20px -2px rgba(37, 99, 235, 0.5); transform: translateY(-2px); }
        .btn-add-custom:active { transform: translateY(0); box-shadow: 0 2px 8px -2px rgba(37, 99, 235, 0.4); }
        .btn-add-custom svg { transition: transform 0.25s ease; }
        .btn-add-custom:hover svg { transform: rotate(90deg); }

        /* Bottoni tabella desktop */
        .btn-table-action { display: inline-flex !important; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s ease; white-space: nowrap; }
        .btn-table-edit { background-color: #3b82f6 !important; color: white !important; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2); }
        .btn-table-edit:hover { background-color: #2563eb !important; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3); transform: translateY(-1px); }
        .btn-table-edit svg { transition: transform 0.2s ease; stroke: white !important; }
        .btn-table-edit:hover svg { transform: rotate(12deg); }
        .btn-table-delete { background-color: #ef4444 !important; color: white !important; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2); }
        .btn-table-delete:hover { background-color: #dc2626 !important; box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3); transform: translateY(-1px); }
        .btn-table-delete svg { transition: transform 0.2s ease; stroke: white !important; }
        .btn-table-delete:hover svg { transform: scale(1.1); }
        .btn-table-action:active { transform: translateY(0); }

        /* Bottoni card mobile */
        .btn-card-compact { padding: 0.625rem 1.25rem !important; font-size: 0.875rem; min-width: auto; flex: 0 0 auto; }
        .btn-card-action { font-weight: 600; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; }
        .btn-card-action:active { transform: translateY(0); }
        .btn-card-edit { background-color: #3b82f6 !important; color: white !important; }
        .btn-card-edit:hover { background-color: #2563eb !important; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4); transform: translateY(-2px); }
        .btn-card-edit svg { transition: transform 0.2s ease; stroke: white !important; }
        .btn-card-edit:hover svg { transform: rotate(12deg); }
        .btn-card-delete { background-color: #ef4444 !important; color: white !important; }
        .btn-card-delete:hover { background-color: #dc2626 !important; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4); transform: translateY(-2px); }
        .btn-card-delete svg { transition: transform 0.2s ease; stroke: white !important; }
        .btn-card-delete:hover svg { transform: scale(1.1); }

        /* Animazioni Modali */
        @keyframes blowUpModal { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }

        /* Modal Overlay */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0); }
        .modal-active .modal-backdrop { animation: fadeIn 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-backdrop { animation: fadeOut 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }

        .modal-container { position: relative; width: 90%; max-width: 500px; margin: 0 auto; pointer-events: auto; }
        .modal-container-large { max-width: 600px; max-height: 90vh; overflow-y: auto; }
        
        .modal-content-wrapper { background: white; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; }
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }

        @keyframes fadeIn { 0% { background: rgba(0, 0, 0, 0); } 100% { background: rgba(0, 0, 0, 0.7); } }
        @keyframes fadeOut { 0% { background: rgba(0, 0, 0, 0.7); } 100% { background: rgba(0, 0, 0, 0); } }

        @keyframes checkmark { 0% { stroke-dashoffset: 50; stroke-dasharray: 50; } 100% { stroke-dashoffset: 0; stroke-dasharray: 50; } }
        .animate-check { stroke-dashoffset: 50; stroke-dasharray: 50; animation: checkmark 0.6s ease-in-out forwards; }
        @keyframes pulse-success { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
        .modal-active .modal-content-wrapper svg { animation: pulse-success 0.6s ease-in-out; }

        .modal-header { background: linear-gradient(135deg, #a16207 0%, #6f3404 100%); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 1.25rem; font-weight: 700; color: white; margin: 0; }
        .modal-close { color: white; background: transparent; border: none; cursor: pointer; padding: 0.25rem; transition: all 0.15s; border-radius: 0.375rem; }
        .modal-close:hover { background: rgba(255, 255, 255, 0.1); transform: rotate(90deg); }

        .modal-body { max-height: calc(90vh - 200px); overflow-y: auto; padding: 1.5rem; }
        @media (max-width: 640px) { .modal-container, .modal-container-large { width: 95%; max-width: none; margin: 1rem auto; } .modal-body { max-height: calc(100vh - 250px); padding: 1rem; } }

        .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem; background-color: #f9fafb; border-top: 1px solid #e5e7eb; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .form-input { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; line-height: 1.25rem; transition: all 0.15s; }
        .form-input:focus { outline: none; border-color: #3b82f6; ring: 2px; ring-color: rgba(59, 130, 246, 0.3); }

        .btn-primary { padding: 0.625rem 1.25rem; background: linear-gradient(135deg, #a16207 0%, #6f3404 100%); color: white; font-weight: 600; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2); }
        .btn-primary:hover { background: linear-gradient(135deg, #6f3404 0%, rgb(46, 27, 2) 100%); box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3); transform: translateY(-1px); }
        .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
        .btn-secondary { padding: 0.625rem 1.25rem; background: white; color: #374151; font-weight: 600; border: 1px solid #d1d5db; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; }
        .btn-secondary:hover { background: #f9fafb; border-color: #9ca3af; }
        .btn-danger { padding: 0.625rem 1.25rem; background: #ef4444; color: white; font-weight: 600; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2); }
        .btn-danger:hover { background: #dc2626; box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3); transform: translateY(-1px); }
        .btn-danger:disabled { opacity: 0.7; cursor: not-allowed; }

        /* Mobile spacing */
        .space-y-4 > * + * { margin-top: 1rem; }
    </style>
</x-app-layout>