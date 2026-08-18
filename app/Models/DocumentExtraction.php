<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentExtraction extends Model
{
    protected $fillable = [
        'document_id',
        'status',
        'document_category',
        'extraction_engine',
        'confidence_score',
        'extracted_payload',
        'normalized_payload',
        'matched_entity_type',
        'matched_entity_id',
        'review_notes',
        'failure_reason',
        'processed_at',
        'reviewed_at',
        'applied_at',
    ];

    protected $casts = [
        'extracted_payload' => 'array',
        'normalized_payload' => 'array',
        'confidence_score' => 'decimal:2',
        'processed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}