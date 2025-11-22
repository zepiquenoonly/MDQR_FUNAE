<?php

namespace App\Console\Commands;

use App\Services\GrievanceAutoAssignmentService;
use Illuminate\Console\Command;

class RebalanceWorkload extends Command
{
    protected $signature = 'grievance:rebalance-workload';
    protected $description = 'Recalculate workload for all technicians';

    public function handle(GrievanceAutoAssignmentService $service)
    {
        $this->info('Rebalancing workload for all technicians...');
        
        $service->rebalanceWorkload();
        
        $this->info('Workload rebalanced successfully!');

        return Command::SUCCESS;
    }
}
