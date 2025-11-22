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
        // Usando Schema::table para ser compatível com diferentes databases
        Schema::table('grievances', function (Blueprint $table) {
            // Para SQLite, precisamos usar change() mas primeiro precisamos
            // garantir que a coluna pode ser alterada
            $table->enum('status', [
                'submitted',
                'under_review', 
                'assigned',
                'in_progress',
                'pending_approval',
                'resolved',
                'rejected'
            ])->default('submitted')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table) {
            // Reverter para os estados antigos
            $table->enum('status', [
                'submitted',
                'under_review',
                'in_progress',
                'resolved',
                'closed',
                'rejected'
            ])->default('submitted')->change();
        });
    }
};
