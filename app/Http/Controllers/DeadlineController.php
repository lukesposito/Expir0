<?php

namespace App\Http\Controllers;

use App\Models\Deadline;
use App\Models\Vehicle;
use App\Models\Patente;
use App\Models\Revisione;
use App\Models\User;
use App\Models\Bollo;
use App\Models\Assicurazione;
use App\Models\CartaIdentita;
use App\Models\TesserinoSanitario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DeadlineController extends Controller
{
    public function index(Request $request)
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
        
        // RISPOSTA ASINCRONA JSON PER ALPINE.JS (SPA)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['deadlines' => $deadlines]);
        }

        $vehicles = Vehicle::all();
        $users = User::all();
        
        return view('deadlines.index', compact('deadlines', 'vehicles', 'users'));
    }

    public function store(Request $request)
    {
        if ($request->tipo === 'patente') {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'numero_patente' => 'required|string|max:20',
                'data_rilascio' => 'required|date',
                'importo' => 'nullable|numeric|min:0',
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Patente registrata e scadenza calcolata!']);
            }
            return redirect()->route('deadlines.index')->with('success', 'Patente registrata e scadenza calcolata in automatico!');
        }

        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'user_id' => 'nullable|exists:users,id',
            'tipo' => 'required|in:bollo,assicurazione,patente,revisione',
            'data_scadenza' => 'required|date|after:today',
            'importo' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'pagato' => 'boolean',
        ]);

        if ($validated['tipo'] === 'patente') {
            if (empty($validated['user_id'])) return back()->withErrors(['user_id' => 'Seleziona la persona per la scadenza patente']);
            $validated['vehicle_id'] = null;
        } elseif ($validated['tipo'] === 'revisione') {
            if (empty($validated['vehicle_id'])) return back()->withErrors(['vehicle_id' => 'Seleziona un veicolo']);
            $validated['user_id'] = null;
        } else {
            if (empty($validated['vehicle_id'])) return back()->withErrors(['vehicle_id' => 'Seleziona un veicolo']);
            $validated['user_id'] = null;
        }

        Deadline::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Scadenza aggiunta con successo!']);
        }
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

        if ($request->tipo === 'patente') {
            $patente = Patente::where('user_id', $deadline->getOriginal('user_id'))->first();
            if ($patente) {
                $patente->update([
                    'user_id' => $request->user_id,
                    'importo' => $request->importo,
                ]);
            }
        }

        $deadline->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Scadenza aggiornata con successo!']);
        }
        return redirect()->route('deadlines.index')->with('success', 'Scadenza aggiornata con successo');
    }

    // Aggiunto Request per la SPA
    public function destroy(Request $request, Deadline $deadline)
    {
        // Elimina record collegati in base al tipo
        if ($deadline->tipo === 'patente' && $deadline->user_id) {
            Patente::where('user_id', $deadline->user_id)->delete();
        } elseif ($deadline->tipo === 'revisione' && $deadline->revisione_id) {
            Revisione::find($deadline->revisione_id)?->delete();
        } elseif ($deadline->tipo === 'bollo' && $deadline->bollo_id) {
            Bollo::find($deadline->bollo_id)?->delete();
        } elseif ($deadline->tipo === 'assicurazione' && $deadline->assicurazione_id) {
            Assicurazione::find($deadline->assicurazione_id)?->delete();
        } elseif ($deadline->tipo === 'carta_identita' && $deadline->carta_identita_id) {
            CartaIdentita::find($deadline->carta_identita_id)?->delete();
        } elseif ($deadline->tipo === 'tesserino_sanitario' && $deadline->tesserino_sanitario_id) {
            TesserinoSanitario::find($deadline->tesserino_sanitario_id)?->delete();
        }

        $deadline->delete();
        
        // RISPOSTA ASINCRONA JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Scadenza e dati collegati eliminati!']);
        }
        return redirect()->route('deadlines.index')->with('success', 'Scadenza e dati collegati eliminati dal sistema!');
    }

    public function togglePagato(Deadline $deadline)
    {
        $deadline->pagato = ($deadline->pagato == 1) ? 0 : 1;
        $deadline->save();
        
        // Rispondiamo in JSON per il Javascript
        return response()->json([
            'success' => true, 
            'pagato' => $deadline->pagato,
            'message' => 'Stato pagamento aggiornato!'
        ]);
    }
}