<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Patenti
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
                editPatente: {},
                deletePatente: {},
                searchQuery: '',
                patenti: {{ $patenti->toJson() }},
                
                get filteredPatenti() {
                    if (!this.searchQuery) {
                        return this.patenti;
                    }
                    
                    const query = this.searchQuery.toLowerCase();
                    return this.patenti.filter(patente => {
                        return (
                            (patente.user && patente.user.name && patente.user.name.toLowerCase().includes(query)) ||
                            (patente.numero_patente && patente.numero_patente.toLowerCase().includes(query)) ||
                            (patente.tipo && patente.tipo.toLowerCase().includes(query))
                        );
                    });
                },
                
                closeCreateModal() {
                    this.isClosingCreate = true;
                    setTimeout(() => {
                        this.showCreateModal = false;
                        this.isClosingCreate = false;
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
                    // Auto-chiudi dopo 3 secondi
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
                               placeholder="Cerca per numero, tipo o persona..." 
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
                        <span x-text="filteredPatenti.length"></span> risultati trovati
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
                                   placeholder="Cerca per numero patente, tipo o persona..." 
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
                                <span class="font-semibold" x-text="filteredPatenti.length"></span> risultati trovati
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
                    <span>Aggiungi Patente</span>
                </button>
            </div>

            <!-- TABELLA DESKTOP -->
            <div class="desktop-only bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persona</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numero Patente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Rilascio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scadenze</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(p, index) in filteredPatenti" :key="index">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-900" x-text="p.user.name"></td>
                                    <td class="px-6 py-4 text-gray-700 font-mono" x-text="p.numero_patente"></td>
                                    <td class="px-6 py-4 text-gray-700" x-text="p.tipo || '—'"></td>
                                    <td class="px-6 py-4 text-gray-700" x-text="p.data_rilascio_formatted || '—'"></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="p.deadlines_count"></span>
                                    </td>
                                    <td class="px-6 py-5 space-x-3">
                                        <button @click="editPatente = p; showEditModal = true" 
                                                class="btn-table-action btn-table-edit">
                                            <svg class="h-4 w-4 mr-1.5" style="stroke: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Modifica
                                        </button>
                                        <button @click="deletePatente = p; showDeleteModal = true" 
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
                            <tr x-show="filteredPatenti.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <!-- <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg> -->
                                    <p class="text-gray-500 font-medium">Nessuna patente trovata</p>
                                    <p class="text-sm text-gray-400 mt-1">Prova con una ricerca diversa</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CARDS MOBILE -->
            <div class="mobile-only space-y-4 px-4">
                <template x-for="(p, index) in filteredPatenti" :key="index">
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                        <!-- Header -->
                        <div class="relative bg-gradient-to-br from-purple-500 via-purple-600 to-purple-700 px-5 py-6">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-16 -mt-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-5 rounded-full -ml-12 -mb-12"></div>
                            
                            <div class="relative flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-2xl font-black text-white tracking-tight mb-1.5" x-text="p.user.name"></h3>
                                    <p class="text-base text-white opacity-90 font-mono" x-text="p.numero_patente"></p>
                                </div>
                                <div class="ml-4">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl px-3.5 py-2 border border-white border-opacity-30">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-lg font-bold text-white" x-text="p.deadlines_count"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Body -->
                        <div class="p-6 bg-gradient-to-b from-gray-50 to-white">
                            <div class="space-y-5">
                                <!-- Numero Patente -->
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Numero Patente</p>
                                        <p class="text-lg font-bold text-gray-900 font-mono" x-text="p.numero_patente"></p>
                                    </div>
                                </div>
                                
                                <!-- Tipo -->
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tipo Patente</p>
                                        <p class="text-lg font-bold text-gray-900" x-text="p.tipo || '—'"></p>
                                    </div>
                                </div>
                                
                                <!-- Data Rilascio -->
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Data Rilascio</p>
                                        <p class="text-lg font-bold text-gray-900" x-text="p.data_rilascio_formatted || '—'"></p>
                                    </div>
                                </div>
                                
                                <!-- Scadenze -->
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Scadenze attive</p>
                                        <p class="text-lg font-bold text-gray-900">
                                            <span x-text="p.deadlines_count"></span>
                                            <span x-text="p.deadlines_count == 1 ? ' scadenza' : ' scadenze'"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Azioni -->
                            <div class="mt-6 pt-5 border-t border-gray-200 flex justify-center gap-3 pb-2">
                                <button @click="editPatente = p; showEditModal = true"
                                        class="btn-card-action btn-card-edit btn-card-compact">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Modifica</span>
                                </button>
                                
                                <button @click="deletePatente = p; showDeleteModal = true"
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

            <!-- Modal Crea Patente -->
            <div x-show="showCreateModal" 
                 x-cloak
                 @keydown.escape.window="closeCreateModal()"
                 class="modal-overlay"
                 :class="{ 'modal-active': showCreateModal && !isClosingCreate, 'modal-out': isClosingCreate }">
                
                <div @click="closeCreateModal()" class="modal-backdrop"></div>
                
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Nuova Patente</h3>
                            <button @click="closeCreateModal()" class="modal-close">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('patenti.store') }}" class="modal-body">
                            @csrf
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Numero Patente *</label>
                                    <input type="text" name="numero_patente" required class="form-input uppercase" placeholder="ES: AB1234567">
                                </div>
                                
                                <div>
                                    <label class="form-label">Tipo (es: B, A, C)</label>
                                    <input type="text" name="tipo" class="form-input uppercase" maxlength="10" placeholder="ES: B">
                                </div>
                                
                                <div>
                                    <label class="form-label">Data Rilascio</label>
                                    <input type="date" name="data_rilascio" class="form-input">
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

            <!-- Modal Modifica Patente -->
            <div x-show="showEditModal" 
                 x-cloak
                 @keydown.escape.window="closeEditModal()"
                 class="modal-overlay"
                 :class="{ 'modal-active': showEditModal && !isClosingEdit, 'modal-out': isClosingEdit }">
                
                <div @click="closeEditModal()" class="modal-backdrop"></div>
                
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title">Modifica Patente</h3>
                            <button @click="closeEditModal()" class="modal-close">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" :action="`/patenti/${editPatente.id}`" class="modal-body">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Numero Patente *</label>
                                    <input type="text" name="numero_patente" x-model="editPatente.numero_patente" required class="form-input uppercase">
                                </div>
                                
                                <div>
                                    <label class="form-label">Tipo (es: B, A, C)</label>
                                    <input type="text" name="tipo" x-model="editPatente.tipo" class="form-input uppercase" maxlength="10">
                                </div>
                                
                                <div>
                                    <label class="form-label">Data Rilascio</label>
                                    <input type="date" name="data_rilascio" x-model="editPatente.data_rilascio" class="form-input">
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

            <!-- Modal Elimina Patente -->
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
                            
                            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Elimina Patente</h3>
                            <p class="text-sm text-gray-600 text-center mb-1">Sei sicuro di voler eliminare la patente</p>
                            <p class="text-center mb-4">
                                <strong class="text-lg text-gray-900 font-bold" x-text="deletePatente.numero_patente"></strong>?
                            </p>
                            <p class="text-xs text-red-600 text-center bg-red-50 py-2 px-4 rounded-lg">
                                ⚠️ Verranno eliminate anche tutte le scadenze associate
                            </p>
                        </div>
                        
                        <form method="POST" :action="`/patenti/${deletePatente.id}`" class="px-6 pb-6">
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