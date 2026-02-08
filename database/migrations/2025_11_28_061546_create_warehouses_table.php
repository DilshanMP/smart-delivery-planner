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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade')->comment('Parent company');
            $table->string('code', 50)->unique()->comment('Unique warehouse code');
            $table->string('name')->comment('Warehouse name');
            $table->text('address')->nullable()->comment('Warehouse address');
            $table->decimal('latitude', 10, 8)->nullable()->comment('GPS latitude');
            $table->decimal('longitude', 11, 8)->nullable()->comment('GPS longitude');
            $table->boolean('is_active')->default(true)->comment('Active status');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('company_id');
            $table->index('is_active');
            $table->index(['latitude', 'longitude']); // For geospatial queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
