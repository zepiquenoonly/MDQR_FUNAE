<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates the basic roles and permissions for the GRM system:
     * - Utente: Can submit complaints and view their own complaints
     * - Técnico: Can manage complaints
     * - Gestor: Can manage and conclude complaints
     * - PCA: Can view reports
     */
    public function run(): void
    {
        // Create basic permissions
        $permissions = [
            'submit_complaint',
            'view_own_complaints',
            'manage_complaints',
            'conclude_complaints',
            'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $roles = [
            'Utente' => ['submit_complaint', 'view_own_complaints'],
            'Técnico' => ['manage_complaints'],
            'Gestor' => ['manage_complaints', 'conclude_complaints'],
            'PCA' => ['view_reports'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
