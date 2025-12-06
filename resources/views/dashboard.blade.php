<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-tight">
                    Dashboard Amministrativa
                </h2>
                <p class="text-sm text-slate-500 mt-1">Monitoraggio veicoli e scadenze famiglia</p>
            </div>
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200 text-sm text-slate-600 font-medium flex items-center gap-2">
                <i class="fa-regular fa-calendar text-indigo-500"></i>
                {{ now()->locale('it')->isoFormat('dddd, D MMMM YYYY') }}
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen w-full"
         x-data="{ 
            showUploadModal: false,
            showViewModal: false,
            showPreviewModal: false,
            isClosingUpload: false,
            isClosingView: false,
            isPreviewClosing: false,
            
            // Variabili per l'upload
            selectedCategory: 'bollo',
            
            // Variabili per la visualizzazione anteprima
            currentDocUrl: '',
            currentDocType: '',
            currentDocName: '',

            // Funzioni Upload Modal
            openUploadModal() { this.showUploadModal = true; },
            closeUploadModal() {
                this.isClosingUpload = true;
                setTimeout(() => {
                    this.showUploadModal = false;
                    this.isClosingUpload = false;
                    this.selectedCategory = 'bollo';
                }, 500);
            },

            // Funzioni View Archive Modal
            openViewModal() { this.showViewModal = true; },
            closeViewModal() {
                this.isClosingView = true;
                setTimeout(() => {
                    this.showViewModal = false;
                    this.isClosingView = false;
                }, 500);
            },

            // Funzioni Preview Modal
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
            }
         }">
         
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
                            Vedi Archivio ({{ \App\Models\Document::count() }})
                        </button>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        @foreach(['bollo' => ['name' => 'Bolli', 'color' => 'amber'], 'assicurazione' => ['name' => 'Assic.', 'color' => 'blue'], 'revisione' => ['name' => 'Rev.', 'color' => 'orange'], 'patente' => ['name' => 'Patenti', 'color' => 'purple']] as $tipo => $info)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-xs font-bold text-slate-700 uppercase">{{ $info['name'] }}</span>
                                <span class="text-xs font-bold text-slate-700">{{ $categorieStats[$tipo]['percentuale'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="bg-{{ $info['color'] }}-500 h-1.5 rounded-full" style="width: {{ $categorieStats[$tipo]['percentuale'] }}%"></div>
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
                <h3 class="font-bold text-lg text-slate-800 mb-4">Calendario</h3>
                <div id="calendar"></div>
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
                    
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo di Documento</label>
                            <select name="categoria" x-model="selectedCategory" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="bollo">Bollo Auto</option>
                                <option value="assicurazione">Polizza Assicurativa</option>
                                <option value="revisione">Revisione</option>
                                <option value="patente">Patente</option>
                                <option value="altro">Altro</option>
                            </select>
                        </div>

                        <div x-show="selectedCategory !== 'patente'" x-transition>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Associa al Veicolo</label>
                            <select name="vehicle_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">-- Seleziona Veicolo --</option>
                                @foreach(\App\Models\Vehicle::all() as $v)
                                    <option value="{{ $v->id }}">{{ $v->targa }} - {{ $v->marca }} {{ $v->modello }}</option>
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
            <div class="modal-container w-full max-w-4xl"> <div @click.stop class="modal-content-wrapper bg-white rounded-xl shadow-2xl overflow-hidden h-[80vh] flex flex-col">
                    <div class="modal-header bg-slate-800 px-6 py-4 flex justify-between items-center flex-shrink-0">
                        <h3 class="text-lg font-bold text-white">Archivio Documenti</h3>
                        <button @click="closeViewModal()" class="text-white hover:text-gray-300 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                        @php 
                            // Query diretta per popolare la modale senza modificare il controller
                            $docs = \App\Models\Document::with('vehicle')->orderBy('created_at', 'desc')->get(); 
                        @endphp
                        
                        @if($docs->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($docs as $doc)
                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow relative group">
                                    <div class="flex items-start gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg flex-shrink-0
                                            {{ $doc->tipo_file == 'application/pdf' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                            <i class="fa-solid {{ $doc->tipo_file == 'application/pdf' ? 'fa-file-pdf' : 'fa-file-image' }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-800 truncate" title="{{ $doc->nome_originale }}">{{ $doc->nome_originale }}</p>
                                            <p class="text-xs text-gray-500 uppercase font-semibold">{{ $doc->categoria }}</p>
                                            <p class="text-xs text-gray-400">{{ $doc->created_at->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        @if($doc->vehicle)
                                            <span class="inline-block px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded">{{ $doc->vehicle->targa }}</span>
                                        @else
                                            <span class="inline-block px-2 py-1 bg-purple-100 text-purple-600 text-xs font-bold rounded">Personale / Patente</span>
                                        @endif
                                    </div>

                                    <div class="flex gap-2">
                                        <button @click="openPreview('{{ Storage::url($doc->path) }}', '{{ $doc->tipo_file }}', '{{ $doc->nome_originale }}')" 
                                                class="flex-1 py-1.5 bg-indigo-50 text-indigo-600 rounded-md text-xs font-bold hover:bg-indigo-100 transition-colors flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-eye"></i> Vedi
                                        </button>
                                        
                                        <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="flex-shrink-0" onsubmit="return confirm('Sei sicuro di voler eliminare questo file?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-500 rounded-md hover:bg-red-100 transition-colors">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-gray-400">
                                <i class="fa-regular fa-folder-open text-6xl mb-4"></i>
                                <p>Nessun documento in archivio.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showPreviewModal" x-cloak class="modal-overlay" style="z-index: 1050;" 
             :class="{ 'modal-active': showPreviewModal && !isPreviewClosing, 'modal-out': isPreviewClosing }">
            <div @click="closePreview()" class="modal-backdrop bg-black/80"></div> <div class="modal-container w-full max-w-5xl h-[90vh]">
                <div @click.stop class="modal-content-wrapper bg-white rounded-xl shadow-2xl overflow-hidden h-full flex flex-col">
                    <div class="bg-gray-900 px-4 py-3 flex justify-between items-center text-white flex-shrink-0">
                        <h3 class="font-bold truncate" x-text="currentDocName"></h3>
                        <button @click="closePreview()" class="hover:text-gray-300"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>

                    <div class="flex-1 bg-gray-200 flex items-center justify-center overflow-hidden p-2">
                        <template x-if="currentDocType === 'application/pdf'">
                            <iframe :src="currentDocUrl" class="w-full h-full rounded shadow-lg border-0"></iframe>
                        </template>
                        
                        <template x-if="currentDocType !== 'application/pdf'">
                            <img :src="currentDocUrl" class="max-w-full max-h-full object-contain rounded shadow-lg">
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/it.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Impostazioni globali Chart.js
            Chart.defaults.font.family = "'Figtree', sans-serif";
            Chart.defaults.color = '#64748b';

            // 1. GRAFICO SPESE MENSILI (Stacked Bar)
            const ctxMensili = document.getElementById('speseMensiliChart').getContext('2d');
            new Chart(ctxMensili, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($speseMensili, 'mese')) !!},
                    datasets: [
                        {
                            label: 'Pagato',
                            data: {!! json_encode(array_column($speseMensili, 'pagato')) !!},
                            backgroundColor: '#10b981', // emerald-500
                            borderRadius: 4,
                            barPercentage: 0.6,
                        },
                        {
                            label: 'Da Pagare',
                            data: {!! json_encode(array_column($speseMensili, 'da_pagare')) !!},
                            backgroundColor: '#ef4444', // red-500
                            borderRadius: 4,
                            barPercentage: 0.6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': €' + context.parsed.y.toLocaleString('it-IT', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false },
                            ticks: { font: { weight: '600' } }
                        },
                        y: {
                            stacked: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(value) { return '€' + value; }
                            }
                        }
                    }
                }
            });

            // 2. GRAFICO TORTA (Doughnut)
            const ctxTipo = document.getElementById('spesePerTipoChart').getContext('2d');
            new Chart(ctxTipo, {
                type: 'doughnut',
                data: {
                    labels: ['Bollo', 'Assicurazione', 'Patente', 'Revisione'],
                    datasets: [{
                        data: [
                            {{ $spesePerTipo['bollo'] ?? 0 }},
                            {{ $spesePerTipo['assicurazione'] ?? 0 }},
                            {{ $spesePerTipo['patente'] ?? 0 }},
                            {{ $spesePerTipo['revisione'] ?? 0 }}
                        ],
                        backgroundColor: [
                            '#fbbf24', // amber
                            '#3b82f6', // blue
                            '#a855f7', // purple
                            '#f97316'  // orange
                        ],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 20 }
                        }
                    }
                }
            });

            // 3. CALENDARIO
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'it',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listYear'
                },
                buttonText: {
                    today: 'Oggi',
                    month: 'Mese',
                    list: 'Lista'
                },
                events: @json($calendarEvents),
                height: 600,
                eventClick: function(info) {
                    const stato = info.event.extendedProps.pagato ? '✅ PAGATO' : '❌ DA PAGARE';
                    const importo = info.event.extendedProps.importo.toLocaleString('it-IT', {minimumFractionDigits: 2});
                    
                    alert(
                        info.event.title + 
                        '\nData: ' + info.event.start.toLocaleDateString('it-IT') +
                        '\nImporto: € ' + importo +
                        '\nStato: ' + stato
                    );
                }
            });
            calendar.render();
        });
    </script>

    <style>
        /* CSS Specifico per Modali (Copiato dal tuo index.blade.php) e Scrollbar */
        [x-cloak] { display: none !important; }
        
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem;
        }
        .modal-backdrop {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0);
        }
        .modal-active .modal-backdrop { animation: fadeIn 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-backdrop { animation: fadeOut 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        
        .modal-container { position: relative; width: 100%; max-width: 28rem; z-index: 1001; }
        
        .modal-content-wrapper {
            background: white; border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden; transform: scale(0);
        }
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.5s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }

        @keyframes fadeIn { 0% { background: rgba(0,0,0,0); } 100% { background: rgba(0,0,0,0.7); } }
        @keyframes fadeOut { 0% { background: rgba(0,0,0,0.7); } 100% { background: rgba(0,0,0,0); } }
        @keyframes blowUpModal { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        /* FullCalendar Custom Overrides */
        .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700; color: #1f2937; }
        .fc-button-primary { background-color: #4f46e5 !important; border-color: #4f46e5 !important; }
        .fc-day-today { background-color: #eef2ff !important; }
        .fc-event { border: none; padding: 2px 4px; font-size: 0.85em; cursor: pointer; }
    </style>
</x-app-layout>