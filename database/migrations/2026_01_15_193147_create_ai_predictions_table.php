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
        // Add AI prediction columns to routes table (check if not exists)
        Schema::table('routes', function (Blueprint $table) {
            if (!Schema::hasColumn('routes', 'predicted_cost')) {
                $table->decimal('predicted_cost', 10, 2)->nullable()->after('actual_cost');
            }
            if (!Schema::hasColumn('routes', 'cost_percentage')) {
                $table->decimal('cost_percentage', 5, 2)->nullable()->after('predicted_cost');
            }
            if (!Schema::hasColumn('routes', 'model_confidence')) {
                $table->decimal('model_confidence', 5, 2)->nullable()->after('cost_percentage');
            }
            if (!Schema::hasColumn('routes', 'is_optimized')) {
                $table->boolean('is_optimized')->default(false)->after('model_confidence');
            }
            if (!Schema::hasColumn('routes', 'optimization_algorithm')) {
                $table->string('optimization_algorithm', 10)->nullable()->after('is_optimized');
            }
            if (!Schema::hasColumn('routes', 'distance_saved_km')) {
                $table->decimal('distance_saved_km', 10, 2)->nullable()->after('optimization_algorithm');
            }
            if (!Schema::hasColumn('routes', 'improvement_percentage')) {
                $table->decimal('improvement_percentage', 5, 2)->nullable()->after('distance_saved_km');
            }
            if (!Schema::hasColumn('routes', 'prediction_date')) {
                $table->timestamp('prediction_date')->nullable()->after('improvement_percentage');
            }
            if (!Schema::hasColumn('routes', 'optimization_date')) {
                $table->timestamp('optimization_date')->nullable()->after('prediction_date');
            }
        });

        // Create AI predictions log table (only if not exists)
        if (!Schema::hasTable('ai_predictions')) {
            Schema::create('ai_predictions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('route_id')->constrained()->onDelete('cascade');
                $table->decimal('predicted_cost', 10, 2);
                $table->decimal('actual_cost', 10, 2)->nullable();
                $table->decimal('accuracy_percentage', 5, 2)->nullable();
                $table->decimal('cost_percentage', 5, 2);
                $table->decimal('model_confidence', 5, 2);
                $table->json('input_data');
                $table->json('prediction_response');
                $table->timestamps();
            });
        }

        // Create route optimizations log table (only if not exists)
        if (!Schema::hasTable('route_optimizations')) {
            Schema::create('route_optimizations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('route_id')->constrained()->onDelete('cascade');
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $columns = [
                'predicted_cost',
                'cost_percentage',
                'model_confidence',
                'is_optimized',
                'optimization_algorithm',
                'distance_saved_km',
                'improvement_percentage',
                'prediction_date',
                'optimization_date'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('routes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('ai_predictions');
        Schema::dropIfExists('route_optimizations');
    }
};
