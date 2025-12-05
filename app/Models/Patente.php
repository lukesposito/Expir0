<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patente extends Model
{
    protected $table = 'patentes';
    
    protected $fillable = [
        'user_id',
        'numero_patente',
        'tipo',
        'data_rilascio'
    ];

    protected $casts = [
        'data_rilascio' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class);
    }
}