<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Visto che dall'errore sappiamo che usi MySQL, usiamo un comando 
        // SQL diretto per trasformare la colonna in un semplice testo da 50 caratteri.
        // Questo distrugge per sempre il limite dell'ENUM!
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN tipo VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deadlines', function (Blueprint $table) {
            //
        });
    }
};
