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
        $tra30giorni = Carbon::now()->addDays(30);
        
        // ==================== GESTIONE FILTRO ANNO ====================
        // Prende l'anno dalla barra URL (es. ?year=2025), altrimenti usa l'anno corrente
        $selectedYear = $request->input('year', $oggi->year);
        
        // Cerca nel DB tutti gli anni per cui esiste almeno una scadenza
        $yearsFromDb = Deadline::selectRaw('YEAR(data_scadenza) as year')
                        ->whereNotNull('data_scadenza')
                        ->distinct()
                        ->pluck('year')
                        ->toArray();
                        
        // Unisce gli anni trovati con l'anno corrente (per essere sicuri che ci sia sempre) e li ordina dal più recente
        $availableYears = array_unique(array_merge($yearsFromDb, [$oggi->year]));
        rsort($availableYears);

        $inizioAnno = Carbon::create($selectedYear)->startOfYear();
        $fineAnno = Carbon::create($selectedYear)->endOfYear();
        
        // ==================== CONTATORI OPERATIVI (Sempre rispetto ad "OGGI") ====================
        
        $totaleVeicoli = Vehicle::count();
        $totalePatenti = Patente::count();
        $totaleScadenze = Deadline::whereYear('data_scadenza', $selectedYear)->count();
        
        // Usa gli scope del model per query pulite
        $scadenzeImminenti = Deadline::whereYear('data_scadenza', $selectedYear)->imminenti()->count();
        $scadenzeUrgenti = Deadline::whereYear('data_scadenza', $selectedYear)->urgenti()->count();
                
        // ==================== STATISTICHE FINANZIARIE (Filtrate per ANNO SELEZIONATO) ====================
        
        $costiAnnuali = Deadline::whereYear('data_scadenza', $selectedYear)->sum('importo');
        $pagatoQuestAnno = Deadline::whereYear('data_scadenza', $selectedYear)->where('pagato', true)->sum('importo');
        $daPagareQuestAnno = Deadline::whereYear('data_scadenza', $selectedYear)->where('pagato', false)->sum('importo');
        $spesaMediaVeicolo = $totaleVeicoli > 0 ? $costiAnnuali / $totaleVeicoli : 0;
        
        $spesoQuestoMese = Deadline::whereMonth('data_scadenza', $oggi->month)
            ->whereYear('data_scadenza', $selectedYear)
            ->where('pagato', true)
            ->sum('importo');
        
        $mesePiuCostoso = Deadline::whereYear('data_scadenza', $selectedYear)
            ->selectRaw('MONTH(data_scadenza) as mese, SUM(importo) as totale')
            ->groupBy('mese')
            ->orderBy('totale', 'desc')
            ->first();
        
        $budgetMensileConsigliato = $mesePiuCostoso ? $mesePiuCostoso->totale : 0;

        $storicoPagamenti = Deadline::where('pagato', true)
                            ->whereYear('data_scadenza', Carbon::now()->year)
                            ->orderBy('data_scadenza', 'desc')
                            ->get();
        
        // ==================== SCADENZE PROSSIME (Sempre rispetto ad OGGI) ====================
        
        $prossimaScadenze = Deadline::with(['vehicle', 'user', 'subscription'])
            ->whereYear('data_scadenza', $selectedYear)
            ->where('pagato', false) // Mostriamo quelle non ancora pagate
            ->orderBy('data_scadenza', 'asc')
            ->limit(10)
            ->get();
        
        // ==================== CALENDARIO (Filtrato per ANNO SELEZIONATO) ====================
        
        $calendarEvents = Deadline::with(['vehicle', 'user', 'subscription'])
            ->whereYear('data_scadenza', $selectedYear)
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function($deadline) {
                $backgroundColor = $deadline->tipo_color; 
                $borderColor = $deadline->tipo_color;

                if ($deadline->pagato) {
                    $backgroundColor = '#10b981'; 
                    $borderColor = '#059669';    
                } elseif ($deadline->data_scadenza->isPast()) {
                    $backgroundColor = '#ef4444'; 
                    $borderColor = '#b91c1c';    
                }

                $soggetto = 'Famiglia'; 
                if ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription) {
                    $soggetto = $deadline->subscription->nome;
                } elseif ($deadline->vehicle) {
                    $soggetto = $deadline->vehicle->targa;
                } elseif ($deadline->user) {
                    $soggetto = $deadline->user->name;
                }

                $tipoFormattato = ucfirst(str_replace('_', ' ', $deadline->tipo));

                return [
                    'id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'title' => $soggetto . ' - ' . $tipoFormattato,
                    'start' => $deadline->data_scadenza->format('Y-m-d'),
                    'importo' => $deadline->importo ?? 0,
                    'pagato' => $deadline->pagato,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'extendedProps' => [
                        'pagato' => $deadline->pagato,
                        'importo' => $deadline->importo ?? 0
                    ]
                ];
            });
        
        // ==================== GRAFICI: SPESE (Filtrati per ANNO SELEZIONATO) ====================
        
        $spesePerTipo = Deadline::whereYear('data_scadenza', $selectedYear)
            ->selectRaw('tipo, SUM(importo) as totale')
            ->groupBy('tipo')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->tipo => $item->totale];
            });
        
        $speseMensili = [];
        for ($mese = 1; $mese <= 12; $mese++) {
            $pagatoMese = Deadline::whereYear('data_scadenza', $selectedYear)->whereMonth('data_scadenza', $mese)->where('pagato', true)->sum('importo');
            $daPagareMese = Deadline::whereYear('data_scadenza', $selectedYear)->whereMonth('data_scadenza', $mese)->where('pagato', false)->sum('importo');
            $speseMensili[] = [
                'mese' => Carbon::create($selectedYear, $mese, 1)->locale('it')->isoFormat('MMM'),
                'pagato' => $pagatoMese,
                'da_pagare' => $daPagareMese,
                'totale' => $pagatoMese + $daPagareMese
            ];
        }
        
        // ==================== REVISIONI IN SCADENZA ====================
        
        $revisioniInScadenza = Revisione::with('vehicle')
            ->whereBetween('data_prossima_revisione', [$oggi, $tra30giorni])
            ->orderBy('data_prossima_revisione', 'asc')
            ->get()
            ->map(function($revisione) use ($oggi) {
                $prossima = Carbon::parse($revisione->data_prossima_revisione);
                $giorniRimanenti = $oggi->diffInDays($prossima, false);
                return [
                    'vehicle_targa' => $revisione->vehicle->targa,
                    'vehicle_marca' => $revisione->vehicle->marca,
                    'data_prossima' => $revisione->data_prossima_revisione->format('d/m/Y'),
                    'giorni_rimanenti' => (int) $giorniRimanenti,
                    'urgente' => $giorniRimanenti <= 30,
                ];
            });
        
        // ==================== STATISTICHE ARCHIVIO DIGITALE ====================
        
        $categorieStats = $this->calcolaStatisticheArchivio();
        
        // Carichiamo solo le deadlines dell'anno per non appesantire il rendering JS
        $deadlines = Deadline::with(['vehicle', 'user'])->whereYear('data_scadenza', $selectedYear)->get();
        
        return view('dashboard', compact(
            'totaleVeicoli', 'totalePatenti', 'totaleScadenze', 'scadenzeImminenti',
            'scadenzeUrgenti', 'costiAnnuali', 'pagatoQuestAnno', 'daPagareQuestAnno',
            'spesaMediaVeicolo', 'budgetMensileConsigliato', 'spesoQuestoMese',
            'prossimaScadenze', 'calendarEvents', 'spesePerTipo', 'speseMensili',
            'revisioniInScadenza', 'categorieStats', 'deadlines', 'selectedYear', 'availableYears'
        ));
    }

    public function stats()
    {
        // Restituisce le statistiche dei DOCUMENTI aggiornate tramite chiamata JSON di Alpine.js
        return response()->json($this->calcolaStatisticheArchivio());
    }

    /**
     * Funzione unica e centralizzata per calcolare le percentuali dei DOCUMENTI CARICATI
     */
    private function calcolaStatisticheArchivio()
    {
        $categorieStats = [];
        
        // Contiamo le "cose fisiche" possedute, non le scadenze!
        $totaleVeicoli = Vehicle::count();

        // Definiamo qual è il "100%" per ogni categoria
        $totaliAttesi = [
            'bollo' => $totaleVeicoli,               // Mi aspetto 1 bollo per ogni auto
            'assicurazione' => $totaleVeicoli,       // Mi aspetto 1 assicurazione per ogni auto
            'revisione' => $totaleVeicoli,           // Mi aspetto 1 referto per ogni auto
            'patente' => Patente::count(),           // Mi aspetto 1 file per ogni patente registrata
            'carta_identita' => CartaIdentita::count(),       // Mi aspetto 1 file per ogni carta identità
            'tesserino_sanitario' => TesserinoSanitario::count() // Mi aspetto 1 file per ogni tesserino
        ];

        foreach ($totaliAttesi as $catForm => $totaleAtteso) {
            // Quanti PDF/Immagini ha fisicamente caricato l'utente per questa categoria
            $documentiCaricati = Document::where('categoria', $catForm)->count();
            
            $percentuale = 0;
            if ($totaleAtteso > 0) {
                // Calcola la percentuale matematica esatta (senza mai sforare il 100%)
                $percentuale = min(100, round(($documentiCaricati / $totaleAtteso) * 100));
            } elseif ($documentiCaricati > 0) {
                // Se non hai ancora registrato l'auto/documento ma hai caricato il file in anticipo
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