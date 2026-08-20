<?php

namespace App\Http\Controllers;

use App\Models\Deadline;
use App\Models\ActionProposal;
use App\Models\Vehicle;
use App\Models\User;
use App\Services\Deadlines\DeadlineRenewalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DomainException;

class ActionProposalController extends Controller
{
    public function index(Request $request)
    {
        $proposals = ActionProposal::query()
            ->visibleToUser()
            ->latest()
            ->get()
            ->map(function (ActionProposal $proposal) {
                $deadline = null;
                $vehicle = null;
                $person = null;

                if ($proposal->source_type === 'deadline' && $proposal->source_id) {
                    $deadline = Deadline::with([
                        'subscription',
                        'vehicle',
                        'user',
                    ])->find($proposal->source_id);
                }

                if ($proposal->action_type === 'archive_document' && $proposal->source_type === 'vehicle' && $proposal->source_id) {
                    $vehicle = Vehicle::find($proposal->source_id);
                }

                if ($proposal->action_type === 'archive_personal_document') {
                    $personId = data_get($proposal->payload, 'user_id');

                    if ($personId) {
                        $person = User::find($personId);
                    }
                }

                $tipo = $deadline?->tipo ?? 'scadenza';
                
                $tipoLabel = ucfirst(str_replace('_', ' ', $tipo));
                $titolo = match ($tipo) {
                    'spesa_abbonamento' => $deadline?->subscription?->nome
                        ?? 'Spesa / Abbonamento',

                    'revisione' => $deadline?->vehicle
                        ? 'Revisione ' . strtoupper(
                            $deadline->vehicle->marca . ' ' . $deadline->vehicle->modello
                        )
                        : 'Revisione',

                    'bollo' => $deadline?->vehicle
                        ? 'Bollo ' . strtoupper(
                            $deadline->vehicle->marca . ' ' . $deadline->vehicle->modello
                        )
                        : 'Bollo',

                    'assicurazione' => $deadline?->vehicle
                        ? 'Assicurazione ' . strtoupper(
                            $deadline->vehicle->marca . ' ' . $deadline->vehicle->modello
                        )
                        : 'Assicurazione',

                    default => $tipoLabel,
                };

                return [
                    $isDocumentProposal = in_array($proposal->action_type, [
                        'archive_document',
                        'archive_personal_document',
                    ], true),
                    $isPersonalDocumentProposal = $proposal->action_type === 'archive_personal_document',
                    $vehicleName = $vehicle
                        ? trim(($vehicle->marca ?? '') . ' ' . ($vehicle->modello ?? ''))
                        : data_get($proposal->payload, 'vehicle_label'),

                    $vehicleTitle = trim(
                        ($vehicleName ?: 'Veicolo')
                        . ' '
                        . (data_get($proposal->payload, 'vehicle_targa') ?: '')
                    ),
                    $personTitle = $person?->name
                        ?? data_get($proposal->payload, 'user_name')
                        ?? 'Persona associata',
                    'id' => $proposal->id,
                    'action_type' => $proposal->action_type,
                    'reason' => $proposal->reason
                        ?? 'Il sistema ha rilevato una possibile azione da verificare.',
                    'title' => $isDocumentProposal
                        ? (data_get($proposal->payload, 'document_label') . ' consigliato')
                        : (
                            $deadline
                                ? match ($proposal->action_type) {
                                    'upcoming_deadline' => $titolo . ' in arrivo',
                                    'high_value_upcoming' => $titolo . ' da pianificare',
                                    default => $titolo . ' da verificare',
                                }
                                : 'Proposta da verificare'
                        ),
                    'subtitle' => $isDocumentProposal
                        ? (
                            $isPersonalDocumentProposal
                                ? $personTitle
                                : $vehicleTitle
                        )
                        : null,

                    'is_document_proposal' => $isDocumentProposal,
                    'is_personal_document_proposal' => $isPersonalDocumentProposal,

                    'document_category' => data_get($proposal->payload, 'categoria'),

                    'document_user_id' => $isPersonalDocumentProposal
                        ? (
                            $person?->id
                            ?? data_get($proposal->payload, 'user_id')
                        )
                        : null,
                    'document_label' => data_get($proposal->payload, 'document_label'),
                    'vehicle_id' => !$isPersonalDocumentProposal
                    ? (
                        $vehicle?->id
                        ?? data_get($proposal->payload, 'vehicle_id')
                    )
                    : null,
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
                    'is_upcoming' => in_array($proposal->action_type, [
                        'upcoming_deadline',
                        'high_value_upcoming',
                    ], true),

                    'is_high_value' => $proposal->action_type === 'high_value_upcoming',
                    'days_remaining' => data_get($proposal->payload, 'days_remaining'),
                    'suggest_renewal' => (bool) data_get(
                        $proposal->payload,
                        'suggest_renewal',
                        false
                    ),
                    'title' => $deadline
                    ? match ($proposal->action_type) {
                        'upcoming_deadline' => $titolo . ' in arrivo',
                        'high_value_upcoming' => $titolo . ' da pianificare',
                        default => $titolo . ' da verificare',
                    }
                    : 'Proposta da verificare',
                ];
            })
            ->filter(fn (array $proposal) =>
                $proposal['deadline_id'] !== null
                || $proposal['is_document_proposal'] === true
            )
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
        if ($actionProposal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Questa proposta è già stata gestita.',
            ], 422);
        }

        if (
            !in_array($actionProposal->action_type, [
                'mark_paid',
                'upcoming_deadline',
                'high_value_upcoming',
            ], true) ||
            $actionProposal->source_type !== 'deadline' ||
            !$actionProposal->source_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Questa proposta non può essere applicata automaticamente.',
            ], 422);
        }

        $rinnova = $request->boolean('rinnova');

        try {
            return DB::transaction(function () use ($actionProposal, $rinnova) {
                $deadline = Deadline::lockForUpdate()
                    ->find($actionProposal->source_id);

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
                    $rinnova,
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

    public function snooze(Request $request, ActionProposal $actionProposal)
    {
        /*
        * Non si può rimandare una proposta già applicata, rifiutata o fallita.
        */
        if ($actionProposal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Questa proposta non è più disponibile.',
            ], 422);
        }

        /*
        * Per ora accettiamo soltanto 7 giorni.
        * In futuro potremo introdurre 1 giorno, fine settimana,
        * data personalizzata e così via.
        */
        $validated = $request->validate([
            'days' => 'nullable|integer|in:7',
        ]);

        $days = $validated['days'] ?? 7;

        $actionProposal->update([
            'snoozed_until' => now()->addDays($days),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Te lo ricorderò tra {$days} giorni.",
            'snoozed_until' => $actionProposal->fresh()
                ->snoozed_until
                ?->toIso8601String(),
        ]);
    }
}