<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deadline extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'revisione_id',
        'tipo',
        'data_scadenza',
        'importo',
        'note',
        'notifica_inviata',
        'pagato',
    ];

    protected $casts = [
        'data_scadenza' => 'date',
        'notifica_inviata' => 'boolean',
        'pagato' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisione()
    {
        return $this->belongsTo(Revisione::class);
    }
    
    // Helper per ottenere il soggetto della scadenza
    public function getSubjectAttribute()
    {
        if ($this->tipo === 'patente' && $this->user_id) {
            return 'Patente di ' . $this->user->name;
        }
        
        if ($this->vehicle_id) {
            return $this->vehicle->targa . ' (' . $this->vehicle->marca . ')';
        }
        
        return 'N/D';
    }
}