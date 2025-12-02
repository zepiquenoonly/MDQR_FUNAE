<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProjectTechnicianSeeder extends Seeder
{
    /**
     * Assign technicians to projects.
     * Each project gets at least one technician assigned.
     */
    public function run(): void
    {
        // Get all technicians
        $technicians = User::role('Técnico')->get();

        if ($technicians->isEmpty()) {
            $this->command->warn('No technicians found. Please run AdminUserSeeder first.');
            return;
        }

        // Get all projects
        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->command->warn('No projects found. Please run ProjectSeeder first.');
            return;
        }

        $technicianCount = $technicians->count();
        $projectCount = $projects->count();

        $this->command->info("Assigning {$technicianCount} technicians to {$projectCount} projects...");

        // Distribute technicians across projects evenly
        $technicianIndex = 0;

        foreach ($projects as $project) {
            // Assign at least one technician to each project
            $technician = $technicians[$technicianIndex % $technicianCount];
            
            // Check if already assigned
            if (!$project->technicians()->where('users.id', $technician->id)->exists()) {
                $project->technicians()->attach($technician->id);
                $this->command->line("  - {$technician->name} assigned to: {$project->name}");
            }

            $technicianIndex++;

            // For projects in provinces with multiple districts, assign a second technician if available
            if ($technicianCount > 1 && in_array($project->provincia, ['Maputo', 'Nampula', 'Sofala', 'Zambézia'])) {
                $secondTechnician = $technicians[($technicianIndex) % $technicianCount];
                
                if ($secondTechnician->id !== $technician->id && 
                    !$project->technicians()->where('users.id', $secondTechnician->id)->exists()) {
                    $project->technicians()->attach($secondTechnician->id);
                    $this->command->line("  - {$secondTechnician->name} also assigned to: {$project->name}");
                }
            }
        }

        $this->command->info('Technician assignment completed!');
        
        // Summary
        $this->command->newLine();
        $this->command->info('Summary of assignments:');
        foreach ($technicians as $technician) {
            $projectsCount = $technician->projects()->count();
            $this->command->line("  {$technician->name}: {$projectsCount} projects");
        }
    }
}
