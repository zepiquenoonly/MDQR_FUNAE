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
            // Adicionar coluna escalated como boolean
            $table->boolean('escalated')->default(false)->after('status');
            
            // Colunas relacionadas ao escalamento
            $table->timestamp('escalated_at')->nullable()->after('escalated');
            $table->integer('escalated_by')->nullable()->after('escalated_at');
            $table->text('escalation_reason')->nullable()->after('escalated_by');
            
            // Se quiser manter o metadata também pode
            // $table->json('metadata')->nullable()->change(); // Se não existir
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table) {
            $table->dropColumn([
                'escalated', 
                'escalated_at', 
                'escalated_by', 
                'escalation_reason'
            ]);
        });
    }
};