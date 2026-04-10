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
        'data_scadenza',
        'importo',
    ];

    protected $casts = [
        'data_rilascio' => 'date',
        'data_scadenza' => 'date',
        'importo' => 'decimal:2',
    ];

    // Blocco per gli eventi automatici (MANTENUTO INALTERATO COME DA RICHIESTA)
    protected static function booted()
    {
        static::saved(function ($patente) {
            $patente->syncDeadline();
        });

        static::deleting(function ($patente) {
            Deadline::where('tipo', 'patente')->where('user_id', $patente->user_id)->delete();
        });
    }

    public function syncDeadline()
    {
        // SE LA PATENTE HA GIA' UNA SCADENZA (inserita a mano o calcolata dal Controller), USA QUELLA!
        if ($this->data_scadenza) {
            $scadenzaEffettiva = $this->data_scadenza;
        } else {
            // ALTRIMENTI USA LA TUA LOGICA CLASSICA DI FALLBACK
            $dataRilascio = Carbon::parse($this->data_rilascio);

            $categorie = explode(',', strtoupper(str_replace(' ', '', $this->tipo ?? '')));
            $haPatentePesante = in_array('C', $categorie) || in_array('CE', $categorie);

            if ($this->user && $this->user->data_nascita) {
                $dataNascita = Carbon::parse($this->user->data_nascita);
                $etaAlRilascio = $dataNascita->diffInYears($dataRilascio);

                if ($haPatentePesante) {
                    $anniValidita = 3; 
                } else {
                    if ($etaAlRilascio < 50) $anniValidita = 10;
                    elseif ($etaAlRilascio < 70) $anniValidita = 5;
                    elseif ($etaAlRilascio < 80) $anniValidita = 3;
                    else $anniValidita = 2;
                }

                $annoScadenza = $dataRilascio->year + $anniValidita;
                $scadenzaEffettiva = Carbon::create($annoScadenza, $dataNascita->month, $dataNascita->day);
            } else {
                if ($haPatentePesante) {
                    $scadenzaEffettiva = $dataRilascio->copy()->addYears(3);
                } else {
                    $scadenzaEffettiva = $dataRilascio->copy()->addYears(10);
                }
            }
        }

        // Inserisce o aggiorna la scadenza nella tabella deadlines (MANTENUTO INALTERATO)
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
        return $this->hasMany(Deadline::class, 'user_id', 'user_id')
                    ->where('tipo', 'patente');
    }
}