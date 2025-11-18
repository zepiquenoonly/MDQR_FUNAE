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
            ]
        );

        if ($testUser->roles()->count() === 0) {
            $testUser->assignRole('Utente');
        }

        // Create sample grievances with different statuses
        $this->call(GrievanceSeeder::class);
    }
}
