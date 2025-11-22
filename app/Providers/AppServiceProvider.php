<?php

namespace App\Providers;

use App\Models\Grievance;
use App\Observers\GrievanceObserver;
use App\Observers\GrievanceAssignmentObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        if (config('app.env') === 'production' || config('app.env') === 'staging' || config('app.env') === 'testing') {
            URL::forceScheme('https');
        }

        // Register model observers
        Grievance::observe(GrievanceObserver::class);
        Grievance::observe(GrievanceAssignmentObserver::class);
    }
}
