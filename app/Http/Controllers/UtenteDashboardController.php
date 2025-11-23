<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\GrievanceNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UtenteDashboardController extends Controller
{
    /**
     * Date format constants
     */
    private const DATE_FORMAT = 'd/m/Y';
    private const DATETIME_FORMAT = 'd/m/Y H:i';

    /**
     * Display the user dashboard with grievances and statistics.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_if(!$user || !$user->hasRole('Utente'), 403);

        // Filtros
        $filters = [
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'category' => $request->input('category'),
            'search' => $request->input('search'),
        ];

        // Query base para reclamações do usuário
        $baseQuery = Grievance::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('is_anonymous', false)
                            ->where('contact_email', $user->email);
                    });
            });

        // Estatísticas
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'submitted' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
            'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // Aplicar filtros
        $grievancesQuery = clone $baseQuery;

        if (!empty($filters['status'])) {
            $grievancesQuery->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $grievancesQuery->where('priority', $filters['priority']);
        }

        if (!empty($filters['category'])) {
            $grievancesQuery->where('category', 'like', '%' . $filters['category'] . '%');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $grievancesQuery->where(function ($query) use ($search) {
                $query->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Buscar reclamações com relacionamentos
        $grievances = $grievancesQuery
            ->with([
                'assignedUser:id,name,email',
                'attachments' => function ($query) {
                    $query->latest('uploaded_at');
                },
                'updates' => function ($query) {
                    $query->with('user:id,name')
                        ->latest('created_at');
                },
            ])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Grievance $grievance) => $this->transformGrievance($grievance));

        // Buscar notificações recentes não lidas
        $notifications = GrievanceNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('opened_at')
            ->with('grievance:id,reference_number,category,status')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'message' => $notification->message,
                'subject' => $notification->subject,
                'created_at' => $notification->created_at->format(self::DATETIME_FORMAT),
                'grievance' => $notification->grievance ? [
                    'reference_number' => $notification->grievance->reference_number,
                    'category' => $notification->grievance->category,
                    'status' => $notification->grievance->status,
                ] : null,
            ]);

        return Inertia::render('Utente/Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'created_at' => $user->created_at->format(self::DATE_FORMAT),
            ],
            'stats' => $stats,
            'grievances' => $grievances,
            'notifications' => $notifications,
            'filters' => $filters,
        ]);
    }

    /**
     * Get user's grievance history with details.
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $grievances = Grievance::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('is_anonymous', false)
                            ->where('contact_email', $user->email);
                    });
            })
            ->with([
                'assignedUser:id,name',
                'resolvedBy:id,name',
                'attachments',
                'updates.user:id,name',
            ])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (Grievance $grievance) => $this->transformGrievanceDetailed($grievance));

        return response()->json([
            'success' => true,
            'grievances' => $grievances,
        ]);
    }

    /**
     * Get detailed information about a specific grievance.
     */
    public function show(Request $request, Grievance $grievance)
    {
        $user = $request->user();

        // Verificar se o usuário tem permissão para visualizar esta reclamação
        $canView = $grievance->user_id === $user->id ||
            (!$grievance->is_anonymous && $grievance->contact_email === $user->email);

        abort_if(!$canView, 403, 'Não autorizado a visualizar esta reclamação.');

        $grievance->load([
            'user:id,name,email',
            'assignedUser:id,name,email',
            'resolvedBy:id,name,email',
            'attachments' => function ($query) {
                $query->orderBy('uploaded_at', 'desc');
            },
            'updates' => function ($query) {
                $query->with('user:id,name')
                    ->where('is_public', true)
                    ->orderBy('created_at', 'desc');
            },
        ]);

        return response()->json([
            'success' => true,
            'grievance' => $this->transformGrievanceDetailed($grievance),
        ]);
    }

    /**
     * Mark notifications as read.
     */
    public function markNotificationsRead(Request $request)
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:grievance_notifications,id',
        ]);

        GrievanceNotification::whereIn('id', $validated['notification_ids'])
            ->where('user_id', $request->user()->id)
            ->update([
                'opened' => true,
                'opened_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notificações marcadas como lidas.',
        ]);
    }

    /**
     * Get real-time status updates for a grievance.
     */
    public function getStatusUpdates(Request $request, Grievance $grievance)
    {
        $user = $request->user();

        $canView = $grievance->user_id === $user->id ||
            (!$grievance->is_anonymous && $grievance->contact_email === $user->email);

        abort_if(!$canView, 403, 'Não autorizado.');

        $updates = $grievance->updates()
            ->with('user:id,name')
            ->where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($update) => [
                'id' => $update->id,
                'description' => $update->description,
                'comment' => $update->comment,
                'created_at' => $update->created_at->format(self::DATETIME_FORMAT),
                'updated_by' => $update->user ? $update->user->name : 'Sistema',
            ]);

        return response()->json([
            'success' => true,
            'updates' => $updates,
            'current_status' => $grievance->status,
            'status_label' => $this->getStatusLabel($grievance->status),
        ]);
    }

    /**
     * Transform grievance for list view.
     */
    private function transformGrievance(Grievance $grievance): array
    {
        return [
            'id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'title' => $grievance->category . ($grievance->subcategory ? ' - ' . $grievance->subcategory : ''),
            'description' => $grievance->description,
            'category' => $grievance->category,
            'subcategory' => $grievance->subcategory,
            'status' => $grievance->status,
            'status_label' => $this->getStatusLabel($grievance->status),
            'priority' => $grievance->priority,
            'priority_label' => $this->getPriorityLabel($grievance->priority),
            'province' => $grievance->province,
            'district' => $grievance->district,
            'is_anonymous' => $grievance->is_anonymous,
            'created_at' => $grievance->created_at->format(self::DATE_FORMAT),
            'submitted_at' => $grievance->submitted_at?->format(self::DATETIME_FORMAT),
            'resolved_at' => $grievance->resolved_at?->format(self::DATETIME_FORMAT),
            'assigned_to' => $grievance->assignedUser ? [
                'name' => $grievance->assignedUser->name,
            ] : null,
            'attachments_count' => $grievance->attachments->count(),
            'updates_count' => $grievance->updates->count(),
        ];
    }

    /**
     * Transform grievance with detailed information.
     */
    private function transformGrievanceDetailed(Grievance $grievance): array
    {
        $basic = $this->transformGrievance($grievance);

        return array_merge($basic, [
            'contact_name' => $grievance->contact_name,
            'contact_email' => $grievance->contact_email,
            'contact_phone' => $grievance->contact_phone,
            'location_details' => $grievance->location_details,
            'resolution_notes' => $grievance->resolution_notes,
            'updated_at' => $grievance->updated_at->format(self::DATETIME_FORMAT),
            'assigned_at' => $grievance->assigned_at?->format(self::DATETIME_FORMAT),
            'resolved_by' => $grievance->resolvedBy ? [
                'name' => $grievance->resolvedBy->name,
            ] : null,
            'attachments' => $grievance->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'name' => $attachment->original_filename,
                'size' => $attachment->size,
                'mime_type' => $attachment->mime_type,
                'uploaded_at' => $attachment->uploaded_at->format(self::DATETIME_FORMAT),
                'download_url' => route('attachments.download', $attachment->id),
            ])->toArray(),
            'updates' => $grievance->updates->map(fn ($update) => [
                'id' => $update->id,
                'description' => $update->description,
                'comment' => $update->comment,
                'is_public' => $update->is_public,
                'created_at' => $update->created_at->format(self::DATETIME_FORMAT),
                'updated_by' => $update->user ? $update->user->name : 'Sistema',
            ])->toArray(),
        ]);
    }

    /**
     * Get human-readable status label.
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'submitted' => 'Submetida',
            'under_review' => 'Em Análise',
            'assigned' => 'Atribuída',
            'in_progress' => 'Em Progresso',
            'pending_approval' => 'Aguardando Aprovação',
            'resolved' => 'Resolvida',
            'closed' => 'Fechada',
            'rejected' => 'Rejeitada',
            default => ucfirst($status),
        };
    }

    /**
     * Get human-readable priority label.
     */
    private function getPriorityLabel(string $priority): string
    {
        return match ($priority) {
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            default => ucfirst($priority),
        };
    }
}
