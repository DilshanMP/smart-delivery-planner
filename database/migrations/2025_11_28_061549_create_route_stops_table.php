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
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade')->comment('Parent route');
            $table->integer('sequence')->comment('Stop order/sequence (1, 2, 3, ...)');
            $table->string('customer_name')->comment('Customer/recipient name');
            $table->text('delivery_address')->comment('Delivery address');
            $table->decimal('latitude', 10, 8)->nullable()->comment('GPS latitude');
            $table->decimal('longitude', 11, 8)->nullable()->comment('GPS longitude');
            $table->decimal('sale_value', 12, 2)->nullable()->comment('Value of goods being delivered (if applicable)');
            $table->text('special_instructions')->nullable()->comment('Special delivery instructions');
            $table->timestamp('delivered_at')->nullable()->comment('Actual delivery timestamp');
            $table->text('delivery_notes')->nullable()->comment('Notes from delivery (e.g., recipient signature, issues)');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('route_id');
            $table->index('sequence');
            $table->index(['route_id', 'sequence']); // Composite for ordered retrieval
            $table->index(['latitude', 'longitude']); // For geospatial queries

            // Constraints
            $table->unique(['route_id', 'sequence']); // Each route has unique sequence numbers
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
