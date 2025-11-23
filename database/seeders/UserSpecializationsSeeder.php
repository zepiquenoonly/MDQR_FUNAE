<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSpecialization;
use Illuminate\Database\Seeder;

class UserSpecializationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Energia',
            'Infraestrutura',
            'Equipamento',
            'Atendimento',
            'Processo Administrativo',
            'Outro',
        ];

        $technicians = User::role('Técnico')->get();

        foreach ($technicians as $technician) {
            // Assign random specializations
            $numSpecializations = rand(1, 3);
            $selectedCategories = collect($categories)->random($numSpecializations);

            foreach ($selectedCategories as $category) {
                UserSpecialization::firstOrCreate([
                    'user_id' => $technician->id,
                    'category' => $category,
                ], [
                    'proficiency_level' => rand(1, 4),
                ]);
            }

            // Set workload capacity
            $technician->update([
                'workload_capacity' => rand(8, 15),
                'is_available' => true,
            ]);

            // Calculate initial workload
            $technician->updateWorkload();
        }

        $this->command->info('User specializations seeded successfully!');
    }
}
