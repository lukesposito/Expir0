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
        
        $scadenze = Deadline::with(['user', 'vehicle', 'subscription.user'])
                            ->where('pagato', false)
                            ->get();
                            
        $utentiValidi = User::where('is_active', true)
                            ->where('receives_notifications', true)
                            ->get();

        $digestPerUtente = []; 

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

            // 1. PREPARAZIONE TITOLO E SOTTOTITOLO EMAIL
            if ($isTesserino) {
                $nomeProprietario = $utenteAssociato ? $utenteAssociato->name : 'Membro Famiglia';
                $titolo = 'Tesserino Sanitario - ' . $nomeProprietario;
                $sottotitolo = 'Il nuovo documento verrà spedito automaticamente a casa dall\'Agenzia delle Entrate.';
            } else {
                $titolo = ucwords(str_replace('_', ' ', $deadline->tipo));
                $sottotitolo = 'Spesa di Famiglia';

                if ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription) {
                    // TITOLO: Nome del servizio (Netflix, Palestra, ecc.)
                    $titolo = $deadline->subscription->nome;
                    
                    // COSTRUZIONE SOTTOTITOLO DINAMICO
                    $parti = [];
                    
                    // Aggiungiamo le Note o la Categoria
                    if (!empty($deadline->subscription->note)) {
                        $parti[] = $deadline->subscription->note;
                    } else {
                        $parti[] = $deadline->subscription->categoria;
                    }

                    // AGGIUNGIAMO SEMPRE L'INTESTATARIO SE PRESENTE
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

            // 2. LOGICA TRIGGER
            $haTriggerato = false;
            $urgenza = null;

            if (!$isTesserino) {
                if ($giorniRimanenti <= 7 && $giorniRimanenti >= -30 && !$deadline->notified_7) {
                    $urgenza = 'urgente';
                    $deadline->notified_7 = true; $deadline->notified_15 = true; $deadline->notified_30 = true;
                    $haTriggerato = true;
                } elseif ($giorniRimanenti <= 15 && $giorniRimanenti > 7 && !$deadline->notified_15) {
                    $urgenza = 'breve';
                    $deadline->notified_15 = true; $deadline->notified_30 = true;
                    $haTriggerato = true;
                } elseif ($giorniRimanenti <= 30 && $giorniRimanenti > 15 && !$deadline->notified_30) {
                    $urgenza = 'avviso';
                    $deadline->notified_30 = true;
                    $haTriggerato = true;
                }
            }

            $documentiLungoTermine = ['patente', 'carta_identita', 'tesserino_sanitario'];
            if (in_array(strtolower($deadline->tipo), $documentiLungoTermine) && $scadeQuestAnno && !$deadline->notified_year) {
                $urgenza = 'annuale';
                $deadline->notified_year = true;
                $haTriggerato = true;
            }

            if ($haTriggerato && $urgenza) {
                $daAggiornare[] = $deadline; 
                if ($isPersonal) {
                    if (isset($digestPerUtente[$ownerEmail])) {
                        $digestPerUtente[$ownerEmail][$urgenza][] = $itemData;
                    }
                } else {
                    foreach ($utentiValidi as $user) {
                        if (isset($digestPerUtente[$user->email])) {
                            $digestPerUtente[$user->email][$urgenza][] = $itemData;
                        }
                    }
                }
            }
        }

        $emailInviate = 0;
        foreach ($digestPerUtente as $email => $notifiche) {
            $totale = count($notifiche['urgente']) + count($notifiche['breve']) + count($notifiche['avviso']) + count($notifiche['annuale']);
            if ($totale > 0) {
                Mail::to($email)->send(new DeadlineDigestMail($notifiche));
                $emailInviate++;
            }
        }

        foreach ($daAggiornare as $deadline) { 
            $deadline->save(); 
        }
        
        $this->info("Inviate {$emailInviate} email riepilogative aggiornate.");
    }
}