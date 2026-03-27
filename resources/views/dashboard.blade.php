<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 w-full">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-tight">
                    Dashboard Amministrativa
                </h2>
                <p class="text-sm text-slate-500 mt-1">Monitoraggio veicoli e scadenze famiglia</p>
            </div>
            <div class="ml-2 bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200 text-sm text-slate-600 font-medium hidden md:flex items-center gap-2">
                <i class="fa-regular fa-calendar text-indigo-500"></i>
                {{ now()->locale('it')->isoFormat('dddd, D MMMM YYYY') }}
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen w-full"
         @open-calendar-event.window="openEventModal($event.detail)"
         x-data="dashboardManager()">
         
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 h-full w-1 bg-red-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Scadenze Urgenti</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-2">{{ $scadenzeUrgenti }}</h3>
                        </div>
                        <div class="p-3 bg-red-50 rounded-xl text-red-500"><i class="fa-solid fa-triangle-exclamation text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 h-full w-1 bg-amber-400"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">In Arrivo (30gg)</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-2">{{ $scadenzeImminenti }}</h3>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-xl text-amber-500"><i class="fa-solid fa-clock text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Da Pagare (Anno)</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-2">€{{ number_format($daPagareQuestAnno, 0, ',', '.') }}</h3>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-xl text-blue-500"><i class="fa-solid fa-euro-sign text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 h-full w-1 bg-emerald-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gestione</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-2">{{ $totaleVeicoli }}</h3>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-xl text-emerald-500"><i class="fa-solid fa-car text-xl"></i></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-lg text-slate-800 mb-4">Andamento Spese</h3>
                    <div class="h-[400px] w-full"><canvas id="speseMensiliChart"></canvas></div>
                </div>
                <div class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-full max-h-[470px] flex flex-col">
                    <h3 class="font-bold text-lg text-slate-800 mb-4 pb-2 border-b border-slate-100">Prossime Scadenze</h3>
                    <div class="overflow-y-auto pr-2 space-y-3 flex-1 custom-scrollbar">
                        @forelse($prossimaScadenze as $scadenza)
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg mr-3" style="background-color: {{ $scadenza->tipo_color }}20; color: {{ $scadenza->tipo_color }}">{{ $scadenza->tipo_icon }}</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-900 truncate">{{ $scadenza->vehicle ? $scadenza->vehicle->targa : $scadenza->user->name }}</p>
                                <p class="text-xs text-slate-500">{{ ucfirst($scadenza->tipo) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate-800">{{ $scadenza->importo_formatted }}</p>
                                <p class="text-xs font-bold {{ $scadenza->urgente ? 'text-red-500' : 'text-amber-500' }}">-{{ abs($scadenza->giorni_rimanenti) }}gg</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-slate-400 py-4">Nessuna scadenza.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-lg text-slate-800 mb-4">Distribuzione Costi</h3>
                    <div class="h-[300px]"><canvas id="spesePerTipoChart"></canvas></div>
                 </div>

                 <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                            <span class="w-2 h-6 bg-cyan-500 rounded-full"></span>
                            Archivio Digitale
                        </h3>
                        <button @click="openViewModal()" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 px-3 py-1 rounded-lg transition-colors">
                            Vedi Archivio (<span x-text="documents.length"></span>)
                        </button>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        @foreach(['bollo' => ['name' => 'Bolli', 'color' => 'yellow'], 'assicurazione' => ['name' => 'Assic.', 'color' => 'orange'], 'revisione' => ['name' => 'Rev.', 'color' => 'green'], 'patente' => ['name' => 'Patenti', 'color' => 'pink'], 'carta_identita' => ['name' => 'Carta d\'Identità', 'color' => 'stone'], 'tesserino_sanitario' => ['name' => 'Tesserino Sanitario', 'color' => 'teal']] as $tipo => $info)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-xs font-bold text-slate-700 uppercase">{{ $info['name'] }}</span>
                                <span class="text-xs font-bold text-slate-700" x-text="(stats['{{ $tipo }}'] ? stats['{{ $tipo }}'].percentuale : 0) + '%'"></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="bg-{{ $info['color'] }}-500 h-1.5 rounded-full transition-all duration-500" :style="`width: ${stats['{{ $tipo }}'] ? stats['{{ $tipo }}'].percentuale : 0}%`"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div @click="openUploadModal()" 
                         class="mt-auto border-2 border-dashed border-slate-300 rounded-xl p-5 text-center hover:bg-indigo-50 hover:border-indigo-400 cursor-pointer transition-all group relative">
                        <div class="flex flex-col items-center">
                            <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-3xl mb-2 group-hover:text-indigo-500 transition-colors"></i>
                            <p class="text-sm text-slate-600 font-bold group-hover:text-indigo-700">Carica Nuovo Documento</p>
                            <p class="text-xs text-slate-400 mt-1">Clicca per selezionare Veicolo e File</p>
                        </div>
                    </div>
                 </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6"> 
                <div class="flex flex-col 2xl:flex-row justify-between items-start 2xl:items-center gap-4 mb-6">
                    <h3 class="font-bold text-xl text-slate-800 flex-shrink-0">
                        <i class="fa-regular fa-calendar-days text-indigo-500 mr-2"></i> Calendario
                    </h3>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        @php
                            $feedUrl = url('/calendario/feed/RmaSegreto2026!');
                            $webcalUrl = str_replace(['http://', 'https://'], 'webcal://', $feedUrl);
                            $googleCalUrl = 'https://calendar.google.com/calendar/render?cid=' . urlencode($webcalUrl);
                        @endphp

                        <a href="{{ $feedUrl }}" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white px-3 py-2 rounded-lg shadow-sm border border-emerald-100 font-bold text-sm transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-download"></i>
                            <span class="hidden md:inline">Scarica .ICS</span>
                        </a>

                        <a href="{{ $googleCalUrl }}" target="_blank" class="bg-white text-slate-600 hover:text-blue-600 hover:border-blue-200 px-3 py-2 rounded-lg shadow-sm border border-slate-200 font-bold text-sm transition-colors flex items-center gap-2">
                            <i class="fa-brands fa-google text-red-500"></i>
                            <span class="hidden md:inline">Esporta in Google</span>
                        </a>

                        <a href="{{ $webcalUrl }}" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white px-3 py-2 rounded-lg shadow-sm border border-indigo-100 font-bold text-sm transition-colors flex items-center gap-2">
                            <i class="fa-brands fa-apple"></i>
                            <i class="fa-brands fa-windows text-xs"></i>
                            <span class="hidden md:inline">Esporta altrove</span>
                        </a>
                    </div>
                </div>
                <div id="calendar" class="mt-2"></div>
            </div>
        </div>

        <div x-show="showUploadModal" x-cloak class="modal-overlay" :class="{ 'modal-active': showUploadModal && !isClosingUpload, 'modal-out': isClosingUpload }">
            <div @click="closeUploadModal()" class="modal-backdrop"></div>
            <div class="modal-container">
                <div @click.stop class="modal-content-wrapper bg-white rounded-xl shadow-2xl overflow-hidden transform transition-all w-full max-w-lg">
                    <div class="modal-header bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-white">Carica Documento</h3>
                        <button @click="closeUploadModal()" class="text-white hover:text-indigo-100 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    
                    <form @submit="handleDocumentSubmit" enctype="multipart/form-data" class="p-6 space-y-5">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo di Documento</label>
                            <select name="categoria" x-model="selectedCategory" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="bollo">Bollo Auto</option>
                                <option value="assicurazione">Polizza Assicurativa</option>
                                <option value="revisione">Revisione</option>
                                <option value="patente">Patente</option>
                                <option value="carta_identita">Carta d'Identità</option>
                                <option value="tesserino_sanitario">Tesserino Sanitario</option>
                                <option value="altro">Altro</option>
                            </select>
                        </div>

                        <div x-show="!['patente', 'carta_identita', 'tesserino_sanitario'].includes(selectedCategory)" x-transition>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Associa al Veicolo</label>
                            <select name="vehicle_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">-- Seleziona Veicolo --</option>
                                @foreach(\App\Models\Vehicle::all() as $v)
                                    <option value="{{ $v->id }}">{{ $v->targa }} - {{ $v->marca }} {{ $v->modello }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="['patente', 'carta_identita', 'tesserino_sanitario'].includes(selectedCategory)" x-transition>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Associa all'Utente</label>
                            <select name="user_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">-- Seleziona Persona --</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">File (PDF o Foto)</label>
                            <input type="file" name="documento" accept=".pdf,.jpg,.jpeg,.png" required
                                   class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="closeUploadModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">Annulla</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold shadow-md transform hover:scale-105 transition-all">Salva Documento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="showViewModal" x-cloak class="modal-overlay" :class="{ 'modal-active': showViewModal && !isClosingView, 'modal-out': isClosingView }">
            <div @click="closeViewModal()" class="modal-backdrop"></div>
            
            <div class="modal-container w-full" style="max-width: 64rem;"> 
                <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden h-[85vh] flex flex-col">
                    
                    <div class="modal-header bg-slate-800 px-6 py-5 flex justify-between items-center flex-shrink-0">
                        <div>
                            <h3 class="text-xl font-bold text-white tracking-tight">Archivio Documenti</h3>
                            <p class="text-slate-400 text-sm mt-1">Gestisci tutti i file e i referti caricati a sistema</p>
                        </div>
                        <button @click="closeViewModal()" class="text-slate-400 hover:text-white transition-colors bg-slate-700/50 hover:bg-slate-700 w-8 h-8 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 bg-slate-50 custom-scrollbar">
                        
                        <template x-if="documents.length > 0">
                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                <template x-for="doc in documents" :key="doc.id">
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all flex flex-col sm:flex-row items-start sm:items-center gap-4 group">
                                        
                                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl flex-shrink-0 transition-colors"
                                             :class="doc.tipo_file == 'application/pdf' ? 'bg-red-50 text-red-500 group-hover:bg-red-100' : 'bg-blue-50 text-blue-500 group-hover:bg-blue-100'">
                                            <i class="fa-solid" :class="doc.tipo_file == 'application/pdf' ? 'fa-file-pdf' : 'fa-file-image'"></i>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0 w-full">
                                            <h4 class="text-base font-bold text-slate-800 truncate" :title="doc.nome_originale" x-text="doc.nome_originale"></h4>
                                            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                                    <i class="fa-regular fa-calendar mr-1"></i> <span x-text="doc.created_at"></span>
                                                </span>
                                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600" x-text="doc.categoria"></span>
                                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                                
                                                <template x-if="doc.vehicle_targa">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded border border-slate-200 text-[10px] font-bold bg-slate-50 text-slate-600 shadow-sm" x-text="doc.vehicle_targa"></span>
                                                </template>
                                                <template x-if="doc.is_personal">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded border border-purple-100 text-[10px] font-bold bg-purple-50 text-purple-600 shadow-sm">Personale</span>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 w-full sm:w-auto mt-3 sm:mt-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                            <button @click="openPreview(doc.path, doc.tipo_file, doc.nome_originale)" 
                                                    class="flex-1 sm:flex-none px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center gap-1.5">
                                                <i class="fa-solid fa-eye"></i> Vedi
                                            </button>
                                            <button @click="openDeleteDoc(doc.id, doc.nome_originale)" 
                                                    class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-colors" title="Elimina">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="documents.length === 0">
                            <div class="flex flex-col items-center justify-center h-full text-slate-500 py-12">
                                <div class="w-24 h-24 bg-slate-200/50 rounded-full flex items-center justify-center mb-5">
                                    <i class="fa-regular fa-folder-open text-5xl text-slate-400"></i>
                                </div>
                                <h4 class="font-bold text-xl text-slate-700">Archivio Vuoto</h4>
                                <p class="text-sm mt-2 text-slate-500">I documenti che caricherai appariranno qui.</p>
                            </div>
                        </template>
                        
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showPreviewModal" x-cloak class="modal-overlay" style="z-index: 1060;" 
             :class="{ 'modal-active': showPreviewModal && !isPreviewClosing, 'modal-out': isPreviewClosing }"
             @keydown.escape.window="closePreview()">
            <div @click="closePreview()" class="modal-backdrop bg-slate-900/70 backdrop-blur-sm"></div>
            
            <div class="modal-container" style="max-width: 95vw !important; width: 95vw !important; height: 95vh !important; display: flex; flex-direction: column;">
                <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex-1 flex flex-col w-full h-full">
                    <div class="bg-slate-800 px-5 py-4 flex justify-between items-center flex-shrink-0">
                        <div>
                            <h4 class="text-lg font-bold text-white truncate" x-text="currentDocName"></h4>
                            <p class="text-slate-400 text-xs">Anteprima Documento</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a :href="currentDocUrl" download class="text-slate-400 hover:text-white transition-colors bg-slate-700/50 hover:bg-slate-700 w-9 h-9 rounded-lg flex items-center justify-center" title="Scarica File">
                                <i class="fa-solid fa-download"></i>
                            </a>
                            <button @click="closePreview()" class="text-slate-400 hover:text-white transition-colors bg-slate-700/50 hover:bg-slate-700 w-9 h-9 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 bg-slate-100 flex items-center justify-center overflow-hidden p-2">
                        <template x-if="currentDocType === 'application/pdf'">
                            <iframe :src="currentDocUrl" class="w-full h-full rounded shadow-lg border-0 bg-white"></iframe>
                        </template>
                        <template x-if="currentDocType !== 'application/pdf'">
                            <img :src="currentDocUrl" class="max-w-full max-h-full object-contain rounded shadow-lg bg-white">
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showSuccessModal" x-cloak class="modal-overlay" style="z-index: 1070;" 
             :class="{ 'modal-active': showSuccessModal, 'modal-out': isClosingSuccess }">
            <div @click="closeSuccessModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="modal-container w-full max-w-md">
                <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-6 text-center pt-8 pb-6">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-emerald-50 mb-5 animate-pulse">
                            <i class="fa-solid fa-check text-3xl text-emerald-500"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 mb-2">Perfetto!</h3>
                        <p class="text-sm text-slate-600" x-text="successMessage"></p>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showErrorModal" x-cloak class="modal-overlay" style="z-index: 1065;" 
             :class="{ 'modal-active': showErrorModal, 'modal-out': isClosingError }">
            <div @click="closeErrorModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="modal-container w-full max-w-md">
                <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-6 text-center pt-8 pb-6">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-5 animate-pulse">
                            <i class="fa-solid fa-circle-exclamation text-3xl text-red-500"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 mb-2">Errore</h3>
                        <p class="text-sm text-slate-600" x-text="errorMessage"></p>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showDeleteDocModal" x-cloak class="modal-overlay" style="z-index: 1060;" 
             :class="{ 'modal-active': showDeleteDocModal && !isClosingDeleteDoc, 'modal-out': isClosingDeleteDoc }">
            <div @click="closeDeleteDoc()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="modal-container w-full max-w-md">
                <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-6 text-center pt-8">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-5">
                            <i class="fa-solid fa-trash-can text-3xl text-red-500"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 mb-2">Elimina Documento</h3>
                        <p class="text-sm text-slate-500 mb-6">
                            Sei sicuro di voler eliminare definitivamente il file <br>
                            <strong class="text-slate-800" x-text="docToDeleteName"></strong>?
                        </p>
                    </div>
                    <form @submit.prevent="handleDocumentDelete" class="px-6 pb-6 bg-slate-50/50 pt-4 border-t border-slate-100">
                        <div class="flex justify-center gap-3">
                            <button type="button" @click="closeDeleteDoc()" class="flex-1 py-3 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition-colors shadow-sm">Annulla</button>
                            <button type="submit" class="flex-1 py-3 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 transition-colors shadow-sm shadow-red-200">Sì, Elimina</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="showEventModal" x-cloak class="modal-overlay" style="z-index: 1060;" 
             :class="{ 'modal-active': showEventModal && !isClosingEvent, 'modal-out': isClosingEvent }"
             @keydown.escape.window="closeEventModal()">
            <div @click="closeEventModal()" class="modal-backdrop bg-slate-900/70 backdrop-blur-sm"></div>
            <div class="modal-container w-full max-w-sm pointer-events-auto">
                <div @click.stop class="modal-content-wrapper bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col relative">
                    <div class="h-2.5 w-full" :style="`background-color: ${eventData.color}`"></div>
                    <div class="p-7">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-slate-50 border border-slate-100 shadow-sm" :style="`color: ${eventData.color}`">
                                <i class="fa-regular fa-calendar-check text-3xl"></i>
                            </div>
                            <button @click="closeEventModal()" class="text-slate-400 hover:text-slate-600 transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-tight mb-1" x-text="eventData.title"></h3>
                        <p class="text-sm font-semibold text-slate-500 capitalize mb-7" x-text="eventData.date"></p>
                        <div class="space-y-4 bg-slate-50 p-5 rounded-2xl border border-slate-100 shadow-inner">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stato</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold shadow-sm"
                                      :class="eventData.pagato ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'">
                                    <i class="fa-solid mr-1.5" :class="eventData.pagato ? 'fa-check' : 'fa-triangle-exclamation'"></i>
                                    <span x-text="eventData.pagato ? 'COMPLETATO' : 'IN SCADENZA'"></span>
                                </span>
                            </div>
                            <div class="flex justify-between items-center border-t border-slate-200 pt-4">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Importo</span>
                                <span class="text-xl font-black text-slate-800">€ <span x-text="eventData.importo"></span></span>
                            </div>
                        </div>
                        <template x-if="eventData.note">
                            <div class="mt-5 p-4 bg-blue-50/50 border border-blue-100 rounded-xl">
                                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                    <i class="fa-solid fa-note-sticky"></i> Note Aggiuntive
                                </h4>
                                <p class="text-sm text-blue-900/80 leading-relaxed font-medium" x-text="eventData.note"></p>
                            </div>
                        </template>
                    </div>
                    <div class="px-7 pb-7">
                        <button @click="closeEventModal()" class="w-full py-4 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-700 transition-transform active:scale-95 shadow-md">
                            Chiudi Dettagli
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function dashboardManager() {
            return {
                showUploadModal: false,
                showViewModal: false,
                showPreviewModal: false,
                showSuccessModal: false,
                showErrorModal: false,
                successMessage: '',
                errorMessage: '',
                isClosingUpload: false,
                isClosingView: false,
                isPreviewClosing: false,
                isClosingSuccess: false,
                isClosingError: false,
                
                selectedCategory: 'bollo',
                currentDocUrl: '',
                currentDocType: '',
                currentDocName: '',

                showDeleteDocModal: false,
                isClosingDeleteDoc: false,
                docToDeleteId: null,
                docToDeleteName: '',

                stats: @json($categorieStats),
                documents: [],
                
                showEventModal: false,
                isClosingEvent: false,
                eventData: {},

                // Questa funzione Alpine auto-carica i dati all'apertura tramite x-init
                init() {
                    this.refreshData();
                },

                async refreshData() {
                    try {
                        let resStats = await fetch('{{ route("dashboard.stats") }}');
                        if (resStats.ok) this.stats = await resStats.json();

                        let resDocs = await fetch('{{ route("dashboard.documents") }}');
                        if (resDocs.ok) {
                            let dataDocs = await resDocs.json();
                            this.documents = dataDocs.documents;
                        }
                    } catch (e) {
                        console.error('Errore fetch dati:', e);
                    }
                },

                openUploadModal() { this.showUploadModal = true; },
                closeUploadModal() {
                    this.isClosingUpload = true;
                    setTimeout(() => {
                        this.showUploadModal = false;
                        this.isClosingUpload = false;
                        this.selectedCategory = 'bollo';
                    }, 500);
                },

                openViewModal() { this.showViewModal = true; },
                closeViewModal() {
                    this.isClosingView = true;
                    setTimeout(() => {
                        this.showViewModal = false;
                        this.isClosingView = false;
                    }, 500);
                },

                openPreview(url, type, name) {
                    this.currentDocUrl = url;
                    this.currentDocType = type;
                    this.currentDocName = name;
                    this.showPreviewModal = true;
                },
                closePreview() {
                    this.isPreviewClosing = true;
                    setTimeout(() => {
                        this.showPreviewModal = false;
                        this.isPreviewClosing = false;
                        this.currentDocUrl = '';
                    }, 500);
                },

                openDeleteDoc(id, name) {
                    this.docToDeleteId = id;
                    this.docToDeleteName = name;
                    this.showDeleteDocModal = true;
                },
                closeDeleteDoc() {
                    this.isClosingDeleteDoc = true;
                    setTimeout(() => {
                        this.showDeleteDocModal = false;
                        this.isClosingDeleteDoc = false;
                        this.docToDeleteId = null;
                    }, 500);
                },

                openEventModal(data) {
                    this.eventData = data;
                    this.showEventModal = true;
                },
                closeEventModal() {
                    this.isClosingEvent = true;
                    setTimeout(() => {
                        this.showEventModal = false;
                        this.isClosingEvent = false;
                    }, 500);
                },

                async handleDocumentSubmit(event) {
                    event.preventDefault();
                    const formData = new FormData(event.target);
                    
                    try {
                        const response = await fetch('{{ route("documents.store") }}', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            event.target.reset();
                            this.selectedCategory = 'bollo';
                            this.closeUploadModal();
                            
                            await this.refreshData();
                            
                            this.successMessage = data.message || 'Documento caricato con successo!';
                            this.showSuccessModal = true;
                            
                            setTimeout(() => {
                                if(this.showSuccessModal) this.closeSuccessModal();
                            }, 2000);
                            
                        } else {
                            this.showError(data.message || 'Errore nel caricamento del documento');
                        }
                    } catch (error) {
                        console.error('Errore:', error);
                        this.showError('Errore nella comunicazione con il server.');
                    }
                },

                async handleDocumentDelete(event) {
                    try {
                        // Chiamata asincrona al server per eliminare
                        const response = await fetch(`/documents/${this.docToDeleteId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}', // Passiamo il token di sicurezza
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            // 1. Chiude la modale di eliminazione
                            this.closeDeleteDoc();
                            
                            // 2. AGGIORNAMENTO REATTIVO: Ricarica archivio e barre
                            await this.refreshData();
                            
                            // 3. Mostra la modale di successo
                            this.successMessage = data.message || 'Documento eliminato con successo!';
                            this.showSuccessModal = true;
                            
                            setTimeout(() => {
                                if(this.showSuccessModal) this.closeSuccessModal();
                            }, 2000);
                            
                        } else {
                            this.closeDeleteDoc();
                            this.showError(data.message || 'Errore durante l\'eliminazione del documento');
                        }
                    } catch (error) {
                        console.error('Errore:', error);
                        this.closeDeleteDoc();
                        this.showError('Errore nella comunicazione con il server.');
                    }
                },

                closeSuccessModal() {
                    this.isClosingSuccess = true;
                    setTimeout(() => {
                        this.showSuccessModal = false;
                        this.isClosingSuccess = false;
                        this.successMessage = '';
                    }, 300);
                },

                closeErrorModal() {
                    this.isClosingError = true;
                    setTimeout(() => {
                        this.showErrorModal = false;
                        this.isClosingError = false;
                        this.errorMessage = '';
                    }, 300);
                },

                showError(message) {
                    this.errorMessage = message;
                    this.showErrorModal = true;
                    setTimeout(() => {
                        if(this.showErrorModal) this.closeErrorModal();
                    }, 4000);
                }
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' /> -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/it.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Impostazioni globali Chart.js
            Chart.defaults.font.family = "'Figtree', sans-serif";
            Chart.defaults.color = '#64748b';

            // 1. GRAFICO SPESE MENSILI
            const ctxMensili = document.getElementById('speseMensiliChart').getContext('2d');
            new Chart(ctxMensili, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($speseMensili, 'mese')) !!},
                    datasets: [
                        {
                            label: 'Pagato',
                            data: {!! json_encode(array_column($speseMensili, 'pagato')) !!},
                            backgroundColor: '#10b981',
                            borderRadius: 4,
                            barPercentage: 0.6,
                        },
                        {
                            label: 'Da Pagare',
                            data: {!! json_encode(array_column($speseMensili, 'da_pagare')) !!},
                            backgroundColor: '#ef4444',
                            borderRadius: 4,
                            barPercentage: 0.6,
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 8, callbacks: { label: function(context) { return context.dataset.label + ': €' + context.parsed.y.toLocaleString('it-IT', {minimumFractionDigits: 2}); } } } },
                    scales: { x: { stacked: true, grid: { display: false }, ticks: { font: { weight: '600' } } }, y: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { callback: function(value) { return '€' + value; } } } }
                }
            });

            // 2. GRAFICO TORTA
            const ctxTipo = document.getElementById('spesePerTipoChart').getContext('2d');
            new Chart(ctxTipo, {
                type: 'doughnut',
                data: {
                    labels: ['Bollo', 'Assicurazione', 'Patente', 'Revisione', 'Carta d\'Identità', 'Tesserino Sanitario'],
                    datasets: [{
                        data: [
                            {{ $spesePerTipo['bollo'] ?? 0 }},
                            {{ $spesePerTipo['assicurazione'] ?? 0 }},
                            {{ $spesePerTipo['patente'] ?? 0 }},
                            {{ $spesePerTipo['revisione'] ?? 0 }},
                            {{ $spesePerTipo['carta_identita'] ?? 0 }},
                            {{ $spesePerTipo['tesserino_sanitario'] ?? 0 }}
                        ],
                        backgroundColor: ['#eab308', '#3b82f6', '#ec4899', '#22c55e', '#a8a29e', '#14b8a6'],
                        borderWidth: 0, hoverOffset: 10
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } } }
            });

            // 3. CALENDARIO
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'it',
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listYear' },
                buttonText: { today: 'Oggi', month: 'Mese', list: 'Lista' },
                events: [
                    @foreach($deadlines as $d)
                    {
                        title: {!! json_encode(ucfirst($d->tipo) . ' ' . ($d->vehicle ? $d->vehicle->targa : ($d->user ? $d->user->name : ''))) !!},
                        start: '{{ $d->data_scadenza->format('Y-m-d') }}',
                        color: '{{ $d->tipo_color }}',
                        allDay: true,
                        extendedProps: {
                            tipo: {!! json_encode(ucfirst($d->tipo)) !!},
                            importo: '{{ number_format($d->importo ?? 0, 2, ',', '.') }}',
                            note: {!! json_encode($d->note ?? '') !!},
                            pagato: {{ $d->pagato ? 'true' : 'false' }}
                        }
                    },
                    @endforeach
                ],
                eventClick: function(info) {
                    window.dispatchEvent(new CustomEvent('open-calendar-event', {
                        detail: {
                            title: info.event.title, date: info.event.start.toLocaleDateString('it-IT', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }), color: info.event.backgroundColor, importo: info.event.extendedProps.importo, note: info.event.extendedProps.note, pagato: info.event.extendedProps.pagato
                        }
                    }));
                }
            });
            calendar.render();
        });
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0); }
        .modal-active .modal-backdrop { animation: fadeIn 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-backdrop { animation: fadeOut 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-container { position: relative; width: 100%; max-width: 28rem; z-index: 1001; }
        .modal-content-wrapper { background: white; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; transform: scale(0); }
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }

        @keyframes fadeIn { 0% { background: rgba(0,0,0,0); } 100% { background: rgba(0,0,0,0.7); } }
        @keyframes fadeOut { 0% { background: rgba(0,0,0,0.7); } 100% { background: rgba(0,0,0,0); } }
        @keyframes blowUpModal { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700; color: #1f2937; }
        .fc-button-primary { background-color: #4f46e5 !important; border-color: #4f46e5 !important; }
        .fc-day-today { background-color: #eef2ff !important; }
        .fc-event { border: none; padding: 2px 4px; font-size: 0.85em; cursor: pointer; }
    </style>
</x-app-layout>