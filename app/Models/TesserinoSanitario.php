<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Deadline;

class TesserinoSanitario extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'codice_fiscale', 'data_scadenza', 'note'];
    protected $casts = ['data_scadenza' => 'date'];

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class);
    }

    protected static function booted()
    {
        static::saved(function ($doc) {
            Deadline::updateOrCreate(
                ['tesserino_sanitario_id' => $doc->id],
                [
                    'user_id' => $doc->user_id,
                    'tipo' => 'tesserino_sanitario',
                    'data_scadenza' => $doc->data_scadenza,
                    'importo' => 0,
                    'note' => $doc->note ?? 'Scadenza Tesserino Sanitario CF: ' . strtoupper($doc->codice_fiscale),
                    'pagato' => false
                ]
            );
        });

        static::deleting(function ($doc) {
            Deadline::where('tesserino_sanitario_id', $doc->id)->delete();
        });
    }
}