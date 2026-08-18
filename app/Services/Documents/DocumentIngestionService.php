<?php

namespace App\Services\Documents;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentIngestionService
{
    public function __construct(
        protected DocumentExtractionService $extractionService,
    ) {
    }

    public function ingest(array $data, UploadedFile $file): Document
    {
        $document = DB::transaction(function () use ($data, $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');

            $document = Document::create([
                'user_id' => Auth::id(),
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'associated_user_id' => $data['user_id'] ?? null,
                'categoria' => $data['categoria'],
                'path' => $path,
                'nome_originale' => $file->getClientOriginalName(),
                'tipo_file' => $file->getClientMimeType(),
            ]);

            $document->extractions()->create([
                'status' => 'pending',
                'document_category' => $data['categoria'],
                'extraction_engine' => 'manual_bootstrap',
                'confidence_score' => null,
                'extracted_payload' => null,
                'normalized_payload' => null,
            ]);

            return $document;
        });

        $this->extractionService->extract($document);

        return $document->fresh(['extractions']);

    }
}