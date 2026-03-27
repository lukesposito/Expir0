<?php

namespace App\Http\Controllers;

use App\Models\CartaIdentita;
use App\Models\User;
use Illuminate\Http\Request;

class CartaIdentitaController extends Controller
{
    public function index(Request $request)
    {
        // Recuperiamo tutte le carte d'identità con i relativi utenti
        $documenti = CartaIdentita::with('user')->orderBy('data_scadenza', 'asc')->get()->map(function($doc) {
            return [
                'id' => $doc->id,
                'user_id' => $doc->user_id,
                'numero' => $doc->numero,
                'comune_rilascio' => $doc->comune_rilascio,
                'data_rilascio' => $doc->data_rilascio ? $doc->data_rilascio->format('Y-m-d') : null,
                'data_scadenza' => $doc->data_scadenza ? $doc->data_scadenza->format('Y-m-d') : null,
                'note' => $doc->note,
                'user' => [
                    'id' => $doc->user->id,
                    'name' => $doc->user->name,
                    'email' => $doc->user->email,
                ]
            ];
        });

        // RISPOSTA ASINCRONA JSON PER ALPINE.JS (SPA)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['documenti' => $documenti]);
        }

        // Recuperiamo gli utenti per la tendina di selezione
        $users = User::orderBy('name')->get(); 

        return view('carte-identita.index', compact('documenti', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'numero' => 'required|string|max:50',
            'comune_rilascio' => 'nullable|string|max:100',
            'data_rilascio' => 'nullable|date',
            'data_scadenza' => 'required|date',
            'note' => 'nullable|string'
        ]);

        CartaIdentita::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Carta d\'Identità aggiunta con successo!']);
        }

        return redirect()->route('carte-identita.index')->with('success', 'Carta d\'Identità aggiunta con successo!');
    }

    public function update(Request $request, CartaIdentita $cartaIdentita)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'numero' => 'required|string|max:50',
            'comune_rilascio' => 'nullable|string|max:100',
            'data_rilascio' => 'nullable|date',
            'data_scadenza' => 'required|date',
            'note' => 'nullable|string'
        ]);

        $cartaIdentita->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Carta d\'Identità aggiornata con successo!']);
        }

        return redirect()->route('carte-identita.index')->with('success', 'Carta d\'Identità aggiornata con successo!');
    }

    public function destroy(Request $request, CartaIdentita $cartaIdentita)
    {
        $cartaIdentita->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Carta d\'Identità eliminata definitivamente!']);
        }

        return redirect()->route('carte-identita.index')->with('success', 'Carta d\'Identità eliminata!');
    }
}