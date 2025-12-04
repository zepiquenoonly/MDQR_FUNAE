<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Grievance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GrievanceController extends Controller
{
    /**
     * Display a listing of grievances for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $filters = $request->only(['priority', 'status', 'category', 'type']);

        // Query base para TODOS os tipos de grievances
        $query = Grievance::query();

        // Se o usuário for utente, mostrar apenas suas reclamações
        if ($user->hasRole('utente')) {
            $query->where('user_id', $user->id)
                ->orWhere(function ($query) use ($user) {
                    $query->where('is_anonymous', false)
                        ->where('contact_email', $user->email);
                });
        }

        // Aplicar filtros para a visualização normal
        foreach ($filters as $field => $value) {
            if ($value) {
                $query->where($field, $value);
            }
        }

        // Dados filtrados para a visualização normal - INCLUIR TODOS OS TIPOS
        $grievances = $query->with(['attachments'])
            ->orderBy('submitted_at', 'desc')
            ->get();

    // Query para TODOS os dados (sem filtros)
    $allGrievancesQuery = Grievance::query();

    if ($user->hasRole('utente')) {
        $allGrievancesQuery->where('user_id', $user->id)
            ->orWhere(function ($query) use ($user) {
                $query->where('is_anonymous', false)
                    ->where('contact_email', $user->email);
            });
    }

        $allComplaints = $allGrievancesQuery->with(['user', 'assignedUser', 'attachments'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($grievance) {
                return [
                    'id' => $grievance->id,
                    'title' => $grievance->title ?? $grievance->description,
                    'description' => $grievance->description,
                    'type' => $grievance->type, // Incluir todos os tipos: complaint, grievance, suggestion
                    'priority' => $grievance->priority,
                    'status' => $grievance->status,
                    'category' => $grievance->category,
                    'subcategory' => $grievance->subcategory,
                    'created_at' => $grievance->created_at,
                    'submitted_at' => $grievance->submitted_at,
                    'reference_number' => $grievance->reference_number,
                    'province' => $grievance->province,
                    'district' => $grievance->district,
                    'location_details' => $grievance->location_details,
                    'assigned_user' => $grievance->assignedUser ? [
                        'name' => $grievance->assignedUser->name,
                    ] : null,
                    'user' => $grievance->user ? [
                        'name' => $grievance->user->name,
                        'email' => $grievance->user->email,
                    ] : null,
                    'technician' => $grievance->assignedUser ? [
                        'name' => $grievance->assignedUser->name,
                    ] : null,
                ];
            });

        return Inertia::render('Grievances/Index', [
            'grievances' => $grievances,
            'allComplaints' => $allComplaints,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new grievance.
     */
    public function create()
    {
        return Inertia::render('Grievances/Create');
    }

    /**
     * Store a newly created grievance.
     */
    public function store(Request $request)
    {
        try {
            // Validação dos dados
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'type' => 'required|in:complaint,grievance,suggestion',
                // Description can be nullable; ensure it's either null or a string
                'description' => 'nullable|string|max:1500',
                'province' => 'nullable|string',
                'district' => 'nullable|string',
                'location_details' => 'nullable|string',
                'is_anonymous' => 'sometimes|boolean',
                'contact_name' => 'required_if:is_anonymous,false,0|nullable|string|max:255',
                'contact_email' => 'required_if:is_anonymous,false,0|nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'attachments' => 'nullable|array|max:5',
                'attachments.*' => 'file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240',
                'audio_attachment' => 'nullable|file|mimes:webm,mp3,wav,ogg,m4a|max:10240',
            ]);

            DB::beginTransaction();

            // Preparar dados da reclamação
            $grievanceData = [
                'project_id' => $validated['project_id'] ?? null,
                'type' => $validated['type'],
                'description' => $validated['description'],
                'category' => $validated['category'] ?? null,
                'subcategory' => $validated['subcategory'] ?? null,
                'province' => $validated['province'] ?? null,
                'district' => $validated['district'] ?? null,
                'location_details' => $validated['location_details'] ?? null,
                'is_anonymous' => $validated['is_anonymous'] ?? false,
                'status' => 'submitted',
                'priority' => 'medium',
                'submitted_at' => now(),
            ];

            // Se for reclamação identificada e o usuário estender autenticado
            if (!$validated['is_anonymous'] && auth()->check()) {
                $grievanceData['user_id'] = auth()->id();
            } else {
                // Se for anônima, armazenar informações de contato
                $grievanceData['contact_name'] = $validated['contact_name'] ?? null;
                $grievanceData['contact_email'] = $validated['contact_email'] ?? null;
                $grievanceData['contact_phone'] = $validated['contact_phone'] ?? null;
            }

            // Criar a reclamação
            $grievance = Grievance::create($grievanceData);

            // Processar anexos se existirem
            // Processar anexos de ficheiros
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->storeAttachment($grievance, $file);
                }
            }

            // Processar anexo de áudio
            if ($request->hasFile('audio_attachment')) {
                $this->storeAttachment($grievance, $request->file('audio_attachment'), 'audio');
            }

            DB::commit();

            // Enviar notificação de confirmação (implementar depois)
            // event(new GrievanceSubmitted($grievance));

            return response()->json([
                'success' => true,
                'message' => 'Submissão feita com sucesso!',
                'reference_number' => $grievance->reference_number,
                'grievance' => $grievance->load('attachments')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Se for erro de validação, retornar 422
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $e->errors()
                ], 422);
            }

            // Para outros erros, determinar mensagem baseada no tipo de erro
            $errorMessage = $this->getUserFriendlyErrorMessage($e);

            Log::error('Erro ao submeter submissão: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
                'error_type' => get_class($e),
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }

    /**
     * Display the specified grievance.
     */
    public function show(Grievance $grievance)
    {
        // Verificar permissões
        $user = auth()->user();

        if ($user->hasRole('utente') && $grievance->user_id !== $user->id) {
            abort(403, 'Não autorizado a visualizar esta reclamação.');
        }

        $grievance->load(['user', 'assignedUser', 'resolvedBy', 'attachments']);

        return Inertia::render('Grievances/Show', [
            'grievance' => $grievance
        ]);
    }

    /**
     * Track a grievance by reference number (for anonymous users).
     */
    public function track(Request $request)
    {
        $validated = $request->validate([
            'reference_number' => 'required|string'
        ]);

        $grievance = Grievance::where('reference_number', $validated['reference_number'])
            ->with(['attachments'])
            ->first();

        if (!$grievance) {
            return response()->json([
                'success' => false,
                'message' => 'Reclamação não encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'grievance' => [
                'reference_number' => $grievance->reference_number,
                'status' => $grievance->status,
                'category' => $grievance->category,
                'submitted_at' => $grievance->submitted_at,
                'updated_at' => $grievance->updated_at,
            ]
        ]);
    }

    /**
     * Store an attachment for a grievance.
     */
    private function storeAttachment(Grievance $grievance, $file, $type = 'document')
    {
        try {
            $originalFilename = $file->getClientOriginalName();
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // Armazenar o arquivo
            $path = $file->storeAs(
                'grievances/' . $grievance->id . '/attachments',
                $filename,
                'private'
            );

            // Calcular hash do arquivo para verificação de integridade
            $fileHash = hash_file('sha256', $file->getRealPath());

            // Criar registro do anexo
            Attachment::create([
                'grievance_id' => $grievance->id,
                'original_filename' => $originalFilename,
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $mimeType,
                'size' => $size,
                'file_hash' => $fileHash,
                'is_encrypted' => false,
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'type' => $type,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao armazenar anexo: ' . $e->getMessage(), [
                'grievance_id' => $grievance->id,
                'filename' => $originalFilename ?? 'unknown'
            ]);

            return false;
        }
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Attachment $attachment)
    {
        // Verificar permissões
        $user = auth()->user();
        $grievance = $attachment->grievance;

        if ($user->hasRole('utente') && $grievance->user_id !== $user->id) {
            abort(403, 'Não autorizado a baixar este anexo.');
        }

        if (!Storage::disk('private')->exists($attachment->path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Storage::disk('private')->download(
            $attachment->path,
            $attachment->original_filename
        );
    }

    /**
     * Get categories for the form.
     */
    public function getCategories()
    {
        $categories = [
            'Serviços Públicos' => [
                'Fornecimento de Energia',
                'Qualidade do Serviço',
                'Atendimento ao Cliente',
                'Faturação',
            ],
            'Infraestrutura' => [
                'Instalação de Equipamentos',
                'Manutenção',
                'Construção',
            ],
            'Ambiental' => [
                'Impacto Ambiental',
                'Poluição',
                'Gestão de Resíduos',
            ],
            'Social' => [
                'Reassentamento',
                'Compensação',
                'Consulta Comunitária',
            ],
            'Administração' => [
                'Processos Administrativos',
                'Documentação',
                'Outros',
            ],
        ];

        return response()->json($categories);
    }

    /**
     * Get provinces and districts.
     */
    public function getLocations()
    {
        $locations = [
            'Maputo' => ['KaMpfumu', 'Nlhamankulu', 'KaMaxaquene', 'KaMavota', 'KaMubukwana', 'KaTembe', 'Kanyaka'],
            'Gaza' => ['Chókwè', 'Chibuto', 'Xai-Xai', 'Manjacaze', 'Bilene', 'Chicualacuala', 'Chigubo', 'Guijá', 'Mabalane', 'Massangena', 'Massingir'],
            'Inhambane' => ['Inhambane', 'Maxixe', 'Vilankulo', 'Massinga', 'Zavala', 'Inharrime', 'Jangamo', 'Homoine', 'Morrumbene', 'Govuro', 'Funhalouro', 'Panda', 'Mabote'],
            'Sofala' => ['Beira', 'Dondo', 'Nhamatanda', 'Búzi', 'Gorongosa', 'Muanza', 'Chemba', 'Chibabava', 'Machanga', 'Marromeu', 'Cheringoma'],
            'Manica' => ['Chimoio', 'Gondola', 'Manica', 'Báruè', 'Sussundenga', 'Macossa', 'Guro', 'Tambara', 'Vanduzi', 'Machaze', 'Mossurize'],
            'Tete' => ['Tete', 'Moatize', 'Angónia', 'Cahora-Bassa', 'Changara', 'Chifunde', 'Chiuta', 'Dôa', 'Macanga', 'Marávia', 'Moatize', 'Mutarara', 'Tsangano', 'Zumbu', 'Magoe'],
            'Zambézia' => ['Quelimane', 'Mocuba', 'Alto Molócuè', 'Gurúè', 'Milange', 'Ile', 'Namarrói', 'Pebane', 'Maganja da Costa', 'Nicoadala', 'Inhassunge', 'Chinde', 'Morrumbala', 'Lugela', 'Mopeia', 'Namacurra'],
            'Nampula' => ['Nampula', 'Nacala', 'Ilha de Moçambique', 'Angoche', 'Monapo', 'Memba', 'Mossuril', 'Mogincual', 'Mogovolas', 'Meconta', 'Muecate', 'Murrupula', 'Nampula', 'Ribaué', 'Malema', 'Mecubúri', 'Eráti', 'Lalaua', 'Larde', 'Liúpo', 'Moma', 'Nacarôa'],
            'Cabo Delgado' => ['Pemba', 'Mocímboa da Praia', 'Palma', 'Mueda', 'Montepuez', 'Chiúre', 'Ancuabe', 'Balama', 'Macomia', 'Meluco', 'Metuge', 'Namuno', 'Nangade', 'Quissanga'],
            'Niassa' => ['Lichinga', 'Cuamba', 'Mandimba', 'Marrupa', 'Majune', 'Mavago', 'Mecanhelas', 'Meculane', 'Metarica', 'Muembe', 'N\'gauma', 'Nipepe', 'Sanga'],
        ];

        return response()->json($locations);
    }

    /**
     * Get projects for grievance form.
     */
    public function getProjects()
    {
        $projects = \App\Models\Project::select('id', 'name', 'provincia', 'distrito')
            ->orderBy('name')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'location' => trim(($project->provincia ?? '') . ' - ' . ($project->distrito ?? ''), ' - '),
                ];
            });

        return response()->json($projects);
    }

    /**
     * Bulk assign grievances to technicians
     */
    public function bulkAssign()
    {
        try {
            // Lógica de atribuição em massa
            $unassignedGrievances = Grievance::whereNull('assigned_to')
                ->where('status', 'submitted')
                ->get();

            $technicians = User::role('Técnico')->get();

            if ($technicians->isEmpty()) {
                return back()->with('error', 'Nenhum técnico disponível para atribuição.');
            }

            $assignedCount = 0;
            $technicianIndex = 0;

            foreach ($unassignedGrievances as $grievance) {
                $technician = $technicians[$technicianIndex];

                $grievance->update([
                    'assigned_to' => $technician->id,
                    'status' => 'in_progress',
                    'assigned_at' => now()
                ]);

                $assignedCount++;
                $technicianIndex = ($technicianIndex + 1) % $technicians->count();
            }

            return back()->with('success', "{$assignedCount} reclamações atribuídas automaticamente.");

        } catch (\Exception $e) {
            Log::error('Erro na atribuição em massa: ' . $e->getMessage());
            return back()->with('error', 'Erro ao realizar atribuição automática.');
        }
    }

    /**
     * Get user-friendly error message based on exception type
     */
    private function getUserFriendlyErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        // Database connection errors
        if (str_contains($message, 'Connection refused') ||
            str_contains($message, 'Connection timed out') ||
            str_contains($message, 'No connection could be made')) {
            return 'Erro de conexão com o servidor. Verifique sua conexão com a internet e tente novamente.';
        }

        // Database constraint violations
        if (str_contains($message, 'Integrity constraint violation') ||
            str_contains($message, 'foreign key constraint')) {
            return 'Erro nos dados enviados. Verifique se todas as informações estão corretas.';
        }

        // File upload errors
        if (str_contains($message, 'upload') ||
            str_contains($message, 'file') ||
            str_contains($message, 'storage')) {
            return 'Erro ao processar os arquivos anexados. Verifique o tamanho e formato dos arquivos.';
        }

        // Disk space errors
        if (str_contains($message, 'disk') ||
            str_contains($message, 'space') ||
            str_contains($message, 'quota')) {
            return 'Erro de armazenamento. Entre em contato com o suporte técnico.';
        }

        // Permission errors
        if (str_contains($message, 'permission') ||
            str_contains($message, 'access denied')) {
            return 'Erro de permissão. Entre em contato com o suporte técnico.';
        }

        // Generic fallback
        return 'Ocorreu um erro inesperado. Por favor, tente novamente ou entre em contato com o suporte.';
    }

}
