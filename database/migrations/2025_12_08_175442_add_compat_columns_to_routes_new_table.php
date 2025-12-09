<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routes_new', function (Blueprint $table) {
            // Add nullable alias columns for legacy queries
            $table->date('delivery_date')->nullable()->after('route_date');
            $table->decimal('actual_cost', 14, 2)->nullable()->after('actual_total_cost');
        });

        // Optional: populate delivery_date from route_date and actual_cost from actual_total_cost
        // NOTE: this uses DB::statement — run carefully on large tables.
        \Illuminate\Support\Facades\DB::statement("
            UPDATE routes_new
            SET delivery_date = route_date,
                actual_cost = actual_total_cost
            WHERE delivery_date IS NULL OR actual_cost IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('routes_new', function (Blueprint $table) {
            $table->dropColumn(['delivery_date', 'actual_cost']);
        });
    }
};
