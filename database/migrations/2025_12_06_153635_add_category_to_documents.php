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
        Schema::table('documents', function (Blueprint $table) {
        $table->string('categoria')->nullable()->after('vehicle_id'); // Es: 'bollo', 'assicurazione', 'patente'
        // Rimuoviamo 'da_classificare' perché ora lo classifichiamo subito
        $table->dropColumn('da_classificare'); 
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('categoria');
            $table->boolean('da_classificare')->default(true)->after('tipo_file');
        });
    }
};
