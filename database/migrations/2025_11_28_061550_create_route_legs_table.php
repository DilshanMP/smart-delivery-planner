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
        Schema::create('route_legs', function (Blueprint $table) {
         $table->id();
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade')->comment('Parent route');
            $table->integer('leg_number')->comment('Leg sequence (1, 2, 3, ..., N+1 for round trip)');
            $table->string('from_location')->comment('Starting location name');
            $table->string('to_location')->comment('Destination location name');
            $table->decimal('from_latitude', 10, 8)->nullable()->comment('Starting GPS latitude');
            $table->decimal('from_longitude', 11, 8)->nullable()->comment('Starting GPS longitude');
            $table->decimal('to_latitude', 10, 8)->nullable()->comment('Destination GPS latitude');
            $table->decimal('to_longitude', 11, 8)->nullable()->comment('Destination GPS longitude');
            $table->decimal('distance_km', 10, 2)->default(0)->comment('Leg distance in kilometers');
            $table->integer('duration_minutes')->default(0)->comment('Estimated travel time in minutes');
            $table->enum('calculation_method', ['google_maps_api', 'manual'])->default('manual')->comment('How distance/duration was calculated');
            $table->timestamp('calculated_at')->nullable()->comment('When distance/duration was calculated');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('route_id');
            $table->index('leg_number');
            $table->index(['route_id', 'leg_number']); // Composite for ordered retrieval
            $table->index('calculation_method');

            // Constraints
            $table->unique(['route_id', 'leg_number']); // Each route has unique leg numbers
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_legs');
    }
};
