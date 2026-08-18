<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeadlineEvent extends Model
{
    protected $fillable = [
        'deadline_id',
        'actor_user_id',
        'event_type',
        'source_type',
        'source_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function deadline(): BelongsTo
    {
        return $this->belongsTo(Deadline::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}