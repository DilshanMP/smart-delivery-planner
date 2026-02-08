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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade')->comment('Parent company');
            $table->string('registration_number', 50)->unique()->comment('Vehicle registration/plate number');
            $table->enum('vehicle_type', ['lorry', 'truck', 'van', 'container'])->comment('Type of vehicle');
            $table->string('make_model')->nullable()->comment('Vehicle make and model');
            $table->decimal('fuel_efficiency_kmpl', 8, 2)->nullable()->comment('Fuel efficiency in km per liter');
            $table->decimal('capacity_kg', 10, 2)->nullable()->comment('Load capacity in kilograms');
            $table->year('year')->nullable()->comment('Manufacturing year');
            $table->boolean('is_active')->default(true)->comment('Active status');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('registration_number');
            $table->index('company_id');
            $table->index('vehicle_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
