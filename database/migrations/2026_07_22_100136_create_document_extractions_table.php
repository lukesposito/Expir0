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
        Schema::create('document_extractions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status', 30)->default('pending');
            $table->string('document_category', 50)->nullable();
            $table->string('extraction_engine', 100)->nullable();

            $table->decimal('confidence_score', 5, 2)->nullable();

            $table->json('extracted_payload')->nullable();
            $table->json('normalized_payload')->nullable();

            $table->string('matched_entity_type', 100)->nullable();
            $table->unsignedBigInteger('matched_entity_id')->nullable();

            $table->text('review_notes')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamp('processed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('applied_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'document_category']);
            $table->index(['matched_entity_type', 'matched_entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_extractions');
    }
};
