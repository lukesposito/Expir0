<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Deadline;

class CartaIdentita extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'numero', 'comune_rilascio', 'data_rilascio', 'data_scadenza', 'note'];
    protected $casts = ['data_rilascio' => 'date', 'data_scadenza' => 'date'];

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
                ['carta_identita_id' => $doc->id],
                [
                    'user_id' => $doc->user_id,
                    'tipo' => 'carta_identita',
                    'data_scadenza' => $doc->data_scadenza,
                    'importo' => 0,
                    'note' => $doc->note ?? 'Scadenza Carta d\'Identità: ' . $doc->numero,
                    'pagato' => false
                ]
            );
        });

        static::deleting(function ($doc) {
            Deadline::where('carta_identita_id', $doc->id)->delete();
        });
    }
}