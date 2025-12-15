<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('can:manage-projects')->except(['index', 'show']);
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

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // Retornar dados do projecto (para API ou modal)
        return response()->json([
            'success' => true,
            'project' => $project
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
}
