<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\Deadlines\DeadlineSyncService;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'vehicle_id', 'nome', 'categoria', 
        'data_scadenza', 'importo', 'rinnovo_automatico', 'notification_override',
        'giorni_preavviso', 'url_gestione', 'note', 'renewal_interval_months', 'renewal_notification_channels'
    ];

    protected $casts = [
        'data_scadenza' => 'date',
        'rinnovo_automatico' => 'boolean',
        'importo' => 'decimal:2',
        'notification_override' => 'array',
        'renewal_interval_months' => 'integer',
        'renewal_notification_channels' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class);
    }

    // Sincronizzazione automatica con le scadenze
    protected static function booted()
    {
        static::saved(function ($subscription) {
            app(DeadlineSyncService::class)->syncFromSource($subscription);
        });

        static::deleting(function ($subscription) {
            app(DeadlineSyncService::class)->deleteFromSource($subscription);
        });
    }
}
