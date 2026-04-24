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
        // Regole globali per l'utente
        Schema::table('users', function (Blueprint $table) {
            $table->json('default_notification_rules')->nullable()->after('password');
        });

        // Regole di override per l'abbonamento/spesa
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->json('notification_override')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_notification_rules');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('notification_override');
        });
    }
};
