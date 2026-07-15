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
        $rinnova = $request->input('rinnova', false);

        if ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription && $deadline->subscription->rinnovo_automatico) {
            return response()->json([
                'success' => false,
                'message' => 'Questo abbonamento si rinnova automaticamente e non può essere marcato manualmente come pagato.'
            ], 422);
        }
        
        $deadline->pagato = ($deadline->pagato == 1) ? 0 : 1;
        $deadline->save();
        
        $messaggio = $deadline->pagato ? 'Stato aggiornato e archiviato nello storico!' : 'Pagamento annullato, scadenza ripristinata!';

        if ($deadline->pagato == 1 && $rinnova) {
            $vecchiaData = Carbon::parse($deadline->data_scadenza);
            
            $nuovaScadenza = $deadline->replicate();
            $nuovaScadenza->pagato = 0; 
            
            // --- 1. CALCOLI INTELLIGENTI DELLE DATE DI SCADENZA ---
            if ($deadline->tipo === 'revisione') {
                $nuovaScadenza->data_scadenza = $vecchiaData->copy()->addYears(2);
            } 
            elseif ($deadline->tipo === 'carta_identita') {
                $nuovaScadenza->data_scadenza = $vecchiaData->copy()->addYears(10); // Standard Italia
            } 
            elseif ($deadline->tipo === 'tesserino_sanitario') {
                $nuovaScadenza->data_scadenza = $vecchiaData->copy()->addYears(6); // Standard Italia
            } 
            elseif ($deadline->tipo === 'patente') {
                // Recuperiamo il tipo patente in modo sicuro per il ricalcolo
                $patenteInfo = Patente::where('user_id', $deadline->user_id)->first();
                $tipoPatente = $patenteInfo ? $patenteInfo->tipo : 'B';
                
                $anniRinnovo = $this->getAnniRinnovoPatente($deadline->user_id, $vecchiaData, $tipoPatente);
                $nuovaScadenza->data_scadenza = $vecchiaData->copy()->addYears($anniRinnovo);
            }
            elseif ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription_id) {
                $subscription = Subscription::find($deadline->subscription_id);
                $mesiRinnovo = max(1, (int) ($subscription->renewal_interval_months ?? 1));
                $nuovaScadenza->data_scadenza = $vecchiaData->copy()->addMonthsNoOverflow($mesiRinnovo);
            } else {
                $nuovaScadenza->data_scadenza = $vecchiaData->copy()->addYear();
            }

            // --- 2. SGANCIAMO GLI ID ---
            // Puliamo i vecchi ID per evitare che il nuovo promemoria rimanga attaccato ai vecchi record.
            if ($deadline->tipo === 'patente') $nuovaScadenza->patente_id = null;
            if ($deadline->tipo === 'revisione') $nuovaScadenza->revisione_id = null;
            if ($deadline->tipo === 'bollo') $nuovaScadenza->bollo_id = null;
            if ($deadline->tipo === 'assicurazione') $nuovaScadenza->assicurazione_id = null;
            if ($deadline->tipo === 'carta_identita') $nuovaScadenza->carta_identita_id = null;
            if ($deadline->tipo === 'tesserino_sanitario') $nuovaScadenza->tesserino_sanitario_id = null;
            if ($deadline->tipo === 'spesa_abbonamento') $nuovaScadenza->subscription_id = null;


            // --- 3. CLONAZIONE (E AGGIORNAMENTO) NELLE TABELLE MADRI ---
            if ($deadline->tipo === 'revisione' && $deadline->revisione_id) {
                $vecchio = Revisione::find($deadline->revisione_id);
                if ($vecchio) {
                    $nuovo = $vecchio->replicate();
                    $nuovo->data_ultima_revisione = $vecchio->data_prossima_revisione; 
                    $nuovo->data_prossima_revisione = $nuovaScadenza->data_scadenza;   
                    $nuovo->esito = 'da_fare'; 
                    $nuovo->saveQuietly();     
                    $nuovaScadenza->revisione_id = $nuovo->id;
                }
            } 
            elseif ($deadline->tipo === 'bollo' && $deadline->bollo_id) {
                $vecchio = Bollo::find($deadline->bollo_id);
                if ($vecchio) {
                    $nuovo = $vecchio->replicate();
                    $nuovo->data_scadenza = $nuovaScadenza->data_scadenza;
                    $nuovo->saveQuietly();
                    $nuovaScadenza->bollo_id = $nuovo->id;
                }
            } 
            elseif ($deadline->tipo === 'assicurazione' && $deadline->assicurazione_id) {
                $vecchio = Assicurazione::find($deadline->assicurazione_id);
                if ($vecchio) {
                    $nuovo = $vecchio->replicate();
                    $nuovo->data_scadenza = $nuovaScadenza->data_scadenza;
                    if ($nuovo->data_inizio) {
                        $nuovo->data_inizio = Carbon::parse($vecchio->data_scadenza)->addDay(); 
                    }
                    $nuovo->saveQuietly();
                    $nuovaScadenza->assicurazione_id = $nuovo->id;
                }
            }
            elseif ($deadline->tipo === 'carta_identita' && $deadline->carta_identita_id) {
                $vecchio = CartaIdentita::find($deadline->carta_identita_id);
                if ($vecchio) {
                    $nuovo = $vecchio->replicate();
                    $nuovo->data_scadenza = $nuovaScadenza->data_scadenza;
                    if (isset($nuovo->data_rilascio)) {
                        $nuovo->data_rilascio = $vecchio->data_scadenza;
                    }
                    $nuovo->saveQuietly();
                    $nuovaScadenza->carta_identita_id = $nuovo->id;
                }
            }
            elseif ($deadline->tipo === 'tesserino_sanitario' && $deadline->tesserino_sanitario_id) {
                $vecchio = TesserinoSanitario::find($deadline->tesserino_sanitario_id);
                if ($vecchio) {
                    $nuovo = $vecchio->replicate();
                    $nuovo->data_scadenza = $nuovaScadenza->data_scadenza;
                    $nuovo->saveQuietly();
                    $nuovaScadenza->tesserino_sanitario_id = $nuovo->id;
                }
            }
            elseif ($deadline->tipo === 'patente') {
                // Cerchiamo la patente con patente_id oppure user_id (per compatibilità con le vecchie scadenze)
                $patente = null;
                if ($deadline->patente_id) {
                    $patente = Patente::find($deadline->patente_id);
                } elseif ($deadline->user_id) {
                    $patente = Patente::where('user_id', $deadline->user_id)->first();
                }

                if ($patente) {
                    // Aggiornamento SILENZIOSO diretto sul database (ignora gli eventi del modello Patente)
                    // Questo previene al 100% che il syncDeadline duplichi la scadenza!
                    DB::table('patentes')
                        ->where('id', $patente->id)
                        ->update([
                            'data_rilascio' => $vecchiaData->format('Y-m-d'),
                            'data_scadenza' => $nuovaScadenza->data_scadenza->format('Y-m-d'),
                            'updated_at' => Carbon::now()
                        ]);
                    
                    $nuovaScadenza->patente_id = $patente->id; // Riagganciamo
                }
            }

            elseif ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription_id) {
                $subscription = Subscription::find($deadline->subscription_id);

                if ($subscription) {
                    $mesiRinnovo = max(1, (int) ($subscription->renewal_interval_months ?? 1));

                    $nuovaScadenza->data_scadenza = $vecchiaData->copy()->addMonthsNoOverflow($mesiRinnovo);

                    $subscription->data_scadenza = $nuovaScadenza->data_scadenza;
                    $subscription->saveQuietly();

                    $nuovaScadenza->subscription_id = $subscription->id;
                }
            }

            // 4. Salviamo la nuova scadenza
            $nuovaScadenza->save();
            
            $messaggio = 'Archiviato! Nuovo rinnovo generato per il ' . $nuovaScadenza->data_scadenza->format('d/m/Y') . '!';
        }
        
        return response()->json([
            'success' => true, 
            'pagato' => $deadline->pagato,
            'message' => $messaggio
        ]);
    }

    private function getAnniRinnovoPatente($userId, $dataRilascio, $tipo)
    {
        $user = User::find($userId);
        if (!$user || !$user->data_nascita) return 10;

        $eta = Carbon::parse($user->data_nascita)->diffInYears($dataRilascio);
        
        if (str_contains(strtoupper($tipo), 'C')) return 3;

        if ($eta < 50) return 10;
        if ($eta < 70) return 5;
        if ($eta < 80) return 3;
        return 2;
    }

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