<?php

namespace App\Http\Controllers;

use App\Models\CartaIdentita;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CartaIdentitaController extends Controller
{
    public function index(Request $request)
    {
        // Recuperiamo tutte le carte d'identità con i relativi utenti
        $documenti = CartaIdentita::with('user')
            ->whereHas('deadlines', function($query) {
                $query->where('pagato', false);
            })
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'user_id' => $doc->user_id,
                    'numero' => $doc->numero,
                    'comune_rilascio' => $doc->comune_rilascio,
                    'data_rilascio' => $doc->data_rilascio ? $doc->data_rilascio->format('Y-m-d') : null,
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
            'data_rilascio' => 'required|date',
            'data_scadenza' => 'nullable|date',
            'note' => 'nullable|string',
            'notification_override' => 'nullable|string',
        ]);

        if (empty($validated['data_scadenza']) && !empty($validated['data_rilascio'])) {
            $validated['data_scadenza'] = $this->calcolaScadenzaCartaIdentita(
                $validated['user_id'],
                $validated['data_rilascio']
            )->format('Y-m-d');
        }

        if ($request->notification_override && $request->notification_override !== 'null') {
            $validated['notification_override'] = json_decode($request->notification_override, true);
        } else {
            $validated['notification_override'] = null;
        }

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
            'data_rilascio' => 'required|date',
            'data_scadenza' => 'nullable|date',
            'note' => 'nullable|string',
            'notification_override' => 'nullable|string'
        ]);

        if (empty($validated['data_scadenza']) && !empty($validated['data_rilascio'])) {
            $validated['data_scadenza'] = $this->calcolaScadenzaCartaIdentita(
                $validated['user_id'],
                $validated['data_rilascio']
            )->format('Y-m-d');
        }

        if ($request->notification_override && $request->notification_override !== 'null') {
            $validated['notification_override'] = json_decode($request->notification_override, true);
        } else {
            $validated['notification_override'] = null;
        }

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

    private function calcolaScadenzaCartaIdentita($userId, $dataRichiestaStr)
    {
        $user = User::find($userId);
        $dataRichiesta = Carbon::parse($dataRichiestaStr);

        if (!$user || !$user->data_nascita) {
            return $dataRichiesta->copy()->addYears(10);
        }

        $dataNascita = Carbon::parse($user->data_nascita);
        $eta = $dataNascita->diffInYears($dataRichiesta);

        if ($eta < 3) {
            $base = $dataRichiesta->copy()->addYears(3);
        } elseif ($eta < 18) {
            $base = $dataRichiesta->copy()->addYears(5);
        } else {
            $base = $dataRichiesta->copy()->addYears(9);
        }

        $scadenza = $dataNascita->copy()->year($base->year);

        if ($scadenza->lt($base)) {
            $scadenza->addYear();
        }

        return $scadenza;
    }
}