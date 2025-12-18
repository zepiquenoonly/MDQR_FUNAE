<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Project;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        try {
            $user = auth()->user();

            // Get statistics with default values
            $stats = [
                'totalUsers' => \App\Models\User::count() ?? 0,
                'totalDepartments' => \App\Models\Department::count() ?? 0,
                'totalProjects' => \App\Models\Project::count() ?? 0,
                'activeUsers' => \App\Models\User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['Técnico', 'Gestor', 'Director']);
                })->count() ?? 0,
            ];

            // Get user distribution by role
            $usersByRole = [
                'utentes' => \App\Models\User::whereHas('roles', function($query) {
                    $query->where('name', 'Utente');
                })->count() ?? 0,
                'tecnicos' => \App\Models\User::whereHas('roles', function($query) {
                    $query->where('name', 'Técnico');
                })->count() ?? 0,
                'gestores' => \App\Models\User::whereHas('roles', function($query) {
                    $query->where('name', 'Gestor');
                })->count() ?? 0,
                'directores' => \App\Models\User::whereHas('roles', function($query) {
                    $query->where('name', 'Director');
                })->count() ?? 0,
                'pca' => \App\Models\User::whereHas('roles', function($query) {
                    $query->where('name', 'PCA');
                })->count() ?? 0,
            ];

            // Get user permissions
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();

            return Inertia::render('Admin/Dashboard', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first(),
                ],
                'permissions' => $permissions,
                'stats' => $stats,
                'usersByRole' => $usersByRole,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in Admin Dashboard: ' . $e->getMessage());

            // Return with default values
            return Inertia::render('Admin/Dashboard', [
                'user' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name ?? 'Admin',
                    'email' => auth()->user()->email ?? '',
                    'role' => 'Admin',
                ],
                'permissions' => [],
                'stats' => [
                    'totalUsers' => 0,
                    'totalDepartments' => 0,
                    'totalProjects' => 0,
                    'activeUsers' => 0,
                ],
                'usersByRole' => [
                    'utentes' => 0,
                    'tecnicos' => 0,
                    'gestores' => 0,
                    'directores' => 0,
                    'pca' => 0,
                ],
            ]);
        }
    }

    public function indexProjects(Request $request)
    {
        $query = Project::with('department');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        $projects = $query->paginate(10)
            ->withQueryString()
            ->through(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'department' => $project->department?->name ?? 'N/A',
                'department_id' => $project->department_id,
                'provincia' => $project->provincia,
                'distrito' => $project->distrito,
                'is_active' => $project->is_active,
                'created_at' => $project->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'departments' => \App\Models\Department::all(['id', 'name']),
            'filters' => [
                'search' => $request->input('search', ''),
                'department' => $request->input('department', ''),
            ],
        ]);
    }

    public function createProject()
    {
        return Inertia::render('Admin/Projects/Create', [
            'departments' => \App\Models\Department::all(['id', 'name']),
        ]);
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'provincia' => 'nullable|string|max:255',
            'distrito' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        Project::create($validated);

        return redirect()->route('admin.projects.index')->with('success', 'Projecto criado com sucesso.');
    }

    public function editProject(Project $project)
    {
        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project->load(['objectives', 'finance', 'deadline', 'department']),
            'departments' => \App\Models\Department::all(['id', 'name']),
        ]);
    }

    public function updateProject(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'provincia' => 'nullable|string|max:255',
            'distrito' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        $project->update($validated);

        return redirect()->route('admin.projects.index')->with('success', 'Projecto atualizado com sucesso.');
    }

    public function destroyProject(Project $project)
    {
        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Projecto removido com sucesso.');
    }
}
