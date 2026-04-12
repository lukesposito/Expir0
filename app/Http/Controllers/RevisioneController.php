<?php

namespace App\Http\Controllers;

use App\Models\Revisione;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RevisioneController extends Controller
{
    public function index(Request $request)
    {
        $revisioni = Revisione::with('vehicle')
            ->whereHas('deadlines', function($query) {
                $query->where('pagato', false);
            })
            ->orderBy('data_prossima_revisione', 'desc')
            ->get()
            ->map(function($revisione) {
                $oggi = Carbon::now()->startOfDay();
                // Calcoliamo i giorni in base alla fine del mese di scadenza
                $prossima = Carbon::parse($revisione->data_prossima_revisione)->endOfDay();
                $giorniRimanenti = $oggi->diffInDays($prossima, false);
                
                return [
                    'id' => $revisione->id,
                    'vehicle_id' => $revisione->vehicle_id,
                    'vehicle_targa' => $revisione->vehicle->targa,
                    'vehicle_marca' => $revisione->vehicle->marca,
                    'vehicle_modello' => $revisione->vehicle->modello,
                    
                    // Formattiamo in Y-m per gli input type="month" (es. 2026-08)
                    'data_ultima_revisione' => $revisione->data_ultima_revisione ? $revisione->data_ultima_revisione->format('Y-m') : null,
                    'data_prossima_revisione' => $revisione->data_prossima_revisione->format('Y-m'),
                    
                    // Formattiamo per la vista in Mese e Anno (es. "Agosto 2026")
                    'data_prossima_revisione_formatted' => ucfirst($revisione->data_prossima_revisione->locale('it')->isoFormat('MMMM YYYY')),
                    
                    'importo' => $revisione->importo ?? 0,
                    'importo_formatted' => number_format($revisione->importo ?? 0, 2, ',', '.'),
                    'centro_revisione' => $revisione->centro_revisione,
                    'esito' => $revisione->esito,
                    'note' => $revisione->note,
                    'giorni_rimanenti' => (int) $giorniRimanenti,
                ];
            });
        
        // RISPOSTA ASINCRONA JSON PER ALPINE.JS
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['revisioni' => $revisioni]);
        }
        
        $vehicles = Vehicle::all();  
        
        return view('revisioni.index', compact('revisioni', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'data_ultima_revisione' => 'nullable|date_format:Y-m',
            'data_prossima_revisione' => 'required|date_format:Y-m',
            'importo' => 'nullable|numeric|min:0',
            'centro_revisione' => 'nullable|string|max:255',
            'esito' => 'required|in:positivo,negativo,da_fare',
            'note' => 'nullable|string|max:500',
        ]);

        // Salva la scadenza all'ULTIMO giorno del mese selezionato
        $validated['data_prossima_revisione'] = Carbon::createFromFormat('Y-m', $request->data_prossima_revisione)->endOfMonth()->toDateString();
        
        if ($request->filled('data_ultima_revisione')) {
            $validated['data_ultima_revisione'] = Carbon::createFromFormat('Y-m', $request->data_ultima_revisione)->startOfMonth()->toDateString();
        } else {
            $validated['data_ultima_revisione'] = null;
        }

        Revisione::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Revisione aggiunta con successo!']);
        }

        return redirect()->route('revisioni.index')->with('success', 'Revisione aggiunta con successo');
    }

    public function update(Request $request, Revisione $revisione)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'data_ultima_revisione' => 'nullable|date_format:Y-m',
            'data_prossima_revisione' => 'required|date_format:Y-m',
            'importo' => 'nullable|numeric|min:0',
            'centro_revisione' => 'nullable|string|max:255',
            'esito' => 'required|in:positivo,negativo,da_fare',
            'note' => 'nullable|string|max:500',
        ]);

        // Salva la scadenza all'ULTIMO giorno del mese selezionato
        $validated['data_prossima_revisione'] = Carbon::createFromFormat('Y-m', $request->data_prossima_revisione)->endOfMonth()->toDateString();
        
        if ($request->filled('data_ultima_revisione')) {
            $validated['data_ultima_revisione'] = Carbon::createFromFormat('Y-m', $request->data_ultima_revisione)->startOfMonth()->toDateString();
        } else {
            $validated['data_ultima_revisione'] = null;
        }

        $revisione->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Revisione aggiornata con successo!']);
        }

        return redirect()->route('revisioni.index')->with('success', 'Revisione aggiornata con successo');
    }

    public function destroy(Request $request, Revisione $revisione)
    {
        $revisione->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Revisione eliminata definitivamente!']);
        }

        return redirect()->route('revisioni.index')->with('success', 'Revisione eliminata con successo');
    }
}