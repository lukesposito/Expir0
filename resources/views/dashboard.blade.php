<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 w-full">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-tight">
                    Dashboard Amministrativa
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Monitoraggio veicoli e scadenze famiglia
                </p>
            </div>

            <button
                type="button"
                @click="$dispatch('open-action-launcher')"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2"
            >
                <i class="fa-solid fa-plus"></i>
                <span>Cosa vuoi fare?</span>
            </button>

            <button 
                x-data
                @click="$dispatch('open-ai-analyst')" 
                class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg hover:shadow-xl transition-all flex items-center gap-2 group">
                <span class="group-hover:animate-bounce">🪄</span>
                Analista AI
            </button>
            
            <div class="flex items-center gap-3">
                <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200 text-sm text-slate-600 font-medium hidden md:flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-indigo-500"></i>
                    {{ now()->locale('it')->isoFormat('dddd, D MMMM YYYY') }}
                </div>
                
            <div
                class="relative"
                x-data="{
                    open: false,
                    selected: '{{ $selectedYear }}'
                }"
                @click.outside="open = false"
                @keydown.escape.window="open = false"
            >
                <button
                    type="button"
                    @click="open = !open"
                    :aria-expanded="open.toString()"
                    class="group relative inline-flex items-center gap-3 rounded-[1.15rem] bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-600 px-4 py-2.5 text-white shadow-[0_14px_30px_-14px_rgba(79,70,229,0.65)] transition duration-300 hover:-translate-y-[1px] hover:shadow-[0_18px_34px_-14px_rgba(79,70,229,0.78)] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
                >
                    <span class="text-sm font-black tracking-[-0.02em]">
                        Anno <span x-text="selected"></span>
                    </span>

                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-white/14 transition duration-300"
                        :class="open ? 'rotate-180 bg-white/20' : ''"
                    >
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </span>

                    <span class="pointer-events-none absolute inset-0 rounded-[1.15rem] ring-1 ring-white/10 ring-inset"></span>
                </button>

                <div
                    x-cloak
                    x-show="open"
                    x-transition:enter="transition ease-[cubic-bezier(.22,1,.36,1)] duration-250"
                    x-transition:enter-start="opacity-0 -translate-y-2 scale-[.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-180"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-2 scale-[.98]"
                    class="absolute right-0 z-50 mt-3 w-56 overflow-hidden rounded-[1.3rem] border border-indigo-200/70 bg-white/92 p-2 shadow-[0_22px_44px_-18px_rgba(79,70,229,0.35)] backdrop-blur-xl"
                >
                    <div class="mb-1 px-3 pt-2 pb-1 text-[0.68rem] font-bold uppercase tracking-[0.22em] text-indigo-400">
                        Seleziona anno
                    </div>

                    <div class="space-y-1">
                        @foreach($availableYears ?? [now()->year] as $year)
                            <button
                                type="button"
                                @click="
                                    selected = '{{ $year }}';
                                    open = false;
                                    $dispatch('change-dashboard-year', '{{ $year }}');
                                "
                                :class="selected == '{{ $year }}'
                                    ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-[0_10px_24px_-16px_rgba(79,70,229,0.55)]'
                                    : 'text-slate-700 hover:bg-indigo-50 hover:text-indigo-700'"
                                class="flex w-full items-center justify-between rounded-xl px-3.5 py-3 text-left text-sm font-bold transition duration-200"
                            >
                                <span>Anno {{ $year }}</span>

                                <span
                                    x-show="selected == '{{ $year }}'"
                                    x-cloak
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15 text-[11px]"
                                >
                                    <i class="fa-solid fa-check"></i>
                                </span>

                                <span
                                    x-show="selected != '{{ $year }}'"
                                    class="text-slate-300"
                                >
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div
        class="py-8 bg-slate-50 min-h-screen w-full relative"
        x-data="dashboardManager()"
        @change-dashboard-year.window="changeYear($event.detail)"
        @open-action-launcher.window="openActionLauncher()"
    >
        
        <div x-show="isLoading" x-transition.opacity.duration.300ms class="absolute inset-0 bg-slate-50/60 backdrop-blur-[2px] z-50 flex items-start justify-center pt-20 rounded-2xl" x-cloak>
            <div class="bg-indigo-600 text-white px-6 py-3 rounded-full font-bold shadow-lg flex items-center gap-3">
                <i class="fa-solid fa-circle-notch fa-spin text-xl"></i> Sincronizzazione Dati...
            </div>
        </div>
         
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8" :class="{ 'opacity-50 pointer-events-none': isLoading }">

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-6">
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

                <a
                    href="{{ route('action-proposals.index') }}"
                    class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-6 relative overflow-hidden group hover:shadow-md transition-all cursor-pointer transform hover:-translate-y-1 block"
                    :class="Number(stats.azioniDaVerificare || 0) > 0
                        ? 'ring-2 ring-indigo-100'
                        : ''"
                >
                    <div
                        class="absolute right-0 top-0 h-full w-1"
                        :class="Number(stats.azioniDaVerificare || 0) > 0
                            ? 'bg-indigo-500'
                            : 'bg-slate-300'"
                    ></div>

                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                                Azioni da verificare
                            </p>

                            <h3
                                class="text-3xl font-black mt-2"
                                :class="Number(stats.azioniDaVerificare || 0) > 0
                                    ? 'text-indigo-600'
                                    : 'text-slate-800'"
                                x-text="stats.azioniDaVerificare || 0"
                            ></h3>

                            <p
                                class="mt-2 text-xs font-semibold"
                                :class="Number(stats.azioniDaVerificare || 0) > 0
                                    ? 'text-indigo-600'
                                    : 'text-slate-400'"
                                x-text="Number(stats.azioniDaVerificare || 0) > 0
                                    ? 'Azioni già preparate per te'
                                    : 'Tutto sotto controllo'"
                            ></p>
                        </div>

                        <div
                            class="p-3 rounded-xl transition-colors"
                            :class="Number(stats.azioniDaVerificare || 0) > 0
                                ? 'bg-indigo-600 text-white'
                                : 'bg-slate-100 text-slate-400'"
                        >
                            <i class="fa-solid fa-wand-magic-sparkles text-xl"></i>
                        </div>
                    </div>

                    <div
                        x-show="Number(stats.azioniDaVerificare || 0) > 0"
                        x-cloak
                        class="absolute bottom-4 right-5 flex items-center gap-1.5 text-xs font-black text-indigo-600"
                    >
                        Apri
                        <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>

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

            <div
                x-show="expenseForecast.peak_month"
                x-cloak
                class="relative overflow-hidden rounded-2xl border border-rose-100 bg-white p-6 shadow-sm"
            >
                <div class="absolute inset-y-0 left-0 w-1 bg-rose-500"></div>

                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                            <i class="fa-solid fa-piggy-bank text-xl"></i>
                        </div>

                        <div class="min-w-0">
                            <div
                                class="flex items-center gap-2"
                                x-data="{ showForecastInfo: false }"
                                @click.outside="showForecastInfo = false"
                            >
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-500">
                                    Mese con più uscite previste
                                </p>

                                <button
                                    type="button"
                                    @click.stop="showForecastInfo = !showForecastInfo"
                                    class="relative flex h-5 w-5 items-center justify-center rounded-full bg-rose-100 text-[11px] font-black text-rose-600 transition hover:bg-rose-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400"
                                    aria-label="Spiegazione della previsione spese"
                                    :aria-expanded="showForecastInfo.toString()"
                                >
                                    <i class="fa-solid fa-info text-[10px]"></i>
                                </button>

                                <div
                                    x-show="showForecastInfo"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                    class="absolute left-0 top-7 z-50 w-[min(20rem,calc(100vw-3rem))] rounded-2xl border border-rose-100 bg-white p-4 text-left shadow-xl"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                                            <i class="fa-solid fa-piggy-bank text-sm"></i>
                                        </div>

                                        <div>
                                            <p class="text-sm font-black text-slate-900">
                                                Come viene calcolata
                                            </p>

                                            <p class="mt-1 text-xs leading-relaxed text-slate-600">
                                                Il sistema somma le scadenze non pagate e i rinnovi futuri stimati dei prossimi 12 mesi.
                                            </p>

                                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                                I rinnovi futuri sono una previsione: vengono calcolati dalla frequenza impostata per ogni abbonamento e non rappresentano pagamenti già effettuati.
                                            </p>

                                            <p class="mt-2 text-xs font-semibold leading-relaxed text-rose-700">
                                                L’accantonamento è un suggerimento, non un importo obbligatorio.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h3
                                class="mt-1 text-xl font-black text-slate-900"
                                x-text="expenseForecast.peak_month?.label"
                            ></h3>

                            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                Il mese con più uscite previste:
                                <span
                                    class="font-black text-slate-900"
                                    x-text="expenseForecast.peak_month?.count || 0"
                                ></span>
                                scadenze per
                                <span
                                    class="font-black text-rose-600"
                                    x-text="Number(expenseForecast.peak_month?.total || 0).toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })"
                                ></span>.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-rose-50 px-5 py-4 lg:max-w-sm">
                        <template x-if="!activeSavingsPlan">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-rose-500">
                                    Suggerimento mensile
                                </p>

                                <p
                                    class="mt-1 text-2xl font-black text-rose-700"
                                    x-text="Number(expenseForecast.suggested_monthly_reserve || 0).toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })"
                                ></p>

                                <p class="mt-1 text-xs leading-relaxed text-rose-700">
                                    Per coprire in media le uscite previste nei prossimi 12 mesi.
                                </p>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        @click="openForecastDetail()"
                                        class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-black text-rose-700 shadow-sm transition hover:bg-rose-100"
                                    >
                                        <i class="fa-solid fa-list-check"></i>
                                        Vedi dettaglio
                                    </button>

                                    <button
                                        type="button"
                                        @click="createSavingsPlan()"
                                        :disabled="isCreatingSavingsPlan"
                                        class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-rose-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <i class="fa-solid fa-piggy-bank"></i>

                                        <span
                                            x-text="isCreatingSavingsPlan
                                                ? 'Creazione...'
                                                : 'Crea piano'"
                                        ></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="activeSavingsPlan">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-rose-500">
                                    Piano attivo
                                </p>

                                <p
                                    class="mt-1 text-lg font-black text-rose-700"
                                    x-text="monthName(activeSavingsPlan.target_month) + ' ' + activeSavingsPlan.target_year"
                                ></p>

                                <div class="mt-3">
                                    <div class="flex items-center justify-between gap-3 text-xs font-bold text-rose-700">
                                        <span>
                                            Accantonato
                                            <span x-text="formatCurrency(activeSavingsPlan.saved_amount || 0)"></span>
                                        </span>

                                        <span
                                            x-text="savingPlanProgress(activeSavingsPlan) + '%'"
                                        ></span>
                                    </div>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-rose-200">
                                        <div
                                            class="h-full rounded-full bg-rose-600 transition-all duration-500"
                                            :style="`width: ${savingPlanProgress(activeSavingsPlan)}%`"
                                        ></div>
                                    </div>

                                    <p class="mt-2 text-xs leading-relaxed text-rose-700">
                                        Manca
                                        <span
                                            class="font-black"
                                            x-text="formatCurrency(savingPlanRemaining(activeSavingsPlan))"
                                        ></span>
                                        per raggiungere l’obiettivo di
                                        <span
                                            class="font-black"
                                            x-text="formatCurrency(activeSavingsPlan.target_amount)"
                                        ></span>.
                                    </p>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        @click="addSavingsContribution()"
                                        :disabled="isAddingSavingsContribution"
                                        class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-rose-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <i class="fa-solid fa-plus"></i>

                                        <span
                                            x-text="isAddingSavingsContribution
                                                ? 'Registrazione...'
                                                : 'Ho accantonato ' + formatCurrency(activeSavingsPlan.monthly_suggestion)"
                                        ></span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="openCustomSavingsContribution()"
                                        :disabled="isAddingSavingsContribution"
                                        class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-black text-rose-700 shadow-sm transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                        Importo diverso
                                    </button>

                                    <button
                                        type="button"
                                        @click="openForecastDetail()"
                                        class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-black text-rose-700 shadow-sm transition hover:bg-rose-100"
                                    >
                                        <i class="fa-solid fa-list-check"></i>
                                        Dettaglio
                                    </button>

                                    <button
                                        type="button"
                                        @click="disableSavingsPlan()"
                                        :disabled="isDisablingSavingsPlan"
                                        class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <i class="fa-solid fa-xmark"></i>

                                        <span
                                            x-text="isDisablingSavingsPlan
                                                ? 'Disattivazione...'
                                                : 'Disattiva'"
                                        ></span>
                                    </button>
                                </div>
                            </div>
                        </template>
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
                            <span class="w-2 h-6 bg-amber-500 rounded-full"></span>
                            Review Inbox
                        </h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-black border border-amber-200">
                            <span x-text="reviewInboxCount"></span>&nbsp;da verificare
                        </span>
                    </div>

                    <div class="space-y-3 flex-1">
                        <template x-for="item in reviewInbox" :key="item.id">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 hover:bg-white transition-all">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate" x-text="item.document?.nome_originale || 'Documento senza nome'"></p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Categoria: <span class="font-semibold" x-text="item.document_category"></span>
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            Motore: <span class="font-semibold" x-text="item.extraction_engine || 'N/D'"></span>
                                        </p>
                                    </div>

                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-black border"
                                        :bg-class="{
                                            'bg-amber-100 text-amber-700 border-amber-200': item.status === 'needs_review',
                                            'bg-blue-100 text-blue-700 border-blue-200': item.status === 'processing',
                                            'bg-slate-100 text-slate-700 border-slate-200': item.status === 'pending',
                                            'bg-red-100 text-red-700 border-red-200': item.status === 'failed',
                                            'bg-emerald-100 text-emerald-700 border-emerald-200': item.status === 'reviewed'
                                        }"
                                        x-text="item.status">
                                    </span>
                                </div>

                                <div class="mt-3">
                                    <textarea
                                        x-model="reviewActionNotes[item.id]"
                                        rows="2"
                                        class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Nota review opzionale..."
                                    ></textarea>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button
                                        @click="handleReviewAction(item, 'approve')"
                                        type="button"
                                        class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-colors"
                                    >
                                        Approva
                                    </button>

                                    <button
                                        @click="handleReviewAction(item, 'defer')"
                                        type="button"
                                        class="px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-bold hover:bg-amber-600 transition-colors"
                                    >
                                        Rimanda
                                    </button>

                                    <button
                                        @click="handleReviewAction(item, 'fail')"
                                        type="button"
                                        class="px-3 py-2 rounded-lg bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition-colors"
                                    >
                                        Fallito
                                    </button>
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <div class="text-xs text-slate-500">
                                        Confidence:
                                        <span class="font-bold text-slate-700" x-text="item.confidence_score ?? 'N/D'"></span>
                                    </div>

                                    <a :href="item.document?.path"
                                    target="_blank"
                                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                        Apri documento
                                    </a>
                                </div>
                            </div>
                        </template>

                        <div x-show="reviewInbox.length === 0" class="flex flex-col items-center justify-center py-8 text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-2 text-emerald-200"></i>
                            <p class="text-sm font-semibold">Nessun documento da verificare.</p>
                        </div>
                    </div>
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
                                <span class="text-xs font-bold text-slate-700" x-text="(archStats['{{ $tipo }}'] ? archStats['{{ $tipo }}'].percentuale : 0) + '%'"></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="bg-{{ $info['color'] }}-500 h-1.5 rounded-full transition-all duration-500" :style="`width: ${archStats['{{ $tipo }}'] ? archStats['{{ $tipo }}'].percentuale : 0}%`"></div>
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

        {{-- MODALE DETTAGLIO FORECAST --}}
        <div
            x-show="showForecastDetail"
            x-cloak
            @keydown.escape.window="closeForecastDetail()"
            class="modal-overlay"
            :class="{
                'modal-active': showForecastDetail && !isClosingForecastDetail,
                'modal-out': isClosingForecastDetail
            }"
        >
            <div
                @click="closeForecastDetail()"
                class="modal-backdrop"
            ></div>

            <div class="modal-container w-full" style="max-width: 44rem;">
                <div
                    @click.stop
                    class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden"
                >
                    <div class="bg-gradient-to-r from-rose-600 to-pink-600 px-6 py-5 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-100">
                                    Previsione spese
                                </p>

                                <h3
                                    class="mt-1 text-xl font-black"
                                    x-text="expenseForecast.peak_month?.label || 'Dettaglio mese'"
                                ></h3>

                                <p class="mt-1 text-sm text-rose-100">
                                    Scadenze e ricorrenze che compongono il mese più impegnativo.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="closeForecastDetail()"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20"
                                aria-label="Chiudi dettaglio"
                            >
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto bg-slate-50 p-5 custom-scrollbar">
                        <div class="mb-4 rounded-2xl border border-rose-100 bg-rose-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-wider text-rose-500">
                                        Totale previsto
                                    </p>

                                    <p
                                        class="mt-1 text-2xl font-black text-rose-700"
                                        x-text="formatCurrency(expenseForecast.peak_month?.total || 0)"
                                    ></p>
                                </div>

                                <div class="text-left sm:text-right">
                                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                        Voci previste
                                    </p>

                                    <p
                                        class="mt-1 text-xl font-black text-slate-800"
                                        x-text="expenseForecast.peak_month?.count || 0"
                                    ></p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <template
                                x-for="item in (expenseForecast.peak_month?.items || [])"
                                :key="`${item.source}-${item.deadline_id || item.subscription_id || item.date}`"
                            >
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                        :class="item.source === 'subscription_forecast'
                                            ? 'bg-violet-100 text-violet-600'
                                            : 'bg-rose-100 text-rose-600'"
                                    >
                                        <i
                                            :class="item.source === 'subscription_forecast'
                                                ? 'fa-solid fa-rotate'
                                                : 'fa-solid fa-calendar-check'"
                                        ></i>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p
                                                class="truncate text-sm font-black text-slate-900"
                                                x-text="item.title"
                                            ></p>

                                            <span
                                                class="rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wide"
                                                :class="item.source === 'subscription_forecast'
                                                    ? 'bg-violet-100 text-violet-700'
                                                    : 'bg-slate-100 text-slate-600'"
                                                x-text="item.source === 'subscription_forecast'
                                                    ? 'Rinnovo futuro stimato'
                                                    : 'Scadenza registrata'"
                                            ></span>
                                        </div>

                                        <p
                                            class="mt-1 text-xs font-semibold text-slate-500"
                                            x-show="item.source !== 'subscription_forecast'"
                                        >
                                            Prevista il
                                            <span x-text="formatDate(item.date)"></span>
                                        </p>

                                        <p
                                            class="mt-1 text-xs font-semibold text-violet-700"
                                            x-show="item.source === 'subscription_forecast'"
                                        >
                                            Successivo al rinnovo previsto del
                                            <span x-text="formatDate(item.previous_date)"></span>
                                            · stimato per il
                                            <span x-text="formatDate(item.date)"></span>
                                        </p>
                                    </div>

                                    <p
                                        class="shrink-0 text-sm font-black text-slate-900"
                                        x-text="formatCurrency(item.amount)"
                                    ></p>
                                </div>
                            </template>

                            <div
                                x-show="(expenseForecast.peak_month?.items || []).length === 0"
                                class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm font-semibold text-slate-500"
                            >
                                Nessun dettaglio disponibile per questo mese.
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 bg-white px-6 py-4">
                        <button
                            type="button"
                            @click="closeForecastDetail()"
                            class="min-h-11 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-200"
                        >
                            Chiudi
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- MODALE IMPORTO ACCANTONAMENTO PERSONALIZZATO --}}
        <div
            x-show="showCustomSavingsModal"
            x-cloak
            @keydown.escape.window="closeCustomSavingsContribution()"
            class="modal-overlay"
            :class="{
                'modal-active': showCustomSavingsModal && !isClosingCustomSavingsModal,
                'modal-out': isClosingCustomSavingsModal
            }"
        >
            <div
                @click="closeCustomSavingsContribution()"
                class="modal-backdrop"
            ></div>

            <div class="modal-container w-full" style="max-width: 28rem;">
                <div
                    @click.stop
                    class="modal-content-wrapper overflow-hidden rounded-2xl bg-white shadow-2xl"
                >
                    <div class="bg-gradient-to-r from-rose-600 to-pink-600 px-6 py-5 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-100">
                                    Piano di accantonamento
                                </p>

                                <h3 class="mt-1 text-xl font-black">
                                    Registra importo
                                </h3>
                            </div>

                            <button
                                type="button"
                                @click="closeCustomSavingsContribution()"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20"
                                aria-label="Chiudi"
                            >
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <p class="text-sm leading-relaxed text-slate-600">
                            Inserisci l’importo che hai realmente messo da parte.
                        </p>

                        <div class="mt-5 rounded-2xl bg-rose-50 p-4">
                            <p class="text-xs font-black uppercase tracking-wider text-rose-500">
                                Importo ancora da accantonare
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-rose-700"
                                x-text="formatCurrency(savingPlanRemaining(activeSavingsPlan))"
                            ></p>
                        </div>

                        <label class="mt-5 block text-sm font-black text-slate-800">
                            Quanto hai accantonato?
                        </label>

                        <div class="relative mt-2">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-black text-slate-500">
                                €
                            </span>

                            <input
                                type="number"
                                x-model.number="customSavingsAmount"
                                min="0.01"
                                :max="savingPlanRemaining(activeSavingsPlan)"
                                step="0.01"
                                inputmode="decimal"
                                placeholder="Es. 100,00"
                                class="w-full rounded-xl border border-slate-300 py-3 pl-8 pr-4 text-base font-bold text-slate-900 outline-none transition focus:border-rose-500 focus:ring-2 focus:ring-rose-200"
                            >
                        </div>

                        <p class="mt-2 text-xs leading-relaxed text-slate-500">
                            Se inserisci una cifra superiore al residuo, il sistema registrerà solo quanto necessario per completare l’obiettivo.
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="closeCustomSavingsContribution()"
                            class="min-h-11 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-100"
                            :disabled="isAddingSavingsContribution"
                        >
                            Annulla
                        </button>

                        <button
                            type="button"
                            @click="addSavingsContribution(customSavingsAmount)"
                            class="min-h-11 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isAddingSavingsContribution || !customSavingsAmount || Number(customSavingsAmount) <= 0"
                        >
                            <span x-show="!isAddingSavingsContribution">
                                Registra importo
                            </span>

                            <span x-show="isAddingSavingsContribution">
                                Registrazione...
                            </span>
                        </button>
                    </div>
                </div>
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

        <div x-show="showDeleteDocModal" x-cloak class="modal-overlay" style="z-index: 1080;" :class="{ 'modal-active': showDeleteDocModal && !isClosingDeleteDoc, 'modal-out': isClosingDeleteDoc }">
            <div @click="closeDeleteDoc()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="modal-container w-full max-w-sm pointer-events-auto">
                <div @click.stop class="modal-content-wrapper bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-7 text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4 animate-pulse">
                            <i class="fa-solid fa-trash-can text-2xl text-red-500"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-2">Eliminare il documento?</h3>
                        <p class="text-sm text-slate-500 mb-6">Stai per eliminare "<strong x-text="docToDeleteName" class="text-slate-700"></strong>". L'azione è irreversibile.</p>
                        <div class="flex gap-3">
                            <button @click="closeDeleteDoc()" class="flex-1 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200 transition-colors">Annulla</button>
                            <button @click="handleDocumentDelete()" class="flex-1 py-3 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 shadow-md transition-transform active:scale-95">Elimina</button>
                        </div>
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

    <div x-data="{ 
        open: false, 
        loading: false, 
        report: '',
        fetchAnalysis() {
            this.loading = true;
            this.open = true;
            this.report = '';
            
            // Prendo l'anno selezionato dalla dashboard
            let year = document.querySelector('select')?.value || new Date().getFullYear();

            fetch('{{ route('ai.analyze') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ year: year })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                // Formattazione markdown semplice per la visualizzazione
                this.report = data.analysis.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            })
            .catch(() => {
                this.loading = false;
                this.report = 'Errore durante l\'analisi. Riprova tra poco.';
            });
        }
    }" 
        @open-ai-analyst.window="fetchAnalysis()"
        x-show="open" 
        class="fixed inset-0 z-[10000] overflow-y-auto" 
        style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm" @click="open = false"></div>

            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-2xl">📊</span> Analisi Strategica AI
                    </h3>
                    <button @click="open = false" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                </div>

                <div class="px-8 py-6 max-h-[70vh] overflow-y-auto">
                    <template x-if="loading">
                        <div class="flex flex-col items-center py-12">
                            <dotlottie-wc src="{{ asset('images/robot.lottie') }}" autoplay loop style="width: 150px; height: 150px;"></dotlottie-wc>
                            <p class="mt-4 text-slate-500 animate-pulse font-medium">L'Analista sta studiando i tuoi dati...</p>
                        </div>
                    </template>

                    <template x-if="!loading">
                        <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed" x-html="report"></div>
                    </template>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-right">
                    <button @click="open = false" class="px-6 py-2 bg-white border border-slate-300 text-slate-700 font-bold rounded-lg hover:bg-slate-100 transition-colors">
                        Chiudi Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- LAUNCHER OPERATIVO --}}
    <div
        x-show="showActionLauncher"
        x-cloak
        @keydown.escape.window="closeActionLauncher()"
        class="modal-overlay"
        :class="{
            'modal-active': showActionLauncher && !isClosingActionLauncher,
            'modal-out': isClosingActionLauncher
        }"
    >
        <div
            class="modal-backdrop"
            @click="closeActionLauncher()"
        ></div>

        <div class="modal-container modal-container-launcher">
            <div
                class="modal-content-wrapper bg-white"
                @click.stop
            >
                <div class="border-b border-slate-100 px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-500">
                                Azione rapida
                            </p>
                            <h3 class="mt-1 text-xl font-black text-slate-900">
                                Cosa vuoi fare?
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Parti da ciò che è appena successo: ti guidiamo noi.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="closeActionLauncher()"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Chiudi"
                        >
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-3 p-5">
                    <button
                        type="button"
                        @click="goToDeadlines()"
                        class="group flex w-full items-center gap-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-left transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-100/70 hover:shadow-md"
                    >
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-xl text-white shadow-sm">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-900">
                                Ho pagato o rinnovato qualcosa
                            </span>
                            <span class="mt-1 block text-xs leading-relaxed text-slate-600">
                                Trova la scadenza, conferma il pagamento e crea il prossimo rinnovo.
                            </span>
                        </span>

                        <i class="fa-solid fa-arrow-right text-sm text-emerald-600 transition group-hover:translate-x-1"></i>
                    </button>

                    <button
                        type="button"
                        @click="openDocumentFromLauncher()"
                        class="group flex w-full items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-left transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-100/70 hover:shadow-md"
                    >
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-xl text-white shadow-sm">
                            <i class="fa-solid fa-file-arrow-up"></i>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-900">
                                Ho un documento o una ricevuta
                            </span>
                            <span class="mt-1 block text-xs leading-relaxed text-slate-600">
                                Carica una foto o un PDF: lo archivierai ora e presto il sistema ti proporrà il collegamento.
                            </span>
                        </span>

                        <i class="fa-solid fa-arrow-right text-sm text-blue-600 transition group-hover:translate-x-1"></i>
                    </button>

                    <button
                        type="button"
                        @click="showCreateChoices = !showCreateChoices"
                        class="group flex w-full items-center gap-4 rounded-2xl border border-violet-100 bg-violet-50 p-4 text-left transition hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-100/70 hover:shadow-md"
                    >
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-600 text-xl text-white shadow-sm">
                            <i class="fa-solid fa-plus"></i>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-900">
                                Voglio aggiungere qualcosa
                            </span>
                            <span class="mt-1 block text-xs leading-relaxed text-slate-600">
                                Scegli cosa vuoi registrare: non devi cercare la sezione giusta.
                            </span>
                        </span>

                        <i
                            class="fa-solid fa-chevron-down text-sm text-violet-600 transition"
                            :class="showCreateChoices ? 'rotate-180' : ''"
                        ></i>
                    </button>

                    <div
                        x-show="showCreateChoices"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="grid grid-cols-1 gap-2 rounded-2xl border border-slate-100 bg-slate-50 p-3 sm:grid-cols-2"
                    >
                        <a
                            href="{{ route('vehicles.index') }}"
                            class="flex min-h-12 items-center gap-2 rounded-xl bg-white px-3 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-700"
                        >
                            <i class="fa-solid fa-car text-emerald-600"></i>
                            Veicolo
                        </a>

                        <a
                            href="{{ route('subscriptions.index') }}"
                            class="flex min-h-12 items-center gap-2 rounded-xl bg-white px-3 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-violet-50 hover:text-violet-700"
                        >
                            <i class="fa-solid fa-receipt text-violet-600"></i>
                            Spesa o abbonamento
                        </a>

                        <a
                            href="{{ route('patenti.index') }}"
                            class="flex min-h-12 items-center gap-2 rounded-xl bg-white px-3 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-amber-50 hover:text-amber-700"
                        >
                            <i class="fa-solid fa-id-card text-amber-600"></i>
                            Documento personale
                        </a>

                        <button
                            type="button"
                            @click="openDocumentFromLauncher()"
                            class="flex min-h-12 items-center gap-2 rounded-xl bg-white px-3 py-3 text-left text-sm font-bold text-slate-700 shadow-sm transition hover:bg-blue-50 hover:text-blue-700"
                        >
                            <i class="fa-solid fa-folder-plus text-blue-600"></i>
                            Documento o prova
                        </button>
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <p class="text-center text-xs font-medium text-slate-500">
                        Inserisci meno dati possibile: il sistema completerà e proporrà i passaggi successivi.
                    </p>
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
                reviewInbox: [],
                reviewInboxCount: 0,
                isUpdatingReview: false,
                reviewActionNotes: {},

                // Dati passati dal server
                stats: @json($stats),
                allExpenses: @json($jsExpenses),
                expenseForecast: @json($expenseForecast ?? []),
                activeSavingsPlan: @json($activeSavingsPlan ?? null),
                
                // Variabili Modali...
                showUploadModal: false, showViewModal: false, showPreviewModal: false, showSuccessModal: false, showErrorModal: false, showActionLauncher: false,                 showCreateChoices: false,
                showForecastDetail: false, showCustomSavingsModal: false,
                successMessage: '', errorMessage: '',
                isClosingUpload: false, isClosingView: false, isPreviewClosing: false, isClosingSuccess: false, isClosingError: false,                 isClosingActionLauncher: false,
                isClosingForecastDetail: false, isClosingCustomSavingsModal: false,
                isCreatingSavingsPlan: false, isDisablingSavingsPlan: false, isAddingSavingsContribution: false,
                selectedCategory: 'bollo', currentDocUrl: '', currentDocType: '', currentDocName: '', currentFolder: null, customSavingsAmount: null,
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
                            this.expenseForecast = data.expenseForecast || {};
                            
                            this.stats = data.stats;
                            this.allExpenses = data.jsExpenses;
                            this.activeSavingsPlan = data.activeSavingsPlan || null;
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
                openFolder(folderId) { 
                    this.currentFolder = folderId; 
                },
                closeFolder() { 
                    this.currentFolder = null; 
                },
                getDocsInFolder(folderId) { 
                    return this.documents.filter(d => d.categoria === folderId); 
                },
                get currentFolderDocs() { 
                    if (!this.currentFolder) return []; 
                    return this.documents.filter(d => d.categoria === this.currentFolder); 
                },
                get currentFolderInfo() { 
                    return this.folders.find(f => f.id === this.currentFolder) || {}; 
                },
                async refreshData() {
                    try {
                        let resStats = await fetch('{{ route("dashboard.stats") }}');
                        if (resStats.ok) this.archStats = await resStats.json();

                        let resDocs = await fetch('{{ route("dashboard.documents") }}');
                        if (resDocs.ok) {
                            let dataDocs = await resDocs.json();
                            this.documents = dataDocs.documents;
                        }

                        let resInbox = await fetch('{{ route("dashboard.review-inbox") }}');
                        if (resInbox.ok) {
                            let dataInbox = await resInbox.json();
                            this.reviewInbox = dataInbox.items || [];
                            this.reviewInboxCount = this.reviewInbox.length;
                        }
                    } catch (e) {
                        console.error('Errore fetch dati:', e);
                    }
                },
                async handleReviewAction(item, action) {
                    if (this.isUpdatingReview) return;

                    this.isUpdatingReview = true;

                    try {
                        const response = await fetch(`/dashboard/review-inbox/${item.id}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                action: action,
                                review_notes: this.reviewActionNotes[item.id] || null
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Errore durante l’aggiornamento.');
                        }

                        this.successMessage = data.message || 'Aggiornamento completato.';
                        this.showSuccessModal = true;

                        await this.refreshData();
                    } catch (error) {
                        this.errorMessage = error.message || 'Errore durante l’operazione.';
                        this.showErrorModal = true;
                    } finally {
                        this.isUpdatingReview = false;
                    }
                },
                openUploadModal() { 
                    this.showUploadModal = true; 
                },
                closeUploadModal() { 
                    this.isClosingUpload = true; 
                    setTimeout(() => { 
                        this.showUploadModal = false; 
                        this.isClosingUpload = false; 
                        this.selectedCategory = 'bollo'; 
                    }, 500); 
                },
                openForecastDetail() {
                    if (!this.expenseForecast?.peak_month) {
                        return;
                    }

                    this.showForecastDetail = true;
                },

                closeForecastDetail() {
                    this.isClosingForecastDetail = true;

                    setTimeout(() => {
                        this.showForecastDetail = false;
                        this.isClosingForecastDetail = false;
                    }, 500);
                },

                formatCurrency(value) {
                    return Number(value || 0).toLocaleString('it-IT', {
                        style: 'currency',
                        currency: 'EUR',
                    });
                },

                formatDate(value) {
                    if (!value) {
                        return 'N/D';
                    }

                    const [year, month, day] = value.split('-');

                    if (!year || !month || !day) {
                        return value;
                    }

                    return `${day}/${month}/${year}`;
                },
                openActionLauncher() {
                    this.showCreateChoices = false;
                    this.showActionLauncher = true;
                },

                closeActionLauncher() {
                    this.isClosingActionLauncher = true;

                    setTimeout(() => {
                        this.showActionLauncher = false;
                        this.isClosingActionLauncher = false;
                        this.showCreateChoices = false;
                    }, 500);
                },

                goToDeadlines() {
                    window.location.href = "{{ route('deadlines.index') }}";
                },

                openDocumentFromLauncher() {
                    this.closeActionLauncher();

                    setTimeout(() => {
                        this.openUploadModal();
                    }, 300);
                },
                openViewModal() { 
                    this.showViewModal = true; 
                },
                closeViewModal() { 
                    this.isClosingView = true; 
                    setTimeout(() => { 
                        this.showViewModal = false; 
                        this.isClosingView = false; 
                        this.currentFolder = null; 
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
                        const response = await fetch('{{ route("documents.store") }}', { method: 'POST', body: formData });
                        const data = await response.json();
                        if (response.ok) { 
                            event.target.reset(); 
                            this.selectedCategory = 'bollo'; 
                            this.closeUploadModal(); 
                            await this.refreshData(); 
                            this.successMessage = data.message || 'Documento caricato con successo!'; 
                            this.showSuccessModal = true; 
                            setTimeout(() => { 
                                if (this.showSuccessModal) this.closeSuccessModal(); 
                            }, 2000); 
                        } else { 
                            this.showError(data.message || 'Errore nel caricamento del documento'); 
                        }
                    } catch (error) { 
                        this.showError('Errore nella comunicazione con il server.'); 
                    }
                },
                async handleDocumentDelete(event) {
                    try {
                        const response = await fetch(`/documents/${this.docToDeleteId}`, { 
                            method: 'DELETE', 
                            headers: { 
                                'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json' 
                            } 
                        });
                        const data = await response.json();
                        if (response.ok) { 
                            this.closeDeleteDoc(); 
                            await this.refreshData(); 
                            this.successMessage = data.message || 'Documento eliminato con successo!'; 
                            this.showSuccessModal = true; 
                            setTimeout(() => { 
                                if (this.showSuccessModal) this.closeSuccessModal(); 
                            }, 2000); 
                        } else { 
                            this.closeDeleteDoc(); 
                            this.showError(data.message || 'Errore durante l\'eliminazione del documento'); 
                        }
                    } catch (error) { 
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
                        if (this.showErrorModal) this.closeErrorModal(); 
                    }, 4000); 
                },
                async createSavingsPlan() {
                    this.isCreatingSavingsPlan = true;

                    try {
                        const response = await fetch(
                            '{{ route('savings-plans.store') }}',
                            {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                            }
                        );

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(
                                data.message || 'Impossibile creare il piano.'
                            );
                        }

                        this.activeSavingsPlan = data.plan;

                        this.successMessage = data.message || 'Piano creato.';
                        this.showSuccessModal = true;

                        setTimeout(() => {
                            if (this.showSuccessModal) {
                                this.closeSuccessModal();
                            }
                        }, 2500);
                    } catch (error) {
                        this.showError(
                            error.message || 'Errore durante la creazione del piano.'
                        );
                    } finally {
                        this.isCreatingSavingsPlan = false;
                    }
                },

                async disableSavingsPlan() {
                    if (!this.activeSavingsPlan?.id) {
                        return;
                    }

                    this.isDisablingSavingsPlan = true;

                    try {
                        const response = await fetch(
                            `/piani-accantonamento/${this.activeSavingsPlan.id}`,
                            {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                            }
                        );

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(
                                data.message || 'Impossibile disattivare il piano.'
                            );
                        }

                        this.activeSavingsPlan = null;

                        this.successMessage = data.message || 'Piano disattivato.';
                        this.showSuccessModal = true;

                        setTimeout(() => {
                            if (this.showSuccessModal) {
                                this.closeSuccessModal();
                            }
                        }, 2500);
                    } catch (error) {
                        this.showError(
                            error.message || 'Errore durante la disattivazione del piano.'
                        );
                    } finally {
                        this.isDisablingSavingsPlan = false;
                    }
                },
                monthName(month) {
                    const names = [
                        'Gennaio',
                        'Febbraio',
                        'Marzo',
                        'Aprile',
                        'Maggio',
                        'Giugno',
                        'Luglio',
                        'Agosto',
                        'Settembre',
                        'Ottobre',
                        'Novembre',
                        'Dicembre',
                    ];

                    return names[Number(month) - 1] || '';
                },
                savingPlanRemaining(plan) {
                    return Math.max(
                        0,
                        Number(plan?.target_amount || 0)
                            - Number(plan?.saved_amount || 0)
                    );
                },

                savingPlanProgress(plan) {
                    const target = Number(plan?.target_amount || 0);
                    const saved = Number(plan?.saved_amount || 0);

                    if (target <= 0) {
                        return 0;
                    }

                    return Math.min(
                        100,
                        Math.round((saved / target) * 100)
                    );
                },

                async addSavingsContribution(amount = null) {
                    if (!this.activeSavingsPlan?.id) {
                        return;
                    }

                    this.isAddingSavingsContribution = true;

                    try {
                        const payload = amount !== null
                            ? { amount: Number(amount) }
                            : {};

                        const response = await fetch(
                            `/piani-accantonamento/${this.activeSavingsPlan.id}/contributo`,
                            {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            }
                        );

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(
                                data.message || 'Impossibile registrare l’accantonamento.'
                            );
                        }

                        this.activeSavingsPlan = data.plan;

                        if (amount !== null) {
                            this.closeCustomSavingsContribution();
                        }

                        this.successMessage = data.message || 'Accantonamento registrato.';
                        this.showSuccessModal = true;

                        setTimeout(() => {
                            if (this.showSuccessModal) {
                                this.closeSuccessModal();
                            }
                        }, 2500);
                    } catch (error) {
                        this.showError(
                            error.message || 'Errore durante la registrazione.'
                        );
                    } finally {
                        this.isAddingSavingsContribution = false;
                    }
                },
                openCustomSavingsContribution() {
                    if (!this.activeSavingsPlan?.id) {
                        return;
                    }

                    this.customSavingsAmount = null;
                    this.showCustomSavingsModal = true;
                },

                closeCustomSavingsContribution() {
                    this.isClosingCustomSavingsModal = true;

                    setTimeout(() => {
                        this.showCustomSavingsModal = false;
                        this.isClosingCustomSavingsModal = false;
                        this.customSavingsAmount = null;
                    }, 500);
                },
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
        .modal-container-launcher { max-width: 34rem; }
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