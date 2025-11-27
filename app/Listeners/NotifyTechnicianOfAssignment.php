<?php

namespace App\Listeners;

use App\Events\GrievanceAutoAssigned;
use App\Mail\GrievanceAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyTechnicianOfAssignment implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(GrievanceAutoAssigned $event): void
    {
        try {
            // Send email notification to the assigned technician
            Mail::to($event->technician->email)->send(
                new GrievanceAssigned($event->grievance, $event->technician)
            );

            Log::info("Auto-assignment notification sent to {$event->technician->email} for grievance {$event->grievance->reference_number}");
        } catch (\Exception $e) {
            Log::error("Failed to send auto-assignment notification: {$e->getMessage()}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(GrievanceAutoAssigned $event, \Throwable $exception): void
    {
        Log::error("Failed to process auto-assignment notification for grievance {$event->grievance->reference_number}: {$exception->getMessage()}");
    }
}
