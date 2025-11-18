<?php

namespace App\Observers;

use App\Models\Grievance;
use App\Models\GrievanceUpdate;

class GrievanceObserver
{
    /**
     * Handle the Grievance "created" event.
     */
    public function created(Grievance $grievance): void
    {
        GrievanceUpdate::log(
            grievanceId: $grievance->id,
            actionType: 'created',
            userId: $grievance->user_id,
            description: 'Reclamação submetida com sucesso',
            isPublic: true
        );
    }

    /**
     * Handle the Grievance "updating" event.
     */
    public function updating(Grievance $grievance): void
    {
        // Get original values before update
        $original = $grievance->getOriginal();
        $userId = auth()->id();

        // Track status changes
        if ($grievance->isDirty('status')) {
            $oldStatus = $original['status'];
            $newStatus = $grievance->status;
            
            $description = $this->getStatusChangeDescription($oldStatus, $newStatus, $grievance);
            
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'status_changed',
                userId: $userId,
                description: $description,
                oldValue: $oldStatus,
                newValue: $newStatus,
                isPublic: true
            );
        }

        // Track assignment changes
        if ($grievance->isDirty('assigned_to')) {
            $oldAssignee = $original['assigned_to'];
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
            }
        }

        // Track priority changes
        if ($grievance->isDirty('priority')) {
            $oldPriority = $original['priority'];
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
    }

    /**
     * Get description for status change.
     */
    private function getStatusChangeDescription(string $oldStatus, string $newStatus, Grievance $grievance): string
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
