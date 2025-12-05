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
        // Modifica l'enum per includere 'revisione'
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN tipo ENUM('bollo', 'assicurazione', 'patente', 'revisione') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: rimuovi 'revisione'
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN tipo ENUM('bollo', 'assicurazione', 'patente') NOT NULL");
    }
};
