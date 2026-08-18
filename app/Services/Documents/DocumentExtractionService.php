<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentExtraction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentExtractionService
{
    public function extract(Document $document): DocumentExtraction
    {
        $extraction = $document->latestExtraction;

        if (! $extraction) {
            $extraction = $document->extractions()->create([
                'status' => 'pending',
                'document_category' => $document->categoria,
                'extraction_engine' => 'manual_bootstrap',
            ]);
        }

        $extraction->update([
            'status' => 'processing',
            'processed_at' => now(),
        ]);

        $payload = [
            'document_id' => $document->id,
            'categoria' => $document->categoria,
            'nome_originale' => $document->nomeoriginale ?? $document->nome_originale ?? null,
            'mime_type' => $document->tipofile ?? $document->tipo_file ?? null,
            'path' => $document->path,
            'public_url' => Storage::url($document->path),
            'vehicle_id' => $document->vehicleid ?? $document->vehicle_id ?? null,
            'associated_user_id' => $document->associateduserid ?? $document->associated_user_id ?? null,
            'detected_at' => Carbon::now()->toIso8601String(),
        ];

        $normalized = [
            'source_category' => $document->categoria,
            'needs_human_review' => true,
            'candidate_matches' => [
                'vehicle_id' => $payload['vehicle_id'],
                'user_id' => $payload['associated_user_id'],
            ],
            'suggested_action' => 'review_and_extract',
        ];

        $extraction->update([
            'status' => 'needs_review',
            'document_category' => $document->categoria,
            'extraction_engine' => 'basic_metadata_v1',
            'confidence_score' => 25.00,
            'extracted_payload' => $payload,
            'normalized_payload' => $normalized,
            'failure_reason' => null,
        ]);

        return $extraction->fresh();
    }
}