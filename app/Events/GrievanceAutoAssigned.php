<?php

namespace App\Events;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GrievanceAutoAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Grievance $grievance;
    public User $technician;

    /**
     * Create a new event instance.
     */
    public function __construct(Grievance $grievance, User $technician)
    {
        $this->grievance = $grievance;
        $this->technician = $technician;
    }
}
