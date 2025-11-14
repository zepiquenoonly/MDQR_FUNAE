<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
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
        $adminUsers = [
            [
                'name' => 'Gestor de Reclamações',
                'username' => 'gestor',
                'email' => 'gestor@funae.co.mz',
                'role' => 'Gestor',
            ],
            [
                'name' => 'Técnico de Suporte',
                'username' => 'tecnico',
                'email' => 'tecnico@funae.co.mz',
                'role' => 'Técnico',
            ],
            [
                'name' => 'PCA',
                'username' => 'pca',
                'email' => 'pca@funae.co.mz',
                'role' => 'PCA',
            ],
        ];

        foreach ($adminUsers as $userData) {
            $user = User::firstOrCreate(
                ['username' => $userData['username']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                ]
            );

            $user->assignRole($userData['role']);
        }
    }
}
