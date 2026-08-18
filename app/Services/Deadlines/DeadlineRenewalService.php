<?php

namespace App\Services\Deadlines;

use App\Models\Assicurazione;
use App\Models\Bollo;
use App\Models\CartaIdentita;
use App\Models\Deadline;
use App\Models\Patente;
use App\Models\Revisione;
use App\Models\Subscription;
use App\Models\TesserinoSanitario;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use App\Services\Patenti\PatenteExpiryCalculator;

class DeadlineRenewalService
{
    public function __construct(
        protected DeadlineEventLogger $eventLogger
    ) {}

    public function toggle(Deadline $deadline, bool $rinnova = false, ?int $actorUserId = null): array
    {
        $this->guardManualPaymentNotAllowed($deadline);

        return DB::transaction(function () use ($deadline, $rinnova, $actorUserId) {
            $wasPagato = (bool) $deadline->pagato;

            if ($wasPagato) {
                $this->rollbackRenewalIfNeeded($deadline, $actorUserId);

                $deadline->pagato = false;
                $deadline->data_pagamento = null;
                $deadline->save();

                $this->eventLogger->log(
                    $deadline,
                    'unpaid',
                    ['rinnova' => false],
                    $actorUserId
                );

                return [
                    'success' => true,
                    'pagato' => false,
                    'message' => 'Pagamento annullato, scadenza ripristinata!',
                    'status_code' => 200,
                ];
            }

            $deadline->pagato = true;
            $deadline->data_pagamento = now();
            $deadline->save();

            $this->eventLogger->log(
                $deadline,
                'paid',
                ['rinnova' => $rinnova],
                $actorUserId
            );

            $messaggio = 'Stato aggiornato e archiviato nello storico!';

            if ($rinnova) {
                $nuovaScadenza = $this->createRenewedDeadline($deadline, $actorUserId);
                $messaggio = 'Archiviato! Nuovo rinnovo generato per il ' . $nuovaScadenza->data_scadenza->format('d/m/Y') . '!';
            }

            return [
                'success' => true,
                'pagato' => true,
                'message' => $messaggio,
                'status_code' => 200,
            ];
        });
    }

    protected function rollbackRenewalIfNeeded(Deadline $deadline, ?int $actorUserId = null): void
    {
        $generated = Deadline::query()
            ->where('generated_from_deadline_id', $deadline->id)
            ->latest('id')
            ->first();

        if (!$generated) {
            return;
        }

        if ($deadline->tipo === 'patente') {
            $patente = $this->resolvePatenteFromDeadline($deadline);

            if ($patente) {
                DB::table('patentes')
                    ->where('id', $patente->id)
                    ->update([
                        'data_scadenza' => Carbon::parse($deadline->data_scadenza)->format('Y-m-d'),
                        'updated_at' => now(),
                    ]);
            }
        }

        $generated->delete();

        $this->eventLogger->log($deadline, 'renewal_rollback', [
            'deleted_generated_deadline_id' => $generated->id,
            'restored_date' => Carbon::parse($deadline->data_scadenza)->format('Y-m-d'),
            'tipo' => $deadline->tipo,
        ], $actorUserId);
    }

    protected function resolvePatenteFromDeadline(Deadline $deadline): ?Patente
    {
        if ($deadline->patente_id) {
            return Patente::find($deadline->patente_id);
        }

        if ($deadline->user_id) {
            return Patente::where('user_id', $deadline->user_id)
                ->where(function ($query) use ($deadline) {
                    $query->whereDate('data_scadenza', $deadline->data_scadenza)
                        ->orWhereDate('data_rilascio', $deadline->data_scadenza);
                })
                ->orderByDesc('id')
                ->first();
        }

        return null;
    }

    protected function guardManualPaymentNotAllowed(Deadline $deadline): void
    {
        if (
            $deadline->tipo === 'spesa_abbonamento' &&
            $deadline->subscription &&
            $deadline->subscription->rinnovo_automatico
        ) {
            throw new DomainException(
                'Questo abbonamento si rinnova automaticamente e non può essere marcato manualmente come pagato.'
            );
        }
    }

    protected function createRenewedDeadline(Deadline $deadline, ?int $actorUserId = null): Deadline
    {
        $vecchiaData = Carbon::parse($deadline->data_scadenza);
        $nuovaScadenza = $deadline->replicate();
        $nuovaScadenza->generated_from_deadline_id = $deadline->id;
        $nuovaScadenza->renewal_parent_deadline_id = $deadline->id;

        $nuovaScadenza->pagato = false;
        $nuovaScadenza->data_pagamento = null;
        $nuovaScadenza->data_scadenza = $this->calculateNextDate($deadline, $vecchiaData);
        $nuovaScadenza->generated_by = 'renewal_service';

        $this->detachLegacyLinks($deadline, $nuovaScadenza);
        $this->renewParentSource($deadline, $nuovaScadenza, $vecchiaData);

        $nuovaScadenza->save();

        $this->eventLogger->log($nuovaScadenza, 'renewed', [
            'previous_deadline_id' => $deadline->id,
            'previous_date' => $vecchiaData->format('Y-m-d'),
            'new_date' => $nuovaScadenza->data_scadenza->format('Y-m-d'),
            'tipo' => $deadline->tipo,
        ], $actorUserId);

        return $nuovaScadenza;
    }

    protected function calculateNextDate(Deadline $deadline, Carbon $vecchiaData): Carbon
    {
        return match ($deadline->tipo) {
            'revisione' => $vecchiaData->copy()->addYears(2),
            'carta_identita' => $this->calcolaScadenzaCartaIdentita($deadline->user_id, $vecchiaData),
            'tesserino_sanitario' => $vecchiaData->copy()->addYears(6),
            'patente' => $this->calculatePatenteNextDate($deadline, $vecchiaData),
            'spesa_abbonamento' => $this->calculateSubscriptionNextDate($deadline, $vecchiaData),
            default => $vecchiaData->copy()->addYear(),
        };
    }

    protected function calculatePatenteNextDate(Deadline $deadline, Carbon $vecchiaData): Carbon
    {
        $patenteInfo = $this->resolvePatenteFromDeadline($deadline);
        $tipoPatente = $patenteInfo ? $patenteInfo->tipo : 'B';

        $esito = app(PatenteExpiryCalculator::class)->calculate(
            $deadline->user_id,
            $vecchiaData,
            $tipoPatente
        );

        if (($esito['status'] ?? null) !== 'ok_auto' || empty($esito['data_scadenza'])) {
            throw new \DomainException(
                $esito['messaggio'] ?? 'Per questa patente il rinnovo va gestito manualmente.'
            );
        }

        return Carbon::parse($esito['data_scadenza']);
    }

    protected function calculateSubscriptionNextDate(Deadline $deadline, Carbon $vecchiaData): Carbon
    {
        $subscription = $deadline->subscription_id
            ? Subscription::find($deadline->subscription_id)
            : null;

        $mesiRinnovo = max(1, (int) ($subscription->renewal_interval_months ?? 1));

        return $vecchiaData->copy()->addMonthsNoOverflow($mesiRinnovo);
    }

    protected function detachLegacyLinks(Deadline $originale, Deadline $nuovaScadenza): void
    {
        if ($originale->tipo === 'patente') $nuovaScadenza->patente_id = null;
        if ($originale->tipo === 'revisione') $nuovaScadenza->revisione_id = null;
        if ($originale->tipo === 'bollo') $nuovaScadenza->bollo_id = null;
        if ($originale->tipo === 'assicurazione') $nuovaScadenza->assicurazione_id = null;
        if ($originale->tipo === 'carta_identita') $nuovaScadenza->carta_identita_id = null;
        if ($originale->tipo === 'tesserino_sanitario') $nuovaScadenza->tesserino_sanitario_id = null;
        if ($originale->tipo === 'spesa_abbonamento') $nuovaScadenza->subscription_id = null;
    }

    protected function renewParentSource(Deadline $deadline, Deadline $nuovaScadenza, Carbon $vecchiaData): void
    {
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
            return;
        }

        if ($deadline->tipo === 'bollo' && $deadline->bollo_id) {
            $vecchio = Bollo::find($deadline->bollo_id);
            if ($vecchio) {
                $nuovo = $vecchio->replicate();
                $nuovo->data_scadenza = $nuovaScadenza->data_scadenza;
                $nuovo->saveQuietly();
                $nuovaScadenza->bollo_id = $nuovo->id;
            }
            return;
        }

        if ($deadline->tipo === 'assicurazione' && $deadline->assicurazione_id) {
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
            return;
        }

        if ($deadline->tipo === 'carta_identita' && $deadline->carta_identita_id) {
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
            return;
        }

        if ($deadline->tipo === 'tesserino_sanitario' && $deadline->tesserino_sanitario_id) {
            $vecchio = TesserinoSanitario::find($deadline->tesserino_sanitario_id);
            if ($vecchio) {
                $nuovo = $vecchio->replicate();
                $nuovo->data_scadenza = $nuovaScadenza->data_scadenza;
                $nuovo->saveQuietly();
                $nuovaScadenza->tesserino_sanitario_id = $nuovo->id;
            }
            return;
        }

        if ($deadline->tipo === 'patente') {
            $patente = $this->resolvePatenteFromDeadline($deadline);

            if ($patente) {
                DB::table('patentes')
                    ->where('id', $patente->id)
                    ->update([
                        'data_rilascio' => $vecchiaData->format('Y-m-d'),
                        'data_scadenza' => $nuovaScadenza->data_scadenza->format('Y-m-d'),
                        'updated_at' => Carbon::now(),
                    ]);

                $nuovaScadenza->patente_id = $patente->id;
            }

            return;
        }

        if ($deadline->tipo === 'spesa_abbonamento' && $deadline->subscription_id) {
            $subscription = Subscription::find($deadline->subscription_id);

            if ($subscription) {
                $mesiRinnovo = max(1, (int) ($subscription->renewal_interval_months ?? 1));

                $nuovaScadenza->data_scadenza = $vecchiaData->copy()->addMonthsNoOverflow($mesiRinnovo);
                $subscription->data_scadenza = $nuovaScadenza->data_scadenza;
                $subscription->saveQuietly();
                $nuovaScadenza->subscription_id = $subscription->id;
            }
        }
    }

    protected function calcolaScadenzaCartaIdentita(?int $userId, Carbon $vecchiaData): Carbon
    {
        $user = $userId ? User::find($userId) : null;

        if (!$user || !$user->data_nascita) {
            return $vecchiaData->copy()->addYears(10);
        }

        $eta = Carbon::parse($user->data_nascita)->diffInYears($vecchiaData);

        if ($eta < 3) {
            return $vecchiaData->copy()->addYears(3);
        }

        if ($eta < 18) {
            return $vecchiaData->copy()->addYears(5);
        }

        return $vecchiaData->copy()->addYears(10);
    }

    /* protected function calcolaScadenzaPatente(?int $userId, Carbon|string $dataRinnovoStr, ?string $tipo = null): array
    {
        $user = $userId ? User::find($userId) : null;
        $dataRinnovo = Carbon::parse($dataRinnovoStr);

        $categorie = explode(',', strtoupper(str_replace(' ', '', $tipo ?? '')));
        $haPatentePesante = in_array('C', $categorie, true) || in_array('CE', $categorie, true);

        if ($user && $user->data_nascita) {
            $dataNascita = Carbon::parse($user->data_nascita);
            $eta = $dataNascita->diffInYears($dataRinnovo);

            if ($haPatentePesante) {
                $anni = 3;
            } else {
                if ($eta < 50) {
                    $anni = 10;
                } elseif ($eta < 70) {
                    $anni = 5;
                } elseif ($eta < 80) {
                    $anni = 3;
                } else {
                    $anni = 2;
                }
            }

            $scadenza = Carbon::create(
                $dataRinnovo->year + $anni,
                $dataNascita->month,
                $dataNascita->day
            );

            return [
                'status' => 'ok_auto',
                'data_scadenza' => $scadenza->format('Y-m-d'),
                'messaggio' => null,
            ];
        }

        return [
            'status' => 'ok_auto',
            'data_scadenza' => $dataRinnovo
                ->copy()
                ->addYears($haPatentePesante ? 3 : 10)
                ->format('Y-m-d'),
            'messaggio' => null,
        ];
    } */
}