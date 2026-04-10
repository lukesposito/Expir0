<?php

namespace App\Http\Controllers;

use App\Models\Assicurazione;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AssicurazioneController extends Controller
{
    public function index(Request $request)
    {
        // Aggiungiamo whereHas per filtrare solo quelle NON pagate nella dashboard delle scadenze
        $assicurazioni = Assicurazione::with('vehicle')
            ->whereHas('deadlines', function($query) {
                $query->where('pagato', false);
            })
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function($assicurazione) {
                $oggi = Carbon::now();
                $scadenza = Carbon::parse($assicurazione->data_scadenza);
                $giorniRimanenti = $oggi->diffInDays($scadenza, false);
                
                return [
                    'id' => $assicurazione->id,
                    'vehicle_id' => $assicurazione->vehicle_id,
                    'vehicle_targa' => $assicurazione->vehicle ? $assicurazione->vehicle->targa : 'N/D',
                    'vehicle_marca' => $assicurazione->vehicle ? $assicurazione->vehicle->marca : '',
                    'vehicle_modello' => $assicurazione->vehicle ? $assicurazione->vehicle->modello : '',
                    'compagnia' => $assicurazione->compagnia,
                    'polizza' => $assicurazione->polizza,
                    'data_inizio' => $assicurazione->data_inizio->format('Y-m-d'),
                    'data_inizio_formatted' => $assicurazione->data_inizio->format('d/m/Y'),
                    'data_scadenza' => $assicurazione->data_scadenza->format('Y-m-d'),
                    'data_scadenza_formatted' => $assicurazione->data_scadenza->format('d/m/Y'),
                    'importo' => $assicurazione->importo ?? 0,
                    'importo_formatted' => number_format($assicurazione->importo ?? 0, 2, ',', '.'),
                    'note' => $assicurazione->note,
                    'giorni_rimanenti' => (int) $giorniRimanenti,
                ];
            });
        
        // RISPOSTA ASINCRONA JSON PER ALPINE.JS
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['assicurazioni' => $assicurazioni]);
        }

        $vehicles = Vehicle::all();
        
        return view('assicurazioni.index', compact('assicurazioni', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'compagnia' => 'nullable|string|max:100',
            'polizza' => 'nullable|string|max:100',
            'data_inizio' => 'required|date',
            'data_scadenza' => 'required|date|after:data_inizio',
            'importo' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        Assicurazione::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Assicurazione aggiunta con successo!']);
        }

        return redirect()->route('assicurazioni.index')->with('success', 'Assicurazione aggiunta con successo');
    }

    public function update(Request $request, Assicurazione $assicurazione)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'compagnia' => 'nullable|string|max:100',
            'polizza' => 'nullable|string|max:100',
            'data_inizio' => 'required|date',
            'data_scadenza' => 'required|date|after:data_inizio',
            'importo' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        $assicurazione->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Assicurazione aggiornata con successo!']);
        }

        return redirect()->route('assicurazioni.index')->with('success', 'Assicurazione aggiornata con successo');
    }

    public function destroy(Request $request, Assicurazione $assicurazione)
    {
        $assicurazione->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Assicurazione eliminata definitivamente!']);
        }

        return redirect()->route('assicurazioni.index')->with('success', 'Assicurazione eliminata con successo');
    }
}