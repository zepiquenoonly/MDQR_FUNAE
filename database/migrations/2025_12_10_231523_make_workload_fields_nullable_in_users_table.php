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
            // Make workload fields nullable - only technicians need these
            $table->integer('workload_capacity')->nullable()->default(null)->change();
            $table->integer('current_workload')->nullable()->default(null)->change();
            $table->boolean('is_available')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to non-nullable with defaults
            $table->integer('workload_capacity')->default(10)->change();
            $table->integer('current_workload')->default(0)->change();
            $table->boolean('is_available')->default(true)->change();
        });
    }
};
