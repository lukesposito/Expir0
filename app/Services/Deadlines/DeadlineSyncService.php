<?php

namespace App\Services\Deadlines;

use App\Models\Deadline;
use Illuminate\Database\Eloquent\Model;

class DeadlineSyncService
{
    public function __construct(
        protected DeadlineSourceMapper $mapper,
        protected DeadlineEventLogger $eventLogger
    ) {}

    public function syncFromSource(Model $source): Deadline
    {
        $data = $this->mapper->map($source);

        if ($data['tipo'] === 'patente' && !empty($data['user_id'])) {
            $attributes = [
                'user_id' => $data['user_id'],
                'tipo' => 'patente',
            ];
        } elseif ($data['tipo'] === 'bollo') {
            $attributes = [
                'bollo_id' => $source->getKey(),
                'tipo' => 'bollo',
            ];
        } elseif ($data['tipo'] === 'revisione') {
            $attributes = [
                'revisione_id' => $source->getKey(),
                'tipo' => 'revisione',
            ];
        } elseif ($data['tipo'] === 'assicurazione') {
            $attributes = [
                'assicurazione_id' => $source->getKey(),
                'tipo' => 'assicurazione',
            ];
        } elseif ($data['tipo'] === 'carta_identita') {
            $attributes = [
                'carta_identita_id' => $source->getKey(),
                'tipo' => 'carta_identita',
            ];
        } elseif ($data['tipo'] === 'tesserino_sanitario') {
            $attributes = [
                'tesserino_sanitario_id' => $source->getKey(),
                'tipo' => 'tesserino_sanitario',
            ];
        } else {
            $attributes = [
                'source_type' => $data['source_type'],
                'source_id' => $data['source_id'],
            ];
        }

        $values = array_merge([
            'vehicle_id' => $data['vehicle_id'],
            'user_id' => $data['user_id'],
            'tipo' => $data['tipo'],
            'data_scadenza' => $data['data_scadenza'],
            'importo' => $data['importo'],
            'note' => $data['note'],
            'pagato' => false,
            'source_type' => $data['source_type'],
            'source_id' => $data['source_id'],
            'source_key' => $data['source_type'] . ':' . $data['source_id'],
            'generated_by' => 'sync_service',
        ], $data['legacy_link']);

        $deadline = Deadline::updateOrCreate($attributes, $values);

        $this->eventLogger->log($deadline, 'synced', [
            'source_model' => get_class($source),
            'source_primary_key' => $source->getKey(),
        ]);

        return $deadline;
    }

    public function deleteFromSource(Model $source): void
    {
        $data = $this->mapper->map($source);

        $query = Deadline::query()
            ->where('source_type', $data['source_type'])
            ->where('source_id', $data['source_id']);

        if (($data['source_type'] ?? null) === 'bollo') {
            $query->orWhere('bollo_id', $source->getKey());
        } elseif (($data['source_type'] ?? null) === 'assicurazione') {
            $query->orWhere('assicurazione_id', $source->getKey());
        } elseif (($data['source_type'] ?? null) === 'revisione') {
            $query->orWhere('revisione_id', $source->getKey());
        } elseif (($data['source_type'] ?? null) === 'carta_identita') {
            $query->orWhere('carta_identita_id', $source->getKey());
        } elseif (($data['source_type'] ?? null) === 'tesserino_sanitario') {
            $query->orWhere('tesserino_sanitario_id', $source->getKey());
        } elseif (($data['source_type'] ?? null) === 'patente') {
            $query->orWhere('patente_id', $source->getKey());
        }

        $deadlines = $query->get();

        foreach ($deadlines as $deadline) {
            $this->eventLogger->log($deadline, 'deleted_from_source', [
                'source_model' => get_class($source),
                'source_primary_key' => $source->getKey(),
            ]);

            $deadline->delete();
        }
    }
}