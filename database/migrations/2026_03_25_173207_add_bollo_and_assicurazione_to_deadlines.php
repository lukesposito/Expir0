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
            $table->unsignedBigInteger('bollo_id')->nullable()->after('revisione_id');
            $table->unsignedBigInteger('assicurazione_id')->nullable()->after('bollo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deadlines', function (Blueprint $table) {
            //
        });
    }
};
