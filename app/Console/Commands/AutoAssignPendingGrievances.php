<?php

namespace App\Console\Commands;

use App\Services\GrievanceAutoAssignmentService;
use Illuminate\Console\Command;

class AutoAssignPendingGrievances extends Command
{
    protected $signature = 'grievance:auto-assign-pending';
    protected $description = 'Auto-assign all pending grievances to available technicians';

    public function handle(GrievanceAutoAssignmentService $service)
    {
        $this->info('Starting auto-assignment of pending grievances...');

        $results = $service->autoAssignPending();

        $this->info("Total grievances: {$results['total']}");
        $this->info("Successfully assigned: {$results['assigned']}");
        
        if ($results['failed'] > 0) {
            $this->warn("Failed to assign: {$results['failed']}");
        }

        $this->info('Auto-assignment completed!');

        return Command::SUCCESS;
    }
}
