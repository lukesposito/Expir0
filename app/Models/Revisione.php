<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revisione extends Model
{
    use HasFactory;

    protected $table = 'revisioni';

    protected $fillable = [
        'vehicle_id',
        'data_ultima_revisione',
        'data_prossima_revisione',
        'importo',
        'centro_revisione',
        'esito',
        'note',
    ];

    protected $casts = [
        'data_ultima_revisione' => 'date',
        'data_prossima_revisione' => 'date',
        'importo' => 'decimal:2',
    ];

    // Relazione con Vehicle
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    
    // Relazione con Deadline
    public function deadline()
    {
        return $this->hasOne(Deadline::class);
    }

    // Event: quando salvi una revisione, sincronizza deadline
    protected static function booted()
    {
        static::saved(function ($revisione) {
            $revisione->syncDeadline();
        });

        static::deleting(function ($revisione) {
            // Elimina anche la deadline collegata
            $revisione->deadline?->delete();
        });
    }

    // Metodo per sincronizzare deadline
    public function syncDeadline()
    {
        // Se esiste già una deadline, aggiornala
        if ($this->deadline) {
            $this->deadline->update([
                'vehicle_id' => $this->vehicle_id,
                'data_scadenza' => $this->data_prossima_revisione,
                'importo' => $this->importo,
                'note' => "Revisione presso: " . ($this->centro_revisione ?? 'N/D'),
            ]);
        } else {
            // Altrimenti creane una nuova
            Deadline::create([
                'vehicle_id' => $this->vehicle_id,
                'tipo' => 'revisione',
                'data_scadenza' => $this->data_prossima_revisione,
                'importo' => $this->importo,
                'note' => "Revisione presso: " . ($this->centro_revisione ?? 'N/D'),
                'revisione_id' => $this->id,
            ]);
        }
    }
}