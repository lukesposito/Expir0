<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Patente;
use App\Models\Deadline;
use App\Models\Revisione;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\Bollo;
use App\Models\Assicurazione;
use App\Models\CartaIdentita;
use App\Models\TesserinoSanitario;
use App\Models\Subscription;
use App\Models\DocumentExtraction;
use App\Models\ActionProposal;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\Forecasting\ExpenseForecastService;

class DashboardController extends Controller
{
    public function __construct(
        protected ExpenseForecastService $expenseForecastService
    ) {}

    public function index(Request $request)
    {
        $oggi = Carbon::now();
        $selectedYear = $request->input('year', $oggi->year);
        
        // 1. Recupero anni disponibili per il menu a tendina
        $yearsFromDb = Deadline::selectRaw('YEAR(data_scadenza) as year')
                        ->whereNotNull('data_scadenza')->distinct()->pluck('year')->toArray();
        $availableYears = array_values(array_unique(array_merge($yearsFromDb, [$oggi->year])));
        rsort($availableYears);

        // 2. Calcolo dei dati per l'anno selezionato (centralizzato)
        $data = $this->getDashboardData($selectedYear);

        // 3. SE È AJAX: Restituiamo solo il "pacchetto dati" JSON, niente HTML!
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        // 4. SE È IL PRIMO CARICAMENTO: Passiamo tutto alla vista Blade
        $categorieStats = $this->calcolaStatisticheArchivio();
        
        return view('dashboard', array_merge($data, [
            'availableYears' => $availableYears,
            'selectedYear' => $selectedYear,
            'categorieStats' => $categorieStats,
        ]));
    }

    /**
     * Il "motore" che preleva i dati. Usato sia per la pagina intera che per l'AJAX.
     */
    private function getDashboardData($selectedYear)
    {
        // Contatori Base
        $totaleVeicoli = Vehicle::count();
        $scadenzeUrgenti = Deadline::whereYear('data_scadenza', $selectedYear)->urgenti()->count();
        $scadenzeImminenti = Deadline::whereYear('data_scadenza', $selectedYear)->imminenti()->count();
        $daPagareQuestAnno = Deadline::whereYear('data_scadenza', $selectedYear)->where('pagato', false)->sum('importo');
        
        /* archive_document	        È una proposta documentale veicolo	                La mantiene
        archive_personal_document	È una proposta documentale personale	            La mantiene
        Proposta deadline	        Verifica che la deadline sorgente esista ancora	    La mantiene solo se esiste
        Qualsiasi altro caso	    Non è gestibile/visualizzabile con logica nota	    La esclude
        */
        $azioniDaVerificare = ActionProposal::query()
            ->visibleToUser()
            ->count();

        // Preleviamo le scadenze dell'anno
        $deadlines = Deadline::with(['vehicle', 'user', 'subscription'])
            ->whereYear('data_scadenza', $selectedYear)
            ->get();

        // Trasformiamo i dati in un Array pulito per JavaScript (Alpine & ChartJS)
        $jsExpenses = $deadlines->map(function($d) {
            $data = Carbon::parse($d->data_scadenza)->startOfDay();
            $giorniDiff = (int) Carbon::now()->startOfDay()->diffInDays($data, false);
            
            $soggetto = 'Spesa di Famiglia';
            if ($d->tipo === 'spesa_abbonamento' && $d->subscription) {
                $soggetto = $d->subscription->nome;
            } elseif ($d->vehicle) {
                $soggetto = strtoupper($d->vehicle->marca . ' ' . $d->vehicle->modello);
            } elseif ($d->user) {
                $soggetto = $d->user->name;
            }
            
            return [
                'id' => $d->id,
                'titolo' => $soggetto,
                'tipo' => $d->tipo,
                'tipo_label' => ucfirst(str_replace('_', ' ', $d->tipo)),
                'tipo_color' => $d->tipo_color ?? '#64748b',
                'tipo_icon' => $d->tipo_icon ?? '📄',
                'importo' => (float) ($d->importo ?? 0),
                'importo_formattato' => '€' . number_format($d->importo ?? 0, 2, ',', '.'),
                'pagato' => (bool) $d->pagato,
                'mese' => $data->month - 1, // 0 = Gennaio, 11 = Dicembre
                'giorni' => $giorniDiff,
                'is_scaduto' => $giorniDiff < 0,
                'data_formattata' => $data->format('d/m/Y'),
            ];
        })->values()->toArray();

        $expenseForecast = $this->expenseForecastService->build(12);

        $activeSavingsPlan = SavingsPlan::query()
            ->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', auth()->id());
            })
            ->where('status', 'active')
            ->orderBy('target_year')
            ->orderBy('target_month')
            ->first();

        return [
            'stats' => [
                'scadenzeUrgenti' => $scadenzeUrgenti,
                'scadenzeImminenti' => $scadenzeImminenti,
                'daPagareQuestAnno' => (float) $daPagareQuestAnno,
                'totaleVeicoli' => $totaleVeicoli,
                'azioniDaVerificare' => $azioniDaVerificare,
            ],
            'jsExpenses' => $jsExpenses,
            'expenseForecast' => $expenseForecast,
            'activeSavingsPlan' => $activeSavingsPlan,
        ];
    }

    public function stats()
    {
        return response()->json($this->calcolaStatisticheArchivio());
    }

    private function calcolaStatisticheArchivio()
    {
        /*
        |--------------------------------------------------------------------------
        | Statistiche archivio: copertura reale
        |--------------------------------------------------------------------------
        |
        | Veicoli:
        | - un veicolo è coperto una sola volta per ogni categoria.
        |
        | Documenti personali:
        | - ogni utente del sistema è atteso per ogni categoria;
        | - più file della stessa categoria per la stessa persona valgono 1;
        | - il legame corretto è documents.associated_user_id.
        |
        */

        $categorieStats = [];

        /*
        |--------------------------------------------------------------------------
        | Copertura documenti veicolo
        |--------------------------------------------------------------------------
        */

        $vehicleIds = Vehicle::query()
            ->pluck('id');

        foreach (['bollo', 'assicurazione', 'revisione'] as $categoria) {
            $totaleAtteso = $vehicleIds->count();

            $coperti = Document::query()
                ->where('categoria', $categoria)
                ->whereNotNull('vehicle_id')
                ->whereIn('vehicle_id', $vehicleIds)
                ->distinct()
                ->count('vehicle_id');

            $categorieStats[$categoria] = [
                'totale_atteso' => $totaleAtteso,
                'documenti_caricati' => $coperti,
                'percentuale' => $totaleAtteso > 0
                    ? min(100, round(($coperti / $totaleAtteso) * 100))
                    : 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Copertura documenti personali
        |--------------------------------------------------------------------------
        |
        | Usiamo tutti gli utenti esistenti, non il numero di record
        | Patente/CartaIdentita/TesserinoSanitario.
        |
        */

        $userIds = User::query()->pluck('id');

        foreach (['patente', 'carta_identita', 'tesserino_sanitario'] as $categoria) {
            $totaleAtteso = $userIds->count();

            $coperti = Document::query()
                ->where('categoria', $categoria)
                ->whereNotNull('associated_user_id')
                ->whereIn('associated_user_id', $userIds)
                ->distinct()
                ->count('associated_user_id');

            $categorieStats[$categoria] = [
                'totale_atteso' => $totaleAtteso,
                'documenti_caricati' => $coperti,
                'percentuale' => $totaleAtteso > 0
                    ? min(100, round(($coperti / $totaleAtteso) * 100))
                    : 0,
            ];
        }

        return $categorieStats;
    }

    public function reviewInbox()
    {
        $items = DocumentExtraction::with('document')
            ->whereIn('status', ['pending', 'processing', 'needs_review', 'failed'])
            ->latest()
            ->take(12)
            ->get();

        return response()->json([
            'items' => $items->map(function ($item) {
                $document = $item->document;

                return [
                    'id' => $item->id,
                    'document_id' => $item->document_id,
                    'status' => $item->status,
                    'document_category' => $item->document_category,
                    'extraction_engine' => $item->extraction_engine,
                    'confidence_score' => $item->confidence_score,
                    'review_notes' => $item->review_notes,
                    'failure_reason' => $item->failure_reason,
                    'created_at' => optional($item->created_at)->format('d/m/Y H:i'),
                    'processed_at' => optional($item->processed_at)?->format('d/m/Y H:i'),
                    'document' => $document ? [
                        'id' => $document->id,
                        'nome_originale' => $document->nomeoriginale ?? $document->nome_originale,
                        'categoria' => $document->categoria,
                        'path' => Storage::url($document->path),
                    ] : null,
                    'normalized_payload' => $item->normalized_payload,
                ];
            }),
        ]);
    }

    public function updateReviewStatus(Request $request, DocumentExtraction $extraction)
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,defer,fail'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $statusMap = [
            'approve' => 'reviewed',
            'defer' => 'needs_review',
            'fail' => 'failed',
        ];

        $extraction->update([
            'status' => $statusMap[$data['action']],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'failure_reason' => $data['action'] === 'fail'
                ? ($data['review_notes'] ?? 'Marcato come fallito manualmente')
                : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => match ($data['action']) {
                'approve' => 'Documento approvato con successo.',
                'defer' => 'Documento rimandato alla review.',
                'fail' => 'Documento marcato come fallito.',
            },
            'item' => [
                'id' => $extraction->id,
                'status' => $extraction->status,
                'review_notes' => $extraction->review_notes,
                'reviewed_at' => optional($extraction->reviewed_at)->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function documents()
    {
        $docs = Document::with('vehicle')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'documents' => $docs->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'nome_originale' => $doc->nome_originale,
                    'created_at' => $doc->created_at->format('d/m/Y'),
                    'categoria' => $doc->categoria,
                    'tipo_file' => $doc->tipo_file,
                    'path' => Storage::url($doc->path),
                    'vehicle_targa' => $doc->vehicle ? $doc->vehicle->targa : null,
                    'vehicle_marca' => $doc->vehicle ? $doc->vehicle->marca : null,
                    'vehicle_modello' => $doc->vehicle ? $doc->vehicle->modello : null,
                    'is_personal' => !$doc->vehicle
                ];
            })
        ]);
    }
}