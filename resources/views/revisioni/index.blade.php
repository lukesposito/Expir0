<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Revisioni
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4" 
             x-data="revisioniManager()"
             x-init="init()">

            <div class="mb-6">
                <div class="mobile-only-search">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"></div>
                        <input type="text" 
                               x-model="searchQuery"
                               placeholder="Cerca per targa, marca, centro o esito..." 
                               class="w-full pl-11 pr-10 py-3.5 bg-white border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition text-base shadow-sm">
                        <div x-show="searchQuery" 
                             @click="searchQuery = ''"
                             class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1" x-show="searchQuery">
                        <span x-text="filteredRevisioni.length"></span> risultati trovati
                    </p>
                </div>
                
                <div class="desktop-only-search">
                    <div class="bg-white rounded-xl shadow-md p-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"></div>
                            <input type="text" 
                                   x-model="searchQuery"
                                   placeholder="Cerca per targa, marca, modello, centro revisione o esito..." 
                                   class="w-full pl-11 pr-10 py-3 bg-gray-50 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-100 transition">
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
                                <span class="font-semibold" x-text="filteredRevisioni.length"></span> risultati trovati
                            </p>
                            <button @click="searchQuery = ''" 
                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Cancella ricerca
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6 flex justify-center lg:justify-start">
                <button @click="openCreateModal()" class="btn-add-custom">
                    <svg class="inline-block h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Aggiungi Revisione</span>
                </button>
            </div>

            <div class="desktop-only bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veicolo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ultima Revisione</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prossima Revisione</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giorni</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Esito</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Centro</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(r, index) in filteredRevisioni" :key="r.id || index">
                                <tr class="hover:bg-gray-50 transition" :class="{ 'bg-red-50': r.giorni_rimanenti <= 30 }">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900 font-mono uppercase" x-text="r.vehicle_targa"></div>
                                        <div class="text-sm text-gray-500 capitalize">
                                            <span x-text="r.vehicle_marca"></span> <span x-text="r.vehicle_modello"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700" x-text="r.data_ultima_revisione_formatted || '—'"></td>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-semibold" x-text="r.data_prossima_revisione_formatted || '—'"></td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold" 
                                              :class="r.giorni_rimanenti <= 30 ? 'text-red-600' : 'text-green-600'">
                                            <span x-text="r.giorni_rimanenti"></span> gg
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                'bg-green-100 text-green-800': r.esito === 'positivo',
                                                'bg-red-100 text-red-800': r.esito === 'negativo',
                                                'bg-gray-100 text-gray-800': r.esito === 'da_fare'
                                              }">
                                            <span x-text="r.esito === 'positivo' ? 'Positivo' : (r.esito === 'negativo' ? 'Negativo' : 'Da fare')"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700" x-text="r.centro_revisione || '—'"></td>
                                    <td class="px-6 py-5 space-x-3">
                                        <button @click="openEditModal(r)" class="btn-table-action btn-table-edit">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Modifica
                                        </button>
                                        <button @click="openDeleteModal(r)" class="btn-table-action btn-table-delete">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Elimina
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="filteredRevisioni.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="text-gray-500 font-medium">Nessuna revisione trovata</p>
                                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mobile-only space-y-4 px-4">
                <template x-for="(r, index) in filteredRevisioni" :key="index">
                    <div class="bg-white rounded-2xl shadow-xl border overflow-hidden"
                         :class="r.giorni_rimanenti <= 30 ? 'border-red-300' : 'border-gray-200'">
                        <div class="relative bg-gradient-to-br from-green-500 via-green-600 to-green-700 px-5 py-6">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full -ml-12 -mb-12"></div>
                            
                            <div class="relative flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-2xl font-black text-white tracking-tight mb-1.5 uppercase font-mono" x-text="r.vehicle_targa"></h3>
                                    <p class="text-base text-white opacity-90 capitalize">
                                        <span x-text="r.vehicle_marca"></span> <span x-text="r.vehicle_modello"></span>
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl px-3.5 py-2 border border-white border-opacity-30">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-lg font-bold text-white" x-text="r.giorni_rimanenti + ' gg'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6 bg-gradient-to-b from-gray-50 to-white">
                            <div class="space-y-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Ultima Revisione</p>
                                        <p class="text-lg font-bold text-gray-900" x-text="r.data_ultima_revisione_formatted || '—'"></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Prossima Revisione</p>
                                        <p class="text-lg font-bold" 
                                           :class="r.giorni_rimanenti <= 30 ? 'text-red-600' : 'text-gray-900'"
                                           x-text="r.data_prossima_revisione_formatted || '—'"></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center"
                                         :class="{
                                            'bg-gradient-to-br from-green-100 to-green-200': r.esito === 'positivo',
                                            'bg-gradient-to-br from-red-100 to-red-200': r.esito === 'negativo',
                                            'bg-gradient-to-br from-gray-100 to-gray-200': r.esito === 'da_fare'
                                         }">
                                        <svg class="h-6 w-6" 
                                             :class="{
                                                'text-green-600': r.esito === 'positivo',
                                                'text-red-600': r.esito === 'negativo',
                                                'text-gray-600': r.esito === 'da_fare'
                                             }" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Esito</p>
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold"
                                              :class="{
                                                'bg-green-100 text-green-800': r.esito === 'positivo',
                                                'bg-red-100 text-red-800': r.esito === 'negativo',
                                                'bg-gray-100 text-gray-800': r.esito === 'da_fare'
                                              }">
                                            <span x-text="r.esito === 'positivo' ? 'Positivo' : (r.esito === 'negativo' ? 'Negativo' : 'Da fare')"></span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Centro Revisione</p>
                                        <p class="text-lg font-bold text-gray-900" x-text="r.centro_revisione || '—'"></p>
                                    </div>
                                </div>
                                
                                <template x-if="r.importo > 0">
                                    <div class="flex items-center bg-gray-100 rounded-lg p-3">
                                        <svg class="h-6 w-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-xs font-medium text-gray-500">Importo</p>
                                            <p class="text-xl font-bold text-gray-900">€<span x-text="r.importo_formatted"></span></p>
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="r.note">
                                    <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                                        <p class="font-medium text-blue-900 mb-1">📝 Note</p>
                                        <p x-text="r.note"></p>
                                    </div>
                                </template>
                            </div>
                            
                            <div class="mt-6 pt-5 border-t border-gray-200 flex justify-center gap-3 pb-2">
                                <button @click="openEditModal(r)"
                                        class="btn-card-action btn-card-edit btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Modifica</span>
                                </button>
                                
                                <button @click="openDeleteModal(r)"
                                        class="btn-card-action btn-card-delete btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Elimina</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div x-show="filteredRevisioni.length === 0" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">Nessuna revisione trovata</p>
                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                </div>
            </div>

            <div x-show="showCreateModal" 
                 x-cloak
                 @keydown.escape.window="closeCreateModal()"
                 class="modal-overlay"
                 :class="{ 'modal-active': showCreateModal && !isClosingCreate, 'modal-out': isClosingCreate }">
                
                <div @click="closeCreateModal()" class="modal-backdrop"></div>
                
                <div class="modal-container modal-container-large">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Nuova Revisione</h3>
                            <button @click="closeCreateModal()" class="modal-close">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitForm($event, '{{ route('revisioni.store') }}', 'POST')" class="modal-body">
                            @csrf
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Veicolo *</label>
                                    <select name="vehicle_id" required class="form-input">
                                        <option value="">Seleziona veicolo</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}">{{ $v->targa }} - {{ $v->marca }} {{ $v->modello }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Data Ultima Revisione *</label>
                                    <input type="date" name="data_ultima_revisione" required class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Data Prossima Revisione *</label>
                                    <input type="date" name="data_prossima_revisione" required class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Esito *</label>
                                    <select name="esito" required class="form-input">
                                        <option value="da_fare">Da fare</option>
                                        <option value="positivo">Positivo</option>
                                        <option value="negativo">Negativo</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Centro Revisione</label>
                                    <input type="text" name="centro_revisione" class="form-input" placeholder="Es: Officina Rossi">
                                </div>

                                <div>
                                    <label class="form-label">Importo (€)</label>
                                    <input type="number" name="importo" step="0.01" min="0" class="form-input" placeholder="0.00">
                                </div>
                                
                                <div>
                                    <label class="form-label">Note</label>
                                    <textarea name="note" rows="3" class="form-input" placeholder="Eventuali note..."></textarea>
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

            <div x-show="showEditModal" 
                 x-cloak
                 @keydown.escape.window="closeEditModal()"
                 class="modal-overlay"
                 :class="{ 'modal-active': showEditModal && !isClosingEdit, 'modal-out': isClosingEdit }">
                
                <div @click="closeEditModal()" class="modal-backdrop"></div>
                
                <div class="modal-container modal-container-large">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Modifica Revisione</h3>
                            <button @click="closeEditModal()" class="modal-close">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitForm($event, `/revisioni/${editRevisione.id}`, 'PUT')" class="modal-body">
                            @csrf
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Veicolo *</label>
                                    <select name="vehicle_id" x-model="editRevisione.vehicle_id" required class="form-input">
                                        <option value="">Seleziona veicolo</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}">{{ $v->targa }} - {{ $v->marca }} {{ $v->modello }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Data Ultima Revisione *</label>
                                    <input type="date" name="data_ultima_revisione" x-model="editRevisione.data_ultima_revisione" required class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Data Prossima Revisione *</label>
                                    <input type="date" name="data_prossima_revisione" x-model="editRevisione.data_prossima_revisione" required class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Esito *</label>
                                    <select name="esito" x-model="editRevisione.esito" required class="form-input">
                                        <option value="da_fare">Da fare</option>
                                        <option value="positivo">Positivo</option>
                                        <option value="negativo">Negativo</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Centro Revisione</label>
                                    <input type="text" name="centro_revisione" x-model="editRevisione.centro_revisione" class="form-input" placeholder="Es: Officina Rossi">
                                </div>

                                <div>
                                    <label class="form-label">Importo (€)</label>
                                    <input type="number" name="importo" x-model="editRevisione.importo" step="0.01" min="0" class="form-input" placeholder="0.00">
                                </div>
                                
                                <div>
                                    <label class="form-label">Note</label>
                                    <textarea name="note" x-model="editRevisione.note" rows="3" class="form-input"></textarea>
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

            <div x-show="showDeleteModal" 
                 x-cloak
                 @keydown.escape.window="closeDeleteModal()"
                 class="modal-overlay"
                 :class="{ 'modal-active': showDeleteModal && !isClosingDelete, 'modal-out': isClosingDelete }">
                
                <div @click="closeDeleteModal()" class="modal-backdrop"></div>
                
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="p-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Elimina Revisione</h3>
                            <p class="text-sm text-gray-600 text-center mb-1">
                                Sei sicuro di voler eliminare la revisione del veicolo 
                            </p>
                            <p class="text-center mb-4">
                                <strong class="text-gray-900" x-text="(deleteRevisione.vehicle_marca || '') + ' ' + (deleteRevisione.vehicle_modello || '')"></strong>?
                            </p>
                            <p class="text-xs text-red-600 text-center bg-red-50 py-2 px-4 rounded-lg">
                                ⚠️ Verranno eliminate anche tutte le scadenze associate
                            </p>
                        </div>
                        
                        <form @submit.prevent="submitDelete(`/revisioni/${deleteRevisione.id}`)" class="px-6 pb-6">
                            @csrf
                            <div class="flex justify-center space-x-3 mb-4">
                                <button type="button" @click="closeDeleteModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-danger" :disabled="isSubmitting">Elimina</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-show="showSuccessModal" 
                 x-cloak
                 @keydown.escape.window="closeSuccessModal()"
                 class="modal-overlay"
                 :class="{ 'modal-active': showSuccessModal && !isClosingSuccess, 'modal-out': isClosingSuccess }">
                
                <div @click="closeSuccessModal()" class="modal-backdrop"></div>
                
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="p-8 text-center">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full mb-6"
                                 :class="{
                                    'bg-green-100': successType === 'success',
                                    'bg-blue-100': successType === 'info',
                                    'bg-red-100': successType === 'error'
                                 }">
                                <svg x-show="successType === 'success'" class="h-10 w-10 text-green-600 animate-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg x-show="successType === 'info'" class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="successType === 'error'" class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            
                            <h3 class="text-2xl font-bold text-gray-900 mb-3"
                                :class="{
                                    'text-green-900': successType === 'success',
                                    'text-blue-900': successType === 'info',
                                    'text-red-900': successType === 'error'
                                }">
                                <span x-show="successType === 'success'">Operazione completata!</span>
                                <span x-show="successType === 'info'">Informazione</span>
                                <span x-show="successType === 'error'">Errore</span>
                            </h3>
                            
                            <p class="text-gray-600 text-lg mb-6" x-text="successMessage"></p>
                            
                            <button @click="closeSuccessModal()" 
                                    class="px-6 py-2.5 rounded-lg font-semibold transition duration-150 shadow-lg hover:shadow-xl transform hover:scale-105"
                                    :class="{
                                        'bg-green-600 hover:bg-green-700 text-white': successType === 'success',
                                        'bg-blue-600 hover:bg-blue-700 text-white': successType === 'info',
                                        'bg-red-600 hover:bg-red-700 text-white': successType === 'error'
                                    }">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function revisioniManager() {
            return {
                showCreateModal: false, showEditModal: false, showDeleteModal: false, showSuccessModal: false,
                isClosingCreate: false, isClosingEdit: false, isClosingDelete: false, isClosingSuccess: false,
                successMessage: '', successType: 'success', isSubmitting: false,
                
                editRevisione: {},
                deleteRevisione: {},
                searchQuery: '',
                
                revisioni: @json($revisioni),

                init() {
                    @if(session('success')) this.showSuccess("{{ session('success') }}", 'success'); @endif
                    @if(session('error')) this.showSuccess("{{ session('error') }}", 'error'); @endif
                },

                get filteredRevisioni() {
                    if (!this.searchQuery) return this.revisioni;
                    const query = this.searchQuery.toLowerCase();
                    return this.revisioni.filter(r => {
                        return (
                            (r.vehicle_targa && r.vehicle_targa.toLowerCase().includes(query)) ||
                            (r.vehicle_marca && r.vehicle_marca.toLowerCase().includes(query)) ||
                            (r.vehicle_modello && r.vehicle_modello.toLowerCase().includes(query)) ||
                            (r.centro_revisione && r.centro_revisione.toLowerCase().includes(query)) ||
                            (r.esito && r.esito.toLowerCase().includes(query))
                        );
                    });
                },

                // GESTIONE MODALI
                openCreateModal() { this.showCreateModal = true; },
                closeCreateModal() { this.isClosingCreate = true; setTimeout(() => { this.showCreateModal = false; this.isClosingCreate = false; }, 500); },
                
                openEditModal(r) { 
                    this.editRevisione = JSON.parse(JSON.stringify(r)); 
                    
                    // Taglia il timestamp se presente ("YYYY-MM-DDT00:00:00.000000Z") per far funzionare l'input type="date"
                    if(this.editRevisione.data_ultima_revisione && this.editRevisione.data_ultima_revisione.includes('T')) {
                        this.editRevisione.data_ultima_revisione = this.editRevisione.data_ultima_revisione.split('T')[0];
                    }
                    if(this.editRevisione.data_prossima_revisione && this.editRevisione.data_prossima_revisione.includes('T')) {
                        this.editRevisione.data_prossima_revisione = this.editRevisione.data_prossima_revisione.split('T')[0];
                    }
                    
                    this.showEditModal = true; 
                },
                closeEditModal() { this.isClosingEdit = true; setTimeout(() => { this.showEditModal = false; this.isClosingEdit = false; }, 500); },
                
                openDeleteModal(r) { this.deleteRevisione = r; this.showDeleteModal = true; },
                closeDeleteModal() { this.isClosingDelete = true; setTimeout(() => { this.showDeleteModal = false; this.isClosingDelete = false; }, 500); },
                
                closeSuccessModal() { this.isClosingSuccess = true; setTimeout(() => { this.showSuccessModal = false; this.isClosingSuccess = false; }, 500); },
                showSuccess(message, type = 'success') {
                    this.successMessage = message; this.successType = type; this.showSuccessModal = true;
                    setTimeout(() => { if(this.showSuccessModal) this.closeSuccessModal(); }, 3000);
                },

                // AGGIORNAMENTO DATI IN BACKGROUND
                async refreshData() {
                    try {
                        const res = await fetch('{{ route("revisioni.index") }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.revisioni = data.revisioni;
                        }
                    } catch (error) {
                        console.error('Errore aggiornamento dati:', error);
                    }
                },

                // SUBMIT POST/PUT ASINCRONO
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
                            
                            await this.refreshData(); // RICARICA LA TABELLA
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

                // DELETE ASINCRONO
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
                            await this.refreshData(); // RICARICA LA TABELLA
                            this.showSuccess(data.message || 'Revisione eliminata con successo!', 'success');
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

        /* Responsive Visibility */
        .desktop-only { display: block; }
        .mobile-only { display: none; }
        @media (max-width: 1023px) { .desktop-only { display: none; } .mobile-only { display: block; } }

        /* Barra ricerca responsive */
        .mobile-only-search { display: block; }
        .desktop-only-search { display: none; }
        @media (min-width: 1024px) { .mobile-only-search { display: none !important; } .desktop-only-search { display: block !important; } }

        /* Bottone Aggiungi */
        .btn-add-custom {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white; font-weight: 600; font-size: 1rem; padding: 0.875rem 1.75rem; border-radius: 0.75rem; border: none; cursor: pointer;
            box-shadow: 0 4px 14px -2px rgba(37, 99, 235, 0.4); transition: all 0.25s ease; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap;
        }
        .btn-add-custom:hover { background: linear-gradient(135deg, #16a34a 0%, #008622 100%); box-shadow: 0 8px 20px -2px rgba(37, 99, 235, 0.5); transform: translateY(-2px); }
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

        .modal-header { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 1.25rem; font-weight: 700; color: white; margin: 0; }
        .modal-close { color: white; background: transparent; border: none; cursor: pointer; padding: 0.25rem; transition: all 0.15s; border-radius: 0.375rem; }
        .modal-close:hover { background: rgba(255, 255, 255, 0.1); transform: rotate(90deg); }

        .modal-body { max-height: calc(90vh - 200px); overflow-y: auto; padding: 1.5rem; }
        @media (max-width: 640px) { .modal-container, .modal-container-large { width: 95%; max-width: none; margin: 1rem auto; } .modal-body { max-height: calc(100vh - 250px); padding: 1rem; } }

        .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem; background-color: #f9fafb; border-top: 1px solid #e5e7eb; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .form-input { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; line-height: 1.25rem; transition: all 0.15s; }
        .form-input:focus { outline: none; border-color: #3b82f6; ring: 2px; ring-color: rgba(59, 130, 246, 0.3); }

        .btn-primary { padding: 0.625rem 1.25rem; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; font-weight: 600; border-radius: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2); }
        .btn-primary:hover { background: linear-gradient(135deg, #16a34a 0%, #066328 100%); box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3); transform: translateY(-1px); }
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