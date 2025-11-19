<?php

namespace App\Services;

use App\Mail\GrievanceAssigned;
use App\Mail\GrievanceCommentAdded;
use App\Mail\GrievanceCreated;
use App\Mail\GrievanceRejected;
use App\Mail\GrievanceResolved;
use App\Mail\GrievanceStatusChanged;
use App\Models\Grievance;
use App\Models\GrievanceNotification;
use App\Models\GrievanceUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Enviar notificação de reclamação criada
     */
    public function notifyGrievanceCreated(Grievance $grievance): void
    {
        // Obter email do utente
        $recipientEmail = $this->getRecipientEmail($grievance);
        
        if (!$recipientEmail) {
            return;
        }

        // Criar registro de notificação
        $notification = GrievanceNotification::create([
            'grievance_id' => $grievance->id,
            'user_id' => $grievance->user_id,
            'type' => GrievanceNotification::TYPE_GRIEVANCE_CREATED,
            'channel' => GrievanceNotification::CHANNEL_EMAIL,
            'recipient_email' => $recipientEmail,
            'subject' => "Reclamação Recebida - {$grievance->reference_number}",
            'message' => "A sua reclamação foi recebida com sucesso e está a ser analisada pela nossa equipa.",
            'data' => [
                'reference_number' => $grievance->reference_number,
                'category' => $grievance->category,
                'status' => $grievance->status,
            ],
        ]);

        // Enviar email
        $this->sendEmail($notification, function() use ($grievance, $recipientEmail) {
            Mail::to($recipientEmail)->send(new GrievanceCreated($grievance));
        });
    }

    /**
     * Enviar notificação de mudança de status
     */
    public function notifyStatusChanged(Grievance $grievance, string $oldStatus, string $newStatus): void
    {
        $recipientEmail = $this->getRecipientEmail($grievance);
        
        if (!$recipientEmail) {
            return;
        }

        // Criar registro de notificação
        $notification = GrievanceNotification::create([
            'grievance_id' => $grievance->id,
            'user_id' => $grievance->user_id,
            'type' => GrievanceNotification::TYPE_STATUS_CHANGED,
            'channel' => GrievanceNotification::CHANNEL_EMAIL,
            'recipient_email' => $recipientEmail,
            'subject' => "Atualização de Status - {$grievance->reference_number}",
            'message' => $this->getStatusChangeMessage($oldStatus, $newStatus),
            'data' => [
                'reference_number' => $grievance->reference_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);

        // Enviar email
        $this->sendEmail($notification, function() use ($grievance, $recipientEmail, $oldStatus, $newStatus) {
            Mail::to($recipientEmail)->send(new GrievanceStatusChanged($grievance, $oldStatus, $newStatus));
        });
    }

    /**
     * Enviar notificação de atribuição a técnico
     */
    public function notifyAssigned(Grievance $grievance, User $assignedUser): void
    {
        $recipientEmail = $this->getRecipientEmail($grievance);
        
        if (!$recipientEmail) {
            return;
        }

        // Criar registro de notificação
        $notification = GrievanceNotification::create([
            'grievance_id' => $grievance->id,
            'user_id' => $grievance->user_id,
            'type' => GrievanceNotification::TYPE_ASSIGNED,
            'channel' => GrievanceNotification::CHANNEL_EMAIL,
            'recipient_email' => $recipientEmail,
            'subject' => "Reclamação Atribuída - {$grievance->reference_number}",
            'message' => "A sua reclamação foi atribuída a {$assignedUser->name} e está a ser analisada.",
            'data' => [
                'reference_number' => $grievance->reference_number,
                'assigned_to' => $assignedUser->name,
                'assigned_to_email' => $assignedUser->email,
            ],
        ]);

        // Enviar email
        $this->sendEmail($notification, function() use ($grievance, $recipientEmail, $assignedUser) {
            Mail::to($recipientEmail)->send(new GrievanceAssigned($grievance, $assignedUser));
        });
    }

    /**
     * Enviar notificação de comentário adicionado
     */
    public function notifyCommentAdded(Grievance $grievance, GrievanceUpdate $update): void
    {
        // Apenas notificar se o comentário for público
        if (!$update->is_public) {
            return;
        }

        $recipientEmail = $this->getRecipientEmail($grievance);
        
        if (!$recipientEmail) {
            return;
        }

        // Criar registro de notificação
        $notification = GrievanceNotification::create([
            'grievance_id' => $grievance->id,
            'user_id' => $grievance->user_id,
            'type' => GrievanceNotification::TYPE_COMMENT_ADDED,
            'channel' => GrievanceNotification::CHANNEL_EMAIL,
            'recipient_email' => $recipientEmail,
            'subject' => "Nova Atualização - {$grievance->reference_number}",
            'message' => "Foi adicionado um novo comentário à sua reclamação.",
            'data' => [
                'reference_number' => $grievance->reference_number,
                'comment' => $update->comment,
                'commented_by' => $update->user?->name,
            ],
        ]);

        // Enviar email
        $this->sendEmail($notification, function() use ($grievance, $recipientEmail, $update) {
            Mail::to($recipientEmail)->send(new GrievanceCommentAdded($grievance, $update));
        });
    }

    /**
     * Enviar notificação de reclamação resolvida
     */
    public function notifyResolved(Grievance $grievance): void
    {
        $recipientEmail = $this->getRecipientEmail($grievance);
        
        if (!$recipientEmail) {
            return;
        }

        // Criar registro de notificação
        $notification = GrievanceNotification::create([
            'grievance_id' => $grievance->id,
            'user_id' => $grievance->user_id,
            'type' => GrievanceNotification::TYPE_RESOLVED,
            'channel' => GrievanceNotification::CHANNEL_EMAIL,
            'recipient_email' => $recipientEmail,
            'subject' => "Reclamação Resolvida - {$grievance->reference_number}",
            'message' => "A sua reclamação foi resolvida. Consulte os detalhes da resolução.",
            'data' => [
                'reference_number' => $grievance->reference_number,
                'resolved_at' => $grievance->resolved_at?->toISOString(),
                'resolved_by' => $grievance->resolvedBy?->name,
            ],
        ]);

        // Enviar email
        $this->sendEmail($notification, function() use ($grievance, $recipientEmail) {
            Mail::to($recipientEmail)->send(new GrievanceResolved($grievance));
        });
    }

    /**
     * Enviar notificação de reclamação rejeitada
     */
    public function notifyRejected(Grievance $grievance, string $reason = ''): void
    {
        $recipientEmail = $this->getRecipientEmail($grievance);
        
        if (!$recipientEmail) {
            return;
        }

        // Criar registro de notificação
        $notification = GrievanceNotification::create([
            'grievance_id' => $grievance->id,
            'user_id' => $grievance->user_id,
            'type' => GrievanceNotification::TYPE_REJECTED,
            'channel' => GrievanceNotification::CHANNEL_EMAIL,
            'recipient_email' => $recipientEmail,
            'subject' => "Reclamação Não Procedente - {$grievance->reference_number}",
            'message' => "A sua reclamação foi analisada e considerada não procedente.",
            'data' => [
                'reference_number' => $grievance->reference_number,
                'reason' => $reason,
            ],
        ]);

        // Enviar email
        $this->sendEmail($notification, function() use ($grievance, $recipientEmail, $reason) {
            Mail::to($recipientEmail)->send(new GrievanceRejected($grievance, $reason));
        });
    }

    /**
     * Enviar email e atualizar status da notificação
     */
    protected function sendEmail(GrievanceNotification $notification, callable $sendCallback): void
    {
        try {
            $sendCallback();
            
            $notification->markAsSent();
            
            Log::info("Notificação enviada com sucesso", [
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'recipient' => $notification->recipient_email,
            ]);
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            
            Log::error("Falha ao enviar notificação", [
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'recipient' => $notification->recipient_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obter email do destinatário (utente)
     */
    protected function getRecipientEmail(Grievance $grievance): ?string
    {
        // Se a reclamação tem usuário associado, usar o email do usuário
        if ($grievance->user_id && $grievance->user) {
            return $grievance->user->email;
        }

        // Se é reclamação anônima mas tem email de contato, usar esse
        if ($grievance->contact_email) {
            return $grievance->contact_email;
        }

        return null;
    }

    /**
     * Obter mensagem personalizada para mudança de status
     */
    protected function getStatusChangeMessage(string $oldStatus, string $newStatus): string
    {
        return match($newStatus) {
            'under_review' => 'A sua reclamação está a ser analisada pela nossa equipa técnica.',
            'assigned' => 'A sua reclamação foi atribuída a um técnico especializado.',
            'in_progress' => 'O processamento da sua reclamação está em andamento.',
            'pending_approval' => 'A resolução da sua reclamação está pendente de aprovação.',
            'resolved' => 'A sua reclamação foi resolvida com sucesso.',
            'rejected' => 'A sua reclamação foi considerada não procedente após análise.',
            default => 'O status da sua reclamação foi atualizado.',
        };
    }

    /**
     * Reenviar notificações falhadas
     */
    public function retryFailedNotifications(int $maxRetries = 3): int
    {
        $notifications = GrievanceNotification::retryable($maxRetries)->get();
        $retried = 0;

        foreach ($notifications as $notification) {
            try {
                // Recriar e enviar o email baseado no tipo
                $this->resendNotification($notification);
                $retried++;
            } catch (\Exception $e) {
                Log::error("Falha ao reenviar notificação", [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $retried;
    }

    /**
     * Reenviar uma notificação específica
     */
    protected function resendNotification(GrievanceNotification $notification): void
    {
        $grievance = $notification->grievance;
        
        if (!$grievance) {
            throw new \Exception("Reclamação não encontrada");
        }

        // Enviar baseado no tipo
        match($notification->type) {
            GrievanceNotification::TYPE_GRIEVANCE_CREATED => 
                $this->sendEmail($notification, fn() => Mail::to($notification->recipient_email)->send(new GrievanceCreated($grievance))),
            
            GrievanceNotification::TYPE_STATUS_CHANGED => 
                $this->sendEmail($notification, fn() => Mail::to($notification->recipient_email)->send(
                    new GrievanceStatusChanged(
                        $grievance, 
                        $notification->data['old_status'] ?? '', 
                        $notification->data['new_status'] ?? ''
                    )
                )),
            
            GrievanceNotification::TYPE_ASSIGNED => 
                $this->sendEmail($notification, fn() => Mail::to($notification->recipient_email)->send(
                    new GrievanceAssigned($grievance, $grievance->assignedUser)
                )),
            
            GrievanceNotification::TYPE_RESOLVED => 
                $this->sendEmail($notification, fn() => Mail::to($notification->recipient_email)->send(new GrievanceResolved($grievance))),
            
            GrievanceNotification::TYPE_REJECTED => 
                $this->sendEmail($notification, fn() => Mail::to($notification->recipient_email)->send(
                    new GrievanceRejected($grievance, $notification->data['reason'] ?? '')
                )),
            
            default => throw new \Exception("Tipo de notificação desconhecido: {$notification->type}")
        };
    }
}
