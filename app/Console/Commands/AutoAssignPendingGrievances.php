<?php

namespace App\Console\Commands;

use App\Services\GrievanceAutoAssignmentService;
use Illuminate\Console\Command;

class AutoAssignPendingGrievances extends Command
{
    protected $signature = 'grievance:auto-assign 
                            {--force : Force assign grievances waiting 24+ hours}
                            {--immediate : Try to assign new submissions immediately}';
    
    protected $description = 'Auto-assign pending grievances to available technicians';

    public function handle(GrievanceAutoAssignmentService $service)
    {
        if ($this->option('force')) {
            $this->info('Force-assigning grievances pending for 24+ hours...');
            $results = $service->autoAssignAfter24Hours();
            
            $this->info("Total pending 24h+: {$results['total']}");
            $this->info("Successfully assigned: {$results['assigned']}");
            
            if ($results['failed'] > 0) {
                $this->warn("Failed to assign: {$results['failed']}");
            }
        } else {
            $this->info('Auto-assigning new submissions to technicians with light workload...');
            $results = $service->autoAssignPending();

            $this->info("Total grievances: {$results['total']}");
            $this->info("Assigned (light workload): {$results['assigned']}");
            $this->info("Left pending for review: {$results['pending']}");
        }

        $this->info('Auto-assignment completed!');

        return Command::SUCCESS;
    }
}
