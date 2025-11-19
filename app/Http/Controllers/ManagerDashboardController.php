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
     * Display the manager dashboard with actionable grievances.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        abort_if(!$user || !$user->hasRole('Gestor'), 403);

        $filters = [
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'category' => $request->input('category'),
            'type' => $request->input('type'),
        ];

        $statusMap = [
            'open' => ['submitted', 'under_review', 'assigned'],
            'in_progress' => ['in_progress'],
            'pending_completion' => ['pending_approval'],
            'closed' => ['resolved', 'rejected'],
        ];

        $complaintsQuery = Grievance::query()
            ->with([
                'user:id,name',
                'assignedUser:id,name,email',
                'attachments:id,grievance_id,original_filename',
                'updates' => fn ($query) => $query
                    ->with('user:id,name')
                    ->latest('created_at')
                    ->take(20),
            ])
            ->when(
                $filters['priority'],
                fn ($query, $priority) => $query->where('priority', $priority)
            )
            ->when(
                $filters['category'],
                fn ($query, $category) => $query->where('category', $category)
            )
            ->when(
                $filters['status'],
                function ($query, $status) use ($statusMap) {
                    $query->whereIn('status', $statusMap[$status] ?? [$status]);
                }
            )
            ->latest('submitted_at');

        $complaints = $complaintsQuery
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Grievance $grievance) => $this->transformComplaint($grievance));

        $statsBase = Grievance::query();
        $stats = [
            'pending_complaints' => (clone $statsBase)
                ->whereIn('status', ['submitted', 'under_review', 'assigned'])
                ->count(),
            'in_progress' => (clone $statsBase)
                ->where('status', 'in_progress')
                ->count(),
            'high_priority' => (clone $statsBase)
                ->where('priority', 'high')
                ->whereNotIn('status', ['resolved', 'rejected'])
                ->count(),
            'pending_completion_requests' => (clone $statsBase)
                ->where('status', 'pending_approval')
                ->count(),
        ];

        $technicians = User::role('Técnico')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Manager/Dashboard', [
            'complaints' => $complaints,
            'stats' => $stats,
            'technicians' => $technicians,
            'filters' => $filters,
        ]);
    }

    /**
     * Prepare complaint payload for the frontend dashboard.
     */
    private function transformComplaint(Grievance $grievance): array
    {
        return [
            'id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'title' => sprintf('Reclamação %s', $grievance->reference_number),
            'description' => Str::limit(strip_tags($grievance->description), 240),
            'category' => $grievance->category,
            'priority' => $grievance->priority,
            'status' => $this->mapStatus($grievance->status),
            'status_label' => $grievance->status_label,
            'type' => 'complaint',
            'submitted_at' => optional($grievance->submitted_at)->toIso8601String(),
            'user' => $grievance->user ? [
                'id' => $grievance->user->id,
                'name' => $grievance->user->name,
            ] : [
                'id' => null,
                'name' => $grievance->display_name,
            ],
            'technician' => $grievance->assignedUser ? [
                'id' => $grievance->assignedUser->id,
                'name' => $grievance->assignedUser->name,
                'email' => $grievance->assignedUser->email,
            ] : null,
            'attachments' => $grievance->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'name' => $attachment->original_filename,
            ]),
            'activities' => $grievance->updates->map(fn ($update) => [
                'id' => $update->id,
                'description' => $update->formatted_description,
                'created_at' => optional($update->created_at)->toIso8601String(),
                'user' => $update->user ? [
                    'id' => $update->user->id,
                    'name' => $update->user->name,
                ] : null,
            ]),
        ];
    }

    /**
     * Map internal status to dashboard-friendly labels.
     */
    private function mapStatus(string $status): string
    {
        return match ($status) {
            'submitted', 'under_review', 'assigned' => 'open',
            'in_progress' => 'in_progress',
            'pending_approval' => 'pending_completion',
            'resolved', 'rejected' => 'closed',
            default => $status,
        };
    }
}

