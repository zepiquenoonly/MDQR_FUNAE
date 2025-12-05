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
        Schema::table('grievances', function (Blueprint $table) {
            // Tornar a coluna description nullable para aceitar submissões sem texto
            // A coluna original é TEXT, então usamos text()->nullable()->change()
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table) {
            // Reverter para obrigatória (não recomendado em produção com dados existentes)
            $table->text('description')->nullable(false)->change();
        });
    }
};
