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
    protected $description = 'Invia una singola email giornaliera per utente con scadenze personali e familiari combinate';

    public function handle()
    {
        $oggi = Carbon::now()->startOfDay();
        
        // Prendiamo le scadenze non pagate
        $scadenze = Deadline::with(['user', 'vehicle', 'subscription'])
                            ->where('pagato', false)
                            ->get();
                            
        // Prendiamo solo gli utenti attivi che vogliono ricevere notifiche
        $utentiValidi = User::where('is_active', true)
                            ->where('receives_notifications', true)
                            ->get();

        $digestPerUtente = []; 

        // 1. PREPARIAMO UN "CESTO" VUOTO PER OGNI UTENTE
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
            
            // 2. CAPISCO SE È PERSONALE O DI FAMIGLIA
            $isPersonal = false;
            $ownerEmail = null;

            // Se è un documento umano o un abbonamento intestato a qualcuno, è PERSONALE
            if (in_array(strtolower($deadline->tipo), ['patente', 'carta_identita', 'tesserino_sanitario'])) {
                if ($deadline->user && !empty($deadline->user->email)) {
                    $isPersonal = true;
                    $ownerEmail = $deadline->user->email;
                }
            } elseif ($deadline->tipo === 'spesa_abbonamento') {
                if ($deadline->user && !empty($deadline->user->email)) {
                    $isPersonal = true;
                    $ownerEmail = $deadline->user->email;
                }
            }

            // 3. PREPARO I DATI PER LA MAIL
            $soggetto = 'N/D';
            if ($isPersonal) {
                $soggetto = $deadline->user->name;
            } elseif ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription) {
                $soggetto = $deadline->subscription->nome;
            } elseif ($deadline->vehicle) {
                $soggetto = strtoupper($deadline->vehicle->marca . ' ' . $deadline->vehicle->modello . ' (' . $deadline->vehicle->targa . ')');
            }

            // ==========================================
            // NUOVA GESTIONE DATE PER REVISIONE E ALTRO
            // ==========================================
            if ($deadline->tipo === 'revisione') {
                // Per le revisioni usiamo "Entro Agosto 2026"
                $dataVisualizzata = "Entro " . ucfirst($dataScadenza->locale('it')->isoFormat('MMMM YYYY'));
            } else {
                // Per tutto il resto data standard
                $dataVisualizzata = $dataScadenza->format('d/m/Y');
            }

            $itemData = [
                'tipo' => ($deadline->tipo === 'revisione') ? 'Revisione Ministeriale' : ucwords(str_replace('_', ' ', $deadline->tipo)),
                'soggetto' => $soggetto,
                'data' => $dataVisualizzata, // <- Inserisco la data dinamica
                'giorni' => $giorniRimanenti,
                'importo' => $deadline->importo > 0 ? '€ ' . number_format($deadline->importo, 2, ',', '.') : '-'
            ];

            // 4. VERIFICO L'URGENZA E I TRIGGER
            $haTriggerato = false;
            $urgenza = null;

            // Logica di scatto avvisi (7gg, 15gg, 30gg)
            if ($giorniRimanenti <= 7 && $giorniRimanenti >= -30 && !$deadline->notified_7) {
                $urgenza = 'urgente';
                $deadline->notified_7 = true; 
                $deadline->notified_15 = true; 
                $deadline->notified_30 = true;
                $haTriggerato = true;
            } elseif ($giorniRimanenti <= 15 && $giorniRimanenti > 7 && !$deadline->notified_15) {
                $urgenza = 'breve';
                $deadline->notified_15 = true; 
                $deadline->notified_30 = true;
                $haTriggerato = true;
            } elseif ($giorniRimanenti <= 30 && $giorniRimanenti > 15 && !$deadline->notified_30) {
                $urgenza = 'avviso';
                $deadline->notified_30 = true;
                $haTriggerato = true;
            } else {
                // Avviso Annuale (Solo per Documenti a lungo termine)
                $documentiLungoTermine = ['patente', 'carta_identita', 'tesserino_sanitario'];
                if (in_array(strtolower($deadline->tipo), $documentiLungoTermine) && $scadeQuestAnno && !$deadline->notified_year) {
                    $urgenza = 'annuale';
                    $deadline->notified_year = true;
                    $haTriggerato = true;
                }
            }

            // 5. METTO LA SCADENZA NEI CESTINI CORRETTI
            if ($haTriggerato && $urgenza) {
                $daAggiornare[] = $deadline; 

                if ($isPersonal) {
                    if (isset($digestPerUtente[$ownerEmail])) {
                        $digestPerUtente[$ownerEmail][$urgenza][] = $itemData;
                    }
                } else {
                    foreach ($utentiValidi as $user) {
                        if (!empty($user->email) && isset($digestPerUtente[$user->email])) {
                            $digestPerUtente[$user->email][$urgenza][] = $itemData;
                        }
                    }
                }
            }
        }

        // =========================================
        // INVIO DI UNA SOLA MAIL PER UTENTE
        // =========================================
        $emailInviate = 0;
        foreach ($digestPerUtente as $email => $notifiche) {
            $totaleNotifiche = count($notifiche['urgente']) + count($notifiche['breve']) + count($notifiche['avviso']) + count($notifiche['annuale']);
            
            if ($totaleNotifiche > 0) {
                Mail::to($email)->send(new DeadlineDigestMail($notifiche));
                $emailInviate++;
            }
        }

        // Aggiorniamo i "notified_x" nel DB solo se la mail è partita davvero
        foreach ($daAggiornare as $deadline) {
            $deadline->save();
        }

        $this->info("Controllo completato! Inviate {$emailInviate} email riepilogative.");
    }
}