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
        Schema::create('action_proposals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('document_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('status')->default('pending');
            // pending | confirmed | rejected | applied | expired | failed

            $table->string('action_type');
            // mark_paid | renew | create_source | update_source | attach_document | forecast_alert

            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();

            $table->json('payload')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->text('reason')->nullable();

            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['source_type', 'source_id']);
            $table->index(['target_type', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_proposals');
    }
};
