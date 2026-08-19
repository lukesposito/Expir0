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
        'monthly_suggestion',
        'status',
    ];

    protected $casts = [
        'target_month' => 'integer',
        'target_year' => 'integer',
        'target_amount' => 'decimal:2',
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
}
