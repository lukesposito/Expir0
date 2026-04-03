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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null'); // Nel caso vogliano collegare il Telepass all'auto
            $table->string('nome'); // Es. "Netflix", "Revisione Caldaia", "IMU"
            $table->string('categoria'); // Es. "Casa", "Software", "Intrattenimento", "Tasse"
            $table->date('data_scadenza');
            $table->decimal('importo', 8, 2)->nullable();
            $table->boolean('rinnovo_automatico')->default(false);
            $table->integer('giorni_preavviso')->default(3); // Quanti giorni prima avvisare
            $table->string('url_gestione')->nullable(); // Es. link per disdire
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
