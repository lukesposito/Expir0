<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deadline;
use App\Models\User;
use App\Mail\DeadlineDigestMail;
use App\Notifications\DeadlinePushNotification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDeadlineReminders extends Command
{
    protected $signature = 'app:send-deadline-reminders';
    protected $description = 'Invia email e Push Notification avanzate (JSON)';

    public function handle()
    {
        $oggi = Carbon::now()->startOfDay();
        
        $scadenze = Deadline::with(['user', 'vehicle', 'subscription.user'])
                            ->where('pagato', false)
                            ->get();
                            
        $utentiValidi = User::where('is_active', true)
                            ->where('receives_notifications', true)
                            ->get();

        $digestPerUtente = []; 

        // $this->info("=======================================");
        // $this->info("Inizio controllo di " . $scadenze->count() . " scadenze non pagate...");
        // $this->info("Data di oggi considerata: " . $oggi->format('d/m/Y'));
        // $this->info("=======================================\n");

        foreach ($utentiValidi as $user) {
            if (!empty($user->email)) {
                $digestPerUtente[$user->email] = [
                    'urgente' => [],
                    'breve' => [], 
                    'avviso' => [], 
                    'annuale' => []
                ];
            }
        }

        $daAggiornare = []; 

        foreach ($scadenze as $deadline) {
            $dataScadenza = Carbon::parse($deadline->data_scadenza)->startOfDay();
            $giorniRimanenti = (int) $oggi->diffInDays($dataScadenza, false);
            $scadeQuestAnno = $dataScadenza->year === $oggi->year;
            $isTesserino = strtolower($deadline->tipo) === 'tesserino_sanitario';
            
            $isPersonal = false;
            $ownerEmail = null;
            $utenteAssociato = null;

            // Identificazione proprietario
            if (in_array(strtolower($deadline->tipo), ['patente', 'carta_identita', 'tesserino_sanitario'])) {
                if ($deadline->user && !empty($deadline->user->email)) {
                    $isPersonal = true;
                    $ownerEmail = $deadline->user->email;
                    $utenteAssociato = $deadline->user;
                }
            } elseif ($deadline->tipo === 'spesa_abbonamento') {
                $utenteAssociato = $deadline->user ?? ($deadline->subscription->user ?? null);
                if ($utenteAssociato && !empty($utenteAssociato->email)) {
                    $isPersonal = true;
                    $ownerEmail = $utenteAssociato->email;
                }
            }

            if ($ownerEmail && !isset($digestPerUtente[$ownerEmail])) {
                $digestPerUtente[$ownerEmail] = ['urgente' => [], 'breve' => [], 'avviso' => [], 'annuale' => []];
            }

            // 1. PREPARAZIONE TITOLO
            if ($isTesserino) {
                $nomeProprietario = $utenteAssociato ? $utenteAssociato->name : 'Membro Famiglia';
                $titolo = 'Tesserino Sanitario - ' . $nomeProprietario;
                $sottotitolo = 'Il nuovo documento verrà spedito automaticamente a casa dall\'Agenzia delle Entrate.';
            } else {
                $titolo = ucwords(str_replace('_', ' ', $deadline->tipo));
                $sottotitolo = 'Spesa di Famiglia';

                if ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription) {
                    $titolo = $deadline->subscription->nome;
                    
                    $parti = [];
                    if (!empty($deadline->subscription->note)) {
                        $parti[] = $deadline->subscription->note;
                    } else {
                        $parti[] = $deadline->subscription->categoria;
                    }

                    if ($isPersonal && $utenteAssociato) {
                         $parti[] = 'Intestatario: ' . $utenteAssociato->name;
                    }

                    $sottotitolo = implode(' • ', $parti);

                } else {
                    if ($deadline->tipo === 'revisione') {
                        $titolo = 'Revisione Ministeriale';
                    }
                    if ($isPersonal && $utenteAssociato) {
                        $sottotitolo = $utenteAssociato->name;
                    } elseif ($deadline->vehicle) {
                        $sottotitolo = strtoupper($deadline->vehicle->marca . ' ' . $deadline->vehicle->modello . ' (' . $deadline->vehicle->targa . ')');
                    }
                }
            }

            $dataVisualizzata = ($deadline->tipo === 'revisione') 
                ? "Entro " . ucfirst($dataScadenza->locale('it')->isoFormat('MMMM YYYY'))
                : $dataScadenza->format('d/m/Y');

            $itemData = [
                'tipo' => $titolo,
                'soggetto' => $sottotitolo,
                'data' => $dataVisualizzata,
                'giorni' => $giorniRimanenti,
                'importo' => $deadline->importo > 0 ? '€ ' . number_format($deadline->importo, 2, ',', '.') : '-'
            ];

            // $this->line("\nAnalizzo: [{$titolo}] - Scadenza: {$dataVisualizzata} -> Mancano {$giorniRimanenti} giorni.");

            // 2. LOGICA TRIGGER DINAMICA (JSON)
            $haTriggerato = false;
            $urgenza = null;
            $tipoRegola = 'sistema';
            $inviaEmail = false;
            $inviaPush = false;

            if (!$isTesserino) {
                $rules = null;

                // A. Override specifico sulla spesa
                if ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription && !empty($deadline->subscription->notification_override)) {
                    $rules = $deadline->subscription->notification_override;
                    
                    // ANTI CRASH - Sbucciamo la doppia stringa come nel frontend!
                    while(is_string($rules)) {
                        $rules = json_decode($rules, true);
                    }

                    $tipoRegola = 'personalizzata';
                    // $this->line("   -> Trovate REGOLE PERSONALIZZATE.");
                } elseif (empty($rules) && $utenteAssociato && !empty($utenteAssociato->default_notification_rules)) {
                    $rules = $utenteAssociato->default_notification_rules;
                    $tipoRegola = 'globale';
                    //$this->line("   -> Trovate REGOLE GLOBALI dell'utente.");
                }

                // B. Fallback di sistema
                if (empty($rules)) {
                    $rules = [
                        ['days_before' => 30, 'channel' => 'email'],
                        ['days_before' => 15, 'channel' => 'email'],
                        ['days_before' => 7, 'channel' => 'email']
                    ];
                    // $this->line("   -> Nessuna regola o db vuoto. Uso REGOLE DI SISTEMA (30, 15, 7 gg solo email).");
                }

                if ($giorniRimanenti >= 0) {
                    foreach ($rules as $rule) {
                        $daysBefore = (int) ($rule['days_before'] ?? 0);
                        $channel = $rule['channel'] ?? 'email';

                        if ($giorniRimanenti === $daysBefore) {
                            $haTriggerato = true;
                            if ($daysBefore <= 7) $urgenza = 'urgente';
                            elseif ($daysBefore <= 15) $urgenza = 'breve';
                            else $urgenza = 'avviso';
                            
                            if (in_array($channel, ['email', 'both'])) $inviaEmail = true;
                            if (in_array($channel, ['push', 'both'])) $inviaPush = true;
                            
                            // $this->info("   [!] MATCH PERFETTO: Regola per {$daysBefore} gg (Canale: " . strtoupper($channel) . ")!");
                        }
                    }
                    if (!$haTriggerato) {
                        // $this->line("   [x] Nessun match. I giorni rimanenti non coincidono con le regole.");
                    }
                } elseif ($giorniRimanenti < 0 && $giorniRimanenti >= -30) {
                    if (abs($giorniRimanenti) % 7 === 0) {
                        $haTriggerato = true;
                        $urgenza = 'urgente';
                        $tipoRegola = 'sollecito';
                        $inviaEmail = true; // Di default i solleciti scaduti li mandiamo via email
                        //$this->info("   [!] MATCH SCADUTO: Invio sollecito settimanale.");
                    }
                }
            }

            // 3. Documenti a Lungo Termine (Annuale)
            $documentiLungoTermine = ['patente', 'carta_identita', 'tesserino_sanitario'];
            if (in_array(strtolower($deadline->tipo), $documentiLungoTermine) && $scadeQuestAnno && !$deadline->notified_year) {
                $urgenza = 'annuale';
                $tipoRegola = 'sistema';
                $inviaEmail = true;
                $deadline->notified_year = true;
                $haTriggerato = true;
                $daAggiornare[] = $deadline; 
                //$this->info("   [!] MATCH ANNUALE DOCUMENTO: Attivato.");
            }

            // 4. ESECUZIONE NOTIFICHE (PUSH IMMEDIATA)
            if ($haTriggerato && $urgenza) {
                $itemData['tipo_regola'] = $tipoRegola; 

                // --- INVIO PUSH IMMEDIATO ---
                if ($inviaPush) {
                    if (isset($isPersonal) && $isPersonal && $utenteAssociato) {
                        // $this->line("   -> 📱 Tento l'invio PUSH a: {$utenteAssociato->name}");
                        try {
                            $utenteAssociato->notify(new DeadlinePushNotification($itemData));
                            // $this->info("      ✅ PUSH INVIATA CON SUCCESSO!");
                        } catch (\Exception $e) {
                            $this->error("      ❌ Errore invio Push: " . $e->getMessage());
                        }
                    } elseif (!empty($utentiValidi)) {
                        foreach ($utentiValidi as $user) {
                            // $this->line("   -> 📱 Tento l'invio PUSH di Famiglia a: {$user->name}");
                            try {
                                $user->notify(new DeadlinePushNotification($itemData));
                                // $this->info("      ✅ PUSH INVIATA CON SUCCESSO!");
                            } catch (\Exception $e) {
                                $this->error("      ❌ Errore invio Push: " . $e->getMessage());
                            }
                        }
                    }
                }

                // --- INSERIMENTO IN EMAIL DIGEST ---
                if ($inviaEmail) {
                    if ($isPersonal) {
                        if (isset($digestPerUtente[$ownerEmail])) {
                            $digestPerUtente[$ownerEmail][$urgenza][] = $itemData;
                            // $this->line("   -> 📧 Aggiunto al digest email personale.");
                        }
                    } else {
                        foreach ($utentiValidi as $user) {
                            if (isset($digestPerUtente[$user->email])) {
                                $digestPerUtente[$user->email][$urgenza][] = $itemData;
                            }
                        }
                        // $this->line("   -> 📧 Aggiunto al digest email di Famiglia.");
                    }
                }
            }
        }

        // 5. INVIO EMAIL
        $emailInviate = 0;
        foreach ($digestPerUtente as $email => $notifiche) {
            $totale = count($notifiche['urgente']) + count($notifiche['breve']) + count($notifiche['avviso']) + count($notifiche['annuale']);
            if ($totale > 0) {
                // $this->line("\n📧 Invio email a {$email}...");
                try {
                    Mail::to($email)->send(new DeadlineDigestMail($notifiche));
                    $emailInviate++;
                    // $this->info("   ✅ Email inviata con successo!");
                } catch (\Exception $e) {
                    $this->error("   ❌ Errore invio Email: " . $e->getMessage());
                }
            }
        }

        foreach ($daAggiornare as $deadline) { 
            $deadline->save(); 
        }
        
        // $this->info("\n=======================================");
        // $this->info("Operazione conclusa. Inviate {$emailInviate} email.");
        // $this->info("=======================================");
    }
}