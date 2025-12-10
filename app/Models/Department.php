<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $fillable = [
        'name',
        'description',
        'manager_id',
    ];

    /**
     * Get the manager that owns the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the users (technicians and managers) in this department
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the projects in this department
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
