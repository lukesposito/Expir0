<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = ['targa', 'marca', 'modello'];

    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class);
    }

    public function revisioni()
    {
        return $this->hasMany(Revisione::class);
    }
}