<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'data_aprovacao',
        'data_inicio',
        'data_inspecao',
        'data_finalizacao',
        'data_inauguracao'
    ];

    protected $casts = [
        'data_aprovacao' => 'date',
        'data_inicio' => 'date',
        'data_inspecao' => 'date',
        'data_finalizacao' => 'date',
        'data_inauguracao' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}