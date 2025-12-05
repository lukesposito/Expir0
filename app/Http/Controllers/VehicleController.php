<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::withCount('deadlines')->get();
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

        Vehicle::create($validated);
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

        $vehicle->update($validated);
        return redirect()->route('vehicles.index')->with('success', 'Veicolo aggiornato');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('success', 'Veicolo eliminato');
    }
}