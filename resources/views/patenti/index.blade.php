<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Patenti
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4" x-data="patentiManager()" x-init="init()">

            <details class="bg-pink-50 p-5 rounded-xl mb-8 border-2 border-pink-200 shadow-sm mt-4">
                <summary class="font-black text-pink-800 cursor-pointer text-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-2xl"></i>
                    Guida passo-passo: Come gestisco le Patenti? (Clicca per leggere)
                </summary>
                <div class="mt-5 text-slate-700 leading-relaxed space-y-6 text-base">
                    <p class="font-medium text-lg bg-white p-3 rounded-lg border border-pink-100">
                        🚗 <strong>Benvenuto nell'archivio Patenti di famiglia!</strong> Qui teniamo traccia delle patenti di guida di tutti.
                    </p>
                    <div>
                        <h4 class="font-bold text-lg text-slate-800 border-b-2 border-slate-200 pb-1 mb-2">➕ 1. Come inserire una nuova patente</h4>
                        <p>Hai appena preso la patente o non l'hai mai inserita prima?</p>
                        <ol class="list-decimal ml-5 mt-2 space-y-1">
                            <li>Clicca sul pulsante rosa brillante <strong>"Aggiungi Patente"</strong> che vedi qui sopra.</li>
                            <li>Si aprirà una finestrella. Scegli la <strong>Persona</strong> a cui appartiene.</li>
                            <li>Scrivi il <strong>Numero della Patente</strong> e il <strong>Tipo</strong>.</li>
                            <li>Clicca su Salva!</li>
                        </ol>
                        <p class="mt-3 bg-white p-2 rounded text-slate-800 text-sm font-semibold border border-slate-200">
                            <i class="fa-solid fa-magic text-pink-500"></i> <strong>Magia automatica:</strong> Se lasci vuota la data di scadenza, il sistema la calcolerà da solo in base all'età!
                        </p>
                    </div>
                </div>
            </details>
            
            <div class="mb-6">
                <div class="mobile-only-search">
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Cerca per numero, tipo o persona..." class="w-full pl-4 pr-10 py-3.5 bg-white border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:ring-4 focus:ring-pink-100 transition text-base shadow-sm">
                        <div x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1" x-show="searchQuery"><span x-text="filteredPatenti.length"></span> risultati trovati</p>
                </div>
                
                <div class="desktop-only-search">
                    <div class="bg-white rounded-xl shadow-md p-4">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Cerca per numero patente, tipo o persona..." class="w-full pl-4 pr-10 py-3 bg-gray-50 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:bg-white focus:ring-4 focus:ring-pink-100 transition">
                            <div x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3" x-show="searchQuery">
                            <p class="text-sm text-gray-600"><span class="font-semibold" x-text="filteredPatenti.length"></span> risultati trovati</p>
                            <button @click="searchQuery = ''" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Cancella ricerca</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6 flex justify-center lg:justify-start">
                <button @click="openCreateModal()" class="btn-add-custom">
                    <svg class="inline-block h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Aggiungi Patente</span>
                </button>
            </div>

            <div class="mobile-only bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-20">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="selectAll" class="w-5 h-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500 shadow-sm">
                        <span class="text-sm font-bold text-gray-700">Seleziona Tutti</span>
                    </label>
                    <span class="text-xs font-bold text-gray-400" x-text="filteredPatenti.length + ' risultati'"></span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500 font-medium">Ordina:</span>
                    <select x-model="sortCol" class="p-2 flex-1 text-sm font-bold text-slate-700 bg-gray-50 border-gray-200 rounded-lg">
                        <option value="data_scadenza">Data Scadenza</option>
                        <option value="numero_patente">Numero Patente</option>
                        <option value="tipo">Tipo</option>
                        <option value="user">Persona</option>
                    </select>
                    <button @click="sortAsc = !sortAsc" class="p-2 bg-pink-50 text-pink-600 rounded-lg">
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
                                    <input type="checkbox" x-model="selectAll" class="w-4 h-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500 cursor-pointer">
                                </th>
                                <th @click="sortBy('user')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        Persona
                                        <span x-show="sortCol === 'user'" class="text-pink-600">
                                            <i class="fa-solid fa-sort-up" x-show="sortAsc"></i>
                                            <i class="fa-solid fa-sort-down" x-show="!sortAsc"></i>
                                        </span>
                                        <span x-show="sortCol !== 'user'" class="text-gray-300 opacity-0 group-hover:opacity-100 transition">
                                            <i class="fa-solid fa-sort"></i>
                                        </span>
                                    </div>
                                </th>
                                <th @click="sortBy('numero_patente')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        Numero Patente
                                        <span x-show="sortCol === 'numero_patente'" class="text-pink-600">
                                            <i class="fa-solid fa-sort-up" x-show="sortAsc"></i>
                                            <i class="fa-solid fa-sort-down" x-show="!sortAsc"></i>
                                        </span>
                                        <span x-show="sortCol !== 'numero_patente'" class="text-gray-300 opacity-0 group-hover:opacity-100 transition">
                                            <i class="fa-solid fa-sort"></i>
                                        </span>
                                    </div>
                                </th>
                                <th @click="sortBy('tipo')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        Tipo
                                        <span x-show="sortCol === 'tipo'" class="text-pink-600">
                                            <i class="fa-solid fa-sort-up" x-show="sortAsc"></i>
                                            <i class="fa-solid fa-sort-down" x-show="!sortAsc"></i>
                                        </span>
                                        <span x-show="sortCol !== 'tipo'" class="text-gray-300 opacity-0 group-hover:opacity-100 transition">
                                            <i class="fa-solid fa-sort"></i>
                                        </span>
                                    </div>
                                </th>
                                <th @click="sortBy('data_scadenza')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        Data Scadenza
                                        <span x-show="sortCol === 'data_scadenza'" class="text-pink-600">
                                            <i class="fa-solid fa-sort-up" x-show="sortAsc"></i>
                                            <i class="fa-solid fa-sort-down" x-show="!sortAsc"></i>
                                        </span>
                                        <span x-show="sortCol !== 'data_scadenza'" class="text-gray-300 opacity-0 group-hover:opacity-100 transition">
                                            <i class="fa-solid fa-sort"></i>
                                        </span>
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(p, index) in filteredPatenti" :key="p.id || index">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox" :value="p.id" x-model="selectedItems" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900" x-text="p.user?.name"></td>
                                    <td class="px-6 py-4 text-gray-700 font-mono uppercase" x-text="p.numero_patente"></td>
                                    <td class="px-6 py-4 text-gray-700 uppercase" x-text="p.tipo || '—'"></td>
                                    <td class="px-6 py-4 font-bold text-red-600" x-text="formatDate(p.data_scadenza) || '—'"></td>
                                    <td class="px-6 py-5 flex justify-center space-x-3">
                                        <button @click="openEditModal(p)" class="btn-table-action btn-table-edit">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Modifica
                                        </button>
                                        <button @click="openDeleteModal(p)" class="btn-table-action btn-table-delete">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Elimina
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredPatenti.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 font-medium">Nessuna patente trovata</p>
                                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>  
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="fixed bottom-4 sm:bottom-6 left-0 right-0 z-50 flex justify-center pointer-events-none w-full px-4">
                <div x-show="selectedItems.length > 0"
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-300 transform origin-bottom"
                     x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform origin-bottom"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-10 scale-95"
                     class="pointer-events-auto bg-slate-900/95 backdrop-blur-md text-white px-4 sm:px-6 py-3.5 sm:py-4 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] flex items-center gap-3 sm:gap-6 border border-slate-700/50 w-full sm:w-auto max-w-2xl justify-between">
                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        <span class="bg-pink-500 text-white font-black px-3 py-1 rounded-lg text-base sm:text-lg shadow-inner" x-text="selectedItems.length"></span>
                        <span class="font-semibold text-sm sm:text-base hidden xs:block">Selezionate</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 flex-1 justify-end">
                        <button @click="showBulkDeleteModal = true" class="bg-red-500 hover:bg-red-400 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl font-bold transition-all active:scale-95 flex items-center justify-center gap-2 shadow-lg flex-1 sm:flex-none" :disabled="isSubmitting">
                            <i class="fa-solid fa-trash-can"></i> <span class="text-sm hidden sm:inline">Elimina</span><span class="text-sm sm:hidden">Elimina</span>
                        </button>
                        <div class="w-px h-8 bg-slate-700 mx-1 sm:mx-2"></div>
                        <button @click="selectedItems = []" class="text-slate-400 hover:text-white p-2 flex-shrink-0 bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors" title="Annulla selezione">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mobile-only space-y-4 px-4">
                <template x-for="(p, index) in filteredPatenti" :key="p.id || index">
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                        <div class="relative bg-gradient-to-br from-pink-500 via-pink-600 to-pink-700 px-5 py-6">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full -ml-12 -mb-12"></div>
                            <div class="relative flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-2xl font-black text-white tracking-tight mb-1.5" x-text="p.user?.name"></h3>
                                    <p class="text-base text-white opacity-90 font-mono uppercase" x-text="p.numero_patente"></p>
                                </div>
                            </div>
                            <div class="px-5 py-5 absolute top-5 right-0">
                                <input type="checkbox" :value="p.id" x-model="selectedItems" class="w-6 h-6 rounded border-gray-300 text-pink-600">
                            </div>
                        </div>
                        
                        <div class="p-6 bg-gradient-to-b from-gray-50 to-white">
                            <div class="space-y-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-red-100 to-red-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Data Scadenza</p>
                                        <p class="text-lg font-bold text-red-600" x-text="formatDate(p.data_scadenza) || '—'"></p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tipo Patente</p>
                                        <p class="text-lg font-bold text-gray-900 uppercase" x-text="p.tipo || '—'"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-5 border-t border-gray-200 flex justify-center gap-3 pb-2">
                                <button @click="openEditModal(p)" class="btn-card-action btn-card-edit btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> <span>Modifica</span>
                                </button>
                                <button @click="openDeleteModal(p)" class="btn-card-action btn-card-delete btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> <span>Elimina</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="filteredPatenti.length === 0" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">Nessuna patente trovata</p>
                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>                
                </div>
            </div>

            <div x-show="showCreateModal" x-cloak @keydown.escape.window="closeCreateModal()" class="modal-overlay" :class="{ 'modal-active': showCreateModal && !isClosingCreate, 'modal-out': isClosingCreate }">
                <div @click="closeCreateModal()" class="modal-backdrop"></div>
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Nuova Patente</h3>
                            <button @click="closeCreateModal()" class="modal-close"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form @submit.prevent="submitForm($event, '{{ route('patenti.store') }}', 'POST')" class="modal-body">
                            @csrf
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Persona *</label>
                                    <select name="user_id" required class="form-input">
                                        <option value="">Seleziona persona</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div><label class="form-label">Numero Patente *</label><input type="text" name="numero_patente" required class="form-input uppercase" placeholder="ES: AB1234567"></div>
                                <div><label class="form-label">Tipo (es: B, A, C)</label><input type="text" name="tipo" class="form-input uppercase" maxlength="10" placeholder="ES: B"></div>
                                <div><label class="form-label">Data Rilascio</label><input type="date" name="data_rilascio" class="form-input"></div>
                                <div><label class="form-label">Data Scadenza (opzionale)</label><input type="date" name="data_scadenza" class="form-input"></div>
                                <div><label class="form-label">Costo previsto (€)</label><input type="number" name="importo" step="0.01" class="form-input" placeholder="0.00"></div>
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
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Modifica Patente</h3>
                            <button @click="closeEditModal()" class="modal-close"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form @submit.prevent="submitForm($event, `/patenti/${editPatente.id}`, 'PUT')" class="modal-body">
                            @csrf
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Persona *</label>
                                    <select name="user_id" x-model="editPatente.user.id" required class="form-input">
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div><label class="form-label">Numero Patente *</label><input type="text" name="numero_patente" x-model="editPatente.numero_patente" required class="form-input uppercase"></div>
                                <div><label class="form-label">Tipo (es: B, A, C)</label><input type="text" name="tipo" x-model="editPatente.tipo" class="form-input uppercase" maxlength="10"></div>
                                <div><label class="form-label">Data Rilascio</label><input type="date" name="data_rilascio" x-model="editPatente.data_rilascio" class="form-input"></div>
                                <div><label class="form-label">Data Scadenza (opzionale)</label><input type="date" name="data_scadenza" x-model="editPatente.data_scadenza" class="form-input"></div>
                                <div><label class="form-label">Costo previsto (€)</label><input type="number" name="importo" x-model="editPatente.importo" step="0.01" class="form-input"></div>
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
                            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Elimina Patente</h3>
                            <p class="text-sm text-gray-600 text-center mb-1">Sei sicuro di voler eliminare la patente di</p>
                            <p class="text-center mb-4"><strong class="text-lg text-gray-900 font-bold" x-text="deletePatente.user?.name"></strong>?</p>
                        </div>
                        <form @submit.prevent="submitDelete(`/patenti/${deletePatente.id}`)" class="px-6 pb-6">
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
                            <p class="text-sm text-slate-500 mb-6">Stai per eliminare definitivamente <strong class="text-slate-800" x-text="selectedItems.length"></strong> patenti in un colpo solo. Confermi?</p>
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
                                    :class="{ 'bg-green-600 hover:bg-green-700': successType === 'success', 'bg-red-600 hover:bg-red-700': successType === 'error' }">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function patentiManager() {
            return {
                showCreateModal: false, showEditModal: false, showDeleteModal: false, showSuccessModal: false,
                isClosingCreate: false, isClosingEdit: false, isClosingDelete: false, isClosingSuccess: false,
                isClosingBulkDelete: false,
                successMessage: '', successType: 'success', isSubmitting: false,
                
                editPatente: { user: {} },
                deletePatente: { user: {} },
                searchQuery: '',
                
                patenti: (() => { const data = @json($patenti); return Array.isArray(data) ? data : []; })(),
                
                sortCol: 'data_scadenza',
                sortAsc: true,
                selectedItems: [],
                showBulkDeleteModal: false,

                // -- STATO NOTIFICHE MODALE CREATE --
                createNotifCustom: false,
                createNotifRules: [{ id: Date.now(), days_before: 15, channel: 'email' }],

                // -- STATO NOTIFICHE MODALE EDIT --
                editNotifCustom: false,
                editNotifRules: [],

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

                get selectAll() {
                    const filtered = this.filteredPatenti;
                    return filtered.length > 0 && this.selectedItems.length === filtered.length && filtered.every(p => this.selectedItems.includes(p.id));
                },
                set selectAll(value) {
                    if (value) {
                        this.selectedItems = this.filteredPatenti.map(p => p.id);
                    } else {
                        this.selectedItems = [];
                    }
                },

                // Funzione di Ordinamento
                sortBy(col) {
                    if (this.sortCol === col) {
                        this.sortAsc = !this.sortAsc;
                    } else {
                        this.sortCol = col;
                        this.sortAsc = true;
                    }
                },

                async executeBulkDelete() {
                    this.isSubmitting = true;
                    this.showBulkDeleteModal = false;
                    try {
                        const promises = this.selectedItems.map(id =>
                            fetch(`/patenti/${id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            })
                        );
                        await Promise.all(promises);
                        this.selectedItems = [];
                        await this.refreshData();
                        this.showSuccess('Patenti eliminate con successo!', 'success');
                    } catch (error) {
                        this.showSuccess('Errore durante l\'eliminazione', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                init() {
                    @if(session('success')) this.showSuccess("{{ session('success') }}", 'success'); @endif
                    @if(session('error')) this.showSuccess("{{ session('error') }}", 'error'); @endif
                },

                get filteredPatenti() {
                    let filtered = Array.isArray(this.patenti) ? this.patenti : [];
                    
                    // 1. Filtro di ricerca
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(p => {
                            return (
                                (p.user?.name && p.user.name.toLowerCase().includes(query)) ||
                                (p.numero_patente && p.numero_patente.toLowerCase().includes(query)) ||
                                (p.tipo && p.tipo.toLowerCase().includes(query))
                            );
                        });
                    }

                    // 2. Ordinamento dinamico
                    return filtered.sort((a, b) => {
                        let aVal, bVal;
                        switch (this.sortCol) {
                            case 'user':
                                aVal = a.user?.name || '';
                                bVal = b.user?.name || '';
                                break;
                            case 'numero_patente':
                                aVal = a.numero_patente || '';
                                bVal = b.numero_patente || '';
                                break;
                            case 'tipo':
                                aVal = a.tipo || '';
                                bVal = b.tipo || '';
                                break;
                            case 'data_scadenza':
                                aVal = a.data_scadenza ? new Date(a.data_scadenza) : new Date(0);
                                bVal = b.data_scadenza ? new Date(b.data_scadenza) : new Date(0);
                                break;
                            default:
                                return 0;
                        }
                        if (aVal < bVal) return this.sortAsc ? -1 : 1;
                        if (aVal > bVal) return this.sortAsc ? 1 : -1;
                        return 0;
                    });
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr.split('T')[0]);
                    return isNaN(d) ? dateStr : d.toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' });
                },

                openCreateModal() { 
                    // Resetta le regole di default ogni volta che apri la finestra
                    this.createNotifCustom = false;
                    this.createNotifRules = [{ id: Date.now(), days_before: 15, channel: 'email' }];
                    this.showCreateModal = true; 
                },
                closeCreateModal() { this.isClosingCreate = true; setTimeout(() => { this.showCreateModal = false; this.isClosingCreate = false; }, 500); },
                
                openEditModal(p) { 
                    this.editPatente = JSON.parse(JSON.stringify(p)); 
                    if(this.editPatente.data_rilascio) this.editPatente.data_rilascio = this.editPatente.data_rilascio.split('T')[0];
                    if(this.editPatente.data_scadenza) this.editPatente.data_scadenza = this.editPatente.data_scadenza.split('T')[0];
                    if(!this.editPatente.user) this.editPatente.user = { id: '' };

                    // --- STAMPIAMO I DATI GREZZI NELLA CONSOLE PER SICUREZZA ---
                    // console.log("🔍 Dati grezzi dal DB per questa spesa:", this.editSub.notification_override);

                    let rawRules = this.editPatente.notification_override;
                    let parsedRules = [];

                    try {
                        // 1. Se è una stringa, la trasformiamo (continuando a sbucciarla se serve)
                        while (typeof rawRules === 'string') {
                            if (rawRules === 'null' || rawRules.trim() === '' || rawRules === '[]') {
                                rawRules = null;
                                break;
                            }
                            rawRules = JSON.parse(rawRules);
                        }

                        // 2. Se è diventato un Array, perfetto!
                        if (Array.isArray(rawRules)) {
                            parsedRules = rawRules;
                        } 
                        // 3. Se Laravel lo ha trasformato in un "Oggetto" (es. {"0": {days_before: 1}})
                        else if (rawRules !== null && typeof rawRules === 'object') {
                            parsedRules = Object.values(rawRules);
                        }
                    } catch (e) {
                        // console.error("❌ Errore durante l'estrazione delle regole:", e);
                    }

                    // --- ACCENDIAMO L'INTERRUTTORE ---
                    if (parsedRules.length > 0) {
                        this.editNotifCustom = true; // ACCESO!
                        
                        this.editNotifRules = parsedRules.map((rule, index) => {
                            return { 
                                id: rule.id || (Date.now() + index), 
                                days_before: parseInt(rule.days_before) || 0, 
                                channel: rule.channel || 'email' 
                            };
                        });
                        // console.log("✅ Regole caricate con successo:", this.editNotifRules);
                    } else {
                        // SPENTO
                        this.editNotifCustom = false;
                        this.editNotifRules = [{ id: Date.now(), days_before: 15, channel: 'email' }];
                        // console.log("⚠️ Nessuna regola trovata, uso i valori di default.");
                    }

                    this.showEditModal = true; 
                },
                closeEditModal() { this.isClosingEdit = true; setTimeout(() => { this.showEditModal = false; this.isClosingEdit = false; }, 500); },
                
                openDeleteModal(p) { this.deletePatente = p; this.showDeleteModal = true; },
                closeDeleteModal() { this.isClosingDelete = true; setTimeout(() => { this.showDeleteModal = false; this.isClosingDelete = false; }, 500); },
                
                closeBulkDeleteModal() { this.isClosingBulkDelete = true; setTimeout(() => { this.showBulkDeleteModal = false; this.isClosingBulkDelete = false; }, 500); },
                
                closeSuccessModal() { this.isClosingSuccess = true; setTimeout(() => { this.showSuccessModal = false; this.isClosingSuccess = false; }, 500); },
                showSuccess(message, type = 'success') {
                    this.successMessage = message; this.successType = type; this.showSuccessModal = true;
                    setTimeout(() => { if(this.showSuccessModal) this.closeSuccessModal(); }, 3000);
                },

                async refreshData() {
                    try {
                        const res = await fetch('{{ route("patenti.index") }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.patenti = Array.isArray(data.patenti) ? data.patenti : Object.values(data.patenti || {});
                        }
                    } catch (error) { console.error('Errore aggiornamento dati:', error); }
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
                            this.closeCreateModal(); this.closeEditModal();
                            if(method === 'POST') form.reset();
                            await this.refreshData(); 
                            this.showSuccess(data.message || 'Operazione completata!', 'success');
                        } else {
                            this.showSuccess(data.message || 'Errore di validazione', 'error');
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
                            this.closeDeleteModal(); await this.refreshData();
                            this.showSuccess(data.message || 'Eliminata con successo!', 'success');
                        } else {
                            this.closeDeleteModal();
                            this.showSuccess(data.message || "Errore eliminazione", 'error');
                        }
                    } catch (error) {
                        this.closeDeleteModal(); this.showSuccess('Errore di connessione', 'error');
                    } finally { this.isSubmitting = false; }
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
        .btn-add-custom { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; font-weight: 600; font-size: 1rem; padding: 0.875rem 1.75rem; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 4px 14px -2px rgba(37, 99, 235, 0.4); transition: all 0.25s ease; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; }
        .btn-add-custom:hover { background: linear-gradient(135deg, #db2777 0%, #c02669 100%); box-shadow: 0 8px 20px -2px rgba(37, 99, 235, 0.5); transform: translateY(-2px); }
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
        .btn-card-compact { padding: 0.625rem 1.25rem !important; font-size: 0.875rem; min-width: auto; flex: 0 0 auto; }
        .btn-card-action { font-weight: 600; border-radius: 0.75rem; border: none; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; }
        .btn-card-edit { background-color: #3b82f6 !important; color: white !important; }
        .btn-card-delete { background-color: #ef4444 !important; color: white !important; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0); }
        .modal-active .modal-backdrop { animation: fadeIn 0.5s forwards; }
        .modal-out .modal-backdrop { animation: fadeOut 0.5s forwards; }
        .modal-container { position: relative; width: 100%; max-width: 28rem; z-index: 1000; }
        .modal-content-wrapper { background: white; border-radius: 1rem; overflow: hidden; transform: scale(0); }
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.5s forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.5s forwards; }
        @keyframes fadeIn { 0% { background: rgba(0, 0, 0, 0); } 100% { background: rgba(0, 0, 0, 0.7); } }
        @keyframes fadeOut { 0% { background: rgba(0, 0, 0, 0.7); } 100% { background: rgba(0, 0, 0, 0); } }
        @keyframes blowUpModal { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }
        .modal-header { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 1.25rem; font-weight: 700; color: white; margin: 0; }
        .modal-close { color: white; background: transparent; border: none; cursor: pointer; transition: all 0.15s; }
        .modal-close:hover { transform: rotate(90deg); }
        .modal-body { padding: 1.5rem; }
        .modal-footer { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 0.75rem; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .form-input { display: block; width: 100%; padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; }
        .btn-primary { padding: 0.625rem 1.5rem; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; font-weight: 600; border-radius: 0.5rem; }
        .btn-secondary { padding: 0.625rem 1.25rem; background: #f3f4f6; color: #374151; font-weight: 500; border-radius: 0.5rem; }
        .btn-danger { padding: 0.625rem 1.5rem; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; font-weight: 600; border-radius: 0.5rem; }
    </style>
</x-app-layout>