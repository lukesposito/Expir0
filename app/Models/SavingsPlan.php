<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_month',
        'target_year',
        'target_amount',
        'saved_amount',
        'monthly_suggestion',
        'status',
    ];

    protected $casts = [
        'target_month' => 'integer',
        'target_year' => 'integer',
        'target_amount' => 'decimal:2',
        'saved_amount' => 'decimal:2',
        'monthly_suggestion' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function remainingAmount(): float
    {
        return max(
            0,
            (float) $this->target_amount - (float) $this->saved_amount
        );
    }

    public function progressPercentage(): float
    {
        if ((float) $this->target_amount <= 0) {
            return 0;
        }

        return min(
            100,
            round(
                ((float) $this->saved_amount / (float) $this->target_amount) * 100,
                2
            )
        );
    }
}
