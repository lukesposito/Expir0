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
            $table->boolean('notified_year')->default(false); // Avviso "Scade quest'anno"
            $table->boolean('notified_30')->default(false);   // Avviso -30gg
            $table->boolean('notified_15')->default(false);   // Avviso -15gg
            $table->boolean('notified_7')->default(false);    // Avviso -7gg (URGENTE)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('deadlines', function (Blueprint $table) {
            $table->dropColumn(['notified_year', 'notified_30', 'notified_15', 'notified_7']);
        });
    }
};
