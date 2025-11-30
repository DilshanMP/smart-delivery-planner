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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Unique company code');
            $table->string('name')->comment('Company name');
            $table->text('address')->nullable()->comment('Company address');
            $table->string('contact_person')->nullable()->comment('Primary contact person');
            $table->string('contact_number', 20)->nullable()->comment('Contact phone number');
            $table->string('email')->nullable()->comment('Company email');
            $table->boolean('is_active')->default(true)->comment('Active status');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
