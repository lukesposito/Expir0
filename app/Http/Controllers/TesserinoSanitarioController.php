<?php

namespace App\Http\Controllers;

use App\Models\TesserinoSanitario;
use App\Models\User;
use Illuminate\Http\Request;

class TesserinoSanitarioController extends Controller
{
    public function index(Request $request)
    {
        // Recuperiamo tutti i tesserini con i relativi utenti
        $documenti = TesserinoSanitario::with('user')
            ->whereHas('deadlines', function($query) {
                $query->where('pagato', false);
            })
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'user_id' => $doc->user_id,
                    'codice_fiscale' => $doc->codice_fiscale,
                    'data_scadenza' => $doc->data_scadenza ? $doc->data_scadenza->format('Y-m-d') : null,
                    'note' => $doc->note,
                    'notification_override' => $doc->notification_override,
                    'user' => [
                        'id' => $doc->user->id,
                        'name' => $doc->user->name,
                        'email' => $doc->user->email,
                    ]
                ];
            });

        // RISPOSTA ASINCRONA JSON PER ALPINE.JS
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['documenti' => $documenti]);
        }

        $users = User::orderBy('name')->get();

        return view('tesserini-sanitari.index', compact('documenti', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'codice_fiscale' => 'required|string|size:16', // Il CF italiano è di 16 caratteri
            'data_scadenza' => 'required|date',
            'note' => 'nullable|string',
            'notification_override' => 'nullable|string'
        ]);

        if ($request->notification_override && $request->notification_override !== 'null') {
            $validated['notification_override'] = json_decode($request->notification_override, true);
        } else {
            $validated['notification_override'] = null;
        }

        // Assicuriamoci che il CF sia sempre maiuscolo
        $validated['codice_fiscale'] = strtoupper($validated['codice_fiscale']);

        TesserinoSanitario::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Tesserino Sanitario aggiunto con successo!']);
        }

        return redirect()->route('tesserini-sanitari.index')->with('success', 'Tesserino Sanitario aggiunto con successo!');
    }

    public function update(Request $request, TesserinoSanitario $tesserinoSanitario)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'codice_fiscale' => 'required|string|size:16',
            'data_scadenza' => 'required|date',
            'note' => 'nullable|string',
            'notification_override' => 'nullable|string'
        ]);

        if ($request->notification_override && $request->notification_override !== 'null') {
            $validated['notification_override'] = json_decode($request->notification_override, true);
        } else {
            $validated['notification_override'] = null;
        }

        $validated['codice_fiscale'] = strtoupper($validated['codice_fiscale']);

        $tesserinoSanitario->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Tesserino Sanitario aggiornato con successo!']);
        }

        return redirect()->route('tesserini-sanitari.index')->with('success', 'Tesserino Sanitario aggiornato!');
    }

    public function destroy(Request $request, TesserinoSanitario $tesserinoSanitario)
    {
        $tesserinoSanitario->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Tesserino Sanitario eliminato definitivamente!']);
        }

        return redirect()->route('tesserini-sanitari.index')->with('success', 'Tesserino Sanitario eliminato!');
    }
}