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
        Schema::create('grievance_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grievance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Tipo de ação realizada
            $table->enum('action_type', [
                'created',           // Reclamação criada
                'status_changed',    // Mudança de status
                'assigned',          // Atribuída a técnico
                'reassigned',        // Reatribuída a outro técnico
                'comment_added',     // Comentário adicionado
                'priority_changed',  // Prioridade alterada
                'attachment_added',  // Anexo adicionado
                'resolved',          // Marcada como resolvida
                'rejected',          // Rejeitada
                'reopened'           // Reaberta
            ]);

            // Valores antigos e novos (para tracking de mudanças)
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();

            // Descrição da atualização
            $table->text('description')->nullable();

            // Comentário/nota do técnico ou gestor
            $table->text('comment')->nullable();

            // Metadados adicionais (JSON)
            $table->json('metadata')->nullable();

            // Flag se é visível para o utente
            $table->boolean('is_public')->default(true);

            $table->timestamps();

            // Índices para performance
            $table->index(['grievance_id', 'created_at']);
            $table->index('action_type');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grievance_updates');
    }
};
