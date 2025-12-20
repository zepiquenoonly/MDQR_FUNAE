<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('active')->after('is_available');
        });

        // Atualizar os dados existentes
        \App\Models\User::where('is_available', true)->update(['status' => 'active']);
        \App\Models\User::where('is_available', false)->update(['status' => 'inactive']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};