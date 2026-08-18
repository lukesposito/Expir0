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
        $deadlines = Deadline::query()
            ->where('pagato', false)
            ->whereDate('data_scadenza', '<', today())
            ->get();

        $created = 0;

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

        $this->info("Generate {$created} proposte operative.");

        return self::SUCCESS;
    }
}