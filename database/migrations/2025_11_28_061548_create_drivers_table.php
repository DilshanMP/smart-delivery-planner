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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade')->comment('Parent company');
            $table->foreignId('vehicle_id')->nullable()->unique()->constrained('vehicles')->onDelete('set null')->comment('Assigned vehicle (exclusive 1:1)');
            $table->string('name')->comment('Driver full name');
            $table->string('license_number', 50)->unique()->comment('Driver license number');
            $table->string('contact_number', 20)->nullable()->comment('Driver contact number');
            $table->string('email')->nullable()->comment('Driver email');
            $table->integer('years_of_experience')->default(0)->comment('Years of driving experience');
            $table->boolean('is_active')->default(true)->comment('Active status');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('license_number');
            $table->index('company_id');
            $table->index('vehicle_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
