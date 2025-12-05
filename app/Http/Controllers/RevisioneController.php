<?php

namespace App\Http\Controllers;

use App\Models\Revisione;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RevisioneController extends Controller
{
    public function index()
    {
        $revisioni = Revisione::with('vehicle')
            ->orderBy('data_prossima_revisione', 'desc')
            ->get()
            ->map(function($revisione) {
                $oggi = Carbon::now();
                $prossima = Carbon::parse($revisione->data_prossima_revisione);
                $giorniRimanenti = $oggi->diffInDays($prossima, false);
                
                return [
                    'id' => $revisione->id,
                    'vehicle_id' => $revisione->vehicle_id,
                    'vehicle_targa' => $revisione->vehicle->targa,
                    'vehicle_marca' => $revisione->vehicle->marca,
                    'vehicle_modello' => $revisione->vehicle->modello,
                    'data_ultima_revisione' => $revisione->data_ultima_revisione->format('Y-m-d'),
                    'data_ultima_revisione_formatted' => $revisione->data_ultima_revisione->format('d/m/Y'),
                    'data_prossima_revisione' => $revisione->data_prossima_revisione->format('Y-m-d'),
                    'data_prossima_revisione_formatted' => $revisione->data_prossima_revisione->format('d/m/Y'),
                    'importo' => $revisione->importo ?? 0,
                    'importo_formatted' => number_format($revisione->importo ?? 0, 2, ',', '.'),
                    'centro_revisione' => $revisione->centro_revisione,
                    'esito' => $revisione->esito,
                    'note' => $revisione->note,
                    'giorni_rimanenti' => (int) $giorniRimanenti,
                ];
            });
        
        $vehicles = Vehicle::all();  // Tutti i veicoli della famiglia
        
        return view('revisioni.index', compact('revisioni', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'data_ultima_revisione' => 'required|date',
            'data_prossima_revisione' => 'required|date|after:data_ultima_revisione',
            'importo' => 'nullable|numeric|min:0',
            'centro_revisione' => 'nullable|string|max:255',
            'esito' => 'required|in:positivo,negativo,da_fare',
            'note' => 'nullable|string|max:500',
        ]);

        Revisione::create($validated);
        return redirect()->route('revisioni.index')->with('success', 'Revisione aggiunta con successo');
    }

    public function update(Request $request, Revisione $revisione)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'data_ultima_revisione' => 'required|date',
            'data_prossima_revisione' => 'required|date|after:data_ultima_revisione',
            'importo' => 'nullable|numeric|min:0',
            'centro_revisione' => 'nullable|string|max:255',
            'esito' => 'required|in:positivo,negativo,da_fare',
            'note' => 'nullable|string|max:500',
        ]);

        $revisione->update($validated);
        return redirect()->route('revisioni.index')->with('success', 'Revisione aggiornata con successo');
    }

    public function destroy(Revisione $revisione)
    {
        $revisione->delete();
        return redirect()->route('revisioni.index')->with('success', 'Revisione eliminata con successo');
    }
}