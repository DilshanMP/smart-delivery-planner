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
        Schema::create('driver_performance_logs', function (Blueprint $table) {
           $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade')->comment('Parent vehicle');
            $table->enum('condition_rating', ['excellent', 'good', 'fair', 'poor'])->comment('Overall vehicle condition');
            $table->integer('odometer_reading')->nullable()->comment('Odometer reading at inspection (km)');
            $table->text('inspection_notes')->nullable()->comment('Detailed inspection notes');
            $table->boolean('needs_maintenance')->default(false)->comment('Flag if maintenance required');
            $table->text('maintenance_required')->nullable()->comment('Description of required maintenance');
            $table->date('next_service_due')->nullable()->comment('Next scheduled service date');
            $table->date('inspection_date')->comment('Date of this condition check');
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null')->comment('User who performed inspection');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('vehicle_id');
            $table->index('condition_rating');
            $table->index('inspection_date');
            $table->index('needs_maintenance');
            $table->index('next_service_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_performance_logs');
    }
};
