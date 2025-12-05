<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VehicleDetailController extends Controller
{
    public function show(Vehicle $vehicle)
    {
        $scadenze = Deadline::where('vehicle_id', $vehicle->id)
            ->with('vehicle')
            ->orderBy('data_scadenza')
            ->get();
        
        $revisioni = Revisione::where('vehicle_id', $vehicle->id)
            ->orderBy('data_prossima_revisione', 'desc')
            ->get();
        
        return view('vehicles.detail', compact('vehicle', 'scadenze', 'revisioni'));
    }
}
