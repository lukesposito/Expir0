<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DocumentExtraction;

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
        'categoria',        // Categoria del documento (es: 'bollo', 'assicurazione', 'patente')
        'processing_status',
        'review_status',
        'extracted_data',
        'confidence_score',
    ];

    /**
     * Cast automatico dei tipi di dato
     */
    protected $casts = [
        'da_classificare' => 'boolean',
        'extracted_data' => 'array',
        'confidence_score' => 'decimal:2',
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

    public function extractions()
    {
        return $this->hasMany(DocumentExtraction::class);
    }

    public function latestExtraction()
    {
        return $this->hasOne(DocumentExtraction::class)->latestOfMany();
    }

    public function actionProposals()
    {
        return $this->hasMany(ActionProposal::class);
    }
}