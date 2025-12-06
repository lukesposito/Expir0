<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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
        'data_pagamento',
    ];

    protected $casts = [
        'data_scadenza' => 'date',
        'data_pagamento' => 'date',
        'notifica_inviata' => 'boolean',
        'pagato' => 'boolean',
    ];

    // Accessors - logica di calcolo spostata dal controller
    protected $appends = [
        'giorni_rimanenti', 
        'urgente', 
        'stato_colore',
        'importo_formatted',
        'data_scadenza_formatted'
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
    
    // Accessors
    public function getGiorniRimanentiAttribute()
    {
        return Carbon::now()->diffInDays($this->data_scadenza, false);
    }

    public function getUrgenteAttribute()
    {
        return $this->giorni_rimanenti <= 10 && !$this->pagato;
    }

    public function getStatoColoreAttribute()
    {
        if ($this->pagato) {
            return 'green';
        }

        if ($this->giorni_rimanenti < 0) {
            return 'red'; // Scaduta
        }

        if ($this->giorni_rimanenti <= 7) {
            return 'red'; // Urgente
        }

        if ($this->giorni_rimanenti <= 30) {
            return 'yellow'; // In arrivo
        }

        return 'green'; // Ok
    }

    public function getImportoFormattedAttribute()
    {
        return $this->importo ? '€' . number_format($this->importo, 2, ',', '.') : '€0,00';
    }

    public function getDataScadenzaFormattedAttribute()
    {
        return $this->data_scadenza->format('d/m/Y');
    }

    public function getTipoIconAttribute()
    {
        return match($this->tipo) {
            'bollo' => '🎫',
            'assicurazione' => '🛡️',
            'patente' => '🪪',
            'revisione' => '🔧',
            default => '📄'
        };
    }

    public function getTipoColorAttribute()
    {
        return match($this->tipo) {
            'bollo' => '#eab308',
            'assicurazione' => '#3b82f6',
            'patente' => '#9333ea',
            'revisione' => '#f97316',
            default => '#6b7280'
        };
    }

    // Scopes utili
    public function scopeUrgenti($query)
    {
        return $query->where('pagato', false)
            ->where(function($q) {
                $q->where('data_scadenza', '<=', Carbon::now()->addDays(10))
                  ->orWhere('data_scadenza', '<', Carbon::now());
            });
    }

    public function scopeImminenti($query)
    {
        return $query->where('pagato', false)
            ->whereBetween('data_scadenza', [Carbon::now(), Carbon::now()->addDays(30)]);
    }

    public function scopePagate($query)
    {
        return $query->where('pagato', true);
    }

    public function scopeNonPagate($query)
    {
        return $query->where('pagato', false);
    }
}