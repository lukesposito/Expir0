<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Chi carica
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null'); // Veicolo (opzionale all'inizio)
            $table->string('path'); // Dove sta il file
            $table->string('nome_originale'); // Nome vero (es. bollo.pdf)
            $table->string('tipo_file'); // pdf, jpg, png
            $table->boolean('da_classificare')->default(true); // Se true, l'utente deve ancora dire cos'è
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
