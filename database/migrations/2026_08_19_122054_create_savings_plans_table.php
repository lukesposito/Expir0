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
        Schema::create('savings_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedTinyInteger('target_month');
            $table->unsignedSmallInteger('target_year');

            $table->decimal('target_amount', 12, 2);
            $table->decimal('monthly_suggestion', 12, 2);

            $table->string('status')
                ->default('active');
            // active | completed | dismissed

            $table->timestamps();

            $table->unique([
                'user_id',
                'target_month',
                'target_year',
            ], 'savings_plans_user_target_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_plans');
    }
};
