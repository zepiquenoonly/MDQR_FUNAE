<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log; 
use Inertia\Inertia;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('can:manage-projects')->except(['index', 'show', 'webIndex']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with(['objectives', 'finance', 'deadline'])->get();
        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validação simplificada - apenas campos básicos
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'department_id' => 'nullable|exists:departments,id',
                'provincia' => 'nullable|string|max:100',
                'distrito' => 'nullable|string|max:100',
                'bairro' => 'nullable|string|max:100',
                'category' => 'required|in:andamento,parados,finalizados',
            ]);

            // Criar projecto
            $project = Project::create($validated);

            return redirect()->route('admin.projects.index')
                ->with('success', 'Projecto criado com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao criar projecto: ' . $e->getMessage());
            return back()->with('error', 'Erro ao criar projecto: ' . $e->getMessage());
        }
    }

     public function webIndex(Request $request)
    {
        // DEBUG detalhado
        $user = Auth::user();
        
        Log::info('ProjectController::webIndex - Acesso tentado', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'user_roles' => $user->getRoleNames()->toArray(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'is_gestor' => $user->hasRole('Gestor'),
            'is_manager' => $user->hasRole('Manager'),
            'is_director' => $user->hasRole('Director'),
            'is_admin' => $user->hasRole('admin'),
        ]);
        
        // Verificar se o usuário tem permissão
        // Permitir múltiplos nomes de roles para compatibilidade
        $allowedRoles = ['Gestor', 'Manager', 'Director', 'admin', 'PCA'];
        
        if (!$user->hasAnyRole($allowedRoles)) {
            Log::warning('Acesso negado para user_id: ' . $user->id, [
                'roles' => $user->getRoleNames()->toArray(),
                'allowed_roles' => $allowedRoles
            ]);
            
            // Mensagem mais descritiva
            $userRoles = implode(', ', $user->getRoleNames()->toArray());
            abort(403, "Acesso não autorizado. Seu perfil ({$userRoles}) não tem permissão para acessar projetos. 
                   Permissões necessárias: Gestor, Manager, Director, Admin ou PCA.");
        }
        
        Log::info('Acesso permitido para user_id: ' . $user->id);
        
        // Obter projetos
         $projects = Project::query()
        ->with(['objectives', 'finance', 'deadline'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    // Estatísticas CORRIGIDAS - usar as mesmas categorias
    $stats = [
        'total' => Project::count(),
        'finished' => Project::where('category', 'finalizados')->count(),
        'progress' => Project::where('category', 'andamento')->count(),
        'suspended' => Project::where('category', 'parados')->count(),
    ];
    
    // DEBUG: Verifique os dados
    \Log::info('Estatísticas enviadas:', $stats);
    \Log::info('Total de projetos:', ['count' => $projects->count()]);
    
    return Inertia::render('Common/ProjectsPage', [
        'projects' => $projects,
        'stats' => $stats,
        'canEdit' => $user->hasAnyRole(['Gestor', 'Director', 'admin']),
    ]);
}

    /**
     * Display the specified resource.
     */
   public function show(Project $project)
    {
        // Carregar relacionamentos necessários
        $project->load(['objectives', 'finance', 'deadline']);
        
        // Verificar permissões (se necessário)
        $user = auth()->user();
        $allowedRoles = ['Gestor', 'Manager', 'Director', 'admin', 'PCA'];
        
        if (!$user->hasAnyRole($allowedRoles)) {
            abort(403, 'Acesso não autorizado');
        }
        
        // Retornar via Inertia para navegação web
        return Inertia::render('Common/ProjectDetails', [
            'project' => $project,
            'canEdit' => $user->hasAnyRole(['admin', 'Director', 'Gestor']),
            'canDelete' => $user->hasAnyRole(['admin']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        try {
            // Validação simplificada
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'department_id' => 'nullable|exists:departments,id',
                'provincia' => 'nullable|string|max:100',
                'distrito' => 'nullable|string|max:100',
                'bairro' => 'nullable|string|max:100',
                'category' => 'sometimes|required|in:andamento,parados,finalizados',
            ]);

            // Actualizar projecto
            $project->update($validated);

            // Para requisições Inertia
            if (!request()->expectsJson()) {
                return redirect()->route('admin.projects.index')
                    ->with('success', 'Projecto actualizado com sucesso!');
            }

            // Para requisições AJAX/API
            return response()->json([
                'success' => true,
                'message' => 'Projecto actualizado com sucesso!',
                'project' => $project
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação: ' . json_encode($e->errors()));

            if (!request()->expectsJson()) {
                return back()->withErrors($e->errors())->withInput();
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao actualizar projecto: ' . $e->getMessage());

            if (!request()->expectsJson()) {
                return back()->with('error', 'Erro ao actualizar projecto.');
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro ao actualizar projecto: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        try {
            // Remover imagem se existir (comentado para uso futuro)
            // if ($project->image_url) {
            //     Storage::disk('public')->delete($project->image_url);
            // }

            $project->delete();

            return back()->with('success', 'Projecto eliminado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao eliminar projecto: ' . $e->getMessage());
            return back()->with('error', 'Erro ao eliminar projecto.');
        }
    }


    public function exportProjectsToPDF(Request $request)
    {
        try {
            // Verificar autenticação e permissões
            $user = Auth::user();
            $allowedRoles = ['Gestor', 'Manager', 'Director', 'admin', 'PCA'];
            
            if (!$user->hasAnyRole($allowedRoles)) {
                abort(403, 'Acesso não autorizado para exportar PDF');
            }
            
            // Obter parâmetros de filtro
            $search = $request->input('search', '');
            $filters = $request->input('filters', []);
            
            // Construir query
            $query = Project::query()
                ->with(['objectives', 'finance', 'deadline'])
                ->orderBy('created_at', 'desc');
            
            // Aplicar filtro de busca
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('bairro', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // Aplicar filtros adicionais
            if (!empty($filters)) {
                if (isset($filters['category']) && !empty($filters['category'])) {
                    $query->where('category', $filters['category']);
                }
                
                if (isset($filters['bairro']) && !empty($filters['bairro'])) {
                    $query->where('bairro', 'like', "%{$filters['bairro']}%");
                }
                
                if (isset($filters['provincia']) && !empty($filters['provincia'])) {
                    $query->where('provincia', $filters['provincia']);
                }
                
                if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                    $query->whereDate('created_at', '>=', $filters['date_from']);
                }
                
                if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                    $query->whereDate('created_at', '<=', $filters['date_to']);
                }
            }
            
            // Obter projetos com paginação para evitar memória excessiva
            $projects = $query->get();
            
            // Dados para o PDF
            $data = [
                'projects' => $projects,
                'user' => $user,
                'filters' => [
                    'search' => $search,
                    'category' => $filters['category'] ?? null,
                    'bairro' => $filters['bairro'] ?? null,
                    'date_from' => $filters['date_from'] ?? null,
                    'date_to' => $filters['date_to'] ?? null,
                ],
                'export_date' => Carbon::now()->format('d/m/Y H:i'),
                'total_projects' => $projects->count(),
                'stats' => [
                    'finalizados' => $projects->where('category', 'finalizados')->count(),
                    'andamento' => $projects->where('category', 'andamento')->count(),
                    'parados' => $projects->where('category', 'parados')->count(),
                ]
            ];
            
            // DEBUG: Log dos dados
            \Log::info('Exportando PDF com dados:', [
                'total_projects' => $projects->count(),
                'user' => $user->email,
                'filters' => $data['filters']
            ]);
            
            // Gerar PDF com configurações otimizadas
            $pdf = Pdf::loadView('exports.projects-pdf', $data);
            
            // Configurações importantes
            $pdf->setPaper('A4', 'landscape');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => false,
                'defaultFont' => 'sans-serif',
                'fontHeightRatio' => 0.9,
                'dpi' => 150
            ]);
            
            // Nome do arquivo
            $filename = 'projectos_' . Carbon::now()->format('Y-m-d_H-i') . '.pdf';
            
            // Forçar download
            return $pdf->stream($filename); // Use stream() em vez de download() para debug
            
        } catch (\Exception $e) {
            \Log::error('Erro ao exportar PDF: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Se for API/JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Erro ao gerar PDF',
                    'message' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ], 500);
            }
            
            // Redirecionar com erro
            return redirect()->back()
                ->with('error', 'Erro ao exportar PDF: ' . $e->getMessage());
        }
    }
}
