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

class ManagerGrievanceController extends Controller
{
    /**
     * Update priority for a grievance.
     */
    public function updatePriority(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureManager($request->user());

        $data = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $grievance->update(['priority' => $data['priority']]);

        return back()->with('success', 'Prioridade atualizada com sucesso.');
    }

    /**
     * Reassign grievance to another technician.
     */
    public function reassign(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureManager($request->user());

        $data = $request->validate([
            'technician_id' => [
                'required',
                Rule::exists('users', 'id'),
            ],
        ]);

        $technician = User::role('Técnico')->findOrFail($data['technician_id']);

        $grievance->update([
            'assigned_to' => $technician->id,
            'assigned_at' => now(),
        ]);

        return back()->with('success', 'Reclamação atribuída ao técnico selecionado.');
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
     * Reject the completion request and send the grievance back to the technician.
     */
    public function rejectCompletion(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureManager($request->user());

        if ($grievance->status !== 'pending_approval') {
            return back()->with('warning', 'Apenas reclamações pendentes de aprovação podem ser devolvidas.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $metadata = $grievance->metadata ?? [];
        $metadata['rejection_reason'] = $data['reason'];
        $metadata['rejected_by'] = $request->user()->id;
        $metadata['rejected_at'] = now()->toIso8601String();

        DB::transaction(function () use ($grievance, $metadata, $request, $data) {
            $grievance->update([
                'status' => 'in_progress',
                'metadata' => $metadata,
            ]);

            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'comment_added',
                userId: $request->user()->id,
                description: 'Gestor devolveu a reclamação para ajustes',
                comment: $data['reason'],
                isPublic: true
            );
        });

        return back()->with('info', 'Solicitação devolvida ao técnico com os motivos informados.');
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
                'uploaded_via' => $source,
            ],
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);
    }
}

