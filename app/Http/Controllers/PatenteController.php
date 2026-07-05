<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patente;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PatenteController extends Controller
{
    public function index(Request $request)
    {
        $patenti = Patente::with('user')
            ->whereHas('deadlines', function($query) {
                $query->where('pagato', false);
            })
            // RIMOSSO: ->withCount('deadlines')
            ->get()
            ->map(function($patente) {
                return [
                    'id' => $patente->id,
                    'numero_patente' => $patente->numero_patente,
                    'tipo' => $patente->tipo,
                    'data_rilascio' => $patente->data_rilascio ? $patente->data_rilascio->format('Y-m-d') : null,
                    'data_rilascio_formatted' => $patente->data_rilascio ? $patente->data_rilascio->format('d/m/Y') : null,
                    'data_scadenza' => $patente->data_scadenza ? $patente->data_scadenza->format('Y-m-d') : null,
                    'data_scadenza_formatted' => $patente->data_scadenza ? $patente->data_scadenza->format('d/m/Y') : null,
                    'importo' => $patente->importo,
                    'notification_override' => $patente->notification_override,
                    'user' => [
                        'id' => $patente->user->id,
                        'name' => $patente->user->name,
                        'email' => $patente->user->email,
                    ]
                ];
            });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['patenti' => $patenti]);
        }

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
            'data_scadenza' => 'nullable|date',
            'importo' => 'nullable|numeric|min:0',
            'notification_override' => 'nullable|string'
        ]);

        if ($request->notification_override && $request->notification_override !== 'null') {
            $validated['notification_override'] = json_decode($request->notification_override, true);
        } else {
            $validated['notification_override'] = null;
        }

        $validated['numero_patente'] = strtoupper($validated['numero_patente']);
        if (!empty($validated['tipo'])) {
            $validated['tipo'] = strtoupper($validated['tipo']);
        }

        if (empty($validated['data_scadenza']) && !empty($validated['data_rilascio'])) {
            $validated['data_scadenza'] = $this->calcolaScadenzaPatente($validated['user_id'], $validated['data_rilascio'], $validated['tipo'] ?? 'B');
        }

        Patente::create($validated);

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
            'data_scadenza' => 'nullable|date',
            'importo' => 'nullable|numeric|min:0',
            'notification_override' => 'nullable|string'
        ]);

        if ($request->notification_override && $request->notification_override !== 'null') {
            $validated['notification_override'] = json_decode($request->notification_override, true);
        } else {
            $validated['notification_override'] = null;
        }

        $validated['numero_patente'] = strtoupper($validated['numero_patente']);
        if (!empty($validated['tipo'])) {
            $validated['tipo'] = strtoupper($validated['tipo']);
        }

        if (empty($validated['data_scadenza']) && !empty($validated['data_rilascio'])) {
            $validated['data_scadenza'] = $this->calcolaScadenzaPatente($validated['user_id'], $validated['data_rilascio'], $validated['tipo'] ?? 'B');
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

    private function calcolaScadenzaPatente($userId, $dataRilascioStr, $tipo)
    {
        $user = User::find($userId);
        $dataRilascio = Carbon::parse($dataRilascioStr);
        $categorie = explode(',', strtoupper(str_replace(' ', '', $tipo ?? '')));
        $haPatentePesante = in_array('C', $categorie) || in_array('CE', $categorie);

        if ($user && $user->data_nascita) {
            $dataNascita = Carbon::parse($user->data_nascita);
            $eta = $dataNascita->diffInYears($dataRilascio);

            if ($haPatentePesante) {
                $anni = 3;
            } else {
                if ($eta < 50) $anni = 10;
                elseif ($eta < 70) $anni = 5;
                elseif ($eta < 80) $anni = 3;
                else $anni = 2;
            }

            return Carbon::create($dataRilascio->year + $anni, $dataNascita->month, $dataNascita->day)->format('Y-m-d');
        }

        return $dataRilascio->copy()->addYears($haPatentePesante ? 3 : 10)->format('Y-m-d');
    }
}