<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Queue Worker Scheduler para processar emails automaticamente em produção
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=60')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Auto-assign grievances pending for 24+ hours (runs every hour)
Schedule::command('grievance:auto-assign --force')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/auto-assignment.log'));
