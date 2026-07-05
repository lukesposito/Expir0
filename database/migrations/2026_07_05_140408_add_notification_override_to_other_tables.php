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
        Schema::table('revisioni', function (Blueprint $table) {
            $table->json('notification_override')->nullable()->after('note');
        });

        Schema::table('assicurazioni', function (Blueprint $table) {
            $table->json('notification_override')->nullable()->after('note');
        });

        Schema::table('bolli', function (Blueprint $table) {
            $table->json('notification_override')->nullable()->after('note');
        });

        Schema::table('carta_identitas', function (Blueprint $table) {
            $table->json('notification_override')->nullable()->after('note');
        });

        Schema::table('patentes', function (Blueprint $table) {
            $table->json('notification_override')->nullable()->after('importo');
        });

        Schema::table('tesserino_sanitarios', function (Blueprint $table) {
            $table->json('notification_override')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revisioni', function (Blueprint $table) {
            $table->dropColumn('notification_override');
        });

        Schema::table('assicurazioni', function (Blueprint $table) {
            $table->dropColumn('notification_override');
        });

        Schema::table('bolli', function (Blueprint $table) {
            $table->dropColumn('notification_override');
        });

        Schema::table('carta_identitas', function (Blueprint $table) {
            $table->dropColumn('notification_override');
        });

        Schema::table('patentes', function (Blueprint $table) {
            $table->dropColumn('notification_override');
        });

        Schema::table('tesserino_sanitarios', function (Blueprint $table) {
            $table->dropColumn('notification_override');
        });
    }
};
