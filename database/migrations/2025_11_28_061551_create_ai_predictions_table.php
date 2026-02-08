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
        Schema::create('ai_predictions', function (Blueprint $table) {
             $table->id();
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade')->comment('Parent route');
            $table->enum('prediction_type', ['cost', 'eta', 'both'])->comment('Type of prediction');
            $table->string('model_version')->default('v1.0')->comment('AI model version used');
            $table->json('input_features')->nullable()->comment('Input features used for prediction (JSON)');
            $table->decimal('predicted_value', 12, 2)->nullable()->comment('Predicted cost or duration');
            $table->decimal('actual_value', 12, 2)->nullable()->comment('Actual cost or duration (after completion)');
            $table->decimal('prediction_accuracy', 8, 2)->nullable()->comment('Accuracy percentage (100 - abs((predicted - actual) / actual * 100))');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('route_id');
            $table->index('prediction_type');
            $table->index('model_version');
            $table->index('prediction_accuracy');
            $table->index('created_at'); // For time-series analysis
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_predictions');
    }
};
