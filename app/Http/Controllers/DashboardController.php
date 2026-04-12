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

class DashboardController extends Controller
{
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
            'categorieStats' => $categorieStats
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
                'data_formattata' => $data->format('d/m/Y')
            ];
        })->values()->toArray();

        return [
            'stats' => [
                'scadenzeUrgenti' => $scadenzeUrgenti,
                'scadenzeImminenti' => $scadenzeImminenti,
                'daPagareQuestAnno' => (float) $daPagareQuestAnno,
                'totaleVeicoli' => $totaleVeicoli
            ],
            'jsExpenses' => $jsExpenses
        ];
    }

    public function stats()
    {
        return response()->json($this->calcolaStatisticheArchivio());
    }

    private function calcolaStatisticheArchivio()
    {
        $categorieStats = [];
        $totaleVeicoli = Vehicle::count();

        $totaliAttesi = [
            'bollo' => $totaleVeicoli,               
            'assicurazione' => $totaleVeicoli,       
            'revisione' => $totaleVeicoli,           
            'patente' => Patente::count(),           
            'carta_identita' => CartaIdentita::count(),       
            'tesserino_sanitario' => TesserinoSanitario::count() 
        ];

        foreach ($totaliAttesi as $catForm => $totaleAtteso) {
            $documentiCaricati = Document::where('categoria', $catForm)->count();
            $percentuale = 0;
            if ($totaleAtteso > 0) {
                $percentuale = min(100, round(($documentiCaricati / $totaleAtteso) * 100));
            } elseif ($documentiCaricati > 0) {
                $percentuale = 100;
            }
            
            $categorieStats[$catForm] = [
                'totale_atteso' => $totaleAtteso,
                'documenti_caricati' => $documentiCaricati,
                'percentuale' => $percentuale
            ];
        }

        return $categorieStats;
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