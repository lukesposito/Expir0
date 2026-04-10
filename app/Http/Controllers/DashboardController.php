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
    public function index()
    {
        $oggi = Carbon::now();
        $tra30giorni = Carbon::now()->addDays(30);
        $inizioAnno = Carbon::now()->startOfYear();
        $fineAnno = Carbon::now()->endOfYear();
        
        // ==================== CONTATORI ====================
        
        $totaleVeicoli = Vehicle::count();
        $totalePatenti = Patente::count();
        $totaleScadenze = Deadline::count();
        
        // Usa gli scope del model per query pulite
        $scadenzeImminenti = Deadline::imminenti()->count();
        $scadenzeUrgenti = Deadline::urgenti()->count();
        
        // ==================== STATISTICHE FINANZIARIE ====================
        
        $costiAnnuali = Deadline::whereYear('data_scadenza', $oggi->year)->sum('importo');
        $pagatoQuestAnno = Deadline::whereYear('data_scadenza', $oggi->year)->where('pagato', true)->sum('importo');
        $daPagareQuestAnno = Deadline::whereBetween('data_scadenza', [$oggi, $fineAnno])->where('pagato', false)->sum('importo');
        $spesaMediaVeicolo = $totaleVeicoli > 0 ? $costiAnnuali / $totaleVeicoli : 0;
        
        $spesoQuestoMese = Deadline::whereMonth('data_scadenza', $oggi->month)
            ->whereYear('data_scadenza', $oggi->year)
            ->where('pagato', true)
            ->sum('importo');
        
        $mesePiuCostoso = Deadline::whereYear('data_scadenza', $oggi->year)
            ->selectRaw('MONTH(data_scadenza) as mese, SUM(importo) as totale')
            ->groupBy('mese')
            ->orderBy('totale', 'desc')
            ->first();
        
        $budgetMensileConsigliato = $mesePiuCostoso ? $mesePiuCostoso->totale : 0;

        $storicoPagamenti = Deadline::where('pagato', true)
                            ->whereYear('data_scadenza', Carbon::now()->year)
                            ->orderBy('data_scadenza', 'desc')
                            ->get();
        
        // ==================== SCADENZE PROSSIME (30 GIORNI) ====================
        
        $prossimaScadenze = Deadline::with(['vehicle', 'user', 'subscription'])
            ->imminenti()
            ->orderBy('data_scadenza', 'asc')
            ->limit(10)
            ->get();
        
        // ==================== CALENDARIO COMPLETO ====================
        
        // Aggiungiamo 'subscription' ai with() per caricare le spese
        $calendarEvents = Deadline::with(['vehicle', 'user', 'subscription'])
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

                // Costruiamo il titolo in modo sicuro per evitare l'errore "on null"
                $soggetto = 'Famiglia'; // Fallback se non c'è né veicolo né utente
                
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
        
        // ==================== GRAFICI: SPESE ====================
        
        $spesePerTipo = Deadline::whereYear('data_scadenza', $oggi->year)
            ->selectRaw('tipo, SUM(importo) as totale')
            ->groupBy('tipo')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->tipo => $item->totale];
            });
        
        $speseMensili = [];
        for ($mese = 1; $mese <= 12; $mese++) {
            $pagatoMese = Deadline::whereYear('data_scadenza', $oggi->year)->whereMonth('data_scadenza', $mese)->where('pagato', true)->sum('importo');
            $daPagareMese = Deadline::whereYear('data_scadenza', $oggi->year)->whereMonth('data_scadenza', $mese)->where('pagato', false)->sum('importo');
            $speseMensili[] = [
                'mese' => Carbon::create($oggi->year, $mese, 1)->locale('it')->isoFormat('MMM'),
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
        $deadlines = Deadline::with(['vehicle', 'user'])->get();
        
        return view('dashboard', compact(
            'totaleVeicoli', 'totalePatenti', 'totaleScadenze', 'scadenzeImminenti',
            'scadenzeUrgenti', 'costiAnnuali', 'pagatoQuestAnno', 'daPagareQuestAnno',
            'spesaMediaVeicolo', 'budgetMensileConsigliato', 'spesoQuestoMese',
            'prossimaScadenze', 'calendarEvents', 'spesePerTipo', 'speseMensili',
            'revisioniInScadenza', 'categorieStats', 'deadlines'
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