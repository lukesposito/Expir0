<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Patente extends Model
{
    protected $table = 'patentes';
    
    protected $fillable = [
        'user_id',
        'numero_patente',
        'tipo',
        'data_rilascio',
        'importo',
    ];

    protected $casts = [
        'data_rilascio' => 'date',
        'importo' => 'decimal:2',
    ];

    // Blocco per gli eventi automatici
    protected static function booted()
    {
        static::saved(function ($patente) {
            $patente->syncDeadline();
        });

        static::deleting(function ($patente) {
            // Elimina anche le scadenze patente di questa persona
            Deadline::where('tipo', 'patente')->where('user_id', $patente->user_id)->delete();
        });
    }

    public function syncDeadline()
    {
        $dataRilascio = Carbon::parse($this->data_rilascio);

        // 1. GESTIONE CATEGORIE MULTIPLE (es. "A, B, C")
        // Rimuoviamo gli spazi vuoti, mettiamo in maiuscolo e spezziamo la stringa in un Array
        $categorie = explode(',', strtoupper(str_replace(' ', '', $this->tipo ?? '')));

        // Controlliamo se nell'array esiste la C (ho aggiunto anche CE nel caso la scrivessero)
        $haPatentePesante = in_array('C', $categorie) || in_array('CE', $categorie);

        // Controlliamo se l'utente collegato alla patente ha una data di nascita salvata
        if ($this->user && $this->user->data_nascita) {
            $dataNascita = Carbon::parse($this->user->data_nascita);
            
            // Calcoliamo quanti anni aveva la persona quando ha preso/rinnovato la patente
            $etaAlRilascio = $dataNascita->diffInYears($dataRilascio);

            // Determiniamo gli anni di validità
            if ($haPatentePesante) {
                $anniValidita = 3; // REGOLA FISSA: Se ha la C o CE -> 3 anni
            } else {
                // REGOLA STANDARD: Basata sull'età per patenti A e B
                if ($etaAlRilascio < 50) {
                    $anniValidita = 10;
                } elseif ($etaAlRilascio < 70) {
                    $anniValidita = 5;
                } elseif ($etaAlRilascio < 80) {
                    $anniValidita = 3;
                } else {
                    $anniValidita = 2;
                }
            }

            // Calcolo: Anno di rilascio + Anni di validità
            $annoScadenza = $dataRilascio->year + $anniValidita;

            // Assegniamo il GIORNO e il MESE del suo compleanno a quell'anno
            $scadenzaEffettiva = Carbon::create(
                $annoScadenza, 
                $dataNascita->month, 
                $dataNascita->day
            );

        } else {
            // Caso di emergenza: manca la data di nascita dell'utente
            if ($haPatentePesante) {
                $scadenzaEffettiva = $dataRilascio->copy()->addYears(3);
            } else {
                $scadenzaEffettiva = $dataRilascio->copy()->addYears(10);
            }
        }

        // Inserisce o aggiorna la scadenza nella tabella deadlines
        Deadline::updateOrCreate(
            [
                'user_id' => $this->user_id,
                'tipo' => 'patente'
            ],
            [
                'data_scadenza' => $scadenzaEffettiva,
                'importo' => $this->importo, 
                'note' => 'Rinnovo Patente ' . ($this->tipo ? 'cat. '.strtoupper($this->tipo) : '') . '. Da rinnovare entro il giorno del compleanno!',
                'pagato' => false
            ]
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class);
    }
}