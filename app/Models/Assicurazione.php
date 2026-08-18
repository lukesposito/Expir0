<?php
namespace App\Models;

use App\Models\Deadline;
use App\Services\Deadlines\DeadlineSyncService;
use Illuminate\Database\Eloquent\Model;

class Assicurazione extends Model
{
    protected $table = 'assicurazioni';
    protected $fillable = ['vehicle_id', 'compagnia', 'polizza', 'data_inizio', 'data_scadenza', 'importo', 'note', 'notification_override'];
    protected $casts = ['data_inizio' => 'date', 'data_scadenza' => 'date', 'notification_override' => 'array'];

    protected static function booted()
    {
        static::saved(function ($assicurazione) {
            app(DeadlineSyncService::class)->syncFromSource($assicurazione);
        });

        static::deleting(function ($assicurazione) {
            app(DeadlineSyncService::class)->deleteFromSource($assicurazione);
        });
    }

    public function vehicle() 
    { 
        return $this->belongsTo(Vehicle::class);
    }
    
    public function deadlines()
    {
        return $this->hasMany(Deadline::class, 'assicurazione_id', 'id')
            ->where('tipo', 'assicurazione');
    }
}