<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Deadline;
use App\Services\Deadlines\DeadlineSyncService;

class CartaIdentita extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'numero', 'comune_rilascio', 'data_rilascio', 'data_scadenza', 'note', 'notification_override'];
    protected $casts = ['data_rilascio' => 'date', 'data_scadenza' => 'date', 'notification_override' => 'array'];

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class, 'carta_identita_id', 'id')
            ->where('tipo', 'carta_identita');
    }

    protected static function booted()
    {
        static::saved(function ($cartaIdentita) {
            app(DeadlineSyncService::class)->syncFromSource($cartaIdentita);
        });

        static::deleting(function ($cartaIdentita) {
            app(DeadlineSyncService::class)->deleteFromSource($cartaIdentita);
        });
    }
}