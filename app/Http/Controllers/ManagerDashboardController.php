<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ManagerDashboardController extends Controller
{
    /**
     * Display the manager dashboard with team oversight.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        abort_if(!$user || !$user->hasRole('Gestor'), 403);

        $activePanel = $request->input('panel', 'dashboard');
        $status = $request->input('status');
        $priority = $request->input('priority');
        $type = $request->input('type');

        $filters = [
            'status' => $status !== null && $status !== '' ? $status : null,
            'priority' => $priority !== null && $priority !== '' ? $priority : null,
            'type' => $type !== null && $type !== '' ? $type : null,
            'search' => $request->input('search'),
        ];

        // Get technicians under this manager
        $technicians = User::where('manager_id', $user->id)
            ->whereHas('roles', fn($query) => $query->where('name', 'Técnico'))
            ->pluck('id');

        $baseQuery = Grievance::query()->whereIn('assigned_to', $technicians);

        $stats = [
            'pending_complaints' => (clone $baseQuery)->whereIn('status', ['submitted', 'under_review'])->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'high_priority' => (clone $baseQuery)->where('priority', 'high')->whereNotIn('status', ['resolved'])->count(),
            'pending_completion_requests' => (clone $baseQuery)->where('status', 'pending_approval')->count(),
            'active_technicians' => $technicians->count(),
            'resolved_this_month' => (clone $baseQuery)
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];

        $data = [];

        if ($activePanel === 'technicians') {
            $techniciansData = User::where('manager_id', $user->id)
                ->whereHas('roles', fn($query) => $query->where('name', 'Técnico'))
                ->with(['specializations', 'grievances' => function($query) {
                    $query->selectRaw('assigned_to, COUNT(*) as total_grievances, COUNT(CASE WHEN status = "resolved" THEN 1 END) as resolved_this_month')
                        ->whereBetween('resolved_at', [now()->startOfMonth(), now()->endOfMonth()])
                        ->groupBy('assigned_to');
                }])
                ->get()
                ->map(function ($technician) {
                    $grievanceStats = $technician->grievances->first();
                    return [
                        'id' => $technician->id,
                        'name' => $technician->name,
                        'email' => $technician->email,
                        'role' => $technician->getRoleNames()->first(),
                        'created_at' => $technician->created_at?->format('d/m/Y'),
                        'workload' => $technician->workload ?? 0,
                        'is_active' => $technician->is_active ?? true,
                        'specializations' => $technician->specializations->pluck('name')->toArray(),
                        'total_grievances' => $grievanceStats->total_grievances ?? 0,
                        'resolved_this_month' => $grievanceStats->resolved_this_month ?? 0,
                        'completion_rate' => $grievanceStats && $grievanceStats->total_grievances > 0
                            ? round(($grievanceStats->resolved_this_month / $grievanceStats->total_grievances) * 100, 1)
                            : 0,
                    ];
                });

            $data = [
                'technicians' => $techniciansData,
            ];
        } else {
            $grievancesQuery = (clone $baseQuery)
                ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
                ->when($filters['priority'], fn ($query, $priority) => $query->where('priority', $priority))
                ->when($filters['type'], fn ($query, $type) => $query->where('type', $type))
                ->when($filters['search'], function ($query, $search) {
                    $like = '%' . trim($search) . '%';
                    $query->where(function ($subQuery) use ($like) {
                        $subQuery->where('reference_number', 'like', $like)
                            ->orWhere('category', 'like', $like)
                            ->orWhere('district', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
                })
                ->with([
                    'user:id,name,email',
                    'assignedUser:id,name,email',
                    'attachments' => function ($query) {
                        $query->latest('uploaded_at')->take(10);
                    },
                    'updates' => function ($query) {
                        $query->with('user:id,name')
                            ->latest('created_at')
                            ->take(15);
                    },
                ]);

            $grievances = $grievancesQuery
                ->orderByDesc('updated_at')
                ->paginate(8)
                ->withQueryString()
                ->through(fn (Grievance $grievance) => $this->transformGrievance($grievance));

            $data = [
                'grievances' => $grievances,
            ];
        }

        return Inertia::render('Manager/Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'created_at' => $user->created_at?->format('d/m/Y'),
            ],
            'stats' => $stats,
            'filters' => $filters,
            'activePanel' => $activePanel,
            'statusOptions' => $this->statusOptions(),
            'priorityOptions' => $this->priorityOptions(),
            ...$data,
        ]);
    }

    /**
     * Transform grievance for frontend consumption.
     */
    private function transformGrievance(Grievance $grievance): array
    {
        $attachments = $grievance->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'original_filename' => $attachment->original_filename,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'uploaded_at' => optional($attachment->uploaded_at)->toIso8601String(),
                'url' => route('attachments.download', $attachment),
            ];
        });

        $updates = $grievance->updates->map(function ($update) {
            return [
                'id' => $update->id,
                'action_type' => $update->action_type,
                'action_label' => $update->action_label,
                'description' => $update->formatted_description,
                'comment' => $update->comment,
                'is_public' => $update->is_public,
                'created_at' => optional($update->created_at)->toIso8601String(),
                'user' => $update->user ? [
                    'id' => $update->user->id,
                    'name' => $update->user->name,
                ] : null,
            ];
        });

        return [
            'id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'category' => $grievance->category,
            'priority' => $grievance->priority,
            'status' => $grievance->status,
            'status_label' => $grievance->status_label,
            'province' => $grievance->province,
            'district' => $grievance->district,
            'description' => $grievance->description,
            'excerpt' => Str::limit(strip_tags($grievance->description), 200),
            'submitted_at' => optional($grievance->submitted_at)->toIso8601String(),
            'updated_at' => optional($grievance->updated_at)->toIso8601String(),
            'assigned_at' => optional($grievance->assigned_at)->toIso8601String(),
            'resolution_notes' => $grievance->resolution_notes,
            'is_anonymous' => $grievance->is_anonymous,
            'contact_name' => $grievance->display_name,
            'contact_email' => $grievance->contact_email ?? $grievance->user?->email,
            'contact_phone' => $grievance->contact_phone,
            'metadata' => $grievance->metadata,
            'assigned_user' => $grievance->assignedUser ? [
                'id' => $grievance->assignedUser->id,
                'name' => $grievance->assignedUser->name,
                'email' => $grievance->assignedUser->email,
            ] : null,
            'attachments' => $attachments,
            'updates' => $updates,
            'can_reassign' => true,
            'can_approve_completion' => $grievance->status === 'pending_approval',
            'can_escalate' => in_array($grievance->status, ['in_progress', 'pending_approval']),
            'is_pending_approval' => $grievance->status === 'pending_approval',
            'is_resolved' => $grievance->status === 'resolved',
        ];
    }

    /**
     * Options for status filter.
     */
    private function statusOptions(): array
    {
        return [
            ['label' => 'Todas', 'value' => null],
            ['label' => 'Submetida', 'value' => 'submitted'],
            ['label' => 'Em Análise', 'value' => 'under_review'],
            ['label' => 'Atribuída', 'value' => 'assigned'],
            ['label' => 'Em Andamento', 'value' => 'in_progress'],
            ['label' => 'Pendente de Aprovação', 'value' => 'pending_approval'],
            ['label' => 'Resolvida', 'value' => 'resolved'],
        ];
    }

    /**
     * Options for priority filter.
     */
    private function priorityOptions(): array
    {
        return [
            ['label' => 'Todas', 'value' => null],
            ['label' => 'Baixa', 'value' => 'low'],
            ['label' => 'Média', 'value' => 'medium'],
            ['label' => 'Alta', 'value' => 'high'],
        ];
    }
}
