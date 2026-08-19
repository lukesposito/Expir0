<?php

namespace App\Console\Commands;

use App\Models\ActionProposal;
use App\Models\Deadline;
use Illuminate\Console\Command;

class GenerateDeadlineActionProposals extends Command
{
    protected $signature = 'app:generate-deadline-action-proposals';

    protected $description = 'Genera proposte operative per scadenze non pagate e scadute';

    public function handle(): int
    {        
        /*
        |--------------------------------------------------------------------------
        | 1. Scadenze già superate e non pagate
        |--------------------------------------------------------------------------
        |
        | Proposta operativa: l'utente può confermare il pagamento,
        | con o senza creazione del rinnovo successivo.
        |
        */

        $created = 0;

        $deadlines = Deadline::query()
            ->where('pagato', false)
            ->whereDate('data_scadenza', '<', today())
            ->get();

        foreach ($deadlines as $deadline) {
            $alreadyExists = ActionProposal::query()
                ->where('status', 'pending')
                ->where('action_type', 'mark_paid')
                ->where('source_type', 'deadline')
                ->where('source_id', $deadline->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            ActionProposal::create([
                'user_id' => $deadline->user_id,
                'status' => 'pending',
                'action_type' => 'mark_paid',
                'source_type' => 'deadline',
                'source_id' => $deadline->id,
                'target_type' => 'deadline',
                'target_id' => $deadline->id,
                'payload' => [
                    'deadline_id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza?->format('Y-m-d'),
                    'importo' => $deadline->importo,
                    'suggest_renewal' => true,
                ],
                'confidence' => 100,
                'reason' => 'La scadenza risulta superata e non ancora segnata come pagata.',
            ]);

            $created++;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Scadenze in arrivo nei prossimi 7 giorni
        |--------------------------------------------------------------------------
        |
        | Proposta preventiva: non modifica nulla e porta solo l'utente
        | alla gestione della scadenza prima della data limite.
        |
        */

        $imminenti = Deadline::query()
            ->where('pagato', false)
            ->whereDate('data_scadenza', '>=', today())
            ->whereDate('data_scadenza', '<=', today()->copy()->addDays(7))
            ->get();

        foreach ($imminenti as $deadline) {
            $alreadyExists = ActionProposal::query()
                ->where('status', 'pending')
                ->where('action_type', 'upcoming_deadline')
                ->where('source_type', 'deadline')
                ->where('source_id', $deadline->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $giorniRimanenti = today()->diffInDays(
                $deadline->data_scadenza->copy()->startOfDay(),
                false
            );

            $messaggio = $giorniRimanenti === 0
                ? 'La scadenza è prevista per oggi.'
                : 'La scadenza è prevista tra ' . $giorniRimanenti . ' giorni.';

            ActionProposal::create([
                'user_id' => $deadline->user_id,
                'status' => 'pending',
                'action_type' => 'upcoming_deadline',
                'source_type' => 'deadline',
                'source_id' => $deadline->id,
                'target_type' => 'deadline',
                'target_id' => $deadline->id,
                'payload' => [
                    'deadline_id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza?->format('Y-m-d'),
                    'importo' => $deadline->importo,
                    'days_remaining' => $giorniRimanenti,
                    'suggest_renewal' => false,
                ],
                'confidence' => 100,
                'reason' => $messaggio,
            ]);

            $created++;
        }

        $this->info("Generate {$created} proposte operative.");

        /*
        |--------------------------------------------------------------------------
        | 3. Spese importanti in arrivo entro 30 giorni
        |--------------------------------------------------------------------------
        |
        | Proposta preventiva per le scadenze non pagate con un importo
        | significativo. Non modifica la deadline: invita l'utente a pianificare
        | o gestire in anticipo una spesa rilevante.
        |
        */

        $sogliaImporto = 300;

        $speseImportanti = Deadline::query()
            ->where('pagato', false)
            ->whereNotNull('importo')
            ->where('importo', '>=', $sogliaImporto)
            ->whereDate('data_scadenza', '>=', today()->copy()->addDays(8))
            ->whereDate('data_scadenza', '<=', today()->copy()->addDays(30))
            ->get();

        foreach ($speseImportanti as $deadline) {
            $alreadyExists = ActionProposal::query()
                ->where('status', 'pending')
                ->where('action_type', 'high_value_upcoming')
                ->where('source_type', 'deadline')
                ->where('source_id', $deadline->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $giorniRimanenti = today()->diffInDays(
                $deadline->data_scadenza->copy()->startOfDay(),
                false
            );

            ActionProposal::create([
                'user_id' => $deadline->user_id,
                'status' => 'pending',
                'action_type' => 'high_value_upcoming',
                'source_type' => 'deadline',
                'source_id' => $deadline->id,
                'target_type' => 'deadline',
                'target_id' => $deadline->id,
                'payload' => [
                    'deadline_id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza?->format('Y-m-d'),
                    'importo' => $deadline->importo,
                    'days_remaining' => $giorniRimanenti,
                    'threshold_amount' => $sogliaImporto,
                    'suggest_renewal' => false,
                ],
                'confidence' => 100,
                'reason' => 'Spesa importante prevista tra '
                    . $giorniRimanenti
                    . ' giorni: €'
                    . number_format($deadline->importo, 2, ',', '.')
                    . '.',
            ]);

            $created++;
        }

        return self::SUCCESS;
    }
}