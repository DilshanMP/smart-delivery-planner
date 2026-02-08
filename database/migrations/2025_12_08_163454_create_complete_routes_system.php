<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Complete Route Management System matching exact requirements
     */
    public function up(): void
    {
        // 1. ROUTES TABLE - Main route planning
        Schema::create('routes_new', function (Blueprint $table) {
            $table->id();

            // PLANNING FIELDS (Step 1)
            $table->date('route_date');
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained(); // Start location
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('route_code', 50)->unique();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('delivery_type', ['own', 'outside'])->default('own');

            // ESTIMATED CALCULATIONS (Step 2)
            $table->decimal('estimated_distance_km', 10, 2)->nullable();
            $table->decimal('estimated_fuel_rate_per_km', 10, 2)->nullable(); // LKR per km
            $table->decimal('estimated_fuel_rate_per_litre', 10, 2)->default(350.00); // LKR per L
            $table->decimal('estimated_fuel_cost', 10, 2)->nullable();
            $table->decimal('estimated_meal_cost', 10, 2)->nullable();
            $table->decimal('estimated_accommodation_cost', 10, 2)->nullable();
            $table->integer('estimated_days')->default(1);
            $table->decimal('estimated_total_cost', 10, 2)->nullable(); // SUM of all costs

            // ACTUAL COMPLETION (Step 4)
            $table->decimal('actual_start_km', 10, 2)->nullable(); // From logbook
            $table->decimal('actual_end_km', 10, 2)->nullable(); // From logbook
            $table->decimal('actual_distance_km', 10, 2)->nullable(); // Calculated: end - start
            $table->decimal('actual_fuel_cost', 10, 2)->nullable();
            $table->decimal('actual_meal_cost', 10, 2)->nullable();
            $table->decimal('actual_accommodation_cost', 10, 2)->nullable();
            $table->decimal('actual_other_costs', 10, 2)->nullable();
            $table->decimal('actual_total_cost', 10, 2)->nullable(); // SUM of actual costs

            // VARIANCE TRACKING (Step 6)
            $table->decimal('km_variance', 10, 2)->nullable(); // Actual - Estimated
            $table->decimal('cost_variance', 10, 2)->nullable(); // Actual - Estimated
            $table->decimal('cost_variance_percentage', 10, 2)->nullable();

            // RETURNS & CLOSURE (Step 5)
            $table->decimal('return_sales_value', 10, 2)->default(0);
            $table->boolean('is_completed')->default(false);
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');

            // TIMESTAMPS & TRACKING
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // INDEXES for Power BI queries
            $table->index(['route_date', 'company_id']);
            $table->index(['driver_id', 'route_date']);
            $table->index(['vehicle_id', 'route_date']);
            $table->index('status');
        });

        // 2. ROUTE STOPS TABLE - Shop visits
        Schema::create('route_stops_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes_new')->cascadeOnDelete();
            $table->integer('stop_sequence'); // 1, 2, 3...

            // STOP DETAILS
            $table->string('shop_name'); // Customer/Shop name
            $table->text('shop_address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('stop_type', ['warehouse', 'shop', 'final'])->default('shop');

            // MULTI-COMPANY SALES (Step 3)
            $table->decimal('sales_value', 12, 2)->default(0);
            $table->integer('sales_qty')->default(0);
            $table->foreignId('sales_company_id')->nullable()->constrained('companies'); // Which company's sale

            // DELIVERY STATUS
            $table->boolean('is_delivered')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->text('special_instructions')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['route_id', 'stop_sequence']);
            $table->index('sales_company_id'); // For company-wise filtering
        });

        // 3. ROUTE LEGS TABLE - Distance between stops (Google Maps data)
        Schema::create('route_legs_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes_new')->cascadeOnDelete();
            $table->integer('leg_number'); // 1, 2, 3...

            // FROM/TO LOCATIONS
            $table->foreignId('from_stop_id')->constrained('route_stops_new');
            $table->foreignId('to_stop_id')->constrained('route_stops_new');
            $table->string('from_location');
            $table->string('to_location');

            // GPS COORDINATES
            $table->decimal('from_latitude', 10, 8);
            $table->decimal('from_longitude', 11, 8);
            $table->decimal('to_latitude', 10, 8);
            $table->decimal('to_longitude', 11, 8);

            // GOOGLE MAPS DATA
            $table->decimal('distance_km', 10, 2);
            $table->integer('duration_minutes')->nullable();
            $table->enum('calculation_method', ['google_maps', 'haversine', 'manual'])->default('google_maps');
            $table->timestamp('calculated_at')->nullable();
            $table->text('google_maps_route_json')->nullable(); // Store full route data

            $table->timestamps();
            $table->softDeletes();

            $table->index(['route_id', 'leg_number']);
        });

        // 4. ROUTE COST BREAKDOWN TABLE - Detailed cost tracking
        Schema::create('route_cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes_new')->cascadeOnDelete();

            $table->enum('cost_type', [
                'fuel',
                'meal',
                'accommodation',
                'toll',
                'parking',
                'maintenance',
                'other'
            ]);
            $table->string('description');
            $table->decimal('estimated_amount', 10, 2)->nullable();
            $table->decimal('actual_amount', 10, 2)->nullable();

            // EXPENSE TRACKING (Step 4 - optional)
            $table->string('receipt_number')->nullable();
            $table->string('receipt_file_path')->nullable(); // Upload bills
            $table->date('expense_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['route_id', 'cost_type']);
        });

        // 5. ROUTE COMPANY ALLOCATIONS - Multi-company cost split (Step 3)
        Schema::create('route_company_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes_new')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained();

            // SALES DATA
            $table->decimal('total_sales_value', 12, 2)->default(0);
            $table->integer('total_sales_qty')->default(0);
            $table->integer('number_of_stops')->default(0);

            // COST ALLOCATION (for Power BI)
            $table->decimal('allocated_cost', 10, 2)->default(0); // Proportional cost
            $table->decimal('allocation_percentage', 5, 2)->default(0); // % of total route

            // PROFITABILITY (Step 6)
            $table->decimal('profit', 10, 2)->default(0); // Sales - Allocated Cost
            $table->decimal('profit_margin_percentage', 5, 2)->default(0);

            $table->timestamps();

            $table->unique(['route_id', 'company_id']);
            $table->index('company_id'); // For Power BI filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_company_allocations');
        Schema::dropIfExists('route_cost_items');
        Schema::dropIfExists('route_legs_new');
        Schema::dropIfExists('route_stops_new');
        Schema::dropIfExists('routes_new');
    }
};
