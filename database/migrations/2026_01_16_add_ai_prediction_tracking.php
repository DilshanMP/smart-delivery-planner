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
        // Add AI prediction columns to routes table (with safety checks)
        Schema::table('routes', function (Blueprint $table) {
            // AI Prediction Data
            if (!Schema::hasColumn('routes', 'ai_predicted_cost')) {
                $table->decimal('ai_predicted_cost', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes', 'ai_cost_percentage')) {
                $table->decimal('ai_cost_percentage', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('routes', 'ai_confidence')) {
                $table->decimal('ai_confidence', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('routes', 'ai_lower_bound')) {
                $table->decimal('ai_lower_bound', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes', 'ai_upper_bound')) {
                $table->decimal('ai_upper_bound', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes', 'ai_recommendation')) {
                $table->string('ai_recommendation', 50)->nullable(); // GOOD/WARNING/DANGER
            }
            if (!Schema::hasColumn('routes', 'ai_predicted_at')) {
                $table->timestamp('ai_predicted_at')->nullable();
            }

            // Actual vs Predicted Comparison (filled after delivery)
            if (!Schema::hasColumn('routes', 'actual_vs_predicted_difference')) {
                $table->decimal('actual_vs_predicted_difference', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes', 'prediction_accuracy_percentage')) {
                $table->decimal('prediction_accuracy_percentage', 5, 2)->nullable();
            }
        });

        // Create AI predictions history table (only if doesn't exist)
        if (!Schema::hasTable('ai_prediction_history')) {
            Schema::create('ai_prediction_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('route_id')->constrained()->onDelete('cascade');

                // Input Data
                $table->decimal('distance_km', 10, 2);
                $table->integer('total_stops');
                $table->integer('num_companies');
                $table->decimal('total_sales_value', 12, 2);
                $table->string('vehicle_type', 50);
                $table->string('day_of_week', 20);

                // Prediction Output
                $table->decimal('predicted_cost', 10, 2);
                $table->decimal('cost_percentage', 5, 2);
                $table->decimal('confidence', 5, 2);
                $table->decimal('lower_bound', 10, 2);
                $table->decimal('upper_bound', 10, 2);
                $table->string('recommendation', 50);

                // Model Info
                $table->string('model_version', 20)->default('v1.0');
                $table->json('full_response')->nullable(); // Store complete API response

                // Actual Data (filled later)
                $table->decimal('actual_cost', 10, 2)->nullable();
                $table->decimal('accuracy_percentage', 5, 2)->nullable();

                $table->timestamps();

                // Indexes
                $table->index('route_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop columns from routes table (with safety checks)
        Schema::table('routes', function (Blueprint $table) {
            $columns = [
                'ai_predicted_cost',
                'ai_cost_percentage',
                'ai_confidence',
                'ai_lower_bound',
                'ai_upper_bound',
                'ai_recommendation',
                'ai_predicted_at',
                'actual_vs_predicted_difference',
                'prediction_accuracy_percentage'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('routes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Drop table
        Schema::dropIfExists('ai_prediction_history');
    }
};
