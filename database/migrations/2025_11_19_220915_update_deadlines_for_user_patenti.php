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
            // Aggiungi colonna user_id per patenti
            $table->foreignId('user_id')->nullable()->after('vehicle_id')->constrained()->onDelete('cascade');
            
            // Rendi vehicle_id nullable (per patenti non serve veicolo)
            $table->foreignId('vehicle_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deadlines', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
