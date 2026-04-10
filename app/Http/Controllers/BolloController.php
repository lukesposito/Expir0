<?php

namespace App\Http\Controllers;

use App\Models\Bollo;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BolloController extends Controller
{
    public function index(Request $request)
    {
        $bolli = Bollo::with('vehicle')
            ->whereHas('deadlines', function($query) {
                $query->where('pagato', false);
            })
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function($bollo) {
                $oggi = Carbon::now();
                $scadenza = Carbon::parse($bollo->data_scadenza);
                $giorniRimanenti = $oggi->diffInDays($scadenza, false);
                
                return [
                    'id' => $bollo->id,
                    'vehicle_id' => $bollo->vehicle_id,
                    'vehicle_targa' => $bollo->vehicle ? $bollo->vehicle->targa : 'N/D',
                    'vehicle_marca' => $bollo->vehicle ? $bollo->vehicle->marca : '',
                    'vehicle_modello' => $bollo->vehicle ? $bollo->vehicle->modello : '',
                    'data_scadenza' => $bollo->data_scadenza->format('Y-m-d'),
                    'data_scadenza_formatted' => $bollo->data_scadenza->format('d/m/Y'),
                    'importo' => $bollo->importo ?? 0,
                    'importo_formatted' => number_format($bollo->importo ?? 0, 2, ',', '.'),
                    'note' => $bollo->note,
                    'giorni_rimanenti' => (int) $giorniRimanenti,
                ];
            });
        
        // RISPOSTA ASINCRONA JSON PER ALPINE.JS
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['bolli' => $bolli]);
        }

        $vehicles = Vehicle::all();
        
        return view('bolli.index', compact('bolli', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'data_scadenza' => 'required|date',
            'importo' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        Bollo::create($validated);

        // SE LA RICHIESTA ARRIVA DA JAVASCRIPT/FETCH
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Bollo aggiunto con successo!']);
        }

        return redirect()->route('bolli.index')->with('success', 'Bollo aggiunto con successo');
    }

    public function update(Request $request, Bollo $bollo)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'data_scadenza' => 'required|date',
            'importo' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        $bollo->update($validated);

        // SE LA RICHIESTA ARRIVA DA JAVASCRIPT/FETCH
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Bollo aggiornato con successo!']);
        }

        return redirect()->route('bolli.index')->with('success', 'Bollo aggiornato con successo');
    }

    // Aggiunto Request $request
    public function destroy(Request $request, Bollo $bollo)
    {
        $bollo->delete();

        // SE LA RICHIESTA ARRIVA DA JAVASCRIPT/FETCH
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Bollo eliminato definitivamente!']);
        }

        return redirect()->route('bolli.index')->with('success', 'Bollo eliminato con successo');
    }
}