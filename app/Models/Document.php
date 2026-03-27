<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    /**
     * I campi che possono essere assegnati in massa (Mass Assignment).
     * Fondamentale per far funzionare Document::create([...])
     */
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'associated_user_id',  // Per patente, carta_identita, tesserino_sanitario
        'path',             // Il percorso del file (es: documents/file.pdf)
        'nome_originale',   // Il nome vero del file (es: Assicurazione_Panda.pdf)
        'tipo_file',        // Il tipo MIME (es: application/pdf, image/jpeg)
        'da_classificare',  // Booleano: true se caricato dalla dashboard rapida
        'categoria',        // Categoria del documento (es: 'bollo', 'assicurazione', 'patente')
    ];

    /**
     * Cast automatico dei tipi di dato
     */
    protected $casts = [
        'da_classificare' => 'boolean',
    ];

    /**
     * Relazione: Un documento appartiene a un Utente (chi lo ha caricato)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione: Un documento può appartenere a un Veicolo
     * (Opzionale: sarà null se caricato dal "caricamento rapido")
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}