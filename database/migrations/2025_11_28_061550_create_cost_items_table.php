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
        Schema::create('cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade')->comment('Parent route');
            $table->enum('item_type', ['fuel', 'meals', 'tolls', 'lodging', 'other'])->comment('Type of cost');
            $table->string('description')->comment('Cost item description');
            $table->decimal('estimated_cost', 10, 2)->default(0)->comment('Estimated cost (from AI or manual entry)');
            $table->decimal('actual_cost', 10, 2)->default(0)->comment('Actual cost incurred');
            $table->decimal('variance', 10, 2)->default(0)->comment('Difference between estimated and actual');
            $table->string('receipt_number')->nullable()->comment('Receipt/invoice number for tracking');
            $table->timestamp('incurred_at')->nullable()->comment('When cost was incurred');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('route_id');
            $table->index('item_type');
            $table->index('receipt_number');
            $table->index('incurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_items');
    }
};
