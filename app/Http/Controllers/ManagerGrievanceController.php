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
    public function show(Grievance $grievance): Response
    {
        // CORREÇÃO: Obter o usuário autenticado corretamente
        $user = auth()->user();
        $this->ensureManager($user);

        // Carregar relações necessárias - INCLUIR TODOS OS TIPOS
        $grievance->load([
            'user',
            'assignedUser', 
            'updates.user' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'attachments'
        ]);

        // Carregar comentários do gestor
        $managerComments = $grievance->updates()
            ->where('user_id', $user->id)
            ->whereIn('action_type', ['comment_added', 'manager_comment', 'manager_approved', 'manager_rejected'])
            ->orderBy('created_at', 'desc')
            ->get();

        // CORREÇÃO: Carregar comentários do director visíveis para gestores
        $directorComments = $grievance->updates()
            ->whereIn('action_type', [
                'director_comment', 
                'director_validation_approved',
                'director_validation_rejected', 
                'director_validation_needs_revision'
            ])
            ->where(function ($query) {
                $query->where('is_public', true)
                    ->orWhere(function ($q) {
                        // Comentários internos visíveis para gestores
                        $q->where('is_public', false)
                            ->whereJsonContains('metadata->visible_to', 'manager');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $technicians = User::whereHas('roles', function($query) {
            $query->where('name', 'Técnico');
        })->get(['id', 'name', 'email']);

        // Preparar dados para o frontend
        $complaintData = [
            'id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'title' => $grievance->description,
            'description' => $grievance->description,
            'type' => $grievance->type,
            'priority' => $grievance->priority,
            'status' => $grievance->status,
            'category' => $grievance->category,
            'created_at' => $grievance->created_at,
            'submitted_at' => $grievance->submitted_at,
            'province' => $grievance->province,
            'district' => $grievance->district,
            'assigned_to' => $grievance->assigned_to,
            'resolution_notes' => $grievance->resolution_notes,
            'user' => $grievance->user ? [
                'name' => $grievance->user->name,
            ] : null,
            'technician' => $grievance->assignedUser ? [
                'id' => $grievance->assignedUser->id,
                'name' => $grievance->assignedUser->name,
                'email' => $grievance->assignedUser->email,
            ] : null,
            'attachments' => $grievance->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'name' => $attachment->original_filename,
                    'original_filename' => $attachment->original_filename,
                    'size' => $attachment->size,
                    'uploaded_at' => $attachment->uploaded_at,
                    'url' => route('attachments.download', $attachment->id),
                ];
            })->toArray(),
            'activities' => $grievance->updates->map(function ($update) {
                return [
                    'id' => $update->id,
                    'type' => $update->action_type,
                    'description' => $update->description,
                    'comment' => $update->comment,
                    'metadata' => $update->metadata,
                    'created_at' => $update->created_at,
                    'user' => $update->user ? [
                        'name' => $update->user->name,
                        'role' => $update->user->getRoleNames()->first(),
                    ] : null,
                ];
            })->toArray(),
            'manager_comments' => $managerComments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'is_public' => $comment->is_public,
                    'created_at' => $comment->created_at,
                    'action_type' => $comment->action_type,
                ];
            })->toArray(),
            'director_comments' => $directorComments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->comment ?? $comment->description,
                    'type' => $comment->action_type,
                    'created_at' => $comment->created_at,
                    'user' => $comment->user ? [
                        'name' => $comment->user->name,
                        'role' => $comment->user->getRoleNames()->first(),
                    ] : null,
                    'metadata' => $comment->metadata ?? [],
                    'is_public' => $comment->is_public ?? false,
                ];
            })->toArray(),
        ];

        // Filtrar anexos de resolução se existirem
        $resolutionAttachments = $grievance->attachments->filter(function ($attachment) {
            return str_contains($attachment->metadata['uploaded_via'] ?? '', 'resolution') || 
                   str_contains($attachment->metadata['uploaded_via'] ?? '', 'manager');
        });

        if ($resolutionAttachments->count() > 0) {
            $complaintData['resolution_attachments'] = $resolutionAttachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'original_filename' => $attachment->original_filename,
                    'size' => $attachment->size,
                    'uploaded_at' => $attachment->uploaded_at,
                    'url' => route('attachments.download', $attachment->id),
                ];
            })->toArray();
        }

        return Inertia::render('Manager/GrievanceDetail', [
            'complaint' => $complaintData,
            'technicians' => $technicians
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

            // Atualizar grievance
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
                    // Último fallback: qualquer usuário administrador
                    $director = User::whereHas('roles', function($query) {
                        $query->whereIn('name', ['Admin', 'Administrador']);
                    })->first();
                }
            }

            // Capitalizar a primeira letra do motivo em português
            $capitalizedReason = ucfirst(mb_strtolower($validated['reason'], 'UTF-8'));
            
            // Garantir que a primeira letra após ponto final também seja capitalizada
            $capitalizedReason = preg_replace_callback('/\.\s*(\w)/', function($matches) {
                return '. ' . mb_strtoupper($matches[1], 'UTF-8');
            }, $capitalizedReason);

            // Usar o método do modelo para marcar como escalado
            $grievance->markAsEscalated(
                escalatedBy: $request->user()->id,
                reason: $capitalizedReason
            );

            // Atualizar status e atribuição
            $grievance->update([
                'status' => 'escalated',
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
                    'previous_assigned_to' => $grievance->getOriginal('assigned_to'),
                    'previous_status' => $grievance->getOriginal('status'),
                    'priority_changed_to' => 'high',
                    'is_public' => true,
                ],
                isPublic: true
            );

            // Criar atividade de mudança de status
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'status_changed',
                userId: $request->user()->id,
                description: 'Status alterado para "Escalado ao Director"',
                oldValue: $grievance->getOriginal('status'),
                newValue: 'escalated',
                metadata: [
                    'changed_by_manager' => true,
                    'escalation_reason' => $capitalizedReason,
                ],
                isPublic: true
            );

            // Criar atividade de mudança de prioridade
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'priority_changed',
                userId: $request->user()->id,
                description: 'Prioridade aumentada para ALTA devido a escalamento para Director',
                oldValue: $grievance->getOriginal('priority'),
                newValue: 'high',
                metadata: [
                    'reason' => 'Escalamento para Director',
                    'escalation_reason' => $capitalizedReason,
                ],
                isPublic: true
            );

            // Se houve mudança de técnico para director
            if ($grievance->getOriginal('assigned_to') !== $director->id) {
                GrievanceUpdate::log(
                    grievanceId: $grievance->id,
                    actionType: 'technician_reassigned',
                    userId: $request->user()->id,
                    description: 'Submissão reatribuída ao Director',
                    oldValue: $grievance->getOriginal('assigned_to'),
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
                'previous_status' => $grievance->getOriginal('status'),
                'new_status' => 'escalated',
            ]);

            // Para requisições Inertia
            if ($request->header('X-Inertia')) {
                return back()->with([
                    'success' => 'Submissão enviada ao Director com sucesso!',
                    'updatedGrievance' => [
                        'id' => $grievance->id,
                        'reference_number' => $grievance->reference_number,
                        'status' => 'escalated',
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
                'message' => 'Submissão enviada ao Director com sucesso!',
                'grievance' => [
                    'id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'status' => 'escalated',
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
            // Atualizar grievance como resolvida
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

            // Atualizar status publicamente
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

            // Atualizar status
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
}
