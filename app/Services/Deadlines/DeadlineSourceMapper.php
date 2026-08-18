<?php

namespace App\Services\Deadlines;

use App\Models\Assicurazione;
use App\Models\Bollo;
use App\Models\Patente;
use App\Models\Revisione;
use App\Models\Subscription;
use App\Models\CartaIdentita;
use App\Models\TesserinoSanitario;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use App\Services\Patenti\PatenteExpiryCalculator;

class DeadlineSourceMapper
{
    public function map(object $source): array
    {
        return match (true) {
            $source instanceof Bollo => [
                'tipo' => 'bollo',
                'vehicle_id' => $source->vehicle_id,
                'user_id' => null,
                'data_scadenza' => $source->data_scadenza,
                'importo' => $source->importo,
                'note' => $source->note ?? 'Scadenza Bollo Auto',
                'source_type' => 'bollo',
                'source_id' => $source->id,
                'legacy_link' => [
                    'bollo_id' => $source->id,
                ],
            ],

            $source instanceof Assicurazione => [
                'tipo' => 'assicurazione',
                'vehicle_id' => $source->vehicle_id,
                'user_id' => null,
                'data_scadenza' => $source->data_scadenza,
                'importo' => $source->importo,
                'note' => $source->note ?? 'Scadenza Assicurazione',
                'source_type' => 'assicurazione',
                'source_id' => $source->id,
                'legacy_link' => [
                    'assicurazione_id' => $source->id,
                ],
            ],

            $source instanceof Revisione => [
                'tipo' => 'revisione',
                'vehicle_id' => $source->vehicle_id,
                'user_id' => null,
                'data_scadenza' => $source->data_prossima_revisione,
                'importo' => $source->importo,
                'note' => 'Revisione presso: ' . ($source->centro_revisione ?? 'N/D'),
                'source_type' => 'revisione',
                'source_id' => $source->id,
                'legacy_link' => [
                    'revisione_id' => $source->id,
                ],
            ],

            $source instanceof CartaIdentita => [
                'tipo' => 'carta_identita',
                'vehicle_id' => null,
                'user_id' => $source->user_id,
                'data_scadenza' => $source->data_scadenza,
                'importo' => 0,
                'note' => $source->note ?? 'Scadenza Carta d\'Identità n. ' . strtoupper($source->numero),
                'source_type' => 'carta_identita',
                'source_id' => $source->id,
                'legacy_link' => [
                    'carta_identita_id' => $source->id,
                ],
            ],

            $source instanceof TesserinoSanitario => [
                'tipo' => 'tesserino_sanitario',
                'vehicle_id' => null,
                'user_id' => $source->user_id,
                'data_scadenza' => $source->data_scadenza,
                'importo' => 0,
                'note' => $source->note ?? 'Scadenza Tesserino Sanitario CF: ' . strtoupper($source->codice_fiscale),
                'source_type' => 'tesserino_sanitario',
                'source_id' => $source->id,
                'legacy_link' => [
                    'tesserino_sanitario_id' => $source->id,
                ],
            ],

            $source instanceof Subscription => [
                'tipo' => 'spesa_abbonamento',
                'vehicle_id' => $source->vehicle_id,
                'user_id' => $source->user_id,
                'data_scadenza' => $source->data_scadenza,
                'importo' => $source->importo,
                'note' => $this->buildSubscriptionNote($source),
                'source_type' => 'subscription',
                'source_id' => $source->id,
                'legacy_link' => [
                    'subscription_id' => $source->id,
                ],
            ],

            $source instanceof Patente => [
                'tipo' => 'patente',
                'vehicle_id' => null,
                'user_id' => $source->user_id,
                'patente_id' => $source->id,
                'data_scadenza' => $this->resolvePatenteScadenza($source),
                'importo' => $source->importo,
                'note' => $this->buildPatenteNote($source),
                'source_type' => 'patente',
                'source_id' => $source->id,
                'legacy_link' => [
                    'patente_id' => $source->id,
                ],
            ],

            default => throw new InvalidArgumentException(
                'Unsupported source model: ' . get_class($source)
            ),
        };
    }

    protected function buildSubscriptionNote(Subscription $source): string
    {
        return $source->nome
            . ' (' . $source->categoria . ')'
            . ($source->note ? ' - ' . $source->note : '');
    }

    protected function buildPatenteNote(Patente $source): string
    {
        $categoria = $source->tipo ? 'cat. ' . strtoupper($source->tipo) : 'senza categoria';

        $esito = app(PatenteExpiryCalculator::class)->calculate(
            $source->user_id,
            $source->data_rilascio,
            $source->tipo
        );

        if (($esito['status'] ?? null) === 'manuale_richiesto') {
            return 'Rinnovo Patente ' . $categoria . '. Gestione manuale richiesta: ' . $esito['messaggio'];
        }

        return 'Rinnovo Patente ' . $categoria . '.';
    }

    protected function resolvePatenteScadenza(Patente $source): string
    {
        if (!empty($source->data_scadenza)) {
            return Carbon::parse($source->data_scadenza)->format('Y-m-d');
        }

        $esito = app(PatenteExpiryCalculator::class)->calculate(
            $source->user_id,
            $source->data_rilascio,
            $source->tipo
        );

        if (($esito['status'] ?? null) !== 'ok_auto' || empty($esito['data_scadenza'])) {
            throw new \DomainException(
                $esito['messaggio'] ?? 'Scadenza patente non calcolabile automaticamente.'
            );
        }

        return $esito['data_scadenza'];
    }
}