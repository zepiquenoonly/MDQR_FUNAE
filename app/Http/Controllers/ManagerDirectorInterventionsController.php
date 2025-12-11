<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grievance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ManagerDirectorInterventionsController extends Controller
{
    /**
     * Listar submissões enviadas ao Director pelo Gestor atual
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Verificar se o usuário é gestor
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }
            
            if (!$user->hasRole('Gestor')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas gestores podem acessar esta API'
                ], 403);
            }
            
            Log::info('API: Buscando intervenções do Director para gestor: ' . $user->id, [
                'user_name' => $user->name
            ]);
            
            // Buscar reclamações escaladas por este gestor OU atribuídas a ele
            $grievances = Grievance::with([
                'user', 
                'assignedUser',
                'escalatedBy',
                'updates.user.roles'
            ])
            ->where(function($query) use ($user) {
                // Reclamações escaladas por este gestor
                $query->where('escalated_by', $user->id)
                      // OU atribuídas a ele e escaladas
                      ->orWhere(function($q) use ($user) {
                          $q->where('assigned_to', $user->id)
                            ->where('escalated', true);
                      });
            })
            ->where('escalated', true) // Apenas escaladas
            ->orderBy('escalated_at', 'desc')
            ->get();
            
            Log::info('Total de reclamações escaladas encontradas: ' . $grievances->count());
            
            // Formatar os dados
            $formattedGrievances = $grievances->map(function ($grievance) {
                return $this->formatGrievance($grievance);
            });
            
            // Estatísticas
            $stats = [
                'total' => $formattedGrievances->count(),
                'with_director_response' => $formattedGrievances->where('has_director_response', true)->count(),
                'pending_response' => $formattedGrievances->where('has_director_response', false)->count(),
                'by_type' => [
                    'suggestions' => $formattedGrievances->where('type', 'suggestion')->count(),
                    'grievances' => $formattedGrievances->where('type', 'grievance')->count(),
                    'complaints' => $formattedGrievances->where('type', 'complaint')->count(),
                ]
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Submissões enviadas ao Director recuperadas com sucesso',
                'data' => [
                    'grievances' => $formattedGrievances,
                    'stats' => $stats,
                    'manager' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => 'Gestor'
                    ],
                    'metadata' => [
                        'total_count' => $formattedGrievances->count(),
                        'timestamp' => now()->toISOString()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro na API de intervenções do Director: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage(),
                'debug' => env('APP_DEBUG') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
    
    /**
     * Detalhes de uma submissão específica
     */
    public function show($id, Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->hasRole('Gestor')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas gestores podem acessar esta API'
                ], 403);
            }
            
            $grievance = Grievance::with([
                'user',
                'assignedUser',
                'escalatedBy',
                'updates.user.roles'
            ])->findOrFail($id);
            
            // Verificar se o gestor tem acesso
            $hasAccess = $grievance->escalated_by == $user->id || 
                        $grievance->assigned_to == $user->id;
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem acesso a esta submissão'
                ], 403);
            }
            
            // Verificar se foi escalada
            if (!$grievance->escalated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta submissão não foi enviada ao Director'
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Detalhes da submissão recuperados com sucesso',
                'data' => $this->formatGrievance($grievance, true)
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Submissão não encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro na API de detalhes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
    
    /**
     * Formatar grievance para API
     */
    private function formatGrievance(Grievance $grievance, $detailed = false): array
    {
        // Detectar intervenções do director
        $directorUpdates = [];
        $hasDirectorResponse = false;
        
        foreach ($grievance->updates as $update) {
            $isDirectorUpdate = false;
            
            // Verificar por action_type
            if (in_array($update->action_type, [
                'director_comment',
                'director_validation_approved',
                'director_validation_rejected',
                'director_validation_needs_revision'
            ])) {
                $isDirectorUpdate = true;
            }
            
            // Verificar se o usuário é director
            if ($update->user && $update->user->hasRole('Director')) {
                $isDirectorUpdate = true;
            }
            
            if ($isDirectorUpdate) {
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
                ];
                $hasDirectorResponse = true;
            }
        }
        
        // Base data
        $data = [
            'id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'title' => $grievance->description,
            'type' => $grievance->type,
            'type_label' => $this->getTypeLabel($grievance->type),
            'priority' => $grievance->priority,
            'priority_label' => $this->getPriorityLabel($grievance->priority),
            'status' => $grievance->status,
            'status_label' => $this->getStatusLabel($grievance->status),
            'escalated' => $grievance->escalated,
            'escalated_at' => $grievance->escalated_at?->toISOString(),
            'escalation_reason' => $grievance->escalation_reason,
            'has_director_intervention' => $grievance->escalated,
            'has_director_response' => $hasDirectorResponse,
            'director_updates_count' => count($directorUpdates),
            'created_at' => $grievance->created_at->toISOString(),
            'user' => $grievance->user ? [
                'id' => $grievance->user->id,
                'name' => $grievance->user->name,
                'email' => $grievance->user->email,
            ] : null,
            'escalated_by' => $grievance->escalatedBy ? [
                'id' => $grievance->escalatedBy->id,
                'name' => $grievance->escalatedBy->name,
                'email' => $grievance->escalatedBy->email,
            ] : null,
            'assigned_to' => $grievance->assignedUser ? [
                'id' => $grievance->assignedUser->id,
                'name' => $grievance->assignedUser->name,
                'email' => $grievance->assignedUser->email,
            ] : null,
        ];
        
        // Adicionar dados detalhados se solicitado
        if ($detailed) {
            $data['description'] = $grievance->description;
            $data['category'] = $grievance->category;
            $data['subcategory'] = $grievance->subcategory;
            $data['province'] = $grievance->province;
            $data['district'] = $grievance->district;
            $data['director_updates'] = $directorUpdates;
            $data['updates_count'] = $grievance->updates->count();
            
            // Verificar validação do director no metadata
            if ($grievance->metadata && isset($grievance->metadata['director_validation'])) {
                $data['director_validation'] = $grievance->metadata['director_validation'];
            }
        }
        
        return $data;
    }
    
    /**
     * Obter label do tipo
     */
    private function getTypeLabel($type): string
    {
        $labels = [
            'suggestion' => 'Sugestão',
            'grievance' => 'Queixa',
            'complaint' => 'Reclamação',
        ];
        
        return $labels[$type] ?? ucfirst($type);
    }
    
    /**
     * Obter label da prioridade
     */
    private function getPriorityLabel($priority): string
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'critical' => 'Crítica',
        ];
        
        return $labels[$priority] ?? ucfirst($priority);
    }
    
    /**
     * Obter label do status
     */
    private function getStatusLabel($status): string
    {
        $labels = [
            'pending' => 'Pendente',
            'submitted' => 'Submetida',
            'in_progress' => 'Em Progresso',
            'resolved' => 'Resolvida',
            'closed' => 'Fechada',
            'escalated' => 'Escalada',
            'under_review' => 'Em Análise',
            'pending_approval' => 'Pendente Aprovação',
            'assigned' => 'Atribuída',
            'rejected' => 'Rejeitada',
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
}