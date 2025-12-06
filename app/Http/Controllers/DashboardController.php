<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Patente;
use App\Models\Deadline;
use App\Models\Revisione;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        
        // Costi totali anno corrente (tutte le scadenze dell'anno)
        $costiAnnuali = Deadline::whereYear('data_scadenza', $oggi->year)
            ->sum('importo');
        
        // Già pagato quest'anno (usando data_pagamento se esiste, altrimenti data_scadenza e flag pagato)
        $pagatoQuestAnno = Deadline::whereYear('data_scadenza', $oggi->year)
            ->where('pagato', true)
            ->sum('importo');
        
        // Da pagare entro fine anno
        $daPagareQuestAnno = Deadline::whereBetween('data_scadenza', [$oggi, $fineAnno])
            ->where('pagato', false)
            ->sum('importo');
        
        // Spesa media per veicolo
        $spesaMediaVeicolo = $totaleVeicoli > 0 ? $costiAnnuali / $totaleVeicoli : 0;
        
        // Speso REALMENTE questo mese
        $spesoQuestoMese = Deadline::whereMonth('data_scadenza', $oggi->month)
            ->whereYear('data_scadenza', $oggi->year)
            ->where('pagato', true)
            ->sum('importo');
        
        // Trova il mese più costoso dell'anno per budget consigliato
        $mesePiuCostoso = Deadline::whereYear('data_scadenza', $oggi->year)
            ->selectRaw('MONTH(data_scadenza) as mese, SUM(importo) as totale')
            ->groupBy('mese')
            ->orderBy('totale', 'desc')
            ->first();
        
        $budgetMensileConsigliato = $mesePiuCostoso ? $mesePiuCostoso->totale : 0;
        
        // ==================== SCADENZE PROSSIME (30 GIORNI) ====================
        
        $prossimaScadenze = Deadline::with(['vehicle', 'user'])
            ->imminenti()
            ->orderBy('data_scadenza', 'asc')
            ->limit(10)
            ->get();
        
        // ==================== CALENDARIO COMPLETO (STORICO E FUTURO) ====================
        
        // Qui prendiamo TUTTE le scadenze senza filtri di data per popolare lo storico
        $calendarEvents = Deadline::with(['vehicle', 'user'])
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function($deadline) {
                // Logica Colori Avanzata
                $backgroundColor = $deadline->tipo_color; // Default dal model (giallo, blu, ecc)
                $borderColor = $deadline->tipo_color;

                if ($deadline->pagato) {
                    // SE PAGATO: Verde (sia passato che futuro)
                    $backgroundColor = '#10b981'; // Emerald 500
                    $borderColor = '#059669';     // Emerald 600
                } elseif ($deadline->data_scadenza->isPast()) {
                    // SE NON PAGATO E SCADUTO: Rosso (evidenzia il problema nello storico)
                    $backgroundColor = '#ef4444'; // Red 500
                    $borderColor = '#b91c1c';     // Red 700
                }

                return [
                    'id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'title' => ($deadline->vehicle ? $deadline->vehicle->targa : $deadline->user->name) . ' - ' . ucfirst($deadline->tipo),
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
        
        // ==================== GRAFICI: SPESE PER TIPO ====================
        
        $spesePerTipo = Deadline::whereYear('data_scadenza', $oggi->year)
            ->selectRaw('tipo, SUM(importo) as totale')
            ->groupBy('tipo')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->tipo => $item->totale];
            });
        
        // ==================== GRAFICO SPESE MENSILI (Statistiche Anno) ====================
        
        $speseMensili = [];
        for ($mese = 1; $mese <= 12; $mese++) {
            $pagatoMese = Deadline::whereYear('data_scadenza', $oggi->year)
                ->whereMonth('data_scadenza', $mese)
                ->where('pagato', true)
                ->sum('importo');
            
            $daPagareMese = Deadline::whereYear('data_scadenza', $oggi->year)
                ->whereMonth('data_scadenza', $mese)
                ->where('pagato', false)
                ->sum('importo');
            
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
        
        // ==================== STATISTICHE CATEGORIE (progress bars) ====================
        
        $categorieStats = [];
        foreach (['bollo', 'assicurazione', 'revisione', 'patente'] as $tipo) {
            $totale = Deadline::where('tipo', $tipo)->count();
            $pagate = Deadline::where('tipo', $tipo)->where('pagato', true)->count();
            $percentuale = $totale > 0 ? ($pagate / $totale) * 100 : 100;
            
            $categorieStats[$tipo] = [
                'totale' => $totale,
                'pagate' => $pagate,
                'percentuale' => round($percentuale, 0),
                'da_pagare' => $totale - $pagate
            ];
        }
        
        return view('dashboard', compact(
            'totaleVeicoli',
            'totalePatenti',
            'totaleScadenze',
            'scadenzeImminenti',
            'scadenzeUrgenti',
            'costiAnnuali',
            'pagatoQuestAnno',
            'daPagareQuestAnno',
            'spesaMediaVeicolo',
            'budgetMensileConsigliato',
            'spesoQuestoMese',
            'prossimaScadenze',
            'calendarEvents', // Variabile aggiornata per il calendario completo
            'spesePerTipo',
            'speseMensili',
            'revisioniInScadenza',
            'categorieStats'
        ));
    }
}