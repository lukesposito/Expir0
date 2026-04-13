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
        Schema::table('users', function (Blueprint $table) {
        // Se false, l'utente è bloccato dall'admin (non accede o non riceve nulla)
        $table->boolean('is_active')->default(true)->after('password');
        
        // Se false, l'utente ha scelto di non ricevere le email di avviso
        $table->boolean('receives_notifications')->default(true)->after('is_active');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'receives_notifications']);
        });
    }
};
