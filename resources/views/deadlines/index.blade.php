<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Scadenze
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4" 
             x-data="{ 
                showCreateModal: false,
                showEditModal: false,
                showDeleteModal: false,
                showSuccessModal: false,
                isClosingCreate: false,
                isClosingEdit: false,
                isClosingDelete: false,
                isClosingSuccess: false,
                successMessage: '',
                successType: '',
                editDeadline: {},
                deleteDeadline: {},
                tipoScadenza: '',
                searchQuery: '',
                deadlines: {{ $deadlines->toJson() }},
                get filteredDeadlines() {
                    if (!this.searchQuery) {
                        return this.deadlines;
                    }
                    
                    const query = this.searchQuery.toLowerCase();
                    return this.deadlines.filter(deadline => {
                        const tipo = deadline.tipo ? deadline.tipo.toLowerCase() : '';
                        const targa = deadline.vehicle_targa ? deadline.vehicle_targa.toLowerCase() : '';
                        const marca = deadline.vehicle_marca ? deadline.vehicle_marca.toLowerCase() : '';
                        const modello = deadline.vehicle_modello ? deadline.vehicle_modello.toLowerCase() : '';  // AGGIUNTO
                        const persona = deadline.user_name ? deadline.user_name.toLowerCase() : '';
                        
                        return tipo.includes(query) || targa.includes(query) || marca.includes(query) || modello.includes(query) || persona.includes(query);
                    });
                },
                
                closeCreateModal() {
                    this.isClosingCreate = true;
                    setTimeout(() => {
                        this.showCreateModal = false;
                        this.isClosingCreate = false;
                        this.tipoScadenza = '';
                    }, 500);
                },
                
                closeEditModal() {
                    this.isClosingEdit = true;
                    setTimeout(() => {
                        this.showEditModal = false;
                        this.isClosingEdit = false;
                    }, 500);
                },
                
                closeDeleteModal() {
                    this.isClosingDelete = true;
                    setTimeout(() => {
                        this.showDeleteModal = false;
                        this.isClosingDelete = false;
                    }, 500);
                },
                
                closeSuccessModal() {
                    this.isClosingSuccess = true;
                    setTimeout(() => {
                        this.showSuccessModal = false;
                        this.isClosingSuccess = false;
                    }, 500);
                },
                
                showSuccess(message, type = 'success') {
                    this.successMessage = message;
                    this.successType = type;
                    this.showSuccessModal = true;
                    setTimeout(() => {
                        if(this.showSuccessModal) {
                            this.closeSuccessModal();
                        }
                    }, 3000);
                }
             }"
             x-init="
                @if(session('success'))
                    showSuccess('{{ session('success') }}', 'success');
                @endif
             ">

            <!-- BARRA DI RICERCA -->
            <div class="mb-6">
                <!-- Mobile -->
                <div class="mobile-only-search">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <!-- <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg> -->
                        </div>
                        <input type="text" 
                               x-model="searchQuery"
                               placeholder="Cerca per tipo, targa o persona..." 
                               class="w-full pl-11 pr-10 py-3.5 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition text-base shadow-sm">
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
                
                <!-- Desktop -->
                <div class="desktop-only-search">
                    <div class="bg-white rounded-xl shadow-md p-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <!-- <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg> -->
                            </div>
                            <input type="text" 
                                   x-model="searchQuery"
                                   placeholder="Cerca per tipo, targa, marca o persona..." 
                                   class="w-full pl-11 pr-10 py-3 bg-gray-50 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 transition">
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

            <!-- BOTTONE AGGIUNGI -->
            <div class="mb-6 flex justify-center lg:justify-start">
                <button @click="showCreateModal = true" class="btn-add-custom">
                    <svg class="inline-block h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Aggiungi Scadenza</span>
                </button>
            </div>

            <!-- TABELLA DESKTOP -->
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(d, index) in filteredDeadlines" :key="index">
                                <tr class="hover:bg-gray-50 transition" :class="{ 'bg-red-50': d.giorni_rimanenti <= 10 }">
                                    <td class="px-6 py-4">
                                        <template x-if="d.tipo === 'patente' && d.user_name">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">Patente</div>
                                                <div class="text-sm text-gray-500" x-text="d.user_name"></div>
                                            </div>
                                        </template>
                                        <template x-if="d.tipo !== 'patente' && d.vehicle_targa">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900" x-text="d.vehicle_targa"></div>
                                                <div class="text-sm text-gray-500">
                                                    <span x-text="d.vehicle_marca"></span> <span x-text="d.vehicle_modello"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!d.vehicle_targa && !d.user_name">
                                            <div class="text-sm text-gray-500">N/D</div>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                'bg-yellow-100 text-yellow-800': d.tipo === 'bollo',
                                                'bg-blue-100 text-blue-800': d.tipo === 'assicurazione',
                                                'bg-purple-100 text-purple-800': d.tipo === 'patente'
                                                'bg-orange-100 text-orange-800': d.tipo === 'revisione'
                                              }">
                                            <span x-text="d.tipo.charAt(0).toUpperCase() + d.tipo.slice(1)"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700" x-text="d.data_scadenza_formatted"></td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900" x-text="'€' + d.importo_formatted"></td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold" 
                                              :class="d.giorni_rimanenti <= 10 ? 'text-red-600' : 'text-green-600'">
                                            <span x-text="d.giorni_rimanenti"></span> gg
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-center gap-3">
                                            <!-- Toggle Switch -->
                                            <label class="switch switch-lg">
                                                <input type="checkbox" 
                                                    :checked="d.pagato"
                                                    @change="
                                                        d.pagato = !d.pagato;
                                                        fetch('/deadlines/' + d.id + '/toggle-pagato', {
                                                            method: 'PATCH',
                                                            headers: {
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                'Content-Type': 'application/json',
                                                                'Accept': 'application/json'
                                                            }
                                                        });
                                                    ">
                                                <span class="slider"></span>
                                            </label>
                                            
                                            <!-- Badge Stato -->
                                            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold shadow-sm"
                                                :class="d.pagato ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : 'bg-gradient-to-r from-red-500 to-red-600 text-white'">
                                                <span x-text="d.pagato ? '✓ PAGATO' : '✗ NON PAGATO'"></span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 space-x-3">
                                        <button @click="editDeadline = d; showEditModal = true; tipoScadenza = d.tipo" 
                                                class="btn-table-action btn-table-edit">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Modifica
                                        </button>
                                        <button @click="deleteDeadline = d; showDeleteModal = true" 
                                                class="btn-table-action btn-table-delete">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Elimina
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Nessun risultato -->
                            <tr x-show="filteredDeadlines.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <!-- <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg> -->
                                    <p class="text-gray-500 font-medium">Nessuna scadenza trovata</p>
                                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CARDS MOBILE -->
            <div class="mobile-only space-y-4 px-4">
                <template x-for="(d, index) in filteredDeadlines" :key="index">
                    <div class="bg-white rounded-2xl shadow-xl border overflow-hidden"
                         :class="d.giorni_rimanenti <= 10 ? 'border-red-300' : 'border-gray-200'">
                        <!-- Header con gradiente dinamico -->
                        <div class="px-5 py-5 relative"
                             :style="`background: linear-gradient(135deg, ${
                                d.tipo === 'bollo' ? '#eab308, #ca8a04' : 
                                d.tipo === 'assicurazione' ? '#3b82f6, #2563eb' : 
                                '#9333ea, #7e22ce'
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
                                    <template x-if="d.vehicle_targa">
                                        <div>
                                            <h3 class="text-2xl font-black text-white tracking-tight mb-1" x-text="d.vehicle_targa"></h3>
                                            <p class="text-base text-white opacity-90">
                                                <span x-text="d.vehicle_marca"></span> <span x-text="d.vehicle_modello"></span>
                                            </p>
                                        </div>
                                    </template>
                                </div>
                                <div class="ml-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white bg-opacity-20 text-white">
                                        <span x-text="d.tipo.charAt(0).toUpperCase() + d.tipo.slice(1)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Body -->
                        <div class="p-6 bg-gradient-to-b from-gray-50 to-white">
                            <div class="space-y-4">
                                <!-- Scadenza e Giorni -->
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
                                           :class="d.giorni_rimanenti <= 10 ? 'text-red-600' : 'text-green-600'">
                                            <span x-text="d.giorni_rimanenti"></span> <span class="text-sm">gg</span>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Importo -->
                                <div class="flex items-center bg-gray-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-xs font-medium text-gray-500">Importo</p>
                                        <p class="text-xl font-bold text-gray-900">€<span x-text="d.importo_formatted"></span></p>
                                    </div>
                                </div>

                                <!-- Stato Pagamento -->
                                <div class="bg-gray-50 rounded-xl p-4 mt-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Stato Pagamento</p>
                                            <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold shadow-md"
                                                :class="d.pagato ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : 'bg-gradient-to-r from-red-500 to-red-600 text-white'">
                                                <span x-text="d.pagato ? '✓ PAGATO' : '✗ NON PAGATO'"></span>
                                            </span>
                                        </div>
                                        <div>
                                            <label class="switch switch-lg">
                                                <input type="checkbox" 
                                                    :checked="d.pagato"
                                                    @change="
                                                        d.pagato = !d.pagato;
                                                        fetch('/deadlines/' + d.id + '/toggle-pagato', {
                                                            method: 'PATCH',
                                                            headers: {
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                'Content-Type': 'application/json',
                                                                'Accept': 'application/json'
                                                            }
                                                        });
                                                    ">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Note (se presenti) -->
                                <template x-if="d.note">
                                    <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                                        <p class="font-medium text-blue-900 mb-1">📝 Note</p>
                                        <p x-text="d.note"></p>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Azioni -->
                            <div class="mt-6 pt-5 border-t border-gray-200 flex justify-center gap-3 pb-2">
                                <button @click="editDeadline = d; showEditModal = true; tipoScadenza = d.tipo"
                                        class="btn-card-action btn-card-edit btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Modifica</span>
                                </button>
                                
                                <button @click="deleteDeadline = d; showDeleteModal = true"
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
                
                <!-- Nessun risultato -->
                <div x-show="filteredDeadlines.length === 0" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <!-- <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg> -->
                    </div>
                    <p class="text-gray-500 font-medium">Nessuna scadenza trovata</p>
                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                </div>
            </div>

            <!-- Modal Crea Scadenza -->
            <div x-show="showCreateModal" 
                 x-cloak
                 @keydown.escape.window="closeCreateModal()"
                 class="modal-overlay"
                 :class="{ 'modal-active': showCreateModal && !isClosingCreate, 'modal-out': isClosingCreate }">
                
                <div @click="closeCreateModal()" class="modal-backdrop"></div>
                
                <div class="modal-container modal-container-large">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Nuova Scadenza</h3>
                            <button @click="closeCreateModal()" class="modal-close">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('deadlines.store') }}" class="modal-body">
                            @csrf
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Tipo Scadenza *</label>
                                    <select name="tipo" x-model="tipoScadenza" required class="form-input">
                                        <option value="">Seleziona tipo</option>
                                        <option value="bollo">Bollo</option>
                                        <option value="assicurazione">Assicurazione</option>
                                        <option value="patente">Patente</option>
                                        <option value="revisione">Revisione</option>
                                    </select>
                                </div>
                                
                                <!-- Select veicolo -->
                                <div x-show="tipoScadenza === 'bollo' || tipoScadenza === 'assicurazione'">
                                    <label class="form-label">Veicolo *</label>
                                    <select name="vehicle_id" class="form-input" :required="tipoScadenza === 'bollo' || tipoScadenza === 'assicurazione'">
                                        <option value="">Seleziona veicolo</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}">{{ $v->targa }} - {{ $v->marca }} {{ $v->modello }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Select utente -->
                                <div x-show="tipoScadenza === 'patente'">
                                    <label class="form-label">Persona *</label>
                                    <select name="user_id" class="form-input" :required="tipoScadenza === 'patente'">
                                        <option value="">Seleziona persona</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="form-label">Data Scadenza *</label>
                                    <input type="date" name="data_scadenza" required class="form-input">
                                </div>
                                
                                <div>
                                    <label class="form-label">Importo (€)</label>
                                    <input type="number" name="importo" step="0.01" class="form-input" placeholder="0.00">
                                </div>

                                <!-- <div>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                            name="pagato" 
                                            value="1"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm font-medium text-gray-700">Segna come pagato</span>
                                    </label>
                                </div> -->
                                
                                <div>
                                    <label class="form-label">Note</label>
                                    <textarea name="note" rows="3" class="form-input" placeholder="Note aggiuntive..."></textarea>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" @click="closeCreateModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-primary">Salva</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Modifica Scadenza -->
            <div x-show="showEditModal" 
                 x-cloak
                 @keydown.escape.window="closeEditModal()"
                 class="modal-overlay"
                 :class="{ 'modal-active': showEditModal && !isClosingEdit, 'modal-out': isClosingEdit }">
                
                <div @click="closeEditModal()" class="modal-backdrop"></div>
                
                <div class="modal-container modal-container-large">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Modifica Scadenza</h3>
                            <button @click="closeEditModal()" class="modal-close">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" :action="`/deadlines/${editDeadline.id}`" class="modal-body">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Tipo Scadenza *</label>
                                    <select name="tipo" x-model="tipoScadenza" required class="form-input">
                                        <option value="bollo">Bollo</option>
                                        <option value="assicurazione">Assicurazione</option>
                                        <option value="patente">Patente</option>
                                        <option value="revisione">Revisione</option>
                                    </select>
                                </div>
                                
                                <!-- Select veicolo -->
                                <div x-show="tipoScadenza === 'bollo' || tipoScadenza === 'assicurazione'">
                                    <label class="form-label">Veicolo *</label>
                                    <select name="vehicle_id" x-model="editDeadline.vehicle_id" class="form-input" :required="tipoScadenza === 'bollo' || tipoScadenza === 'assicurazione'">
                                        <option value="">Seleziona veicolo</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}">{{ $v->targa }} - {{ $v->marca }} {{ $v->modello }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Select utente -->
                                <div x-show="tipoScadenza === 'patente'">
                                    <label class="form-label">Persona *</label>
                                    <select name="user_id" x-model="editDeadline.user_id" class="form-input" :required="tipoScadenza === 'patente'">
                                        <option value="">Seleziona persona</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="form-label">Data Scadenza *</label>
                                    <input type="date" name="data_scadenza" x-model="editDeadline.data_scadenza" required class="form-input">
                                </div>
                                
                                <div>
                                    <label class="form-label">Importo (€)</label>
                                    <input type="number" name="importo" x-model="editDeadline.importo" step="0.01" class="form-input">
                                </div>

                                <!-- <div>
                                    <label class="switch flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                            name="pagato" 
                                            value="1"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="slider round"></span>
                                    </label>
                                </div> -->
                                
                                <div>
                                    <label class="form-label">Note</label>
                                    <textarea name="note" x-model="editDeadline.note" rows="3" class="form-input"></textarea>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" @click="closeEditModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-primary">Salva</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Elimina Scadenza -->
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
                            
                            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Elimina Scadenza</h3>
                            <p class="text-sm text-gray-600 text-center mb-4">
                                Sei sicuro di voler eliminare questa scadenza di tipo 
                                <strong class="text-gray-900" x-text="deleteDeadline.tipo"></strong>?
                            </p>
                        </div>
                        
                        <form method="POST" :action="`/deadlines/${deleteDeadline.id}`" class="px-6 pb-6">
                            @csrf
                            @method('DELETE')
                            
                            <div class="flex justify-center space-x-3 mb-4">
                                <button type="button" @click="closeDeleteModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-danger">Elimina</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Successo -->
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

    <style>
        [x-cloak] { display: none !important; }

        /* Responsive Visibility */
        .desktop-only {
            display: block;
        }

        .mobile-only {
            display: none;
        }

        @media (max-width: 1023px) {
            .desktop-only {
                display: none;
            }
            
            .mobile-only {
                display: block;
            }
        }

        /* Barra ricerca responsive */
        .mobile-only-search {
            display: block;
        }

        .desktop-only-search {
            display: none;
        }

        @media (min-width: 1024px) {
            .mobile-only-search {
                display: none !important;
            }
            
            .desktop-only-search {
                display: block !important;
            }
        }

        /* Bottone Aggiungi */
        .btn-add-custom {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.875rem 1.75rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px -2px rgba(37, 99, 235, 0.4);
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .btn-add-custom:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 8px 20px -2px rgba(37, 99, 235, 0.5);
            transform: translateY(-2px);
        }

        .btn-add-custom:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px -2px rgba(37, 99, 235, 0.4);
        }

        .btn-add-custom svg {
            transition: transform 0.25s ease;
        }

        .btn-add-custom:hover svg {
            transform: rotate(90deg);
        }

        /* Bottoni tabella desktop */
        .btn-table-action {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-table-edit {
            background-color: #3b82f6 !important;
            color: white !important;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }

        .btn-table-edit:hover {
            background-color: #2563eb !important;
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }

        .btn-table-edit svg {
            transition: transform 0.2s ease;
            stroke: white !important;
        }

        .btn-table-edit:hover svg {
            transform: rotate(12deg);
        }

        .btn-table-delete {
            background-color: #ef4444 !important;
            color: white !important;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }

        .btn-table-delete:hover {
            background-color: #dc2626 !important;
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
            transform: translateY(-1px);
        }

        .btn-table-delete svg {
            transition: transform 0.2s ease;
            stroke: white !important;
        }

        .btn-table-delete:hover svg {
            transform: scale(1.1);
        }

        .btn-table-action:active {
            transform: translateY(0);
        }

        /* Bottoni card mobile */
        .btn-card-compact {
            padding: 0.625rem 1.25rem !important;
            font-size: 0.875rem;
            min-width: auto;
            flex: 0 0 auto;
        }

        .btn-card-action {
            font-weight: 600;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-card-action:active {
            transform: translateY(0);
        }

        .btn-card-edit {
            background-color: #3b82f6 !important;
            color: white !important;
        }

        .btn-card-edit:hover {
            background-color: #2563eb !important;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }

        .btn-card-edit svg {
            transition: transform 0.2s ease;
            stroke: white !important;
        }

        .btn-card-edit:hover svg {
            transform: rotate(12deg);
        }

        .btn-card-delete {
            background-color: #ef4444 !important;
            color: white !important;
        }

        .btn-card-delete:hover {
            background-color: #dc2626 !important;
            box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4);
            transform: translateY(-2px);
        }

        .btn-card-delete svg {
            transition: transform 0.2s ease;
            stroke: white !important;
        }

        .btn-card-delete:hover svg {
            transform: scale(1.1);
        }

        /* Toggle Switch moderno stile iOS */
        input[type="checkbox"].sr-only:checked + div {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        }

        input[type="checkbox"].sr-only + div {
            background: #d1d5db;
            transition: all 0.3s ease;
        }

        input[type="checkbox"].sr-only + div::after {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Badge con gradiente */
        .bg-gradient-to-r {
            background-image: linear-gradient(to right, var(--tw-gradient-stops));
        }

        .from-green-500 {
            --tw-gradient-from: #10b981;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(16, 185, 129, 0));
        }

        .to-green-600 {
            --tw-gradient-to: #059669;
        }

        .from-red-500 {
            --tw-gradient-from: #ef4444;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(239, 68, 68, 0));
        }

        .to-red-600 {
            --tw-gradient-to: #dc2626;
        }

        /* Toggle Switch Base */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        input:checked + .slider {
            background-color: #10b981;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #10b981;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Toggle Switch Large (per mobile) */
        .switch-lg {
            width: 60px;
            height: 30px;
        }

        .switch-lg .slider:before {
            height: 24px;
            width: 24px;
        }

        .switch-lg input:checked + .slider:before {
            transform: translateX(30px);
        }

        /* Hover effects */
        .slider:hover {
            opacity: 0.9;
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0);
        }

        .modal-active .modal-backdrop {
            animation: fadeIn 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards;
        }

        .modal-out .modal-backdrop {
            animation: fadeOut 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards;
        }

        .modal-container {
            position: relative;
            width: 100%;
            max-width: 28rem;
            z-index: 1000;
        }

        .modal-container-large {
            max-width: 36rem;
        }

        .modal-content-wrapper {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transform: scale(0);
        }

        .modal-active .modal-content-wrapper {
            animation: blowUpModal 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards;
        }

        .modal-out .modal-content-wrapper {
            animation: blowUpModalTwo 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards;
        }

        @keyframes fadeIn {
            0% { background: rgba(0, 0, 0, 0); }
            100% { background: rgba(0, 0, 0, 0.7); }
        }

        @keyframes fadeOut {
            0% { background: rgba(0, 0, 0, 0.7); }
            100% { background: rgba(0, 0, 0, 0); }
        }

        @keyframes blowUpModal {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        @keyframes blowUpModalTwo {
            0% { 
                transform: scale(1);
                opacity: 1;
            }
            100% { 
                transform: scale(0);
                opacity: 0;
            }
        }

        @keyframes checkmark {
            0% {
                stroke-dashoffset: 50;
                stroke-dasharray: 50;
            }
            100% {
                stroke-dashoffset: 0;
                stroke-dasharray: 50;
            }
        }

        .animate-check {
            stroke-dashoffset: 50;
            stroke-dasharray: 50;
            animation: checkmark 0.6s ease-in-out forwards;
        }

        @keyframes pulse-success {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .modal-active .modal-content-wrapper svg {
            animation: pulse-success 0.6s ease-in-out;
        }

        .modal-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            margin: 0;
        }

        .modal-close {
            color: white;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            transition: all 0.15s;
            border-radius: 0.375rem;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: #111827;
            background-color: #fff;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            padding: 0.625rem 1.5rem;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            transform: scale(1.05);
        }

        .btn-secondary {
            padding: 0.625rem 1.25rem;
            background: #f3f4f6;
            color: #374151;
            font-weight: 500;
            font-size: 0.875rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-danger {
            padding: 0.625rem 1.5rem;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            transform: scale(1.05);
        }
    </style>
</x-app-layout>