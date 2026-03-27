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
        Schema::table('patentes', function (Blueprint $table) {
            $table->decimal('importo', 8, 2)->nullable()->after('data_rilascio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patentes', function (Blueprint $table) {
            $table->dropColumn('importo');
        });
    }
};
