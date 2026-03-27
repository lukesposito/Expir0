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

        // Controlliamo se l'utente collegato alla patente ha una data di nascita salvata
        if ($this->user && $this->user->data_nascita) {
            $dataNascita = Carbon::parse($this->user->data_nascita);
            
            // 1. Calcoliamo quanti anni aveva la persona quando ha preso/rinnovato la patente
            $etaAlRilascio = $dataNascita->diffInYears($dataRilascio);

            // 2. Determiniamo gli anni di validità in base alla sua età in quel momento
            if ($etaAlRilascio < 50) {
                $anniValidita = 10; // Come te: 10 anni
            } elseif ($etaAlRilascio < 70) {
                $anniValidita = 5;  // Come tuo papà: 5 anni
            } elseif ($etaAlRilascio < 80) {
                $anniValidita = 3;
            } else {
                $anniValidita = 2;
            }

            // 3. IL TUO RAGIONAMENTO: Anno di rilascio + Anni di validità
            $annoScadenza = $dataRilascio->year + $anniValidita;

            // 4. Assegniamo il GIORNO e il MESE del suo compleanno a quell'anno
            $scadenzaEffettiva = Carbon::create(
                $annoScadenza, 
                $dataNascita->month, 
                $dataNascita->day
            );

        } else {
            // Caso di emergenza: se ti sei dimenticato di inserire la data di nascita dell'utente, 
            // il sistema fa un calcolo base di +10 anni esatti dal rilascio per non andare in errore.
            $scadenzaEffettiva = $dataRilascio->copy()->addYears(10);
        }

        // 5. Inserisce o aggiorna la scadenza nella tabella deadlines
        Deadline::updateOrCreate(
            [
                'user_id' => $this->user_id,
                'tipo' => 'patente'
            ],
            [
                'data_scadenza' => $scadenzaEffettiva,
                // Se hai inserito un importo usa quello, altrimenti lascia null
                'importo' => $this->importo, 
                'note' => 'Rinnovo Patente ' . ($this->tipo ? 'cat. '.$this->tipo : '') . '. Da rinnovare entro il giorno del compleanno!',
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