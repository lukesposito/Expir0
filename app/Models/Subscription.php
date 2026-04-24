<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'vehicle_id', 'nome', 'categoria', 
        'data_scadenza', 'importo', 'rinnovo_automatico', 'notification_override',
        'giorni_preavviso', 'url_gestione', 'note', 
    ];

    protected $casts = [
        'data_scadenza' => 'date',
        'rinnovo_automatico' => 'boolean',
        'importo' => 'decimal:2',
        'notification_override' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class);
    }

    // Sincronizzazione automatica con le scadenze
    protected static function booted()
    {
        // 1. QUANDO CREI UNA SPESA -> CREA LA SCADENZA
        static::created(function ($subscription) {
            Deadline::create([
                'tipo' => 'spesa_abbonamento',
                'data_scadenza' => $subscription->data_scadenza,
                'importo' => $subscription->importo,
                'note' => $subscription->nome . ' (' . $subscription->categoria . ')' . ($subscription->note ? ' - ' . $subscription->note : ''),
                'user_id' => $subscription->user_id,
                'vehicle_id' => $subscription->vehicle_id,
                'subscription_id' => $subscription->id, // Il collegamento!
                'pagato' => false,
            ]);
        });

        // 2. QUANDO MODIFICHI UNA SPESA -> AGGIORNA LA SCADENZA
        static::updated(function ($subscription) {
            $deadline = Deadline::where('subscription_id', $subscription->id)->first();
            
            if ($deadline) {
                $deadline->update([
                    'data_scadenza' => $subscription->data_scadenza,
                    'importo' => $subscription->importo,
                    'note' => $subscription->nome . ' (' . $subscription->categoria . ')' . ($subscription->note ? ' - ' . $subscription->note : ''),
                    'user_id' => $subscription->user_id,
                    'vehicle_id' => $subscription->vehicle_id,
                    // Se imposti il rinnovo automatico, forse vuoi che segni 'pagato' in automatico? 
                    // Per ora lo lasciamo intatto.
                ]);
            }
        });

        // 3. QUANDO CANCELLI UNA SPESA -> ELIMINA LA SCADENZA
        static::deleting(function ($subscription) {
            DB::table('deadlines')
                ->where('subscription_id', $subscription->id)
                ->delete();
        });


        //DA VERIFICARE!!!!!!!
        // 3. QUANDO CANCELLI UNA SPESA -> ELIMINA LA SCADENZA
        // Nota: se hai messo onDelete('cascade') nella migration, questo passaggio
        // avviene già nel database, ma mettiamolo per sicurezza se usi SoftDeletes in futuro
        static::deleted(function ($subscription) {
            Deadline::where('subscription_id', $subscription->id)->delete();
        });
    }
}
