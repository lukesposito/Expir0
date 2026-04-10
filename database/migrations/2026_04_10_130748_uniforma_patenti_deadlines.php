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
        // Aggiungiamo la data di scadenza alla tabella patentes
        Schema::table('patentes', function (Blueprint $table) {
            $table->date('data_scadenza')->nullable()->after('data_rilascio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patentes', function (Blueprint $table) {
            $table->dropColumn('data_scadenza');
        });
    }
};
