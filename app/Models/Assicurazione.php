<?php
namespace App\Models;

use App\Models\Deadline;
use Illuminate\Database\Eloquent\Model;

class Assicurazione extends Model
{
    protected $table = 'assicurazioni';
    protected $fillable = ['vehicle_id', 'compagnia', 'polizza', 'data_inizio', 'data_scadenza', 'importo', 'note'];
    protected $casts = ['data_inizio' => 'date', 'data_scadenza' => 'date'];

    protected static function booted()
    {
        static::saved(function ($assicurazione) {
            Deadline::updateOrCreate(
                ['assicurazione_id' => $assicurazione->id], // Cerca la scadenza collegata a questa specifica assicurazione
                [
                    'vehicle_id' => $assicurazione->vehicle_id,
                    'tipo' => 'assicurazione',
                    'data_scadenza' => $assicurazione->data_scadenza,
                    'importo' => $assicurazione->importo,
                    'note' => $assicurazione->note ?? 'Scadenza Assicurazione',
                    'pagato' => false
                ]
            );
        });

        static::deleting(function ($assicurazione) {
            Deadline::where('assicurazione_id', $assicurazione->id)->delete();
        });
    }

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
}