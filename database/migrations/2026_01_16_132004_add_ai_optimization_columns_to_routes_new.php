<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routes_new', function (Blueprint $table) {
            // AI Prediction columns
            if (!Schema::hasColumn('routes_new', 'ai_predicted_cost')) {
                $table->decimal('ai_predicted_cost', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'ai_cost_percentage')) {
                $table->decimal('ai_cost_percentage', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'ai_confidence')) {
                $table->decimal('ai_confidence', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'ai_lower_bound')) {
                $table->decimal('ai_lower_bound', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'ai_upper_bound')) {
                $table->decimal('ai_upper_bound', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'ai_recommendation')) {
                $table->string('ai_recommendation', 50)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'ai_predicted_at')) {
                $table->timestamp('ai_predicted_at')->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'actual_vs_predicted_difference')) {
                $table->decimal('actual_vs_predicted_difference', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'prediction_accuracy_percentage')) {
                $table->decimal('prediction_accuracy_percentage', 5, 2)->nullable();
            }

            // Optimization columns
            if (!Schema::hasColumn('routes_new', 'is_optimized')) {
                $table->boolean('is_optimized')->default(false);
            }
            if (!Schema::hasColumn('routes_new', 'optimization_algorithm')) {
                $table->string('optimization_algorithm', 10)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'distance_saved_km')) {
                $table->decimal('distance_saved_km', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'improvement_percentage')) {
                $table->decimal('improvement_percentage', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('routes_new', 'optimization_date')) {
                $table->timestamp('optimization_date')->nullable();
            }
        });

        // Create optimization history table
        if (!Schema::hasTable('route_optimizations')) {
            Schema::create('route_optimizations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('route_id')->constrained('routes_new')->onDelete('cascade');
                $table->string('algorithm', 10);
                $table->decimal('baseline_distance', 10, 2);
                $table->decimal('optimized_distance', 10, 2);
                $table->decimal('distance_saved', 10, 2);
                $table->decimal('improvement_percentage', 5, 2);
                $table->json('original_route');
                $table->json('optimized_route');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('routes_new', function (Blueprint $table) {
            $columns = [
                'ai_predicted_cost',
                'ai_cost_percentage',
                'ai_confidence',
                'ai_lower_bound',
                'ai_upper_bound',
                'ai_recommendation',
                'ai_predicted_at',
                'actual_vs_predicted_difference',
                'prediction_accuracy_percentage',
                'is_optimized',
                'optimization_algorithm',
                'distance_saved_km',
                'improvement_percentage',
                'optimization_date'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('routes_new', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('route_optimizations');
    }
};
