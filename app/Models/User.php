<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * User model for the GRM (Gestão de Reclamações) system.
 *
 * This model represents users in the system with role-based access control.
 * Users can have different roles: Utente, Técnico, Gestor, or PCA.
 *
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'province',
        'district',
        'neighborhood',
        'street',
        'workload_capacity',
        'current_workload',
        'is_available',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_available' => 'boolean',
            'workload_capacity' => 'integer',
            'current_workload' => 'integer',
        ];
    }

    /**
     * Get the user's primary role name.
     *
     * @return string|null
     */
    public function getRoleName(): ?string
    {
        return $this->getRoleNames()->first();
    }

    /**
     * Check if user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Get the grievances submitted by this user.
     */
    public function grievances(): HasMany
    {
        return $this->hasMany(Grievance::class);
    }

    /**
     * Get the grievances assigned to this user.
     */
    public function assignedGrievances(): HasMany
    {
        return $this->hasMany(Grievance::class, 'assigned_to');
    }

    /**
     * Get the grievances resolved by this user.
     */
    public function resolvedGrievances(): HasMany
    {
        return $this->hasMany(Grievance::class, 'resolved_by');
    }

    /**
     * Get the attachments uploaded by this user.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    /**
     * Get the specializations for this user.
     */
    public function specializations(): HasMany
    {
        return $this->hasMany(UserSpecialization::class);
    }

    /**
     * Calculate current workload based on assigned grievances.
     */
    public function calculateWorkload(): int
    {
        return $this->assignedGrievances()
            ->whereIn('status', ['submitted', 'under_review', 'assigned', 'in_progress'])
            ->get()
            ->sum(function ($grievance) {
                return match($grievance->priority) {
                    'urgent' => 4,
                    'high' => 3,
                    'medium' => 2,
                    'low' => 1,
                    default => 2,
                };
            });
    }

    /**
     * Update current workload.
     */
    public function updateWorkload(): void
    {
        $this->current_workload = $this->calculateWorkload();
        $this->save();
    }

    /**
     * Check if user can accept more grievances.
     */
    public function canAcceptGrievance(int $weight = 2): bool
    {
        return $this->is_available && 
               ($this->current_workload + $weight) <= $this->workload_capacity;
    }

    /**
     * Check if user has specialization in a category.
     */
    public function hasSpecialization(string $category): bool
    {
        return $this->specializations()
            ->where('category', $category)
            ->exists();
    }

    /**
     * Get proficiency level for a category.
     */
    public function getProficiencyLevel(string $category): int
    {
        $specialization = $this->specializations()
            ->where('category', $category)
            ->first();
        
        return $specialization ? $specialization->proficiency_level : 0;
    }
}
