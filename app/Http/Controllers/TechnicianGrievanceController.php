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

class TechnicianGrievanceController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
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
        $path = $file->storeAs(
            'grievances/' . $grievance->id . '/attachments',
            $filename,
            'private'
        );

        $fileHash = hash_file('sha256', $file->getRealPath());

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
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);
    }
}

