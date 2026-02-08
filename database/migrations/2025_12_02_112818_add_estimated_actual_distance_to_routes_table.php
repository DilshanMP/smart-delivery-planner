<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // Add estimated and actual distance columns (nullable)
            $table->decimal('estimated_distance_km', 10, 2)->nullable()->after('total_distance_km')->comment('Estimated distance for the route');
            $table->decimal('actual_distance_km', 10, 2)->nullable()->after('estimated_distance_km')->comment('Actual distance recorded');
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn(['estimated_distance_km', 'actual_distance_km']);
        });
    }
};
