<?php

namespace App\Http\Controllers;

use App\Models\Deadline;
use App\Models\Vehicle;
use App\Models\Patente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DeadlineController extends Controller
{
    public function index()
    {
        $deadlines = Deadline::with(['vehicle', 'user', 'revisione'])
            ->orderBy('data_scadenza')
            ->get()  // Usa get() invece di paginate() per Alpine.js
            ->map(function($deadline) {
                $oggi = Carbon::now();
                $scadenza = Carbon::parse($deadline->data_scadenza);
                $giorniRimanenti = $oggi->diffInDays($scadenza, false);
                
                return [
                    'id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza->format('Y-m-d'),
                    'data_scadenza_formatted' => $deadline->data_scadenza->format('d/m/Y'),
                    'importo' => $deadline->importo ?? 0,
                    'importo_formatted' => number_format($deadline->importo ?? 0, 2, ',', '.'),
                    'note' => $deadline->note,
                    'giorni_rimanenti' => (int) $giorniRimanenti,
                    'urgenza' => $giorniRimanenti <= 10 ? 'urgente' : 'normale',
                    'scaduta' => $giorniRimanenti < 0,
                    'pagato' => (bool) ($deadline->pagato ?? false),
                    
                    // Dati veicolo (per bollo/assicurazione)
                    'vehicle_id' => $deadline->vehicle_id,
                    'vehicle_targa' => $deadline->vehicle ? $deadline->vehicle->targa : null,
                    'vehicle_marca' => $deadline->vehicle ? $deadline->vehicle->marca : null,
                    'vehicle_modello' => $deadline->vehicle ? $deadline->vehicle->modello : null,
                    
                    // Dati utente (per patente)
                    'user_id' => $deadline->user_id,
                    'user_name' => $deadline->user ? $deadline->user->name : null,
                    'user_email' => $deadline->user ? $deadline->user->email : null,

                    // Dati revisione (se collegata)
                    'revisione_id' => $deadline->revisione_id,
                    'revisione_esito' => $deadline->revisione ? $deadline->revisione->esito : null,
                    'revisione_centro' => $deadline->revisione ? $deadline->revisione->centro_revisione : null,
                ];
            });
        
        $vehicles = Vehicle::all();
        $users = User::all();
        
        return view('deadlines.index', compact('deadlines', 'vehicles', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'user_id' => 'nullable|exists:users,id',
            'tipo' => 'required|in:bollo,assicurazione,patente,revisione',
            'data_scadenza' => 'required|date|after:today',
            'importo' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'pagato' => 'boolean',
        ]);

        // Validazione logica
        if ($validated['tipo'] === 'patente') {
            if (empty($validated['user_id'])) {
                return back()->withErrors(['user_id' => 'Seleziona la persona per la scadenza patente']);
            }
            $validated['vehicle_id'] = null;
        } elseif ($validated['tipo'] === 'revisione') {
            // Per revisioni create manualmente (non da tabella revisioni)
            if (empty($validated['vehicle_id'])) {
                return back()->withErrors(['vehicle_id' => 'Seleziona un veicolo']);
            }
            $validated['user_id'] = null;
        } else {
            // Per bollo/assicurazione
            if (empty($validated['vehicle_id'])) {
                return back()->withErrors(['vehicle_id' => 'Seleziona un veicolo']);
            }
            $validated['user_id'] = null;
        }

        Deadline::create($validated);
        return redirect()->route('deadlines.index')->with('success', 'Scadenza aggiunta con successo');
    }

    public function edit(Deadline $deadline)
    {
        $vehicles = Vehicle::all();
        $users = User::all();
        return view('deadlines.edit', compact('deadline', 'vehicles', 'users'));
    }

    public function update(Request $request, Deadline $deadline)
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'user_id' => 'nullable|exists:users,id',
            'tipo' => 'required|in:bollo,assicurazione,patente',
            'data_scadenza' => 'required|date',
            'importo' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'pagato' => 'nullable|boolean',
        ]);

        // Validazione logica
        if ($validated['tipo'] === 'patente') {
            if (empty($validated['user_id'])) {
                return back()->withErrors(['user_id' => 'Seleziona la persona per la scadenza patente']);
            }
            $validated['vehicle_id'] = null;
        } else {
            if (empty($validated['vehicle_id'])) {
                return back()->withErrors(['vehicle_id' => 'Seleziona un veicolo']);
            }
            $validated['user_id'] = null;
        }

        $deadline->update($validated);
        return redirect()->route('deadlines.index')->with('success', 'Scadenza aggiornata con successo');
    }

    public function destroy(Deadline $deadline)
    {
        $deadline->delete();
        return redirect()->route('deadlines.index')->with('success', 'Scadenza eliminata con successo');
    }

    public function togglePagato(Deadline $deadline)
    {
        // Toggle diretto con confronto esplicito
        $deadline->pagato = ($deadline->pagato == 1) ? 0 : 1;
        $deadline->save();
        
        // return redirect()->back()->with('success', $deadline->pagato ? 'Scadenza marcata come pagata' : 'Scadenza marcata come non pagata');
        return response()->noContent();
    }
}