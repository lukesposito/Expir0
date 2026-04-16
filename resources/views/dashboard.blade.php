<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 w-full">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-tight">
                    Dashboard Amministrativa
                </h2>
                <p class="text-sm text-slate-500 mt-1">Monitoraggio veicoli e scadenze famiglia</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200 text-sm text-slate-600 font-medium hidden md:flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-indigo-500"></i>
                    {{ now()->locale('it')->isoFormat('dddd, D MMMM YYYY') }}
                </div>
                
                <div class="relative" x-data>
                    <select @change="$dispatch('change-dashboard-year', $event.target.value)" class="appearance-none bg-indigo-600 text-white font-bold rounded-lg shadow-md border-0 focus:ring-indigo-500 py-2.5 pl-4 pr-10 cursor-pointer hover:bg-indigo-700 transition-colors">
                        @foreach($availableYears ?? [now()->year] as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>Anno {{ $year }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-white">
                        <i class="fa-solid fa-chevron-down text-sm"></i>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen w-full relative" x-data="dashboardManager()" @change-dashboard-year.window="changeYear($event.detail)">
        
        <div x-show="isLoading" x-transition.opacity.duration.300ms class="absolute inset-0 bg-slate-50/60 backdrop-blur-[2px] z-50 flex items-start justify-center pt-20 rounded-2xl" x-cloak>
            <div class="bg-indigo-600 text-white px-6 py-3 rounded-full font-bold shadow-lg flex items-center gap-3">
                <i class="fa-solid fa-circle-notch fa-spin text-xl"></i> Sincronizzazione Dati...
            </div>
        </div>
         
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8" :class="{ 'opacity-50 pointer-events-none': isLoading }">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div @click="setTopFilter('urgente')" class="bg-white rounded-2xl shadow-sm p-6 relative overflow-hidden group hover:shadow-md transition-all cursor-pointer transform hover:-translate-y-1" :class="activeFilter === 'urgente' ? 'ring-4 ring-red-100 border-2 border-red-400' : 'border border-slate-200'">
                    <div class="absolute right-0 top-0 h-full w-1 bg-red-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Urgenti (<span x-text="selectedYear"></span>)</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-2" x-text="stats.scadenzeUrgenti"></h3>
                        </div>
                        <div class="p-3 rounded-xl transition-colors" :class="activeFilter === 'urgente' ? 'bg-red-500 text-white' : 'bg-red-50 text-red-500'"><i class="fa-solid fa-triangle-exclamation text-xl"></i></div>
                    </div>
                </div>

                <div @click="setTopFilter('imminente')" class="bg-white rounded-2xl shadow-sm p-6 relative overflow-hidden group hover:shadow-md transition-all cursor-pointer transform hover:-translate-y-1" :class="activeFilter === 'imminente' ? 'ring-4 ring-amber-100 border-2 border-amber-400' : 'border border-slate-200'">
                    <div class="absolute right-0 top-0 h-full w-1 bg-amber-400"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">In Arrivo (<span x-text="selectedYear"></span>)</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-2" x-text="stats.scadenzeImminenti"></h3>
                        </div>
                        <div class="p-3 rounded-xl transition-colors" :class="activeFilter === 'imminente' ? 'bg-amber-400 text-white' : 'bg-amber-50 text-amber-500'"><i class="fa-solid fa-clock text-xl"></i></div>
                    </div>
                </div>

                <div @click="setTopFilter('all')" class="bg-white rounded-2xl shadow-sm p-6 relative overflow-hidden group hover:shadow-md transition-all cursor-pointer transform hover:-translate-y-1" :class="activeFilter === 'all' && chartFilterType === null ? 'ring-4 ring-blue-100 border-2 border-blue-400' : 'border border-slate-200'">
                    <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Da Pagare (<span x-text="selectedYear"></span>)</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-2" x-text="'€' + Number(stats.daPagareQuestAnno).toLocaleString('it-IT', {minimumFractionDigits: 0})"></h3>
                        </div>
                        <div class="p-3 rounded-xl transition-colors" :class="activeFilter === 'all' && chartFilterType === null ? 'bg-blue-500 text-white' : 'bg-blue-50 text-blue-500'"><i class="fa-solid fa-euro-sign text-xl"></i></div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 h-full w-1 bg-emerald-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gestione</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-2" x-text="stats.totaleVeicoli"></h3>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-xl text-emerald-500"><i class="fa-solid fa-car text-xl"></i></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-lg text-slate-800 mb-4 flex items-center">
                        Andamento Spese (<span x-text="selectedYear"></span>)
                        <span x-show="activeFilter === 'urgente'" x-cloak class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-md ml-3 transition-all">Solo Urgenti</span>
                        <span x-show="activeFilter === 'imminente'" x-cloak class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-md ml-3 transition-all">In Arrivo</span>
                        <span class="text-xs font-normal text-slate-400 ml-auto italic hidden sm:block"><i class="fa-solid fa-hand-pointer"></i> Clicca sulle barre per filtrare i mesi</span>
                    </h3>
                    <div class="h-[400px] w-full"><canvas id="speseMensiliChart"></canvas></div>
                </div>
                
                <div class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-full max-h-[490px] flex flex-col">
                    <h3 class="font-bold text-lg mb-4 pb-2 border-b border-slate-100 flex justify-between items-center transition-colors" 
                        :class="chartFilterType !== null ? 'text-indigo-600' : (activeFilter === 'urgente' ? 'text-red-600' : (activeFilter === 'imminente' ? 'text-amber-600' : 'text-slate-800'))">
                        <span x-text="listTitle"></span>
                        <span x-show="activeFilter !== 'all' || chartFilterType !== null" @click="resetFilters()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-1 rounded cursor-pointer shadow-sm transition-colors uppercase tracking-wider font-black">Reset</span>
                    </h3>
                    
                    <div class="overflow-y-auto pr-2 space-y-3 flex-1 custom-scrollbar">
                        <template x-for="scadenza in displayedList" :key="scadenza.id">
                            <div class="flex items-center p-3 bg-slate-50 rounded-lg border shadow-sm transition-all hover:bg-white" :class="scadenza.pagato ? 'border-green-200 bg-green-50/40' : 'border-slate-100'">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg mr-3 flex-shrink-0" :style="`background-color: ${scadenza.tipo_color}20; color: ${scadenza.tipo_color}`" x-text="scadenza.tipo_icon"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <p class="text-sm font-bold text-slate-900 truncate" x-text="scadenza.titolo"></p>
                                        <span x-show="scadenza.pagato" class="px-1.5 py-0.5 rounded text-[10px] font-black bg-green-100 text-green-700 tracking-wider border border-green-200">PAGATO</span>
                                    </div>
                                    <p class="text-xs text-slate-500" x-text="scadenza.tipo_label"></p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <p class="text-sm font-bold" :class="scadenza.pagato ? 'text-green-700 line-through opacity-60' : 'text-slate-800'" x-text="scadenza.importo_formattato"></p>
                                    <template x-if="!scadenza.pagato">
                                        <div>
                                            <template x-if="selectedYear == currentYear">
                                                <p class="text-xs font-bold" :class="scadenza.is_scaduto ? 'text-red-500' : 'text-amber-500'" x-text="scadenza.is_scaduto ? 'Scaduta da ' + Math.abs(scadenza.giorni) + 'gg' : '-' + scadenza.giorni + 'gg'"></p>
                                            </template>
                                            <template x-if="selectedYear != currentYear">
                                                <p class="text-xs font-bold text-slate-500" x-text="scadenza.data_formattata"></p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="scadenza.pagato">
                                         <p class="text-xs font-bold text-green-600 flex items-center justify-end gap-1"><i class="fa-solid fa-check"></i> <span x-text="scadenza.data_formattata"></span></p>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        <div x-show="displayedList.length === 0" class="flex flex-col items-center justify-center py-6 text-slate-400">
                            <i class="fa-solid fa-check-circle text-3xl mb-2" :class="chartFilterType !== null ? 'text-indigo-200' : 'text-emerald-200'"></i>
                            <p class="text-sm font-semibold" x-text="chartFilterType !== null ? 'Nessuna spesa per questo filtro.' : 'Nessuna spesa in sospeso.'"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-lg text-slate-800 mb-4 flex items-center">
                        Distribuzione Costi
                        <span class="text-xs font-normal text-slate-400 ml-auto italic hidden sm:block"><i class="fa-solid fa-hand-pointer"></i> Clicca sulle fette per filtrare</span>
                    </h3>
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

                    <div @click="openUploadModal()" class="mt-auto border-2 border-dashed border-slate-300 rounded-xl p-5 text-center hover:bg-indigo-50 hover:border-indigo-400 cursor-pointer transition-all group relative">
                        <div class="flex flex-col items-center">
                            <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-3xl mb-2 group-hover:text-indigo-500 transition-colors"></i>
                            <p class="text-sm text-slate-600 font-bold group-hover:text-indigo-700">Carica Nuovo Documento</p>
                        </div>
                    </div>
                 </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6"> 
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-8">
                    <h3 class="font-bold text-xl text-slate-800 flex-shrink-0">
                        <i class="fa-regular fa-calendar-days text-indigo-500 mr-2"></i> Calendario Scadenze
                    </h3>
                    
                    <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                        <a href="https://www.google.com/calendar/render?cid={{ urlencode(url('/calendario/feed/' . env('CALENDAR_FEED_TOKEN'))) }}" 
                           target="_blank"
                           class="flex-1 sm:flex-none px-4 py-2 bg-[#f8f9fa] text-[#3c4043] rounded-xl text-xs font-bold hover:bg-white hover:shadow-md transition-all flex items-center justify-center gap-2 border border-slate-200">
                            <i class="fa-brands fa-google text-lg text-[#4285F4]"></i> 
                            <span>Google Calendar</span>
                        </a>

                        <a href="webcal://{{ str_replace(['http://', 'https://'], '', url('/calendario/feed/' . env('CALENDAR_FEED_TOKEN'))) }}" 
                           class="flex-1 sm:flex-none px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-900 hover:shadow-md transition-all flex items-center justify-center gap-2">
                            <i class="fa-brands fa-apple text-lg"></i> 
                            <span>iOS / Outlook</span>
                        </a>
    
                        <a href="{{ route('calendario.feed', ['token' => env('CALENDAR_FEED_TOKEN')]) }}" 
                           class="flex-1 sm:flex-none px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-bold hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center gap-2 border border-indigo-100">
                            <i class="fa-solid fa-file-export text-lg"></i> 
                            <span>Scarica .ics</span>
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
                        <button @click="closeUploadModal()" class="text-white hover:text-indigo-100 focus:outline-none"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <form @submit="handleDocumentSubmit" enctype="multipart/form-data" class="p-6 space-y-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo di Documento</label>
                            <select name="categoria" x-model="selectedCategory" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="bollo">Bollo Auto</option><option value="assicurazione">Polizza Assicurativa</option><option value="revisione">Revisione</option><option value="patente">Patente</option><option value="carta_identita">Carta d'Identità</option><option value="tesserino_sanitario">Tesserino Sanitario</option><option value="altro">Altro</option>
                            </select>
                        </div>
                        <div x-show="!['patente', 'carta_identita', 'tesserino_sanitario'].includes(selectedCategory)" x-transition>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Associa al Veicolo</label>
                            <select name="vehicle_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">-- Seleziona Veicolo --</option>
                                @foreach(\App\Models\Vehicle::all() as $v) <option value="{{ $v->id }}">{{ $v->marca }} {{ $v->modello }} - {{ $v->targa }}</option> @endforeach
                            </select>
                        </div>
                        <div x-show="['patente', 'carta_identita', 'tesserino_sanitario'].includes(selectedCategory)" x-transition>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Associa all'Utente</label>
                            <select name="user_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">-- Seleziona Persona --</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">File (PDF o Foto)</label>
                            <input type="file" name="documento" accept=".pdf,.jpg,.jpeg,.png" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
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
                        <div class="flex items-center gap-3">
                            <template x-if="currentFolder">
                                <button @click="closeFolder()" class="text-slate-300 hover:text-white transition-colors bg-slate-700/50 hover:bg-slate-600 w-8 h-8 rounded-lg flex items-center justify-center"><i class="fa-solid fa-arrow-left"></i></button>
                            </template>
                            <div>
                                <h3 class="text-xl font-bold text-white tracking-tight">
                                    <template x-if="!currentFolder"><span>Archivio Documenti</span></template>
                                    <template x-if="currentFolder"><span x-text="currentFolderInfo.name"></span></template>
                                </h3>
                                <p class="text-slate-400 text-sm mt-1">
                                    <template x-if="!currentFolder"><span>Scegli una cartella per visualizzare i file</span></template>
                                    <template x-if="currentFolder"><span x-text="currentFolderDocs.length + ' documenti archiviati'"></span></template>
                                </p>
                            </div>
                        </div>
                        <button @click="closeViewModal()" class="text-slate-400 hover:text-white transition-colors bg-slate-700/50 hover:bg-slate-700 w-8 h-8 rounded-lg flex items-center justify-center"><i class="fa-solid fa-xmark text-lg"></i></button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 bg-slate-50 custom-scrollbar">
                        <template x-if="!currentFolder">
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                <template x-for="folder in folders" :key="folder.id">
                                    <div @click="openFolder(folder.id)" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm transition-all cursor-pointer group flex flex-col items-center text-center" :class="folder.hover">
                                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-3 transition-transform group-hover:scale-110" :class="folder.bg + ' ' + folder.color"><i class="fa-solid" :class="folder.icon"></i></div>
                                        <h4 class="font-bold text-slate-800 text-sm mb-1" x-text="folder.name"></h4>
                                        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-1 rounded-md" x-text="getDocsInFolder(folder.id).length + ' file'"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="currentFolder">
                            <div>
                                <template x-if="currentFolderDocs.length > 0">
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                        <template x-for="doc in currentFolderDocs" :key="doc.id">
                                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all flex flex-col sm:flex-row items-start sm:items-center gap-4 group">
                                                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl flex-shrink-0 transition-colors" :class="doc.tipo_file == 'application/pdf' ? 'bg-red-50 text-red-500 group-hover:bg-red-100' : 'bg-blue-50 text-blue-500 group-hover:bg-blue-100'"><i class="fa-solid" :class="doc.tipo_file == 'application/pdf' ? 'fa-file-pdf' : 'fa-file-image'"></i></div>
                                                <div class="flex-1 min-w-0 w-full">
                                                    <h4 class="text-base font-bold text-slate-800 truncate" :title="doc.nome_originale" x-text="doc.nome_originale"></h4>
                                                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500"><i class="fa-regular fa-calendar mr-1"></i> <span x-text="doc.created_at"></span></span>
                                                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                                        <template x-if="doc.vehicle_targa"><span class="inline-flex items-center px-2 py-0.5 rounded border border-slate-200 text-[10px] font-bold bg-slate-50 text-slate-600 shadow-sm capitalize" x-text="doc.vehicle_marca + ' ' + doc.vehicle_modello"></span></template>
                                                        <template x-if="doc.is_personal"><span class="inline-flex items-center px-2 py-0.5 rounded border border-purple-100 text-[10px] font-bold bg-purple-50 text-purple-600 shadow-sm">Personale</span></template>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 w-full sm:w-auto mt-3 sm:mt-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                                    <button @click="openPreview(doc.path, doc.tipo_file, doc.nome_originale)" class="flex-1 sm:flex-none px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center gap-1.5"><i class="fa-solid fa-eye"></i> Vedi</button>
                                                    <button @click="openDeleteDoc(doc.id, doc.nome_originale)" class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-colors" title="Elimina"><i class="fa-solid fa-trash-can"></i></button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="currentFolderDocs.length === 0">
                                    <div class="flex flex-col items-center justify-center h-full text-slate-500 py-12"><div class="w-24 h-24 bg-slate-200/50 rounded-full flex items-center justify-center mb-5"><i class="fa-regular fa-folder-open text-5xl text-slate-400"></i></div><h4 class="font-bold text-xl text-slate-700">Cartella Vuota</h4><p class="text-sm mt-2 text-slate-500">Non ci sono documenti in questa categoria.</p></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showPreviewModal" x-cloak class="modal-overlay" style="z-index: 1060;" :class="{ 'modal-active': showPreviewModal && !isPreviewClosing, 'modal-out': isPreviewClosing }" @keydown.escape.window="closePreview()">
            <div @click="closePreview()" class="modal-backdrop bg-slate-900/70 backdrop-blur-sm"></div>
            <div class="modal-container" style="max-width: 95vw !important; width: 95vw !important; height: 95vh !important; display: flex; flex-direction: column;">
                <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex-1 flex flex-col w-full h-full">
                    <div class="bg-slate-800 px-5 py-4 flex justify-between items-center flex-shrink-0">
                        <div><h4 class="text-lg font-bold text-white truncate" x-text="currentDocName"></h4><p class="text-slate-400 text-xs">Anteprima Documento</p></div>
                        <div class="flex items-center gap-3"><a :href="currentDocUrl" download class="text-slate-400 hover:text-white transition-colors bg-slate-700/50 hover:bg-slate-700 w-9 h-9 rounded-lg flex items-center justify-center" title="Scarica File"><i class="fa-solid fa-download"></i></a><button @click="closePreview()" class="text-slate-400 hover:text-white transition-colors bg-slate-700/50 hover:bg-slate-700 w-9 h-9 rounded-lg flex items-center justify-center"><i class="fa-solid fa-xmark text-lg"></i></button></div>
                    </div>
                    <div class="flex-1 bg-slate-100 flex items-center justify-center overflow-hidden p-2">
                        <template x-if="currentDocType === 'application/pdf'"><iframe :src="currentDocUrl" class="w-full h-full rounded shadow-lg border-0 bg-white"></iframe></template>
                        <template x-if="currentDocType !== 'application/pdf'"><img :src="currentDocUrl" class="max-w-full max-h-full object-contain rounded shadow-lg bg-white"></template>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showSuccessModal" x-cloak class="modal-overlay" style="z-index: 1070;" :class="{ 'modal-active': showSuccessModal, 'modal-out': isClosingSuccess }">
            <div @click="closeSuccessModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="modal-container w-full max-w-md"><div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"><div class="p-6 text-center pt-8 pb-6"><div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-emerald-50 mb-5 animate-pulse"><i class="fa-solid fa-check text-3xl text-emerald-500"></i></div><h3 class="text-2xl font-black text-slate-800 mb-2">Perfetto!</h3><p class="text-sm text-slate-600" x-text="successMessage"></p></div></div></div>
        </div>

        <div x-show="showErrorModal" x-cloak class="modal-overlay" style="z-index: 1065;" :class="{ 'modal-active': showErrorModal, 'modal-out': isClosingError }">
            <div @click="closeErrorModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="modal-container w-full max-w-md"><div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"><div class="p-6 text-center pt-8 pb-6"><div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-5 animate-pulse"><i class="fa-solid fa-circle-exclamation text-3xl text-red-500"></i></div><h3 class="text-2xl font-black text-slate-800 mb-2">Errore</h3><p class="text-sm text-slate-600" x-text="errorMessage"></p></div></div></div>
        </div>

        <div x-show="showEventModal" x-cloak class="modal-overlay" style="z-index: 1060;" :class="{ 'modal-active': showEventModal && !isClosingEvent, 'modal-out': isClosingEvent }" @keydown.escape.window="closeEventModal()">
            <div @click="closeEventModal()" class="modal-backdrop bg-slate-900/70 backdrop-blur-sm"></div>
            <div class="modal-container w-full max-w-sm pointer-events-auto">
                <div @click.stop class="modal-content-wrapper bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col relative">
                    <div class="h-2.5 w-full" :style="`background-color: ${eventData.color}`"></div>
                    <div class="p-7">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-slate-50 border border-slate-100 shadow-sm" :style="`color: ${eventData.color}`"><i class="fa-regular fa-calendar-check text-3xl"></i></div>
                            <button @click="closeEventModal()" class="text-slate-400 hover:text-slate-600 transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark text-lg"></i></button>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-tight mb-1" x-text="eventData.title"></h3><p class="text-sm font-semibold text-slate-500 capitalize mb-7" x-text="eventData.date"></p>
                        <div class="space-y-4 bg-slate-50 p-5 rounded-2xl border border-slate-100 shadow-inner">
                            <div class="flex justify-between items-center"><span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stato</span><span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold shadow-sm" :class="eventData.pagato ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'"><i class="fa-solid mr-1.5" :class="eventData.pagato ? 'fa-check' : 'fa-triangle-exclamation'"></i><span x-text="eventData.pagato ? 'COMPLETATO' : 'IN SCADENZA'"></span></span></div>
                            <div class="flex justify-between items-center border-t border-slate-200 pt-4"><span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Importo</span><span class="text-xl font-black text-slate-800">€ <span x-text="eventData.importo"></span></span></div>
                        </div>
                    </div>
                    <div class="px-7 pb-7"><button @click="closeEventModal()" class="w-full py-4 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-700 transition-transform active:scale-95 shadow-md">Chiudi Dettagli</button></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let chartMensileInstance = null;
        let chartTipoInstance = null;
        let calendarInstance = null;

        function dashboardManager() {
            return {
                selectedYear: {{ $selectedYear }},
                currentYear: {{ now()->year }},
                isLoading: false,

                // Dati passati dal server
                stats: @json($stats),
                allExpenses: @json($jsExpenses),
                
                // Variabili Modali...
                showUploadModal: false, showViewModal: false, showPreviewModal: false, showSuccessModal: false, showErrorModal: false,
                successMessage: '', errorMessage: '',
                isClosingUpload: false, isClosingView: false, isPreviewClosing: false, isClosingSuccess: false, isClosingError: false,
                selectedCategory: 'bollo', currentDocUrl: '', currentDocType: '', currentDocName: '', currentFolder: null,
                folders: [
                    { id: 'bollo', name: 'Bolli', icon: 'fa-file-invoice-dollar', color: 'text-yellow-500', bg: 'bg-yellow-50', hover: 'hover:border-yellow-300' },
                    { id: 'assicurazione', name: 'Assicurazioni', icon: 'fa-shield-halved', color: 'text-orange-500', bg: 'bg-orange-50', hover: 'hover:border-orange-300' },
                    { id: 'revisione', name: 'Revisioni', icon: 'fa-screwdriver-wrench', color: 'text-green-500', bg: 'bg-green-50', hover: 'hover:border-green-300' },
                    { id: 'patente', name: 'Patenti', icon: 'fa-id-card', color: 'text-pink-500', bg: 'bg-pink-50', hover: 'hover:border-pink-300' },
                    { id: 'carta_identita', name: 'Carte d\'Identità', icon: 'fa-passport', color: 'text-stone-500', bg: 'bg-stone-50', hover: 'hover:border-stone-300' },
                    { id: 'tesserino_sanitario', name: 'Tess. Sanitari', icon: 'fa-notes-medical', color: 'text-teal-500', bg: 'bg-teal-50', hover: 'hover:border-teal-300' },
                    { id: 'altro', name: 'Altro', icon: 'fa-folder-open', color: 'text-indigo-500', bg: 'bg-indigo-50', hover: 'hover:border-indigo-300' }
                ],
                archStats: @json($categorieStats), documents: [],
                showDeleteDocModal: false, isClosingDeleteDoc: false, docToDeleteId: null, docToDeleteName: '',
                showEventModal: false, isClosingEvent: false, eventData: {},

                // FILTRI
                activeFilter: 'all', 
                chartFilterType: null, 
                chartFilterValue: null, 
                mesiNomi: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],

                // LA CHIAMATA AJAX MAGICA
                async changeYear(year) {
                    // console.log("🔄 Richiesto cambio anno a:", year); // <-- LOG
                    this.isLoading = true;
                    
                    window.history.pushState(null, '', '?year=' + year);
                    
                    try {
                        const response = await fetch(`?year=${year}`, {
                            headers: { 
                                'X-Requested-With': 'XMLHttpRequest', 
                                'Accept': 'application/json' 
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            // console.log("✅ Dati ricevuti dal server:", data); // <-- LOG
                            
                            this.stats = data.stats;
                            this.allExpenses = data.jsExpenses;
                            this.selectedYear = year; 
                            
                            this.activeFilter = 'all';
                            this.chartFilterType = null;
                            
                            this.updateCharts();
                            this.updateCalendar(); 
                        } else {
                            // console.error("❌ Errore dal server:", response.status);
                        }
                    } catch (error) {
                        // console.error("❌ Errore di connessione AJAX:", error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                get displayedList() {
                    let list = this.allExpenses;
                    if (this.chartFilterType === 'mese') return list.filter(e => e.mese === this.chartFilterValue).sort((a,b) => a.giorni - b.giorni);
                    if (this.chartFilterType === 'categoria') return list.filter(e => e.tipo === this.chartFilterValue).sort((a,b) => a.giorni - b.giorni);
                    if (this.activeFilter === 'urgente') return list.filter(e => e.giorni <= 7 && !e.pagato).sort((a,b) => a.giorni - b.giorni);
                    if (this.activeFilter === 'imminente') return list.filter(e => e.giorni <= 30 && !e.pagato).sort((a,b) => a.giorni - b.giorni);
                    return list.filter(e => !e.pagato).sort((a,b) => a.giorni - b.giorni);
                },

                get listTitle() {
                    if (this.chartFilterType === 'mese') return 'Scadenze di ' + this.mesiNomi[this.chartFilterValue];
                    if (this.chartFilterType === 'categoria') {
                        let cat = this.chartFilterValue.replace('_', ' ');
                        return 'Categoria: ' + cat.charAt(0).toUpperCase() + cat.slice(1);
                    }
                    if (this.activeFilter === 'urgente') return 'Solo Scadenze Urgenti';
                    if (this.activeFilter === 'imminente') return 'Solo in Arrivo (30gg)';
                    return 'Tutte da Pagare (' + this.selectedYear + ')';
                },

                setTopFilter(type) { 
                    this.activeFilter = this.activeFilter === type ? 'all' : type;
                    this.chartFilterType = null;
                    this.updateCharts();
                },

                setChartFilter(type, value) {
                    if (this.chartFilterType === type && this.chartFilterValue === value) {
                        this.chartFilterType = null;
                    } else {
                        this.chartFilterType = type;
                        this.chartFilterValue = value;
                        this.activeFilter = 'all';
                    }
                },

                resetFilters() {
                    this.activeFilter = 'all';
                    this.chartFilterType = null;
                    this.updateCharts();
                },
                
                init() { 
                    this.refreshData(); 
                    this.$nextTick(() => { 
                        this.initCharts(); 
                        this.initCalendar();
                    });
                },

                initCharts() {
                    Chart.defaults.font.family = "'Figtree', sans-serif";
                    Chart.defaults.color = '#64748b';

                    if (chartMensileInstance) chartMensileInstance.destroy();
                    if (chartTipoInstance) chartTipoInstance.destroy();

                    chartMensileInstance = new Chart(document.getElementById('speseMensiliChart').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
                            datasets: [
                                { label: 'Pagato', data: Array(12).fill(0), backgroundColor: '#10b981', borderRadius: 4, barPercentage: 0.6 },
                                { label: 'Da Pagare', data: Array(12).fill(0), backgroundColor: '#ef4444', borderRadius: 4, barPercentage: 0.6 }
                            ]
                        },
                        options: { 
                            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, 
                            scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true } },
                            onHover: (e, elements) => { e.native.target.style.cursor = elements.length ? 'pointer' : 'default'; },
                            onClick: (e, elements) => { if (elements.length > 0) this.setChartFilter('mese', elements[0].index); }
                        }
                    });

                    chartTipoInstance = new Chart(document.getElementById('spesePerTipoChart').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Bollo', 'Assicurazione', 'Patente', 'Revisione', 'Abbonamenti'],
                            datasets: [{
                                data: Array(5).fill(0), 
                                backgroundColor: [
                                    '#eab308', // Giallo Bollo
                                    '#3b82f6', // Blu Assicurazione
                                    '#ec4899', // Rosa Patente
                                    '#22c55e', // Verde Revisione
                                    '#6366f1'  // Indigo Abbonamenti (Nuovo!)
                                ], 
                                borderWidth: 0, 
                                hoverOffset: 10
                            }]
                        },
                        options: { 
                            responsive: true, maintainAspectRatio: false, cutout: '70%', 
                            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } },
                            onHover: (e, elements) => { e.native.target.style.cursor = elements.length ? 'pointer' : 'default'; },
                            onClick: (e, elements) => {
                                if (elements.length > 0) {
                                    // ORDINE CATEGORIE AGGIORNATO
                                    const categorie = ['bollo', 'assicurazione', 'patente', 'revisione', 'spesa_abbonamento'];
                                    this.setChartFilter('categoria', categorie[elements[0].index]);
                                }
                            }
                        }
                    });
                    this.updateCharts();
                },

                updateCharts() {
                    if (!chartMensileInstance || !chartTipoInstance) return;

                    let filtered = this.allExpenses;
                    if (this.activeFilter === 'urgente') filtered = filtered.filter(e => e.giorni <= 7 && !e.pagato);
                    else if (this.activeFilter === 'imminente') filtered = filtered.filter(e => e.giorni <= 30 && !e.pagato);

                    let pagatoMese = Array(12).fill(0);
                    let daPagareMese = Array(12).fill(0);
                    
                    filtered.forEach(e => {
                        if (e.pagato) pagatoMese[e.mese] += e.importo;
                        else daPagareMese[e.mese] += e.importo;
                    });
                    
                    chartMensileInstance.data.datasets[0].data = pagatoMese;
                    chartMensileInstance.data.datasets[1].data = daPagareMese;
                    chartMensileInstance.update();

                    // --- CALCOLO SOMME PER CATEGORIA ---
                    let spese = { 
                        bollo: 0, 
                        assicurazione: 0, 
                        patente: 0, 
                        revisione: 0, 
                        spesa_abbonamento: 0 
                    };

                    filtered.forEach(e => { 
                        if (spese[e.tipo] !== undefined) {
                            spese[e.tipo] += e.importo; 
                        }
                    });
                    
                    chartTipoInstance.data.datasets[0].data = [
                        spese.bollo, 
                        spese.assicurazione, 
                        spese.patente, 
                        spese.revisione, 
                        spese.spesa_abbonamento
                    ];
                    chartTipoInstance.update();
                },

                // CALENDARIO INTEGRATO IN ALPINE (Per aggiornarsi in tempo reale)
                initCalendar() {
                    const calendarEl = document.getElementById('calendar');
                    if (!calendarEl) return;
                    
                    // Distruggiamo il vecchio calendario se esiste già
                    if (calendarInstance) calendarInstance.destroy();
                    
                    calendarInstance = new FullCalendar.Calendar(calendarEl, {
                        locale: 'it',
                        initialView: 'dayGridMonth',
                        initialDate: this.selectedYear == this.currentYear ? new Date().toISOString().split('T')[0] : `${this.selectedYear}-01-01`,
                        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listYear' },
                        buttonText: { today: 'Oggi', month: 'Mese', list: 'Lista' },
                        events: this.getCalendarEvents(),
                        eventClick: (info) => {
                            this.openEventModal({
                                title: info.event.title, 
                                date: info.event.start.toLocaleDateString('it-IT', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }), 
                                color: info.event.backgroundColor, 
                                importo: info.event.extendedProps.importo, 
                                pagato: info.event.extendedProps.pagato
                            });
                        }
                    });
                    calendarInstance.render();
                },

                getCalendarEvents() {
                    return this.allExpenses.map(e => {
                        let parts = e.data_formattata.split('/');
                        return {
                            title: e.titolo + ' - ' + e.tipo_label,
                            start: `${parts[2]}-${parts[1]}-${parts[0]}`,
                            color: e.pagato ? '#10b981' : (e.is_scaduto ? '#ef4444' : e.tipo_color),
                            allDay: true,
                            extendedProps: { tipo: e.tipo_label, importo: e.importo_formattato, pagato: e.pagato }
                        };
                    });
                },

                updateCalendar() {
                    if (calendarInstance) {
                        calendarInstance.removeAllEvents();
                        calendarInstance.addEventSource(this.getCalendarEvents());
                        calendarInstance.gotoDate(`${this.selectedYear}-01-01`);
                    }
                },

                // METODI ARCHIVIO
                openFolder(folderId) { this.currentFolder = folderId; },
                closeFolder() { this.currentFolder = null; },
                getDocsInFolder(folderId) { return this.documents.filter(d => d.categoria === folderId); },
                get currentFolderDocs() { if (!this.currentFolder) return []; return this.documents.filter(d => d.categoria === this.currentFolder); },
                get currentFolderInfo() { return this.folders.find(f => f.id === this.currentFolder) || {}; },
                async refreshData() {
                    try {
                        let resStats = await fetch('{{ route("dashboard.stats") }}');
                        if (resStats.ok) this.archStats = await resStats.json();
                        let resDocs = await fetch('{{ route("dashboard.documents") }}');
                        if (resDocs.ok) { let dataDocs = await resDocs.json(); this.documents = dataDocs.documents; }
                    } catch (e) { console.error('Errore fetch dati:', e); }
                },
                openUploadModal() { this.showUploadModal = true; },
                closeUploadModal() { this.isClosingUpload = true; setTimeout(() => { this.showUploadModal = false; this.isClosingUpload = false; this.selectedCategory = 'bollo'; }, 500); },
                openViewModal() { this.showViewModal = true; },
                closeViewModal() { this.isClosingView = true; setTimeout(() => { this.showViewModal = false; this.isClosingView = false; this.currentFolder = null; }, 500); },
                openPreview(url, type, name) { this.currentDocUrl = url; this.currentDocType = type; this.currentDocName = name; this.showPreviewModal = true; },
                closePreview() { this.isPreviewClosing = true; setTimeout(() => { this.showPreviewModal = false; this.isPreviewClosing = false; this.currentDocUrl = ''; }, 500); },
                openDeleteDoc(id, name) { this.docToDeleteId = id; this.docToDeleteName = name; this.showDeleteDocModal = true; },
                closeDeleteDoc() { this.isClosingDeleteDoc = true; setTimeout(() => { this.showDeleteDocModal = false; this.isClosingDeleteDoc = false; this.docToDeleteId = null; }, 500); },
                openEventModal(data) { this.eventData = data; this.showEventModal = true; },
                closeEventModal() { this.isClosingEvent = true; setTimeout(() => { this.showEventModal = false; this.isClosingEvent = false; }, 500); },
                async handleDocumentSubmit(event) {
                    event.preventDefault(); const formData = new FormData(event.target);
                    try {
                        const response = await fetch('{{ route("documents.store") }}', { method: 'POST', body: formData });
                        const data = await response.json();
                        if (response.ok) { event.target.reset(); this.selectedCategory = 'bollo'; this.closeUploadModal(); await this.refreshData(); this.successMessage = data.message || 'Documento caricato con successo!'; this.showSuccessModal = true; setTimeout(() => { if(this.showSuccessModal) this.closeSuccessModal(); }, 2000); } else { this.showError(data.message || 'Errore nel caricamento del documento'); }
                    } catch (error) { this.showError('Errore nella comunicazione con il server.'); }
                },
                async handleDocumentDelete(event) {
                    try {
                        const response = await fetch(`/documents/${this.docToDeleteId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' } });
                        const data = await response.json();
                        if (response.ok) { this.closeDeleteDoc(); await this.refreshData(); this.successMessage = data.message || 'Documento eliminato con successo!'; this.showSuccessModal = true; setTimeout(() => { if(this.showSuccessModal) this.closeSuccessModal(); }, 2000); } else { this.closeDeleteDoc(); this.showError(data.message || 'Errore durante l\'eliminazione del documento'); }
                    } catch (error) { this.closeDeleteDoc(); this.showError('Errore nella comunicazione con il server.'); }
                },
                closeSuccessModal() { this.isClosingSuccess = true; setTimeout(() => { this.showSuccessModal = false; this.isClosingSuccess = false; this.successMessage = ''; }, 300); },
                closeErrorModal() { this.isClosingError = true; setTimeout(() => { this.showErrorModal = false; this.isClosingError = false; this.errorMessage = ''; }, 300); },
                showError(message) { this.errorMessage = message; this.showErrorModal = true; setTimeout(() => { if(this.showErrorModal) this.closeErrorModal(); }, 4000); }
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/it.global.min.js'></script>

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