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
}
