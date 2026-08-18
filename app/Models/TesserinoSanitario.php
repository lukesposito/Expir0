<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Deadline;
use App\Services\Deadlines\DeadlineSyncService;

class TesserinoSanitario extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'codice_fiscale', 'data_scadenza', 'note', 'notification_override',];
    protected $casts = ['data_scadenza' => 'date', 'notification_override' => 'array'];

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class, 'tesserino_sanitario_id', 'id')
            ->where('tipo', 'tesserino_sanitario');
    }

    protected static function booted()
    {
        static::saved(function ($tesserinoSanitario) {
            app(DeadlineSyncService::class)->syncFromSource($tesserinoSanitario);
        });

        static::deleting(function ($tesserinoSanitario) {
            app(DeadlineSyncService::class)->deleteFromSource($tesserinoSanitario);
        });
    }
}