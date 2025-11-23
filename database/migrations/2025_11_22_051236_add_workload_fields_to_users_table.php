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
            $table->integer('workload_capacity')->default(10)->after('email');
            $table->integer('current_workload')->default(0)->after('workload_capacity');
            $table->boolean('is_available')->default(true)->after('current_workload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['workload_capacity', 'current_workload', 'is_available']);
        });
    }
};
