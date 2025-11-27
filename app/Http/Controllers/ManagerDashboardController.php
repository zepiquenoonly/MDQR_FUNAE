<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManagerDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        
        abort_if(!$user || !$user->hasRole('Gestor'), 403);

        $status = $request->input('status');
        $priority = $request->input('priority');
        $category = $request->input('category');

        $filters = [
            'status' => $status !== null && $status !== '' ? $status : null,
            'priority' => $priority !== null && $priority !== '' ? $priority : null,
            'category' => $category !== null && $category !== '' ? $category : null,
        ];

        // Query base para reclamações - SEMPRE retornar dados
        $complaintsQuery = Grievance::query()
            ->with(['user:id,name,email', 'assignedUser:id,name', 'attachments'])
            ->latest('submitted_at');

        // Aplicar filtros
        if ($filters['status']) {
            $complaintsQuery->where('status', $filters['status']);
        }
        if ($filters['priority']) {
            $complaintsQuery->where('priority', $filters['priority']);
        }
        if ($filters['category']) {
            $complaintsQuery->where('category', $filters['category']);
        }

        // Paginação para a lista principal - garantir que sempre retorna pelo menos array vazio
        $complaints = $complaintsQuery->paginate(10)->through(function ($grievance) {
            return [
                'id' => $grievance->id,
                'title' => $grievance->description,
                'description' => $grievance->description,
                'type' => 'complaint',
                'priority' => $grievance->priority,
                'status' => $grievance->status,
                'category' => $grievance->category,
                'created_at' => $grievance->created_at,
                'submitted_at' => $grievance->submitted_at,
                'reference_number' => $grievance->reference_number,
                'province' => $grievance->province,
                'district' => $grievance->district,
                'user' => $grievance->user ? [
                    'name' => $grievance->user->name,
                ] : null,
                'technician' => $grievance->assignedUser ? [
                    'name' => $grievance->assignedUser->name,
                ] : null,
                'attachments' => $grievance->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'name' => $attachment->original_filename,
                        'size' => $attachment->size,
                    ];
                })->toArray(),
            ];
        });

        // TODAS as reclamações (sem paginação) para a visualização completa
        $allComplaintsQuery = Grievance::query()
            ->with(['user:id,name,email', 'assignedUser:id,name'])
            ->latest('submitted_at');

        $allComplaints = $allComplaintsQuery->get()->map(function ($grievance) {
            return [
                'id' => $grievance->id,
                'title' => $grievance->description,
                'description' => $grievance->description,
                'type' => 'complaint',
                'priority' => $grievance->priority,
                'status' => $grievance->status,
                'category' => $grievance->category,
                'created_at' => $grievance->created_at,
                'submitted_at' => $grievance->submitted_at,
                'reference_number' => $grievance->reference_number,
                'province' => $grievance->province,
                'district' => $grievance->district,
                'assigned_user' => $grievance->assignedUser ? [
                    'name' => $grievance->assignedUser->name,
                ] : null,
            ];
        })->toArray();

        // Estatísticas - garantir valores padrão
        $stats = [
            'pending_complaints' => Grievance::whereIn('status', ['submitted', 'under_review', 'assigned'])->count() ?: 0,
            'in_progress' => Grievance::where('status', 'in_progress')->count() ?: 0,
            'high_priority' => Grievance::where('priority', 'high')->count() ?: 0,
            'pending_completion_requests' => Grievance::where('status', 'pending_approval')->count() ?: 0,
        ];

        // Técnicos disponíveis
        $technicians = User::role('Técnico')
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($technician) {
                return [
                    'id' => $technician->id,
                    'name' => $technician->name,
                    'email' => $technician->email,
                ];
            })
            ->toArray();

        return Inertia::render('Manager/Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'created_at' => $user->created_at?->format('d/m/Y'),
            ],
            'complaints' => $complaints,
            'allComplaints' => $allComplaints,
            'stats' => $stats,
            'technicians' => $technicians,
            'filters' => $filters,
            'canEdit' => $user->hasRole('Gestor'),
        ]);
    }
}