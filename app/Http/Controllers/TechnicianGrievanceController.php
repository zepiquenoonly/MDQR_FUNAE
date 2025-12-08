<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Grievance;
use App\Models\GrievanceUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TechnicianGrievanceController extends Controller
{
    /**
     * Display the specified grievance for technician.
     */
    public function show(Grievance $grievance): Response
    {
        $this->ensureTechnician(auth()->user());

        // Ensure technician can only access assigned grievances
        if ($grievance->assigned_to !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar esta reclamação.');
        }

        // Load necessary relationships
        $grievance->load([
            'user',
            'updates.user',
            'attachments'
        ]);

        return Inertia::render('Technician/GrievanceDetail', [
            'complaint' => $grievance,
        ]);
    }

    /**
     * Start working on a grievance.
     */
    public function startWork(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureTechnician($request->user());

        // Ensure technician can only work on assigned grievances
        if ($grievance->assigned_to !== $request->user()->id) {
            return back()->with('error', 'Você não tem permissão para trabalhar nesta reclamação.');
        }

        if ($grievance->status !== 'assigned') {
            return back()->with('warning', 'Esta reclamação não pode ser iniciada.');
        }

        DB::transaction(function () use ($grievance, $request) {
            $grievance->update(['status' => 'in_progress']);

            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'status_changed',
                userId: $request->user()->id,
                description: 'Técnico iniciou o trabalho na reclamação',
                metadata: [
                    'previous_status' => 'assigned',
                    'new_status' => 'in_progress',
                    'started_by' => $request->user()->name,
                ],
                isPublic: true
            );
        });

        return back()->with('success', 'Trabalho iniciado com sucesso.');
    }

    /**
     * Store an update for the grievance.
     */
    public function storeUpdate(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureTechnician($request->user());

        // Ensure technician can only update assigned grievances
        if ($grievance->assigned_to !== $request->user()->id) {
            return back()->with('error', 'Você não tem permissão para atualizar esta reclamação.');
        }

        $data = $request->validate([
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
            'is_public' => ['boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,pdf,doc,docx', 'max:10240'],
        ]);

        DB::beginTransaction();

        try {
            // Store the update
            $update = GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'comment_added',
                userId: $request->user()->id,
                description: 'Técnico adicionou uma atualização',
                comment: $data['comment'],
                isPublic: $data['is_public'] ?? true
            );

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachment = $this->storeAttachment($grievance, $file, 'technician_update');

                    GrievanceUpdate::log(
                        grievanceId: $grievance->id,
                        actionType: 'attachment_added',
                        userId: $request->user()->id,
                        description: 'Anexo adicionado pelo técnico',
                        metadata: [
                            'attachment_id' => $attachment->id,
                            'filename' => $attachment->original_filename,
                        ],
                        isPublic: true
                    );
                }
            }

            DB::commit();

            return back()->with('success', 'Atualização adicionada com sucesso.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Erro ao adicionar atualização', [
                'grievance_id' => $grievance->id,
                'user_id' => $request->user()->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Request completion approval from manager.
     */
    public function requestCompletion(Request $request, Grievance $grievance): RedirectResponse
    {
        $this->ensureTechnician($request->user());

        // Ensure technician can only request completion for assigned grievances
        if ($grievance->assigned_to !== $request->user()->id) {
            return back()->with('error', 'Você não tem permissão para solicitar conclusão desta reclamação.');
        }

        if ($grievance->status !== 'in_progress') {
            return back()->with('warning', 'Apenas reclamações em andamento podem ser solicitadas para conclusão.');
        }

        $data = $request->validate([
            'resolution_notes' => ['required', 'string', 'min:20', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,pdf,doc,docx', 'max:10240'],
        ]);

        DB::beginTransaction();

        try {
            $grievance->update([
                'status' => 'pending_approval',
                'resolution_notes' => $data['resolution_notes'],
            ]);

            // Log the completion request
            GrievanceUpdate::log(
                grievanceId: $grievance->id,
                actionType: 'completion_requested',
                userId: $request->user()->id,
                description: 'Técnico solicitou aprovação da conclusão',
                comment: $data['resolution_notes'],
                isPublic: true
            );

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachment = $this->storeAttachment($grievance, $file, 'completion_request');

                    GrievanceUpdate::log(
                        grievanceId: $grievance->id,
                        actionType: 'attachment_added',
                        userId: $request->user()->id,
                        description: 'Evidência de conclusão adicionada',
                        metadata: [
                            'attachment_id' => $attachment->id,
                            'filename' => $attachment->original_filename,
                        ],
                        isPublic: true
                    );
                }
            }

            DB::commit();

            return back()->with('success', 'Solicitação de conclusão enviada ao gestor para aprovação.');
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
     * Ensure authenticated user is a technician.
     */
    private function ensureTechnician($user): void
    {
        abort_if(!$user || !$user->hasRole('Técnico'), 403);
    }

    /**
     * Store attachments uploaded by the technician.
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
            ],
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);
    }
}
