<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\GrievanceNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UtenteDashboardController extends Controller
{
    /**
     * Display the utente dashboard with their grievances.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_if(!$user, 403);

        // Buscar reclamações do usuário autenticado
        $baseQuery = Grievance::query()->where('user_id', $user->id);

        // Estatísticas gerais
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'submitted' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
            'pending' => (clone $baseQuery)->whereIn('status', ['submitted', 'under_review', 'assigned'])->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
        ];

        // Estatísticas por tipo
        $statsByType = [
            'complaints' => (clone $baseQuery)->where('type', 'complaint')->count(),
            'grievances' => (clone $baseQuery)->where('type', 'grievance')->count(),
            'suggestions' => (clone $baseQuery)->where('type', 'suggestion')->count(),
        ];

        // Estatísticas para gráficos (últimos 7 dias e este mês)
        $sevenDaysAgo = now()->subDays(7);
        $startOfMonth = now()->startOfMonth();

        // Por tipo (últimos 7 dias)
        $chartDataByType = [
            'complaints' => (clone $baseQuery)->where('type', 'complaint')
                ->where('created_at', '>=', $sevenDaysAgo)->count(),
            'grievances' => (clone $baseQuery)->where('type', 'grievance')
                ->where('created_at', '>=', $sevenDaysAgo)->count(),
            'suggestions' => (clone $baseQuery)->where('type', 'suggestion')
                ->where('created_at', '>=', $sevenDaysAgo)->count(),
        ];

        // Por status (últimos 7 dias)
        $chartDataByStatus = [
            'resolved' => (clone $baseQuery)->where('status', 'resolved')
                ->where('created_at', '>=', $sevenDaysAgo)->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')
                ->where('created_at', '>=', $sevenDaysAgo)->count(),
            'pending' => (clone $baseQuery)->whereIn('status', ['submitted', 'under_review', 'assigned'])
                ->where('created_at', '>=', $sevenDaysAgo)->count(),
        ];

        // Dados para tabela de submissões recentes (últimas 10)
        $recentSubmissions = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($grievance) {
                return [
                    'id' => $grievance->reference_number,
                    'type' => $grievance->type_label,
                    'status' => $grievance->status_label,
                    'statusClass' => $this->getStatusClass($grievance->status),
                    'statusColor' => $this->getStatusColor($grievance->status),
                    'date' => optional($grievance->created_at)->format('d/m/Y'),
                    'grievance_id' => $grievance->id,
                ];
            });

        // Filtros
        $filters = [
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'type' => $request->input('type'),
            'search' => $request->input('search'),
        ];

        // Aplicar filtros
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
                'attachments' => function ($query) {
                    $query->latest('uploaded_at')->take(10);
                },
                'updates' => function ($query) {
                    $query->with('user:id,name')
                        ->latest('created_at')
                        ->take(15);
                },
                'assignedUser:id,name,email',
            ]);

        $grievances = $grievancesQuery
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Grievance $grievance) => $this->transformGrievance($grievance));

        // Notificações não lidas
        $notifications = GrievanceNotification::where('user_id', $user->id)
            // ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'message' => $notification->message,
                    'grievance_id' => $notification->grievance_id,
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                ];
            });

        // Separar grievances por tipo para os componentes específicos
        $allGrievances = Grievance::where('user_id', $user->id)
            ->with(['attachments', 'assignedUser:id,name,email', 'project:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Grievance $g) => $this->transformGrievance($g));

        $complaints = $allGrievances->where('type', 'complaint')->values();
        $claims = $allGrievances->where('type', 'grievance')->values();
        $suggestions = $allGrievances->where('type', 'suggestion')->values();

        // Estatísticas específicas por tipo
        $complaintsStats = [
            'total' => $complaints->count(),
            'validated' => $complaints->where('status', 'resolved')->count(),
            'in_analysis' => $complaints->whereIn('status', ['submitted', 'under_review'])->count(),
            'rejected' => $complaints->where('status', 'rejected')->count(),
        ];

        $claimsStats = [
            'total' => $claims->count(),
            'validated' => $claims->where('status', 'resolved')->count(),
            'in_analysis' => $claims->whereIn('status', ['submitted', 'under_review'])->count(),
            'rejected' => $claims->where('status', 'rejected')->count(),
        ];

        $suggestionsStats = [
            'total' => $suggestions->count(),
            'approved' => $suggestions->where('status', 'resolved')->count(),
            'in_analysis' => $suggestions->whereIn('status', ['submitted', 'under_review'])->count(),
            'rejected' => $suggestions->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Utente/Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'created_at' => $user->created_at?->format('d/m/Y'),
            ],
            'stats' => $stats,
            'statsByType' => $statsByType,
            'chartDataByType' => $chartDataByType,
            'chartDataByStatus' => $chartDataByStatus,
            'recentSubmissions' => $recentSubmissions,
            'grievances' => $grievances,
            'complaints' => $complaints,
            'claims' => $claims,
            'suggestions' => $suggestions,
            'complaintsStats' => $complaintsStats,
            'claimsStats' => $claimsStats,
            'suggestionsStats' => $suggestionsStats,
            'notifications' => $notifications,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the grievances history page.
     */
    public function history(Request $request): Response
    {
        $user = $request->user();

        abort_if(!$user, 403);

        $grievances = Grievance::where('user_id', $user->id)
            ->with(['attachments', 'updates.user:id,name', 'assignedUser:id,name,email'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Grievance $grievance) => $this->transformGrievance($grievance));

        return Inertia::render('Utente/Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'grievances' => $grievances,
            'showHistory' => true,
        ]);
    }

    /**
     * Display a specific grievance.
     */
    public function show(Request $request, Grievance $grievance): Response|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        abort_if(!$user || $grievance->user_id !== $user->id, 403);

        $grievance->load([
            'attachments',
            'updates.user:id,name',
            'assignedUser:id,name,email',
            'user:id,name,email',
        ]);

        // If this is an AJAX request, return JSON data for the modal
        if ($request->header('X-Requested-With') === 'XMLHttpRequest' || $request->expectsJson()) {
            return response()->json([
                'grievance' => $this->transformGrievance($grievance),
            ]);
        }

        return Inertia::render('Utente/GrievanceDetail', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'grievance' => $this->transformGrievance($grievance),
        ]);
    }

    /**
     * Get status updates for a grievance.
     */
    public function getStatusUpdates(Request $request, Grievance $grievance): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        abort_if(!$user || $grievance->user_id !== $user->id, 403);

        $updates = $grievance->updates()
            ->with('user:id,name')
            ->latest('created_at')
            ->get()
            ->map(function ($update) {
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

        return response()->json([
            'updates' => $updates,
            'current_status' => $grievance->status,
            'status_label' => $grievance->status_label,
        ]);
    }

    /**
     * Mark notifications as read.
     */
    public function markNotificationsRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        abort_if(!$user, 403);

        $notificationIds = $request->input('notification_ids', []);

        if (empty($notificationIds)) {
            // Marcar todas como lidas
            // GrievanceNotification::where('user_id', $user->id)
            //     ->whereNull('read_at')
            //     ->update(['read_at' => now()]);
        } else {
            // Marcar específicas como lidas
            GrievanceNotification::where('user_id', $user->id)
                ->whereIn('id', $notificationIds)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notificações marcadas como lidas',
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

        // Extrair título da descrição ou usar categoria como fallback
        $title = $grievance->category ?? Str::limit(strip_tags($grievance->description ?? ''), 50);
        
        return [
            'id' => $grievance->reference_number ?? 'GRM-' . $grievance->id,
            'title' => $title,
            'reference_number' => $grievance->reference_number,
            'category' => $grievance->category,
            'priority' => $grievance->priority,
            'status' => $grievance->status,
            'status_label' => $grievance->status_label,
            'type' => $grievance->type,
            'type_label' => $grievance->type_label,
            'province' => $grievance->province,
            'district' => $grievance->district,
            'description' => $grievance->description,
            'excerpt' => Str::limit(strip_tags($grievance->description ?? ''), 200),
            'date' => optional($grievance->created_at)->format('Y-m-d'),
            'submitted_at' => optional($grievance->submitted_at)->toIso8601String(),
            'updated_at' => optional($grievance->updated_at)->toIso8601String(),
            'assigned_at' => optional($grievance->assigned_at)->toIso8601String(),
            'resolved_at' => optional($grievance->resolved_at)->toIso8601String(),
            'resolution_notes' => $grievance->resolution_notes,
            'is_anonymous' => $grievance->is_anonymous,
            'contact_name' => $grievance->display_name,
            'contact_email' => $grievance->contact_email ?? $grievance->user?->email,
            'contact_phone' => $grievance->contact_phone,
            'metadata' => $grievance->metadata,
            'attachments' => $attachments,
            'updates' => $updates,
            'assigned_to' => $grievance->assignedUser ? [
                'id' => $grievance->assignedUser->id,
                'name' => $grievance->assignedUser->name,
                'email' => $grievance->assignedUser->email,
            ] : null,
            'project' => $grievance->project ? [
                'id' => $grievance->project->id,
                'name' => $grievance->project->name,
            ] : null,
        ];
    }

    /**
     * Get CSS class for status badge.
     */
    private function getStatusClass(string $status): string
    {
        return match($status) {
            'resolved' => 'bg-green-100 text-green-700',
            'in_progress' => 'bg-blue-100 text-blue-700',
            'submitted', 'under_review', 'assigned' => 'bg-yellow-100 text-yellow-700',
            'rejected' => 'bg-red-100 text-red-700',
            'closed' => 'bg-gray-100 text-gray-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Get color for status indicator.
     */
    private function getStatusColor(string $status): string
    {
        return match($status) {
            'resolved' => 'bg-green-500',
            'in_progress' => 'bg-blue-500',
            'submitted', 'under_review', 'assigned' => 'bg-yellow-500',
            'rejected' => 'bg-red-500',
            'closed' => 'bg-gray-500',
            default => 'bg-gray-500',
        };
    }
}

