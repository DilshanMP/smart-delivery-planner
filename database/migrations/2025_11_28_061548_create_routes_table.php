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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade')->comment('Parent company');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict')->comment('Starting warehouse');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict')->comment('Assigned vehicle');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('restrict')->comment('Assigned driver');
            $table->string('route_code', 100)->unique()->comment('Unique route identifier (e.g., RT-2024-001)');
            $table->date('delivery_date')->comment('Scheduled delivery date');
            $table->enum('delivery_type', ['own', 'outside'])->default('own')->comment('Own company or external client delivery');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned')->comment('Route status');

            // Calculated fields
            $table->decimal('total_distance_km', 10, 2)->default(0)->comment('Total route distance (sum of all legs)');
            $table->integer('total_duration_minutes')->default(0)->comment('Total estimated duration (sum of all legs)');
            $table->decimal('estimated_cost', 12, 2)->default(0)->comment('Estimated total cost (from AI or manual)');
            $table->decimal('actual_cost', 12, 2)->default(0)->comment('Actual total cost (sum of cost items)');
            $table->decimal('cost_variance', 12, 2)->default(0)->comment('Difference between estimated and actual cost');
            $table->decimal('cost_variance_percentage', 8, 2)->default(0)->comment('Variance as percentage');

            // Timestamps
            $table->timestamp('started_at')->nullable()->comment('Route start timestamp');
            $table->timestamp('completed_at')->nullable()->comment('Route completion timestamp');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('route_code');
            $table->index('delivery_date');
            $table->index('status');
            $table->index('company_id');
            $table->index('warehouse_id');
            $table->index('vehicle_id');
            $table->index('driver_id');
            $table->index('delivery_type');
            $table->index(['company_id', 'delivery_date']); // Composite for common queries
            $table->index(['warehouse_id', 'delivery_date']); // Composite for warehouse-specific queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
