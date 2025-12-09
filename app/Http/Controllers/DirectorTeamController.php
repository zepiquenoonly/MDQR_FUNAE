<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class DirectorTeamController extends Controller
{
    /**
     * Verificar se o usuário tem acesso de Director
     */
    private function checkAccess($user)
    {
        if (!$user) {
            abort(403, 'Usuário não autenticado.');
        }
        
        if (!$user->hasRole('Director')) {
            abort(403, 'Acesso não autorizado. Apenas Directors podem acessar esta página.');
        }
    }

    /**
     * Listar todos os funcionários (gestores e técnicos)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);
        
        $query = User::with(['roles'])
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['gestor', 'Técnico']);
            });

        // Filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_available', $request->status === 'active');
        }

        $employees = $query->paginate(15)
            ->withQueryString()
            ->through(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'photo' => $employee->avatar_url,
                    'avatar_url' => $employee->avatar_url,
                    'position' => $employee->getRoleNames()->first(),
                    'employee_id' => $employee->username,
                    'username' => $employee->username,
                    'phone' => $employee->phone,
                    'province' => $employee->province,
                    'district' => $employee->district,
                    'neighborhood' => $employee->neighborhood,
                    'street' => $employee->street,
                    'block' => $employee->block,
                    'house_number' => $employee->house_number,
                    'department' => null, // Não temos departamento no User model
                    'department_id' => null,
                    'status' => $employee->is_available ? 'active' : 'inactive',
                    'is_available' => $employee->is_available ?? true,
                    'roles' => $employee->getRoleNames(),
                    'primary_role' => $employee->getRoleNames()->first(),
                    'hire_date' => $employee->created_at->format('Y-m-d'),
                    'last_login' => $employee->last_login_at?->format('d/m/Y H:i') ?? 'Nunca',
                    'workload' => [
                        'current' => $employee->current_workload ?? 0,
                        'capacity' => $employee->workload_capacity ?? 20,
                        'percentage' => $employee->workload_capacity > 0 
                            ? round(($employee->current_workload / $employee->workload_capacity) * 100) 
                            : 0,
                    ],
                ];
            });

        // Estatísticas
        $stats = [
            'total' => User::whereHas('roles', function($query) {
                $query->whereIn('name', ['gestor', 'Técnico']);
            })->count(),
            'active' => User::whereHas('roles', function($query) {
                $query->whereIn('name', ['gestor', 'Técnico']);
            })->where('is_available', true)->count(),
            'inactive' => User::whereHas('roles', function($query) {
                $query->whereIn('name', ['gestor', 'Técnico']);
            })->where('is_available', false)->count(),
            'by_role' => [
                'gestor' => User::role('gestor')->count(),
                'technician' => User::role('Técnico')->count(),
            ],
        ];

        return Inertia::render('Director/StaffPage', [
            'employees' => $employees,
            'stats' => $stats,
            'departments' => [], // Array vazio por enquanto
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }


    /**
     * Mostrar detalhes de um funcionário
     */
     public function show($id)
    {
        $user = request()->user();
        $this->checkAccess($user);

        $employee = User::with(['roles', 'assignedGrievances' => function($query) {
            $query->with(['category', 'statusUpdates'])
                ->latest()
                ->limit(10);
        }])->findOrFail($id);

        // Estatísticas do funcionário
        $stats = [
            'total_cases' => $employee->assignedGrievances()->count(),
            'pending_cases' => $employee->assignedGrievances()
                ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
                ->count(),
            'resolved_cases' => $employee->assignedGrievances()->where('status', 'resolved')->count(),
            'avg_resolution_time' => $employee->assignedGrievances()
                ->where('status', 'resolved')
                ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
                ->first()
                ->avg_days ?? 0,
            'success_rate' => $employee->assignedGrievances()->count() > 0
                ? round(($employee->assignedGrievances()->where('status', 'resolved')->count() 
                    / $employee->assignedGrievances()->count()) * 100)
                : 0,
        ];

        // Histórico mensal de casos
        $monthlyPerformance = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            
            $monthlyPerformance[] = [
                'month' => $monthStart->format('M/Y'),
                'assigned' => $employee->assignedGrievances()
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count(),
                'resolved' => $employee->assignedGrievances()
                    ->where('status', 'resolved')
                    ->whereBetween('resolved_at', [$monthStart, $monthEnd])
                    ->count(),
            ];
        }

        return Inertia::render('Director/EmployeeDetail', [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'username' => $employee->username,
                'phone' => $employee->phone,
                'avatar_url' => $employee->avatar_url,
                'is_available' => $employee->is_available ?? true,
                'roles' => $employee->getRoleNames(),
                'province' => $employee->province,
                'district' => $employee->district,
                'neighborhood' => $employee->neighborhood,
                'street' => $employee->street,
                'block' => $employee->block,
                'house_number' => $employee->house_number,
                'created_at' => $employee->created_at->format('d/m/Y'),
                'last_login' => $employee->last_login_at?->format('d/m/Y H:i') ?? 'Nunca',
                'workload_capacity' => $employee->workload_capacity ?? 20,
                'current_workload' => $employee->current_workload ?? 0,
            ],
            'stats' => $stats,
            'monthlyPerformance' => $monthlyPerformance,
            'recent_cases' => $employee->assignedGrievances->map(function ($grievance) {
                return [
                    'id' => $grievance->id,
                    'reference_number' => $grievance->reference_number,
                    'description' => $grievance->description,
                    'status' => $grievance->status,
                    'priority' => $grievance->priority,
                    'category' => $grievance->category,
                    'created_at' => $grievance->created_at->format('d/m/Y'),
                    'days_pending' => $grievance->created_at->diffInDays(now()),
                ];
            }),
        ]);
    }


    /**
     * Mostrar formulário para editar funcionário
     */
    public function edit($id)
    {
        $user = request()->user();
        $this->checkAccess($user);

        $employee = User::with(['roles'])->findOrFail($id);
        $roles = Role::whereIn('name', ['gestor', 'Técnico'])->get();

        return Inertia::render('Director/Team/Edit', [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'username' => $employee->username,
                'phone' => $employee->phone,
                'province' => $employee->province,
                'district' => $employee->district,
                'neighborhood' => $employee->neighborhood,
                'street' => $employee->street,
                'block' => $employee->block,
                'house_number' => $employee->house_number,
                'is_available' => $employee->is_available ?? true,
                'role' => $employee->getRoleNames()->first(),
                'workload_capacity' => $employee->workload_capacity ?? 20,
            ],
            'roles' => $roles,
        ]);
    }


    /**
     * Criar novo funcionário
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'street' => 'nullable|string|max:255',
            'block' => 'nullable|string|max:50',
            'house_number' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|in:gestor,Técnico',
            'workload_capacity' => 'integer|min:10|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Criar usuário
            $employee = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'phone' => $validated['phone'],
                'province' => $validated['province'],
                'district' => $validated['district'],
                'neighborhood' => $validated['neighborhood'],
                'street' => $validated['street'] ?? null,
                'block' => $validated['block'] ?? null,
                'house_number' => $validated['house_number'] ?? null,
                'password' => Hash::make($validated['password']),
                'workload_capacity' => $validated['workload_capacity'] ?? 20,
                'is_available' => true,
            ]);

            // Atribuir role
            $employee->assignRole($validated['role']);

            DB::commit();

            return redirect()->route('director.team.index')
                ->with('success', 'Funcionário criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao criar funcionário: ' . $e->getMessage()]);
        }
    }

    /**
     * Atualizar funcionário
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $employee = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'phone' => 'required|string|max:20',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'street' => 'nullable|string|max:255',
            'block' => 'nullable|string|max:50',
            'house_number' => 'nullable|string|max:20',
            'role' => 'required|string|in:gestor,Técnico',
            'workload_capacity' => 'integer|min:10|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Atualizar dados básicos
            $employee->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'phone' => $validated['phone'],
                'province' => $validated['province'],
                'district' => $validated['district'],
                'neighborhood' => $validated['neighborhood'],
                'street' => $validated['street'] ?? null,
                'block' => $validated['block'] ?? null,
                'house_number' => $validated['house_number'] ?? null,
                'workload_capacity' => $validated['workload_capacity'] ?? $employee->workload_capacity,
            ]);

            // Atualizar role se necessário
            $currentRole = $employee->getRoleNames()->first();
            if ($currentRole !== $validated['role']) {
                $employee->syncRoles([]);
                $employee->assignRole($validated['role']);
            }

            DB::commit();

            return redirect()->route('director.team.index')
                ->with('success', 'Funcionário atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao atualizar funcionário: ' . $e->getMessage()]);
        }
    }

    /**
     * Alternar status do funcionário (ativo/inativo)
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $employee = User::findOrFail($id);
        $newStatus = !$employee->is_available;

        $employee->update(['is_available' => $newStatus]);

        return back()->with('success', 
            'Status do funcionário alterado para ' . ($newStatus ? 'ativo' : 'inativo') . '!'
        );
    }

    /**
     * Remover funcionário
     */
    public function destroy($id)
    {
        $user = request()->user();
        $this->checkAccess($user);

        $employee = User::findOrFail($id);

        // Verificar se o funcionário tem casos atribuídos
        $hasActiveCases = $employee->assignedGrievances()
            ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->exists();

        if ($hasActiveCases) {
            return back()->withErrors([
                'error' => 'Não é possível remover um funcionário com casos ativos.'
            ]);
        }

        $employee->delete();

        return redirect()->route('director.team.index')
            ->with('success', 'Funcionário removido com sucesso!');
    }

    /**
     * Gerar relatório da equipe
     */
    public function generateReport(Request $request)
    {
        $user = $request->user();
        $this->checkAccess($user);

        $period = $request->query('period', 'monthly');
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        switch ($period) {
            case 'weekly':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'quarterly':
                $startDate = now()->startOfQuarter();
                $endDate = now()->endOfQuarter();
                break;
            case 'yearly':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
        }

        // Dados do relatório
        $reportData = [
            'period' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y'),
                'type' => $period,
            ],
            'team_stats' => [
                'total_employees' => User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['gestor', 'Técnico']);
                })->count(),
                'active_employees' => User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['gestor', 'Técnico']);
                })->where('is_available', true)->count(),
                'by_role' => [
                    'gestor' => User::role('gestor')->count(),
                    'technician' => User::role('Técnico')->count(),
                ],
            ],
            'performance_summary' => [
                'total_cases' => \App\Models\Grievance::whereBetween('created_at', [$startDate, $endDate])->count(),
                'resolved_cases' => \App\Models\Grievance::where('status', 'resolved')
                    ->whereBetween('resolved_at', [$startDate, $endDate])
                    ->count(),
                'avg_resolution_time' => \App\Models\Grievance::where('status', 'resolved')
                    ->whereBetween('resolved_at', [$startDate, $endDate])
                    ->selectRaw('AVG(DATEDIFF(resolved_at, created_at)) as avg_days')
                    ->first()
                    ->avg_days ?? 0,
            ],
            'top_performers' => User::withCount(['assignedGrievances as cases_resolved' => function($query) use ($startDate, $endDate) {
                    $query->where('status', 'resolved')
                        ->whereBetween('resolved_at', [$startDate, $endDate]);
                }])
                ->whereHas('roles', function($query) {
                    $query->whereIn('name', ['gestor', 'Técnico']);
                })
                ->orderByDesc('cases_resolved')
                ->limit(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'name' => $user->name,
                        'role' => $user->getRoleNames()->first(),
                        'cases_resolved' => $user->cases_resolved,
                    ];
                }),
        ];

        // Se for solicitado download em PDF
        if ($request->has('download')) {
            // Aqui você pode implementar geração de PDF usando DomPDF, TCPDF, etc.
            return response()->json([
                'message' => 'Relatório gerado com sucesso',
                'data' => $reportData,
            ]);
        }
    }
}