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
        'bollo_id',
        'assicurazione_id',
        'carta_identita_id',
        'tesserino_sanitario_id',
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
        'data_scadenza_formatted',
        'scade_quest_anno',
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

    public function bollo()
    {
        return $this->belongsTo(Bollo::class);
    }

    public function assicurazione()
    {
        return $this->belongsTo(Assicurazione::class);
    }

    public function cartaIdentita()
    {
        return $this->belongsTo(CartaIdentita::class);
    }

    public function tesserinoSanitario()
    {
        return $this->belongsTo(TesserinoSanitario::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
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
            'assicurazione' => '#ee7300', 
            'patente' => '#ec4899',       
            'revisione' => '#22c55e', 
            'carta_identita' => '#6f3404',      
            'tesserino_sanitario' => '#00e7fc',     
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

    public function getScadeQuestAnnoAttribute()
    {
        // Ritorna true se la scadenza è nell'anno in corso (es. 2026)
        return $this->data_scadenza->year === Carbon::now()->year;
    }

    protected static function booted()
    {
        // Quando una scadenza viene modificata...
        static::updating(function ($deadline) {
            // Se la data di scadenza è stata cambiata (es. l'ha rinnovata)
            if ($deadline->isDirty('data_scadenza')) {
                // Resettiamo tutti gli avvisi per il futuro!
                $deadline->notified_year = false;
                $deadline->notified_30 = false;
                $deadline->notified_15 = false;
                $deadline->notified_7 = false;
                $deadline->pagato = false; // Opzionale: rimettiamolo da pagare
            }
        });
    }
}