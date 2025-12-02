<?php

namespace App\Services;

use App\Events\GrievanceAutoAssigned;
use App\Models\Grievance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrievanceAutoAssignmentService
{
    /**
     * Threshold for "light workload" - technician has less than 50% capacity used
     */
    protected const LIGHT_WORKLOAD_THRESHOLD = 0.5;

    /**
     * Auto-assign a grievance to the best available technician.
     * Only assigns if technician has light workload, otherwise leaves pending for manager review.
     */
    public function autoAssign(Grievance $grievance): ?User
    {
        Log::info("Starting auto-assignment for grievance {$grievance->reference_number}");

        $technician = $this->findBestTechnician($grievance);

        if (!$technician) {
            Log::info("No technician with light workload found for grievance {$grievance->reference_number}. Leaving pending for manager review.");
            
            // Mark as pending for manager review
            $grievance->update([
                'status' => 'pending_review',
            ]);
            
            return null;
        }

        DB::transaction(function () use ($grievance, $technician) {
            $grievance->update([
                'assigned_to' => $technician->id,
                'assigned_at' => now(),
                'status' => 'assigned',
            ]);

            $technician->updateWorkload();

            Log::info("Grievance {$grievance->reference_number} assigned to {$technician->name}");
        });

        // Dispatch event to notify technician
        event(new GrievanceAutoAssigned($grievance->fresh(), $technician));

        return $technician;
    }

    /**
     * Find the best technician for a grievance.
     * Only returns technicians assigned to the project with light workload.
     */
    protected function findBestTechnician(Grievance $grievance): ?User
    {
        // Must have a project to auto-assign
        if (!$grievance->project_id) {
            Log::info("Grievance {$grievance->reference_number} has no project. Cannot auto-assign.");
            return null;
        }

        $weight = $this->getGrievanceWeight($grievance);

        // Get technicians assigned to this project with light workload
        $technicians = User::role('Técnico')
            ->where('is_available', true)
            ->whereHas('projects', function ($query) use ($grievance) {
                $query->where('projects.id', $grievance->project_id);
            })
            ->where(function ($query) use ($weight) {
                $query->whereRaw('(current_workload + ?) <= workload_capacity', [$weight]);
            })
            ->get()
            ->filter(function ($technician) {
                // Only technicians with light workload (< 50% capacity)
                return $this->hasLightWorkload($technician);
            });

        if ($technicians->isEmpty()) {
            return null;
        }

        // Sort by workload (prefer less loaded technicians)
        $sorted = $technicians->sortBy(function ($technician) {
            return $technician->workload_capacity > 0 
                ? $technician->current_workload / $technician->workload_capacity 
                : 0;
        });

        return $sorted->first();
    }

    /**
     * Check if technician has light workload (less than 50% capacity used)
     */
    protected function hasLightWorkload(User $technician): bool
    {
        if ($technician->workload_capacity <= 0) {
            return false;
        }

        $workloadPercentage = $technician->current_workload / $technician->workload_capacity;
        return $workloadPercentage < self::LIGHT_WORKLOAD_THRESHOLD;
    }

    /**
     * Get weight of a grievance based on priority.
     */
    protected function getGrievanceWeight(Grievance $grievance): int
    {
        return match($grievance->priority) {
            'urgent' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 2,
        };
    }

    /**
     * Auto-assign pending grievances that have been waiting for 24+ hours.
     * This should be run by a scheduled task.
     */
    public function autoAssignAfter24Hours(): array
    {
        $pendingGrievances = Grievance::whereNull('assigned_to')
            ->where('status', 'pending_review')
            ->where('submitted_at', '<=', now()->subHours(24))
            ->orderBy('priority', 'desc')
            ->orderBy('submitted_at', 'asc')
            ->get();

        $results = [
            'total' => $pendingGrievances->count(),
            'assigned' => 0,
            'failed' => 0,
        ];

        foreach ($pendingGrievances as $grievance) {
            $technician = $this->forceAssign($grievance);
            if ($technician) {
                $results['assigned']++;
            } else {
                $results['failed']++;
            }
        }

        Log::info("Auto-assigned {$results['assigned']} grievances after 24h wait", $results);

        return $results;
    }

    /**
     * Force assign a grievance to any available technician (ignores light workload requirement).
     * Used after 24h waiting period.
     */
    protected function forceAssign(Grievance $grievance): ?User
    {
        $weight = $this->getGrievanceWeight($grievance);

        // First try technicians assigned to the project
        $technician = null;
        
        if ($grievance->project_id) {
            $technician = User::role('Técnico')
                ->where('is_available', true)
                ->whereHas('projects', function ($query) use ($grievance) {
                    $query->where('projects.id', $grievance->project_id);
                })
                ->where(function ($query) use ($weight) {
                    $query->whereRaw('(current_workload + ?) <= workload_capacity', [$weight]);
                })
                ->orderByRaw('current_workload / NULLIF(workload_capacity, 0) ASC')
                ->first();
        }

        // If no project technician available, get any available technician
        if (!$technician) {
            $technician = User::role('Técnico')
                ->where('is_available', true)
                ->where(function ($query) use ($weight) {
                    $query->whereRaw('(current_workload + ?) <= workload_capacity', [$weight]);
                })
                ->orderByRaw('current_workload / NULLIF(workload_capacity, 0) ASC')
                ->first();
        }

        if (!$technician) {
            Log::warning("No available technician for forced assignment of grievance {$grievance->reference_number}");
            return null;
        }

        DB::transaction(function () use ($grievance, $technician) {
            $grievance->update([
                'assigned_to' => $technician->id,
                'assigned_at' => now(),
                'status' => 'assigned',
            ]);

            $technician->updateWorkload();

            Log::info("Grievance {$grievance->reference_number} force-assigned to {$technician->name} after 24h");
        });

        event(new GrievanceAutoAssigned($grievance->fresh(), $technician));

        return $technician;
    }

    /**
     * Auto-assign all pending grievances (immediate).
     */
    public function autoAssignPending(): array
    {
        $pendingGrievances = Grievance::whereNull('assigned_to')
            ->whereIn('status', ['submitted', 'under_review'])
            ->orderBy('priority', 'desc')
            ->orderBy('submitted_at', 'asc')
            ->get();

        $results = [
            'total' => $pendingGrievances->count(),
            'assigned' => 0,
            'pending' => 0,
        ];

        foreach ($pendingGrievances as $grievance) {
            if ($this->autoAssign($grievance)) {
                $results['assigned']++;
            } else {
                $results['pending']++;
            }
        }

        return $results;
    }

    /**
     * Rebalance workload across technicians.
     */
    public function rebalanceWorkload(): void
    {
        $technicians = User::role('Técnico')->get();

        foreach ($technicians as $technician) {
            $technician->updateWorkload();
        }

        Log::info('Workload rebalanced for all technicians');
    }
}
