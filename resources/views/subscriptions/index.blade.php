<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Spese e Abbonamenti
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4" 
             x-data="subscriptionsManager()"
             x-init="init()">

            <details class="bg-violet-50 p-5 rounded-xl mb-8 border-2 border-violet-200 shadow-sm mt-4">
                <summary class="font-black text-violet-800 cursor-pointer text-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-2xl"></i>
                    Guida passo-passo: Cosa metto in questa pagina? (Clicca per leggere)
                </summary>
                <div class="mt-5 text-slate-700 leading-relaxed space-y-6 text-base">
                    <p class="font-medium text-lg bg-white p-3 rounded-lg border border-violet-100">
                        📋 <strong>Il grande raccoglitore!</strong> Qui dentro puoi inserire TUTTO quello che non è un'auto o un documento d'identità. Ad esempio: la Revisione della Caldaia, l'abbonamento a Netflix, la rata dell'IMU, la palestra o l'assicurazione della casa.
                    </p>
                    <div>
                        <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">➕ 1. Come inserire una nuova spesa</h4>
                        <ol class="list-decimal ml-5 mt-2 space-y-1">
                            <li>Clicca sul pulsante viola <strong>"Aggiungi Spesa"</strong> in alto.</li>
                            <li>Dai un <strong>Nome</strong> facile da ricordare (es. "Abbonamento Palestra").</li>
                            <li>Scegli la <strong>Categoria</strong> giusta e l'eventuale <strong>Intestatario</strong>.</li>
                            <li>Scegli la <strong>Data di Scadenza</strong> o del prossimo pagamento.</li>
                        </ol>
                    </div>
                    <div>
                        <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">🤖 2. Il "Rinnovo Automatico"</h4>
                        <ul class="space-y-2 ml-2 mt-2">
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-1"><i class="fa-solid fa-toggle-on"></i></span>
                                <div><strong>ACCESO:</strong> Per cose come Netflix o Prime. I soldi si scalano da soli dalla carta, tu non devi fare bonifici.</div>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-gray-400 mt-1"><i class="fa-solid fa-toggle-off"></i></span>
                                <div><strong>SPENTO:</strong> Per cose come la Caldaia, l'IMU o la TARI. In questi casi l'app aspetterà che tu confermi di averlo pagato.</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </details>

            <div class="mb-6">
                <div class="mobile-only-search">
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Cerca nome o categoria..." 
                               class="w-full pl-4 pr-10 py-3.5 bg-white border-2 border-gray-200 rounded-xl focus:border-violet-500 focus:ring-4 focus:ring-violet-100 transition text-base shadow-sm">
                        <div x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1" x-show="searchQuery"><span x-text="filteredSubscriptions.length"></span> risultati trovati</p>
                </div>
                
                <div class="desktop-only-search">
                    <div class="bg-white rounded-xl shadow-md p-4">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Cerca per nome servizio, categoria o persona..." 
                                   class="w-full pl-4 pr-10 py-3 bg-gray-50 border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-100 transition">
                            <div x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3" x-show="searchQuery">
                            <p class="text-sm text-gray-600"><span class="font-semibold" x-text="filteredSubscriptions.length"></span> risultati trovati</p>
                            <button @click="searchQuery = ''" class="text-sm text-violet-600 hover:text-violet-700 font-medium">Cancella ricerca</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6 flex justify-center lg:justify-start">
                <button @click="openCreateModal()" class="btn-add-custom">
                    <svg class="inline-block h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Aggiungi Spesa / Abbonamento</span>
                </button>
            </div>

            <div class="mobile-only bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="selectAll" class="w-5 h-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500 shadow-sm">
                        <span class="text-sm font-bold text-gray-700">Seleziona Tutti</span>
                    </label>
                    <span class="text-xs font-bold text-gray-400" x-text="filteredSubscriptions.length + ' risultati'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 font-medium">Ordina:</span>
                    <select x-model="sortCol" @change="sortAsc = true" class="flex-1 text-sm font-bold text-slate-700 bg-gray-50 border-gray-200 rounded-lg py-2">
                        <option value="data_scadenza">Scadenza</option>
                        <option value="nome">Nome Servizio</option>
                        <option value="importo">Importo</option>
                        <option value="user_name">Intestatario</option>
                    </select>
                    <button @click="sortAsc = !sortAsc" class="p-2 bg-violet-50 text-violet-600 rounded-lg">
                        <i class="fa-solid" :class="sortAsc ? 'fa-arrow-down-short-wide' : 'fa-arrow-up-wide-short'"></i>
                    </button>
                </div>
            </div>

            <div class="desktop-only bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center w-12">
                                    <input type="checkbox" x-model="selectAll" class="h-4 w-4 text-violet-600 focus:ring-violet-500 border-gray-300 rounded cursor-pointer">
                                </th>
                                <th @click="sortBy('nome')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition group select-none">
                                    <div class="flex items-center gap-2">Servizio
                                        <span x-show="sortCol === 'nome'" x-cloak class="text-violet-600"><i class="fa-solid fa-sort-up" x-show="sortAsc"></i><i class="fa-solid fa-sort-down" x-show="!sortAsc"></i></span>
                                        <span x-show="sortCol !== 'nome'" x-cloak class="text-gray-300 opacity-0 group-hover:opacity-100"><i class="fa-solid fa-sort"></i></span>
                                    </div>
                                </th>
                                <th @click="sortBy('user_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition group select-none">
                                    <div class="flex items-center gap-2">Intestatario / Veicolo
                                        <span x-show="sortCol === 'user_name'" x-cloak class="text-violet-600"><i class="fa-solid fa-sort-up" x-show="sortAsc"></i><i class="fa-solid fa-sort-down" x-show="!sortAsc"></i></span>
                                        <span x-show="sortCol !== 'user_name'" x-cloak class="text-gray-300 opacity-0 group-hover:opacity-100"><i class="fa-solid fa-sort"></i></span>
                                    </div>
                                </th>
                                <th @click="sortBy('data_scadenza')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition group select-none">
                                    <div class="flex items-center gap-2">Scadenza
                                        <span x-show="sortCol === 'data_scadenza'" x-cloak class="text-violet-600"><i class="fa-solid fa-sort-up" x-show="sortAsc"></i><i class="fa-solid fa-sort-down" x-show="!sortAsc"></i></span>
                                        <span x-show="sortCol !== 'data_scadenza'" x-cloak class="text-gray-300 opacity-0 group-hover:opacity-100"><i class="fa-solid fa-sort"></i></span>
                                    </div>
                                </th>
                                <th @click="sortBy('importo')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition group select-none">
                                    <div class="flex items-center gap-2">Importo
                                        <span x-show="sortCol === 'importo'" x-cloak class="text-violet-600"><i class="fa-solid fa-sort-up" x-show="sortAsc"></i><i class="fa-solid fa-sort-down" x-show="!sortAsc"></i></span>
                                        <span x-show="sortCol !== 'importo'" x-cloak class="text-gray-300 opacity-0 group-hover:opacity-100"><i class="fa-solid fa-sort"></i></span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rinnovo Auto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(sub, index) in filteredSubscriptions" :key="sub.id || index">
                                <tr class="hover:bg-gray-50 transition cursor-pointer" :class="{ 'bg-red-50': sub.giorni_rimanenti <= 7, 'bg-violet-50': selectedItems.includes(sub.id) }" @click.self="toggleSelection(sub.id)">
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" :value="sub.id" x-model="selectedItems" class="h-4 w-4 text-violet-600 focus:ring-violet-500 border-gray-300 rounded cursor-pointer transition">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900" x-text="sub.nome"></div>
                                        <div class="text-xs font-semibold text-violet-600 uppercase tracking-wider" x-text="sub.categoria"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-700" x-text="sub.user_name !== 'N/D' ? sub.user_name : (sub.vehicle_desc || 'Famiglia')"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-semibold" x-text="sub.data_scadenza_formatted"></div>
                                        <div class="text-xs font-bold" :class="sub.giorni_rimanenti <= 7 ? 'text-red-600' : 'text-green-600'">
                                            <span x-text="sub.giorni_rimanenti"></span> gg
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        €<span x-text="sub.importo_formatted"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="sub.rinnovo_automatico ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                            <i class="fa-solid mr-1" :class="sub.rinnovo_automatico ? 'fa-check' : 'fa-xmark'"></i>
                                            <span x-text="sub.rinnovo_automatico ? 'Sì' : 'No'"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 space-x-3">
                                        <button @click="openEditModal(sub)" class="btn-table-action btn-table-edit">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Modifica
                                        </button>
                                        <button @click="openDeleteModal(sub)" class="btn-table-action btn-table-delete">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Elimina
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredSubscriptions.length === 0" x-cloak>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fa-solid fa-file-invoice-dollar text-4xl text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">Nessuna spesa trovata</p>
                                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mobile-only space-y-4 px-4 pb-20">
                <template x-for="(sub, index) in filteredSubscriptions" :key="sub.id || index">
                    <div class="bg-white rounded-2xl shadow-xl border overflow-hidden relative" :class="[sub.giorni_rimanenti <= 7 ? 'border-red-300' : 'border-gray-200', selectedItems.includes(sub.id) ? 'ring-4 ring-violet-400 transform scale-[1.02] shadow-2xl' : '']">
                        
                        <div class="absolute top-4 right-4 z-10 bg-white/40 backdrop-blur-sm p-1 rounded-xl shadow-sm border border-white">
                            <input type="checkbox" :value="sub.id" x-model="selectedItems" class="w-6 h-6 rounded border-gray-300 text-violet-600 focus:ring-violet-500 cursor-pointer shadow-sm">
                        </div>

                        <div class="relative bg-gradient-to-br from-violet-500 via-violet-600 to-violet-700 px-5 py-6">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full -ml-12 -mb-12"></div>
                            
                            <div class="relative flex items-center pr-12">
                                <div class="flex-1">
                                    <p class="text-xs text-violet-200 font-bold uppercase tracking-wider mb-1" x-text="sub.categoria"></p>
                                    <h3 class="text-2xl font-black text-white tracking-tight mb-1 truncate" x-text="sub.nome"></h3>
                                    <p class="text-base text-white opacity-90 truncate" x-text="sub.user_name !== 'N/D' ? sub.user_name : (sub.vehicle_desc || 'Famiglia')"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6 bg-gradient-to-b from-gray-50 to-white">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="bg-gray-100 rounded-lg p-3">
                                    <p class="text-xs font-medium text-gray-500">Scadenza</p>
                                    <p class="text-lg font-bold text-gray-900" x-text="sub.data_scadenza_formatted"></p>
                                    <p class="text-sm font-bold mt-1" :class="sub.giorni_rimanenti <= 7 ? 'text-red-600' : 'text-green-600'" x-text="sub.giorni_rimanenti + ' gg'"></p>
                                </div>
                                <div class="bg-gray-100 rounded-lg p-3">
                                    <p class="text-xs font-medium text-gray-500">Importo</p>
                                    <p class="text-xl font-bold text-gray-900 mt-1">€<span x-text="sub.importo_formatted"></span></p>
                                    <p class="text-xs font-semibold mt-1" :class="sub.rinnovo_automatico ? 'text-green-600' : 'text-gray-500'">
                                        <i class="fa-solid" :class="sub.rinnovo_automatico ? 'fa-rotate' : 'fa-hand-pointer'"></i>
                                        <span x-text="sub.rinnovo_automatico ? 'Rinnovo Auto' : 'Manuale'"></span>
                                    </p>
                                </div>
                            </div>

                            <template x-if="sub.note || sub.url_gestione">
                                <div class="text-sm text-gray-600 bg-violet-50 p-3 rounded-lg border-l-4 border-violet-400">
                                    <template x-if="sub.note">
                                        <p class="mb-2"><span class="font-medium text-violet-900">📝 Note:</span> <span x-text="sub.note"></span></p>
                                    </template>
                                    <template x-if="sub.url_gestione">
                                        <a :href="sub.url_gestione" target="_blank" class="text-violet-700 font-bold hover:underline flex items-center">
                                            <i class="fa-solid fa-link mr-2"></i> Gestisci online
                                        </a>
                                    </template>
                                </div>
                            </template>
                            
                            <div class="mt-5 pt-4 border-t border-gray-200 flex justify-center gap-3 pb-2">
                                <button @click="openEditModal(sub)" class="btn-card-action btn-card-compact" style="background-color: #8b5cf6 !important;">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> <span>Modifica</span>
                                </button>
                                <button @click="openDeleteModal(sub)" class="btn-card-action btn-card-delete btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> <span>Elimina</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="filteredSubscriptions.length === 0" x-cloak class="bg-white rounded-2xl shadow-lg border border-gray-200 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-file-invoice-dollar text-4xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Nessuna spesa trovata</p>
                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                </div>
            </div>

            <div class="fixed bottom-4 sm:bottom-6 left-0 right-0 z-50 flex justify-center pointer-events-none w-full px-4">
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
                        <span class="bg-violet-900 text-white font-black px-3 py-1 rounded-lg text-base sm:text-lg shadow-inner" x-text="selectedItems.length"></span>
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
                            <h3 class="modal-title">Nuova Spesa / Abbonamento</h3>
                            <button @click="closeCreateModal()" class="modal-close"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form @submit.prevent="submitCreate($event)" class="modal-body">
                            @csrf
                            <div class="space-y-5">
                                <div><label class="form-label">Nome Servizio / Spesa *</label><input type="text" name="nome" required class="form-input" placeholder="Es. Netflix, Palestra"></div>
                                <div>
                                    <label class="form-label">Categoria *</label>
                                    <select name="categoria" required class="form-input">
                                        <option value="Casa e Manutenzione">🏠 Casa e Manutenzione</option>
                                        <option value="Software e App">💻 Software e App</option>
                                        <option value="Intrattenimento">🍿 Intrattenimento (TV, Musica)</option>
                                        <option value="Salute e Benessere">🏥 Salute e Benessere</option>
                                        <option value="Tasse e Tributi">🏛️ Tasse e Tributi (IMU, TARI)</option>
                                        <option value="Altro">📦 Altro</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="form-label">Prossima Scadenza *</label><input type="date" name="data_scadenza" required class="form-input"></div>
                                    <div><label class="form-label">Importo (€)</label><input type="number" name="importo" step="0.01" min="0" class="form-input" placeholder="0.00"></div>
                                </div>
                                
                                <div class="bg-violet-50 p-4 rounded-lg border border-violet-100">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-bold text-violet-900 text-sm">Rinnovo Automatico?</p>
                                            <p class="text-xs text-violet-700">I soldi vengono scalati da soli?</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="rinnovo_automatico" value="1">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    
                                    <div class="mt-5 pt-4 border-t border-violet-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <p class="font-bold text-violet-900 text-sm">Regole di Notifica</p>
                                                <p class="text-xs text-violet-700 mt-0.5">Scegli quando essere avvisato</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" x-model="createNotifCustom" class="sr-only peer">
                                                <div class="w-9 h-5 bg-violet-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-violet-600"></div>
                                            </label>
                                        </div>
                                        
                                        <input type="hidden" name="notification_override" :value="JSON.stringify(createNotifCustom ? createNotifRules : null)">

                                        <div x-show="!createNotifCustom" class="text-xs text-violet-800 bg-white p-2.5 rounded-lg border border-violet-200 font-medium flex items-center gap-2">
                                            <i class="fa-solid fa-globe text-violet-500"></i> Usa le impostazioni globali
                                        </div>

                                        <div x-show="createNotifCustom" x-cloak class="space-y-2 mt-3">
                                            <template x-for="(rule, index) in createNotifRules" :key="rule.id">
                                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-violet-200 shadow-sm">
                                                    <input type="number" x-model.number="rule.days_before" min="0" class="w-16 px-2 py-1.5 text-sm border-gray-300 rounded focus:ring-violet-500 focus:border-violet-500 font-bold text-center">
                                                    <span class="text-xs font-medium text-gray-600">gg prima via</span>
                                                    <select x-model="rule.channel" class="flex-1 px-2 py-1.5 text-sm border-gray-300 rounded focus:ring-violet-500 focus:border-violet-500 font-medium">
                                                        <option value="email">Email</option>
                                                        <option value="push">Push (App)</option>
                                                    </select>
                                                    <button type="button" @click="removeCreateRule(rule.id)" class="text-red-400 hover:text-red-600 p-1.5 rounded hover:bg-red-50 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                                </div>
                                            </template>
                                            <button type="button" @click="addCreateRule()" class="text-xs font-bold text-violet-700 hover:text-violet-900 mt-2 flex items-center gap-1.5 px-1">
                                                <i class="fa-solid fa-circle-plus"></i> Aggiungi un'altra regola
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Intestatario (Opzionale)</label>
                                        <select name="user_id" class="form-input">
                                            <option value="">Tutta la famiglia</option>
                                            @foreach($users as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Veicolo (Opzionale)</label>
                                        <select name="vehicle_id" class="form-input">
                                            <option value="">Nessun veicolo</option>
                                            @foreach($vehicles as $v) <option value="{{ $v->id }}">{{ $v->marca }} {{ $v->modello }}</option> @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div><label class="form-label">Link Gestione online</label><input type="url" name="url_gestione" class="form-input" placeholder="https://..."></div>
                                <div><label class="form-label">Note</label><textarea name="note" rows="2" class="form-input"></textarea></div>
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
                            <h3 class="modal-title">Modifica Spesa / Abbonamento</h3>
                            <button @click="closeEditModal()" class="modal-close"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form @submit.prevent="submitEdit($event)" class="modal-body">
                            @csrf
                            <div class="space-y-5">
                                <div><label class="form-label">Nome Servizio / Spesa *</label><input type="text" name="nome" x-model="editSub.nome" required class="form-input"></div>
                                <div>
                                    <label class="form-label">Categoria *</label>
                                    <select name="categoria" x-model="editSub.categoria" required class="form-input">
                                        <option value="Casa e Manutenzione">🏠 Casa e Manutenzione</option>
                                        <option value="Software e App">💻 Software e App</option>
                                        <option value="Intrattenimento">🍿 Intrattenimento (TV, Musica)</option>
                                        <option value="Salute e Benessere">🏥 Salute e Benessere</option>
                                        <option value="Tasse e Tributi">🏛️ Tasse e Tributi (IMU, TARI)</option>
                                        <option value="Altro">📦 Altro</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="form-label">Prossima Scadenza *</label><input type="date" name="data_scadenza" x-model="editSub.data_scadenza" required class="form-input"></div>
                                    <div><label class="form-label">Importo (€)</label><input type="number" name="importo" x-model="editSub.importo" step="0.01" min="0" class="form-input"></div>
                                </div>
                                
                                <div class="bg-violet-50 p-4 rounded-lg border border-violet-100">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-bold text-violet-900 text-sm">Rinnovo Automatico?</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="rinnovo_automatico" x-model="editSub.rinnovo_automatico" value="1">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    
                                    <div class="mt-5 pt-4 border-t border-violet-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <p class="font-bold text-violet-900 text-sm">Regole di Notifica</p>
                                                <p class="text-xs text-violet-700 mt-0.5">Scegli quando essere avvisato</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" x-model="editNotifCustom" class="sr-only peer">
                                                <div class="w-9 h-5 bg-violet-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-violet-600"></div>
                                            </label>
                                        </div>
                                        
                                        <input type="hidden" name="notification_override" :value="JSON.stringify(editNotifCustom ? editNotifRules : null)">

                                        <div x-show="!editNotifCustom" class="text-xs text-violet-800 bg-white p-2.5 rounded-lg border border-violet-200 font-medium flex items-center gap-2">
                                            <i class="fa-solid fa-globe text-violet-500"></i> Usa le impostazioni globali
                                        </div>

                                        <div x-show="editNotifCustom" x-cloak class="space-y-2 mt-3">
                                            <template x-for="(rule, index) in editNotifRules" :key="rule.id">
                                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-violet-200 shadow-sm">
                                                    <input type="number" x-model.number="rule.days_before" min="0" class="w-16 px-2 py-1.5 text-sm border-gray-300 rounded focus:ring-violet-500 focus:border-violet-500 font-bold text-center">
                                                    <span class="text-xs font-medium text-gray-600">gg prima via</span>
                                                    <select x-model="rule.channel" class="flex-1 px-2 py-1.5 text-sm border-gray-300 rounded focus:ring-violet-500 focus:border-violet-500 font-medium">
                                                        <option value="email">Email</option>
                                                        <option value="push">Push (App)</option>
                                                    </select>
                                                    <button type="button" @click="removeEditRule(rule.id)" class="text-red-400 hover:text-red-600 p-1.5 rounded hover:bg-red-50 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                                </div>
                                            </template>
                                            <button type="button" @click="addEditRule()" class="text-xs font-bold text-violet-700 hover:text-violet-900 mt-2 flex items-center gap-1.5 px-1">
                                                <i class="fa-solid fa-circle-plus"></i> Aggiungi un'altra regola
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Intestatario (Opzionale)</label>
                                        <select name="user_id" x-model="editSub.user_id" class="form-input">
                                            <option value="">Tutta la famiglia</option>
                                            @foreach($users as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Veicolo (Opzionale)</label>
                                        <select name="vehicle_id" x-model="editSub.vehicle_id" class="form-input">
                                            <option value="">Nessun veicolo</option>
                                            @foreach($vehicles as $v) <option value="{{ $v->id }}">{{ $v->marca }} {{ $v->modello }}</option> @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div><label class="form-label">Link Gestione online</label><input type="url" name="url_gestione" x-model="editSub.url_gestione" class="form-input"></div>
                                <div><label class="form-label">Note</label><textarea name="note" x-model="editSub.note" rows="2" class="form-input"></textarea></div>
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
                            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Elimina Spesa</h3>
                            <p class="text-sm text-gray-600 text-center mb-1">Sei sicuro di voler eliminare definitivamente</p>
                            <p class="text-center mb-4"><strong class="text-gray-900" x-text="deleteSub.nome"></strong>?</p>
                        </div>
                        <form @submit.prevent="submitDelete(`/subscriptions/${deleteSub.id}`)" class="px-6 pb-6">
                            @csrf
                            <div class="flex justify-center space-x-3 mb-4">
                                <button type="button" @click="closeDeleteModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-danger" :disabled="isSubmitting">Elimina</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div x-show="showBulkDeleteModal" style="display: none;" x-cloak @keydown.escape="closeBulkDeleteModal()" class="modal-overlay" :class="{ 'modal-active': showBulkDeleteModal && !isClosingBulkDelete, 'modal-out': isClosingBulkDelete }">
                <div class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
                <div class="modal-container w-[90%] sm:w-full max-w-md pointer-events-auto mx-auto" @click.stop>
                    <div class="modal-content-wrapper bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-6 text-center pt-8">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4"><i class="fa-solid fa-trash-can text-3xl text-red-500"></i></div>
                            <h3 class="text-2xl font-black text-slate-800 mb-2">Elimina Multipla</h3>
                            <p class="text-sm text-slate-500 mb-6">Stai per eliminare definitivamente <strong class="text-slate-800" x-text="selectedItems.length"></strong> spese/abbonamenti in un colpo solo. Confermi?</p>
                        </div>
                        <div class="px-6 pb-6 flex flex-col sm:flex-row gap-3 border-t border-slate-50 pt-4">
                            <button @click="closeBulkDeleteModal()" class="flex-1 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200">Annulla</button>
                            <button @click="executeBulkDelete()" class="flex-1 py-3 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 shadow-md">Sì, Elimina Tutti</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div x-show="showSuccessModal" x-cloak @keydown.escape.window="closeSuccessModal()" class="modal-overlay" :class="{ 'modal-active': showSuccessModal && !isClosingSuccess, 'modal-out': isClosingSuccess }">
                <div @click="closeSuccessModal()" class="modal-backdrop"></div>
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="p-8 text-center">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full mb-6" :class="{ 'bg-green-100': successType === 'success', 'bg-red-100': successType === 'error' }">
                                <svg x-show="successType === 'success'" class="h-10 w-10 text-green-600 animate-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="successType === 'error'" class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3" :class="{ 'text-green-900': successType === 'success', 'text-red-900': successType === 'error' }">
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
        function subscriptionsManager() {
            return {
                showCreateModal: false, showEditModal: false, showDeleteModal: false, showSuccessModal: false,
                isClosingCreate: false, isClosingEdit: false, isClosingDelete: false, isClosingSuccess: false,
                successMessage: '', successType: 'success', isSubmitting: false,
                
                editSub: {},
                deleteSub: {},
                searchQuery: '',
                
                selectedItems: [],
                showBulkDeleteModal: false,
                isClosingBulkDelete: false,
                sortCol: 'data_scadenza',
                sortAsc: true,
                
                subscriptions: @json($subscriptions),

                // -- STATO NOTIFICHE MODALE CREATE --
                createNotifCustom: false,
                createNotifRules: [{ id: Date.now(), days_before: 15, channel: 'email' }],

                // -- STATO NOTIFICHE MODALE EDIT --
                editNotifCustom: false,
                editNotifRules: [],

                init() {
                    @if(session('success')) this.showSuccess("{{ session('success') }}", 'success'); @endif
                    @if(session('error')) this.showSuccess("{{ session('error') }}", 'error'); @endif
                },

                // GESTIONE REGOLE CREATE
                addCreateRule() {
                    this.createNotifRules.push({ id: Date.now(), days_before: 7, channel: 'email' });
                },
                removeCreateRule(id) {
                    this.createNotifRules = this.createNotifRules.filter(r => r.id !== id);
                    if(this.createNotifRules.length === 0) this.addCreateRule();
                },

                // GESTIONE REGOLE EDIT
                addEditRule() {
                    this.editNotifRules.push({ id: Date.now(), days_before: 7, channel: 'email' });
                },
                removeEditRule(id) {
                    this.editNotifRules = this.editNotifRules.filter(r => r.id !== id);
                    if(this.editNotifRules.length === 0) this.addEditRule();
                },

                get filteredSubscriptions() {
                    let filtered = Array.isArray(this.subscriptions) ? this.subscriptions : [];
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(s => {
                            const name = s.nome ? s.nome.toLowerCase() : '';
                            const cat = s.categoria ? s.categoria.toLowerCase() : '';
                            const user = s.user_name ? s.user_name.toLowerCase() : '';
                            return name.includes(q) || cat.includes(q) || user.includes(q);
                        });
                    }

                    return filtered.sort((a, b) => {
                        let aVal, bVal;
                        switch (this.sortCol) {
                            case 'data_scadenza': aVal = a.data_scadenza ? new Date(a.data_scadenza) : new Date(0); bVal = b.data_scadenza ? new Date(b.data_scadenza) : new Date(0); break;
                            case 'nome': aVal = a.nome || ''; bVal = b.nome || ''; break;
                            case 'importo': aVal = a.importo || 0; bVal = b.importo || 0; break;
                            case 'user_name': aVal = a.user_name || ''; bVal = b.user_name || ''; break;
                            default: return 0;
                        }
                        if (aVal < bVal) return this.sortAsc ? -1 : 1;
                        if (aVal > bVal) return this.sortAsc ? 1 : -1;
                        return 0;
                    });
                },

                get selectAll() {
                    return this.filteredSubscriptions.length > 0 && this.selectedItems.length === this.filteredSubscriptions.length;
                },
                set selectAll(value) {
                    if (value) {
                        this.selectedItems = [...this.filteredSubscriptions.map(sub => sub.id)];
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

                openCreateModal() { 
                    // Resetta le regole di default ogni volta che apri la finestra
                    this.createNotifCustom = false;
                    this.createNotifRules = [{ id: Date.now(), days_before: 15, channel: 'email' }];
                    this.showCreateModal = true; 
                },
                closeCreateModal() { this.isClosingCreate = true; setTimeout(() => { this.showCreateModal = false; this.isClosingCreate = false; }, 500); },
                
                openEditModal(sub) { 
                    this.editSub = JSON.parse(JSON.stringify(sub)); 
                    if(this.editSub.data_scadenza && this.editSub.data_scadenza.includes('T')) {
                        this.editSub.data_scadenza = this.editSub.data_scadenza.split('T')[0];
                    }

                    // Carica il JSON delle notifiche o imposta il default
                    if(this.editSub.notification_override && this.editSub.notification_override.length > 0) {
                        this.editNotifCustom = true;
                        this.editNotifRules = this.editSub.notification_override;
                    } else {
                        this.editNotifCustom = false;
                        this.editNotifRules = [{ id: Date.now(), days_before: 15, channel: 'email' }];
                    }

                    this.showEditModal = true; 
                },
                closeEditModal() { this.isClosingEdit = true; setTimeout(() => { this.showEditModal = false; this.isClosingEdit = false; }, 500); },
                
                openDeleteModal(sub) { this.deleteSub = sub; this.showDeleteModal = true; },
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
                            fetch(`/subscriptions/${id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            })
                        );
                        await Promise.all(promises);

                        const count = this.selectedItems.length;
                        this.selectedItems = [];
                        await this.refreshData();
                        this.showSuccess(`Eliminati ${count} abbonamenti con successo!`, 'success');
                    } catch(e) {
                        this.showSuccess('Errore durante l\'eliminazione', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async refreshData() {
                    try {
                        const res = await fetch('{{ route("subscriptions.index") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }});
                        if (res.ok) {
                            const data = await res.json();
                            this.subscriptions = data.subscriptions;
                        }
                    } catch (error) { console.error('Errore aggiornamento dati:', error); }
                },

                async submitCreate(event) {
                    this.isSubmitting = true;
                    const form = event.target;
                    const formData = new FormData(form);
                    try {
                        const response = await fetch('{{ route("subscriptions.store") }}', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        const data = await response.json();
                        if (response.ok || response.status === 200 || response.status === 201) {
                            this.closeCreateModal();
                            form.reset();
                            await this.refreshData();
                            this.showSuccess(data.message || 'Operazione completata!', 'success');
                        } else {
                            this.showSuccess(data.message || 'Errore', 'error');
                        }
                    } catch (error) {
                        this.showSuccess('Errore di connessione', 'error');
                    } finally { this.isSubmitting = false; }
                },

                async submitEdit(event) {
                    this.isSubmitting = true;
                    const form = event.target;
                    const formData = new FormData(form);
                    formData.set('_method', 'PUT');
                    try {
                        const response = await fetch(`/subscriptions/${this.editSub.id}`, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        const data = await response.json();
                        if (response.ok || response.status === 200 || response.status === 201) {
                            this.closeEditModal();
                            await this.refreshData();
                            this.showSuccess(data.message || 'Operazione completata!', 'success');
                        } else {
                            this.showSuccess(data.message || 'Errore', 'error');
                        }
                    } catch (error) {
                        this.showSuccess('Errore di connessione', 'error');
                    } finally { this.isSubmitting = false; }
                },

                async submitDelete(url) {
                    this.isSubmitting = true;
                    try {
                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });
                        const data = await response.json();
                        if (response.ok) {
                            this.closeDeleteModal();
                            await this.refreshData();
                            this.showSuccess(data.message || 'Spesa eliminata!', 'success');
                        } else {
                            this.closeDeleteModal();
                            this.showSuccess(data.message || "Impossibile eliminare", 'error');
                        }
                    } catch (error) {
                        this.closeDeleteModal();
                        this.showSuccess('Errore di connessione', 'error');
                    } finally { this.isSubmitting = false; }
                }
            }
        }
    </script>

    <style>
        /* ... IL TUO BLOCCO CSS INVARIATO ... */
        [x-cloak] { display: none !important; }
        .desktop-only { display: block; }
        .mobile-only { display: none; }
        @media (max-width: 1023px) { .desktop-only { display: none; } .mobile-only { display: block; } }
        .mobile-only-search { display: block; }
        .desktop-only-search { display: none; }
        @media (min-width: 1024px) { .mobile-only-search { display: none !important; } .desktop-only-search { display: block !important; } }

        .btn-add-custom { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; font-weight: 600; font-size: 1rem; padding: 0.875rem 1.75rem; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 4px 14px -2px rgba(109, 40, 217, 0.4); transition: all 0.25s ease; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; }
        .btn-add-custom:hover { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); box-shadow: 0 8px 20px -2px rgba(109, 40, 217, 0.5); transform: translateY(-2px); }
        .btn-add-custom:active { transform: translateY(0); box-shadow: 0 2px 8px -2px rgba(109, 40, 217, 0.4); }

        .btn-table-action { display: inline-flex !important; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s ease; white-space: nowrap; }
        .btn-table-edit { background-color: #8b5cf6 !important; color: white !important; box-shadow: 0 2px 4px rgba(139, 92, 246, 0.2); }
        .btn-table-edit:hover { background-color: #7c3aed !important; box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3); transform: translateY(-1px); }
        .btn-table-delete { background-color: #ef4444 !important; color: white !important; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2); }
        .btn-table-delete:hover { background-color: #dc2626 !important; box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3); transform: translateY(-1px); }

        .btn-card-compact { padding: 0.625rem 1.25rem !important; font-size: 0.875rem; min-width: auto; flex: 0 0 auto; color: white; }
        .btn-card-action { font-weight: 600; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; }
        .btn-card-action:active { transform: translateY(0); }
        .btn-card-delete { background-color: #ef4444 !important; }
        .btn-card-delete:hover { background-color: #dc2626 !important; transform: translateY(-2px); }

        .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);}
        input:checked + .slider { background-color: #10b981; }
        input:checked + .slider:before { transform: translateX(26px); }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0); }
        .modal-active .modal-backdrop { animation: fadeIn 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-backdrop { animation: fadeOut 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }

        .modal-container { position: relative; width: 90%; max-width: 500px; margin: 0 auto; pointer-events: auto; }
        .modal-container-large { max-width: 600px; max-height: 90vh; overflow-y: auto; }
        .modal-content-wrapper { background: white; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; transform: scale(0); }
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }

        @keyframes fadeIn { 0% { background: rgba(0, 0, 0, 0); } 100% { background: rgba(0, 0, 0, 0.7); } }
        @keyframes fadeOut { 0% { background: rgba(0, 0, 0, 0.7); } 100% { background: rgba(0, 0, 0, 0); } }
        @keyframes blowUpModal { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }

        .modal-header { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 1.25rem; font-weight: 700; color: white; margin: 0; }
        .modal-close { color: white; background: transparent; border: none; cursor: pointer; padding: 0.25rem; transition: all 0.15s; border-radius: 0.375rem; }
        .modal-close:hover { background: rgba(255, 255, 255, 0.1); transform: rotate(90deg); }
        
        .modal-body { max-height: calc(90vh - 200px); overflow-y: auto; padding: 1.5rem; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem; background-color: #f9fafb; border-top: 1px solid #e5e7eb; }
        
        .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .form-input { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.15s; }
        .form-input:focus { outline: none; border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.3); }

        .btn-primary { padding: 0.625rem 1.25rem; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; font-weight: 600; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; }
        .btn-primary:hover { box-shadow: 0 4px 8px rgba(109, 40, 217, 0.3); transform: translateY(-1px); }
        .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .btn-secondary { padding: 0.625rem 1.25rem; background: white; color: #374151; font-weight: 600; border: 1px solid #d1d5db; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; }
        .btn-secondary:hover { background: #f9fafb; border-color: #9ca3af; }
        .btn-danger { padding: 0.625rem 1.25rem; background: #ef4444; color: white; font-weight: 600; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; }
        .btn-danger:hover { background: #dc2626; transform: translateY(-1px); }
        
        @keyframes checkmark { 0% { stroke-dashoffset: 50; stroke-dasharray: 50; } 100% { stroke-dashoffset: 0; stroke-dasharray: 50; } }
        .animate-check { stroke-dashoffset: 50; stroke-dasharray: 50; animation: checkmark 0.6s ease-in-out forwards; }
    </style>
</x-app-layout>