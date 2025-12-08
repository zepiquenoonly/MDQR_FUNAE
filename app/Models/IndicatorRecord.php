<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_id', // Este é o campo que existe
        'record_date',
        'value',
        'breakdown',
        'notes'
    ];

    protected $casts = [
        'breakdown' => 'array',
        'value' => 'decimal:4',
        'record_date' => 'date'
    ];

    public function indicator(): BelongsTo
    {
        // CORREÇÃO: Use 'indicator_id' (igual ao fillable)
        return $this->belongsTo(DepartmentIndicator::class, 'indicator_id');
    }

    public function getFormattedValueAttribute(): string
    {
        if (!$this->indicator) {
            return number_format($this->value, 2);
        }
        
        $unit = $this->indicator->measurement_unit;
        $value = $this->value;

        return match($unit) {
            'percentage' => number_format($value, 1) . '%',
            'days' => number_format($value, 1) . ' dias',
            'count' => number_format($value, 0),
            'rating' => number_format($value, 1) . '/5',
            default => number_format($value, 2) . ' ' . $unit
        };
    }

    
}