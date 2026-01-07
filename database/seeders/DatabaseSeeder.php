<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * This seeder creates:
     * - Roles and permissions for the GRM system
     * - Default admin users (Gestor, Técnico, PCA)
     * - A test user for development purposes
     */
    public function run(): void
    {
        // Create roles and permissions first
        $this->call(RoleSeeder::class);

        // Create admin users
        $this->call(AdminUserSeeder::class);

        // Create a test user for development (if not exists)
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => Hash::make('password'),
                'locale' => 'en'
            ]
        );

        if ($testUser->roles()->count() === 0) {
            $testUser->assignRole('Utente');
        }

         // Create departments with directors and assign users/projects
        $this->call(DepartmentSeeder::class);

        // Create sample grievances with different statuses
        $this->call(GrievanceSeeder::class);

        // Another seeders cruciais
        $this->call(ProjectSeeder::class);
        $this->call(UserSpecializationsSeeder::class);
        
        // Create additional managers and technicians
        $this->call(AdditionalUsersSeeder::class);
        
        // Update workload fields for technicians only
        $this->call(UpdateTechnicianWorkloadSeeder::class);
        
        // Assign technicians to projects
        $this->call(ProjectTechnicianSeeder::class);
        
    }

    /**
     * Run performance test seeder (optional - use with caution).
     * 
     * To seed with large volumes of test data, use the artisan command:
     * php artisan db:seed-performance --utentes=500 --tecnicos=20 --gestores=5 --reclamacoes=2000
     */
    public function runPerformanceTest(): void
    {
        $this->call(PerformanceTestSeeder::class);
    }
}
