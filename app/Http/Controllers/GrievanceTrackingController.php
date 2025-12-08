<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GrievanceTrackingController extends Controller
{
    /**
     * Show the grievance tracking page.
     */
    public function index(): Response
    {
        return Inertia::render('GrievanceTracking/Index');
    }

    /**
     * Track a grievance by its reference number.
     */
    public function track(Request $request)
    {
        \Log::info('Track request received', ['data' => $request->all()]);

        $request->validate([
            'reference_number' => ['required', 'string'],
        ]);

        $referenceNumber = strtoupper(trim($request->reference_number));

        \Log::info('Searching for grievance', ['reference' => $referenceNumber]);

        // Find the grievance by reference number
        $grievance = Grievance::where('reference_number', $referenceNumber)
            ->with([
                'attachments',
                'user:id,name,email',
                'assignedUser:id,name',
                'resolvedBy:id,name',
                'project:id,name',
                'publicUpdates' => function ($query) {
                    $query->with('user:id,name')
                        ->orderBy('created_at', 'asc');
                }
            ])
            ->first();

        if (!$grievance) {
            \Log::warning('Grievance not found', ['reference' => $referenceNumber]);
            return response()->json([
                'success' => false,
                'message' => 'Reclamação não encontrada. Verifique o código de rastreamento.',
            ], 404);
        }

        \Log::info('Grievance found', ['id' => $grievance->id]);

        return response()->json([
            'success' => true,
            'grievance' => [
                'id' => $grievance->id,
                'reference_number' => $grievance->reference_number,
                'description' => $grievance->description,
                'category' => $grievance->category,
                'subcategory' => $grievance->subcategory,
                'status' => $grievance->status,
                'status_label' => $grievance->status_label,
                'priority' => $grievance->priority,
                'priority_label' => $this->getPriorityLabel($grievance->priority),
                'type' => $grievance->type,
                'type_label' => $this->getTypeLabel($grievance->type),
                'province' => $grievance->province,
                'district' => $grievance->district,
                'location_details' => $grievance->location_details,
                'submitted_at' => $grievance->submitted_at,
                'assigned_at' => $grievance->assigned_at,
                'resolved_at' => $grievance->resolved_at,
                'resolution_notes' => $grievance->resolution_notes,
                'assigned_user' => $grievance->assignedUser ? [
                    'name' => $grievance->assignedUser->name,
                ] : null,
                'resolved_by' => $grievance->resolvedBy ? [
                    'name' => $grievance->resolvedBy->name,
                ] : null,
                'project' => $grievance->project ? [
                    'id' => $grievance->project->id,
                    'name' => $grievance->project->name,
                ] : null,
                'attachments' => $grievance->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'original_filename' => $attachment->original_filename,
                        'mime_type' => $attachment->mime_type,
                        'size' => $attachment->size,
                        'uploaded_at' => $attachment->uploaded_at,
                        'url' => url($attachment->path),
                        'path' => $attachment->path,
                    ];
                }),
                'updates' => $grievance->publicUpdates->map(function ($update) {
                    return [
                        'id' => $update->id,
                        'action_type' => $update->action_type,
                        'action_label' => $update->action_label,
                        'description' => $update->formatted_description,
                        'comment' => $update->comment,
                        'user' => $update->user ? [
                            'name' => $update->user->name,
                        ] : null,
                        'created_at' => $update->created_at,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get priority label in Portuguese
     */
    private function getPriorityLabel($priority)
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'urgent' => 'Urgente',
        ];

        return $labels[$priority] ?? $priority;
    }

    /**
     * Get type label in Portuguese
     */
    private function getTypeLabel($type)
    {
        $labels = [
            'complaint' => 'Reclamação',
            'suggestion' => 'Sugestão',
            'inquiry' => 'Queixa',
        ];

        return $labels[$type] ?? $type;
    }
}
