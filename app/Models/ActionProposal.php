<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_id',
        'status',
        'action_type',
        'source_type',
        'source_id',
        'target_type',
        'target_id',
        'payload',
        'confidence',
        'reason',
        'resolved_at',
        'snoozed_until',
    ];

    protected $casts = [
        'payload' => 'array',
        'confidence' => 'decimal:2',
        'resolved_at' => 'datetime',
        'snoozed_until' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function markRejected(): void
    {
        $this->update([
            'status' => 'rejected',
            'resolved_at' => now(),
        ]);
    }

    public function markApplied(): void
    {
        $this->update([
            'status' => 'applied',
            'resolved_at' => now(),
        ]);
    }

    public function scopeVisibleToUser(Builder $query): Builder
    {
        return $query
            ->pending()
            ->where(function (Builder $query) {
                $query->whereNull('snoozed_until')
                    ->orWhere('snoozed_until', '<=', now());
            })
            ->where(function (Builder $query) {
                $query
                    ->whereIn('action_type', [
                        'archive_document',
                        'archive_personal_document',
                    ])
                    ->orWhere(function (Builder $query) {
                        $query
                            ->where('source_type', 'deadline')
                            ->whereNotNull('source_id')
                            ->whereExists(function ($exists) {
                                $exists
                                    ->selectRaw('1')
                                    ->from('deadlines')
                                    ->whereColumn(
                                        'deadlines.id',
                                        'action_proposals.source_id'
                                    );
                            });
                    });
            });
    }
}
