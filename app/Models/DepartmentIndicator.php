<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;


class DepartmentIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'calculation_formula',
        'measurement_unit',
        'target_value',
        'min_value',
        'max_value',
        'is_active',
        'display_order',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'target_value' => 'decimal:2',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function records(): HasMany
{
    return $this->hasMany(IndicatorRecord::class, 'indicator_id');
}

    public function getLatestValueAttribute()
    {
        return $this->records()->latest('record_date')->first()?->value;
    }

    public function getTrendAttribute()
    {
        $latest = $this->records()->latest('record_date')->first();
        $previous = $this->records()
            ->where('record_date', '<', $latest?->record_date)
            ->latest('record_date')
            ->first();

        if (!$latest || !$previous) {
            return null;
        }

        if ($previous->value == 0) {
            return 100; // Growth from 0
        }

        return (($latest->value - $previous->value) / $previous->value) * 100;
    }

    public function calculateValueForDate($date): float
    {
        // Implement calculation based on formula
        $formula = $this->calculation_formula;
        
        // Example formulas:
        // total_complaints, resolved_rate, avg_resolution_time, satisfaction_rate
        return match($formula) {
            'total_complaints' => Grievance::whereDate('created_at', $date)->count(),
            'resolved_rate' => $this->calculateResolvedRate($date),
            'avg_resolution_time' => $this->calculateAvgResolutionTime($date),
            'satisfaction_rate' => $this->calculateSatisfactionRate($date),
            default => 0
        };
    }

    private function calculateResolvedRate($date): float
    {
        $total = Grievance::whereDate('created_at', $date)->count();
        $resolved = Grievance::whereDate('resolved_at', $date)->count();
        
        return $total > 0 ? ($resolved / $total) * 100 : 0;
    }

    private function calculateAvgResolutionTime($date): float
    {
        $grievances = Grievance::whereDate('resolved_at', $date)
            ->whereNotNull('resolved_at')
            ->whereNotNull('created_at')
            ->get();

        if ($grievances->isEmpty()) {
            return 0;
        }

        $totalDays = $grievances->sum(function ($grievance) {
            return $grievance->created_at->diffInDays($grievance->resolved_at);
        });

        return $totalDays / $grievances->count();
    }

    private function calculateSatisfactionRate($date): float
    {
        // This would require a feedback system
        return 0; // Placeholder
    }
}