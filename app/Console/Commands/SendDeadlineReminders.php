<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deadline;
use App\Models\User;
use App\Mail\DeadlineDigestMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDeadlineReminders extends Command
{
    protected $signature = 'app:send-deadline-reminders';
    protected $description = 'Invia una singola email giornaliera per utente con scadenze personali e familiari combinate (Motore Avanzato JSON)';

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

        $this->info("=======================================");
        $this->info("Inizio controllo di " . $scadenze->count() . " scadenze non pagate...");
        $this->info("Data di oggi considerata: " . $oggi->format('d/m/Y'));
        $this->info("=======================================\n");

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

            // Inizializza array se l'utente esiste ma non ha receives_notifications (salvaguardia)
            if ($ownerEmail && !isset($digestPerUtente[$ownerEmail])) {
                $digestPerUtente[$ownerEmail] = ['urgente' => [], 'breve' => [], 'avviso' => [], 'annuale' => []];
            }

            // 1. PREPARAZIONE TITOLO E SOTTOTITOLO EMAIL
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

            $this->line("Analizzo: [{$titolo}] - Scadenza: {$dataVisualizzata} -> Mancano {$giorniRimanenti} giorni.");

            // 2. LOGICA TRIGGER DINAMICA (JSON)
            $haTriggerato = false;
            $urgenza = null;
            $tipoRegola = 'sistema';

            if (!$isTesserino) {
                $rules = null;

                // A. Override specifico sulla spesa
                if ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription && !empty($deadline->subscription->notification_override)) {
                    $rules = $deadline->subscription->notification_override;
                    $tipoRegola = 'personalizzata';
                    $this->line("   -> Trovate REGOLE PERSONALIZZATE sulla spesa.");
                } 
                // B. Impostazioni Globali Utente
                elseif (empty($rules) && $utenteAssociato && !empty($utenteAssociato->default_notification_rules)) {
                    $rules = $utenteAssociato->default_notification_rules;
                    $tipoRegola = 'globale';
                    $this->line("   -> Trovate REGOLE GLOBALI dell'utente.");
                }

                // C. Fallback di sistema
                if (empty($rules)) {
                    $rules = [
                        ['days_before' => 30, 'channel' => 'email'],
                        ['days_before' => 15, 'channel' => 'email'],
                        ['days_before' => 7, 'channel' => 'email']
                    ];
                    $this->line("   -> Uso REGOLE DI SISTEMA (30, 15, 7 gg).");
                }

                // Match esatto del giorno prima della scadenza
                if ($giorniRimanenti >= 0) {
                    foreach ($rules as $rule) {
                        $daysBefore = (int) ($rule['days_before'] ?? 0);
                        $channel = $rule['channel'] ?? 'email';

                        if ($giorniRimanenti === $daysBefore && $channel === 'email') {
                            $haTriggerato = true;
                            if ($daysBefore <= 7) $urgenza = 'urgente';
                            elseif ($daysBefore <= 15) $urgenza = 'breve';
                            else $urgenza = 'avviso';
                            
                            $this->info("   [!] MATCH PERFETTO: Regola per {$daysBefore} gg attivata! (Livello: {$urgenza})");
                            break;
                        }
                    }
                    if (!$haTriggerato) {
                        $this->line("   [x] Nessun match. I giorni rimanenti ({$giorniRimanenti}) non coincidono.");
                    }
                } 
                // Solleciti per le scadenze superate
                elseif ($giorniRimanenti < 0 && $giorniRimanenti >= -30) {
                    if (abs($giorniRimanenti) % 7 === 0) {
                        $haTriggerato = true;
                        $urgenza = 'urgente';
                        $tipoRegola = 'sollecito';
                        $this->info("   [!] MATCH SCADUTO: Invio sollecito settimanale.");
                    } else {
                        $this->line("   [x] Scaduto, ma non è il giorno del sollecito settimanale.");
                    }
                }
            }

            // 3. Documenti a Lungo Termine (Annuale)
            $documentiLungoTermine = ['patente', 'carta_identita', 'tesserino_sanitario'];
            if (in_array(strtolower($deadline->tipo), $documentiLungoTermine) && $scadeQuestAnno && !$deadline->notified_year) {
                $urgenza = 'annuale';
                $tipoRegola = 'sistema';
                $deadline->notified_year = true;
                $haTriggerato = true;
                $daAggiornare[] = $deadline; 
                $this->info("   [!] MATCH ANNUALE DOCUMENTO: Attivato.");
            }

            // 4. INSERIMENTO NEL DIGEST
            if ($haTriggerato && $urgenza) {
                
                // Passiamo l'etichetta alla mail grafica
                $itemData['tipo_regola'] = $tipoRegola; 

                if ($isPersonal) {
                    if (isset($digestPerUtente[$ownerEmail])) {
                        $digestPerUtente[$ownerEmail][$urgenza][] = $itemData;
                        $this->line("   -> Aggiunto al digest email personale di: {$ownerEmail}\n");
                    }
                } else {
                    foreach ($utentiValidi as $user) {
                        if (isset($digestPerUtente[$user->email])) {
                            $digestPerUtente[$user->email][$urgenza][] = $itemData;
                        }
                    }
                    $this->line("   -> Aggiunto al digest email di Famiglia.\n");
                }
            } else {
                $this->line("\n");
            }
        }

        // 5. INVIO EMAIL
        $emailInviate = 0;
        foreach ($digestPerUtente as $email => $notifiche) {
            $totale = count($notifiche['urgente']) + count($notifiche['breve']) + count($notifiche['avviso']) + count($notifiche['annuale']);
            if ($totale > 0) {
                $this->line("Invio email a {$email} con {$totale} notifiche...");
                Mail::to($email)->send(new DeadlineDigestMail($notifiche));
                $emailInviate++;
            }
        }

        // Salviamo solo i documenti annuali che hanno flaggato "notified_year"
        // Le notifiche giornaliere non usano più il salvataggio su DB, perché ricalcolano i giorni dal vivo!
        foreach ($daAggiornare as $deadline) { 
            $deadline->save(); 
        }
        
        $this->info("=======================================");
        $this->info("Operazione conclusa. Inviate {$emailInviate} email riepilogative aggiornate.");
        $this->info("=======================================");
    }
}