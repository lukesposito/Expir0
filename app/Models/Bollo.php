<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Deadline;
use App\Models\Vehicle;

class Bollo extends Model
{
    protected $table = 'bolli';
    protected $fillable = ['vehicle_id', 'data_scadenza', 'importo', 'note'];
    protected $casts = ['data_scadenza' => 'date'];

    protected static function booted()
    {
        static::saved(function ($bollo) {
            Deadline::updateOrCreate(
                ['bollo_id' => $bollo->id], // Cerca la scadenza collegata a questo specifico bollo
                [
                    'vehicle_id' => $bollo->vehicle_id,
                    'tipo' => 'bollo',
                    'data_scadenza' => $bollo->data_scadenza,
                    'importo' => $bollo->importo,
                    'note' => $bollo->note ?? 'Scadenza Bollo Auto',
                    'pagato' => false
                ]
            );
        });

        static::deleting(function ($bollo) {
            Deadline::where('bollo_id', $bollo->id)->delete();
        });
    }

    public function vehicle() 
    { 
        return $this->belongsTo(Vehicle::class); 
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class);
    }
}