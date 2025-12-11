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

        // Adicionar URLs públicas aos anexos e garantir path
        $grievances->each(function($grievance) {
            $grievance->attachments->each(function($attachment) {
                $attachment->url = url($attachment->path);
                // Garantir que o path também esteja disponível
                $attachment->path = $attachment->path;
            });
        });

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
                // Processar attachments para adicionar url e garantir path
                $attachments = $grievance->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'original_filename' => $attachment->original_filename,
                        'filename' => $attachment->filename,
                        'path' => $attachment->path,
                        'url' => url($attachment->path),
                        'mime_type' => $attachment->mime_type,
                        'size' => $attachment->size,
                        'uploaded_at' => $attachment->uploaded_at,
                        'type' => $attachment->type,
                    ];
                });

                return [
                    'id' => $grievance->id,
                    'title' => $grievance->title ?? $grievance->description,
                    'description' => $grievance->description,
                    'type' => $grievance->type,
                    'priority' => $grievance->priority,
                    'status' => $grievance->status,
                    'category' => $grievance->category,
                    'subcategory' => $grievance->subcategory,
                    'created_at' => $grievance->created_at,
                    'submitted_at' => $grievance->submitted_at,
                    'reference_number' => $grievance->reference_number,
                    'province' => $grievance->province,
                    'district' => $grievance->district,
                    'municipal_district' => $grievance->municipal_district,
                    'administrative_post' => $grievance->administrative_post,
                    'locality' => $grievance->locality,
                    'location_details' => $grievance->location_details,
                    'attachments' => $attachments,
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
                'province' => 'required|string',
                'district' => 'nullable|string',
                'municipal_district' => 'nullable|string',
                'administrative_post' => 'nullable|string',
                'locality' => 'nullable|string',
                'location_details' => 'nullable|string',
                'is_anonymous' => 'sometimes|boolean',
                // Contact fields optional; validate when present
                'contact_name' => 'sometimes|nullable|string|max:255',
                'contact_email' => 'sometimes|nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'gender' => 'nullable|string|in:Masculino,Feminino,Outro',
                'attachments' => 'nullable|array|max:5',
                'attachments.*' => 'file|mimes:jpeg,jpg,png,pdf,doc,docx,txt,csv,xls,xlsx,mp3,wav,ogg,webm,m4a,aac|max:10240',
                'audio_attachment' => 'nullable|file|mimes:webm,mp3,wav,ogg,m4a,aac|max:10240',
            ]);

            DB::beginTransaction();

            // Preparar dados da reclamação
            $grievanceData = [
                'project_id' => $validated['project_id'] ?? null,
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'subcategory' => $validated['subcategory'] ?? null,
                'province' => $validated['province'] ?? null,
                'district' => $validated['district'] ?? null,
                'municipal_district' => $validated['municipal_district'] ?? null,
                'administrative_post' => $validated['administrative_post'] ?? null,
                'locality' => $validated['locality'] ?? null,
                'location_details' => $validated['location_details'] ?? null,
                'is_anonymous' => $validated['is_anonymous'] ?? false,
                'status' => 'submitted',
                'priority' => 'medium',
                'submitted_at' => now(),
                'gender' => $validated['gender'] ?? null,
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
            Log::info('Iniciando processamento de anexos', [
                'has_attachments' => $request->hasFile('attachments'),
                'has_audio' => $request->hasFile('audio_attachment'),
                'attachments_count' => $request->hasFile('attachments') ? count($request->file('attachments')) : 0
            ]);

            // Processar anexos de ficheiros
            if ($request->hasFile('attachments')) {
                $attachments = $request->file('attachments');
                Log::info('Processando anexos regulares', ['count' => count($attachments)]);

                foreach ($attachments as $index => $file) {
                    Log::info('Processando anexo', [
                        'index' => $index,
                        'filename' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType()
                    ]);

                    $result = $this->storeAttachment($grievance, $file);
                    if (!$result) {
                        Log::error('Falha ao armazenar anexo', [
                            'index' => $index,
                            'filename' => $file->getClientOriginalName()
                        ]);
                    }
                }
            }

            // Processar anexo de áudio
            if ($request->hasFile('audio_attachment')) {
                $audioFile = $request->file('audio_attachment');
                Log::info('Processando anexo de áudio', [
                    'filename' => $audioFile->getClientOriginalName(),
                    'size' => $audioFile->getSize(),
                    'mime' => $audioFile->getMimeType()
                ]);

                $result = $this->storeAttachment($grievance, $audioFile, 'audio');
                if (!$result) {
                    Log::error('Falha ao armazenar anexo de áudio', [
                        'filename' => $audioFile->getClientOriginalName()
                    ]);
                }
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

        // Adicionar URLs públicas aos anexos
        $grievance->attachments->each(function($attachment) {
            $attachment->url = url($attachment->path);
            // Garantir que o path também esteja disponível
            $attachment->path = $attachment->path;
        });

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
            ->with(['attachments:id,grievance_id,original_filename,filename,path,mime_type,size'])
            ->first();

        if (!$grievance) {
            return response()->json([
                'success' => false,
                'message' => 'Reclamação não encontrada.'
            ], 404);
        }

        // Adicionar URLs públicas aos anexos
        $grievance->attachments->each(function($attachment) {
            $attachment->url = url($attachment->path);
            // Garantir que o path também esteja disponível
            $attachment->path = $attachment->path;
        });

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

            Log::info('Iniciando armazenamento de anexo', [
                'grievance_id' => $grievance->id,
                'original_filename' => $originalFilename,
                'generated_filename' => $filename,
                'mime_type' => $mimeType,
                'size' => $size,
                'type' => $type
            ]);

            // Calcular hash do arquivo ANTES de mover
            $fileHash = hash_file('sha256', $file->getRealPath());

            // Armazenar o arquivo diretamente na pasta public
            $publicPath = 'uploads/grievances/' . $grievance->id . '/attachments';
            $fullPath = public_path($publicPath);

            Log::info('Verificando/criando diretório', [
                'path' => $fullPath,
                'exists' => file_exists($fullPath)
            ]);

            // Criar diretório se não existir
            if (!file_exists($fullPath)) {
                $created = mkdir($fullPath, 0755, true);
                Log::info('Diretório criado', [
                    'path' => $fullPath,
                    'success' => $created
                ]);

                if (!$created) {
                    throw new \Exception('Falha ao criar diretório: ' . $fullPath);
                }
            }

            $path = '/' . $publicPath . '/' . $filename;
            $destinationFile = $fullPath . '/' . $filename;

            Log::info('Movendo arquivo', [
                'from' => $file->getRealPath(),
                'to' => $destinationFile
            ]);

            $file->move($fullPath, $filename);

            // Verificar se o arquivo foi movido com sucesso
            if (!file_exists($destinationFile)) {
                throw new \Exception('Arquivo não foi movido para o destino: ' . $destinationFile);
            }

            Log::info('Arquivo movido com sucesso, criando registro no banco');

            // Criar registro do anexo
            $attachment = Attachment::create([
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

            Log::info('Anexo criado com sucesso', [
                'attachment_id' => $attachment->id,
                'path' => $path
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao armazenar anexo: ' . $e->getMessage(), [
                'grievance_id' => $grievance->id,
                'filename' => $originalFilename ?? 'unknown',
                'error_type' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
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

        $filePath = public_path($attachment->path);
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return response()->download($filePath, $attachment->original_filename);
    }

    /**
     * View attachment inline (for images, PDFs, etc.).
     */
    public function viewAttachment(Attachment $attachment)
    {
        // Verificar permissões
        $user = auth()->user();
        $grievance = $attachment->grievance;

        if ($user->hasRole('utente') && $grievance->user_id !== $user->id) {
            abort(403, 'Não autorizado a visualizar este anexo.');
        }

        $filePath = public_path($attachment->path);
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $file = file_get_contents($filePath);
        $mimeType = $attachment->mime_type ?: 'application/octet-stream';

        return response($file, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $attachment->original_filename . '"',
        ]);
    }

    /**
     * View attachment inline for public access (with restrictions).
     */
    public function viewAttachmentPublic(Attachment $attachment)
    {
        // Para acesso público, só permitir tipos de arquivo seguros
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'audio/webm', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a', 'audio/mpeg'
        ];

        if (!in_array($attachment->mime_type, $allowedTypes)) {
            abort(403, 'Este tipo de arquivo não está disponível para visualização pública.');
        }

        $filePath = public_path($attachment->path);
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $file = file_get_contents($filePath);
        $mimeType = $attachment->mime_type ?: 'application/octet-stream';

        return response($file, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $attachment->original_filename . '"',
            'Cache-Control' => 'public, max-age=3600', // Cache por 1 hora
        ]);
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
            'Maputo Cidade' => [
                'districts' => ['KaMpfumu', 'Nlhamankulu', 'KaMaxaquene', 'KaMavota', 'KaMubukwana', 'KaTembe', 'Kanyaka'],
                'municipal_districts' => ['KaMpfumu', 'Nlhamankulu', 'KaMaxaquene', 'KaMavota', 'KaMubukwana', 'KaTembe', 'Kanyaka'],
                 'administrative_posts' => [
                     'KaMpfumu' => ['Alto Maé', 'Malhangalene', 'Polana Cimento'],
                     'Nlhamankulu' => ['Chamanculo', 'Xipamanine'],
                     'KaMaxaquene' => ['Maxaquene', 'Polana Caniço'],
                     'KaMavota' => ['Mavota', 'Costa do Sol', 'Albasine'],
                     'KaMubukwana' => ['Zimpeto', 'Magoanine', 'Jardim'],
                     'KaTembe' => ['Katembe', 'Incassane', 'Guachene'],
                     'Kanyaka' => ['Inguane', 'Ribene', 'Quewene'],
                ],
                'localities' => [
                    'Alto Maé' => ['Alto Maé A', 'Alto Maé B'],
                    'Chamanculo' => ['Chamanculo A', 'Chamanculo B', 'Chamanculo C', 'Chamanculo D'],
                ]
            ],
            'Maputo Província' => [
                'districts' => ['Boane', 'Magude', 'Manhiça', 'Marracuene', 'Matola', 'Matutuíne', 'Moamba', 'Namaacha']
            ],
            'Gaza' => [
                'districts' => ['Chókwè', 'Chibuto', 'Xai-Xai', 'Manjacaze', 'Bilene', 'Chicualacuala', 'Chigubo', 'Guijá', 'Mabalane', 'Massangena', 'Massingir'],
                'administrative_posts' => ['Chókwè' => ['Macarretane', 'Lionde', 'Xilembene'], 'Bilene' => ['Praia de Bilene', 'Macia']],
            ],
            'Inhambane' => [
                'districts' => ['Inhambane', 'Maxixe', 'Vilankulo', 'Massinga', 'Zavala', 'Inharrime', 'Jangamo', 'Homoine', 'Morrumbene', 'Govuro', 'Funhalouro', 'Panda', 'Mabote']
            ],
            'Sofala' => [
                'districts' => ['Beira', 'Dondo', 'Nhamatanda', 'Búzi', 'Gorongosa', 'Muanza', 'Chemba', 'Chibabava', 'Machanga', 'Marromeu', 'Cheringoma']
            ],
            'Manica' => [
                'districts' => ['Chimoio', 'Gondola', 'Manica', 'Báruè', 'Sussundenga', 'Macossa', 'Guro', 'Tambara', 'Vanduzi', 'Machaze', 'Mossurize']
            ],
            'Tete' => [
                'districts' => ['Tete', 'Moatize', 'Angónia', 'Cahora-Bassa', 'Changara', 'Chifunde', 'Chiuta', 'Dôa', 'Macanga', 'Marávia', 'Moatize', 'Mutarara', 'Tsangano', 'Zumbu', 'Magoe']
            ],
            'Zambézia' => [
                'districts' => ['Quelimane', 'Mocuba', 'Alto Molócuè', 'Gurúè', 'Milange', 'Ile', 'Namarrói', 'Pebane', 'Maganja da Costa', 'Nicoadala', 'Inhassunge', 'Chinde', 'Morrumbala', 'Lugela', 'Mopeia', 'Namacurra']
            ],
            'Nampula' => [
                'districts' => ['Nampula', 'Nacala', 'Ilha de Moçambique', 'Angoche', 'Monapo', 'Memba', 'Mossuril', 'Mogincual', 'Mogovolas', 'Meconta', 'Muecate', 'Murrupula', 'Nampula', 'Ribaué', 'Malema', 'Mecubúri', 'Eráti', 'Lalaua', 'Larde', 'Liúpo', 'Moma', 'Nacarôa']
            ],
            'Cabo Delgado' => [
                'districts' => ['Pemba', 'Mocímboa da Praia', 'Palma', 'Mueda', 'Montepuez', 'Chiúre', 'Ancuabe', 'Balama', 'Macomia', 'Meluco', 'Metuge', 'Namuno', 'Nangade', 'Quissanga']
            ],
            'Niassa' => [
                'districts' => ['Lichinga', 'Cuamba', 'Mandimba', 'Marrupa', 'Majune', 'Mavago', 'Mecanhelas', 'Meculane', 'Metarica', 'Muembe', 'N\'gauma', 'Nipepe', 'Sanga']
            ],
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
     * List files in a grievance folder (for browsing).
     */
    public function listGrievanceFiles(Grievance $grievance)
    {
        // Verificar permissões
        $user = auth()->user();

        if ($user->hasRole('utente') && $grievance->user_id !== $user->id) {
            abort(403, 'Não autorizado a visualizar os arquivos desta reclamação.');
        }

        $folderPath = public_path('uploads/grievances/' . $grievance->id . '/attachments');

        $files = [];
        if (file_exists($folderPath)) {
            $fileItems = scandir($folderPath);
            foreach ($fileItems as $item) {
                if ($item !== '.' && $item !== '..') {
                    $filePath = $folderPath . '/' . $item;
                    $fileSize = filesize($filePath);
                    $fileModified = filemtime($filePath);

                    // Encontrar o anexo correspondente no banco de dados
                    $attachment = $grievance->attachments->where('filename', $item)->first();

                    $files[] = [
                        'filename' => $item,
                        'original_filename' => $attachment ? $attachment->original_filename : $item,
                        'url' => url('uploads/grievances/' . $grievance->id . '/attachments/' . $item),
                        'size' => $fileSize,
                        'size_human' => $this->formatBytes($fileSize),
                        'modified' => date('Y-m-d H:i:s', $fileModified),
                        'mime_type' => $attachment ? $attachment->mime_type : mime_content_type($filePath),
                        'type' => $attachment ? $attachment->type : 'unknown',
                    ];
                }
            }
        }

        return response()->json([
            'grievance_id' => $grievance->id,
            'reference_number' => $grievance->reference_number,
            'folder_path' => 'uploads/grievances/' . $grievance->id . '/attachments',
            'folder_url' => url('uploads/grievances/' . $grievance->id . '/attachments'),
            'files' => $files,
            'total_files' => count($files),
        ]);
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
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
