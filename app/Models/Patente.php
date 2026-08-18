<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Services\Deadlines\DeadlineSyncService;
use App\Services\Patenti\PatenteExpiryCalculator;

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
        'notification_override',
    ];

    protected $casts = [
        'data_rilascio' => 'date',
        'data_scadenza' => 'date',
        'importo' => 'decimal:2',
        'notification_override' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($patente) {
            app(DeadlineSyncService::class)->syncFromSource($patente);
        });

        static::deleting(function ($patente) {
            app(DeadlineSyncService::class)->deleteFromSource($patente);
        });
    }

    /* public function calcolaScadenzaEffettiva(): array
    {
        if (!$this->data_rilascio) {
            return [
                'status' => 'errore',
                'messaggio' => 'La data di rilascio è obbligatoria per calcolare la scadenza.',
                'data_scadenza' => null,
            ];
        }

        $dataRilascio = Carbon::parse($this->data_rilascio);
        $categorie = explode(',', strtoupper(str_replace(' ', '', $this->tipo ?? '')));
        $haPatentePesante = in_array('C', $categorie, true) || in_array('CE', $categorie, true);

        if ($this->user && $this->user->data_nascita) {
            $dataNascita = Carbon::parse($this->user->data_nascita);
            $etaAlRilascio = $dataNascita->diffInYears($dataRilascio);

            if ($haPatentePesante) {
                $anniValidita = 3;
            } else {
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

            $annoScadenza = $dataRilascio->year + $anniValidita;
            $scadenza = Carbon::create(
                $annoScadenza,
                $dataNascita->month,
                $dataNascita->day
            );

            return [
                'status' => 'ok_auto',
                'messaggio' => null,
                'data_scadenza' => $scadenza->format('Y-m-d'),
            ];
        }

        return [
            'status' => 'ok_auto',
            'messaggio' => null,
            'data_scadenza' => $dataRilascio
                ->copy()
                ->addYears($haPatentePesante ? 3 : 10)
                ->format('Y-m-d'),
        ];
    } */

    /* public function syncDeadline()
    {
        $esito = app(PatenteExpiryCalculator::class)->calculate(
            $patente->user_id,
            $patente->data_rilascio,
            $patente->tipo
        );

        if (($esito['status'] ?? null) !== 'ok_auto' || empty($esito['data_scadenza'])) {
            return;
        }

        Deadline::updateOrCreate(
            [
                'patente_id' => $this->id,
                'tipo' => 'patente',
                'pagato' => false,
            ],
            [
                'user_id' => $this->user_id,
                'data_scadenza' => $esito['data_scadenza'],
                'importo' => $this->importo,
                'note' => 'Rinnovo Patente ' . ($this->tipo ? 'cat. ' . strtoupper($this->tipo) : '') . '. Da rinnovare entro il giorno del compleanno!',
            ]
        );
    } */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class, 'patente_id', 'id')
            ->where('tipo', 'patente');
    }
}