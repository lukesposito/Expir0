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
            $esito = $this->calcolaScadenzaPatente($validated['user_id'], $validated['data_rilascio'], $validated['tipo'] ?? 'B');

            if ($esito['status'] !== 'ok_auto') {
                return back()->withErrors([
                    'data_scadenza' => $esito['messaggio']
                ])->withInput();
            }

            $validated['data_scadenza'] = $esito['data_scadenza'];
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
            $esito = $this->calcolaScadenzaPatente($validated['user_id'], $validated['data_rilascio'], $validated['tipo'] ?? 'B');

            if ($esito['status'] !== 'ok_auto') {
                return back()->withErrors([
                    'data_scadenza' => $esito['messaggio']
                ])->withInput();
            }

            $validated['data_scadenza'] = $esito['data_scadenza'];
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

    private function calcolaScadenzaPatente($userId, $dataRinnovoStr, $tipo = null)
    {
        $user = User::find($userId);
        $dataRinnovo = Carbon::parse($dataRinnovoStr);
        $tipo = strtoupper(trim($tipo ?? ''));

        if (!$user || !$user->data_nascita) {
            return [
                'status' => 'ok_auto',
                'data_scadenza' => $dataRinnovo->copy()->addYears(10)->format('Y-m-d'),
                'messaggio' => null,
            ];
        }

        $dataNascita = Carbon::parse($user->data_nascita);
        $eta = $dataNascita->diffInYears($dataRinnovo);

        $categorieLeggere = ['AM', 'A1', 'A2', 'A', 'B1', 'B', 'BE'];
        $categorieC = ['C1', 'C1E', 'C', 'CE'];
        $categorieD = ['D1', 'D1E', 'D', 'DE'];

        if (in_array($tipo, $categorieD, true)) {
            if ($eta < 60) {
                return [
                    'status' => 'ok_auto',
                    'data_scadenza' => $dataRinnovo->copy()->addYears(5)->format('Y-m-d'),
                    'messaggio' => null,
                ];
            }

            return [
                'status' => 'manuale_richiesto',
                'data_scadenza' => null,
                'messaggio' => 'Per le categorie D/D1/DE/D1E da 60 anni in poi il rinnovo va gestito manualmente con verifica Commissione Medica Locale.',
            ];
        }

        if (in_array($tipo, $categorieC, true)) {
            if ($eta < 65) {
                return [
                    'status' => 'ok_auto',
                    'data_scadenza' => $dataRinnovo->copy()->addYears(5)->format('Y-m-d'),
                    'messaggio' => null,
                ];
            }

            return [
                'status' => 'manuale_richiesto',
                'data_scadenza' => null,
                'messaggio' => 'Per le categorie C/CE da 65 anni in poi è consigliata gestione manuale in base al caso d’uso e agli eventuali limiti.',
            ];
        }

        if (in_array($tipo, $categorieLeggere, true) || $tipo === '') {
            if ($eta < 50) {
                $anniBase = 10;
            } elseif ($eta < 70) {
                $anniBase = 5;
            } elseif ($eta < 80) {
                $anniBase = 3;
            } else {
                $anniBase = 2;
            }

            $base = $dataRinnovo->copy()->addYears($anniBase - 1);
            $scadenza = $dataNascita->copy()->year($base->year);

            if ($scadenza->lt($base)) {
                $scadenza->addYear();
            }

            return [
                'status' => 'ok_auto',
                'data_scadenza' => $scadenza->format('Y-m-d'),
                'messaggio' => null,
            ];
        }

        return [
            'status' => 'manuale_richiesto',
            'data_scadenza' => null,
            'messaggio' => 'Categoria patente non riconosciuta automaticamente.',
        ];
    }
}