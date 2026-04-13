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
        Schema::table('revisioni', function (Blueprint $table) {
            $table->decimal('prezzo', 10, 2)->nullable()->after('esito');
            $table->date('data_ultima_revisione')->nullable()->change();
        });

        Schema::table('assicurazioni', function (Blueprint $table) {
            $table->date('data_inizio')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
        {
        Schema::table('revisioni', function (Blueprint $table) {
            $table->dropColumn('prezzo');
            $table->date('data_ultima_revisione')->nullable(false)->change();
        });
    }
};