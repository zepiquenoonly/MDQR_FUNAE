<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AuthController;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Grievance;
use App\Models\GrievanceUpdate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DirectorComplaintsController extends Controller
{
    /**
     * Verificar se o usuário tem acesso de Director
     */
    private function checkAccess($user)
    {
        if (!$user) {
            abort(403, 'Usuário não autenticado.');
        }
        
        if (!$user->hasAnyRole(['Director', 'director', 'PCA', 'pca'])) {
            abort(403, 'Acesso não autorizado. Apenas usuários com privilégios administrativos podem acessar esta página.');
        }
    }

    /**
     * Listar todas as reclamações com filtros avançados
     */
    public function index(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    // **CRÍTICO: Director vê TODAS as submissões do sistema**
    $query = Grievance::with(['user', 'assignedUser', 'project', 'updates.user.roles'])
        ->latest();

    // Aplicar filtros dinâmicos
    $this->applyFilters($query, $request);
    
    $grievances = $query->get();
    
    // Formatar os dados com informações de intervenção
    $submissions = $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    });

    // **CORREÇÃO CRÍTICA: Obter dados específicos ANTES de passar para a view**
    $managerRequestsData = $this->getManagerRequestsData($user);
    $directorInterventionsData = $this->getDirectorInterventionsData($user);
    
    // Calcular estatísticas específicas para Director
    $allCasesCount = $grievances->count();
    
    // Contadores por tipo
    $suggestionsCount = $grievances->filter(function($item) {
        return $item->type === 'suggestion' || str_contains(strtolower($item->type), 'sugest');
    })->count();
    
    $grievancesCount = $grievances->filter(function($item) {
        return $item->type === 'grievance' || str_contains(strtolower($item->type), 'queixa');
    })->count();
    
    $complaintsCount = $grievances->filter(function($item) {
        return $item->type === 'complaint' || str_contains(strtolower($item->type), 'reclam');
    })->count();
    
    // **USAR OS DADOS REAIS DOS MÉTODOS ESPECÍFICOS**
    $managerRequestsCount = count($managerRequestsData);
    $directorInterventionsCount = count($directorInterventionsData);
    
    // Estatísticas gerais
    $stats = [
        'total' => $allCasesCount,
        'pending' => $grievances->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count(),
        'escalated' => $managerRequestsCount,
        'interventions' => $directorInterventionsCount,
        'resolved' => $grievances->where('status', 'resolved')->count(),
        'suggestions' => $suggestionsCount,
        'grievances' => $grievancesCount,
        'complaints' => $complaintsCount,
    ];

    return Inertia::render('Director/SubmissionsPage', [
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
        ] : null,
        'role' => app(AuthController::class)->getUserNormalizedRole($user),
        
        'submissions' => $submissions,
        'allComplaints' => $submissions,
        
        'recentSubmissions' => $submissions->take(4)->values(),

        // **CRÍTICO: Passar os dados específicos - CORRIGIDO NOME DOS PARÂMETROS**
        'manager_requests' => $managerRequestsData,
        'director_interventions' => $directorInterventionsData,
        
        'stats' => array_merge($stats, $this->getDashboardStats()),
        'filters' => $request->only([
            'search', 'status', 'province', 'priority', 'type',
            'date_from', 'date_to', 'category', 'is_anonymous'
        ]),
        'counts' => [
            'suggestions' => $suggestionsCount,
            'grievances' => $grievancesCount,
            'complaints' => $complaintsCount,
            'director_interventions' => $directorInterventionsCount,
            'manager_requests' => $managerRequestsCount,
            'total' => $allCasesCount
        ],
        'debug_info' => [
            'user_id' => $user->id,
            'role' => $user->getRoleNames()->first(),
            'total_grievances' => $grievances->count(),
            'escalated_count' => $managerRequestsCount,
            'interventions_count' => $directorInterventionsCount,
            'recent_submissions_count' => $submissions->take(4)->count(),
            // Adicionar debug dos dados específicos
            'manager_requests_count' => count($managerRequestsData),
            'director_interventions_count' => count($directorInterventionsData),
        ],
        'filterOptions' => $this->getFilterOptions(),
    ]);
}


private function hasDirectorInterventionByUser(Grievance $grievance, User $user): bool
{
    // Verificar updates do director específico
    $hasDirectorUpdate = $grievance->updates->contains(function($update) use ($user) {
        if (in_array($update->action_type, [
            'director_comment',
            'director_validation_approved',
            'director_validation_rejected',
            'director_validation_needs_revision'
        ]) && $update->user_id === $user->id) {
            return true;
        }
        
        if ($update->user && $update->user->hasRole('Director') && $update->user_id === $user->id) {
            return true;
        }
        
        return false;
    });
    
    if ($hasDirectorUpdate) {
        return true;
    }
    
    // Verificar validação do director específico no metadata
    if ($grievance->metadata && isset($grievance->metadata['director_validation'])) {
        $validation = $grievance->metadata['director_validation'];
        if (isset($validation['validated_by']) && $validation['validated_by'] == $user->id) {
            return true;
        }
    }
    
    return false;
}

    /**
     * Marcar submissão como vista e atualizar status para "Em Análise"
     */
    public function markAsSeen(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);
        
        try {
            $grievance = Grievance::findOrFail($id);
            
            // Verificar se é uma nova submissão (status "submitted")
            if ($grievance->status === 'submitted') {
                DB::beginTransaction();
                
                // Atualizar status para "Em Análise"
                $grievance->update([
                    'status' => 'under_review',
                    'reviewed_at' => now(),
                    'reviewed_by' => $user->id,
                ]);
                
                // Registrar atividade
                GrievanceUpdate::create([
                    'grievance_id' => $grievance->id,
                    'user_id' => $user->id,
                    'action_type' => 'status_changed',
                    'description' => 'Submissão marcada como "Em Análise" pelo Director',
                    'metadata' => [
                        'old_status' => 'submitted',
                        'new_status' => 'under_review',
                        'marked_as_seen' => true,
                        'marked_by' => $user->id,
                        'marked_at' => now()->toISOString(),
                    ],
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                DB::commit();
                
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Submissão atualizada com sucesso',
                'grievance' => $grievance->fresh(['updates'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar submissão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalhes de uma reclamação
     */
    public function show($identifier)
    {
        $user = request()->user();
        $this->checkAccess($user);

        try {
            if (is_numeric($identifier)) {
                $grievance = Grievance::findOrFail($identifier);
            } else {
                $grievance = Grievance::where('reference_number', $identifier)->firstOrFail();
            }
            
            // **SE A SUBMISSÃO É NOVA (status "submitted"), ATUALIZAR PARA "EM ANÁLISE"**
            if ($grievance->status === 'submitted') {
                DB::beginTransaction();
                
                $grievance->update([
                    'status' => 'under_review',
                    'reviewed_at' => now(),
                    'reviewed_by' => $user->id,
                ]);
                
                // Registrar atividade
                GrievanceUpdate::create([
                    'grievance_id' => $grievance->id,
                    'user_id' => $user->id,
                    'action_type' => 'status_changed',
                    'description' => 'Submissão visualizada pelo Director e marcada como "Em Análise"',
                    'metadata' => [
                        'old_status' => 'submitted',
                        'new_status' => 'under_review',
                        'marked_as_seen' => true,
                        'marked_by' => $user->id,
                        'marked_at' => now()->toISOString(),
                    ],
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                DB::commit();
                
            }
            
            // Carregar todas as relações necessárias
            $grievance->load([
                'user',
                'assignedUser',
                'escalatedBy',
                'project',
                'resolvedBy',
                'attachments',
                'updates.user' // Relação já existe
            ]);

            // Formatar comentários DOS UPDATES
            $comments = $grievance->updates
                ->whereIn('action_type', [
                    'comment_added', 
                    'manager_comment', 
                    'technician_comment',
                    'director_comment',
                    'manager_approved',
                    'manager_rejected'
                ])
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($update) use ($user) {
                    // Determinar se o comentário é visível para o usuário atual
                    $isVisible = $this->isCommentVisible($update, $user);
                    
                    if (!$isVisible) {
                        return null;
                    }
                    
                    return [
                        'id' => $update->id,
                        'content' => $update->comment ?? $update->description,
                        'type' => $this->getCommentType($update),
                        'action_type' => $update->action_type,
                        'created_at' => $update->created_at->toISOString(),
                        'user' => $update->user ? [
                            'name' => $update->user->name,
                            'email' => $update->user->email,
                            'role' => $update->user->getRoleNames()->first(),
                        ] : null,
                        'metadata' => $update->metadata ?? [],
                    ];
                })
                ->filter() // Remover nulls
                ->toArray();

            // Buscar dados do escalamento
            $escalationUpdate = $grievance->updates
                ->where('action_type', 'escalated_to_director')
                ->first();

            // Extrair informações do escalamento
            $escalationData = [];
            if ($escalationUpdate) {
                $escalationData = [
                    'escalated_by_name' => $escalationUpdate->user->name ?? null,
                    'escalated_by_id' => $escalationUpdate->user_id,
                    'escalated_at' => $escalationUpdate->created_at->toISOString(),
                    'escalation_reason' => $escalationUpdate->metadata['reason'] ?? null,
                    'escalation_comment' => $escalationUpdate->comment ?? null,
                    'escalation_metadata' => $escalationUpdate->metadata ?? [],
                ];
            }

            // Buscar validação do director
            $validationData = [];
            if ($grievance->metadata && isset($grievance->metadata['director_validation'])) {
                $validationData = $grievance->metadata['director_validation'];
            }

            // Formatar dados para o frontend
            $submissionData = $this->formatGrievanceForShow($grievance);

            // Adicionar dados do escalamento
            $submissionData['escalation_details'] = array_merge([
                'escalated' => $grievance->escalated,
                'escalated_at' => $grievance->escalated_at?->toISOString(),
                'escalation_reason' => $grievance->escalation_reason,
                'escalated_by' => $grievance->escalatedBy ? [
                    'id' => $grievance->escalatedBy->id,
                    'name' => $grievance->escalatedBy->name,
                    'email' => $grievance->escalatedBy->email,
                ] : null,
            ], $escalationData);

            // Adicionar dados de validação
            $submissionData['director_validation'] = $validationData;

            return Inertia::render('Director/Show', [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                ] : null,
                'role' => app(AuthController::class)->getUserNormalizedRole($user),
                
                'submission' => $submissionData,
                'complaint' => $submissionData, // Para compatibilidade
                'comments' => $comments,
                'technicians' => $this->getAvailableTechnicians(),
                'managers' => $this->getAvailableManagers(),
                'projects' => $this->getActiveProjects(),
                
                'timeline_data' => $grievance->updates->sortByDesc('created_at')->values()->toArray(),
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Reclamação não encontrada');
        }
    }


/**
 * Formatar grievance para a lista do Director
 */
private function formatGrievanceForList(Grievance $grievance): array
{
    
    // Carregar updates com relação user
    $grievance->load(['updates.user.roles']);
    
    // Inicializar variáveis
    $hasDirectorIntervention = false;
    $directorUpdates = [];
    $directorCommentsCount = 0;
    $directorInterventions = [];
    $managerRequestDetails = null;
    
    // **VERIFICAR SE É UMA SOLICITAÇÃO DO GESTOR (escalada)**
    $isEscalatedByManager = $grievance->escalated || 
                           ($grievance->metadata && 
                            isset($grievance->metadata['is_escalated_to_director']) && 
                            $grievance->metadata['is_escalated_to_director'] === true);
    
    if ($isEscalatedByManager) {
        
        $hasDirectorIntervention = true;
        
        // Extrair detalhes do escalamento
        $escalationUpdate = $grievance->updates
            ->where('action_type', 'escalated_to_director')
            ->first();
        
        if ($escalationUpdate) {
            $managerRequestDetails = [
                'type' => 'escalation_request',
                'escalated_by' => $escalationUpdate->user ? [
                    'name' => $escalationUpdate->user->name,
                    'role' => $escalationUpdate->user->getRoleNames()->first(),
                    'id' => $escalationUpdate->user->id
                ] : null,
                'escalated_at' => $escalationUpdate->created_at->toISOString(),
                'escalation_reason' => $escalationUpdate->metadata['reason'] ?? $grievance->escalation_reason,
                'escalation_comment' => $escalationUpdate->comment,
                'metadata' => $escalationUpdate->metadata ?? [],
                'update_id' => $escalationUpdate->id
            ];
            
            // Adicionar como intervenção
            $directorInterventions[] = array_merge([
                'type' => 'manager_request'
            ], $managerRequestDetails);
        }
    }
    
    // Analisar updates para outras intervenções do director
    foreach ($grievance->updates as $update) {
        $isDirectorUpdate = false;
        
        // Verificar por action_type do director
        $directorActionTypes = [
            'director_comment',
            'director_validation_approved', 
            'director_validation_rejected',
            'director_validation_needs_revision'
        ];
        
        if (in_array($update->action_type, $directorActionTypes)) {
            $isDirectorUpdate = true;
        }
        
        // Verificar se o usuário é director
        if ($update->user && $update->user->hasRole('Director')) {
            $isDirectorUpdate = true;
        }
        
        // Verificar metadados
        if ($update->metadata && 
            (isset($update->metadata['created_by_director']) && $update->metadata['created_by_director'] === true)) {
            $isDirectorUpdate = true;
        }
        
        if ($isDirectorUpdate) {
            $hasDirectorIntervention = true;
            
            $directorUpdates[] = [
                'id' => $update->id,
                'action_type' => $update->action_type,
                'description' => $update->description,
                'comment' => $update->comment,
                'created_at' => $update->created_at->toISOString(),
                'user' => $update->user ? [
                    'name' => $update->user->name,
                    'role' => $update->user->getRoleNames()->first(),
                ] : null,
                'metadata' => $update->metadata ?? [],
            ];
            
            if (!empty($update->comment)) {
                $directorCommentsCount++;
            }
        }
    }
    
    // Verificar validação do director no metadata
    $directorValidation = null;
    if ($grievance->metadata && isset($grievance->metadata['director_validation'])) {
        $hasDirectorIntervention = true;
        $directorValidation = $grievance->metadata['director_validation'];
        
        $directorInterventions[] = [
            'type' => 'validation',
            'validation_status' => $directorValidation['status'] ?? null,
            'comment' => $directorValidation['comment'] ?? null,
            'validated_by' => $directorValidation['validated_by_name'] ?? null,
            'validated_at' => $directorValidation['validated_at'] ?? null,
        ];
    }
    
    // Formatar dados básicos
    $formatted = [
        'id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'title' => $grievance->description,
        'description' => $grievance->description,
        'type' => $grievance->type,
        'priority' => $grievance->priority,
        'status' => $grievance->status,
        'category' => $grievance->category,
        'created_at' => $grievance->created_at->toISOString(),
        'submitted_at' => $grievance->submitted_at->toISOString(),
        'province' => $grievance->province,
        'district' => $grievance->district,
        
        // Informações de escalamento
        'escalated' => $grievance->escalated,
        'escalation_reason' => $grievance->escalation_reason,
        'escalated_at' => $grievance->escalated_at?->toISOString(),
        'escalated_by' => $grievance->escalated_by,
        'is_escalated_to_director' => $isEscalatedByManager,
        
        // Informações do gestor que escalou (se for uma solicitação do gestor)
        'manager_request' => $managerRequestDetails,
        
        // Informações de intervenção do director
        'has_director_intervention' => $hasDirectorIntervention,
        'director_updates' => $directorUpdates,
        'director_comments_count' => $directorCommentsCount,
        'director_interventions' => $directorInterventions,
        'director_validation' => $directorValidation,
        
        'metadata' => $grievance->metadata,
        'assigned_to' => $grievance->assigned_to,
    ];
    
    // Informações do usuário
    if ($grievance->user) {
        $formatted['user'] = [
            'name' => $grievance->user->name,
            'email' => $grievance->user->email,
        ];
    }
    
    // Informações do técnico atribuído
    if ($grievance->assignedUser) {
        $formatted['technician'] = [
            'id' => $grievance->assignedUser->id,
            'name' => $grievance->assignedUser->name,
            'email' => $grievance->assignedUser->email,
        ];
    }
    
    return $formatted;
}


private function getDirectorInterventionsData(User $user): array
{
    // Buscar reclamações onde o director atual fez intervenções
    $query = Grievance::with([
            'user', 
            'assignedUser',
            'escalatedBy',
            'updates.user.roles'
        ])
        ->where(function($q) use ($user) {
            $q->whereHas('updates', function($q2) use ($user) {
                $q2->where(function($q3) use ($user) {
                    $q3->whereIn('action_type', [
                        'director_comment',
                        'director_validation_approved',
                        'director_validation_rejected',
                        'director_validation_needs_revision'
                    ])
                    ->where('user_id', $user->id);
                });
            })
            ->orWhere(function($q2) use ($user) {
                $q2->whereNotNull('metadata')
                    ->whereJsonLength('metadata->director_validation', '>', 0)
                    ->whereJsonContains('metadata->director_validation->validated_by', $user->id);
            });
        })
        ->latest();
    
    $grievances = $query->get();
    
    \Log::info('Director - My Interventions Data', [
        'count' => $grievances->count(),
        'user_id' => $user->id,
        'grievance_ids' => $grievances->pluck('id')->toArray()
    ]);
    
    return $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    })->values()->toArray();
}
/**
 * Verificar se tem intervenção do director
 */
private function hasDirectorIntervention(Grievance $grievance): bool
{
    // Verificar se foi escalado para director
    if ($grievance->escalated || 
        ($grievance->metadata && isset($grievance->metadata['is_escalated_to_director']) && 
         $grievance->metadata['is_escalated_to_director'] === true)) {
        return true;
    }
    
    // Verificar updates do director
    $hasDirectorUpdate = $grievance->updates->contains(function($update) {
        if (in_array($update->action_type, [
            'director_comment',
            'director_validation_approved',
            'director_validation_rejected',
            'director_validation_needs_revision'
        ])) {
            return true;
        }
        
        if ($update->user && $update->user->hasRole('Director')) {
            return true;
        }
        
        if ($update->metadata && 
            (isset($update->metadata['created_by_director']) && $update->metadata['created_by_director'] === true)) {
            return true;
        }
        
        return false;
    });
    
    if ($hasDirectorUpdate) {
        return true;
    }
    
    // Verificar validação do director no metadata
    if ($grievance->metadata && isset($grievance->metadata['director_validation'])) {
        return true;
    }
    
    return false;
}

public function getRecentSubmissions(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    // Obter as 4 submissões mais recentes
    $query = Grievance::with(['user', 'assignedUser', 'updates.user.roles'])
        ->latest()
        ->limit(4);
    
    $grievances = $query->get();
    
    $formattedGrievances = $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    });
    
    return response()->json([
        'success' => true,
        'data' => $formattedGrievances,
        'count' => $formattedGrievances->count()
    ]);
}

/**
 * Obter todas as submissões para API
 */
public function getAllSubmissions(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    // Director vê TODAS as submissões
    $query = Grievance::with(['user', 'assignedUser', 'updates.user.roles'])
        ->latest();
    
    // Aplicar filtros se fornecidos
    $this->applyFilters($query, $request);
    
    $grievances = $query->get();
    
    $formattedGrievances = $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    });
    
    return response()->json([
        'success' => true,
        'data' => $formattedGrievances,
        'count' => $formattedGrievances->count(),
        'filters' => $request->all()
    ]);
}


public function getInterventions(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    $query = Grievance::with(['updates.user.roles'])
        ->where(function($q) {
            $q->whereHas('updates', function($q2) {
                $q2->where(function($q3) {
                    $q3->whereIn('action_type', [
                        'director_comment',
                        'director_validation_approved',
                        'director_validation_rejected',
                        'director_validation_needs_revision'
                    ])
                    ->orWhereHas('user', function($q4) {
                        $q4->role('Director');
                    });
                });
            })
            ->orWhere(function($q2) {
                $q2->whereNotNull('metadata')
                    ->whereJsonLength('metadata->director_validation', '>', 0);
            });
        })
        ->latest();
    
    $grievances = $query->get();
    
    $formattedGrievances = $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    });
    
    return response()->json([
        'success' => true,
        'data' => $formattedGrievances,
        'count' => $formattedGrievances->count()
    ]);
}

private function getManagerRequestsData(User $user): array
{
    // Buscar reclamações escaladas para o director
    $query = Grievance::with([
            'user', 
            'assignedUser',
            'escalatedBy',
            'updates.user.roles'
        ])
        ->where(function($q) {
            $q->where('escalated', true)
              ->orWhereJsonContains('metadata->is_escalated_to_director', true);
        })
        ->latest();
    
    $grievances = $query->get();
    
    \Log::info('Director - Manager Requests Data', [
        'count' => $grievances->count(),
        'user_id' => $user->id,
        'grievance_ids' => $grievances->pluck('id')->toArray()
    ]);
    
    return $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    })->values()->toArray();
}


private function getCommentType($update): string
{
    $actionType = $update->action_type;
    $metadata = $update->metadata ?? [];
    
    // Verificar se é um comentário do director
    if ($actionType === 'director_comment' || $actionType === 'director_validation') {
        if (isset($metadata['is_public']) && $metadata['is_public'] === true) {
            return 'public';
        }
        if (isset($metadata['comment_type']) && $metadata['comment_type'] === 'director_only') {
            return 'director_only';
        }
        return 'internal';
    }
    
    // Verificar se é um comentário público
    if ($update->is_public || (isset($metadata['is_public']) && $metadata['is_public'] === true)) {
        return 'public';
    }
    
    // Comentários internos por padrão
    return 'internal';
}


private function isCommentVisible($update, $user): bool
{
    $commentType = $this->getCommentType($update);
    
    // Se for público, todos podem ver
    if ($commentType === 'public') {
        return true;
    }
    
    // Se for apenas para director, apenas director pode ver
    if ($commentType === 'director_only') {
        return $user->hasRole('Director');
    }
    
    // Se for interno, gestor, técnico e director podem ver
    if ($commentType === 'internal') {
        return $user->hasAnyRole(['Director', 'Gestor', 'Técnico']);
    }
    
    return false;
}



private function formatForVueShow(Grievance $grievance): array
{
    return [
        'id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'subject' => $grievance->description, // Use description como subject
        'description' => $grievance->description,
        'type' => $grievance->type,
        'priority' => $grievance->priority,
        'status' => $grievance->status,
        'category' => $grievance->category,
        'created_at' => $grievance->created_at->toIso8601String(),
        'updated_at' => $grievance->updated_at->toIso8601String(),
        'submitter_name' => $grievance->is_anonymous 
            ? ($grievance->contact_name ?? 'Anónimo') 
            : ($grievance->user->name ?? 'N/A'),
        'submitter_email' => $grievance->is_anonymous
            ? ($grievance->contact_email ?? 'N/A')
            : ($grievance->user->email ?? 'N/A'),
        'assigned_to' => $grievance->assignedUser ? [
            'id' => $grievance->assignedUser->id,
            'name' => $grievance->assignedUser->name,
            'email' => $grievance->assignedUser->email,
        ] : null,
        'project' => $grievance->project ? [
            'id' => $grievance->project->id,
            'name' => $grievance->project->name,
            'code' => 'PROJ-' . str_pad($grievance->project->id, 4, '0', STR_PAD_LEFT),
        ] : null,
        'department' => $grievance->project ? [
            'name' => $grievance->project->category ?? 'N/A',
        ] : null,
        'province' => $grievance->province,
        'district' => $grievance->district,
        'location_details' => $grievance->location_details,
        'is_anonymous' => $grievance->is_anonymous,
        'contact_phone' => $grievance->contact_phone,
    ];
}


private function formatComments(Grievance $grievance): array
{
    // Filtrar updates que são comentários
    return $grievance->updates
        ->filter(function ($update) {
            return $update->action_type === 'comment_added' 
                || $update->action_type === 'manager_comment'
                || $update->action_type === 'technician_comment'
                || !empty($update->comment);
        })
        ->map(function ($update) {
            return [
                'id' => $update->id,
                'content' => $update->comment ?? $update->description,
                'is_internal' => $update->is_public === false, // Se não é público, é interno
                'created_at' => $update->created_at->toIso8601String(),
                'user' => $update->user ? [
                    'id' => $update->user->id,
                    'name' => $update->user->name,
                    'email' => $update->user->email,
                ] : null,
            ];
        })
        ->values()
        ->toArray();
}

/**
 * Formatar atividades (updates não comentários)
 */
private function formatActivities(Grievance $grievance): array
{
    // Filtrar updates que não são comentários
    return $grievance->updates
        ->filter(function ($update) {
            return !in_array($update->action_type, [
                'comment_added', 
                'manager_comment', 
                'technician_comment'
            ]) && empty($update->comment);
        })
        ->map(function ($update) {
            return [
                'id' => $update->id,
                'type' => $update->action_type,
                'description' => $this->formatActivityDescription($update),
                'created_at' => $update->created_at->toIso8601String(),
                'user' => $update->user ? [
                    'name' => $update->user->name,
                ] : null,
            ];
        })
        ->values()
        ->toArray();
}

/**
 * Formatar descrição da atividade
 */
private function formatActivityDescription(GrievanceUpdate $update): string
{
    switch ($update->action_type) {
        case 'status_changed':
            return "Estado alterado de '{$this->getStatusLabel($update->old_value)}' para '{$this->getStatusLabel($update->new_value)}'";
        
        case 'assigned':
            return "Caso atribuído a {$update->user->name}";
        
        case 'priority_changed':
            return "Prioridade alterada de '{$update->old_value}' para '{$update->new_value}'";
        
        case 'created':
            return "Caso criado";
        
        default:
            return $update->description ?? ucfirst(str_replace('_', ' ', $update->action_type));
    }
}

private function getStatusLabel(?string $status): string
{
    if (!$status) return 'N/A';
    
    $labels = [
        'submitted' => 'Submetido',
        'pending' => 'Pendente',
        'in_progress' => 'Em Análise',
        'resolved' => 'Resolvido',
        'closed' => 'Fechado',
        'rejected' => 'Rejeitado',
        'escalated' => 'Escalado',
    ];
    
    return $labels[$status] ?? ucfirst($status);
}

    /**
     * Actualizar status de uma reclamação
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'status' => 'required|in:submitted,under_review,assigned,in_progress,pending_approval,resolved,rejected',
            'resolution_notes' => 'nullable|string|required_if:status,resolved',
            'notes' => 'nullable|string',
        ]);

        $grievance = Grievance::findOrFail($id);

        DB::beginTransaction();
        try {
            $updates = ['status' => $validated['status']];

            // Processar status específicos
            switch ($validated['status']) {
                case 'resolved':
                    $updates['resolved_at'] = now();
                    $updates['resolved_by'] = $user->id;
                    $updates['resolution_notes'] = $validated['resolution_notes'] ?? null;
                    break;
                    
                case 'assigned':
                    $updates['assigned_at'] = now();
                    break;
                    
                case 'rejected':
                    $updates['rejected_at'] = now();
                    $updates['rejected_by'] = $user->id;
                    break;
            }

            $grievance->update($updates);

            // Registrar atualização de status
            $this->createStatusUpdate($grievance, $validated['status'], $validated['notes'] ?? null, $user->id);

            // Se resolvido, atualizar workload do técnico
            if ($validated['status'] === 'resolved' && $grievance->assigned_to) {
                $this->updateTechnicianWorkload($grievance, 'decrement');
            }

            DB::commit();

            return back()->with('success', 'Status atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao atualizar status: ' . $e->getMessage()]);
        }
    }

    /**
     * Atribuir reclamação a um técnico
     */
    public function assignToTechnician(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $grievance = Grievance::findOrFail($id);
        $technician = User::findOrFail($validated['technician_id']);

        // Verificar se o técnico está ativo
        if (!$technician->is_available) {
            return back()->withErrors(['error' => 'O técnico selecionado não está ativo.']);
        }

        DB::beginTransaction();
        try {
            // Se já havia um técnico atribuído, atualizar seu workload
            if ($grievance->assigned_to) {
                $this->updateTechnicianWorkload($grievance, 'decrement');
            }

            // Actualizar a reclamação
            $updates = [
                'assigned_to' => $validated['technician_id'],
                'status' => 'assigned',
                'assigned_at' => now(),
            ];

            if ($request->filled('priority')) {
                $updates['priority'] = $validated['priority'];
            }

            $grievance->update($updates);

            // Actualizar workload do novo técnico
            $this->updateTechnicianWorkload($grievance, 'increment', $technician);

            // Registrar a atribuição
            $this->createStatusUpdate(
                $grievance, 
                'assigned', 
                'Atribuído a ' . $technician->name . '. ' . ($validated['notes'] ?? ''),
                $user->id
            );

            // Adicionar comentário interno
            $grievance->comments()->create([
                'content' => 'Caso atribuído ao técnico ' . $technician->name . ' pelo Director.',
                'is_internal' => true,
                'user_id' => $user->id,
            ]);

            DB::commit();

            return back()->with('success', 'Caso atribuído com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao atribuir caso: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar prioridade de uma reclamação
     */
    public function updatePriority(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
            'notes' => 'nullable|string',
        ]);

        $grievance = Grievance::findOrFail($id);

        $oldPriority = $grievance->priority;
        $grievance->update(['priority' => $validated['priority']]);

        // Registrar a alteração
        $this->createStatusUpdate(
            $grievance, 
            $grievance->status, 
            'Prioridade alterada de ' . $oldPriority . ' para ' . $validated['priority'] . '. ' . ($validated['notes'] ?? ''),
            $user->id
        );

        return back()->with('success', 'Prioridade atualizada com sucesso!');
    }

    /**
     * Actualizar informações básicas da reclamação
     */
    public function updateInfo(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'category' => 'nullable|string',
            'subcategory' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'type' => 'nullable|in:grievance,complaint,suggestion',
            'notes' => 'nullable|string',
        ]);

        $grievance = Grievance::findOrFail($id);
        $oldValues = $grievance->only(array_keys($validated));

        $grievance->update(array_filter($validated, fn($value) => !is_null($value)));

        // Registrar as alterações
        $changes = [];
        foreach ($validated as $field => $newValue) {
            if ($newValue && isset($oldValues[$field]) && $oldValues[$field] != $newValue) {
                $changes[] = ucfirst($field) . ': ' . ($oldValues[$field] ?? 'vazio') . ' → ' . $newValue;
            }
        }

        if (!empty($changes)) {
            $this->createStatusUpdate(
                $grievance, 
                $grievance->status, 
                'Informações atualizadas: ' . implode('; ', $changes) . '. ' . ($validated['notes'] ?? ''),
                $user->id
            );
        }

        return back()->with('success', 'Informações atualizadas com sucesso!');
    }

    /**
     * Reatribuir reclamação para outro gestor/técnico
     */
    public function reassign(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'new_assignee_id' => 'required|exists:users,id',
            'assignee_type' => 'required|in:technician,manager',
            'notes' => 'nullable|string',
        ]);

        $grievance = Grievance::findOrFail($id);
        $newAssignee = User::findOrFail($validated['new_assignee_id']);

        // Verificar se o novo responsável está ativo
        if (!$newAssignee->is_available) {
            return back()->withErrors(['error' => 'O responsável selecionado não está ativo.']);
        }

        // Verificar se tem a role apropriada
        $requiredRole = $validated['assignee_type'] === 'technician' ? 'Técnico' : 'gestor';
        if (!$newAssignee->hasRole($requiredRole)) {
            return back()->withErrors(['error' => 'O usuário selecionado não tem a role apropriada.']);
        }

        DB::beginTransaction();
        try {
            $oldAssignee = $grievance->assignedUser;
            $oldAssigneeName = $oldAssignee ? $oldAssignee->name : 'Ninguém';

            // Se havia um técnico atribuído, atualizar seu workload
            if ($grievance->assigned_to && $oldAssignee && $oldAssignee->hasRole('Técnico')) {
                $this->updateTechnicianWorkload($grievance, 'decrement');
            }

            // Actualizar a reclamação
            $grievance->update([
                'assigned_to' => $validated['new_assignee_id'],
                'assigned_at' => now(),
            ]);

            // Se o novo responsável é um técnico, atualizar seu workload
            if ($validated['assignee_type'] === 'technician') {
                $this->updateTechnicianWorkload($grievance, 'increment', $newAssignee);
            }

            // Registrar a reatribuição
            $this->createStatusUpdate(
                $grievance, 
                $grievance->status, 
                'Reatribuído de ' . $oldAssigneeName . ' para ' . $newAssignee->name . '. ' . ($validated['notes'] ?? ''),
                $user->id
            );

            DB::commit();

            return back()->with('success', 'Caso reatribuído com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao reatribuir caso: ' . $e->getMessage()]);
        }
    }

    /**
     * Escalar reclamação (marcar como crítico/urgente)
     */
    public function escalate(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'reason' => 'required|string',
            'target_priority' => 'nullable|in:high,urgent',
        ]);

        $grievance = Grievance::findOrFail($id);

        $oldPriority = $grievance->priority;
        $newPriority = $validated['target_priority'] ?? 'urgent';

        $grievance->update([
            'priority' => $newPriority,
            'metadata' => array_merge(
                $grievance->metadata ?? [],
                [
                    'escalated_at' => now()->toDateTimeString(),
                    'escalated_by' => $user->id,
                    'escalation_reason' => $validated['reason'],
                    'previous_priority' => $oldPriority,
                ]
            )
        ]);

        // Registrar a escalação
        $this->createStatusUpdate(
            $grievance, 
            $grievance->status, 
            'Caso escalado. Prioridade alterada de ' . $oldPriority . ' para ' . $newPriority . '. Motivo: ' . $validated['reason'],
            $user->id
        );

        return back()->with('success', 'Caso escalado com sucesso!');
    }

    /**
     * Marcar reclamação como resolvida
     */
    public function markAsResolved(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'resolution_notes' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $grievance = Grievance::findOrFail($id);

        DB::beginTransaction();
        try {
            $grievance->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $user->id,
                'resolution_notes' => $validated['resolution_notes'],
            ]);

            // Actualizar workload do técnico se houver um atribuído
            if ($grievance->assigned_to) {
                $this->updateTechnicianWorkload($grievance, 'decrement');
            }

            // Registrar a resolução
            $this->createStatusUpdate(
                $grievance, 
                'resolved', 
                ($validated['notes'] ?? 'Caso resolvido pelo Director') . '. Notas: ' . $validated['resolution_notes'],
                $user->id
            );

            DB::commit();

            return back()->with('success', 'Caso marcado como resolvido com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao marcar caso como resolvido: ' . $e->getMessage()]);
        }
    }

    /**
     * Rejeitar reclamação
     */
    public function rejectSubmission(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $grievance = Grievance::findOrFail($id);

        DB::beginTransaction();
        try {
            $grievance->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $user->id,
                'resolution_notes' => $validated['reason'],
            ]);

            // Actualizar workload do técnico se houver um atribuído
            if ($grievance->assigned_to) {
                $this->updateTechnicianWorkload($grievance, 'decrement');
            }

            // Registrar a rejeição
            $this->createStatusUpdate(
                $grievance, 
                'rejected', 
                'Caso rejeitado. Motivo: ' . $validated['reason'] . '. ' . ($validated['notes'] ?? ''),
                $user->id
            );

            DB::commit();

            return back()->with('success', 'Caso rejeitado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao rejeitar caso: ' . $e->getMessage()]);
        }
    }


public function exportStatistics(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    // Validar parâmetros
    $validated = $request->validate([
        'period' => ['required', 'string', 'in:today,week,month,3months,6months,year,12months'],
        'format' => ['required', 'string', 'in:xlsx,csv,pdf'],
    ]);
    
    $period = $validated['period'];
    $format = $validated['format'];
    
    if ($format === 'pdf') {
        return $this->exportToPdf($request);
    }
    
    // TODO: Implementar exportação para Excel/CSV
    return response()->json([
        'success' => false,
        'message' => 'Formato não implementado ainda'
    ], 400);
}

    public function exportToPdf(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    try {
        \Log::info('=== EXPORTAÇÃO DE RELATÓRIO DO DIRECTOR ===');
        \Log::info('Director: ' . $user->name . ' (ID: ' . $user->id . ')');
        
        // **1. OBTER DADOS DAS RECLAMAÇÕES**
        $query = Grievance::with(['user', 'assignedUser', 'project', 'updates'])
            ->latest('submitted_at');
        
        // Aplicar filtros
        $this->applyReportFilters($query, $request);
        
        $grievances = $query->get();
        $totalGrievances = $grievances->count();
        
        \Log::info('Total de reclamações: ' . $totalGrievances);
        
        // **2. CALCULAR ESTATÍSTICAS COMPLETAS**
        $stats = $this->calculateCompleteStatistics($grievances, $user);
        
        // **3. DADOS PARA TIMELINE/RESOLUÇÃO**
        $resolutionStats = $this->calculateResolutionStats($grievances);
        
        // **4. DISTRIBUIÇÕES**
        $distributions = $this->calculateDistributions($grievances);
        
        // **5. PREPARAR DADOS PARA PDF**
        $data = [
            'title' => 'Relatório Director - Todas as Submissões',
            'subtitle' => 'Director: ' . $user->name . ' - ' . now()->format('F Y'),
            'user' => $user,
            'user_name' => $user->name,
            'export_date' => now()->format('d/m/Y H:i'),
            'period' => $this->getReportPeriod($request),
            
            // Seção de estatísticas
            'statistics' => $stats,
            'resolution_stats' => $resolutionStats,
            'distributions' => $distributions,
            
            // Lista de reclamações
            'total_grievances' => $totalGrievances,
            'grievances' => $grievances->map(function ($grievance) {
                return [
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'type' => $this->getTypeLabelForExport($grievance->type),
                    'priority' => $this->getPriorityLabel($grievance->priority),
                    'status' => $this->getStatusText($grievance->status),
                    'category' => $grievance->category ?? 'N/A',
                    'created_at' => $grievance->created_at->format('d/m/Y H:i'),
                    'submitted_at' => $grievance->submitted_at ? $grievance->submitted_at->format('d/m/Y H:i') : 'N/A',
                    'resolved_at' => $grievance->resolved_at ? $grievance->resolved_at->format('d/m/Y H:i') : 'N/A',
                    'user_name' => $grievance->user ? $grievance->user->name : 'Anônimo',
                    'technician' => $grievance->assignedUser ? $grievance->assignedUser->name : 'Não atribuído',
                    'project' => $grievance->project ? $grievance->project->name : 'N/A',
                    'escalated' => $grievance->escalated ? 'Sim' : 'Não',
                    'escalation_reason' => $grievance->escalation_reason ?? 'N/A',
                    'updates_count' => $grievance->updates->count(),
                    'has_attachments' => $grievance->attachments->count() > 0,
                    'has_director_intervention' => $this->hasDirectorIntervention($grievance),
                ];
            })->toArray(),
            
            // Filtros aplicados
            'filters_applied' => $this->getAppliedFilters($request),
            
            // Flags específicas para director
            'is_director_report' => true,
            'role' => 'Director',
        ];
        
        \Log::info('Relatório do Director preparado com ' . $totalGrievances . ' registros');
        
        // **6. GERAR PDF**
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.director-submissions-pdf', $data);
        $pdf->setPaper('A4', 'landscape'); // Modo paisagem para mais colunas
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
            'dpi' => 150,
        ]);
        
        $filename = 'relatorio-director-' . now()->format('Y-m-d-H-i') . '.pdf';
        
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao exportar relatório do Director: ' . $e->getMessage());
        return $this->createErrorPdf('Erro ao gerar relatório: ' . $e->getMessage(), $user);
    }
}


private function applyReportFilters($query, Request $request): void
{
    // Filtro por período
    if ($request->filled('period')) {
        $period = $request->input('period');
        $dateRange = $this->getDateRangeForPeriod($period);
        
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }
    }
    
    // Filtros regulares
    if ($request->filled('type') && $request->input('type') !== '') {
        $query->where('type', $request->input('type'));
    }
    
    if ($request->filled('status') && $request->input('status') !== '') {
        $query->where('status', $request->input('status'));
    }
    
    if ($request->filled('priority') && $request->input('priority') !== '') {
        $query->where('priority', $request->input('priority'));
    }
    
    if ($request->filled('category') && $request->input('category') !== '') {
        $query->where('category', $request->input('category'));
    }
    
    if ($request->filled('province') && $request->input('province') !== '') {
        $query->where('province', $request->input('province'));
    }
    
    // Filtro por tab específica
    if ($request->filled('tab') && $request->input('tab') !== 'all') {
        $tab = $request->input('tab');
        
        switch ($tab) {
            case 'suggestions':
                $query->where(function($q) {
                    $q->where('type', 'suggestion')
                      ->orWhere('type', 'like', '%sugest%');
                });
                break;
                
            case 'grievances':
                $query->where(function($q) {
                    $q->where('type', 'grievance')
                      ->orWhere('type', 'like', '%queixa%');
                });
                break;
                
            case 'complaints':
                $query->where(function($q) {
                    $q->where('type', 'complaint')
                      ->orWhere('type', 'like', '%reclam%');
                });
                break;
                
            case 'resolved':
                $query->where('status', 'resolved');
                break;
                
            case 'rejected':
                $query->where('status', 'rejected');
                break;
                
            case 'manager_requests':
                $query->where('escalated', true);
                break;
                
            case 'director_interventions':
                $query->where(function($q) {
                    $q->whereHas('updates', function($q2) {
                        $q2->whereIn('action_type', [
                            'director_comment',
                            'director_validation_approved',
                            'director_validation_rejected',
                            'director_validation_needs_revision'
                        ]);
                    })
                    ->orWhere(function($q2) {
                        $q2->whereNotNull('metadata')
                            ->whereJsonLength('metadata->director_validation', '>', 0);
                    });
                });
                break;
        }
    }
}


private function calculateCompleteStatistics($grievances, $user): array
{
    $total = $grievances->count();
    
    // Contagem por status
    $byStatus = $grievances->groupBy('status')->map->count();
    
    // Contagem por tipo
    $byType = $grievances->groupBy('type')->map->count();
    
    // Contagem por prioridade
    $byPriority = $grievances->groupBy('priority')->map->count();
    
    // Reclamações escaladas
    $escalatedCount = $grievances->where('escalated', true)->count();
    
    // Reclamações com intervenção do director
    $withDirectorIntervention = $grievances->filter(function($g) {
        return $this->hasDirectorIntervention($g);
    })->count();
    
    // Taxa de resolução
    $resolvedCount = $grievances->whereIn('status', ['resolved', 'closed'])->count();
    $resolutionRate = $total > 0 ? round(($resolvedCount / $total) * 100, 1) : 0;
    
    // Reclamações novas (últimos 7 dias)
    $newLast7Days = $grievances->filter(function($g) {
        return $g->created_at->greaterThan(now()->subDays(7));
    })->count();
    
    // Reclamações pendentes
    $pendingCount = $grievances->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count();
    
    // Reclamações atribuídas ao director
    $assignedToDirector = $grievances->where('assigned_to', $user->id)->count();
    
    return [
        'total' => $total,
        'by_status' => $byStatus->toArray(),
        'by_type' => $byType->toArray(),
        'by_priority' => $byPriority->toArray(),
        'escalated_count' => $escalatedCount,
        'with_director_intervention' => $withDirectorIntervention,
        'resolved_count' => $resolvedCount,
        'resolution_rate' => $resolutionRate,
        'new_last_7_days' => $newLast7Days,
        'pending_count' => $pendingCount,
        'assigned_to_director' => $assignedToDirector,
        'average_updates_per_grievance' => $total > 0 ? 
            round($grievances->sum(function($g) { return $g->updates->count(); }) / $total, 1) : 0,
    ];
}

/**
 * Calcular estatísticas de resolução
 */
private function calculateResolutionStats($grievances): array
{
    $resolvedGrievances = $grievances->whereIn('status', ['resolved', 'closed'])
        ->whereNotNull('resolved_at')
        ->whereNotNull('submitted_at');
    
    $totalResolved = $resolvedGrievances->count();
    
    if ($totalResolved === 0) {
        return [
            'average_resolution_time_hours' => 0,
            'average_resolution_time_days' => 0,
            'fastest_resolution_hours' => 0,
            'slowest_resolution_hours' => 0,
            'total_resolved' => 0,
            'resolution_time_distribution' => [],
        ];
    }
    
    $resolutionTimes = [];
    $totalHours = 0;
    $fastest = PHP_INT_MAX;
    $slowest = 0;
    
    foreach ($resolvedGrievances as $grievance) {
        $hours = $grievance->submitted_at->diffInHours($grievance->resolved_at);
        $resolutionTimes[] = $hours;
        $totalHours += $hours;
        
        if ($hours < $fastest) $fastest = $hours;
        if ($hours > $slowest) $slowest = $hours;
    }
    
    // Distribuição de tempos
    $timeDistribution = [
        'menos_24h' => count(array_filter($resolutionTimes, fn($h) => $h <= 24)),
        '1_3_dias' => count(array_filter($resolutionTimes, fn($h) => $h > 24 && $h <= 72)),
        '3_7_dias' => count(array_filter($resolutionTimes, fn($h) => $h > 72 && $h <= 168)),
        'mais_7_dias' => count(array_filter($resolutionTimes, fn($h) => $h > 168)),
    ];
    
    return [
        'average_resolution_time_hours' => round($totalHours / $totalResolved, 1),
        'average_resolution_time_days' => round(($totalHours / $totalResolved) / 24, 1),
        'fastest_resolution_hours' => $fastest,
        'slowest_resolution_hours' => $slowest,
        'total_resolved' => $totalResolved,
        'resolution_time_distribution' => $timeDistribution,
    ];
}

/**
 * Calcular distribuições
 */
private function calculateDistributions($grievances): array
{
    // Distribuição por mês (últimos 6 meses)
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        
        $count = $grievances->filter(function($g) use ($monthStart, $monthEnd) {
            return $g->created_at->between($monthStart, $monthEnd);
        })->count();
        
        $months[] = [
            'month' => $month->format('M/Y'),
            'count' => $count,
        ];
    }
    
    // Distribuição por gestor
    $byManager = $grievances->groupBy('assigned_to')->map(function($items, $managerId) {
        $manager = User::find($managerId);
        return [
            'manager_id' => $managerId,
            'manager_name' => $manager ? $manager->name : 'Não atribuído',
            'count' => $items->count(),
            'resolved' => $items->whereIn('status', ['resolved', 'closed'])->count(),
            'pending' => $items->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count(),
            'escalated' => $items->where('escalated', true)->count(),
        ];
    })->sortByDesc('count')->take(10)->values();
    
    // Distribuição por projeto
    $byProject = $grievances->groupBy('project_id')->map(function($items, $projectId) {
        return [
            'project_id' => $projectId,
            'count' => $items->count(),
            'project_name' => $items->first()->project->name ?? 'N/A',
        ];
    })->sortByDesc('count')->take(5)->values();
    
    // Distribuição por província
    $byProvince = $grievances->whereNotNull('province')
        ->groupBy('province')
        ->map(function($items, $province) {
            return [
                'province' => $province,
                'count' => $items->count(),
            ];
        })
        ->sortByDesc('count')
        ->values();
    
    return [
        'by_month' => $months,
        'by_manager' => $byManager->toArray(),
        'by_project' => $byProject->toArray(),
        'by_province' => $byProvince->toArray(),
    ];
}

/**
 * Obter período do relatório
 */
private function getReportPeriod(Request $request): string
{
    if ($request->filled('period')) {
        $periods = [
            'today' => 'Hoje',
            'week' => 'Esta Semana',
            'month' => 'Este Mês',
            '3months' => 'Últimos 3 Meses',
            '6months' => 'Últimos 6 Meses',
            'year' => 'Este Ano',
            '12months' => 'Últimos 12 Meses',
        ];
        
        return $periods[$request->input('period')] ?? 'Período não especificado';
    }
    
    return 'Todo o Período';
}

/**
 * Obter filtros aplicados
 */
private function getAppliedFilters(Request $request): array
{
    $filters = [];
    
    if ($request->filled('period')) {
        $filters['período'] = $this->getReportPeriod($request);
    }
    
    if ($request->filled('type')) {
        $filters['tipo'] = $this->getTypeLabelForExport($request->input('type'));
    }
    
    if ($request->filled('status')) {
        $filters['estado'] = $this->getStatusText($request->input('status'));
    }
    
    if ($request->filled('priority')) {
        $filters['prioridade'] = $this->getPriorityLabel($request->input('priority'));
    }
    
    if ($request->filled('category')) {
        $filters['categoria'] = $request->input('category');
    }
    
    if ($request->filled('province')) {
        $filters['província'] = $request->input('province');
    }
    
    if ($request->filled('tab') && $request->input('tab') !== 'all') {
        $tabLabels = [
            'suggestions' => 'Sugestões',
            'grievances' => 'Queixas',
            'complaints' => 'Reclamações',
            'resolved' => 'Resolvidas',
            'rejected' => 'Rejeitadas',
            'manager_requests' => 'Solicitações do Gestor',
            'director_interventions' => 'Minhas Intervenções',
        ];
        
        $filters['aba'] = $tabLabels[$request->input('tab')] ?? $request->input('tab');
    }
    
    return $filters;
}

/**
 * Obter intervalo de datas para período
 */
private function getDateRangeForPeriod($period): ?array
{
    $now = now();
    
    switch ($period) {
        case 'today':
            return [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ];
            
        case 'week':
            return [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ];
            
        case 'month':
            return [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ];
            
        case '3months':
            return [
                'start' => $now->copy()->subMonths(3)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ];
            
        case '6months':
            return [
                'start' => $now->copy()->subMonths(6)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ];
            
        case 'year':
            return [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ];
            
        case '12months':
            return [
                'start' => $now->copy()->subMonths(12)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ];
            
        default:
            return null;
    }
}

/**
 * Obter label do tipo para exportação
 */
private function getTypeLabelForExport($type): string
{
    if (!$type) return 'Não especificado';
    
    $type = strtolower($type);
    $labels = [
        'suggestion' => 'Sugestão',
        'sugestão' => 'Sugestão',
        'sugestao' => 'Sugestão',
        'grievance' => 'Queixa',
        'queixa' => 'Queixa',
        'complaint' => 'Reclamação',
        'reclamação' => 'Reclamação',
        'reclamacao' => 'Reclamação',
    ];
    
    return $labels[$type] ?? ucfirst($type);
}

/**
 * Obter label da prioridade
 */
private function getPriorityLabel($priority): string
{
    if (!$priority) return 'N/A';
    
    $labels = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'critical' => 'Crítica',
        'urgent' => 'Urgente',
    ];
    
    return $labels[$priority] ?? $priority;
}

/**
 * Criar PDF de erro
 */
private function createErrorPdf($errorMessage, $user)
{
    $data = [
        'title' => 'Erro ao Exportar Relatório',
        'subtitle' => 'Director: ' . $user->name,
        'user' => $user,
        'user_name' => $user->name,
        'export_date' => now()->format('d/m/Y H:i'),
        'error_message' => $errorMessage,
        'is_error' => true,
    ];
    
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.director-submissions-pdf', $data);
    $pdf->setPaper('A4', 'portrait');
    
    $filename = 'erro-exportacao-' . now()->format('Y-m-d-H-i') . '.pdf';
    return $pdf->download($filename);
}

/**
 * Método para verificar exportação (para debug)
 */
public function checkExport(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    try {
        \Log::info('Director - Check export', [
            'user_id' => $user->id,
            'request_params' => $request->all()
        ]);
        
        $query = Grievance::with(['user', 'assignedUser'])
            ->latest();
        
        $this->applyReportFilters($query, $request);
        
        $grievances = $query->get();
        
        return response()->json([
            'success' => true,
            'count' => $grievances->count(),
            'filters' => $request->all(),
            'sample_data' => $grievances->count() > 0 ? [
                'first_grievance' => [
                    'id' => $grievances->first()->id,
                    'reference' => $grievances->first()->reference_number,
                    'type' => $grievances->first()->type,
                    'status' => $grievances->first()->status,
                ]
            ] : null
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erro em checkExport do Director: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao verificar dados: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Exportar lista simples de submissões (sem estatísticas)
 */
/*public function exportSimpleList(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    try {
        $query = Grievance::with(['user', 'assignedUser', 'project'])
            ->latest();
        
        $this->applyReportFilters($query, $request);
        
        $grievances = $query->get();
        
        $data = [
            'title' => 'Lista de Submissões - Director',
            'subtitle' => 'Director: ' . $user->name,
            'user' => $user,
            'user_name' => $user->name,
            'export_date' => now()->format('d/m/Y H:i'),
            'period' => $this->getReportPeriod($request),
            'total_grievances' => $grievances->count(),
            'grievances' => $grievances->map(function ($grievance) {
                return [
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'type' => $this->getTypeLabelForExport($grievance->type),
                    'priority' => $this->getPriorityLabel($grievance->priority),
                    'status' => $this->getStatusText($grievance->status),
                    'category' => $grievance->category ?? 'N/A',
                    'created_at' => $grievance->created_at->format('d/m/Y H:i'),
                    'user_name' => $grievance->user ? $grievance->user->name : 'Anônimo',
                    'technician' => $grievance->assignedUser ? $grievance->assignedUser->name : 'Não atribuído',
                    'project' => $grievance->project ? $grievance->project->name : 'N/A',
                    'escalated' => $grievance->escalated ? 'Sim' : 'Não',
                ];
            })->toArray(),
            'filters_applied' => $this->getAppliedFilters($request),
            'is_simple_list' => true,
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.director-submissions-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'lista-submissoes-' . now()->format('Y-m-d-H-i') . '.pdf';
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao exportar lista simples: ' . $e->getMessage());
        return $this->createErrorPdf('Erro ao gerar lista: ' . $e->getMessage(), $user);
    }
}*/


/**
 * Obter texto do status
 */
private function getStatusText($status): string
{
    if (empty($status)) return 'Não definido';
    
    $statusMap = [
        'submitted' => 'Submetido',
        'under_review' => 'Em Análise',
        'assigned' => 'Atribuído',
        'in_progress' => 'Em Progresso',
        'completed' => 'Concluído',
        'closed' => 'Fechado',
        'reopened' => 'Reaberto',
        'cancelled' => 'Cancelado',
        'awaiting_approval' => 'Aguardando Aprovação',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado',
        'validated' => 'Validado',
        'analyzed' => 'Analisado',
        'awaiting_validation' => 'Aguardando Validação',
        'awaiting_director_approval' => 'Aguardando Aprovação do Director',
        'pending' => 'Pendente',
        'resolved' => 'Resolvido',
        'escalated' => 'Escalado',
        'returned' => 'Devolvido',
    ];
    
    return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

    /**
     * Aplicar filtros à query
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_anonymous')) {
            $query->where('is_anonymous', $request->boolean('is_anonymous'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    }

    /**
     * Obter estatísticas do dashboard
     */
    private function getDashboardStats(): array
    {
        $total = Grievance::count();
        $pending = Grievance::whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress', 'pending_approval'])->count();
        $critical = Grievance::whereIn('priority', ['high', 'urgent'])
            ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->count();
        $resolved = Grievance::where('status', 'resolved')->count();
        $rejected = Grievance::where('status', 'rejected')->count();
        $unassigned = Grievance::whereNull('assigned_to')
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'critical' => $critical,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'unassigned' => $unassigned,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Obter opções de filtro disponíveis
     */
    private function getFilterOptions(): array
    {
        return [
            'provinces' => Grievance::select('province')
                ->whereNotNull('province')
                ->distinct()
                ->orderBy('province')
                ->pluck('province')
                ->map(fn($p) => ['id' => $p, 'name' => $p])
                ->values()
                ->toArray(),
            
            'categories' => Grievance::select('category')
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->map(fn($c) => ['id' => $c, 'name' => $c])
                ->values()
                ->toArray(),
            
            'types' => [
                ['id' => 'grievance', 'name' => 'Queixa'],
                ['id' => 'submission', 'name' => 'Reclamação'],
                ['id' => 'suggestion', 'name' => 'Sugestão'],
            ],
            
            'statuses' => [
                ['id' => 'submitted', 'name' => 'Submetida'],
                ['id' => 'under_review', 'name' => 'Em Análise'],
                ['id' => 'assigned', 'name' => 'Atribuída'],
                ['id' => 'in_progress', 'name' => 'Em Andamento'],
                ['id' => 'pending_approval', 'name' => 'Pendente Aprovação'],
                ['id' => 'resolved', 'name' => 'Resolvida'],
                ['id' => 'rejected', 'name' => 'Rejeitada'],
            ],
            
            'priorities' => [
                ['id' => 'low', 'name' => 'Baixa'],
                ['id' => 'medium', 'name' => 'Média'],
                ['id' => 'high', 'name' => 'Alta'],
                ['id' => 'urgent', 'name' => 'Urgente'],
            ],
        ];
    }

    /**
     * Obter reclamação com todas as relações
     */
    private function getGrievanceWithRelations($id)
    {
        return Grievance::with([
            'user',
            'assignedUser',
            'project',
            'resolvedBy',
            'attachments',
            'comments' => function($query) {
                $query->with('user')->latest();
            },
            'updates' => function($query) {
                $query->with('user')->latest();
            }
        ])->findOrFail($id);
    }

    /**
     * Formatar reclamação para lista
     */
    private function formatGrievanceForShow(Grievance $grievance): array
{
    $data = [
        'id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'subject' => $grievance->description,
        'description' => $grievance->description,
        'type' => $grievance->type,
        'priority' => $grievance->priority,
        'status' => $grievance->status,
        'category' => $grievance->category,
        'subcategory' => $grievance->subcategory,
        'created_at' => $grievance->created_at->toISOString(),
        'submitted_at' => $grievance->submitted_at->toISOString(),
        'updated_at' => $grievance->updated_at->toISOString(),
        
        // Campos de escalamento
        'escalated' => $grievance->escalated,
        'escalated_at' => $grievance->escalated_at?->toISOString(),
        'escalation_reason' => $grievance->escalation_reason,
        
        // Informações do submetedor
        'is_anonymous' => $grievance->is_anonymous,
        'contact_name' => $grievance->contact_name,
        'contact_email' => $grievance->contact_email,
        'contact_phone' => $grievance->contact_phone,
        
        // Localização
        'province' => $grievance->province,
        'district' => $grievance->district,
        'location_details' => $grievance->location_details,
        
        // Relacionamentos
        'user' => $grievance->user ? [
            'id' => $grievance->user->id,
            'name' => $grievance->user->name,
            'email' => $grievance->user->email,
        ] : null,
        
        'assigned_to' => $grievance->assignedUser ? [
            'id' => $grievance->assignedUser->id,
            'name' => $grievance->assignedUser->name,
            'email' => $grievance->assignedUser->email,
        ] : null,
        
        'escalated_by' => $grievance->escalatedBy ? [
            'id' => $grievance->escalatedBy->id,
            'name' => $grievance->escalatedBy->name,
            'email' => $grievance->escalatedBy->email,
        ] : null,
        
        'project' => $grievance->project ? [
            'id' => $grievance->project->id,
            'name' => $grievance->project->name,
        ] : null,
        
        // Anexos
        'attachments' => $grievance->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'name' => $attachment->original_filename,
                'size' => $this->formatBytes($attachment->size),
                'path' => $attachment->path,
            ];
        })->toArray(),
        
        // Updates/histórico
        'updates' => $grievance->updates->map(function ($update) {
            return [
                'id' => $update->id,
                'action_type' => $update->action_type,
                'description' => $update->description,
                'comment' => $update->comment,
                'metadata' => $update->metadata ?? [],
                'created_at' => $update->created_at->toISOString(),
                'user' => $update->user ? [
                    'id' => $update->user->id,
                    'name' => $update->user->name,
                    'email' => $update->user->email,
                ] : null,
            ];
        })->sortByDesc('created_at')->values()->toArray(),
        
        'metadata' => $grievance->metadata ?? [],
    ];
    
    // Adicionar validação do director se existir
    if (isset($grievance->metadata['director_validation'])) {
        $data['director_validation'] = $grievance->metadata['director_validation'];
    }
    
    return $data;
}


public function validateCase(Request $request, $id)
{
    $user = $request->user();
    $this->checkAccess($user);

    if (!$user->hasRole('Director')) {
        return response()->json([
            'error' => 'Apenas o Director pode validar submissões.'
        ], 403);
    }

    // Validação com as duas opções
    $validated = $request->validate([
        'status' => 'required|in:approved,commented', // Agora 'commented' em vez de 'needs_revision'
        'comment' => 'required|string|min:10|max:2000',
        'notify_manager' => 'boolean',
        'notify_technician' => 'boolean',
        'notify_user' => 'boolean',
        'assumed_by_director' => 'boolean', // Novo campo para controle
    ]);

    $grievance = Grievance::findOrFail($id);
    
    // Verificar se é um caso escalado para director
    if (!$grievance->isEscalated()) {
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => 'Esta submissão não foi escalada para o Director']);
        }
        return response()->json([
            'success' => false,
            'message' => 'Esta submissão não foi escalada para o Director'
        ], 400);
    }

    DB::beginTransaction();
    try {
        // Preparar dados da validação
        $validationData = [
            'status' => $validated['status'],
            'comment' => $validated['comment'],
            'validated_by' => $user->id,
            'validated_by_name' => $user->name,
            'validated_at' => now()->toISOString(),
            'assumed_by_director' => $validated['assumed_by_director'] ?? ($validated['status'] === 'approved'),
            'notifications' => [
                'manager' => $validated['notify_manager'] ?? true,
                'technician' => $validated['notify_technician'] ?? true,
                'user' => $validated['notify_user'] ?? false,
            ]
        ];

        // Atualizar metadados
        $metadata = $grievance->metadata ?? [];
        $metadata['director_validation'] = $validationData;
        
        // ========== OPÇÃO 1: DIRECTOR ASSUME O CASO ==========
        if ($validated['status'] === 'approved') {
            $metadata['is_validated'] = true;
            $metadata['validation_status'] = 'approved';
            $metadata['director_assumed_case'] = true;
            
            // Director assume o caso - atribui a si mesmo
            $grievance->assigned_to = $user->id;
            $grievance->assigned_at = now();
            
            // Maném status atual ou define para 'under_review' pelo director
            if ($grievance->status === 'escalated') {
                $grievance->status = 'under_review';
            }
            
            // Aumentar prioridade se necessário
            if (!in_array($grievance->priority, ['high', 'critical'])) {
                $grievance->priority = 'high';
            }
            
            // Registrar que o director assumiu o caso
            $this->createDirectorUpdate(
                $grievance,
                'director_assumed_case',
                "Director assumiu a responsabilidade pelo caso",
                $validated['comment'],
                $user->id,
                [
                    'validation_status' => 'approved',
                    'assumed_by_director' => true,
                    'previous_assigned_to' => $grievance->getOriginal('assigned_to'),
                    'new_assigned_to' => $user->id,
                    'visible_to' => ['manager', 'technician', 'director']
                ]
            );
            
            // Criar update de validação aprovada
            $this->createDirectorUpdate(
                $grievance,
                'director_validation_approved',
                "Director aprovou a submissão e assumiu o caso",
                $validated['comment'],
                $user->id,
                [
                    'validation_status' => 'approved',
                    'assumed_by_director' => true,
                    'visible_to' => ['manager', 'technician', 'director']
                ]
            );
        } 
        // ========== OPÇÃO 2: DIRECTOR COMENTA E DEVOLVE AO GESTOR ==========
        elseif ($validated['status'] === 'commented') {
            $metadata['is_validated'] = false;
            $metadata['validation_status'] = 'commented';
            $metadata['director_provided_guidance'] = true;
            
            // Determinar para quem devolver o caso
            $returnToManager = true;
            
            if ($returnToManager) {
                // Encontrar o gestor original (quem escalou) ou primeiro gestor disponível
                $originalManager = $grievance->escalatedBy;
                $manager = $originalManager ?? User::role('Gestor')->where('is_available', true)->first();
                
                if ($manager) {
                    // Devolver ao gestor
                    $grievance->assigned_to = $manager->id;
                    $grievance->assigned_at = now();
                    $grievance->status = 'under_review';
                    
                    // Registrar devolução ao gestor
                    $this->createDirectorUpdate(
                        $grievance,
                        'director_provided_guidance',
                        "Director forneceu orientações e devolveu o caso ao gestor",
                        $validated['comment'],
                        $user->id,
                        [
                            'validation_status' => 'commented',
                            'returned_to_manager' => true,
                            'manager_id' => $manager->id,
                            'manager_name' => $manager->name,
                            'visible_to' => ['manager', 'technician', 'director']
                        ]
                    );
                }
            }
            
            // Criar update de comentário do director
            $this->createDirectorUpdate(
                $grievance,
                'director_comment',
                "Director forneceu parecer técnico",
                $validated['comment'],
                $user->id,
                [
                    'comment_type' => 'director_guidance',
                    'visible_to' => ['manager', 'technician', 'director']
                ]
            );
        }

        // Atualizar metadados e salvar
        $grievance->metadata = $metadata;
        $grievance->save();

        // Enviar notificações
        $this->sendDirectorInterventionNotifications($grievance, $validationData, $user);

        DB::commit();

        // Preparar resposta
        $responseData = [
            'success' => true,
            'message' => $validated['status'] === 'approved' 
                ? 'Caso assumido com sucesso! Você agora é responsável por esta submissão.'
                : 'Parecer enviado com sucesso! O caso foi devolvido ao gestor.',
            'validation' => $validationData,
            'grievance' => [
                'id' => $grievance->id,
                'assigned_to' => $grievance->assigned_to,
                'status' => $grievance->status,
                'metadata' => $grievance->metadata,
            ]
        ];

        // Verificar se é uma requisição Inertia
        if ($request->header('X-Inertia')) {
            return back()->with([
                'success' => $responseData['message'],
                'validation' => $validationData
            ]);
        }

        // Para requisições API/JSON
        return response()->json($responseData);

    } catch (\Exception $e) {
        DB::rollBack();
        
        $errorMessage = 'Erro ao processar intervenção: ' . $e->getMessage();
        
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => $errorMessage]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ], 500);
    }
}




public function updateValidation(Request $request, $id, $validationId = null)
{
    $user = $request->user();
    $this->checkAccess($user);

    if (!$user->hasRole('Director')) {
        // Retornar resposta Inertia para erro
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => 'Apenas o Director pode editar validações.']);
        }
        return response()->json([
            'error' => 'Apenas o Director pode editar validações.'
        ], 403);
    }

    $validated = $request->validate([
        'status' => 'required|in:approved,commented,needs_revision',
        'comment' => 'required|string|min:10|max:2000',
        'notify_manager' => 'boolean',
        'notify_technician' => 'boolean',
        'notify_user' => 'boolean',
    ]);

    $grievance = Grievance::findOrFail($id);
    
    // Verificar se existe validação para editar
    if (!$grievance->metadata || !isset($grievance->metadata['director_validation'])) {
        // RETORNO INERTIA PARA ERRO
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => 'Nenhuma validação encontrada para editar']);
        }
        return response()->json([
            'success' => false,
            'message' => 'Nenhuma validação encontrada para editar'
        ], 404);
    }

    DB::beginTransaction();
    try {
        $validationData = $grievance->metadata['director_validation'];
        
        // Atualizar dados da validação
        $validationData['status'] = $validated['status'];
        $validationData['comment'] = $validated['comment'];
        $validationData['updated_at'] = now()->toISOString();
        $validationData['updated_by'] = $user->id;
        $validationData['notifications'] = [
            'manager' => $validated['notify_manager'] ?? true,
            'technician' => $validated['notify_technician'] ?? true,
            'user' => $validated['notify_user'] ?? false,
        ];

        // Atualizar metadados
        $metadata = $grievance->metadata;
        $metadata['director_validation'] = $validationData;
        
        // Atualizar status do caso baseado na nova validação
        if ($validated['status'] === 'approved') {
            $metadata['is_validated'] = true;
            $metadata['validation_status'] = 'approved';
            $grievance->status = 'under_review';
        } elseif ($validated['status'] === 'rejected') {
            $metadata['validation_status'] = 'rejected';
            $grievance->status = 'rejected';
        } elseif ($validated['status'] === 'needs_revision') {
            $metadata['validation_status'] = 'needs_revision';
            $grievance->status = 'under_review';
        }

        $grievance->metadata = $metadata;
        $grievance->save();

        // Registrar a edição no histórico
        $this->createDirectorUpdate(
            $grievance,
            'director_validation_updated',
            "Director atualizou sua resposta",
            "Resposta atualizada: " . $validated['comment'],
            $user->id,
            [
                'validation_status' => $validated['status'],
                'previous_status' => $validationData['status'],
                'visible_to' => ['manager', 'technician', 'director']
            ]
        );

        DB::commit();

        // PREPARAR RESPOSTA INERTIA
        $responseData = [
            'success' => true,
            'message' => 'Resposta atualizada com sucesso!',
            'validation' => $validationData,
        ];

        // VERIFICAR SE É REQUISIÇÃO INERTIA
        if ($request->header('X-Inertia')) {
            // Carregar dados atualizados
            $grievance->load(['user', 'assignedUser', 'updates.user.roles']);
            $submissionData = $this->formatGrievanceForShow($grievance);
            
            return back()->with([
                'success' => $responseData['message'],
                'validation' => $validationData,
                'complaint' => $submissionData, // Enviar dados atualizados
                'submission' => $submissionData, // Para compatibilidade
            ]);
        }

        // Para requisições API/JSON
        return response()->json($responseData);

    } catch (\Exception $e) {
        DB::rollBack();
        
        $errorMessage = 'Erro ao atualizar validação: ' . $e->getMessage();
        
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => $errorMessage]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ], 500);
    }
}


private function sendDirectorInterventionNotifications(Grievance $grievance, array $validationData, User $director): void
{
    try {
        $status = $validationData['status'];
        $notifications = $validationData['notifications'] ?? [];
        
        // ========== NOTIFICAR O GESTOR ==========
        if (($notifications['manager'] ?? true)) {
            $manager = $grievance->escalatedBy ?? User::find($grievance->assigned_to);
            
            if ($manager && $manager->id !== $director->id) {
                \Log::info('Director - Notificação ao gestor', [
                    'grievance_id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'director_id' => $director->id,
                    'director_name' => $director->name,
                    'manager_id' => $manager->id,
                    'manager_name' => $manager->name,
                    'action' => $status === 'approved' ? 'assumed_case' : 'provided_guidance',
                    'comment_preview' => substr($validationData['comment'], 0, 100),
                ]);
                
                // TODO: Enviar email/notificação real
            }
        }
        
        // ========== NOTIFICAR O TÉCNICO ==========
        if (($notifications['technician'] ?? true) && $grievance->assigned_to) {
            $technician = User::find($grievance->assigned_to);
            
            if ($technician && $technician->id !== $director->id) {
                \Log::info('Director - Notificação ao técnico', [
                    'grievance_id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'director_id' => $director->id,
                    'director_name' => $director->name,
                    'technician_id' => $technician->id,
                    'technician_name' => $technician->name,
                    'action' => $status === 'approved' ? 'director_assumed' : 'director_commented',
                ]);
            }
        }
        
        // ========== NOTIFICAR O UTENTE (se público) ==========
        if (($notifications['user'] ?? false) && !$grievance->is_anonymous) {
            $user = $grievance->user;
            
            if ($user) {
                \Log::info('Director - Notificação ao utente', [
                    'grievance_id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'action' => $status === 'approved' ? 'validated_by_director' : 'commented_by_director',
                ]);
            }
        }
        
    } catch (\Exception $e) {
        \Log::error('Erro ao enviar notificações do director', [
            'grievance_id' => $grievance->id,
            'error' => $e->getMessage(),
        ]);
    }
}

public function addComment(Request $request, $id)
{
    $user = $request->user();
    $this->checkAccess($user);

    $validated = $request->validate([
        'content' => 'required|string|min:10|max:5000',
        'comment_type' => 'required|in:internal,public,director_only',
        'notify_manager' => 'boolean',
        'notify_technician' => 'boolean',
        'notify_user' => 'boolean',
        'attachments' => 'nullable|array|max:5',
        'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:10240',
    ]);

    $grievance = Grievance::findOrFail($id);
    
    // Verificar se é um caso escalado para director
    if (!$grievance->isEscalated()) {
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => 'Esta submissão não foi escalada para o Director']);
        }
        return response()->json([
            'success' => false,
            'message' => 'Esta submissão não foi escalada para o Director'
        ], 400);
    }

    DB::beginTransaction();
    try {
        // Criar o update como comentário do director
        $updateData = [
            'grievance_id' => $grievance->id,
            'user_id' => $user->id,
            'action_type' => 'director_comment',
            'description' => 'Comentário do Director',
            'comment' => $validated['content'],
            'is_public' => $validated['comment_type'] === 'public',
            'metadata' => [
                'comment_type' => $validated['comment_type'],
                'notifications' => [
                    'manager' => $validated['notify_manager'] ?? false,
                    'technician' => $validated['notify_technician'] ?? false,
                    'user' => $validated['notify_user'] ?? false,
                ],
                'visible_to' => $this->getVisibleTo($validated['comment_type'])
            ]
        ];

        $comment = GrievanceUpdate::create($updateData);

        // Processar anexos se existirem
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeCommentAttachment($grievance, $comment, $file);
            }
        }

        // Enviar notificações se solicitado
        $this->sendCommentNotifications($grievance, $comment, $validated);

        DB::commit();

        // Verificar se é uma requisição Inertia
        if ($request->header('X-Inertia')) {
            return back()->with([
                'success' => 'Comentário adicionado com sucesso!',
                'new_comment' => [
                    'id' => $comment->id,
                    'content' => $comment->comment,
                    'type' => $validated['comment_type'],
                    'created_at' => $comment->created_at->toISOString(),
                    'user' => [
                        'name' => $user->name,
                        'role' => 'Director'
                    ]
                ]
            ]);
        }

        // Para requisições API/JSON
        return response()->json([
            'success' => true,
            'message' => 'Comentário adicionado com sucesso!',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->comment,
                'type' => $validated['comment_type'],
                'created_at' => $comment->created_at->toISOString(),
                'user' => [
                    'name' => $user->name,
                    'role' => 'Director'
                ]
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        
        $errorMessage = 'Erro ao adicionar comentário: ' . $e->getMessage();
        
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => $errorMessage]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ], 500);
    }
}


private function createDirectorUpdate(Grievance $grievance, string $actionType, string $description, string $comment, int $userId, array $metadata = []): GrievanceUpdate
{
    return GrievanceUpdate::create([
        'grievance_id' => $grievance->id,
        'user_id' => $userId,
        'action_type' => $actionType,
        'description' => $description,
        'comment' => $comment,
        'metadata' => array_merge($metadata, [
            'created_by_director' => true,
            'director_id' => $userId,
        ]),
        'is_public' => in_array('user', $metadata['visible_to'] ?? []),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * Determinar quem pode ver o comentário
 */
private function getVisibleTo(string $commentType): array
{
    switch ($commentType) {
        case 'public':
            return ['manager', 'technician', 'user', 'director'];
        case 'director_only':
            return ['director'];
        case 'internal':
        default:
            return ['manager', 'technician', 'director'];
    }
}

private function createValidationUpdate(Grievance $grievance, string $status, string $comment, int $userId): void
{
    $statusLabels = [
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado',
        'needs_revision' => 'Precisa de Revisão'
    ];

    $grievance->updates()->create([
        'action_type' => 'director_validation',
        'user_id' => $userId,
        'description' => "Submissão {$statusLabels[$status]} pelo Director",
        'comment' => $comment,
        'metadata' => [
            'validation_status' => $status,
            'is_public' => false,
            'visible_to' => ['manager', 'technician', 'director']
        ],
        'is_public' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * Criar update de comentário
 */
private function createCommentUpdate(Grievance $grievance, $comment, int $userId): void
{
    $grievance->updates()->create([
        'action_type' => 'director_comment',
        'user_id' => $userId,
        'description' => 'Director adicionou um comentário',
        'comment' => $comment->content,
        'metadata' => [
            'comment_id' => $comment->id,
            'comment_type' => $comment->is_public ? 'public' : ($comment->is_director_only ? 'director_only' : 'internal'),
            'is_public' => $comment->is_public,
            'visible_to' => $this->getVisibleTo($comment)
        ],
        'is_public' => $comment->is_public,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}


private function sendValidationNotifications(Grievance $grievance, array $data): void
{
    // TODO: Implementar sistema de notificações
    // Enviar email/notificação ao gestor se solicitado
    if ($data['notify_manager'] ?? false) {
        $manager = $grievance->escalatedBy ?? User::role('Gestor')->first();
        if ($manager) {
            // Enviar notificação
        }
    }
    
    // Enviar ao técnico se solicitado
    if ($data['notify_technician'] ?? false && $grievance->assigned_to) {
        $technician = User::find($grievance->assigned_to);
        if ($technician) {
            // Enviar notificação
        }
    }
    
    // Enviar ao utente se solicitado e for público
    if ($data['notify_user'] ?? false) {
        $user = $grievance->user;
        if ($user) {
            // Enviar notificação
        }
    }
}


    private function storeCommentAttachment(Grievance $grievance, GrievanceUpdate $comment, $file): \App\Models\Attachment
    {
        $originalFilename = $file->getClientOriginalName();
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        $publicPath = 'uploads/grievances/' . $grievance->id . '/comments/' . $comment->id;
        $fullPath = public_path($publicPath);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $file->move($fullPath, $filename);
        $path = '/' . $publicPath . '/' . $filename;

        return \App\Models\Attachment::create([
            'grievance_id' => $grievance->id,
            'original_filename' => $originalFilename,
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'metadata' => [
                'uploaded_via' => 'director_comment',
                'uploaded_by' => auth()->id(),
                'comment_id' => $comment->id,
                'uploaded_at' => now()->toISOString(),
            ],
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);
    }
    /**
     * Obter técnicos disponíveis
     */
    private function getAvailableTechnicians(): array
    {
        return User::role('Técnico')
            ->where('is_available', true)
            ->select('id', 'name', 'email', 'phone', 'current_workload', 'workload_capacity')
            ->get()
            ->map(function ($tech) {
                return [
                    'id' => $tech->id,
                    'name' => $tech->name,
                    'email' => $tech->email,
                    'phone' => $tech->phone,
                    'workload' => $tech->current_workload . '/' . $tech->workload_capacity,
                    'available' => $tech->current_workload < $tech->workload_capacity,
                    'available_percentage' => $tech->workload_capacity > 0 
                        ? round(($tech->current_workload / $tech->workload_capacity) * 100, 1)
                        : 0,
                ];
            })
            ->sortBy('available_percentage')
            ->values()
            ->toArray();
    }

    /**
     * Obter gestores disponíveis
     */
    private function getAvailableManagers(): array
    {
        return User::role('gestor')
            ->where('is_available', true)
            ->select('id', 'name', 'email', 'phone')
            ->get()
            ->map(function ($manager) {
                return [
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'email' => $manager->email,
                    'phone' => $manager->phone,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obter projetos ativos
     */
    private function getActiveProjects(): array
    {
        return \App\Models\Project::where('is_active', true)
            ->select('id', 'name', 'description')
            ->orderBy('name')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Actualizar workload do técnico
     */
    private function updateTechnicianWorkload(Grievance $grievance, string $operation, ?User $technician = null): void
    {
        $tech = $technician ?? $grievance->assignedUser;
        
        if (!$tech || !$tech->hasRole('Técnico')) {
            return;
        }

        $workloadWeight = match($grievance->priority) {
            'urgent' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 2,
        };

        if ($operation === 'increment') {
            $tech->current_workload = ($tech->current_workload ?? 0) + $workloadWeight;
        } elseif ($operation === 'decrement') {
            $tech->current_workload = max(0, ($tech->current_workload ?? 0) - $workloadWeight);
        }

        $tech->save();
    }

    /**
     * Criar atualização de status
     */
    private function createStatusUpdate(Grievance $grievance, string $status, ?string $notes, int $userId): void
    {
        $grievance->updates()->create([
            'status' => $status,
            'notes' => $notes,
            'user_id' => $userId,
            'is_public' => false, // Atualizações do Director são internas por padrão
        ]);
    }

    /**
     * Formatar tamanho de arquivo
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
 * API: Obter solicitações do gestor
 */
public function getManagerRequestsApi(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    $data = $this->getManagerRequestsData($user);
    
    return response()->json([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
}

/**
 * API: Obter minhas intervenções
 */
public function getMyInterventionsApi(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    $data = $this->getDirectorInterventionsData($user);
    
    return response()->json([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
}

public function getTabData(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    $tab = $request->get('tab', 'all');
    
    switch ($tab) {
        case 'manager_requests':
            $data = $this->getManagerRequestsData($user);
            break;
        case 'director_interventions':
            $data = $this->getDirectorInterventionsData($user);
            break;
        case 'suggestions':
            $data = $this->getGrievancesByType('suggestion', $user);
            break;
        case 'grievances':
            $data = $this->getGrievancesByType('grievance', $user);
            break;
        case 'complaints':
            $data = $this->getGrievancesByType('complaint', $user);
            break;
        case 'all':
        default:
            $data = $this->getAllGrievances($user);
            break;
    }
    
    return response()->json([
        'success' => true,
        'tab' => $tab,
        'data' => $data,
        'count' => count($data)
    ]);
}

/**
 * Obter reclamações por tipo
 */
private function getGrievancesByType(string $type, User $user): array
{
    $query = Grievance::with(['user', 'assignedUser', 'updates.user.roles']);
    
    if ($type === 'suggestion') {
        $query->where(function($q) {
            $q->where('type', 'suggestion')
              ->orWhere('type', 'like', '%sugest%');
        });
    } elseif ($type === 'grievance') {
        $query->where(function($q) {
            $q->where('type', 'grievance')
              ->orWhere('type', 'like', '%queixa%');
        });
    } elseif ($type === 'complaint') {
        $query->where(function($q) {
            $q->where('type', 'complaint')
              ->orWhere('type', 'like', '%reclam%');
        });
    }
    
    $grievances = $query->latest()->get();
    
    return $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    })->values()->toArray();
}

/**
 * Obter todas as reclamações
 */
private function getAllGrievances(User $user): array
{
    $grievances = Grievance::with(['user', 'assignedUser', 'updates.user.roles'])
        ->latest()
        ->get();
    
    return $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    })->values()->toArray();
}


    // Método para adicionar comentário simples
     public function addSimpleComment(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        try {
            $validated = $request->validate([
                'comment' => ['required', 'string', 'min:2', 'max:2000'],
                'comment_type' => ['sometimes', 'in:public,internal,director_only'],
                'attachments' => ['nullable', 'array', 'max:5'],
                'attachments.*' => ['file', 'mimes:jpeg,jpg,png,pdf,doc,docx,txt', 'max:10240'],
            ]);

            $grievance = Grievance::findOrFail($id);
            
            DB::beginTransaction();

            // Determinar visibilidade
            $commentType = $validated['comment_type'] ?? 'internal';
            $isPublic = $commentType === 'public';

            // Criar o comentário
            $update = GrievanceUpdate::create([
                'grievance_id' => $grievance->id,
                'user_id' => $user->id,
                'action_type' => 'director_comment',
                'description' => 'Comentário do Director',
                'comment' => $validated['comment'],
                'is_public' => $isPublic,
                'metadata' => [
                    'comment_type' => $commentType,
                    'timestamp' => now()->toISOString(),
                    'created_by_director' => true,
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $attachments = [];
            // Processar anexos
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalFilename = $file->getClientOriginalName();
                    $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs(
                        'grievances/' . $grievance->id . '/comments/' . $update->id,
                        $filename,
                        'private'
                    );

                    $attachment = Attachment::create([
                        'grievance_id' => $grievance->id,
                        'original_filename' => $originalFilename,
                        'filename' => $filename,
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'metadata' => [
                            'uploaded_via' => 'director_comment',
                            'comment_id' => $update->id,
                        ],
                        'uploaded_by' => $user->id,
                        'uploaded_at' => now(),
                    ]);

                    $attachments[] = [
                        'id' => $attachment->id,
                        'name' => $attachment->original_filename,
                        'size' => $attachment->size,
                        'url' => route('attachments.download', $attachment->id),
                    ];
                }
            }

            DB::commit();

            // Carregar relacionamentos
            $update->load('user.roles');

            return response()->json([
                'success' => true,
                'message' => 'Comentário enviado com sucesso',
                'comment' => [
                    'id' => $update->id,
                    'content' => $update->comment,
                    'comment' => $update->comment,
                    'type' => $commentType,
                    'action_type' => $update->action_type,
                    'created_at' => $update->created_at->toISOString(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->getRoleNames()->first() ?: 'Director',
                    ],
                    'user_id' => $user->id,
                    'is_public' => $isPublic,
                    'attachments' => $attachments,
                    'metadata' => $update->metadata,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao criar comentário do director', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar comentário: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Método para obter comentários
    public function getComments(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $grievance = Grievance::findOrFail($id);
        
        $comments = GrievanceUpdate::where('grievance_id', $grievance->id)
            ->whereNotNull('comment')
            ->where(function($query) {
                $query->where('action_type', 'manager_comment')
                      ->orWhere('action_type', 'technician_comment')
                      ->orWhere('action_type', 'director_comment');
            })
            ->with(['user.roles'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($update) use ($user) {
                // Director pode ver todos os comentários
                // Exceto validações que são tratadas separadamente
                if ($update->action_type === 'director_validation' || 
                    $update->action_type === 'validation_updated') {
                    return null;
                }
                
                return [
                    'id' => $update->id,
                    'content' => $update->comment,
                    'type' => $update->metadata['comment_type'] ?? 'internal',
                    'action_type' => $update->action_type,
                    'created_at' => $update->created_at->toISOString(),
                    'user' => $update->user ? [
                        'id' => $update->user->id,
                        'name' => $update->user->name,
                        'role' => $update->user->getRoleNames()->first() ?: 'User',
                    ] : null,
                    'is_public' => $update->is_public,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }


    // Método auxiliar para verificar se o usuário pode ver o comentário
    private function canViewComment($update, $user)
    {
        $commentType = $update->metadata['comment_type'] ?? 'internal';
        $userRole = $user->getRoleNames()->first();
        
        // Gestor pode ver todos os comentários
        if ($userRole === 'Gestor' || $userRole === 'manager') {
            return true;
        }
        
        // Comentários públicos são visíveis para todos
        if ($commentType === 'public') {
            return true;
        }
        
        // Comentários internos são visíveis apenas para gestores
        if ($commentType === 'internal') {
            return $userRole === 'Gestor' || $userRole === 'manager';
        }
        
        return false;
    }

public function rejectApproval(Request $request, $id)
{
    $user = $request->user();
    $this->checkAccess($user);

    $validated = $request->validate([
        'reason' => 'required|string|min:10|max:2000',
        'internal_comment' => 'nullable|string|max:1000',
        'notify_user' => 'boolean',
        'notify_technician' => 'boolean',
        'notify_manager' => 'boolean',
        'attachments' => 'nullable|array|max:5',
        'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:10240',
        'rejection_type' => 'required|in:approval_rejection',
    ]);

    $grievance = Grievance::findOrFail($id);
    
    // Verificar se a submissão está no estado "Pendente de Aprovação"
    if ($grievance->status !== 'pending_approval') {
        if ($request->header('X-Inertia')) {
            return back()->withErrors([
                'error' => 'Apenas submissões no estado "Pendente de Aprovação" podem ser rejeitadas'
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Apenas submissões no estado "Pendente de Aprovação" podem ser rejeitadas'
        ], 400);
    }

    DB::beginTransaction();
    try {
        // 1. Atualizar o status da submissão de 'pending_approval' para 'in_progress'
        $oldStatus = $grievance->status;
        $grievance->update([
            'status' => 'in_progress',
            'updated_at' => now(),
        ]);

        // 2. Registrar a rejeição no metadata
        $metadata = $grievance->metadata ?? [];
        $metadata['approval_rejection'] = [
            'rejected_by' => $user->id,
            'rejected_by_name' => $user->name,
            'rejected_at' => now()->toISOString(),
            'reason' => $validated['reason'],
            'internal_comment' => $validated['internal_comment'] ?? null,
            'rejection_type' => $validated['rejection_type'],
            'previous_status' => $oldStatus,
            'new_status' => 'in_progress',
            'notifications' => [
                'user' => $validated['notify_user'] ?? false,
                'technician' => $validated['notify_technician'] ?? false,
                'manager' => $validated['notify_manager'] ?? false,
            ]
        ];

        // Adicionar flag de rejeição de aprovação
        $metadata['has_approval_been_rejected'] = true;
        $metadata['approval_rejection_count'] = ($metadata['approval_rejection_count'] ?? 0) + 1;
        $metadata['last_approval_rejection'] = now()->toISOString();

        $grievance->metadata = $metadata;
        $grievance->save();

        // 3. Criar um update/histórico da ação
        $description = "Aprovação rejeitada pelo Director. Status alterado de 'Pendente de Aprovação' para 'Em Andamento'.";
        
        $grievance->updates()->create([
            'user_id' => $user->id,
            'action_type' => 'director_rejected_approval',
            'description' => $description,
            'comment' => $validated['reason'],
            'metadata' => [
                'rejection_details' => [
                    'type' => 'approval_rejection',
                    'reason' => $validated['reason'],
                    'internal_comment' => $validated['internal_comment'] ?? null,
                    'previous_status' => $oldStatus,
                    'new_status' => 'in_progress',
                ],
                'notifications' => [
                    'user' => $validated['notify_user'] ?? false,
                    'technician' => $validated['notify_technician'] ?? false,
                    'manager' => $validated['notify_manager'] ?? false,
                ],
                'created_by_director' => true,
                'director_id' => $user->id,
            ],
            'is_public' => false, // Comentário interno por padrão
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Processar anexos se existirem
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachment = $this->storeApprovalRejectionAttachment($grievance, $file, $user->id);
                $attachments[] = [
                    'id' => $attachment->id,
                    'name' => $attachment->original_filename,
                    'size' => $attachment->size,
                ];
            }
        }

        // 5. Enviar notificações se solicitado
        $this->sendApprovalRejectionNotifications($grievance, $validated);

        DB::commit();

        // 6. Formatar resposta
        $responseData = [
            'success' => true,
            'message' => 'Aprovação rejeitada com sucesso! A submissão voltou ao estado "Em Andamento".',
            'grievance' => [
                'id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'previous_status' => $oldStatus,
                'new_status' => $grievance->status,
                'rejection' => $metadata['approval_rejection'],
            ],
            'attachments' => $attachments,
        ];

        // Verificar se é uma requisição Inertia
        if ($request->header('X-Inertia')) {
            return back()->with([
                'success' => $responseData['message'],
                'updatedGrievance' => $grievance->fresh(['updates']),
                'rejection_details' => $metadata['approval_rejection']
            ]);
        }

        // Para requisições API/JSON
        return response()->json($responseData);

    } catch (\Exception $e) {
        DB::rollBack();
        
        $errorMessage = 'Erro ao rejeitar aprovação: ' . $e->getMessage();
        
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => $errorMessage]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ], 500);
    }
}

/**
 * Armazenar anexo da rejeição de aprovação
 */
private function storeApprovalRejectionAttachment(Grievance $grievance, $file, int $userId): Attachment
{
    $originalFilename = $file->getClientOriginalName();
    $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

    $path = $file->storeAs(
        'grievances/' . $grievance->id . '/approval_rejections',
        $filename,
        'private'
    );

    return Attachment::create([
        'grievance_id' => $grievance->id,
        'original_filename' => $originalFilename,
        'filename' => $filename,
        'path' => $path,
        'mime_type' => $file->getMimeType(),
        'size' => $file->getSize(),
        'metadata' => [
            'uploaded_via' => 'director_approval_rejection',
            'uploaded_by' => $userId,
            'uploaded_at' => now()->toISOString(),
            'purpose' => 'approval_rejection_documentation',
        ],
        'uploaded_by' => $userId,
        'uploaded_at' => now(),
    ]);
}

/**
 * Enviar notificações de rejeição de aprovação
 */
private function sendApprovalRejectionNotifications(Grievance $grievance, array $data): void
{
    // TODO: Implementar sistema de notificações
    
    // 1. Notificar o técnico responsável (se solicitado)
    if (($data['notify_technician'] ?? false) && $grievance->assigned_to) {
        $technician = User::find($grievance->assigned_to);
        if ($technician) {
            // Enviar notificação ao técnico
            \Log::info('Notificação de rejeição de aprovação enviada ao técnico', [
                'technician_id' => $technician->id,
                'technician_name' => $technician->name,
                'grievance_id' => $grievance->id,
                'reason' => substr($data['reason'], 0, 100) . '...',
            ]);
        }
    }
    
    // 2. Notificar o gestor responsável (se solicitado)
    if (($data['notify_manager'] ?? false)) {
        // Tentar encontrar o gestor responsável
        $manager = null;
        
        // Primeiro, verificar se há um gestor no histórico de updates
        $managerUpdate = $grievance->updates()
            ->whereHas('user', function($query) {
                $query->role('gestor');
            })
            ->latest()
            ->first();
        
        if ($managerUpdate) {
            $manager = $managerUpdate->user;
        } else {
            // Se não encontrar, usar o primeiro gestor disponível
            $manager = User::role('gestor')->first();
        }
        
        if ($manager) {
            // Enviar notificação ao gestor
            \Log::info('Notificação de rejeição de aprovação enviada ao gestor', [
                'manager_id' => $manager->id,
                'manager_name' => $manager->name,
                'grievance_id' => $grievance->id,
                'reason' => substr($data['reason'], 0, 100) . '...',
            ]);
        }
    }
    
    // 3. Notificar o utente (se solicitado e não for anônimo)
    if (($data['notify_user'] ?? false) && !$grievance->is_anonymous && $grievance->user) {
        $user = $grievance->user;
        // Enviar notificação ao utente
        \Log::info('Notificação de rejeição de aprovação enviada ao utente', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'grievance_id' => $grievance->id,
            'reason' => substr($data['reason'], 0, 100) . '...',
        ]);
    }
}

/**
 * Exportar relatório completo (similar ao do gestor)
 */
public function exportCompleteReport(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    try {
        \Log::info('=== DIRECTOR - EXPORTAÇÃO DE RELATÓRIO COMPLETO ===');
        \Log::info('Director: ' . $user->name . ' (ID: ' . $user->id . ')');
        
        // **1. OBTER DADOS DAS RECLAMAÇÕES**
        $query = Grievance::with(['user', 'assignedUser', 'project', 'updates'])
            ->latest('submitted_at');
        
        // Aplicar filtros
        $this->applyReportFilters($query, $request);
        
        $grievances = $query->get();
        $totalGrievances = $grievances->count();
        
        \Log::info('Total de reclamações para Director: ' . $totalGrievances);
        
        // **2. CALCULAR ESTATÍSTICAS COMPLETAS (ADAPTADAS PARA DIRECTOR)**
        $stats = $this->calculateCompleteDirectorStatistics($grievances, $user);
        
        // **3. DADOS PARA TIMELINE/RESOLUÇÃO**
        $resolutionStats = $this->calculateResolutionStats($grievances);
        
        // **4. DISTRIBUIÇÕES (ADAPTADAS PARA DIRECTOR)**
        $distributions = $this->calculateDirectorDistributions($grievances, $user);
        
        // **5. OBTER PERÍODO CORRETAMENTE**
        $periodValue = $request->filled('period') 
            ? $this->getReportPeriod($request) 
            : 'Todo o Período';
        
        // **6. DETERMINAR O TÍTULO BASEADO NA TAB ATIVA**
        $tab = $request->input('tab', 'all');
        $title = $this->getExportTitleForDirector($tab);
        
        // **7. PREPARAR DADOS PARA PDF**
        $data = [
            'title' => $title,
            'subtitle' => 'Director: ' . $user->name . ' - ' . now()->format('F Y'),
            'user' => $user,
            'user_name' => $user->name, 
            'export_date' => now()->format('d/m/Y H:i'),
            'period' => $periodValue,
            
            // Seção de estatísticas
            'statistics' => $stats,
            'resolution_stats' => $resolutionStats,
            'distributions' => $distributions,
            
            // Lista de reclamações
            'total_grievances' => $totalGrievances,
            'grievances' => $grievances->map(function ($grievance) use ($user) {
                return [
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'type' => $this->getTypeLabelForExport($grievance->type),
                    'priority' => $this->getPriorityLabel($grievance->priority),
                    'status' => $this->getStatusText($grievance->status),
                    'category' => $grievance->category ?? 'N/A',
                    'created_at' => $grievance->created_at->format('d/m/Y H:i'),
                    'submitted_at' => $grievance->submitted_at ? $grievance->submitted_at->format('d/m/Y H:i') : 'N/A',
                    'resolved_at' => $grievance->resolved_at ? $grievance->resolved_at->format('d/m/Y H:i') : 'N/A',
                    'user_name' => $grievance->user ? $grievance->user->name : 'Anônimo',
                    'technician' => $grievance->assignedUser ? $grievance->assignedUser->name : 'Não atribuído',
                    'project' => $grievance->project ? $grievance->project->name : 'N/A',
                    'escalated' => $grievance->escalated ? 'Sim' : 'Não',
                    'escalation_reason' => $grievance->escalation_reason ?? 'N/A',
                    'updates_count' => $grievance->updates->count(),
                    'has_attachments' => $grievance->attachments->count() > 0,
                    'has_director_intervention' => $this->hasDirectorIntervention($grievance),
                    'director_response_type' => $this->getDirectorResponseType($grievance, $user),
                ];
            })->toArray(),
            
            // Filtros aplicados
            'filters_applied' => $this->getAppliedFilters($request),
            'is_director_report' => true,
        ];
        
        \Log::info('Relatório do Director preparado com ' . $totalGrievances . ' registros');
        
        // **8. GERAR PDF**
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.director-submissions-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
            'dpi' => 150,
        ]);
        
        $filename = 'relatorio-director-' . now()->format('Y-m-d-H-i') . '.pdf';
        
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao exportar relatório do Director: ' . $e->getMessage());
        return $this->createDirectorErrorPdf('Erro ao gerar relatório: ' . $e->getMessage(), $user);
    }
}

/**
 * Calcular estatísticas completas para Director
 */
private function calculateCompleteDirectorStatistics($grievances, $user): array
{
    $total = $grievances->count();
    
    // Contagem por status
    $byStatus = $grievances->groupBy('status')->map->count();
    
    // Contagem por tipo
    $byType = $grievances->groupBy('type')->map->count();
    
    // Contagem por prioridade
    $byPriority = $grievances->groupBy('priority')->map->count();
    
    // Reclamações escaladas
    $escalatedCount = $grievances->where('escalated', true)->count();
    
    // Reclamações com intervenção do director atual
    $withMyIntervention = $grievances->filter(function($g) use ($user) {
        return $this->hasDirectorInterventionByUser($g, $user);
    })->count();
    
    // Reclamações atribuídas ao director atual
    $assignedToMe = $grievances->where('assigned_to', $user->id)->count();
    
    // Solicitações do gestor (escaladas)
    $managerRequests = $grievances->filter(function($g) {
        return $g->escalated || 
               ($g->metadata && isset($g->metadata['is_escalated_to_director']) && 
                $g->metadata['is_escalated_to_director'] === true);
    })->count();
    
    // Taxa de resolução
    $resolvedCount = $grievances->whereIn('status', ['resolved', 'closed'])->count();
    $resolutionRate = $total > 0 ? round(($resolvedCount / $total) * 100, 1) : 0;
    
    // Reclamações novas (últimos 7 dias)
    $newLast7Days = $grievances->filter(function($g) {
        return $g->created_at->greaterThan(now()->subDays(7));
    })->count();
    
    // Reclamações pendentes
    $pendingCount = $grievances->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count();
    
    // Reclamações com comentários do director
    $withDirectorComments = $grievances->filter(function($g) use ($user) {
        return $g->updates->contains(function($update) use ($user) {
            return $update->user_id === $user->id && 
                   $update->action_type === 'director_comment' &&
                   !empty($update->comment);
        });
    })->count();
    
    return [
        'total' => $total,
        'by_status' => $byStatus->toArray(),
        'by_type' => $byType->toArray(),
        'by_priority' => $byPriority->toArray(),
        'escalated_count' => $escalatedCount,
        'with_my_intervention' => $withMyIntervention,
        'assigned_to_me' => $assignedToMe,
        'manager_requests' => $managerRequests,
        'resolved_count' => $resolvedCount,
        'resolution_rate' => $resolutionRate,
        'new_last_7_days' => $newLast7Days,
        'pending_count' => $pendingCount,
        'with_director_comments' => $withDirectorComments,
        'average_updates_per_grievance' => $total > 0 ? 
            round($grievances->sum(function($g) { return $g->updates->count(); }) / $total, 1) : 0,
    ];
}

/**
 * Calcular distribuições para Director
 */
private function calculateDirectorDistributions($grievances, $user): array
{
    // Distribuição por mês (últimos 6 meses)
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        
        $count = $grievances->filter(function($g) use ($monthStart, $monthEnd) {
            return $g->created_at->between($monthStart, $monthEnd);
        })->count();
        
        $months[] = [
            'month' => $month->format('M/Y'),
            'count' => $count,
        ];
    }
    
    // Distribuição por gestor (quem escalou)
    $byManager = $grievances->where('escalated', true)
        ->groupBy('escalated_by')
        ->map(function($items, $managerId) {
            $manager = User::find($managerId);
            return [
                'manager_id' => $managerId,
                'manager_name' => $manager ? $manager->name : 'Desconhecido',
                'count' => $items->count(),
                'resolved' => $items->whereIn('status', ['resolved', 'closed'])->count(),
                'pending' => $items->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])->count(),
            ];
        })
        ->sortByDesc('count')
        ->take(10)
        ->values();
    
    // Distribuição por tipo de intervenção do director
    $byInterventionType = [
        'comentários' => $grievances->filter(function($g) use ($user) {
            return $g->updates->contains(function($update) use ($user) {
                return $update->user_id === $user->id && 
                       $update->action_type === 'director_comment';
            });
        })->count(),
        
        'validações_aprovadas' => $grievances->filter(function($g) use ($user) {
            return $g->updates->contains(function($update) use ($user) {
                return $update->user_id === $user->id && 
                       $update->action_type === 'director_validation_approved';
            });
        })->count(),
        
        'validações_rejeitadas' => $grievances->filter(function($g) use ($user) {
            return $g->updates->contains(function($update) use ($user) {
                return $update->user_id === $user->id && 
                       $update->action_type === 'director_validation_rejected';
            });
        })->count(),
        
        'assumiu_caso' => $grievances->filter(function($g) use ($user) {
            return $g->assigned_to === $user->id &&
                   $g->updates->contains(function($update) use ($user) {
                       return $update->user_id === $user->id && 
                              $update->action_type === 'director_assumed_case';
                   });
        })->count(),
    ];
    
    // Distribuição por província
    $byProvince = $grievances->whereNotNull('province')
        ->groupBy('province')
        ->map(function($items, $province) {
            return [
                'province' => $province,
                'count' => $items->count(),
            ];
        })
        ->sortByDesc('count')
        ->values();
    
    return [
        'by_month' => $months,
        'by_manager' => $byManager->toArray(),
        'by_intervention_type' => $byInterventionType,
        'by_province' => $byProvince->toArray(),
    ];
}

/**
 * Obter título da exportação baseado na tab
 */
private function getExportTitleForDirector($tab): string
{
    $titles = [
        'all' => 'Relatório Completo - Todas as Submissões',
        'suggestions' => 'Relatório de Sugestões',
        'grievances' => 'Relatório de Queixas',
        'complaints' => 'Relatório de Reclamações',
        'resolved' => 'Relatório de Submissões Concluídas',
        'rejected' => 'Relatório de Submissões Rejeitadas',
        'manager_requests' => 'Relatório de Solicitações do Gestor',
        'director_interventions' => 'Relatório das Minhas Intervenções',
        'my_submissions_to_director' => 'Relatório de Submissões ao Director',
    ];
    
    return $titles[$tab] ?? 'Relatório do Director';
}

/**
 * Obter tipo de resposta do director
 */
private function getDirectorResponseType(Grievance $grievance, User $director): ?string
{
    $validation = $grievance->metadata['director_validation'] ?? null;
    
    if ($validation && isset($validation['validated_by']) && $validation['validated_by'] == $director->id) {
        return match($validation['status'] ?? '') {
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'needs_revision' => 'Revisão Solicitada',
            'commented' => 'Comentado',
            default => null
        };
    }
    
    // Verificar updates específicos do director
    $directorUpdate = $grievance->updates
        ->where('user_id', $director->id)
        ->whereIn('action_type', [
            'director_comment',
            'director_validation_approved',
            'director_validation_rejected',
            'director_validation_needs_revision',
            'director_assumed_case'
        ])
        ->first();
    
    if ($directorUpdate) {
        return match($directorUpdate->action_type) {
            'director_comment' => 'Comentário',
            'director_validation_approved' => 'Aprovado',
            'director_validation_rejected' => 'Rejeitado',
            'director_validation_needs_revision' => 'Revisão Solicitada',
            'director_assumed_case' => 'Assumiu o Caso',
            default => 'Intervenção'
        };
    }
    
    return null;
}

/**
 * Criar PDF de erro para Director
 */
private function createDirectorErrorPdf($errorMessage, $user)
{
    $data = [
        'title' => 'Erro ao Exportar Relatório',
        'subtitle' => 'Director: ' . $user->name,
        'user' => $user,
        'user_name' => $user->name,
        'export_date' => now()->format('d/m/Y H:i'),
        'error_message' => $errorMessage,
        'is_error' => true,
    ];
    
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.director-submissions-pdf', $data);
    $pdf->setPaper('A4', 'portrait');
    
    $filename = 'erro-exportacao-director-' . now()->format('Y-m-d-H-i') . '.pdf';
    return $pdf->download($filename);
}

/**
 * Verificar dados para exportação (para debug)
 */
public function checkExportData(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    try {
        \Log::info('Director - Check export data', [
            'user_id' => $user->id,
            'request_params' => $request->all()
        ]);
        
        $query = Grievance::with(['user', 'assignedUser'])
            ->latest();
        
        $this->applyReportFilters($query, $request);
        
        $grievances = $query->get();
        
        // Calcular estatísticas básicas
        $stats = [
            'total' => $grievances->count(),
            'by_type' => $grievances->groupBy('type')->map->count()->toArray(),
            'by_status' => $grievances->groupBy('status')->map->count()->toArray(),
            'by_priority' => $grievances->groupBy('priority')->map->count()->toArray(),
            'escalated_count' => $grievances->where('escalated', true)->count(),
        ];
        
        return response()->json([
            'success' => true,
            'count' => $grievances->count(),
            'statistics' => $stats,
            'filters' => $request->all(),
            'sample_data' => $grievances->count() > 0 ? [
                'first_grievance' => [
                    'id' => $grievances->first()->id,
                    'reference' => $grievances->first()->reference_number,
                    'type' => $grievances->first()->type,
                    'status' => $grievances->first()->status,
                    'priority' => $grievances->first()->priority,
                    'escalated' => $grievances->first()->escalated,
                ]
            ] : null
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erro em checkExportData do Director: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao verificar dados: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Exportar lista simples de submissões (sem estatísticas)
 */
public function exportSimpleList(Request $request)
{
    $user = $request->user();
    $this->checkAccess($user);
    
    try {
        $query = Grievance::with(['user', 'assignedUser', 'project'])
            ->latest();
        
        $this->applyReportFilters($query, $request);
        
        $grievances = $query->get();
        
        // Determinar título baseado na tab
        $tab = $request->input('tab', 'all');
        $title = $this->getExportTitleForDirector($tab);
        
        $data = [
            'title' => $title,
            'subtitle' => 'Director: ' . $user->name,
            'user' => $user,
            'user_name' => $user->name,
            'export_date' => now()->format('d/m/Y H:i'),
            'period' => $this->getReportPeriod($request),
            'total_grievances' => $grievances->count(),
            'grievances' => $grievances->map(function ($grievance) {
                return [
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'type' => $this->getTypeLabelForExport($grievance->type),
                    'priority' => $this->getPriorityLabel($grievance->priority),
                    'status' => $this->getStatusText($grievance->status),
                    'category' => $grievance->category ?? 'N/A',
                    'created_at' => $grievance->created_at->format('d/m/Y H:i'),
                    'user_name' => $grievance->user ? $grievance->user->name : 'Anônimo',
                    'technician' => $grievance->assignedUser ? $grievance->assignedUser->name : 'Não atribuído',
                    'project' => $grievance->project ? $grievance->project->name : 'N/A',
                    'escalated' => $grievance->escalated ? 'Sim' : 'Não',
                    'has_director_intervention' => $this->hasDirectorIntervention($grievance),
                ];
            })->toArray(),
            'filters_applied' => $this->getAppliedFilters($request),
            'is_simple_list' => true,
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.director-submissions-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'lista-submissoes-director-' . now()->format('Y-m-d-H-i') . '.pdf';
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao exportar lista simples do Director: ' . $e->getMessage());
        return $this->createDirectorErrorPdf('Erro ao gerar lista: ' . $e->getMessage(), $user);
    }
}
}