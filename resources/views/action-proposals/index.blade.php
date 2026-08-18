<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">
                    Centro Azioni
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Controlla le operazioni che il sistema ha preparato per te.
                </p>
            </div>

            <a
                href="{{ route('dashboard') }}"
                class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-200"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Dashboard
            </a>
        </div>
    </x-slot>

    <div
        class="min-h-screen bg-slate-50 py-8"
        x-data="actionProposalsManager()"
        x-init="init()"
    >
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            <div class="mb-6 rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                        <i class="fa-solid fa-wand-magic-sparkles text-xl"></i>
                    </div>

                    <div>
                        <h3 class="font-black text-slate-900">
                            Il sistema ha preparato queste azioni
                        </h3>

                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                            Non viene modificato nulla automaticamente. Controlla ogni proposta e scegli se procedere o ignorarla.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Da verificare
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-indigo-600"
                        x-text="proposals.length"
                    ></p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Scadute
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-red-500"
                        x-text="expiredCount"
                    ></p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Importo coinvolto
                    </p>

                    <p
                        class="mt-2 text-3xl font-black text-slate-800"
                        x-text="formattedTotal"
                    ></p>
                </div>
            </div>

            <div
                x-show="loading"
                x-cloak
                class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm"
            >
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-indigo-600"></i>

                <p class="mt-3 text-sm font-semibold text-slate-500">
                    Caricamento proposte...
                </p>
            </div>

            <div
                x-show="!loading && proposals.length === 0"
                x-cloak
                class="rounded-2xl border border-emerald-100 bg-white p-10 text-center shadow-sm"
            >
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-check text-2xl"></i>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-900">
                    Nessuna azione da verificare
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500">
                    Al momento è tutto sotto controllo. Quando il sistema troverà qualcosa da verificare, apparirà qui.
                </p>
            </div>

            <div
                x-show="!loading && proposals.length > 0"
                x-cloak
                class="space-y-4"
            >
                <template x-for="proposal in proposals" :key="proposal.id">
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                            <div class="flex min-w-0 items-start gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl"
                                    :class="proposal.action_type === 'mark_paid'
                                        ? 'bg-amber-100 text-amber-600'
                                        : 'bg-indigo-100 text-indigo-600'"
                                >
                                    <i
                                        class="text-xl"
                                        :class="proposal.action_type === 'mark_paid'
                                            ? 'fa-solid fa-circle-check'
                                            : 'fa-solid fa-wand-magic-sparkles'"
                                    ></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3
                                            class="text-base font-black text-slate-900"
                                            x-text="proposal.title"
                                        ></h3>

                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-700">
                                            Da verificare
                                        </span>
                                    </div>

                                    <p
                                        class="mt-1 text-sm text-slate-500"
                                        x-text="proposal.reason"
                                    ></p>

                                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-slate-600">
                                        <span class="rounded-lg bg-slate-100 px-2.5 py-1">
                                            Scadenza:
                                            <span x-text="proposal.deadline_date"></span>
                                        </span>

                                        <span class="rounded-lg bg-slate-100 px-2.5 py-1">
                                            Importo:
                                            <span x-text="proposal.amount"></span>
                                        </span>

                                        <span
                                            x-show="proposal.suggest_renewal"
                                            class="rounded-lg bg-indigo-100 px-2.5 py-1 text-indigo-700"
                                        >
                                            Rinnovo disponibile
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                                <button
                                    type="button"
                                    @click="rejectProposal(proposal)"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-200"
                                    :disabled="proposal.processing"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                    Ignora
                                </button>

                                <button
                                    type="button"
                                    @click="openConfirmModal(proposal)"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700"
                                    :disabled="proposal.processing"
                                >
                                    <i class="fa-solid fa-check"></i>
                                    Verifica
                                </button>
                            </div>
                        </div>
                    </article>
                </template>
            </div>
        </div>

        <div
            x-show="showConfirmModal"
            x-cloak
            @keydown.escape.window="closeConfirmModal()"
            class="fixed inset-0 z-[1000] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
                @click.stop
            >
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-indigo-500">
                            Verifica pagamento
                        </p>

                        <h3 class="mt-1 text-xl font-black text-slate-900">
                            Come vuoi procedere?
                        </h3>
                    </div>

                    <button
                        type="button"
                        @click="closeConfirmModal()"
                        class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        aria-label="Chiudi"
                    >
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p
                            class="font-black text-slate-900"
                            x-text="selectedProposal?.title"
                        ></p>

                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-slate-600">
                            <span class="rounded-lg bg-white px-2.5 py-1 shadow-sm">
                                Scadenza:
                                <span x-text="selectedProposal?.deadline_date"></span>
                            </span>

                            <span class="rounded-lg bg-white px-2.5 py-1 shadow-sm">
                                Importo:
                                <span x-text="selectedProposal?.amount"></span>
                            </span>
                        </div>
                    </div>

                    <p class="mt-5 text-sm font-semibold text-slate-700">
                        Hai pagato questa scadenza?
                    </p>

                    <div class="mt-3 space-y-3">
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition"
                            :class="confirmWithRenewal
                                ? 'border-indigo-200 bg-indigo-50'
                                : 'border-slate-200 bg-white hover:border-slate-300'"
                        >
                            <input
                                type="radio"
                                name="proposal-payment-action"
                                :value="true"
                                x-model="confirmWithRenewal"
                                class="mt-1 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            >

                            <span>
                                <span class="block text-sm font-black text-slate-900">
                                    Sì, pagato e crea il prossimo rinnovo
                                </span>

                                <span class="mt-1 block text-xs leading-relaxed text-slate-600">
                                    Segna la scadenza come pagata e genera automaticamente la successiva secondo le regole previste.
                                </span>
                            </span>
                        </label>

                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition"
                            :class="!confirmWithRenewal
                                ? 'border-emerald-200 bg-emerald-50'
                                : 'border-slate-200 bg-white hover:border-slate-300'"
                        >
                            <input
                                type="radio"
                                name="proposal-payment-action"
                                :value="false"
                                x-model="confirmWithRenewal"
                                class="mt-1 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            >

                            <span>
                                <span class="block text-sm font-black text-slate-900">
                                    Sì, pagato senza rinnovo
                                </span>

                                <span class="mt-1 block text-xs leading-relaxed text-slate-600">
                                    Archivia questa scadenza senza crearne una nuova.
                                </span>
                            </span>
                        </label>
                    </div>

                    <p class="mt-5 text-xs leading-relaxed text-slate-500">
                        Se non hai ancora pagato, chiudi questa finestra: la proposta resterà disponibile.
                    </p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="closeConfirmModal()"
                        class="min-h-11 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-100"
                        :disabled="confirming"
                    >
                        Non ancora
                    </button>

                    <button
                        type="button"
                        @click="confirmProposal()"
                        class="min-h-11 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="confirming"
                    >
                        <span x-show="!confirming">
                            Conferma pagamento
                        </span>

                        <span x-show="confirming">
                            Elaborazione...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function actionProposalsManager() {
            return {
                proposals: @json($proposals),
                loading: false,
                confirming: false,
                showConfirmModal: false,
                selectedProposal: null,
                confirmWithRenewal: true,

                get expiredCount() {
                    return this.proposals.filter(proposal => proposal.is_expired).length;
                },

                get formattedTotal() {
                    const total = this.proposals.reduce((sum, proposal) => {
                        return sum + Number(proposal.amount_value || 0);
                    }, 0);

                    return total.toLocaleString('it-IT', {
                        style: 'currency',
                        currency: 'EUR'
                    });
                },

                init() {
                    this.proposals = this.proposals.map(proposal => ({
                        ...proposal,
                        processing: false
                    }));
                },

                openConfirmModal(proposal) {
                    this.selectedProposal = proposal;
                    this.confirmWithRenewal = Boolean(proposal.suggest_renewal);
                    this.showConfirmModal = true;
                },

                closeConfirmModal() {
                    this.showConfirmModal = false;
                    this.selectedProposal = null;
                },

                async rejectProposal(proposal) {
                    proposal.processing = true;

                    try {
                        const response = await fetch(
                            `/azioni/${proposal.id}/rifiuta`,
                            {
                                method: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            }
                        );

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Impossibile ignorare la proposta.');
                        }

                        this.proposals = this.proposals.filter(
                            item => item.id !== proposal.id
                        );
                    } catch (error) {
                        alert(error.message);
                        proposal.processing = false;
                    }
                },

                async confirmProposal() {
                    if (!this.selectedProposal) return;

                    this.confirming = true;

                    try {
                        const response = await fetch(
                            `/azioni/${this.selectedProposal.id}/conferma`,
                            {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    rinnova: this.confirmWithRenewal
                                })
                            }
                        );

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(
                                data.message || 'Impossibile confermare questa proposta.'
                            );
                        }

                        const proposalId = this.selectedProposal.id;

                        this.proposals = this.proposals.filter(
                            proposal => proposal.id !== proposalId
                        );

                        this.closeConfirmModal();

                        alert(data.message || 'Operazione completata.');
                    } catch (error) {
                        alert(error.message);
                    } finally {
                        this.confirming = false;
                    }
                }
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-app-layout>