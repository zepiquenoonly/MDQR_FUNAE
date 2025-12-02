<?php

namespace App\Observers;

use App\Models\Grievance;
use App\Models\GrievanceUpdate;
use App\Services\GrievanceAutoAssignmentService;
use App\Services\NotificationService;

class GrievanceObserver
{
    protected NotificationService $notificationService;
    protected GrievanceAutoAssignmentService $autoAssignmentService;

    public function __construct(
        NotificationService $notificationService,
        GrievanceAutoAssignmentService $autoAssignmentService
    ) {
        $this->notificationService = $notificationService;
        $this->autoAssignmentService = $autoAssignmentService;
    }

    /**
     * Handle the Grievance "created" event.
     */
    public function created(Grievance $grievance): void
    {
        try {
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'created',
                userId: $grievance->user_id,
                description: 'Reclamação submetida com sucesso',
                isPublic: true
            );

            // Enviar notificação de criação
            $this->notificationService->notifyGrievanceCreated($grievance);

            // Tentar auto assignment imediato se houver técnico com workload leve
            // Se não conseguir, deixa como "submitted" para os comandos agendados (24h)
            $this->autoAssignmentService->autoAssign($grievance);
        } catch (\Exception $e) {
            // Log error but don't break the grievance creation
            \Log::error('Error in GrievanceObserver::created: ' . $e->getMessage(), [
                'grievance_id' => $grievance->id,
                'exception' => $e
            ]);
        }
    }

    /**
     * Handle the Grievance "updating" event.
     */
    public function updating(Grievance $grievance): void
    {
        try {
        // Get original values before update
        $original = $grievance->getOriginal();

        $userId = auth()->id();

        // Track status changes
        if ($grievance->isDirty('status')) {
            $oldStatus = $original['status'] ?? null;
            $newStatus = $grievance->status;

            // Only log status changes if we have an old status (not for new records)
            if ($oldStatus !== null) {
                $description = $this->getStatusChangeDescription($oldStatus, $newStatus);

                GrievanceUpdate::log(
                    grievanceId: $grievance->id,
                    actionType: 'status_changed',
                    userId: $userId,
                    description: $description,
                    oldValue: $oldStatus,
                    newValue: $newStatus,
                    isPublic: true
                );

                // Enviar notificação de mudança de status
                // $this->notificationService->notifyStatusChanged($grievance, $oldStatus, $newStatus);

                // Notificações específicas para resolved e rejected
                if ($newStatus === 'resolved') {
                    // $this->notificationService->notifyResolved($grievance);
                } elseif ($newStatus === 'rejected') {
                    $reason = $grievance->resolution_notes ?? 'Sem justificativa fornecida';
                    // $this->notificationService->notifyRejected($grievance, $reason);
                }
            }
        }

        // Track assignment changes
        if ($grievance->isDirty('assigned_to')) {
            $oldAssignee = $original['assigned_to'] ?? null;
            $newAssignee = $grievance->assigned_to;
            
            if ($oldAssignee === null && $newAssignee !== null) {
                // First assignment
                GrievanceUpdate::log(
                    grievanceId: $grievance->id,
                    actionType: 'assigned',
                    userId: $userId,
                    description: 'Reclamação atribuída a um técnico',
                    newValue: (string) $newAssignee,
                    isPublic: true
                );

                // Enviar notificação de atribuição
                if ($grievance->assignedUser) {
                    $this->notificationService->notifyAssigned($grievance, $grievance->assignedUser);
                }
            } elseif ($oldAssignee !== null && $newAssignee !== null) {
                // Reassignment
                GrievanceUpdate::log(
                    grievanceId: $grievance->id,
                    actionType: 'reassigned',
                    userId: $userId,
                    description: 'Reclamação reatribuída a outro técnico',
                    oldValue: (string) $oldAssignee,
                    newValue: (string) $newAssignee,
                    isPublic: true
                );

                // Enviar notificação de reatribuição
                if ($grievance->assignedUser) {
                    $this->notificationService->notifyAssigned($grievance, $grievance->assignedUser);
                }
            }
        }

        // Track priority changes
        if ($grievance->isDirty('priority')) {
            $oldPriority = $original['priority'] ?? null;
            $newPriority = $grievance->priority;
            
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'priority_changed',
                userId: $userId,
                description: "Prioridade alterada de '{$oldPriority}' para '{$newPriority}'",
                oldValue: $oldPriority,
                newValue: $newPriority,
                isPublic: false  // Internal change
            );
        }

            // Track resolution
            if ($grievance->isDirty('resolved_at') && $grievance->resolved_at !== null) {
                GrievanceUpdate::log(
                    grievanceId: $grievance->id,
                    actionType: 'resolved',
                    userId: $userId,
                    description: 'Reclamação marcada como resolvida',
                    comment: $grievance->resolution_notes,
                    isPublic: true
                );
            }
        } catch (\Exception $e) {
            // Log error but don't break the grievance update
            \Log::error('Error in GrievanceObserver::updating: ' . $e->getMessage(), [
                'grievance_id' => $grievance->id,
                'exception' => $e
            ]);
        }
    }    /**
     * Get description for status change.
     */
    private function getStatusChangeDescription(string $oldStatus, string $newStatus): string
    {
        return match($newStatus) {
            'submitted' => 'Reclamação submetida',
            'under_review' => 'Reclamação em análise pelo gestor',
            'assigned' => 'Reclamação atribuída a um técnico',
            'in_progress' => 'Técnico iniciou trabalho na reclamação',
            'pending_approval' => 'Reclamação aguarda aprovação do gestor',
            'resolved' => 'Reclamação resolvida com sucesso',
            'rejected' => 'Reclamação foi rejeitada',
            default => "Estado alterado de '{$oldStatus}' para '{$newStatus}'",
        };
    }
}
