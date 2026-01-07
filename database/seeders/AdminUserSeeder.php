<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates default admin users for the GRM system:
     * - Gestor: Manager role for complaint management
     * - Técnico: Technical support role
     * - PCA: Principal Coordinator role for reports
     *
     * Default password: 'password' (should be changed in production)
     */
    public function run(): void
    {
        // Obter departamentos existentes para atribuição
        $departments = Department::pluck('id');
        $defaultDepartmentId = $departments->isNotEmpty() ? $departments->first() : null;

        $adminUsers = [
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@funae.co.mz',
                'role' => 'Admin',
                'needs_department' => false,
                'locale' => 'pt', // ← ADICIONAR LOCALE
            ],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@funae.co.mz',
                'role' => 'Super Admin',
                'needs_department' => false,
                'locale' => 'pt', // ← ADICIONAR LOCALE
            ],
            [
                'name' => 'Gestor de Reclamações',
                'username' => 'gestor',
                'email' => 'gestor@funae.co.mz',
                'role' => 'Gestor',
                'needs_department' => true,
                'locale' => 'pt', // ← ADICIONAR LOCALE
            ],
            [
                'name' => 'Técnico de Suporte',
                'username' => 'tecnico',
                'email' => 'tecnico@funae.co.mz',
                'role' => 'Técnico',
                'needs_department' => true,
                'locale' => 'pt', // ← ADICIONAR LOCALE
            ],
            [
                'name' => 'PCA',
                'username' => 'pca',
                'email' => 'pca@funae.co.mz',
                'role' => 'PCA',
                'needs_department' => false,
                'locale' => 'pt', // ← ADICIONAR LOCALE
            ],
            [
                'name' => 'Utente',
                'username' => 'utente',
                'email' => 'utente@gmail.com',
                'role' => 'Utente',
                'needs_department' => false,
                'locale' => 'pt', // ← ADICIONAR LOCALE
            ],
            [
                'name' => 'Técnico de Suporte 2',
                'username' => 'tecnico2',
                'email' => 'tecnico2@funae.co.mz',
                'role' => 'Técnico',
                'needs_department' => true,
                'locale' => 'en', // ← TÉCNICO 2 EM INGLÊS PARA TESTE
            ],
            [
                'name' => 'Director de Departamento',
                'username' => 'director',
                'email' => 'director@funae.co.mz',
                'phone' => '+258846789012',
                'role' => 'Director',
                'needs_department' => true,
                'locale' => 'pt', // ← ADICIONAR LOCALE
            ],
        ];

        foreach ($adminUsers as $userData) {
            $user = User::firstOrCreate(
                ['username' => $userData['username']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'phone' => $userData['phone'] ?? null,
                    'department_id' => ($userData['needs_department'] && $defaultDepartmentId) ? $defaultDepartmentId : null,
                    'locale' => $userData['locale'] ?? 'pt', // ← ADICIONAR LOCALE
                ]
            );

            $user->assignRole($userData['role']);
            
            // Se já existe e precisa de department mas não tem, atualizar
            if ($userData['needs_department'] && !$user->department_id && $defaultDepartmentId) {
                $user->update(['department_id' => $defaultDepartmentId]);
            }
            
            // Atualizar locale se diferente
            if ($user->locale !== $userData['locale']) {
                $user->update(['locale' => $userData['locale']]);
            }
        }

        // Setup Relationships: Department and Project assignments
        $this->setupRelationships();
    }

    /**
     * Setup default relationships for seeded users.
     */
    private function setupRelationships(): void
    {
        // 1. Find key users
        $director = User::where('username', 'director')->first();
        $gestor = User::where('username', 'gestor')->first();
        $tecnico = User::where('username', 'tecnico')->first();
        $tecnico2 = User::where('username', 'tecnico2')->first();

        if (!$director) return;

        // 2. Create or find a Default Department
        $department = Department::firstOrCreate(
            ['name' => 'Departamento de Operações e Suporte'],
            [
                'description' => 'Departamento central para gestão de operações e suporte técnico.',
                'manager_id' => $director->id,
            ]
        );

        // Ensure director is the manager if department existed but had different/no manager
        if ($department->manager_id !== $director->id) {
            $department->manager_id = $director->id;
            $department->save();
        }

        // 3. Assign Users to Department (garantir que todos têm department_id)
        $usersToAssign = array_filter([$director, $gestor, $tecnico, $tecnico2]);
        foreach ($usersToAssign as $user) {
            if (!$user->department_id) {
                $user->department_id = $department->id;
                $user->save();
            }
        }

        // 4. Create or find a Default Project
        $project = Project::firstOrCreate(
            ['name' => 'Projecto de Manutenção Geral'],
            [
                'description' => 'Projecto transversal para manutenção e suporte contínuo.',
                'provincia' => 'Maputo',
                'distrito' => 'Maputo Cidade',
                'bairro' => 'Central',
                'category' => 'andamento',
                'data_criacao' => now(),
                'department_id' => $department->id,
            ]
        );

        // Ensure project belongs to department if it existed
        if ($project->department_id !== $department->id) {
            $project->department_id = $department->id;
            $project->save();
        }

        // 5. Assign Technicians to Project
        $technicians = [$tecnico, $tecnico2];
        foreach ($technicians as $tech) {
            if ($tech && !$tech->isAssignedToProject($project->id)) {
                $tech->projects()->attach($project->id);
            }
        }
    }
}