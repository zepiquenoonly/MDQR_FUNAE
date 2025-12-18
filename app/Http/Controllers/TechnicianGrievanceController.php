<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Grievance;
use App\Models\GrievanceUpdate;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TechnicianGrievanceController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }

    /**
     * Show grievance detail page for technician.
     */
    public function show(Request $request, Grievance $grievance)
    {
        $this->ensureOwnership($request->user(), $grievance);

        $grievance->load([
            'updates.user',
            'attachments',
            'user',
        ]);

        return Inertia::render('Technician/GrievanceDetail', [
            'grievance' => [
                'id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'title' => $grievance->title,
                'description' => $grievance->description,
                'status' => $grievance->status,
                'status_label' => $this->getStatusLabel($grievance->status),
                'priority' => $grievance->priority,
                'category' => $grievance->category,
                'district' => $grievance->district,
                'contact_name' => $grievance->user?->name,
                'contact_email' => $grievance->user?->email,
                'contact_phone' => $grievance->user?->phone,
                'submitted_at' => $grievance->created_at,
                'updated_at' => $grievance->updated_at,
                'created_at' => $grievance->created_at,
                'is_pending_approval' => $grievance->status === 'pending_approval',
                'can_start' => !in_array($grievance->status, ['in_progress', 'pending_approval', 'closed', 'rejected']),
                'can_request_completion' => $grievance->status === 'in_progress',
                'updates' => $grievance->updates->map(fn($update) => [
                    'id' => $update->id,
                    'description' => $update->description,
                    'comment' => $update->comment,
                    'created_at' => $update->created_at,
                    'user' => $update->user ? [
                        'id' => $update->user->id,
                        'name' => $update->user->name,
                    ] : null,
                    'attachments' => $update->metadata['attachment_id'] ?? null ? [
                        [
                            'id' => $update->metadata['attachment_id'],
                            'original_filename' => $update->metadata['filename'],
                            'url' => route('attachments.download', $update->metadata['attachment_id']),
                        ]
                    ] : [],
                ]),
                'attachments' => $grievance->attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'original_filename' => $attachment->original_filename,
                    'size' => $attachment->size,
                    'url' => route('attachments.download', $attachment->id),
                ]),
            ]
        ]);
    }

    /**
     * Get status label in Portuguese.
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'submitted' => 'Submetida',
            'under_review' => 'Sob Revisão',
            'assigned' => 'Atribuída',
            'in_progress' => 'Em Andamento',
            'pending_approval' => 'Pendente Aprovação',
            'closed' => 'Concluída',
            'rejected' => 'Rejeitada',
            default => 'Desconhecida',
        };
    }

    /**
     * Mark grievance as in progress.
     */
    public function startWork(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureOwnership($request->user(), $grievance);

        if (!in_array($grievance->status, ['submitted', 'under_review', 'assigned'])) {
            return back()->with('warning', 'Esta reclamação já foi iniciada ou aguarda aprovação.');
        }

        $metadata = $grievance->metadata ?? [];
        $metadata['started_by'] = $request->user()->id;
        $metadata['started_at'] = now()->toIso8601String();

        $grievance->update([
            'status' => 'in_progress',
            'metadata' => $metadata,
        ]);

        return back()->with('success', 'Reclamação marcada como "Em Andamento".');
    }

    /**
     * Store a technician update/comment with optional attachments.
     */
    public function storeUpdate(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureOwnership($request->user(), $grievance);

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'min:5'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,pdf,doc,docx', 'max:10240'],
        ]);

        if (empty($validated['comment']) && !$request->hasFile('attachments')) {
            throw ValidationException::withMessages([
                'comment' => 'Adicione um comentário ou anexos para registar a atualização.',
            ]);
        }

        DB::beginTransaction();

        try {
            $isPublic = $validated['is_public'] ?? true;
            $update = GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'comment_added',
                userId: $request->user()->id,
                description: $validated['description'] ?? 'Atualização do técnico',
                comment: $validated['comment'] ?? null,
                isPublic: $isPublic
            );

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachment = $this->storeAttachment($grievance, $file);

                    GrievanceUpdate::log(
                        grievanceId: $grievance->id,
                        actionType: 'attachment_added',
                        userId: $request->user()->id,
                        description: 'Evidência anexada pelo técnico',
                        metadata: [
                            'attachment_id' => $attachment->id,
                            'filename' => $attachment->original_filename,
                        ],
                        isPublic: $isPublic
                    );
                }
            }

            DB::commit();

            if ($update->is_public) {
                $grievance->loadMissing('user');
                $this->notificationService->notifyCommentAdded($grievance, $update);
            }

            return back()->with('success', 'Atualização registada com sucesso.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Erro ao guardar atualização do técnico', [
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Request final approval from the manager.
     */
    public function requestCompletion(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureOwnership($request->user(), $grievance);

        if ($grievance->status === 'pending_approval') {
            return back()->with('info', 'Esta reclamação já aguarda aprovação do gestor.');
        }

        if ($grievance->status !== 'in_progress') {
            return back()->with('warning', 'Só é possível solicitar conclusão para reclamações em andamento.');
        }

        $validated = $request->validate([
            'resolution_summary' => ['required', 'string', 'min:10'],
            'notify_user' => ['sometimes', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,pdf,doc,docx', 'max:10240'],
        ]);

        DB::beginTransaction();

        try {
            $metadata = $grievance->metadata ?? [];
            $metadata['pending_approval_requested_at'] = now()->toIso8601String();
            $metadata['pending_approval_requested_by'] = $request->user()->id;

            $grievance->update([
                'status' => 'pending_approval',
                'resolution_notes' => $validated['resolution_summary'],
                'metadata' => $metadata,
            ]);

            $isPublic = $validated['notify_user'] ?? true;

            $summaryUpdate = GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'comment_added',
                userId: $request->user()->id,
                description: 'Técnico solicitou a conclusão da reclamação',
                comment: $validated['resolution_summary'],
                isPublic: $isPublic
            );

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachment = $this->storeAttachment($grievance, $file);

                    GrievanceUpdate::log(
                        grievanceId: $grievance->id,
                        actionType: 'attachment_added',
                        userId: $request->user()->id,
                        description: 'Evidência final anexada',
                        metadata: [
                            'attachment_id' => $attachment->id,
                            'filename' => $attachment->original_filename,
                        ],
                        isPublic: $isPublic
                    );
                }
            }

            DB::commit();

            if ($summaryUpdate->is_public) {
                $grievance->loadMissing('user');
                $this->notificationService->notifyCommentAdded($grievance, $summaryUpdate);
            }

            return back()->with('success', 'Solicitação de conclusão enviada ao gestor.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Erro ao solicitar conclusão', [
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Ensure the authenticated technician can manage the grievance.
     */
    private function ensureOwnership(?User $user, Grievance $grievance): void
    {
        abort_if(!$user || !$user->hasRole('Técnico'), 403);
        abort_if($grievance->assigned_to !== $user->id, 403, 'Esta reclamação não está atribuída a si.');
    }

    /**
     * Store an attachment for a grievance.
     */
    private function storeAttachment(Grievance $grievance, UploadedFile $file): Attachment
    {
        $originalFilename = $file->getClientOriginalName();
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        $publicPath = 'uploads/grievances/' . $grievance->id . '/attachments';
        $fullPath = public_path($publicPath);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $file->move($fullPath, $filename);
        $path = '/' . $publicPath . '/' . $filename;

        // Hash after move to ensure we hash the stored file
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
                'uploaded_via' => 'technician_dashboard',
            ],
            'uploaded_by' => auth('web')->user()?->id ?? null,
            'uploaded_at' => now(),
        ]);
    }

 
/**
 * Método simples de chat/comentários para técnicos
 */
public function addComment(Request $request, Grievance $grievance)
{
    $this->ensureOwnership($request->user(), $grievance);

    try {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
            'is_public' => ['sometimes', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,pdf,doc,docx,txt', 'max:10240'],
        ]);

        DB::beginTransaction();

        // Criar o comentário
        $update = GrievanceUpdate::log(
            grievanceId: $grievance->id,
            actionType: 'technician_comment',
            userId: $request->user()->id,
            description: 'Comentário do técnico',
            comment: $validated['comment'],
            isPublic: $validated['is_public'] ?? true,
            metadata: [
                'comment_type' => 'technician',
                'timestamp' => now()->toISOString(),
            ]
        );

        $attachments = [];
        // Processar anexos se existirem
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachment = $this->storeAttachment($grievance, $file);
                $attachments[] = [
                    'id' => $attachment->id,
                    'name' => $attachment->original_filename,
                    'url' => route('attachments.download', $attachment->id),
                ];

                // Registrar anexo como update separado
                GrievanceUpdate::log(
                    grievanceId: $grievance->id,
                    actionType: 'attachment_added',
                    userId: $request->user()->id,
                    description: 'Anexo adicionado pelo técnico',
                    metadata: [
                        'attachment_id' => $attachment->id,
                        'filename' => $attachment->original_filename,
                        'comment_id' => $update->id,
                    ],
                    isPublic: $validated['is_public'] ?? true
                );
            }
        }

        DB::commit();

        // Retornar resposta
        return response()->json([
            'success' => true,
            'message' => 'Comentário enviado com sucesso',
            'data' => [
                'comment' => [
                    'id' => $update->id,
                    'content' => $update->comment,
                    'created_at' => $update->created_at->toISOString(),
                    'user' => [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'role' => 'Técnico',
                    ],
                    'is_public' => $update->is_public,
                ],
                'attachments' => $attachments,
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao enviar comentário: ' . $e->getMessage(),
        ], 500);
    }
}
}
 
