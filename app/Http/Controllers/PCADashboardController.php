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
     * Get complaints grouped by status
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

        return $query->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->total];
            })
            ->toArray();
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
     * Get complaints grouped by category
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

        return $query->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category,
                    'total' => $item->total,
                ];
            })
            ->toArray();
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
     * Get trend data for the last 6 months
     */
    private function getTrendData(): array
    {
        $months = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->locale('pt')->translatedFormat('M');
            
            $count = Grievance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $resolved = Grievance::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->whereIn('status', ['resolved', 'closed'])
                ->count();

            $months[] = $monthName;
            $data[] = [
                'total' => $count,
                'resolved' => $resolved,
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
}
