<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSpecialization extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'proficiency_level',
    ];

    protected $casts = [
        'proficiency_level' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
