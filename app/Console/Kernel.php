<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Update indicator records daily at midnight
        $schedule->call(function () {
            \App\Http\Controllers\DepartmentIndicatorController::updateIndicatorRecords();
        })
        ->name('update-indicator-records')
        ->dailyAt('00:00')
        ->withoutOverlapping()
        ->onOneServer();

        // Generate daily report at 6 AM
        $schedule->call(function () {
            $reportService = app('report.generator');
            $reportService->generateDailyReport();
        })
        ->name('generate-daily-report')
        ->dailyAt('06:00')
        ->withoutOverlapping();

        // Generate weekly report every Monday at 8 AM
        $schedule->call(function () {
            $reportService = app('report.generator');
            $reportService->generateWeeklyReport();
        })
        ->name('generate-weekly-report')
        ->weekly()
        ->mondays()
        ->at('08:00')
        ->withoutOverlapping();

        // Generate monthly report on the first day of the month at 9 AM
        $schedule->call(function () {
            $reportService = app('report.generator');
            $reportService->generateMonthlyReport();
        })
        ->name('generate-monthly-report')
        ->monthly()
        ->on(1)
        ->at('09:00')
        ->withoutOverlapping();

        // Clean up old reports (older than 90 days)
        $schedule->call(function () {
            \App\Models\Report::where('created_at', '<', now()->subDays(90))
                ->where('status', 'completed')
                ->chunk(100, function ($reports) {
                    foreach ($reports as $report) {
                        if ($report->file_path) {
                            \Storage::delete('public/' . $report->file_path);
                        }
                        $report->delete();
                    }
                });
        })
        ->name('cleanup-old-reports')
        ->weekly()
        ->sundays()
        ->at('02:00');

        // Backup indicator data
        $schedule->command('backup:run --only-db')
            ->name('backup-indicator-database')
            ->dailyAt('01:00')
            ->onOneServer();

        // Test indicator calculations
        $schedule->call(function () {
            $indicatorService = app('indicator.calculator');
            $indicators = \App\Models\DepartmentIndicator::where('is_active', true)->get();
            
            foreach ($indicators as $indicator) {
                try {
                    $value = $indicatorService->calculate($indicator, now());
                    \Log::info("Indicator {$indicator->name} calculated: {$value}");
                } catch (\Exception $e) {
                    \Log::error("Error calculating indicator {$indicator->name}: {$e->getMessage()}");
                }
            }
        })
        ->name('test-indicator-calculations')
        ->dailyAt('04:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}