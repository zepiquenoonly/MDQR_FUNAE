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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Grievance permissions
            'submit_complaint',
            'view_own_complaints',
            'manage_complaints',
            'conclude_complaints',
            'view_reports',

            // Administrative permissions
            'manage-users',
            'manage-projects',
            'manage-departments',
            'manage-settings',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles and their specific permissions
        $roles = [
            'Utente' => ['submit_complaint', 'view_own_complaints'],
            'Técnico' => ['manage_complaints'],
            'Gestor' => ['manage_complaints', 'conclude_complaints'],
            'Director' => ['manage_complaints', 'conclude_complaints', 'view_reports'],
            'PCA' => ['view_reports'],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // Create Admin role and assign all permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions(Permission::all());
    }
}
