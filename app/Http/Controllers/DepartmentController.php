<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage-departments');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Department::with('manager');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $departments = $query->paginate(10)
            ->withQueryString()
            ->through(fn ($department) => [
                'id' => $department->id,
                'name' => $department->name,
                'description' => $department->description,
                'manager' => $department->manager ? $department->manager->name : 'N/A',
                'manager_id' => $department->manager_id,
                'created_at' => $department->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $managers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Director', 'Gestor']);
        })->get(['id', 'name']);
        
        return Inertia::render('Admin/Departments/Create', [
            'managers' => $managers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Departamento criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return Inertia::render('Admin/Departments/Show', [
            'department' => $department->load('manager'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $managers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Director', 'Gestor']);
        })->get(['id', 'name']);
        
        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department->load('manager'),
            'managers' => $managers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Departamento atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')->with('success', 'Departamento removido com sucesso.');
    }
}
