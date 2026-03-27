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
        Schema::table('deadlines', function (Blueprint $table) {
            $table->unsignedBigInteger('carta_identita_id')->nullable()->after('assicurazione_id');
            $table->unsignedBigInteger('tesserino_sanitario_id')->nullable()->after('carta_identita_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('deadlines', function (Blueprint $table) {
            $table->dropColumn(['carta_identita_id', 'tesserino_sanitario_id']);
        });
    }
};
