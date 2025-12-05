<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['bollo', 'assicurazione']);
            $table->date('data_scadenza');
            $table->decimal('importo', 8, 2)->nullable();
            $table->text('note')->nullable();
            $table->boolean('notifica_inviata')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deadlines');
    }
};