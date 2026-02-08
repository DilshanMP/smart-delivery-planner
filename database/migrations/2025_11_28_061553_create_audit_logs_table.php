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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('User who performed the action');
            $table->string('auditable_type')->comment('Model class that was changed (polymorphic)');
            $table->unsignedBigInteger('auditable_id')->comment('ID of the record that was changed');
            $table->enum('action', ['create', 'update', 'delete', 'restore', 'predict'])->comment('Action performed');
            $table->json('old_values')->nullable()->comment('Original values before change (for update/delete)');
            $table->json('new_values')->nullable()->comment('New values after change (for create/update)');
            $table->string('ip_address', 45)->nullable()->comment('IP address of user');
            $table->string('user_agent')->nullable()->comment('Browser user agent');
            $table->text('notes')->nullable()->comment('Additional context');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('auditable_type');
            $table->index('auditable_id');
            $table->index(['auditable_type', 'auditable_id']); // Polymorphic index
            $table->index('action');
            $table->index('created_at'); // For time-based queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
