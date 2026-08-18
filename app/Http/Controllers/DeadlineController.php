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
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Deadlines\DeadlineRenewalService;

class DeadlineController extends Controller
{
    public function index(Request $request)
    {
        $deadlines = Deadline::with(['vehicle', 'user', 'revisione', 'subscription'])
            ->orderBy('data_scadenza')
            ->get()
            ->map(function($deadline) {
                $oggi = Carbon::now();
                $scadenza = Carbon::parse($deadline->data_scadenza);
                $giorniRimanenti = $oggi->diffInDays($scadenza, false);

                $notificationRules = [];

                if ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription) {
                    $notificationRules = $this->getSubscriptionNotificationRules($deadline->subscription);
                }
                
                return [
                    'id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza->format('Y-m-d'),
                    'data_scadenza_formatted' => $deadline->data_scadenza->format('d/m/Y'),
                    'importo' => $deadline->importo ?? 0,
                    'importo_formatted' => number_format($deadline->importo ?? 0, 2, ',', '.'),
                    'note' => $deadline->note,
                    'giorni_rimanenti' => (int) $giorniRimanenti,
                    'pagato' => (bool) $deadline->pagato,
                    
                    // Relazioni
                    'vehicle_targa' => $deadline->vehicle ? $deadline->vehicle->targa : null,
                    'vehicle_marca' => $deadline->vehicle ? $deadline->vehicle->marca : null,
                    'vehicle_modello' => $deadline->vehicle ? $deadline->vehicle->modello : null,
                    'user_name' => $deadline->user ? $deadline->user->name : null,
                    'subscription_nome' => $deadline->subscription ? $deadline->subscription->nome : null,
                    'subscription_categoria' => $deadline->subscription ? $deadline->subscription->categoria : null,

                    'notification_rules' => $notificationRules,
                    'subscription_rinnovo_automatico' => (bool) optional($deadline->subscription)->rinnovo_automatico,
                    'subscription_renewal_interval_months' => optional($deadline->subscription)->renewal_interval_months,
                ];
            });
        
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

    public function destroy(Request $request, Deadline $deadline)
    {
        // 1. Salviamo il tipo e gli ID PRIMA di cancellare la scadenza
        $tipo = $deadline->tipo;
        $userId = $deadline->user_id;
        $revisioneId = $deadline->revisione_id;
        $bolloId = $deadline->bollo_id;
        $assicurazioneId = $deadline->assicurazione_id;
        $cartaIdentitaId = $deadline->carta_identita_id;
        $tesserinoId = $deadline->tesserino_sanitario_id;
        $subscriptionId = $deadline->subscription_id;

        // 2. Cancelliamo SOLO la riga della scadenza selezionata
        $deadline->delete();

        // 3. Controlliamo se esistono ANCORA altre scadenze (es. il rinnovo) collegate allo stesso genitore.
        // Se il conteggio è 0 (non c'è più nulla), allora possiamo cancellare il genitore per tenere pulito il DB.
        
        if ($tipo === 'patente' && $userId) {
            if (Deadline::where('tipo', 'patente')->where('user_id', $userId)->count() === 0) {
                Patente::where('user_id', $userId)->delete();
            }
        } elseif ($tipo === 'revisione' && $revisioneId) {
            if (Deadline::where('tipo', 'revisione')->where('revisione_id', $revisioneId)->count() === 0) {
                Revisione::find($revisioneId)?->delete();
            }
        } elseif ($tipo === 'bollo' && $bolloId) {
            if (Deadline::where('tipo', 'bollo')->where('bollo_id', $bolloId)->count() === 0) {
                Bollo::find($bolloId)?->delete();
            }
        } elseif ($tipo === 'assicurazione' && $assicurazioneId) {
            if (Deadline::where('tipo', 'assicurazione')->where('assicurazione_id', $assicurazioneId)->count() === 0) {
                Assicurazione::find($assicurazioneId)?->delete();
            }
        } elseif ($tipo === 'carta_identita' && $cartaIdentitaId) {
            if (Deadline::where('tipo', 'carta_identita')->where('carta_identita_id', $cartaIdentitaId)->count() === 0) {
                CartaIdentita::find($cartaIdentitaId)?->delete();
            }
        } elseif ($tipo === 'tesserino_sanitario' && $tesserinoId) {
            if (Deadline::where('tipo', 'tesserino_sanitario')->where('tesserino_sanitario_id', $tesserinoId)->count() === 0) {
                TesserinoSanitario::find($tesserinoId)?->delete();
            }
        } elseif ($tipo === 'spesa_abbonamento' && $subscriptionId) {
            if (Deadline::where('tipo', 'spesa_abbonamento')->where('subscription_id', $subscriptionId)->count() === 0) {
                Subscription::find($subscriptionId)?->delete();
            }
        }

        // RISPOSTA ASINCRONA JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Scadenza eliminata con successo!']);
        }
        return redirect()->route('deadlines.index')->with('success', 'Scadenza eliminata dal sistema!');
    }

    public function togglePagato(Request $request, Deadline $deadline)
    {
        try {
            $result = app(DeadlineRenewalService::class)->toggle(
                $deadline,
                (bool) $request->input('rinnova', false),
                auth()->id()
            );

            return response()->json($result, $result['status_code'] ?? 200);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /* private function calcolaScadenzaPatente($userId, $dataRinnovoStr, $tipo = null)
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
    } */

    private function getSubscriptionNotificationRules(?Subscription $subscription): array
    {
        if (!$subscription) {
            return [];
        }

        return !empty($subscription->notification_override)
            ? $subscription->notification_override
            : [
                [
                    'days_before' => max(0, (int) ($subscription->giorni_preavviso ?? 0)),
                    'channels' => !empty($subscription->renewal_notification_channels)
                        ? $subscription->renewal_notification_channels
                        : ['email'],
                ]
            ];
    }
}