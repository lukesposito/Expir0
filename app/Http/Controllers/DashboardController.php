<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Patente;
use App\Models\Deadline;
use App\Models\Revisione;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $oggi = Carbon::now();
        $tra30giorni = Carbon::now()->addDays(30);
        $inizioAnno = Carbon::now()->startOfYear();
        $fineAnno = Carbon::now()->endOfYear();
        
        // ==================== CONTATORI ====================
        
        // Totale veicoli
        $totaleVeicoli = Vehicle::count();
        
        // Totale patenti
        $totalePatenti = Patente::count();
        
        // Totale scadenze
        $totaleScadenze = Deadline::count();
        
        // Scadenze imminenti (prossimi 30 giorni)
        $scadenzeImminenti = Deadline::whereBetween('data_scadenza', [$oggi, $tra30giorni])
            ->where('pagato', false)
            ->count();
        
        // Scadenze urgenti (prossimi 10 giorni + scadenze già passate non pagate)
        $scadenzeUrgenti = Deadline::where(function($query) use ($oggi) {
                $query->whereBetween('data_scadenza', [$oggi, Carbon::now()->addDays(10)])
                    ->orWhere('data_scadenza', '<', $oggi);
            })
            ->where('pagato', false)
            ->count();
        
        // ==================== STATISTICHE FINANZIARIE ====================
        
        // Costi totali anno corrente
        $costiAnnuali = Deadline::whereYear('data_scadenza', $oggi->year)
            ->sum('importo');
        
        // Già pagato quest'anno
        $pagatoQuestAnno = Deadline::whereYear('data_scadenza', $oggi->year)
            ->where('pagato', true)
            ->sum('importo');
        
        // Da pagare entro fine anno
        $daPagareQuestAnno = Deadline::whereBetween('data_scadenza', [$oggi, $fineAnno])
            ->where('pagato', false)
            ->sum('importo');
        
        // Spesa media per veicolo
        $spesaMediaVeicolo = $totaleVeicoli > 0 ? $costiAnnuali / $totaleVeicoli : 0;
        
        // Budget mensile consigliato
        $budgetMensileConsigliato = $costiAnnuali / 12;
        
        // Speso questo mese
        $spesoQuestoMese = Deadline::whereMonth('data_scadenza', $oggi->month)
            ->whereYear('data_scadenza', $oggi->year)
            ->where('pagato', true)
            ->sum('importo');
        
        // ==================== SCADENZE PROSSIME (30 GIORNI) ====================
        
        $prossimaScadenze = Deadline::with(['vehicle', 'user'])
            ->whereBetween('data_scadenza', [$oggi, $tra30giorni])
            ->where('pagato', false)
            ->orderBy('data_scadenza', 'asc')
            ->limit(10)
            ->get()
            ->map(function($deadline) use ($oggi) {
                $scadenza = Carbon::parse($deadline->data_scadenza);
                $giorniRimanenti = $oggi->diffInDays($scadenza, false);
                
                return [
                    'id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza->format('Y-m-d'),
                    'data_scadenza_formatted' => $deadline->data_scadenza->format('d/m/Y'),
                    'importo' => $deadline->importo ?? 0,
                    'importo_formatted' => number_format($deadline->importo ?? 0, 2, ',', '.'),
                    'giorni_rimanenti' => (int) $giorniRimanenti,
                    'urgente' => $giorniRimanenti <= 10,
                    'vehicle_targa' => $deadline->vehicle ? $deadline->vehicle->targa : null,
                    'vehicle_marca' => $deadline->vehicle ? $deadline->vehicle->marca : null,
                    'user_name' => $deadline->user ? $deadline->user->name : null,
                ];
            });
        
        // ==================== CALENDARIO SCADENZE MESE CORRENTE ====================
        
        $inizioMese = Carbon::now()->startOfMonth();
        $fineMese = Carbon::now()->endOfMonth();
        
        $scadenzeMese = Deadline::with(['vehicle', 'user'])
            ->whereBetween('data_scadenza', [$inizioMese, $fineMese])
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function($deadline) use ($oggi) {
                $scadenza = Carbon::parse($deadline->data_scadenza);
                $giorniRimanenti = $oggi->diffInDays($scadenza, false);
                
                return [
                    'id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'title' => ($deadline->vehicle ? $deadline->vehicle->targa : $deadline->user->name) . ' - ' . ucfirst($deadline->tipo),
                    'start' => $deadline->data_scadenza->format('Y-m-d'),
                    'importo' => $deadline->importo ?? 0,
                    'pagato' => $deadline->pagato,
                    'color' => $this->getColorByTipo($deadline->tipo, $deadline->pagato),
                    'urgente' => $giorniRimanenti <= 10,
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
            'scadenzeMese',
            'spesePerTipo',
            'revisioniInScadenza'
        ));
    }
    
    private function getColorByTipo($tipo, $pagato)
    {
        if ($pagato) {
            return '#10b981'; // Verde per pagato
        }
        
        return match($tipo) {
            'bollo' => '#eab308',        // Giallo
            'assicurazione' => '#3b82f6', // Blu
            'patente' => '#9333ea',       // Viola
            'revisione' => '#f97316',     // Arancione
            default => '#6b7280',         // Grigio
        };
    }
}