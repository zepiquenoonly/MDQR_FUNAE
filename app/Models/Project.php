<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_url', // Alterado para image_url
        'provincia',
        'distrito',
        'bairro',
        'category',
        'data_criacao'
    ];

    // Accessor para garantir URL completa se necessário
    public function getImageUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return asset('storage/' . $value);
        }
        return $value;
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }

    public function finance()
    {
        return $this->hasOne(Finance::class);
    }

    public function deadline()
    {
        return $this->hasOne(Deadline::class);
    }

    /**
     * Get the technicians assigned to this project.
     */
    public function technicians()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withTimestamps();
    }

    /**
     * Get the grievances related to this project.
     */
    public function grievances()
    {
        return $this->hasMany(Grievance::class);
    }
}