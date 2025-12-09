<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class RetryFailedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:retry {--max-retries=3 : Maximum number of retry attempts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry sending failed notifications';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $maxRetries = (int) $this->option('max-retries');
        
        $this->info("Retrying failed notifications (max retries: {$maxRetries})...");
        
        try {
            $retried = $notificationService->retryFailedNotifications($maxRetries);
            
            $this->info("Successfully retried {$retried} notification(s).");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to retry notifications: {$e->getMessage()}");
            
            return Command::FAILURE;
        }
    }
}
