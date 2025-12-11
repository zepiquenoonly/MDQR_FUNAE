<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Objective;
use App\Models\Finance;
use App\Models\Deadline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display the projects page (Inertia)
     */
    public function indexPage()
    {
        // Obter estatísticas
        $stats = $this->getProjectStats();
        
        // Obter todos os projetos com relações
        $projects = Project::with(['objectives', 'finance', 'deadline'])
            ->orderBy('created_at', 'desc')
            ->get();

    $user = auth()->user();
    $role = $user->role ?? 'utente'; // Certifique-se de que isso está correto
    
    // Debug: Log para verificar
    \Log::info('ProjectController - User role:', ['role' => $role, 'user' => $user->id]);
        
        return Inertia::render('Common/ProjectsPage', [
            'user' => auth()->user(),
            'role' => auth()->user()->role ?? 'user',
            'stats' => $stats, // Envie as estatísticas
            'projects' => $projects, // Envie os projetos diretamente
            'canEdit' => $this->userCanEdit() // Adicione esta verificação
        ]);
    }
    
    /**
     * Check if user can edit projects
     */
    private function userCanEdit()
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        $allowedRoles = ['admin', 'manager', 'editor'];
        return in_array($user->role, $allowedRoles);
    }

    /**
     * Get project statistics
     */
    private function getProjectStats()
    {
        $total = Project::count();
        $finished = Project::where('category', 'finalizados')->count();
        $progress = Project::where('category', 'andamento')->count();
        $suspended = Project::where('category', 'parados')->count();

        return [
            'total' => $total,
            'finished' => $finished,
            'progress' => $progress,
            'suspended' => $suspended,
        ];
    }

    /**
     * API: Display a listing of the resource (JSON)
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
            // Validação dos dados
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'provincia' => 'required|string|max:100',
                'distrito' => 'required|string|max:100',
                'bairro' => 'required|string|max:100',
                'category' => 'required|in:andamento,parados,finalizados',
                'data_criacao' => 'required|date',
                
                // Objectivos
                'objectives' => 'required|array|min:1',
                'objectives.*.title' => 'required|string|max:255',
                'objectives.*.description' => 'required|string',
                
                // Financiamento
                'financiador' => 'required|string|max:255',
                'beneficiario' => 'required|string|max:255',
                'responsavel' => 'required|string|max:255',
                'valor_financiado' => 'required|string|max:100',
                'codigo' => 'required|string|max:50',
                
                // Prazos
                'data_aprovacao' => 'required|date',
                'data_inicio' => 'required|date',
                'data_inspecao' => 'required|date',
                'data_finalizacao' => 'required|date',
                'data_inauguracao' => 'required|date',
            ]);

            // Processar upload da imagem
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('projects', 'public');
            }

            // Criar projecto usando transação para garantir consistência
            return DB::transaction(function () use ($validated, $imagePath) {
                // Criar projecto
                $project = Project::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'image_url' => $imagePath,
                    'provincia' => $validated['provincia'],
                    'distrito' => $validated['distrito'],
                    'bairro' => $validated['bairro'],
                    'category' => $validated['category'],
                    'data_criacao' => $validated['data_criacao'],
                ]);

                // Criar objectivos
                foreach ($validated['objectives'] as $index => $objective) {
                    Objective::create([
                        'project_id' => $project->id,
                        'title' => $objective['title'],
                        'description' => $objective['description'],
                        'order' => $index + 1,
                    ]);
                }

                // Criar financiamento
                Finance::create([
                    'project_id' => $project->id,
                    'financiador' => $validated['financiador'],
                    'beneficiario' => $validated['beneficiario'],
                    'responsavel' => $validated['responsavel'],
                    'valor_financiado' => $validated['valor_financiado'],
                    'codigo' => $validated['codigo'],
                ]);

                // Criar prazos
                Deadline::create([
                    'project_id' => $project->id,
                    'data_aprovacao' => $validated['data_aprovacao'],
                    'data_inicio' => $validated['data_inicio'],
                    'data_inspecao' => $validated['data_inspecao'],
                    'data_finalizacao' => $validated['data_finalizacao'],
                    'data_inauguracao' => $validated['data_inauguracao'],
                ]);

                // Para Inertia, podemos redirecionar de volta à página
                return redirect()->route('projects')
                    ->with('success', 'Projecto criado com sucesso!');

            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Erro ao criar projecto: ' . $e->getMessage());
            return back()->with('error', 'Erro ao criar projecto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
{
    // Para requisições Inertia (navegação normal ou modal)
    if (request()->expectsJson() || request()->is('api/*')) {
        // Retorna JSON para requisições AJAX do modal
        return response()->json([
            'success' => true,
            'project' => $project->load(['objectives', 'finance', 'deadline'])
        ]);
    }
    
    // Para navegação normal
    return Inertia::render('Common/ProjectDetails', [
        'project' => $project->load(['objectives', 'finance', 'deadline'])
    ]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        try {
            // Validação com campos opcionais para actualização
            $validated = $request->validate([
                // Campos básicos - opcionais na actualização
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'provincia' => 'sometimes|required|string|max:100',
                'distrito' => 'sometimes|required|string|max:100',
                'bairro' => 'sometimes|required|string|max:100',
                'category' => 'sometimes|required|in:andamento,parados,finalizados',
                'data_criacao' => 'sometimes|required|date',
                
                // Objectivos - opcional, mas se fornecido deve ser válido
                'objectives' => 'sometimes|array|min:1',
                'objectives.*.title' => 'required_with:objectives|string|max:255',
                'objectives.*.description' => 'required_with:objectives|string',
                'objectives.*.id' => 'sometimes|integer|exists:objectives,id',
                
                // Financiamento - campos opcionais
                'financiador' => 'sometimes|string|max:255',
                'beneficiario' => 'sometimes|string|max:255',
                'responsavel' => 'sometimes|string|max:255',
                'valor_financiado' => 'sometimes|string|max:100',
                'codigo' => 'sometimes|string|max:50',
                
                // Prazos - campos opcionais
                'data_aprovacao' => 'sometimes|date',
                'data_inicio' => 'sometimes|date',
                'data_inspecao' => 'sometimes|date',
                'data_finalizacao' => 'sometimes|date',
                'data_inauguracao' => 'sometimes|date',
            ]);

            \Log::info('Iniciando actualização do projecto ID: ' . $project->id, $validated);

            return DB::transaction(function () use ($validated, $request, $project) {
                
                // ACTUALIZAR PROJECTO
                $projectData = [];
                
                // Campos básicos - apenas actualizar se fornecidos
                $basicFields = ['name', 'description', 'provincia', 'distrito', 'bairro', 'category', 'data_criacao'];
                foreach ($basicFields as $field) {
                    if (isset($validated[$field])) {
                        $projectData[$field] = $validated[$field];
                    }
                }
                
                // Processar upload da imagem se fornecida
                if ($request->hasFile('image')) {
                    // Remover imagem antiga se existir
                    if ($project->image_url) {
                        Storage::disk('public')->delete($project->image_url);
                    }
                    $imagePath = $request->file('image')->store('projects', 'public');
                    $projectData['image_url'] = $imagePath;
                }
                
                // Actualizar projecto apenas se houver dados para actualizar
                if (!empty($projectData)) {
                    $project->update($projectData);
                    \Log::info('Projecto actualizado com sucesso', $projectData);
                }

                // ACTUALIZAR OBJECTIVOS se fornecidos
                if (isset($validated['objectives'])) {
                    \Log::info('Actualizando objectivos', $validated['objectives']);
                    
                    // Coletar IDs de objectivos existentes para eliminar os que não estão na lista
                    $existingObjectiveIds = $project->objectives->pluck('id')->toArray();
                    $updatedObjectiveIds = [];
                    
                    foreach ($validated['objectives'] as $index => $objectiveData) {
                        if (isset($objectiveData['id'])) {
                            // Actualizar objectivo existente
                            $objective = Objective::where('id', $objectiveData['id'])
                                                ->where('project_id', $project->id)
                                                ->first();
                            
                            if ($objective) {
                                $objective->update([
                                    'title' => $objectiveData['title'],
                                    'description' => $objectiveData['description'],
                                    'order' => $index + 1,
                                ]);
                                $updatedObjectiveIds[] = $objectiveData['id'];
                            }
                        } else {
                            // Criar novo objectivo
                            $newObjective = Objective::create([
                                'project_id' => $project->id,
                                'title' => $objectiveData['title'],
                                'description' => $objectiveData['description'],
                                'order' => $index + 1,
                            ]);
                            $updatedObjectiveIds[] = $newObjective->id;
                        }
                    }
                    
                    // Eliminar objectivos que não estão na lista actualizada
                    $objectivesToDelete = array_diff($existingObjectiveIds, $updatedObjectiveIds);
                    if (!empty($objectivesToDelete)) {
                        Objective::whereIn('id', $objectivesToDelete)
                                ->where('project_id', $project->id)
                                ->delete();
                    }
                }

                // ACTUALIZAR FINANCIAMENTO se fornecido
                $financeData = [];
                $financeFields = ['financiador', 'beneficiario', 'responsavel', 'valor_financiado', 'codigo'];
                
                foreach ($financeFields as $field) {
                    if (isset($validated[$field])) {
                        $financeData[$field] = $validated[$field];
                    }
                }
                
                if (!empty($financeData)) {
                    if ($project->finance) {
                        $project->finance->update($financeData);
                    } else {
                        Finance::create(array_merge(['project_id' => $project->id], $financeData));
                    }
                    \Log::info('Financiamento actualizado', $financeData);
                }

                // ACTUALIZAR PRAZOS se fornecidos
                $deadlineData = [];
                $deadlineFields = [
                    'data_aprovacao', 'data_inicio', 'data_inspecao', 
                    'data_finalizacao', 'data_inauguracao'
                ];
                
                foreach ($deadlineFields as $field) {
                    if (isset($validated[$field])) {
                        $deadlineData[$field] = $validated[$field];
                    }
                }
                
                if (!empty($deadlineData)) {
                    if ($project->deadline) {
                        $project->deadline->update($deadlineData);
                    } else {
                        Deadline::create(array_merge(['project_id' => $project->id], $deadlineData));
                    }
                    \Log::info('Prazos actualizados', $deadlineData);
                }

                // Recarregar relações actualizadas
                $project->load(['objectives', 'finance', 'deadline']);

                \Log::info('Projecto actualizado com sucesso ID: ' . $project->id);

                return back()->with('success', 'Projecto actualizado com sucesso!');

            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erro de validação na actualização: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Erro ao actualizar projecto ID ' . $project->id . ': ' . $e->getMessage());
            return back()->with('error', 'Erro ao actualizar projecto: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        try {
            return DB::transaction(function () use ($project) {
                // Remover imagem se existir
                if ($project->image_url) {
                    Storage::disk('public')->delete($project->image_url);
                }
                
                // Remover objectivos
                $project->objectives()->delete();
                
                // Remover financiamento
                if ($project->finance) {
                    $project->finance()->delete();
                }
                
                // Remover prazos
                if ($project->deadline) {
                    $project->deadline()->delete();
                }
                
                // Remover projecto
                $project->delete();
                
                return back()->with('success', 'Projecto eliminado com sucesso!');
            });
        } catch (\Exception $e) {
            \Log::error('Erro ao eliminar projecto ID ' . $project->id . ': ' . $e->getMessage());
            return back()->with('error', 'Erro ao eliminar projecto: ' . $e->getMessage());
        }
    }
}