<?php

namespace App\Http\Controllers;

use App\Models\Deadline;
use App\Services\Deadlines\DeadlineRenewalService;
use Illuminate\Support\Facades\DB;
use App\Models\ActionProposal;
use Illuminate\Http\Request;
use DomainException;

class ActionProposalController extends Controller
{
    public function index(Request $request)
    {
        $proposals = ActionProposal::query()
            ->pending()
            ->latest()
            ->get()
            ->map(function (ActionProposal $proposal) {
                $deadline = null;

                if (
                    $proposal->source_type === 'deadline' &&
                    $proposal->source_id
                ) {
                    $deadline = \App\Models\Deadline::find($proposal->source_id);
                }

                $tipo = $deadline?->tipo ?? 'scadenza';
                $tipoLabel = ucfirst(str_replace('_', ' ', $tipo));

                return [
                    'id' => $proposal->id,
                    'action_type' => $proposal->action_type,
                    'reason' => $proposal->reason
                        ?? 'Il sistema ha rilevato una possibile azione da verificare.',
                    'title' => $deadline
                        ? $tipoLabel . ' da verificare'
                        : 'Proposta da verificare',
                    'deadline_id' => $deadline?->id,
                    'deadline_date' => $deadline?->data_scadenza
                        ? $deadline->data_scadenza->format('d/m/Y')
                        : 'N/D',
                    'amount_value' => (float) ($deadline?->importo ?? 0),
                    'amount' => '€' . number_format(
                        $deadline?->importo ?? 0,
                        2,
                        ',',
                        '.'
                    ),
                    'is_expired' => $deadline
                        ? $deadline->data_scadenza->isPast()
                        : false,
                    'suggest_renewal' => (bool) data_get(
                        $proposal->payload,
                        'suggest_renewal',
                        false
                    ),
                ];
            })
            ->filter(fn (array $proposal) => $proposal['deadline_id'] !== null)
            ->values();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'proposals' => $proposals,
            ]);
        }

        return view('action-proposals.index', [
            'proposals' => $proposals,
        ]);
    }

    public function reject(ActionProposal $actionProposal)
    {
        $actionProposal->markRejected();

        return response()->json([
            'success' => true,
            'message' => 'Proposta ignorata.',
        ]);
    }

    public function confirm(Request $request, ActionProposal $actionProposal)
    {
        $validated = $request->validate([
            'rinnova' => ['required', 'boolean'],
        ]);

        if ($actionProposal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Questa proposta è già stata gestita.',
            ], 422);
        }

        if (
            $actionProposal->action_type !== 'mark_paid' ||
            $actionProposal->source_type !== 'deadline' ||
            !$actionProposal->source_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Questa proposta non può essere applicata automaticamente.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($actionProposal, $validated) {
                $deadline = Deadline::lockForUpdate()->find($actionProposal->source_id);

                if (!$deadline) {
                    $actionProposal->update([
                        'status' => 'failed',
                        'resolved_at' => now(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'La scadenza collegata non esiste più.',
                    ], 404);
                }

                if ($deadline->pagato) {
                    $actionProposal->update([
                        'status' => 'applied',
                        'resolved_at' => now(),
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'La scadenza risultava già pagata.',
                    ]);
                }

                $result = app(DeadlineRenewalService::class)->toggle(
                    $deadline,
                    (bool) $validated['rinnova'],
                    auth()->id()
                );

                if (!($result['success'] ?? false)) {
                    throw new DomainException(
                        $result['message'] ?? 'Impossibile applicare la proposta.'
                    );
                }

                $actionProposal->update([
                    'status' => 'applied',
                    'resolved_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'Operazione completata.',
                    'proposal_id' => $actionProposal->id,
                ]);
            });
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}