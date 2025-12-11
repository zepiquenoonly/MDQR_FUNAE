<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateTechnicianWorkloadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🔧 Atualizando campos de workload apenas para Técnicos...\n\n";

        // Get all technicians
        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'Técnico');
        })->get();

        if ($technicians->isEmpty()) {
            echo "⚠️  Nenhum técnico encontrado.\n";
            return;
        }

        $updated = 0;
        foreach ($technicians as $technician) {
            // Set workload values only for technicians
            $technician->update([
                'workload_capacity' => 10, // Capacity: 10 cases
                'current_workload' => rand(0, 5), // Random current workload
                'is_available' => true,
            ]);
            $updated++;
            echo "  ✅ {$technician->name} - Capacidade: 10, Carga atual: {$technician->current_workload}\n";
        }

        echo "\n✅ {$updated} técnicos atualizados com campos de workload!\n";
        
        // Clear workload for non-technicians
        $nonTechnicians = User::whereDoesntHave('roles', function($q) {
            $q->where('name', 'Técnico');
        })->get();

        $cleared = 0;
        foreach ($nonTechnicians as $user) {
            $user->update([
                'workload_capacity' => null,
                'current_workload' => null,
                'is_available' => null,
            ]);
            $cleared++;
        }

        echo "🧹 {$cleared} usuários não-técnicos tiveram os campos de workload limpos (null).\n";
    }
}
