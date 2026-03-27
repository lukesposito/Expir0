<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatenteController extends Controller
{
    public function index(Request $request)
    {
        $patenti = Patente::with('user')->withCount('deadlines')->get()->map(function($patente) {
            return [
                'id' => $patente->id,
                'numero_patente' => $patente->numero_patente,
                'tipo' => $patente->tipo,
                'data_rilascio' => $patente->data_rilascio ? $patente->data_rilascio->format('Y-m-d') : null,
                'data_rilascio_formatted' => $patente->data_rilascio ? $patente->data_rilascio->format('d/m/Y') : null,
                'importo' => $patente->importo, // Passiamo l'importo
                'deadlines_count' => $patente->deadlines_count,
                'user' => [
                    'id' => $patente->user->id,
                    'name' => $patente->user->name,
                    'email' => $patente->user->email,
                ]
            ];
        });

        // SE LA RICHIESTA VIENE DA ALPINE (AJAX), RISPONDI CON IL JSON PULITO
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['patenti' => $patenti]);
        }

        // ALTRIMENTI (al primo caricamento), CARICA LA PAGINA NORMALE
        $users = User::orderBy('name')->get();
        return view('patenti.index', compact('patenti', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'numero_patente' => 'required|string|max:20|unique:patentes',
            'tipo' => 'nullable|string|max:10',
            'data_rilascio' => 'nullable|date',
            'importo' => 'nullable|numeric|min:0'
        ]);

        $validated['numero_patente'] = strtoupper($validated['numero_patente']);
        if (!empty($validated['tipo'])) {
            $validated['tipo'] = strtoupper($validated['tipo']);
        }

        Patente::create($validated);

        // SE E' UNA CHIAMATA AJAX (come quella che fa ora Alpine), RESTITUISCI JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Patente aggiunta con successo!']);
        }

        return redirect()->route('patenti.index')->with('success', 'Patente creata!');
    }

    public function update(Request $request, Patente $patente)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'numero_patente' => 'required|string|max:20|unique:patentes,numero_patente,' . $patente->id,
            'tipo' => 'nullable|string|max:10',
            'data_rilascio' => 'nullable|date',
            'importo' => 'nullable|numeric|min:0'
        ]);

        $validated['numero_patente'] = strtoupper($validated['numero_patente']);
        if (!empty($validated['tipo'])) {
            $validated['tipo'] = strtoupper($validated['tipo']);
        }

        $patente->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Patente aggiornata con successo!']);
        }

        return redirect()->route('patenti.index')->with('success', 'Patente aggiornata!');
    }

    public function destroy(Request $request, Patente $patente)
    {
        $patente->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Patente eliminata definitivamente!']);
        }

        return redirect()->route('patenti.index')->with('success', 'Patente eliminata!');
    }
}