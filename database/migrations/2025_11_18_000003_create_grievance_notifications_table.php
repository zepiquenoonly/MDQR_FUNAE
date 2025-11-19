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
        Schema::create('grievance_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grievance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Tipo de notificação
            $table->enum('type', [
                'grievance_created',        // Reclamação criada
                'status_changed',           // Status alterado
                'assigned',                 // Atribuída a técnico
                'comment_added',            // Comentário adicionado
                'resolved',                 // Resolvida
                'rejected',                 // Rejeitada
            ]);

            // Canal de notificação
            $table->enum('channel', ['email', 'sms', 'system'])->default('email');

            // Destinatário
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();

            // Conteúdo da notificação
            $table->string('subject')->nullable();
            $table->text('message');

            // Dados adicionais (JSON)
            $table->json('data')->nullable();

            // Status de envio
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);

            // Tracking de abertura e cliques
            $table->boolean('opened')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->boolean('clicked')->default(false);
            $table->timestamp('clicked_at')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['grievance_id', 'type']);
            $table->index(['status', 'created_at']);
            $table->index('recipient_email');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grievance_notifications');
    }
};
