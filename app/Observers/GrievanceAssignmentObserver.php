<?php

namespace App\Observers;

use App\Models\Grievance;
use App\Services\GrievanceAutoAssignmentService;
use Illuminate\Support\Facades\Log;

class GrievanceAssignmentObserver
{
    protected GrievanceAutoAssignmentService $assignmentService;

    public function __construct(GrievanceAutoAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Handle the Grievance "created" event.
     */
    public function created(Grievance $grievance): void
    {
        if ($grievance->status === 'submitted' && !$grievance->assigned_to) {
            Log::info("Triggering auto-assignment for new grievance {$grievance->reference_number}");
            $this->assignmentService->autoAssign($grievance);
        }
    }

    /**
     * Handle the Grievance "updated" event.
     */
    public function updated(Grievance $grievance): void
    {
        $original = $grievance->getOriginal();

        // Update workload when assignment changes
        if ($grievance->wasChanged('assigned_to')) {
            $oldAssignee = $original['assigned_to'] ?? null;
            $newAssignee = $grievance->assigned_to;

            if ($oldAssignee && $user = \App\Models\User::find($oldAssignee)) {
                $user->updateWorkload();
            }

            if ($newAssignee && $user = \App\Models\User::find($newAssignee)) {
                $user->updateWorkload();
            }
        }

        // Update workload when status changes to resolved/rejected
        if ($grievance->wasChanged('status') && 
            in_array($grievance->status, ['resolved', 'rejected', 'closed'])) {
            if ($grievance->assigned_to && $user = \App\Models\User::find($grievance->assigned_to)) {
                $user->updateWorkload();
            }
        }

        // Update workload when priority changes
        if ($grievance->wasChanged('priority') && $grievance->assigned_to) {
            if ($user = \App\Models\User::find($grievance->assigned_to)) {
                $user->updateWorkload();
            }
        }
    }
}
