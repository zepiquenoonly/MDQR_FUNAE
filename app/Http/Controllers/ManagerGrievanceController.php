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
use Inertia\Inertia;
use Inertia\Response;

class ManagerGrievanceController extends Controller
{
    /**
     * Display the specified grievance.
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
        
        // Verificar se o usuário tem acesso a esta reclamação
      /*  $hasAccess = $this->checkGrievanceAccess($user, $grievance);
        
        if (!$hasAccess) {
            abort(403, 'Você não tem permissão para visualizar esta reclamação.');
        }*/
        
        // Carregar relações necessárias
        $grievance->load([
            'user',
            'assignedUser',
            'escalatedBy',
            'project',
            'attachments',
            'updates.user.roles'
        ]);
        
        // Formatar dados para o frontend - NOVO: usar o mesmo formato que o Show.vue espera
        $formattedGrievance = $this->formatGrievanceForShow($grievance);
        
        // Obter técnicos disponíveis para reatribuição
        $technicians = $this->getAvailableTechnicians();
        
        // Obter comentários/atualizações formatados para o Show.vue
        $comments = $this->formatCommentsForShow($grievance, $user);
        
        // Retornar para o Show.vue com as props corretas
        return Inertia::render('Director/Show', [
            'submission' => $formattedGrievance, // Show.vue espera 'submission'
            'complaint' => $formattedGrievance,  // Para compatibilidade
            'comments' => $comments,
            'technicians' => $technicians,
            'projects' => $this->getActiveProjects(),
            'managers' => $this->getAvailableManagers(),
            'timeline_data' => $grievance->updates->sortByDesc('created_at')->values()->toArray(),
            'user' => $user ? [  // Adicionar user para o Show.vue
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
        ->whereIn('action_type', [
            'comment_added',
            'manager_comment',
            'technician_comment',
            'director_comment',
            'director_validation',
            'director_validation_approved',
            'director_validation_rejected',
            'director_validation_needs_revision',
            'manager_approved',
            'manager_rejected'
        ])
        ->sortByDesc('created_at')
        ->values()
        ->map(function ($update) use ($user) {
            // Determinar tipo de comentário
            $commentType = $this->getCommentType($update);
            
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
                'name' => $attachment->original_filename,
                'size' => $this->formatBytes($attachment->size),
                'path' => $attachment->path,
                'download_url' => route('attachments.download', $attachment),
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

    // **ADICIONAR: Filtrar por intervenções do director se solicitado**
    if ($request->filled('director_interventions') && $request->boolean('director_interventions')) {
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
    }

    $grievances = $query->paginate(15);

    // Formatar os dados para incluir informações de intervenção do director
    $formattedGrievances = $grievances->through(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    });

    // **ADICIONAR: Calcular contadores específicos para o frontend**
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
    
    $directorInterventionsCount = $allGrievances->filter(function($item) {
        return $this->hasDirectorIntervention($item);
    })->count();
    
    $managerRequestsCount = $allGrievances->filter(function($item) {
        return $item->escalated || 
               ($item->metadata && isset($item->metadata['is_escalated_to_director']) && 
                $item->metadata['is_escalated_to_director'] === true);
    })->count();

    return Inertia::render('Manager/GrievanceDetail', [
        'grievances' => $formattedGrievances,
        'filters' => $request->only(['search', 'status', 'priority', 'director_interventions']),
        'counts' => [
            'suggestions' => $suggestionsCount,
            'grievances' => $grievancesCount,
            'complaints' => $complaintsCount,
            'director_interventions' => $directorInterventionsCount,
            'manager_requests' => $managerRequestsCount,
            'total' => $allGrievances->count()
        ],
        'debug_info' => [
            'user_id' => $user->id,
            'user_role' => $user->getRoleNames()->first(),
            'director_interventions_filtered' => $request->boolean('director_interventions', false)
        ]
    ]);
}

    /**
     * Format grievance data for the list view
     */
   private function formatGrievanceForList(Grievance $grievance): array
{
    // Inicializar variáveis
    $hasDirectorIntervention = false;
    $directorUpdates = [];
    $directorCommentsCount = 0;
    $directorInterventions = [];
    
    // DEBUG: Verificar se a grievance tem updates
    \Log::info('=== Processando grievance para Gestor: ' . $grievance->id . ' - ' . $grievance->reference_number . ' ===', [
        'total_updates' => $grievance->updates->count(),
        'escalated' => $grievance->escalated,
        'metadata' => $grievance->metadata,
        'status' => $grievance->status
    ]);
    
    // **CORREÇÃO: Carregar updates com relação user**
    $grievance->load(['updates.user.roles']);
    
    // Analisar updates para encontrar intervenções do director
    foreach ($grievance->updates as $update) {
        $isDirectorUpdate = false;
        $updateDetails = null;
        
        // Verificar pelo action_type (indicador direto)
        $directorActionTypes = [
            'director_comment',
            'director_validation_approved', 
            'director_validation_rejected',
            'director_validation_needs_revision',
            'escalated_to_director'
        ];
        
        \Log::info('Analisando update ' . $update->id . ': ' . $update->action_type, [
            'user_id' => $update->user_id,
            'user_roles' => $update->user ? $update->user->getRoleNames()->toArray() : null,
            'metadata' => $update->metadata
        ]);
        
        // 1. Verificar por action_type do director
        if (in_array($update->action_type, $directorActionTypes)) {
            $isDirectorUpdate = true;
            \Log::info('Encontrado update do director por action_type', [
                'update_id' => $update->id,
                'action_type' => $update->action_type
            ]);
        }
        
        // 2. Verificar se o usuário é director
        if ($update->user && $update->user->hasRole('Director')) {
            $isDirectorUpdate = true;
            \Log::info('Encontrado update do director por role do usuário', [
                'update_id' => $update->id,
                'user_name' => $update->user->name,
                'user_role' => $update->user->getRoleNames()->first()
            ]);
        }
        
        // 3. Verificar metadados
        if ($update->metadata) {
            if (isset($update->metadata['created_by_director']) && $update->metadata['created_by_director'] === true) {
                $isDirectorUpdate = true;
                \Log::info('Encontrado update do director por metadata', [
                    'update_id' => $update->id,
                    'metadata' => $update->metadata
                ]);
            }
            
            // Verificar se é uma intervenção do director
            if (isset($update->metadata['director_intervention']) && $update->metadata['director_intervention'] === true) {
                $isDirectorUpdate = true;
            }
            
            // Verificar se é uma validação do director
            if (isset($update->metadata['validation_status']) && in_array($update->metadata['validation_status'], ['approved', 'rejected', 'needs_revision'])) {
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
            
            // Adicionar à lista de intervenções
            $directorInterventions[] = [
                'type' => 'update',
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
    
    // **CORREÇÃO: Verificar se foi escalado para director (outra forma de intervenção)**
    $isEscalatedToDirector = $grievance->escalated || 
                            ($grievance->metadata && 
                             isset($grievance->metadata['is_escalated_to_director']) && 
                             $grievance->metadata['is_escalated_to_director'] === true);
    
    if ($isEscalatedToDirector) {
        $hasDirectorIntervention = true;
        
        \Log::info('Grievance escalada para director', [
            'grievance_id' => $grievance->id,
            'escalated' => $grievance->escalated,
            'metadata' => $grievance->metadata
        ]);
        
        // Adicionar intervenção de escalamento
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
    }
    
    // Formatar os dados básicos da reclamação
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
        'assigned_to' => $grievance->assigned_to,
        'escalated' => $grievance->escalated,
        'escalation_reason' => $grievance->escalation_reason,
        'escalated_at' => $grievance->escalated_at?->toISOString(),
        'escalated_by' => $grievance->escalated_by,
        
        // **INFORMAÇÕES CRÍTICAS PARA DETECÇÃO DE INTERVENÇÕES**
        'has_director_intervention' => $hasDirectorIntervention,
        'director_updates' => $directorUpdates,
        'director_comments_count' => $directorCommentsCount,
        'director_interventions' => $directorInterventions,
        
        // Verificar se tem validação do director no metadata
        'director_validation' => null,
        'metadata' => $grievance->metadata,
        'is_escalated_to_director' => $isEscalatedToDirector,
    ];
    
    // **CORREÇÃO: Extrair validação do director do metadata de forma mais abrangente**
    if ($grievance->metadata) {
        // Verificar no metadata direto
        if (isset($grievance->metadata['director_validation'])) {
            $formatted['director_validation'] = $grievance->metadata['director_validation'];
            $formatted['has_director_intervention'] = true;
            
            \Log::info('Encontrada validação do director no metadata', [
                'grievance_id' => $grievance->id,
                'validation' => $grievance->metadata['director_validation']
            ]);
            
            // Adicionar como intervenção
            $formatted['director_interventions'][] = [
                'type' => 'validation',
                'action_type' => 'director_validation',
                'status' => $grievance->metadata['director_validation']['status'] ?? null,
                'comment' => $grievance->metadata['director_validation']['comment'] ?? null,
                'validated_by' => $grievance->metadata['director_validation']['validated_by_name'] ?? null,
                'validated_at' => $grievance->metadata['director_validation']['validated_at'] ?? null,
                'metadata' => $grievance->metadata['director_validation'] ?? [],
            ];
        }
        
        // Verificar outras formas de intervenção do director
        if (isset($grievance->metadata['is_validated']) && $grievance->metadata['is_validated'] === true) {
            $formatted['has_director_intervention'] = true;
        }
        
        if (isset($grievance->metadata['validation_status'])) {
            $formatted['has_director_intervention'] = true;
        }
        
        // Verificar se tem comentários do director no metadata
        if (isset($grievance->metadata['director_comments']) && is_array($grievance->metadata['director_comments'])) {
            foreach ($grievance->metadata['director_comments'] as $comment) {
                $formatted['director_interventions'][] = [
                    'type' => 'comment',
                    'action_type' => 'director_comment',
                    'comment' => $comment['content'] ?? null,
                    'created_at' => $comment['created_at'] ?? null,
                    'metadata' => $comment,
                ];
                $directorCommentsCount++;
            }
            $formatted['director_comments_count'] = $directorCommentsCount;
        }
    }
    
    // Informações do usuário
    if ($grievance->user) {
        $formatted['user'] = [
            'name' => $grievance->user->name,
            'email' => $grievance->user->email,
            'phone' => $grievance->user->phone,
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
    
    // **DEBUG: Log dos resultados detalhados**
    \Log::info('=== RESULTADO formatGrievanceForList para Gestor ===', [
        'grievance_id' => $grievance->id,
        'reference_number' => $grievance->reference_number,
        'has_director_intervention' => $hasDirectorIntervention,
        'director_updates_count' => count($directorUpdates),
        'director_comments_count' => $directorCommentsCount,
        'director_interventions_count' => count($directorInterventions),
        'is_escalated_to_director' => $isEscalatedToDirector,
        'escalated' => $grievance->escalated,
        'status' => $grievance->status,
        'director_updates_examples' => array_slice($directorUpdates, 0, 3)
    ]);
    
    return $formatted;
}


/*private function hasDirectorIntervention(Grievance $grievance): bool
{
    // Verificar se foi escalado
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
}*/


public function checkDirectorInterventions()
{
    $user = auth()->user();
    $this->ensureManager($user);
    
    \Log::info('=== CHECKING DIRECTOR INTERVENTIONS ===');
    
    // 1. Check grievances assigned to this manager
    $assignedGrievances = Grievance::where('assigned_to', $user->id)->count();
    \Log::info("Grievances assigned to manager: {$assignedGrievances}");
    
    // 2. Check escalated grievances
    $escalatedGrievances = Grievance::where('escalated', true)
        ->where('assigned_to', $user->id)
        ->count();
    \Log::info("Escalated grievances: {$escalatedGrievances}");
    
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
    
    \Log::info("Grievances with director updates: {$grievancesWithDirectorUpdates}");
    
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
        $this->ensureManager($request->user());

        $data = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $oldPriority = $grievance->priority;
        
        $grievance->update(['priority' => $data['priority']]);

        // Registrar a alteração
        GrievanceUpdate::log(
            grievanceId: $grievance->id,
            actionType: 'priority_changed',
            userId: $request->user()->id,
            description: 'Prioridade alterada pelo gestor',
            oldValue: $oldPriority,
            newValue: $data['priority'],
            metadata: [
                'changed_by_manager' => true,
            ],
            isPublic: true
        );

        return back()->with('success', 'Prioridade atualizada com sucesso.');
    }

    /**
     * Reassign grievance to another technician.
     */
    public function reassign(Request $request, Grievance $grievance)
    {
        $this->ensureManager(auth()->user());

        \Log::info('Iniciando reatribuição de técnico', [
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
        ]);

        // Validação SIMPLIFICADA - apenas verifica se o usuário existe
        $validated = $request->validate([
            'technician_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ]);

        try {
            DB::beginTransaction();

            // Buscar usuário e VERIFICAR MANUALMENTE se é técnico
            $user = User::find($validated['technician_id']);
            
            if (!$user) {
                \Log::warning('Usuário não encontrado', [
                    'technician_id' => $validated['technician_id'],
                    'grievance_id' => $grievance->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ], 422);
            }

            // Verificar se o usuário tem role 'Técnico'
            if (!$user->hasRole('Técnico')) {
                \Log::warning('Usuário não é técnico', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'grievance_id' => $grievance->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'O usuário selecionado não é um técnico.'
                ], 422);
            }

            // Agora sim, temos um técnico válido
            $technician = $user;
            $previousTechnician = $grievance->assigned_to;

            \Log::info('Atualizando grievance', [
                'grievance_id' => $grievance->id,
                'tecnico_anterior' => $previousTechnician,
                'novo_tecnico' => $technician->id,
                'novo_tecnico_nome' => $technician->name
            ]);

            // Actualizar grievance
            $grievance->assigned_to = $technician->id;
            $grievance->assigned_at = now();
            $grievance->status = 'assigned';
            $grievance->save();

            \Log::info('Grievance atualizada com sucesso');

            // Criar atividade de reatribuição
            try {
                $update = GrievanceUpdate::create([
                    'grievance_id' => $grievance->id,
                    'user_id' => $request->user()->id,
                    'action_type' => 'technician_assigned',
                    'description' => "Técnico reatribuído: {$technician->name}",
                    'metadata' => [
                        'previous_technician_id' => $previousTechnician,
                        'new_technician_id' => $technician->id,
                        'new_technician_name' => $technician->name,
                        'assigned_by_manager' => true,
                    ],
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                \Log::info('Atividade registrada', ['update_id' => $update->id]);
            } catch (\Exception $e) {
                \Log::warning('Não foi possível registrar atividade', [
                    'error' => $e->getMessage(),
                    'grievance_id' => $grievance->id
                ]);
            }

            DB::commit();

            \Log::info('Reatribuição concluída com sucesso', [
                'grievance_id' => $grievance->id,
                'technician_name' => $technician->name
            ]);

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
            
            \Log::error('Erro na reatribuição', [
                'grievance_id' => $grievance->id,
                'technician_id' => $validated['technician_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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

    /**
     * Mark grievance as resolved after manager approval.
     */
    public function markComplete(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureManager($request->user());

        if ($grievance->status !== 'pending_approval') {
            return back()->with('warning', 'A reclamação precisa estar pendente de aprovação para concluir.');
        }

        $data = $request->validate([
            'approval_comment' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,pdf,doc,docx', 'max:10240'],
        ]);

        DB::beginTransaction();

        try {
            $grievance->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $request->user()->id,
                'resolution_notes' => $grievance->resolution_notes ?: $data['approval_comment'],
            ]);

            if (!empty($data['approval_comment'])) {
                GrievanceUpdate::log(
                    grievanceId: $grievance->id,
                    actionType: 'comment_added',
                    userId: $request->user()->id,
                    description: 'Gestor aprovou a conclusão da reclamação',
                    comment: $data['approval_comment'],
                    isPublic: true
                );
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachment = $this->storeAttachment($grievance, $file, 'manager_resolution');

                    GrievanceUpdate::log(
                        grievanceId: $grievance->id,
                        actionType: 'attachment_added',
                        userId: $request->user()->id,
                        description: 'Evidência de resolução adicionada pelo gestor',
                        metadata: [
                            'attachment_id' => $attachment->id,
                            'filename' => $attachment->original_filename,
                        ],
                        isPublic: true
                    );
                }
            }

            DB::commit();

            return back()->with('success', 'Reclamação marcada como resolvida. O utente será notificado automaticamente.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Erro ao concluir reclamação', [
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
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

        // Log de sucesso
        \Log::info('Submissão escalada para o Director com sucesso', [
            'grievance_id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'manager_id' => $request->user()->id,
            'manager_name' => $request->user()->name,
            'director_id' => $director->id ?? null,
            'director_name' => $director->name ?? 'N/A',
            'reason' => $capitalizedReason,
            'previous_status' => $previousStatus,
            'current_status' => $grievance->status,
            'escalated' => $grievance->escalated,
        ]);

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
        
        \Log::error('Erro ao enviar submissão para o Director', [
            'grievance_id' => $grievance->id,
            'user_id' => $request->user()->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all(),
        ]);

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
    
    \Log::info('=== GET DIRECTOR INTERVENTIONS for Manager: ' . $user->id . ' ===');
    
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
    
    \Log::info('Total grievances with director interventions found: ' . $grievances->count());
    
    // Formatar os dados
    $formattedGrievances = $grievances->map(function ($grievance) {
        return $this->formatGrievanceForList($grievance);
    });
    
    // Filtrar apenas as que realmente têm intervenções
    $filteredGrievances = $formattedGrievances->filter(function ($grievance) {
        return $grievance['has_director_intervention'] === true;
    })->values();
    
    \Log::info('Filtered grievances with director interventions: ' . $filteredGrievances->count());
    
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
    public function approveCompletion(Request $request, Grievance $grievance)
    {
        $this->ensureManager($request->user());

        if ($grievance->status !== 'pending_approval') {
            return back()->withErrors(['error' => 'Só pode aprovar conclusão quando o status for "Pendente de Aprovação"']);
        }

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
            'is_public' => ['required', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            // Actualizar grievance como resolvida
            $grievance->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $request->user()->id,
            ]);

            // Adicionar comentário do gestor
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'manager_approved',
                userId: $request->user()->id,
                description: 'Gestor aprovou a conclusão da submissão',
                comment: $validated['comment'],
                metadata: [
                    'is_public' => $validated['is_public'],
                    'approval_type' => 'manager_approval',
                    'approved_at' => now()->toIso8601String(),
                ],
                isPublic: $validated['is_public']
            );

            // Actualizar status publicamente
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'status_changed',
                userId: $request->user()->id,
                description: 'Submissão marcada como resolvida',
                oldValue: 'pending_approval',
                newValue: 'resolved',
                metadata: [
                    'approved_by' => $request->user()->id,
                    'approved_at' => now()->toIso8601String(),
                ],
                isPublic: true
            );

            DB::commit();

            // TODO: Enviar notificação ao utente

            return back()->with('success', 'Conclusão aprovada com sucesso! O utente será notificado.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao aprovar conclusão', [
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Erro ao aprovar conclusão: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject completion and send back to technician.
     */
    public function rejectCompletion(Request $request, Grievance $grievance)
    {
        $this->ensureManager($request->user());

        if ($grievance->status !== 'pending_approval') {
            return back()->withErrors(['error' => 'Só pode rejeitar conclusão quando o status for "Pendente de Aprovação"']);
        }

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
            'is_public' => ['required', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            // Voltar para in_progress
            $grievance->update([
                'status' => 'in_progress',
            ]);

            // Adicionar comentário do gestor
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'manager_rejected',
                userId: $request->user()->id,
                description: 'Gestor rejeitou a conclusão da submissão',
                comment: $validated['comment'],
                metadata: [
                    'is_public' => $validated['is_public'],
                    'rejection_type' => 'manager_rejection',
                    'returned_to_technician' => true,
                    'rejected_at' => now()->toIso8601String(),
                ],
                isPublic: $validated['is_public']
            );

            // Actualizar status
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'status_changed',
                userId: $request->user()->id,
                description: 'Submissão devolvida ao técnico para ajustes',
                oldValue: 'pending_approval',
                newValue: 'in_progress',
                metadata: [
                    'rejection_reason' => 'Conclusão rejeitada pelo gestor',
                ],
                isPublic: true
            );

            DB::commit();

            // TODO: Enviar notificação ao técnico

            return back()->with('success', 'Conclusão rejeitada. A submissão voltou para o técnico.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao rejeitar conclusão', [
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Erro ao rejeitar conclusão: ' . $e->getMessage()]);
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

    private function getStatusText(string $status): string
{
    $statusMap = [
        'submitted' => 'Submetida',
        'under_review' => 'Em Análise',
        'assigned' => 'Atribuída',
        'in_progress' => 'Em Andamento',
        'pending_approval' => 'Pendente de Aprovação',
        'resolved' => 'Resolvida',
        'rejected' => 'Rejeitada',
        'open' => 'Aberta',
        'closed' => 'Concluída',
        'escalated' => 'Enviada ao Director',
        'draft' => 'Rascunho',
    ];

    return $statusMap[$status] ?? $status;
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
}
