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
    public function index()
    {
        $user = auth()->user();

        // Se o usuário for utente, mostrar apenas suas reclamações
        if ($user->hasRole('utente')) {
            $grievances = Grievance::where('user_id', $user->id)
                ->orWhere(function ($query) use ($user) {
                    $query->where('is_anonymous', false)
                        ->where('contact_email', $user->email);
                })
                ->with(['attachments'])
                ->orderBy('submitted_at', 'desc')
                ->paginate(10);
        } else {
            // Para outros roles, mostrar todas as reclamações
            $grievances = Grievance::with(['user', 'assignedUser', 'attachments'])
                ->orderBy('submitted_at', 'desc')
                ->paginate(10);
        }

        return Inertia::render('Grievances/Index', [
            'grievances' => $grievances
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
                'description' => 'required|string|min:10',
                'category' => 'required|string',
                'subcategory' => 'nullable|string',
                'province' => 'nullable|string',
                'district' => 'nullable|string',
                'location_details' => 'nullable|string',
                'is_anonymous' => 'sometimes|boolean',
                'contact_name' => 'required_if:is_anonymous,true|nullable|string|max:255',
                'contact_email' => 'required_if:is_anonymous,true|nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'attachments' => 'nullable|array|max:5',
                'attachments.*' => 'file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240', // 10MB max
            ]);

            DB::beginTransaction();

            // Preparar dados da reclamação
            $grievanceData = [
                'description' => $validated['description'],
                'category' => $validated['category'],
                'subcategory' => $validated['subcategory'] ?? null,
                'province' => $validated['province'] ?? null,
                'district' => $validated['district'] ?? null,
                'location_details' => $validated['location_details'] ?? null,
                'is_anonymous' => $validated['is_anonymous'] ?? false,
                'status' => 'submitted',
                'priority' => 'medium', // Prioridade padrão
                'submitted_at' => now(),
            ];

            // Se for reclamação identificada e o usuário estiver autenticado
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
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->storeAttachment($grievance, $file);
                }
            }

            DB::commit();

            // Enviar notificação de confirmação (implementar depois)
            // event(new GrievanceSubmitted($grievance));

            return response()->json([
                'success' => true,
                'message' => 'Reclamação submetida com sucesso!',
                'reference_number' => $grievance->reference_number,
                'grievance' => $grievance->load('attachments')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao submeter reclamação: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao submeter reclamação. Por favor, tente novamente.'
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
    private function storeAttachment(Grievance $grievance, $file)
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
}
