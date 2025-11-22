<?php

namespace App\Services;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrievanceAutoAssignmentService
{
    /**
     * Auto-assign a grievance to the best available technician.
     */
    public function autoAssign(Grievance $grievance): ?User
    {
        Log::info("Starting auto-assignment for grievance {$grievance->reference_number}");

        $technician = $this->findBestTechnician($grievance);

        if (!$technician) {
            Log::warning("No available technician found for grievance {$grievance->reference_number}");
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

        return $technician;
    }

    /**
     * Find the best technician for a grievance.
     */
    protected function findBestTechnician(Grievance $grievance): ?User
    {
        $weight = $this->getGrievanceWeight($grievance);

        // Get all available technicians
        $technicians = User::role('Técnico')
            ->where('is_available', true)
            ->where(function ($query) use ($weight) {
                $query->whereRaw('(current_workload + ?) <= workload_capacity', [$weight]);
            })
            ->with('specializations')
            ->get();

        if ($technicians->isEmpty()) {
            return null;
        }

        // Score and sort technicians
        $scored = $technicians->map(function ($technician) use ($grievance) {
            return [
                'technician' => $technician,
                'score' => $this->calculateTechnicianScore($technician, $grievance),
            ];
        })->sortByDesc('score');

        return $scored->first()['technician'] ?? null;
    }

    /**
     * Calculate score for a technician based on grievance requirements.
     */
    protected function calculateTechnicianScore(User $technician, Grievance $grievance): float
    {
        $score = 0;

        // 1. Workload score (lower workload = higher score)
        $workloadPercentage = $technician->workload_capacity > 0
            ? ($technician->current_workload / $technician->workload_capacity)
            : 0;
        $score += (1 - $workloadPercentage) * 40;

        // 2. Specialization score
        if ($technician->hasSpecialization($grievance->category)) {
            $proficiency = $technician->getProficiencyLevel($grievance->category);
            $score += $proficiency * 15;
        }

        // 3. Location match score
        if ($grievance->province && $technician->province === $grievance->province) {
            $score += 20;
            
            if ($grievance->district && $technician->district === $grievance->district) {
                $score += 10;
            }
        }

        // 4. Priority handling (more experienced for urgent)
        if ($grievance->priority === 'urgent') {
            $maxProficiency = $technician->specializations->max('proficiency_level') ?? 0;
            $score += $maxProficiency * 5;
        }

        return $score;
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
     * Auto-assign all pending grievances.
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
            'failed' => 0,
        ];

        foreach ($pendingGrievances as $grievance) {
            if ($this->autoAssign($grievance)) {
                $results['assigned']++;
            } else {
                $results['failed']++;
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
