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
    // NO DirectorComplaintsController.php, no método index, adicione:
public function index(Request $request)
{
     $user = $request->user();
    $this->checkAccess($user);

    $query = Grievance::with(['user', 'assignedUser', 'project'])
        ->latest();

    // Aplicar filtros dinâmicos
    $this->applyFilters($query, $request);

    // GARANTIR que casos escalados para director sejam incluídos
    // Opcional: Mostrar apenas casos relevantes para o director
    // $query->where('status', 'escalated')->orWhere('assigned_to', $user->id);

    $submissions = $query->get()
    ->map(function ($grievance) {
        // Verificar se é um caso escalado para director
        $isEscalatedToDirector = $grievance->status === 'escalated' || 
                                ($grievance->metadata && 
                                 isset($grievance->metadata['is_escalated_to_director']) && 
                                 $grievance->metadata['is_escalated_to_director'] === true);
        
        // Determinar especificidade baseada no escalamento
        $specificity = 'normal';
        if ($isEscalatedToDirector) {
            $specificity = 'high';
        } elseif ($grievance->priority === 'critical') {
            $specificity = 'critical';
        } elseif ($grievance->priority === 'high') {
            $specificity = 'high';
        }
        
        return [
            'id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'subject' => $grievance->description,
            'description' => $grievance->description,
            'type' => $grievance->type,
            'priority' => $grievance->priority,
            'status' => $grievance->status,
            'category' => $grievance->category,
            'created_at' => $grievance->created_at,
            'submitted_at' => $grievance->submitted_at,
            'province' => $grievance->province,
            'district' => $grievance->district,
            'is_anonymous' => $grievance->is_anonymous,
            'specificity' => $specificity, 
            'metadata' => $grievance->metadata ?? [],
            'is_escalated_to_director' => $isEscalatedToDirector,
            'escalation_reason' => $grievance->metadata['escalation_reason'] ?? null,
            'escalated_by' => $grievance->metadata['escalated_by_name'] ?? null,
            'escalated_at' => $grievance->metadata['escalated_at'] ?? null,
            'user' => $grievance->user ? [
                'name' => $grievance->user->name,
                'email' => $grievance->user->email
            ] : null,
            'assigned_to' => $grievance->assignedUser ? [
                'id' => $grievance->assignedUser->id,
                'name' => $grievance->assignedUser->name
            ] : null,
            'project' => $grievance->project ? [
                'name' => $grievance->project->name
            ] : null,
        ];
    });

    return Inertia::render('Director/SubmissionsPage', [
        // ADICIONE USER E ROLE:
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
        ] : null,
        'role' => app(AuthController::class)->getUserNormalizedRole($user),
        
        'submissions' => $submissions,
        'stats' => $this->getDashboardStats(),
        'filters' => $request->only([
            'search', 'status', 'province', 'priority', 'type',
            'date_from', 'date_to', 'category', 'is_anonymous'
        ]),
        'filterOptions' => $this->getFilterOptions(),
    ]);
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

        // Formatar comentários DOS UPDATES (em vez de uma relação separada comments)
        $comments = $grievance->updates
            ->whereIn('action_type', [
                'comment_added', 
                'manager_comment', 
                'technician_comment',
                'director_comment', // Adicionar tipo para comentários do director
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
            // ADICIONE USER E ROLE:
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
            ] : null,
            'role' => app(AuthController::class)->getUserNormalizedRole($user),// ← Pode ser 'director' específico
            
            'submission' => $submissionData,
            'comments' => $comments,
            'technicians' => $this->getAvailableTechnicians(),
            'managers' => $this->getAvailableManagers(),
            'projects' => $this->getActiveProjects(),
            
            // ADICIONE timeline_data se necessário
            'timeline_data' => $grievance->updates->sortByDesc('created_at')->values()->toArray(),
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        abort(404, 'Reclamação não encontrada');
    }
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
     * Atualizar status de uma reclamação
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

            // Atualizar a reclamação
            $updates = [
                'assigned_to' => $validated['technician_id'],
                'status' => 'assigned',
                'assigned_at' => now(),
            ];

            if ($request->filled('priority')) {
                $updates['priority'] = $validated['priority'];
            }

            $grievance->update($updates);

            // Atualizar workload do novo técnico
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
     * Atualizar prioridade de uma reclamação
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
     * Atualizar informações básicas da reclamação
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

            // Atualizar a reclamação
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

            // Atualizar workload do técnico se houver um atribuído
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
    public function reject(Request $request, $id)
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

            // Atualizar workload do técnico se houver um atribuído
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

    /**
     * Exportar reclamações para CSV
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $query = Grievance::with(['user', 'assignedUser', 'project']);
        $this->applyFilters($query, $request);

        $submissions = $query->get();

        $fileName = 'reclamacoes_director_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->stream(function() use ($submissions) {
            $file = fopen('php://output', 'w');
            
            // Cabeçalho em português
            fputcsv($file, [
                'ID',
                'Número Referência',
                'Tipo',
                'Categoria',
                'Subcategoria',
                'Descrição',
                'Província',
                'Distrito',
                'Status',
                'Prioridade',
                'Atribuído A',
                'Data Submissão',
                'Data Atribuição',
                'Data Resolução',
                'Projeto',
                'Submetido Por',
                'Email',
                'Telefone',
                'Anónimo'
            ], ';');

            // Dados
            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->id,
                    $submission->reference_number,
                    $submission->type_label,
                    $submission->category,
                    $submission->subcategory ?? '',
                    $submission->description,
                    $submission->province ?? '',
                    $submission->district ?? '',
                    $submission->status_label,
                    $submission->priority,
                    $submission->assignedUser->name ?? '',
                    $submission->submitted_at->format('d/m/Y H:i'),
                    $submission->assigned_at?->format('d/m/Y H:i') ?? '',
                    $submission->resolved_at?->format('d/m/Y H:i') ?? '',
                    $submission->project?->name ?? '',
                    $submission->display_name,
                    $submission->effective_email ?? '',
                    $submission->contact_phone ?? '',
                    $submission->is_anonymous ? 'Sim' : 'Não'
                ], ';');
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * ==============================================
     * MÉTODOS AUXILIARES PRIVADOS
     * ==============================================
     */

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

    $validated = $request->validate([
        'status' => 'required|in:approved,rejected,needs_revision',
        'comment' => 'required|string|min:10|max:2000',
        'notify_manager' => 'boolean',
        'notify_technician' => 'boolean',
        'notify_user' => 'boolean',
    ]);

    $grievance = Grievance::findOrFail($id);
    
    // Verificar se é um caso escalado para director usando o método do modelo
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
        // Atualizar metadados da validação
        $metadata = $grievance->metadata ?? [];
        $metadata['director_validation'] = [
            'status' => $validated['status'],
            'comment' => $validated['comment'],
            'validated_by' => $user->id,
            'validated_by_name' => $user->name,
            'validated_at' => now()->toISOString(),
            'notifications' => [
                'manager' => $validated['notify_manager'] ?? false,
                'technician' => $validated['notify_technician'] ?? false,
                'user' => $validated['notify_user'] ?? false,
            ]
        ];

        // Se aprovado, marcar como validado e manter atribuição
        if ($validated['status'] === 'approved') {
            $metadata['is_validated'] = true;
            $metadata['validation_status'] = 'approved';
            
            // Atualizar prioridade se necessário
            if ($grievance->priority !== 'high' && $grievance->priority !== 'critical') {
                $grievance->priority = 'high';
            }
            
            // Criar update para validação aprovada
            $this->createDirectorUpdate(
                $grievance,
                'director_validation_approved',
                "Director aprovou a submissão",
                $validated['comment'],
                $user->id,
                [
                    'validation_status' => 'approved',
                    'visible_to' => ['manager', 'technician', 'director']
                ]
            );
        } 
        // Se rejeitado, retornar ao gestor
        elseif ($validated['status'] === 'rejected') {
            $metadata['is_validated'] = false;
            $metadata['validation_status'] = 'rejected';
            
            // Encontrar o gestor original ou gestor disponível
            $manager = User::role('Gestor')->first();
            if ($manager) {
                $grievance->assigned_to = $manager->id;
                $grievance->status = 'under_review';
                
                // Criar update para retorno ao gestor
                $this->createDirectorUpdate(
                    $grievance,
                    'director_validation_rejected',
                    "Director rejeitou a submissão",
                    $validated['comment'],
                    $user->id,
                    [
                        'validation_status' => 'rejected',
                        'returned_to_manager' => true,
                        'manager_id' => $manager->id,
                        'visible_to' => ['manager', 'technician', 'director']
                    ]
                );
            }
        }
        // Se precisa de revisão
        elseif ($validated['status'] === 'needs_revision') {
            $metadata['needs_revision'] = true;
            $metadata['revision_notes'] = $validated['comment'];
            
            // Criar update para solicitar revisão
            $this->createDirectorUpdate(
                $grievance,
                'director_validation_needs_revision',
                "Director solicitou revisão da submissão",
                $validated['comment'],
                $user->id,
                [
                    'validation_status' => 'needs_revision',
                    'visible_to' => ['manager', 'technician', 'director']
                ]
            );
        }

        $grievance->metadata = $metadata;
        $grievance->save();

        // Enviar notificações se solicitado
        $this->sendValidationNotifications($grievance, $validated);

        DB::commit();

        // Verificar se é uma requisição Inertia
        if ($request->header('X-Inertia')) {
            return back()->with([
                'success' => 'Validação realizada com sucesso!',
                'validation' => $metadata['director_validation']
            ]);
        }

        // Para requisições API/JSON
        return response()->json([
            'success' => true,
            'message' => 'Validação realizada com sucesso!',
            'validation' => $metadata['director_validation']
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Erro ao validar submissão: ' . $e->getMessage(), [
            'exception' => $e,
            'grievance_id' => $id,
            'user_id' => $user->id
        ]);
        
        $errorMessage = 'Erro ao validar: ' . $e->getMessage();
        
        if ($request->header('X-Inertia')) {
            return back()->withErrors(['error' => $errorMessage]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ], 500);
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
        \Log::error('Erro ao adicionar comentário: ' . $e->getMessage(), [
            'exception' => $e,
            'grievance_id' => $id,
            'user_id' => $user->id
        ]);
        
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

    $path = $file->storeAs(
        'grievances/' . $grievance->id . '/comments/' . $comment->id,
        $filename,
        'private'
    );

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
     * Atualizar workload do técnico
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
}