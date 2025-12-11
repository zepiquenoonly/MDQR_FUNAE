<?php

namespace App\Providers;

use App\Events\GrievanceAutoAssigned;
use App\Events\ReportGenerated;
use App\Listeners\NotifyTechnicianOfAssignment;
use App\Listeners\SendReportNotification;
use App\Models\Grievance;
use App\Models\Report;
use App\Models\DepartmentIndicator;
use App\Observers\GrievanceObserver;
use App\Observers\GrievanceAssignmentObserver;
use App\Observers\ReportObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register report generation service
        $this->app->singleton('report.generator', function ($app) {
            return new \App\Services\ReportGeneratorService();
        });
        
        // Register indicator calculation service
        $this->app->singleton('indicator.calculator', function ($app) {
            return new \App\Services\IndicatorCalculationService();
        });
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
        //Report::observe(ReportObserver::class);

        // Register event listeners
        Event::listen(
            GrievanceAutoAssigned::class,
            NotifyTechnicianOfAssignment::class
        );
        
        Event::listen(
            ReportGenerated::class,
            SendReportNotification::class
        );
        

        // Share Inertia data
        Inertia::share([
            'app' => [
                'name' => config('app.name'),
                'version' => '1.0.0',
                'environment' => config('app.env'),
            ],
            'auth' => fn () => auth()->check() ? [
                'user' => auth()->user()->only(['id', 'name', 'email', 'role']),
                'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
                'roles' => auth()->user()->getRoleNames(),
            ] : null,
            'indicators' => fn () => [
        'routes' => [
            'dashboard' => route('dashboard.indicadores'),
            //'generate_report' => route('gestor.dashboard.indicadores.gerar-relatorio'),
            //'export' => route('gestor.dashboard.indicadores.export'), 
        ],
        'categories' => [
            'performance' => 'Performance',
            'satisfaction' => 'Satisfação',
            'efficiency' => 'Eficiência',
            'quality' => 'Qualidade',
            'volume' => 'Volume',
            'responsiveness' => 'Responsividade',
            'compliance' => 'Conformidade',
        ],
        'time_ranges' => [
            'week' => 'Última Semana',
            'month' => 'Último Mês',
            'quarter' => 'Último Trimestre',
            'year' => 'Último Ano',
        ]
    ],
            'reports' => fn () => [
                'formats' => [
                    'pdf' => 'PDF',
                    'excel' => 'Excel',
                    'html' => 'HTML'
                ],
                'types' => [
                    'monthly' => 'Mensal',
                    'quarterly' => 'Trimestral',
                    'annual' => 'Anual',
                    'custom' => 'Personalizado'
                ],
                'sections' => [
                    'summary' => 'Resumo',
                    'indicators' => 'Indicadores',
                    'technicians' => 'Técnicos',
                    'categories' => 'Categorias',
                    'timeline' => 'Timeline'
                ]
            ],
            'flash' => fn () => [
                'success' => session('success'),
                'error' => session('error'),
                'warning' => session('warning'),
                'info' => session('info'),
            ],
        ]);
    }
}