<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class PCADashboardController extends Controller
{
    /**
     * Display PCA Dashboard with comprehensive statistics and reports
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        abort_if(!$user || !$user->hasRole('PCA'), 403);

        // Get date range filters
        $startDate = $request->input('start_date', now()->subMonths(3)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());
        $department = $request->input('department');
        $complaintType = $request->input('complaint_type');

        // Global Statistics
        $globalStats = $this->getGlobalStatistics($startDate, $endDate);

        // Complaints by Status
        $complaintsByStatus = $this->getComplaintsByStatus($startDate, $endDate, $department, $complaintType);

        // Complaints by Priority
        $complaintsByPriority = $this->getComplaintsByPriority($startDate, $endDate, $department, $complaintType);

        // Complaints by Type (Reclamações, Queixas, Sugestões)
        $complaintsByType = $this->getComplaintsByType($startDate, $endDate, $department);

        // Complaints by Category
        $complaintsByCategory = $this->getComplaintsByCategory($startDate, $endDate, $department, $complaintType);

        // Performance Metrics
        $performanceMetrics = $this->getPerformanceMetrics($startDate, $endDate);

        // Trend Data (last 6 months)
        $trendData = $this->getTrendData();

        // Top Technicians Performance
        $topTechnicians = $this->getTopTechnicians($startDate, $endDate);

        // Recent Activities
        $recentActivities = $this->getRecentActivities();

        // Project Insights
        $submissionsByProject = $this->getSubmissionsByProject($startDate, $endDate);
        $projectsWithTechnicians = $this->getProjectsWithTechnicians();
        $projectPerformance = $this->getProjectPerformance($startDate, $endDate);

        // Available filters
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'department' => $department,
            'complaint_type' => $complaintType,
        ];

        return Inertia::render('PCA/Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'PCA',
                'created_at' => $user->created_at->format('d/m/Y'),
            ],
            'globalStats' => $globalStats,
            'complaintsByStatus' => $complaintsByStatus,
            'complaintsByPriority' => $complaintsByPriority,
            'complaintsByType' => $complaintsByType,
            'complaintsByCategory' => $complaintsByCategory,
            'performanceMetrics' => $performanceMetrics,
            'trendData' => $trendData,
            'topTechnicians' => $topTechnicians,
            'recentActivities' => $recentActivities,
            'submissionsByProject' => $submissionsByProject,
            'projectsWithTechnicians' => $projectsWithTechnicians,
            'projectPerformance' => $projectPerformance,
            'filters' => $filters,
        ]);
    }

    /**
     * Get global statistics
     */
    private function getGlobalStatistics($startDate, $endDate): array
    {
        $totalComplaints = Grievance::whereBetween('created_at', [$startDate, $endDate])->count();
        $resolvedComplaints = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['resolved', 'closed'])->count();
        $pendingComplaints = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['submitted', 'under_review'])->count();
        $inProgressComplaints = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'in_progress')->count();

        $averageResolutionTime = Grievance::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->get()
            ->avg(function ($grievance) {
                return $grievance->created_at->diffInDays($grievance->resolved_at);
            });

        $resolutionRate = $totalComplaints > 0 ? ($resolvedComplaints / $totalComplaints) * 100 : 0;

        return [
            'total_complaints' => $totalComplaints,
            'resolved_complaints' => $resolvedComplaints,
            'pending_complaints' => $pendingComplaints,
            'in_progress_complaints' => $inProgressComplaints,
            'average_resolution_time' => round($averageResolutionTime ?? 0, 1),
            'resolution_rate' => round($resolutionRate, 1),
        ];
    }

    /**
     * Get complaints grouped by status and type
     */
    private function getComplaintsByStatus($startDate, $endDate, $department, $complaintType): array
    {
        $query = Grievance::whereBetween('created_at', [$startDate, $endDate]);

        if ($department) {
            $query->where('province', $department);
        }
        if ($complaintType) {
            $query->where('category', $complaintType);
        }

        $results = $query->select('status', 'type', DB::raw('count(*) as total'))
            ->groupBy('status', 'type')
            ->get();

        $statusData = [];
        foreach ($results as $item) {
            if (!isset($statusData[$item->status])) {
                $statusData[$item->status] = [
                    'total' => 0,
                    'complaint' => 0,
                    'grievance' => 0,
                    'suggestion' => 0,
                ];
            }
            $statusData[$item->status]['total'] += $item->total;
            $statusData[$item->status][$item->type] = $item->total;
        }

        return $statusData;
    }

    /**
     * Get complaints grouped by priority
     */
    private function getComplaintsByPriority($startDate, $endDate, $department, $complaintType): array
    {
        $query = Grievance::whereBetween('created_at', [$startDate, $endDate]);

        if ($department) {
            $query->where('province', $department);
        }
        if ($complaintType) {
            $query->where('category', $complaintType);
        }

        return $query->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->priority => $item->total];
            })
            ->toArray();
    }

    /**
     * Get complaints grouped by type (Reclamação, Queixa, Sugestão)
     */
    private function getComplaintsByType($startDate, $endDate, $department): array
    {
        $query = Grievance::whereBetween('created_at', [$startDate, $endDate]);

        if ($department) {
            $query->where('province', $department);
        }

        $results = $query->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type => $item->total];
            })
            ->toArray();

        // Garantir que todos os tipos estejam presentes
        return [
            'complaint' => $results['complaint'] ?? 0,
            'grievance' => $results['grievance'] ?? 0,
            'suggestion' => $results['suggestion'] ?? 0,
        ];
    }

    /**
     * Get complaints grouped by category and type
     */
    private function getComplaintsByCategory($startDate, $endDate, $department, $complaintType): array
    {
        $query = Grievance::whereBetween('created_at', [$startDate, $endDate]);

        if ($department) {
            $query->where('province', $department);
        }
        if ($complaintType) {
            $query->where('category', $complaintType);
        }

        $results = $query->select('category', 'type', DB::raw('count(*) as total'))
            ->groupBy('category', 'type')
            ->get();

        $categoryData = [];
        foreach ($results as $item) {
            if (!isset($categoryData[$item->category])) {
                $categoryData[$item->category] = [
                    'name' => $item->category,
                    'total' => 0,
                    'complaint' => 0,
                    'grievance' => 0,
                    'suggestion' => 0,
                ];
            }
            $categoryData[$item->category]['total'] += $item->total;
            $categoryData[$item->category][$item->type] = $item->total;
        }

        // Sort by total and limit to 10
        usort($categoryData, function($a, $b) {
            return $b['total'] - $a['total'];
        });

        return array_slice($categoryData, 0, 10);
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics($startDate, $endDate): array
    {
        $totalTechnicians = User::role('Técnico')->count();
        $activeTechnicians = User::role('Técnico')
            ->whereHas('assignedGrievances', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();

        $averageComplaintsPerTechnician = $activeTechnicians > 0
            ? Grievance::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('assigned_to')
                ->count() / $activeTechnicians
            : 0;

        return [
            'total_technicians' => $totalTechnicians,
            'active_technicians' => $activeTechnicians,
            'average_complaints_per_technician' => round($averageComplaintsPerTechnician, 1),
        ];
    }

    /**
     * Get trend data for the last 6 months by type
     */
    private function getTrendData(): array
    {
        $months = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->locale('pt')->translatedFormat('M');

            $complaints = Grievance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('type', 'complaint')
                ->count();

            $grievances = Grievance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('type', 'grievance')
                ->count();

            $suggestions = Grievance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('type', 'suggestion')
                ->count();

            $months[] = $monthName;
            $data[] = [
                'complaint' => $complaints,
                'grievance' => $grievances,
                'suggestion' => $suggestions,
                'total' => $complaints + $grievances + $suggestions,
            ];
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }

    /**
     * Get top performing technicians
     */
    private function getTopTechnicians($startDate, $endDate): array
    {
        return User::role('Técnico')
            ->withCount(['assignedGrievances as resolved_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('status', ['resolved', 'closed']);
            }])
            ->withCount(['assignedGrievances as total_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->having('total_count', '>', 0)
            ->orderByDesc('resolved_count')
            ->limit(10)
            ->get()
            ->map(function ($technician) {
                $resolutionRate = $technician->total_count > 0
                    ? ($technician->resolved_count / $technician->total_count) * 100
                    : 0;

                return [
                    'id' => $technician->id,
                    'name' => $technician->name,
                    'resolved_count' => $technician->resolved_count,
                    'total_count' => $technician->total_count,
                    'resolution_rate' => round($resolutionRate, 1),
                ];
            })
            ->toArray();
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(): array
    {
        return Grievance::with(['user:id,name', 'assignedUser:id,name'])
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($grievance) {
                return [
                    'id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'status' => $grievance->status,
                    'priority' => $grievance->priority,
                    'user_name' => $grievance->user?->name,
                    'technician_name' => $grievance->assignedUser?->name,
                    'updated_at' => $grievance->updated_at->format('d/m/Y H:i'),
                ];
            })
            ->toArray();
    }

    /**
     * Get submissions grouped by project
     */
    private function getSubmissionsByProject($startDate, $endDate): array
    {
        return \App\Models\Project::withCount(['grievances' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['grievances as resolved_grievances_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('status', ['resolved', 'closed']);
            }])
            ->having('grievances_count', '>', 0)
            ->orderByDesc('grievances_count')
            ->limit(10)
            ->get()
            ->map(function ($project) {
                $resolutionRate = $project->grievances_count > 0
                    ? ($project->resolved_grievances_count / $project->grievances_count) * 100
                    : 0;

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'total_submissions' => $project->grievances_count,
                    'resolved_submissions' => $project->resolved_grievances_count,
                    'resolution_rate' => round($resolutionRate, 1),
                    'province' => $project->provincia,
                ];
            })
            ->toArray();
    }

    /**
     * Get projects with technicians ready to respond
     */
    private function getProjectsWithTechnicians(): array
    {
        return \App\Models\Project::with(['technicians:id,name,email', 'grievances' => function ($query) {
                $query->whereIn('status', ['submitted', 'under_review', 'in_progress'])
                    ->latest()
                    ->limit(5);
            }])
            ->whereHas('technicians')
            ->orderBy('name')
            ->get()
            ->map(function ($project) {
                $activeGrievances = $project->grievances->count();
                $techniciansCount = $project->technicians->count();

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'technicians_count' => $techniciansCount,
                    'active_grievances' => $activeGrievances,
                    'technicians' => $project->technicians->map(function ($tech) {
                        return [
                            'id' => $tech->id,
                            'name' => $tech->name,
                            'email' => $tech->email,
                        ];
                    }),
                    'province' => $project->provincia,
                ];
            })
            ->toArray();
    }

    /**
     * Get project performance metrics
     */
    private function getProjectPerformance($startDate, $endDate): array
    {
        $totalProjects = \App\Models\Project::count();
        $projectsWithTechnicians = \App\Models\Project::whereHas('technicians')->count();
        $projectsWithSubmissions = \App\Models\Project::whereHas('grievances', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        $averageSubmissionsPerProject = $projectsWithSubmissions > 0
            ? \App\Models\Grievance::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('project_id')
                ->count() / $projectsWithSubmissions
            : 0;

        return [
            'total_projects' => $totalProjects,
            'projects_with_technicians' => $projectsWithTechnicians,
            'projects_with_submissions' => $projectsWithSubmissions,
            'average_submissions_per_project' => round($averageSubmissionsPerProject, 1),
        ];
    }
}
