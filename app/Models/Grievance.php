<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $reference_number
 * @property string $description
 * @property string $category
 * @property string|null $subcategory
 * @property string|null $contact_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property string|null $province
 * @property string|null $district
 * @property string|null $location_details
 * @property string $status
 * @property string $priority
 * @property int|null $assigned_to
 * @property \Carbon\Carbon|null $assigned_at
 * @property string|null $resolution_notes
 * @property \Carbon\Carbon|null $resolved_at
 * @property int|null $resolved_by
 * @property bool $is_anonymous
 * @property array|null $metadata
 * @property \Carbon\Carbon $submitted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Grievance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'reference_number',
        'type',
        'description',
        'category',
        'subcategory',
        'contact_name',
        'contact_email',
        'contact_phone',
        'province',
        'district',
        'location_details',
        'status',
        'priority',
        'assigned_to',
        'assigned_at',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
        'is_anonymous',
        'metadata',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
            'submitted_at' => 'datetime',
            'is_anonymous' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($grievance) {
            if (empty($grievance->reference_number)) {
                $grievance->reference_number = static::generateReferenceNumber();
            }
            if (empty($grievance->submitted_at)) {
                $grievance->submitted_at = now();
            }
        });
    }

    /**
     * Generate a unique reference number for the grievance.
     *
     * @return string
     */
    public static function generateReferenceNumber(): string
    {
        do {
            $reference = 'GRM-' . date('Y') . '-' . strtoupper(Str::random(8));
        } while (static::where('reference_number', $reference)->exists());

        return $reference;
    }

    /**
     * Get the user that owns the grievance (for identified complaints).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to handle this grievance.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who resolved this grievance.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the attachments for this grievance.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Get the updates/history for this grievance.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(GrievanceUpdate::class);
    }

    /**
     * Get public updates only.
     */
    public function publicUpdates(): HasMany
    {
        return $this->hasMany(GrievanceUpdate::class)->where('is_public', true);
    }

    /**
     * Get notifications for this grievance.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(GrievanceNotification::class);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for filtering assigned grievances.
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to');
    }

    /**
     * Scope for filtering unassigned grievances.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope for filtering anonymous grievances.
     */
    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    /**
     * Scope for filtering identified grievances.
     */
    public function scopeIdentified($query)
    {
        return $query->where('is_anonymous', false);
    }

    /**
     * Check if the grievance is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if the grievance is in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['in_progress', 'assigned', 'under_review', 'pending_approval']);
    }

    /**
     * Tipos de grievance
     */
    public const TYPE_GRIEVANCE = 'grievance';    // Queixa
    public const TYPE_COMPLAINT = 'complaint';     // Reclamação
    public const TYPE_SUGGESTION = 'suggestion';   // Sugestão

    /**
     * Get type label in Portuguese.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_GRIEVANCE => 'Queixa',
            self::TYPE_COMPLAINT => 'Reclamação',
            self::TYPE_SUGGESTION => 'Sugestão',
            default => 'Reclamação',
        };
    }

    /**
     * Get type label in lowercase for use in sentences.
     */
    public function getTypeLabelLowercaseAttribute(): string
    {
        return match($this->type) {
            self::TYPE_GRIEVANCE => 'queixa',
            self::TYPE_COMPLAINT => 'reclamação',
            self::TYPE_SUGGESTION => 'sugestão',
            default => 'reclamação',
        };
    }

    /**
     * Get status label in Portuguese.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'submitted' => 'Submetida',
            'under_review' => 'Em Análise',
            'assigned' => 'Atribuída',
            'in_progress' => 'Em Andamento',
            'pending_approval' => 'Pendente de Aprovação',
            'resolved' => 'Resolvida',
            'rejected' => 'Rejeitada',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the display name for the grievance.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return $this->contact_name ?: 'Reclamação Anónima';
        }

        return $this->user?->name ?: 'Utente';
    }

    /**
     * Get the contact email for the grievance.
     */
    public function getContactEmailAttribute(): ?string
    {
        // Use attributes array to avoid recursion
        return $this->is_anonymous ? $this->attributes['contact_email'] ?? null : $this->user?->email;
    }
}
