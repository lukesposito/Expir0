<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Deadline;
use App\Models\Vehicle;
use App\Services\Deadlines\DeadlineSyncService;

class Bollo extends Model
{
    protected $table = 'bolli';
    protected $fillable = ['vehicle_id', 'data_scadenza', 'importo', 'note', 'notification_override'];
    protected $casts = ['data_scadenza' => 'date', 'notification_override' => 'array'];

    protected static function booted()
    {
        static::saved(function ($bollo) {
            app(DeadlineSyncService::class)->syncFromSource($bollo);
        });

        static::deleting(function ($bollo) {
            app(DeadlineSyncService::class)->deleteFromSource($bollo);
        });
    }

    public function vehicle() 
    { 
        return $this->belongsTo(Vehicle::class); 
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class, 'bollo_id', 'id')
            ->where('tipo', 'bollo');
    }
}