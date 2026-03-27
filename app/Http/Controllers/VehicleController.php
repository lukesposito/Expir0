<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::withCount('deadlines')->orderBy('marca')->get();
        
        // QUESTO E' QUELLO CHE PERMETTE L'AGGIORNAMENTO SILENZIOSO DA ALPINE
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['vehicles' => $vehicles]);
        }
        
        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'targa' => 'required|string|max:10|unique:vehicles',
            'marca' => 'nullable|string|max:50',
            'modello' => 'nullable|string|max:50',
        ]);

        // Forza la targa ad essere maiuscola nel DB
        $validated['targa'] = strtoupper($validated['targa']);

        Vehicle::create($validated);

        // RISPOSTA ASINCRONA JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Veicolo aggiunto con successo!']);
        }

        // RISPOSTA TRADIZIONALE
        return redirect()->route('vehicles.index')->with('success', 'Veicolo aggiunto');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'targa' => 'required|string|max:10|unique:vehicles,targa,' . $vehicle->id,
            'marca' => 'nullable|string|max:50',
            'modello' => 'nullable|string|max:50',
        ]);

        // Forza la targa ad essere maiuscola
        $validated['targa'] = strtoupper($validated['targa']);

        $vehicle->update($validated);

        // RISPOSTA ASINCRONA JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Veicolo aggiornato con successo!']);
        }

        // RISPOSTA TRADIZIONALE
        return redirect()->route('vehicles.index')->with('success', 'Veicolo aggiornato');
    }

    // Nota: ho aggiunto 'Request $request' nei parametri per poter verificare se è una chiamata AJAX
    public function destroy(Request $request, Vehicle $vehicle)
    {
        $vehicle->delete();

        // RISPOSTA ASINCRONA JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Veicolo eliminato definitivamente!']);
        }

        // RISPOSTA TRADIZIONALE
        return redirect()->route('vehicles.index')->with('success', 'Veicolo eliminato');
    }
}