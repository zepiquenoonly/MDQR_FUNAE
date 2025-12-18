<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Grievance;
use App\Models\GrievanceUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\StatisticsExport;
use Inertia\Inertia;
use Inertia\Response;

class ManagerGrievanceController extends Controller
{
    /**
     * Display the manager dashboard with grievances
     */
   public function index(Request $request): Response
{
    $user = auth()->user();
    $this->ensureManager($user);

    $query = Grievance::with([
            'user', 
            'assignedUser',
            'escalatedBy',
            'updates.user.roles'
        ])
        ->where(function($q) use ($user) {
            // Reclamações atribuídas ao gestor OU que foram escaladas por ele
            $q->where('assigned_to', $user->id)
              ->orWhereHas('updates', function($q2) use ($user) {
                  $q2->where('user_id', $user->id)
                     ->whereIn('action_type', ['manager_comment', 'manager_approved', 'manager_rejected']);
              });
        })
        ->latest();

    // Aplicar filtros
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('description', 'like', '%' . $request->search . '%')
              ->orWhere('reference_number', 'like', '%' . $request->search . '%')
              ->orWhereHas('user', function ($q) use ($request) {
                  $q->where('name', 'like', '%' . $request->search . '%');
              });
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }

    $grievances = $query->get();

    // Formatar os dados para incluir informações de intervenção do director
    $formattedGrievances = $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    });

    // **CORREÇÃO CRÍTICA: Obter dados específicos ANTES de passar para a view**
    $directorInterventionsData = $this->getDirectorInterventionsData($user);
    $mySubmissionsData = $this->getMySubmissionsToDirectorData($user);
    
    // Calcular contadores específicos para o frontend
    $allGrievances = Grievance::where('assigned_to', $user->id)->get();
    
    $suggestionsCount = $allGrievances->filter(function($item) {
        return $item->type === 'suggestion' || str_contains(strtolower($item->type), 'sugest');
    })->count();
    
    $grievancesCount = $allGrievances->filter(function($item) {
        return $item->type === 'grievance' || str_contains(strtolower($item->type), 'queixa');
    })->count();
    
    $complaintsCount = $allGrievances->filter(function($item) {
        return $item->type === 'complaint' || str_contains(strtolower($item->type), 'reclam');
    })->count();
    
    // **USAR OS DADOS REAIS DOS MÉTODOS ESPECÍFICOS**
    $directorInterventionsCount = count($directorInterventionsData);
    $mySubmissionsToDirectorCount = count($mySubmissionsData);

    return Inertia::render('Manager/GrievanceDetail', [
        'grievances' => $formattedGrievances,
        'allComplaints' => $formattedGrievances,
        
        'recentSubmissions' => $formattedGrievances->take(4)->values(),
        
        // **CRÍTICO: Passar os dados específicos com nomes CORRETOS**
        'director_interventions' => $directorInterventionsData,
        'my_submissions_to_director' => $mySubmissionsData,
        
        'filters' => $request->only(['search', 'status', 'priority', 'director_interventions']),
        'counts' => [
            'suggestions' => $suggestionsCount,
            'grievances' => $grievancesCount,
            'complaints' => $complaintsCount,
            'director_interventions' => $directorInterventionsCount,
            'manager_requests' => 0, // Para compatibilidade, mas não usado por manager
            'my_submissions_to_director' => $mySubmissionsToDirectorCount,
            'total' => $allGrievances->count()
        ],
        'debug_info' => [
            'user_id' => $user->id,
            'user_role' => $user->getRoleNames()->first(),
            'director_interventions_filtered' => $request->boolean('director_interventions', false),
            'recent_submissions_count' => $formattedGrievances->take(4)->count(),
            // Adicionar debug dos dados específicos
            'director_interventions_count' => count($directorInterventionsData),
            'my_submissions_count' => count($mySubmissionsData),
        ]
    ]);
}

    /**
     * Marcar submissão como vista e atualizar status para "Em Análise"
     */
    public function markAsSeen(Request $request, $id)
    {
        $user = auth()->user();
        $this->ensureManager($user);
        
        try {
            $grievance = Grievance::findOrFail($id);
            
            // Verificar se a submissão está atribuída a este gestor
            if ($grievance->assigned_to !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para visualizar esta submissão'
                ], 403);
            }
            
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
                    'description' => 'Submissão marcada como "Em Análise" pelo Gestor',
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
     * Display the specified grievance
     */
    public function show(Request $request, $identifier): Response
    {
        $user = auth()->user();
        
        try {
            // Buscar a reclamação por ID ou número de referência
            if (is_numeric($identifier)) {
                $grievance = Grievance::findOrFail($identifier);
            } else {
                $grievance = Grievance::where('reference_number', $identifier)->firstOrFail();
            }
            
            // **SE A SUBMISSÃO É NOVA (status "submitted"), ATUALIZAR PARA "EM ANÁLISE"**
            if ($grievance->status === 'submitted' && $grievance->assigned_to === $user->id) {
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
                    'description' => 'Submissão visualizada pelo Gestor e marcada como "Em Análise"',
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
            
            // Carregar relações necessárias
            $grievance->load([
                'user',
                'assignedUser',
                'escalatedBy',
                'project',
                'attachments',
                'updates.user.roles'
            ]);
            
            // Formatar dados para o frontend
            $formattedGrievance = $this->formatGrievanceForShow($grievance);
            
            // Obter técnicos disponíveis para reatribuição
            $technicians = $this->getAvailableTechnicians();
            
            // Obter comentários/atualizações formatados
            $comments = $this->formatCommentsForShow($grievance, $user);
            
            return Inertia::render('Director/Show', [
                'submission' => $formattedGrievance,
                'complaint' => $formattedGrievance,
                'comments' => $comments,
                'technicians' => $technicians,
                'projects' => $this->getActiveProjects(),
                'managers' => $this->getAvailableManagers(),
                'timeline_data' => $grievance->updates->sortByDesc('created_at')->values()->toArray(),
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first(),
                ] : null,
                'user_role' => $user->getRoleNames()->first(),
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Reclamação não encontrada.');
        }
    }
/**
 * Formatar comentários para o Show.vue
 */
private function formatCommentsForShow($grievance, $user): array
{
    return $grievance->updates
        ->filter(function ($update) use ($user) {
            // Determinar se o comentário é visível para o gestor
            return $this->isCommentVisibleToManager($update, $user);
        })
        ->sortByDesc('created_at')
        ->values()
        ->map(function ($update) use ($user) {
            // Determinar tipo de comentário
            $commentType = $this->getCommentTypeForManager($update, $user);
            
            return [
                'id' => $update->id,
                'content' => $update->comment ?? $update->description,
                'type' => $commentType,
                'action_type' => $update->action_type,
                'created_at' => $update->created_at->toISOString(),
                'user' => $update->user ? [
                    'name' => $update->user->name,
                    'email' => $update->user->email,
                    'role' => $update->user->getRoleNames()->first(),
                ] : null,
                'metadata' => $update->metadata ?? [],
                'is_visible_to_manager' => true, // Adicionar flag
            ];
        })
        ->toArray();
}


/**
 * Formatar grievance para o Show.vue
 */
private function formatGrievanceForShow($grievance): array
{
    return [
        'id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'description' => $grievance->description,
        'subject' => $grievance->description, // Para compatibilidade
        'title' => $grievance->description,   // Para compatibilidade
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
        
        // Informações do escalamento detalhadas
        'escalation_details' => [
            'escalated' => $grievance->escalated,
            'escalated_at' => $grievance->escalated_at?->toISOString(),
            'escalation_reason' => $grievance->escalation_reason,
            'escalated_by' => $grievance->escalatedBy ? [
                'id' => $grievance->escalatedBy->id,
                'name' => $grievance->escalatedBy->name,
                'email' => $grievance->escalatedBy->email,
            ] : null,
        ],
        
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
        
        'technician' => $grievance->assignedUser ? [ // Para compatibilidade com ComplaintRow
            'id' => $grievance->assignedUser->id,
            'name' => $grievance->assignedUser->name,
            'email' => $grievance->assignedUser->email,
        ] : null,
        
        'escalated_by' => $grievance->escalatedBy ? [
            'id' => $grievance->escalatedBy->id,
            'name' => $grievance->escalatedBy->name,
        ] : null,
        
        'project' => $grievance->project ? [
            'id' => $grievance->project->id,
            'name' => $grievance->project->name,
        ] : null,
        
        'department' => $grievance->project ? [
            'name' => $grievance->project->category ?? 'N/A',
        ] : null,
        
        // Anexos
       'attachments' => $grievance->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'name' => $attachment->original_filename, // <-- Use 'name' em vez de 'original_filename'
                'size' => $attachment->size, // <-- Use o número, não formatado
                'path' => $attachment->path,
                'url' => url($attachment->path), // <-- URL para visualização
                'download_url' => route('attachments.download', $attachment), // <-- URL para download
                'mime_type' => $attachment->mime_type,
                'type' => $attachment->type,
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
        
        // Atividades para timeline
        'activities' => $grievance->updates->map(function ($update) {
            return [
                'id' => $update->id,
                'type' => $update->action_type,
                'description' => $this->formatActivityDescription($update),
                'created_at' => $update->created_at->toISOString(),
                'user' => $update->user ? [
                    'name' => $update->user->name,
                ] : null,
            ];
        })->sortByDesc('created_at')->values()->toArray(),
        
        'metadata' => $grievance->metadata ?? [],
        
        // Validação do director se existir
        'director_validation' => $grievance->metadata && isset($grievance->metadata['director_validation']) 
            ? $grievance->metadata['director_validation'] 
            : null,
    ];
}


private function isCommentVisibleToManager($update, $user): bool
{
    // Se o gestor criou o comentário, pode ver
    if ($update->user_id === $user->id) {
        return true;
    }
    
    // Se for do director, sempre visível para gestor
    if ($update->user && $update->user->hasRole('Director')) {
        return true;
    }
    
    // Se for de um técnico atribuído ao gestor, pode ver
    if ($update->user && $update->user->hasRole('Técnico')) {
        // Verificar se o técnico está atribuído a este gestor
        // (Você pode precisar ajustar esta lógica baseado na sua estrutura)
        return true;
    }
    
    // Se for de outro gestor, não mostrar (a menos que seja público)
    if ($update->user && $update->user->hasRole('Gestor')) {
        return $update->is_public || ($update->metadata['is_public'] ?? false);
    }
    
    // Por padrão, mostrar se for público
    return $update->is_public || ($update->metadata['is_public'] ?? false);
}


private function getCommentTypeForManager($update, $user): string
{
    $actionType = $update->action_type;
    $metadata = $update->metadata ?? [];
    
    // Se for do director
    if ($update->user && $update->user->hasRole('Director')) {
        // Se for uma validação do director
        if (str_contains($actionType, 'director_validation')) {
            return 'director_validation';
        }
        // Se for comentário do director
        if ($actionType === 'director_comment') {
            return 'director_comment';
        }
        return 'director';
    }
    
    // Se for público
    if ($update->is_public || ($metadata['is_public'] ?? false)) {
        return 'public';
    }
    
    // Se for interno
    return 'internal';
}


private function formatActivityDescription(GrievanceUpdate $update): string
{
    switch ($update->action_type) {
        case 'status_changed':
            $oldStatus = $this->getStatusText($update->old_value);
            $newStatus = $this->getStatusText($update->new_value);
            return "Estado alterado de '{$oldStatus}' para '{$newStatus}'";
        
        case 'priority_changed':
            $oldPriority = $this->getPriorityLabel($update->old_value);
            $newPriority = $this->getPriorityLabel($update->new_value);
            return "Prioridade alterada de '{$oldPriority}' para '{$newPriority}'";
        
        case 'technician_assigned':
        case 'assigned':
            return "Caso atribuído";
        
        case 'created':
            return "Submissão criada";
        
        case 'escalated_to_director':
            return "Submissão reencaminhada ao Director";
            
        case 'director_validation_approved':
            return "Director aprovou a submissão";
            
        case 'director_validation_rejected':
            return "Director rejeitou a submissão";
            
        case 'director_validation_needs_revision':
            return "Director solicitou revisão";
            
        case 'director_comment':
            return "Director adicionou um comentário";
            
        default:
            return $update->description ?? ucfirst(str_replace('_', ' ', $update->action_type));
    }
}



private function getPriorityLabel(?string $priority): string
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
 * Obter tipo de comentário
 */
private function getCommentType($update): string
{
    $actionType = $update->action_type;
    $metadata = $update->metadata ?? [];
    
    if ($actionType === 'director_comment' || $actionType === 'director_validation') {
        if (isset($metadata['is_public']) && $metadata['is_public'] === true) {
            return 'public';
        }
        if (isset($metadata['comment_type']) && $metadata['comment_type'] === 'director_only') {
            return 'director_only';
        }
        return 'internal';
    }
    
    if ($update->is_public || (isset($metadata['is_public']) && $metadata['is_public'] === true)) {
        return 'public';
    }
    
    return 'internal';
}

/**
 * Obter técnicos disponíveis
 */
private function getAvailableTechnicians(): array
{
    return User::role('Técnico')
        ->where('is_available', true)
        ->select('id', 'name', 'email', 'current_workload', 'workload_capacity')
        ->get()
        ->map(function ($technician) {
            return [
                'id' => $technician->id,
                'name' => $technician->name,
                'email' => $technician->email,
                'workload' => $technician->current_workload . '/' . $technician->workload_capacity,
                'available_percentage' => $technician->workload_capacity > 0 
                    ? round((($technician->workload_capacity - $technician->current_workload) / $technician->workload_capacity) * 100)
                    : 100,
            ];
        })
        ->toArray();
}

/**
 * Verificar se pode editar a reclamação
 */
private function canEditGrievance($user, $grievance): bool
{
    // Gestor pode editar se estiver atribuído a ele
    if ($user->hasRole('Gestor')) {
        return $grievance->assigned_to === $user->id;
    }
    
    // Director pode sempre editar
    if ($user->hasRole('Director')) {
        return true;
    }
    
    return false;
}

/**
 * Verificar se pode reatribuir a reclamação
 */
private function canReassignGrievance($user, $grievance): bool
{
    // Apenas gestor pode reatribuir
    return $user->hasRole('Gestor') && $grievance->assigned_to === $user->id;
}

/**
 * Verificar se pode enviar para director
 */
private function canSendToDirector($user, $grievance): bool
{
    // Apenas gestor pode enviar para director
    if (!$user->hasRole('Gestor')) {
        return false;
    }
    
    // Não pode enviar se já foi escalado
    if ($grievance->escalated) {
        return false;
    }
    
    // Pode enviar casos críticos ou que necessitam de atenção especial
    return in_array($grievance->priority, ['high', 'critical']) || 
           $grievance->status === 'pending';
}

/**
 * Formatar bytes para tamanho legível
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
 * Verificar se tem intervenção do director
 */
private function hasDirectorIntervention($grievance): bool
{
    return $grievance->escalated === true ||
           ($grievance->metadata && isset($grievance->metadata['is_escalated_to_director']) && 
            $grievance->metadata['is_escalated_to_director'] === true) ||
           $grievance->updates->contains(function ($update) {
               return $update->user && $update->user->hasRole('Director');
           }) ||
           ($grievance->metadata && isset($grievance->metadata['director_validation']));
}



public function getRecentSubmissions(Request $request)
{
    $user = auth()->user();
    $this->ensureManager($user);
    
    // Obter as 4 submissões mais recentes atribuídas ao gestor
    $query = Grievance::with(['user', 'assignedUser', 'updates.user.roles'])
        ->where('assigned_to', $user->id)
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
 * Obter todas as submissões para API (Manager)
 */
public function getAllSubmissions(Request $request)
{
    $user = auth()->user();
    $this->ensureManager($user);
    
    // Manager vê apenas submissões atribuídas a ele
    $query = Grievance::with(['user', 'assignedUser', 'updates.user.roles'])
        ->where(function($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhereHas('updates', function($q2) use ($user) {
                  $q2->where('user_id', $user->id)
                     ->whereIn('action_type', ['manager_comment', 'manager_approved', 'manager_rejected']);
              });
        })
        ->latest();
    
    // Aplicar filtros se fornecidos
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('description', 'like', '%' . $request->search . '%')
              ->orWhere('reference_number', 'like', '%' . $request->search . '%')
              ->orWhereHas('user', function ($q) use ($request) {
                  $q->where('name', 'like', '%' . $request->search . '%');
              });
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }
    
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

    /**
     * Format grievance data for the list view
     */
   private function formatGrievanceForList(Grievance $grievance): array
{
    \Log::info("📋 Formatting grievance for list: {$grievance->id}");
    
    // **CRÍTICO: Garantir que updates sejam carregados**
    if (!$grievance->relationLoaded('updates')) {
        $grievance->load(['updates.user.roles']);
    }
    
    // Inicializar variáveis
    $hasDirectorIntervention = false;
    $directorUpdates = [];
    $directorCommentsCount = 0;
    $directorInterventions = [];
    $directorValidation = null;
    
    // **1. Verificar se foi escalado para director**
    $isEscalatedToDirector = $grievance->escalated || 
                            ($grievance->metadata && 
                             isset($grievance->metadata['is_escalated_to_director']) && 
                             $grievance->metadata['is_escalated_to_director'] === true);
    
    if ($isEscalatedToDirector) {
        $hasDirectorIntervention = true;
        
        $directorInterventions[] = [
            'type' => 'escalation',
            'action_type' => 'escalated_to_director',
            'escalated_at' => $grievance->escalated_at?->toISOString(),
            'escalated_by' => $grievance->escalatedBy ? [
                'name' => $grievance->escalatedBy->name,
                'role' => 'Gestor',
            ] : null,
            'escalation_reason' => $grievance->escalation_reason,
            'metadata' => [
                'escalated' => true,
                'escalation_reason' => $grievance->escalation_reason,
            ],
        ];
        
        \Log::info("  - É escalado para director");
    }
    
    // **2. Verificar updates do director**
    foreach ($grievance->updates as $update) {
        $isDirectorUpdate = false;
        
        // Verificar pelo action_type
        $directorActionTypes = [
            'director_comment',
            'director_validation_approved', 
            'director_validation_rejected',
            'director_validation_needs_revision',
            'escalated_to_director'
        ];
        
        if (in_array($update->action_type, $directorActionTypes)) {
            $isDirectorUpdate = true;
        }
        
        // Verificar se o usuário é director
        if ($update->user && $update->user->hasRole('Director')) {
            $isDirectorUpdate = true;
        }
        
        // Verificar metadados
        if ($update->metadata) {
            if (isset($update->metadata['created_by_director']) && $update->metadata['created_by_director'] === true) {
                $isDirectorUpdate = true;
            }
            
            if (isset($update->metadata['director_intervention']) && $update->metadata['director_intervention'] === true) {
                $isDirectorUpdate = true;
            }
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
            
            \Log::info("  - Tem update do director: {$update->action_type}");
        }
    }
    
    // **3. Verificar validação do director no metadata**
    if ($grievance->metadata && isset($grievance->metadata['director_validation'])) {
        $hasDirectorIntervention = true;
        $directorValidation = $grievance->metadata['director_validation'];
        
        $directorInterventions[] = [
            'type' => 'validation',
            'action_type' => 'director_validation',
            'status' => $directorValidation['status'] ?? null,
            'comment' => $directorValidation['comment'] ?? null,
            'validated_by' => $directorValidation['validated_by_name'] ?? null,
            'validated_at' => $directorValidation['validated_at'] ?? null,
            'metadata' => $directorValidation ?? [],
        ];
        
        \Log::info("  - Tem validação do director");
    }
    
    // Formatar os dados básicos da reclamação
    $formatted = [
        'id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'title' => $grievance->description ? (strlen($grievance->description) > 50 ? substr($grievance->description, 0, 50) . '...' : $grievance->description) : 'Sem título',
        'description' => $grievance->description,
        'type' => $grievance->type,
        'priority' => $grievance->priority,
        'status' => $grievance->status,
        'category' => $grievance->category,
        'created_at' => $grievance->created_at->toISOString(),
        'submitted_at' => $grievance->submitted_at?->toISOString(),
        'province' => $grievance->province,
        'district' => $grievance->district,
        'assigned_to' => $grievance->assigned_to,
        
        // **CAMPOS CRÍTICOS - INICIALIZAR COM VALORES PADRÃO**
        'has_director_intervention' => false, // Default
        'escalated' => (bool) $grievance->escalated,
        'is_escalated_to_director' => (bool) $grievance->escalated,
        'escalation_reason' => $grievance->escalation_reason,
        'escalated_at' => $grievance->escalated_at?->toISOString(),
        'escalated_by' => $grievance->escalated_by,
        
        // Arrays
        'director_updates' => [],
        'director_interventions' => [],
        'director_comments_count' => 0,
        'director_validation' => null,
        
        'metadata' => $grievance->metadata ?? [],
        
        // Relacionamentos
        'user' => $grievance->user ? [
            'name' => $grievance->user->name,
            'email' => $grievance->user->email,
        ] : null,
        
        'technician' => $grievance->assignedUser ? [
            'id' => $grievance->assignedUser->id,
            'name' => $grievance->assignedUser->name,
            'email' => $grievance->assignedUser->email,
        ] : null,
        
        'assigned_to_user' => $grievance->assignedUser ? [
            'id' => $grievance->assignedUser->id,
            'name' => $grievance->assignedUser->name,
        ] : null,
        
        // Campos para compatibilidade
        'updates' => [],
        'activities' => [],
    ];
    
    
    \Log::info("  - Resultado final:", [
        'has_director_intervention' => $hasDirectorIntervention,
        'director_updates_count' => count($directorUpdates),
        'director_interventions_count' => count($directorInterventions),
        'is_escalated_to_director' => $isEscalatedToDirector
    ]);
    
    return $formatted;
}



public function checkDirectorInterventions()
{
    $user = auth()->user();
    $this->ensureManager($user);
    
    
    // 1. Check grievances assigned to this manager
    $assignedGrievances = Grievance::where('assigned_to', $user->id)->count();
    
    // 2. Check escalated grievances
    $escalatedGrievances = Grievance::where('escalated', true)
        ->where('assigned_to', $user->id)
        ->count();
    
    // 3. Check grievances with director updates
    $grievancesWithDirectorUpdates = Grievance::whereHas('updates', function($query) {
        $query->whereIn('action_type', [
            'director_comment',
            'director_validation_approved',
            'director_validation_rejected',
            'director_validation_needs_revision',
            'escalated_to_director'
        ]);
    })->where('assigned_to', $user->id)->count();

    
    // 4. Get detailed list
    $grievances = Grievance::with(['updates' => function($query) {
        $query->whereIn('action_type', [
            'director_comment',
            'director_validation_approved',
            'director_validation_rejected',
            'director_validation_needs_revision',
            'escalated_to_director'
        ]);
    }])
    ->where('assigned_to', $user->id)
    ->get();
    
    $detailedList = [];
    foreach ($grievances as $grievance) {
        $detailedList[] = [
            'id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'escalated' => $grievance->escalated,
            'director_updates_count' => $grievance->updates->count(),
            'updates' => $grievance->updates->map(function($update) {
                return [
                    'action_type' => $update->action_type,
                    'description' => $update->description,
                    'comment' => $update->comment,
                    'user_id' => $update->user_id,
                ];
            })->toArray()
        ];
    }
    
    return response()->json([
        'message' => 'Director interventions check',
        'stats' => [
            'assigned_grievances' => $assignedGrievances,
            'escalated_grievances' => $escalatedGrievances,
            'grievances_with_director_updates' => $grievancesWithDirectorUpdates,
        ],
        'detailed_list' => $detailedList
    ]);
}

    /**
     * Update priority for a grievance.
     */
    public function updatePriority(Request $request, Grievance $grievance): RedirectResponse
{
    // Permitir tanto gestor quanto director
    $user = $request->user();
    
    if (!$user->hasAnyRole(['Gestor', 'Director'])) {
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => 'Acesso não autorizado.']);
        }
        return response()->json([
            'success' => false,
            'message' => 'Acesso não autorizado. Apenas Gestor ou Director podem atualizar prioridades.'
        ], 403);
    }

    $data = $request->validate([
        'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])], // Adicionado 'urgent'
    ]);

    $oldPriority = $grievance->priority;
    
    $grievance->update(['priority' => $data['priority']]);

    // Determinar quem está fazendo a alteração
    $changedBy = $user->hasRole('Director') ? 'Director' : 'Gestor';
    
    // Registrar a alteração
    GrievanceUpdate::log(
        grievanceId: $grievance->id,
        actionType: 'priority_changed',
        userId: $user->id,
        description: "Prioridade alterada pelo {$changedBy}",
        oldValue: $oldPriority,
        newValue: $data['priority'],
        metadata: [
            'changed_by' => $changedBy,
            'changed_by_id' => $user->id,
            'changed_by_name' => $user->name,
        ],
        isPublic: true
    );

    // Log para auditoria
    \Log::info('Prioridade atualizada', [
        'grievance_id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'user_id' => $user->id,
        'user_role' => $user->roles->first()->name ?? 'N/A',
        'old_priority' => $oldPriority,
        'new_priority' => $data['priority']
    ]);

    if ($request->header('X-Inertia')) {
        return back()->with('success', 'Prioridade atualizada com sucesso.');
    }

    return response()->json([
        'success' => true,
        'message' => 'Prioridade atualizada com sucesso.',
        'grievance' => [
            'id' => $grievance->id,
            'priority' => $grievance->priority,
            'priority_label' => $this->getPriorityLabel($grievance->priority),
        ]
    ]);
}

    /**
     * Reassign grievance to another technician.
     */
    public function reassign(Request $request, Grievance $grievance)
{
    $user = $request->user();
    
    // Permitir tanto gestor quanto director
    if (!$user->hasAnyRole(['Gestor', 'Director'])) {
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => 'Acesso não autorizado.']);
        }
        return response()->json([
            'success' => false,
            'message' => 'Acesso não autorizado. Apenas Gestor ou Director podem reatribuir técnicos.'
        ], 403);
    }

    // Validação
    $validated = $request->validate([
        'technician_id' => [
            'required',
            'integer',
            Rule::exists('users', 'id'),
        ],
    ]);

    try {
        DB::beginTransaction();

        // Buscar usuário
        $user = User::find($validated['technician_id']);
        
        if (!$user) {    

            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], 422);
        }

        // Verificar se o usuário tem role 'Técnico'
        if (!$user->hasRole('Técnico')) {
            return response()->json([
                'success' => false,
                'message' => 'O usuário selecionado não é um técnico.'
            ], 422);
        }

        $technician = $user;
        $previousTechnician = $grievance->assigned_to;
        $assignedBy = $request->user();

        // Atualizar grievance
        $grievance->assigned_to = $technician->id;
        $grievance->assigned_at = now();
        $grievance->status = 'assigned';
        $grievance->save();

        // Criar atividade de reatribuição
        try {
            $update = GrievanceUpdate::create([
                'grievance_id' => $grievance->id,
                'user_id' => $assignedBy->id,
                'action_type' => 'technician_assigned',
                'description' => "Técnico reatribuído por {$assignedBy->name} ({$assignedBy->roles->first()->name})",
                'metadata' => [
                    'previous_technician_id' => $previousTechnician,
                    'new_technician_id' => $technician->id,
                    'new_technician_name' => $technician->name,
                    'assigned_by' => $assignedBy->roles->first()->name,
                    'assigned_by_id' => $assignedBy->id,
                    'assigned_by_name' => $assignedBy->name,
                ],
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        } catch (\Exception $e) {
            \Log::warning('Não foi possível registrar atividade', [
                'error' => $e->getMessage(),
                'grievance_id' => $grievance->id
            ]);
        }

        DB::commit();

        // Determinar tipo de resposta
        if ($request->header('X-Inertia')) {
            return back()->with([
                'success' => 'Técnico reatribuído com sucesso.',
                'updatedTechnician' => [
                    'id' => $technician->id,
                    'name' => $technician->name,
                    'email' => $technician->email,
                ]
            ]);
        }

        // Para API/JSON
        return response()->json([
            'success' => true,
            'message' => 'Técnico reatribuído com sucesso.',
            'technician' => [
                'id' => $technician->id,
                'name' => $technician->name,
                'email' => $technician->email,
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
    
        $errorMessage = 'Erro ao reatribuir técnico: ' . $e->getMessage();
        
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => $errorMessage]);
        }

        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ], 500);
    }
}

public function rejectSubmission(Request $request, Grievance $grievance)
{
    $this->ensureManager($request->user());

    // Verificar se é uma requisição Inertia ou API
    $isInertiaRequest = $request->header('X-Inertia');
    $isAjaxRequest = $request->ajax() || $request->wantsJson();

    try {
        // Validação atualizada
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'reason_value' => ['required', 'string'], // Adicionar reason_value
            'internal_comment' => ['nullable', 'string', 'max:1000'],
            'rejection_type' => ['required', 'in:duplicate_submission,outside_scope,insufficient_evidence,unclear_description,already_resolved,inappropriate_content,other'],
            'notify_user' => ['required'], // Apenas required
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:10240'],
        ]);

        // Converter notify_user para booleano
        $notifyUser = $validated['notify_user'];
        if (is_string($notifyUser)) {
            $validated['notify_user'] = in_array(strtolower($notifyUser), ['true', '1', 'yes']);
        } elseif (is_numeric($notifyUser)) {
            $validated['notify_user'] = (bool) $notifyUser;
        }

        DB::beginTransaction();

        // Mapear os motivos para labels mais amigáveis
        $reasonLabels = [
            'duplicate_submission' => 'Submissão Duplicada',
            'outside_scope' => 'Fora do Âmbito do Projecto',
            'insufficient_evidence' => 'Evidências Insuficientes',
            'unclear_description' => 'Descrição Pouco Clara',
            'already_resolved' => 'Já Resolvido',
            'inappropriate_content' => 'Conteúdo Inapropriado',
            'other' => 'Outro motivo',
        ];

        // Usar a label enviada ou buscar do mapeamento
        $reasonLabelForUser = $validated['reason']; // Já vem a label do frontend
        $reasonValueForSystem = $validated['rejection_type'] ?? $validated['reason_value'];
        
        // Se a reason for muito longa (pode ser o comentário), usar o mapeamento
        if (strlen($reasonLabelForUser) > 50) {
            $reasonLabelForUser = $reasonLabels[$reasonValueForSystem] ?? 'Motivo não especificado';
        }

        // Atualizar status da reclamação
        $previousStatus = $grievance->status;
        
        $grievance->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
            'rejection_reason' => $reasonLabelForUser, // Usar a label amigável
            'metadata' => array_merge($grievance->metadata ?? [], [
                'rejection_details' => [
                    'reason_for_user' => $reasonLabelForUser, // Label para utente
                    'reason_for_system' => $reasonValueForSystem, // Valor interno
                    'rejection_type' => $validated['rejection_type'],
                    'internal_comment' => $validated['internal_comment'] ?? null,
                    'rejected_by' => $request->user()->id,
                    'rejected_by_name' => $request->user()->name,
                    'rejected_at' => now()->toIso8601String(),
                    'notify_user' => $validated['notify_user'],
                    'attachments_count' => $request->hasFile('attachments') ? count($request->file('attachments')) : 0,
                ]
            ])
        ]);

        // *** CRÍTICO: Criar um update específico para o motivo da rejeição ***
        // Este será visível ao utente na página de rastreamento
        GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'action_type' => 'rejection_reason',
            'description' => 'Motivo da rejeição',
            'comment' => $reasonLabelForUser, // Usar a LABEL amigável para o utente
            'metadata' => [
                'is_public' => true,
                'visible_to_user' => true,
                'rejection_type' => $reasonValueForSystem, // Valor interno
                'rejection_label' => $reasonLabelForUser, // Label amigável
                'rejected_by_name' => $request->user()->name,
            ],
            'is_public' => true, // TORNAR VISÍVEL AO UTENTE
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Registrar atividade de rejeição (para histórico interno)
        GrievanceUpdate::log(
            grievanceId: $grievance->id,
            actionType: 'submission_rejected',
            userId: $request->user()->id,
            description: 'Submissão rejeitada pelo gestor',
            comment: null,
            metadata: [
                'rejection_type' => $reasonValueForSystem,
                'rejection_label' => $reasonLabelForUser,
                'internal_comment' => $validated['internal_comment'] ?? null,
                'previous_status' => $previousStatus,
                'new_status' => 'rejected',
                'notify_user' => $validated['notify_user'],
                'is_public' => false,
            ],
            isPublic: false
        );

        // Adicionar comentário interno se fornecido (também visível ao utente)
        if (!empty($validated['internal_comment'])) {
            GrievanceUpdate::create([
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'action_type' => 'manager_comment',
                'description' => 'Comentário adicional sobre a rejeição',
                'comment' => $validated['internal_comment'],
                'metadata' => [
                    'comment_type' => 'rejection_comment',
                    'visible_to_user' => true,
                    'rejection_type' => $reasonValueForSystem,
                    'rejection_label' => $reasonLabelForUser,
                    'is_additional_comment' => true,
                ],
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Processar anexos
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachment = $this->storeAttachment($grievance, $file, 'rejection_evidence');

                GrievanceUpdate::log(
                    grievanceId: $grievance->id,
                    actionType: 'attachment_added',
                    userId: $request->user()->id,
                    description: 'Evidência de rejeição adicionada',
                    metadata: [
                        'attachment_id' => $attachment->id,
                        'filename' => $attachment->original_filename,
                        'purpose' => 'rejection_evidence',
                        'is_public' => false,
                    ],
                    isPublic: false
                );
            }
        }

        DB::commit();

        // Notificar o utente por email (se solicitado)
        if ($validated['notify_user']) {
            $this->sendRejectionNotification($grievance, [
                'reason_label' => $reasonLabelForUser,
                'reason_value' => $reasonValueForSystem,
                'comment' => $validated['internal_comment'] ?? $validated['comment'] ?? null,
                'rejected_by_name' => $request->user()->name,
            ]);
        }

        // DECISÃO: Se for requisição Inertia, redirecionar com flash message
        if ($isInertiaRequest) {
            return redirect()->back()->with([
                'success' => 'Submissão rejeitada com sucesso!',
                'updatedGrievance' => [
                    'id' => $grievance->id,
                    'status' => 'rejected',
                    'rejection_reason' => $reasonLabelForUser, // Label amigável
                    'rejected_at' => now()->toIso8601String(),
                    'metadata' => $grievance->metadata,
                ]
            ]);
        }

        // Para requisições AJAX/JSON
        return response()->json([
            'success' => true,
            'message' => 'Submissão rejeitada com sucesso!',
            'grievance' => $grievance->fresh(['rejectedBy', 'updates'])
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::warning('Erro de validação na rejeição', [
            'errors' => $e->errors(),
        ]);
        
        if ($isInertiaRequest) {
            return redirect()->back()->withErrors($e->errors());
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Erro de validação',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        

        if ($isInertiaRequest) {
            return redirect()->back()->withErrors([
                'error' => 'Erro ao rejeitar submissão: ' . $e->getMessage()
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao rejeitar submissão: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Enviar notificação de rejeição ao utente
 */
/**
 * Enviar notificação de rejeição ao utente
 */
private function sendRejectionNotification(Grievance $grievance, array $data): void
{
    try {
        $userEmail = $grievance->contact_email ?? $grievance->user->email ?? null;
        
        if (!$userEmail) {
          
            return;
        }
        
        // TODO: Implementar envio de email real
        // Exemplo:
        /*
        Mail::to($userEmail)
            ->send(new GrievanceRejectedMail(
                grievance: $grievance,
                reason: $data['reason'],
                rejectionType: $data['rejection_type'],
                internalComment: $data['internal_comment'] ?? null,
                rejectedBy: auth()->user()->name
            ));
        */
        

    } catch (\Exception $e) {
        \Log::error('Erro ao preparar notificação de rejeição', [
            'grievance_id' => $grievance->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}

    /**
     * Mark grievance as resolved after manager approval.
     */
   public function markComplete(Request $request, Grievance $grievance): RedirectResponse
{
    $this->ensureManager($request->user());

    // Verificar estado atual
    if ($grievance->status !== 'pending_approval') {
        if ($request->header('X-Inertia')) {
            return back()->with('warning', 'A reclamação precisa estar pendente de aprovação para concluir.');
        }
        return response()->json([
            'success' => false,
            'message' => 'A reclamação precisa estar pendente de aprovação para concluir.'
        ], 400);
    }

    // Validação mais flexível para compatibilidade
    $data = $request->validate([
        'approval_comment' => ['nullable', 'string', 'max:2000'],
        'comment' => ['nullable', 'string', 'max:2000'], // Aceitar 'comment' também
        'attachments' => ['nullable', 'array', 'max:5'],
        'attachments.*' => ['file', 'mimes:jpeg,jpg,png,pdf,doc,docx', 'max:10240'],
        'notify_user' => ['nullable', 'boolean'],
    ]);

    DB::beginTransaction();

    try {
        // Determinar o comentário a usar
        $comment = $data['approval_comment'] ?? $data['comment'] ?? 'Aprovado pelo gestor';
        
        // Determinar se deve notificar o utente
        $notifyUser = $data['notify_user'] ?? true;

        $grievance->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $request->user()->id,
            'resolution_notes' => $grievance->resolution_notes ?: $comment,
            'metadata' => array_merge($grievance->metadata ?? [], [
                'manager_approval' => [
                    'approved_at' => now()->toISOString(),
                    'approved_by' => $request->user()->id,
                    'approved_by_name' => $request->user()->name,
                    'comment' => $comment,
                    'notify_user' => $notifyUser,
                ]
            ])
        ]);

        // Registrar atividade de aprovação
        GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'action_type' => 'manager_approved_completion',
            'description' => 'Gestor aprovou a conclusão da reclamação',
            'comment' => $comment,
            'metadata' => [
                'approved_at' => now()->toISOString(),
                'notify_user' => $notifyUser,
                'is_public' => true,
            ],
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Registrar mudança de status
        GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'action_type' => 'status_changed',
            'description' => 'Estado alterado de "Pendente de Aprovação" para "Resolvido"',
            'metadata' => [
                'old_status' => 'pending_approval',
                'new_status' => 'resolved',
                'changed_by' => $request->user()->id,
                'changed_by_name' => $request->user()->name,
                'is_public' => true,
            ],
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Processar anexos
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachment = $this->storeAttachment($grievance, $file, 'manager_resolution');

                GrievanceUpdate::create([
                    'grievance_id' => $grievance->id,
                    'user_id' => $request->user()->id,
                    'action_type' => 'attachment_added',
                    'description' => 'Evidência de resolução adicionada pelo gestor',
                    'metadata' => [
                        'attachment_id' => $attachment->id,
                        'filename' => $attachment->original_filename,
                        'purpose' => 'resolution_evidence',
                        'is_public' => true,
                    ],
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::commit();

        // Notificar o utente se solicitado
        if ($notifyUser) {
            $this->sendResolutionNotification($grievance, $comment);
        }

        // Determinar tipo de resposta
        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Reclamação marcada como resolvida. O utente será notificado automaticamente.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Reclamação marcada como resolvida.',
            'grievance' => $grievance->fresh(['updates'])
        ]);

    } catch (\Exception $exception) {
        DB::rollBack();
        
        Log::error('Erro ao concluir reclamação', [
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $errorMessage = 'Erro ao concluir reclamação: ' . $exception->getMessage();

        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => $errorMessage]);
        }

        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ], 500);
    }
}


public function rejectCompletion(Request $request, Grievance $grievance)
{
    \Log::info('=== DEBUG rejectCompletion INICIADO ===', [
        'grievance_id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'current_status' => $grievance->status,
        'user_id' => $request->user()->id,
        'user_name' => $request->user()->name,
        'user_role' => $request->user()->getRoleNames()->first(),
        'request_method' => $request->method(),
        'request_url' => $request->fullUrl(),
        'request_headers' => [
            'X-Inertia' => $request->header('X-Inertia'),
            'Content-Type' => $request->header('Content-Type'),
            'Accept' => $request->header('Accept'),
        ],
        'request_data' => $request->all(),
        'request_query_params' => $request->query->all(),
        'timestamp' => now()->toIso8601String(),
    ]);

    try {
        $this->ensureManager($request->user());
        \Log::info('DEBUG - Usuário autenticado como gestor', [
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
    } catch (\Exception $e) {
        \Log::error('DEBUG - Erro na autenticação do gestor', [
            'error' => $e->getMessage(),
            'user_id' => $request->user()->id ?? 'não autenticado',
        ]);
        throw $e;
    }

    // Verificar estado
    \Log::info('DEBUG - Verificando estado da reclamação', [
        'grievance_id' => $grievance->id,
        'current_status' => $grievance->status,
        'expected_status' => 'pending_approval',
        'is_correct_status' => $grievance->status === 'pending_approval',
        'status_details' => [
            'is_pending_approval' => $grievance->status === 'pending_approval',
            'is_in_progress' => $grievance->status === 'in_progress',
            'is_resolved' => $grievance->status === 'resolved',
            'is_approved' => $grievance->status === 'approved',
        ]
    ]);
    
    if ($grievance->status !== 'pending_approval') {
        \Log::warning('DEBUG - Estado incorreto para rejeitar conclusão', [
            'grievance_id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'expected' => 'pending_approval',
            'actual' => $grievance->status,
            'allowed_statuses' => ['pending_approval'],
            'current_metadata' => $grievance->metadata,
        ]);
        
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => 'Só pode rejeitar conclusão quando o status for "Pendente de Aprovação". Estado atual: ' . $this->getStatusText($grievance->status)]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Só pode rejeitar conclusão quando o status for "Pendente de Aprovação". Estado atual: ' . $this->getStatusText($grievance->status),
            'current_status' => $grievance->status,
            'current_status_text' => $this->getStatusText($grievance->status),
            'required_status' => 'pending_approval',
        ], 400);
    }

    // Validação flexível
    \Log::info('DEBUG - Iniciando validação dos dados', [
        'request_data_raw' => $request->all(),
        'request_files' => $request->hasFile('attachments') ? count($request->file('attachments')) : 0,
    ]);

    try {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
            'is_public' => ['required', 'boolean'],
            'notify_technician' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:10240'],
        ]);

        \Log::info('DEBUG - Dados validados com sucesso', [
            'validated_data' => $validated,
            'comment_length' => strlen($validated['comment'] ?? ''),
            'is_public_type' => gettype($validated['is_public']),
            'is_public_value' => $validated['is_public'],
            'notify_technician_value' => $validated['notify_technician'] ?? 'não definido',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('DEBUG - Erro na validação dos dados', [
            'errors' => $e->errors(),
            'request_data' => $request->all(),
            'validation_rules' => [
                'comment' => 'required|string|min:10|max:2000',
                'is_public' => 'required|boolean',
                'notify_technician' => 'nullable|boolean',
            ]
        ]);
        throw $e;
    }

    \Log::info('DEBUG - Iniciando transação de banco de dados');
    DB::beginTransaction();

    try {
        \Log::info('DEBUG - Estado anterior da reclamação', [
            'old_status' => $grievance->status,
            'old_metadata' => $grievance->metadata,
            'old_assigned_to' => $grievance->assigned_to,
            'old_updated_at' => $grievance->updated_at,
        ]);

        // Preparar dados para atualização
        $updateData = [
            'status' => 'in_progress',
            'updated_at' => now(),
        ];

        // Preparar metadata
        $metadata = $grievance->metadata ?? [];
        $metadata['completion_rejection'] = [
            'rejected_at' => now()->toISOString(),
            'rejected_by' => $request->user()->id,
            'rejected_by_name' => $request->user()->name,
            'comment' => $validated['comment'],
            'notify_technician' => $validated['notify_technician'] ?? true,
            'is_public' => $validated['is_public'],
            'previous_status' => 'pending_approval',
        ];

        $updateData['metadata'] = $metadata;

        \Log::info('DEBUG - Dados para atualização', [
            'update_data' => $updateData,
            'metadata_size' => strlen(json_encode($metadata)),
        ]);

        // Voltar para in_progress
        $result = $grievance->update($updateData);
        
        \Log::info('DEBUG - Resultado da atualização', [
            'update_result' => $result,
            'grievance_id' => $grievance->id,
            'new_status' => $grievance->fresh()->status,
            'new_metadata' => $grievance->fresh()->metadata,
            'updated_at' => $grievance->fresh()->updated_at,
            'row_count' => $result, // número de linhas afetadas
        ]);

        if (!$result) {
            \Log::error('DEBUG - Falha na atualização do registro', [
                'grievance_id' => $grievance->id,
                'update_data' => $updateData,
            ]);
            throw new \Exception('Falha ao atualizar o registro no banco de dados');
        }

        \Log::info('DEBUG - Criando registro de atividade (manager_rejected_completion)');
        
        // Adicionar comentário do gestor
        $grievanceUpdate1 = GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'action_type' => 'manager_rejected_completion',
            'description' => 'Gestor rejeitou a conclusão da submissão',
            'comment' => $validated['comment'],
            'metadata' => [
                'is_public' => $validated['is_public'],
                'rejection_type' => 'manager_rejection',
                'returned_to_technician' => true,
                'rejected_at' => now()->toIso8601String(),
                'notify_technician' => $validated['notify_technician'] ?? true,
                'created_by_manager' => true,
                'manager_id' => $request->user()->id,
                'manager_name' => $request->user()->name,
            ],
            'is_public' => $validated['is_public'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Log::info('DEBUG - Primeiro registro de atividade criado', [
            'update_id' => $grievanceUpdate1->id,
            'action_type' => $grievanceUpdate1->action_type,
            'comment_length' => strlen($grievanceUpdate1->comment ?? ''),
            'is_public' => $grievanceUpdate1->is_public,
        ]);

        \Log::info('DEBUG - Criando registro de mudança de status');
        
        // Atualizar status
        $grievanceUpdate2 = GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'action_type' => 'status_changed',
            'description' => 'Submissão devolvida ao técnico para ajustes',
            'metadata' => [
                'old_status' => 'pending_approval',
                'new_status' => 'in_progress',
                'rejection_reason' => 'Conclusão rejeitada pelo gestor',
                'is_public' => true,
                'changed_by' => $request->user()->id,
                'changed_by_name' => $request->user()->name,
                'changed_at' => now()->toISOString(),
            ],
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Log::info('DEBUG - Segundo registro de atividade criado', [
            'update_id' => $grievanceUpdate2->id,
            'action_type' => $grievanceUpdate2->action_type,
            'old_status' => $grievanceUpdate2->metadata['old_status'] ?? null,
            'new_status' => $grievanceUpdate2->metadata['new_status'] ?? null,
        ]);

        // Processar anexos se existirem
        if ($request->hasFile('attachments')) {
            $attachmentsCount = count($request->file('attachments'));
            \Log::info('DEBUG - Processando anexos', [
                'attachments_count' => $attachmentsCount,
            ]);
            
            foreach ($request->file('attachments') as $index => $file) {
                try {
                    $attachment = $this->storeAttachment($grievance, $file, 'rejection_evidence');
                    
                    GrievanceUpdate::create([
                        'grievance_id' => $grievance->id,
                        'user_id' => $request->user()->id,
                        'action_type' => 'attachment_added',
                        'description' => 'Evidência de rejeição de conclusão anexada',
                        'metadata' => [
                            'attachment_id' => $attachment->id,
                            'filename' => $attachment->original_filename,
                            'filesize' => $attachment->size,
                            'mime_type' => $attachment->mime_type,
                            'purpose' => 'rejection_evidence',
                            'is_public' => false,
                        ],
                        'is_public' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    \Log::info('DEBUG - Anexo processado', [
                        'attachment_index' => $index,
                        'attachment_id' => $attachment->id,
                        'filename' => $attachment->original_filename,
                        'filesize' => $attachment->size,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('DEBUG - Erro ao processar anexo', [
                        'attachment_index' => $index,
                        'error' => $e->getMessage(),
                        'filename' => $file->getClientOriginalName(),
                    ]);
                    // Continua processando outros anexos
                }
            }
        }

        DB::commit();
        \Log::info('DEBUG - Transação commitada com sucesso', [
            'transaction_completed' => true,
            'grievance_status_final' => $grievance->fresh()->status,
            'total_updates_created' => GrievanceUpdate::where('grievance_id', $grievance->id)->count(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Notificar o técnico se solicitado
        if ($validated['notify_technician'] ?? true) {
            \Log::info('DEBUG - Enviando notificação ao técnico', [
                'technician_id' => $grievance->assigned_to,
                'grievance_id' => $grievance->id,
            ]);
            
            $this->sendRejectionNotificationToTechnician($grievance, $validated['comment']);
        }

        // Determinar tipo de resposta
        $isInertiaRequest = $request->header('X-Inertia');
        
        \Log::info('DEBUG - Preparando resposta', [
            'is_inertia_request' => $isInertiaRequest,
            'response_type' => $isInertiaRequest ? 'inertia' : 'json',
            'grievance_final_state' => [
                'id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'status' => $grievance->status,
                'updated_at' => $grievance->updated_at,
                'assigned_to' => $grievance->assigned_to,
            ]
        ]);

        if ($isInertiaRequest) {
            return back()->with([
                'success' => 'Conclusão rejeitada. A submissão voltou para o técnico.',
                'updated_grievance' => [
                    'id' => $grievance->id,
                    'status' => $grievance->status,
                    'reference_number' => $grievance->reference_number,
                    'updated_at' => $grievance->updated_at,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conclusão rejeitada. A submissão voltou para o técnico.',
            'grievance' => [
                'id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'status' => $grievance->status,
                'status_text' => $this->getStatusText($grievance->status),
                'updated_at' => $grievance->updated_at,
                'assigned_to' => $grievance->assigned_to,
                'metadata' => $grievance->metadata,
            ],
            'updates_created' => [
                'rejection_update_id' => $grievanceUpdate1->id,
                'status_update_id' => $grievanceUpdate2->id,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('=== DEBUG rejectCompletion ERRO ===', [
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'error_trace' => $e->getTraceAsString(),
            'grievance_current_status' => $grievance->fresh() ? $grievance->fresh()->status : 'não disponível',
            'transaction_rolled_back' => true,
            'timestamp' => now()->toIso8601String(),
        ]);

        if ($request->header('X-Inertia')) {
            return back()->withErrors([
                'error' => 'Erro ao rejeitar conclusão: ' . $e->getMessage(),
                'debug_info' => [
                    'grievance_id' => $grievance->id,
                    'error' => $e->getMessage(),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao rejeitar conclusão: ' . $e->getMessage(),
            'error_details' => [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            'grievance_id' => $grievance->id,
            'timestamp' => now()->toIso8601String(),
        ], 500);
    }
}


private function sendRejectionNotificationToTechnician(Grievance $grievance, string $comment): void
{
    try {
        \Log::info('DEBUG - Iniciando envio de notificação ao técnico', [
            'grievance_id' => $grievance->id,
            'assigned_to' => $grievance->assigned_to,
            'comment_preview' => substr($comment, 0, 100) . (strlen($comment) > 100 ? '...' : ''),
        ]);

        $technician = User::find($grievance->assigned_to);
        
        if (!$technician) {
            \Log::warning('DEBUG - Técnico não encontrado para notificação', [
                'grievance_id' => $grievance->id,
                'assigned_to' => $grievance->assigned_to,
            ]);
            return;
        }

        \Log::info('DEBUG - Técnico encontrado', [
            'technician_id' => $technician->id,
            'technician_name' => $technician->name,
            'technician_email' => $technician->email,
            'technician_roles' => $technician->getRoleNames(),
        ]);

        // TODO: Implementar envio de email real
        /*
        Mail::to($technician->email)
            ->send(new CompletionRejectedMail(
                grievance: $grievance,
                comment: $comment,
                rejectedBy: auth()->user()->name
            ));
        */
        
        \Log::info('DEBUG - Notificação de rejeição preparada para envio', [
            'grievance_id' => $grievance->id,
            'technician_id' => $technician->id,
            'technician_email' => $technician->email,
            'comment_length' => strlen($comment),
            'notification_type' => 'completion_rejection',
            'timestamp' => now()->toIso8601String(),
        ]);

    } catch (\Exception $e) {
        \Log::error('DEBUG - Erro ao preparar notificação de rejeição', [
            'grievance_id' => $grievance->id,
            'error' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
        ]);
    }
}


private function getGrievanceDebugInfo(Grievance $grievance): array
{
    return [
        'id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'status' => $grievance->status,
        'type' => $grievance->type,
        'priority' => $grievance->priority,
        'assigned_to' => $grievance->assigned_to,
        'escalated' => $grievance->escalated,
        'escalated_by' => $grievance->escalated_by,
        'created_at' => $grievance->created_at,
        'updated_at' => $grievance->updated_at,
        'metadata_keys' => $grievance->metadata ? array_keys($grievance->metadata) : [],
        'has_pending_approval' => $grievance->status === 'pending_approval',
        'updates_count' => $grievance->updates()->count(),
        'attachments_count' => $grievance->attachments()->count(),
    ];
}

private function sendResolutionNotification(Grievance $grievance, string $comment): void
{
    try {
        $userEmail = $grievance->contact_email ?? $grievance->user->email ?? null;
        
        if (!$userEmail) {
            \Log::warning('Não foi possível enviar notificação: email do utente não encontrado', [
                'grievance_id' => $grievance->id
            ]);
            return;
        }
        
        // TODO: Implementar envio de email real
        /*
        Mail::to($userEmail)
            ->send(new GrievanceResolvedMail(
                grievance: $grievance,
                comment: $comment,
                resolvedBy: auth()->user()->name
            ));
        */
        
        \Log::info('Notificação de resolução preparada para envio', [
            'grievance_id' => $grievance->id,
            'user_email' => $userEmail,
            'comment' => substr($comment, 0, 100) . '...',
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro ao preparar notificação de resolução', [
            'grievance_id' => $grievance->id,
            'error' => $e->getMessage(),
        ]);
    }
}

    /**
     * Add comment to a grievance.
     */
    public function addComment(Request $request, Grievance $grievance)
    {
        $this->ensureManager($request->user());

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
            'is_public' => ['required', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            // Criar atividade de comentário
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'comment_added',
                userId: $request->user()->id,
                description: 'Gestor adicionou um comentário',
                comment: $validated['comment'],
                metadata: [
                    'is_public' => $validated['is_public'],
                    'comment_type' => 'manager_comment',
                ],
                isPublic: $validated['is_public']
            );

            // Se o comentário não for público, adicionar metadados
            if (!$validated['is_public']) {
                $metadata = $grievance->metadata ?? [];
                $metadata['private_comments'][] = [
                    'user_id' => $request->user()->id,
                    'comment' => $validated['comment'],
                    'created_at' => now()->toIso8601String(),
                ];
                $grievance->metadata = $metadata;
                $grievance->save();
            }

            DB::commit();

            return back()->with('success', 'Comentário adicionado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao adicionar comentário', [
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Erro ao adicionar comentário: ' . $e->getMessage()]);
        }
    }

    /**
     * Send grievance to director.
     */
     public function sendToDirector(Request $request, Grievance $grievance)
{
    $this->ensureManager($request->user());

    // Verificar se pode enviar ao director
    $allowedStatuses = ['submitted', 'under_review', 'assigned', 'in_progress', 'open'];
    if (!in_array($grievance->status, $allowedStatuses)) {
        return back()->withErrors([
            'error' => 'Só pode enviar ao director quando o status for: ' . 
                     implode(', ', array_map([$this, 'getStatusText'], $allowedStatuses))
        ]);
    }

    // Verificar se já foi escalado
    if ($grievance->escalated) {
        return back()->withErrors([
            'error' => 'Esta submissão já foi escalada ao Director anteriormente.'
        ]);
    }

    $validated = $request->validate([
        'reason' => ['required', 'string', 'min:5', 'max:500'],
        'comment' => ['required', 'string', 'min:20', 'max:2000'],
    ]);

    DB::beginTransaction();

    try {
        // Encontrar o director
        $director = User::role('Director')->first();
        
        if (!$director) {
            // Se não houver director, usar admin como fallback
            $director = User::role('Admin')->first();
            
            if (!$director) {
                $director = User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['Admin', 'Administrador']);
                })->first();
            }
        }

        $capitalizedReason = ucfirst(mb_strtolower($validated['reason'], 'UTF-8'));
        
        $capitalizedReason = preg_replace_callback('/\.\s*(\w)/', function($matches) {
            return '. ' . mb_strtoupper($matches[1], 'UTF-8');
        }, $capitalizedReason);

        $previousStatus = $grievance->status;
        $previousAssignedTo = $grievance->assigned_to;
        $previousPriority = $grievance->priority;

        $grievance->markAsEscalated(
            escalatedBy: $request->user()->id,
            reason: $capitalizedReason
        );

        $grievance->update([
            // 'status' => $grievance->status, // Mantém o status original
            'assigned_to' => $director->id ?? null,
            'priority' => 'high',
        ]);

        // Criar atividade de escalamento
        GrievanceUpdate::log(
            grievanceId: $grievance->id,
            actionType: 'escalated_to_director',
            userId: $request->user()->id,
            description: 'Submissão escalada para o Director',
            comment: $validated['comment'],
            metadata: [
                'reason' => $capitalizedReason,
                'escalated_by' => $request->user()->id,
                'escalated_by_name' => $request->user()->name,
                'escalated_at' => now()->toIso8601String(),
                'director_id' => $director->id ?? null,
                'director_name' => $director->name ?? 'Director',
                'previous_assigned_to' => $previousAssignedTo,
                'previous_status' => $previousStatus,
                'previous_priority' => $previousPriority,
                'priority_changed_to' => 'high',
                'is_public' => true,
            ],
            isPublic: true
        );

        // Criar atividade de mudança de prioridade
        GrievanceUpdate::log(
            grievanceId: $grievance->id,
            actionType: 'priority_changed',
            userId: $request->user()->id,
            description: 'Prioridade aumentada para ALTA devido a escalamento para Director',
            oldValue: $previousPriority,
            newValue: 'high',
            metadata: [
                'reason' => 'Escalamento para Director',
                'escalation_reason' => $capitalizedReason,
            ],
            isPublic: true
        );

        // Se houve mudança de técnico para director
        if ($previousAssignedTo !== $director->id) {
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'technician_reassigned',
                userId: $request->user()->id,
                description: 'Submissão reatribuída ao Director',
                oldValue: $previousAssignedTo,
                newValue: $director->id ?? null,
                metadata: [
                    'new_technician_name' => $director->name ?? 'Director',
                    'reassigned_by_manager' => true,
                    'reason' => 'Escalamento para Director',
                ],
                isPublic: true
            );
        }

        DB::commit();

        // Para requisições Inertia
        if ($request->header('X-Inertia')) {
            return back()->with([
                'success' => 'Submissão reencaminhada ao Director com sucesso!',
                'updatedGrievance' => [
                    'id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'status' => $grievance->status, // Status original
                    'escalated' => true,
                    'priority' => 'high',
                    'assigned_to' => $director ? [
                        'id' => $director->id,
                        'name' => $director->name,
                        'email' => $director->email,
                    ] : null,
                    'escalated_by' => [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                    ],
                    'escalation_reason' => $capitalizedReason,
                    'escalated_at' => now()->toIso8601String(),
                ]
            ]);
        }

        // Para requisições API/JSON
        return response()->json([
            'success' => true,
            'message' => 'Submissão reencaminhada ao Director com sucesso!',
            'grievance' => [
                'id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'status' => $grievance->status, // Status original
                'escalated' => true,
                'priority' => 'high',
                'assigned_to' => $director ? [
                    'id' => $director->id,
                    'name' => $director->name,
                    'email' => $director->email,
                ] : null,
                'escalated_by' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                ],
                'escalation_reason' => $capitalizedReason,
                'escalated_at' => now()->toIso8601String(),
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        // Para requisições Inertia
        if ($request->header('X-Inertia')) {
            return back()->withErrors([
                'error' => 'Erro ao enviar para director: ' . $e->getMessage()
            ]);
        }

        // Para requisições API/JSON
        return response()->json([
            'success' => false,
            'message' => 'Erro ao enviar para director: ' . $e->getMessage()
        ], 500);
    }
}


public function getDirectorInterventions(Request $request)
{
    $user = auth()->user();
    $this->ensureManager($user);
    
    // Buscar reclamações atribuídas ao gestor OU que foram escaladas por ele
    $query = Grievance::with([
        'user',
        'assignedUser',
        'escalatedBy',
        'updates.user.roles'  // Carregar updates com usuários e roles
    ])
    ->where(function($q) use ($user) {
        // Reclamações atribuídas ao gestor
        $q->where('assigned_to', $user->id)
          ->orWhereHas('updates', function($q2) use ($user) {
              $q2->where('user_id', $user->id)
                 ->whereIn('action_type', ['manager_comment', 'manager_approved', 'manager_rejected']);
          });
    })
    ->latest();
    
    // Filtrar apenas as que têm intervenção do director
    $query->where(function($q) {
        $q->where('escalated', true)
          ->orWhereJsonContains('metadata->is_escalated_to_director', true)
          ->orWhereHas('updates', function($q2) {
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
    });
    
    $grievances = $query->get();
    
    // Formatar os dados
    $formattedGrievances = $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    });
    
    // Filtrar apenas as que realmente têm intervenções
    $filteredGrievances = $formattedGrievances->filter(function ($grievance) {
        return $grievance['has_director_intervention'] === true;
    })->values();
    
    
    return response()->json([
        'success' => true,
        'data' => $filteredGrievances,
        'count' => $filteredGrievances->count(),
        'debug' => [
            'total_grievances' => $grievances->count(),
            'filtered_grievances' => $filteredGrievances->count(),
            'sample' => $filteredGrievances->count() > 0 ? $filteredGrievances->first() : null
        ]
    ]);
}

private function getDirectorInterventionsData(User $user): array
{
    \Log::info("=== 🎯 getDirectorInterventionsData para Gestor ===");
    \Log::info("Gestor ID: {$user->id}, Nome: {$user->name}");
    
    // Buscar casos atribuídos ao gestor que tenham intervenções do director
    $query = Grievance::with([
            'user', 
            'assignedUser',
            'escalatedBy',
            'updates.user.roles'
        ])
        ->where('assigned_to', $user->id) // Apenas casos do gestor atual
        ->where(function($q) {
            // Critérios para ter intervenção do director
            $q->where('escalated', true)
              ->orWhereJsonContains('metadata->is_escalated_to_director', true)
              ->orWhereHas('updates', function($q2) {
                  $q2->whereIn('action_type', [
                      'director_comment',
                      'director_validation_approved',
                      'director_validation_rejected',
                      'director_validation_needs_revision'
                  ]);
              })
              ->orWhere(function($q2) {
                  $q2->whereNotNull('metadata')
                      ->where('metadata', 'LIKE', '%director_validation%');
              });
        })
        ->latest();
    
    $grievances = $query->get();
    
    \Log::info("Casos com intervenção do director encontrados: {$grievances->count()}");
    
    // **IMPORTANTE: Garantir que os dados são formatados corretamente**
    $formatted = $grievances->map(function ($grievance) {
        $data = $this->formatGrievanceForList($grievance);
        
        // **CRÍTICO: Forçar campos que o frontend espera**
        $data['has_director_intervention'] = true;
        $data['escalated'] = $grievance->escalated ?? false;
        $data['is_escalated_to_director'] = $grievance->escalated ?? false;
        
        // Garantir que arrays não sejam null
        $data['director_updates'] = $data['director_updates'] ?? [];
        $data['director_interventions'] = $data['director_interventions'] ?? [];
        
        return $data;
    })->values()->toArray();
    
    \Log::info("Dados formatados: " . count($formatted) . " casos");
    
    return $formatted;
}


private function getMySubmissionsToDirectorData(User $user): array
{
    \Log::info("=== 📤 getMySubmissionsToDirectorData para Gestor ===");
    \Log::info("Gestor ID: {$user->id}, Nome: {$user->name}");
    
    // **CRÍTICO: Buscar casos que foram escalados por ESTE gestor específico**
    $query = Grievance::with([
            'user', 
            'assignedUser',
            'escalatedBy',
            'updates.user.roles'
        ])
        ->where('escalated_by', $user->id) // Apenas casos escalados por este gestor
        ->where('escalated', true)
        ->latest();
    
    $myEscalatedGrievances = $query->get();
    
    \Log::info("Casos escalados por {$user->name}: {$myEscalatedGrievances->count()}");
    
    // **ALTERNATIVA: Se não encontrar por escalated_by, buscar por assigned_to**
    if ($myEscalatedGrievances->isEmpty()) {
        \Log::info("Buscando casos atribuídos ao gestor que estão escalados...");
        
        $myEscalatedGrievances = Grievance::with([
                'user', 
                'assignedUser',
                'escalatedBy',
                'updates.user.roles'
            ])
            ->where('assigned_to', $user->id)
            ->where('escalated', true)
            ->latest()
            ->get();
            
        \Log::info("Casos atribuídos e escalados: {$myEscalatedGrievances->count()}");
    }
    
    // **CRÍTICO: Garantir que os dados são formatados corretamente**
    $formatted = $myEscalatedGrievances->map(function ($grievance) {
        $data = $this->formatGrievanceForList($grievance);
        
        // **CRÍTICO: Forçar campos que o frontend espera**
        $data['escalated'] = true;
        $data['is_escalated_to_director'] = true;
        $data['has_director_intervention'] = true; // Porque foi enviado ao director
        $data['escalated_by'] = $grievance->escalated_by;
        $data['escalated_at'] = $grievance->escalated_at?->toISOString();
        $data['escalation_reason'] = $grievance->escalation_reason;
        
        // Garantir que arrays não sejam null
        $data['director_updates'] = $data['director_updates'] ?? [];
        $data['director_interventions'] = $data['director_interventions'] ?? [];
        
        return $data;
    })->values()->toArray();
    
    \Log::info("📤 Retornando " . count($formatted) . " casos formatados");
    
    return $formatted;
}



    public function revokeEscalation(Request $request, Grievance $grievance)
{
    $this->ensureManager($request->user());

    if (!$grievance->escalated) {
        return back()->withErrors(['error' => 'Esta submissão não foi escalada ao Director']);
    }

    DB::beginTransaction();

    try {
        // Restaurar para estado anterior
        $previousStatus = $grievance->metadata['previous_status_before_escalation'] ?? 'under_review';
        $previousAssignedTo = $grievance->metadata['previous_assigned_to_before_escalation'] ?? null;
        
        $grievance->update([
            'escalated' => false,
            'status' => $previousStatus,
            'assigned_to' => $previousAssignedTo,
            'priority' => $grievance->metadata['previous_priority_before_escalation'] ?? 'medium',
            'escalation_reason' => null,
            'escalated_at' => null,
            'escalated_by' => null,
        ]);

        // Registrar a revogação
        GrievanceUpdate::log(
            grievanceId: $grievance->id,
            actionType: 'escalation_revoked',
            userId: $request->user()->id,
            description: 'Encaminhamento ao Director revogado pelo gestor',
            metadata: [
                'revoked_by' => $request->user()->id,
                'revoked_by_name' => $request->user()->name,
                'previous_status' => 'escalated',
                'new_status' => $previousStatus,
                'previous_assigned_to' => $grievance->getOriginal('assigned_to'),
                'new_assigned_to' => $previousAssignedTo,
                'is_public' => true,
            ],
            isPublic: true
        );

        DB::commit();

        // Para requisições Inertia
        if ($request->header('X-Inertia')) {
            return back()->with([
                'success' => 'Encaminhamento revogado com sucesso!',
                'updatedGrievance' => [
                    'id' => $grievance->id,
                    'escalated' => false,
                    'status' => $previousStatus,
                    'priority' => $grievance->priority,
                    'assigned_to' => $previousAssignedTo ? [
                        'id' => $previousAssignedTo,
                        'name' => User::find($previousAssignedTo)?->name ?? 'Técnico',
                    ] : null,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Encaminhamento revogado com sucesso!',
            'grievance' => $grievance->fresh(['assignedUser']),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erro ao revogar escalamento', [
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'error' => $e->getMessage(),
        ]);

        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => 'Erro ao revogar encaminhamento: ' . $e->getMessage()]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao revogar encaminhamento: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Approve completion of a grievance.
     */
   public function validateSubmission(Request $request, Grievance $grievance)
{
  $this->ensureDirector($request->user());
    
    // Aceitar apenas 'approved' ou 'commented'
    $validated = $request->validate([
        'status' => ['required', 'in:approved,commented'], // Apenas estes dois
        'comment' => ['required', 'string', 'min:10', 'max:2000'],
        'assumed_by_director' => ['nullable', 'boolean'],
    ]);
    
    DB::beginTransaction();
    try {
        // Salvar a validação
        $metadata = $grievance->metadata ?? [];
        $metadata['director_validation'] = [
            'status' => $validated['status'], // 'approved' ou 'commented'
            'comment' => $validated['comment'],
            'validated_by' => $request->user()->id,
            'validated_by_name' => $request->user()->name,
            'validated_at' => now()->toISOString(),
            'assumed_by_director' => $validated['status'] === 'approved', // Apenas true para 'approved'
        ];
        
        // Salvar como objeto no campo director_validation
        $grievance->update([
            'director_validation' => $metadata['director_validation'], // Objeto completo
            'metadata' => $metadata,
            'status' => 'under_review', // Muda para under_review em ambos os casos
            'assigned_to' => $validated['status'] === 'approved' ? $request->user()->id : $grievance->assigned_to,
        ]);
        
        // Criar registro na timeline
        $actionType = $validated['status'] === 'approved' ? 'approved_by_director' : 'commented_by_director';
        $description = $validated['status'] === 'approved' 
            ? 'Director aprovou e assumiu o caso' 
            : 'Director forneceu parecer técnico';
        
        GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'action_type' => $actionType,
            'description' => $description,
            'comment' => $validated['comment'],
            'metadata' => [
                'validation_status' => $validated['status'],
                'assumed_by_director' => $validated['status'] === 'approved',
                'is_public' => true,
            ],
            'is_public' => true,
        ]);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Resposta do Director registrada com sucesso',
            'validation' => $metadata['director_validation']
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Enviar notificação de validação ao técnico
 */
private function sendValidationNotificationToTechnician(Grievance $grievance, string $status, string $comment): void
{
    try {
        $technician = User::find($grievance->assigned_to);
        
        if (!$technician) {
            \Log::warning('Não foi possível enviar notificação: técnico não encontrado', [
                'grievance_id' => $grievance->id,
                'assigned_to' => $grievance->assigned_to
            ]);
            return;
        }
        
        // TODO: Implementar envio de email real
        /*
        Mail::to($technician->email)
            ->send(new SubmissionValidatedMail(
                grievance: $grievance,
                status: $status,
                comment: $comment,
                validatedBy: auth()->user()->name
            ));
        */
        
        \Log::info('Notificação de validação preparada para envio ao técnico', [
            'grievance_id' => $grievance->id,
            'technician_id' => $technician->id,
            'technician_email' => $technician->email,
            'status' => $status,
            'comment' => substr($comment, 0, 100) . '...',
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro ao preparar notificação de validação', [
            'grievance_id' => $grievance->id,
            'error' => $e->getMessage(),
        ]);
    }
}
    /**
     * Save manager comment without changing status.
     */
    public function saveComment(Request $request, Grievance $grievance)
    {
        $this->ensureManager($request->user());

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
            'is_public' => ['required', 'boolean'],
        ]);

        try {
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'manager_comment',
                userId: $request->user()->id,
                description: 'Comentário do gestor',
                comment: $validated['comment'],
                metadata: [
                    'is_public' => $validated['is_public'],
                    'comment_type' => 'manager_standalone',
                    'created_at' => now()->toIso8601String(),
                ],
                isPublic: $validated['is_public']
            );

            return back()->with('success', 'Comentário salvo com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar comentário', [
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Erro ao salvar comentário: ' . $e->getMessage()]);
        }
    }

    /**
     * Placeholder for escalation flow.
     */
    public function escalate(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureManager($request->user());

        return back()->with('info', 'Fluxo de escalonamento será implementado em breve.');
    }

    /**
     * Placeholder for automatic assignment.
     */
    public function bulkAssign(Request $request): RedirectResponse
    {
        $this->ensureManager($request->user());

        return back()->with('info', 'Atribuição automática ainda não está disponível.');
    }

    /**
     * Placeholder for export feature.
     */
    public function export(Request $request): RedirectResponse
    {
        $this->ensureManager($request->user());

        return back()->with('info', 'Exportação será disponibilizada numa próxima versão.');
    }

    /**
     * Ensure authenticated user is a manager.
     */
    private function ensureManager(?User $user): void
    {
        abort_if(!$user || !$user->hasRole('Gestor'), 403);
    }

    /**
     * Store attachments uploaded by the manager.
     */
    private function storeAttachment(Grievance $grievance, UploadedFile $file, string $source): Attachment
    {
        $originalFilename = $file->getClientOriginalName();
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        // Armazenar o arquivo diretamente na pasta public
        $publicPath = 'uploads/grievances/' . $grievance->id . '/attachments';
        $fullPath = public_path($publicPath);

        // Criar diretório se não existir
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $path = '/' . $publicPath . '/' . $filename;
        $file->move($fullPath, $filename);

        $fileHash = hash_file('sha256', $fullPath . '/' . $filename);

        return Attachment::create([
            'grievance_id' => $grievance->id,
            'original_filename' => $originalFilename,
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'file_hash' => $fileHash,
            'is_encrypted' => false,
            'metadata' => [
                'uploaded_via' => $source,
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now()->toIso8601String(),
            ],
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);
    }

    public function getStatusText($status)
{
    // Validar se $status é nulo ou vazio
    if (empty($status)) {
        return 'Não definido';
    }

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
 * Obter gestores disponíveis
 */
private function getAvailableManagers(): array
{
    return User::role('Gestor')
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

public function getDirectorInterventionsApi(Request $request)
{
    $user = auth()->user();
    $this->ensureManager($user);
    
    $data = $this->getDirectorInterventionsData($user);
    
    return response()->json([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
}

/**
 * API: Obter minhas submissões ao director
 */
public function getMySubmissionsToDirectorApi(Request $request)
{
    $user = auth()->user();
    $this->ensureManager($user);
    
    $data = $this->getMySubmissionsToDirectorData($user);
    
    return response()->json([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
}


public function getTabData(Request $request)
{
    $user = auth()->user();
    $this->ensureManager($user);
    
    $tab = $request->get('tab', 'all');
    
    switch ($tab) {
        case 'director_interventions':
            $data = $this->getDirectorInterventionsData($user);
            break;
        case 'my_submissions_to_director':
            $data = $this->getMySubmissionsToDirectorData($user);
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
            $data = $this->getAllGrievancesForManager($user);
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
    $query = Grievance::with(['user', 'assignedUser', 'updates.user.roles'])
        ->where('assigned_to', $user->id);
    
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
 * Obter todas as reclamações do gestor
 */
private function getAllGrievancesForManager(User $user): array
{
    $grievances = Grievance::with(['user', 'assignedUser', 'updates.user.roles'])
        ->where('assigned_to', $user->id)
        ->latest()
        ->get();
    
    return $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    })->values()->toArray();
}

 private function checkAccess($user)
    {
        // Verificar se o usuário é um gestor
        if (!$user->hasRole('Gestor') && !$user->hasRole('manager')) {
            abort(403, 'Acesso não autorizado. Apenas gestores podem realizar esta ação.');
        }
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
    $this->ensureManager($user);

    $validated = $request->validate([
        'reason' => 'required|string|min:10|max:2000',
        'internal_comment' => 'nullable|string|max:1000',
        'notify_technician' => 'boolean',
        'notify_manager' => 'boolean',
        'attachments' => 'nullable|array|max:5',
        'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:10240',
    ]);

    $grievance = Grievance::findOrFail($id);
    
    // Verificar se a submissão está no estado "Pendente de Aprovação"
    if ($grievance->status !== 'pending_approval') {
        return response()->json([
            'success' => false,
            'message' => 'Apenas submissões no estado "Pendente de Aprovação" podem ter a aprovação rejeitada'
        ], 400);
    }

    DB::beginTransaction();
    try {
        // 1. Atualizar status para "in_progress" (devolver ao técnico)
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
            'notify_technician' => $validated['notify_technician'] ?? true,
            'notify_manager' => $validated['notify_manager'] ?? false,
            'previous_status' => $oldStatus,
            'new_status' => 'in_progress',
        ];

        $grievance->metadata = $metadata;
        $grievance->save();

        // 3. Criar um update/histórico da ação
        $grievance->updates()->create([
            'user_id' => $user->id,
            'action_type' => 'manager_rejected_approval',
            'description' => 'Aprovação rejeitada pelo gestor. Submissão devolvida ao técnico.',
            'comment' => $validated['reason'],
            'metadata' => [
                'rejection_details' => [
                    'reason' => $validated['reason'],
                    'internal_comment' => $validated['internal_comment'] ?? null,
                    'previous_status' => $oldStatus,
                    'new_status' => 'in_progress',
                ],
                'notifications' => [
                    'technician' => $validated['notify_technician'] ?? true,
                    'manager' => $validated['notify_manager'] ?? false,
                ],
                'returned_to_technician' => true,
                'technician_id' => $grievance->assigned_to,
            ],
            'is_public' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Processar anexos se existirem
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachment = $this->storeRejectionAttachment($grievance, $file, $user->id);
            }
        }

        // 5. Enviar notificações se solicitado
        $this->sendApprovalRejectionNotifications($grievance, $validated);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Aprovação rejeitada com sucesso! A submissão foi devolvida ao técnico.',
            'grievance' => $grievance->fresh(['updates'])
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao rejeitar aprovação: ' . $e->getMessage()
        ], 500);
    }
}

private function sendApprovalRejectionNotifications(Grievance $grievance, array $data): void
{
    // TODO: Implementar sistema de notificações
    
    // Notificar o técnico se solicitado
    if (($data['notify_technician'] ?? true) && $grievance->assigned_to) {
        $technician = User::find($grievance->assigned_to);
        if ($technician) {
            \Log::info('Notificação de rejeição de aprovação enviada ao técnico', [
                'technician_id' => $technician->id,
                'technician_name' => $technician->name,
                'grievance_id' => $grievance->id,
                'reason' => substr($data['reason'], 0, 100) . '...',
            ]);
        }
    }
    
    // Notificar o gestor se solicitado
    if (($data['notify_manager'] ?? false)) {
        \Log::info('Notificação de rejeição de aprovação enviada ao gestor', [
            'manager_id' => auth()->id(),
            'manager_name' => auth()->user()->name,
            'grievance_id' => $grievance->id,
            'reason' => substr($data['reason'], 0, 100) . '...',
        ]);
    }
}

// No método exportStatistics do ManagerGrievanceController
public function exportStatistics(Request $request)
{
    $this->ensureManager(auth()->user());
    
    // Validar parâmetros
    $validated = $request->validate([
        'period' => ['required', 'string', 'in:today,week,month,3months,6months,year,12months'],
        'format' => ['required', 'string', 'in:xlsx,csv,pdf'],
    ]);
    
    $user = auth()->user();
    $period = $validated['period'];
    $format = $validated['format'];
    
    $export = new StatisticsExport($period, $user);
    
    if ($format === 'pdf') {
        // Para PDF, use DomPDF se disponível
        return $this->exportToPdf($period, $user);
    }
    
    if ($format === 'csv') {
        return $export->exportCsv();
    }
    
    // Padrão: Excel
    return $export->exportExcel();
}

private function exportToPdf($period, $user)
{
    // Se DomPDF estiver instalado
    if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
        $data = $this->gatherStatisticsData($period, $user);
        $filename = 'estatisticas-' . $period . '-' . now()->format('Y-m-d') . '.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.statistics-pdf', [
            'data' => $data,
            'period' => $period,
            'user' => $user,
        ])->setPaper('A4', 'landscape');
        
        return $pdf->download($filename);
    }
    
    // Se não tiver PDF, redirecionar para Excel
    return redirect()->route('statistics.export', [
        'period' => $period,
        'format' => 'xlsx'
    ]);
}

private function gatherStatisticsData($period, $user)
{
    // Reuse a lógica do StatisticsExport
    $export = new StatisticsExport($period, $user);
    
    // Você precisaria adicionar um método público para obter os dados
    // Por enquanto, vamos criar um array básico
    return [
        'period' => $period,
        'user' => $user,
        'exported_at' => now()->format('d/m/Y H:i:s'),
        'period_label' => $this->getPeriodLabel($period),
    ];
}

private function getPeriodLabel($period)
{
    $labels = [
        'today' => 'Hoje',
        'week' => 'Esta Semana',
        'month' => 'Este Mês',
        '3months' => 'Últimos 3 Meses',
        '6months' => 'Últimos 6 Meses',
        'year' => 'Este Ano',
        '12months' => 'Últimos 12 Meses',
    ];
    
    return $labels[$period] ?? 'Período Desconhecido';
}

private function processDirectorValidation($grievance, $data, $user)
{
    $metadata = $grievance->metadata ?? [];
    
    $validationData = [
        'status' => $data['status'],
        'comment' => $data['comment'] ?? '',
        'validated_by' => $user->id,
        'validated_by_name' => $user->name,
        'validated_at' => now()->toISOString(),
        'assumed_by_director' => $data['status'] === 'approved',
        'notify_manager' => $data['notify_manager'] ?? true,
        'notify_technician' => $data['notify_technician'] ?? true,
    ];
    
    $metadata['director_validation'] = $validationData;
    
    // Se o director assumiu o caso, atualizar assigned_to para o director
    if ($data['status'] === 'approved') {
        $grievance->assigned_to = $user->id;
        $grievance->assigned_at = now();
        
        // Registrar mudança de atribuição
        GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $user->id,
            'action_type' => 'director_assumed_case',
            'description' => 'Director assumiu a responsabilidade pelo caso',
            'metadata' => [
                'previous_manager' => $grievance->assigned_to,
                'new_manager' => $user->id,
                'reason' => $data['comment'] ?? '',
            ],
            'is_public' => true,
        ]);
    }
    
    // Se foi apenas comentário, manter atribuição ao gestor
    if ($data['status'] === 'needs_revision') {
        // Status permanece 'under_review' para o gestor continuar
        $grievance->status = 'under_review';
        
        // Registrar parecer do director
        GrievanceUpdate::create([
            'grievance_id' => $grievance->id,
            'user_id' => $user->id,
            'action_type' => 'director_provided_guidance',
            'description' => 'Director forneceu parecer técnico',
            'comment' => $data['comment'] ?? '',
            'metadata' => [
                'returned_to_manager' => true,
                'manager_id' => $grievance->assigned_to,
            ],
            'is_public' => true,
        ]);
    }
    
    $grievance->metadata = $metadata;
    $grievance->save();
    
    return $validationData;
}
}

