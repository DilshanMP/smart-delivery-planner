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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade')->comment('Associated company (null for admins)');
            $table->string('phone_number', 20)->nullable()->after('email')->comment('User phone number');
            $table->boolean('is_active')->default(true)->after('password')->comment('Active status');

            // Index
            $table->index('company_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['company_id', 'phone_number', 'is_active']);
        });
    }
};
