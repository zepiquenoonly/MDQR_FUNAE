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
            ]);
        }
    }

    public function indexProjects()
    {
        return Inertia::render('Admin/Projects/Index');
    }

    public function createProject()
    {
        return Inertia::render('Admin/Projects/Create');
    }

    public function editProject(Project $project)
    {
        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project->load(['objectives', 'finance', 'deadline']),
        ]);
    }
}
