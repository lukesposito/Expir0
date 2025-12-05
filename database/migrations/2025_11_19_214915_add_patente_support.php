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
        Schema::table('deadlines', function (Blueprint $table) {
            // Modifica enum per aggiungere 'patente'
            $table->dropColumn('tipo');
        });
        
        Schema::table('deadlines', function (Blueprint $table) {
            $table->enum('tipo', ['bollo', 'assicurazione', 'patente'])->after('vehicle_id');
        });
        
        // Crea tabella patenti (opzionale, se vuoi gestirle separate)
        Schema::create('patentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('numero_patente')->unique();
            $table->string('tipo')->nullable(); // A, B, C, ecc
            $table->date('data_rilascio')->nullable();
            $table->timestamps();
        });
        
        // Modifica deadlines per supportare patenti
        Schema::table('deadlines', function (Blueprint $table) {
            $table->foreignId('patente_id')->nullable()->after('vehicle_id')->constrained()->onDelete('cascade');
            // Rendi vehicle_id nullable
            $table->foreignId('vehicle_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patentes');
        Schema::table('deadlines', function (Blueprint $table) {
            $table->dropForeign(['patente_id']);
            $table->dropColumn('patente_id');
        });
    }
};
