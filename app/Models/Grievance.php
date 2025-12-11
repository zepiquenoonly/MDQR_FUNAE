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
 * @property int|null $project_id
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
 * @property bool $escalated
 * @property \Carbon\Carbon|null $escalated_at
 * @property int|null $escalated_by
 * @property string|null $escalation_reason
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
        'project_id',
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
        'escalated',
        'escalated_at',
        'escalated_by',
        'escalation_reason',
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
            'escalated_at' => 'datetime',
            'is_anonymous' => 'boolean',
            'escalated' => 'boolean',
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
     * Scope for filtering grievances escalated to director.
     */
    public function scopeEscalatedToDirector($query)
    {
        return $query->where('escalated', true)
            ->orWhere(function ($q) {
                $q->whereJsonContains('metadata->is_escalated_to_director', true);
            });
    }

public function hasDirectorIntervention(): bool
    {
        // Check for director updates
        $hasDirectorUpdate = $this->updates()
            ->whereHas('user', function ($query) {
                $query->role('Director');
            })
            ->orWhereIn('action_type', [
                'director_comment',
                'director_validation_approved',
                'director_validation_rejected',
                'director_validation_needs_revision'
            ])
            ->exists();
        
        // Check for director validation in metadata
        $hasDirectorValidation = isset($this->metadata['director_validation']);
        
        // Check if escalated to director
        $isEscalatedToDirector = $this->isEscalated();
        
        // Check for director comments in updates
        $hasDirectorComment = $this->updates()
            ->where('action_type', 'comment_added')
            ->whereHas('user', function ($query) {
                $query->role('Director');
            })
            ->exists();
        
        return $hasDirectorUpdate || $hasDirectorValidation || $isEscalatedToDirector || $hasDirectorComment;
    }

    /**
     * Get all director interventions for this grievance
     */
    public function getDirectorInterventions(): array
    {
        $interventions = [];
        
        // Get director updates
        $directorUpdates = $this->updates()
            ->where(function ($query) {
                $query->whereHas('user', function ($q) {
                        $q->role('Director');
                    })
                    ->orWhereIn('action_type', [
                        'director_comment',
                        'director_validation_approved',
                        'director_validation_rejected',
                        'director_validation_needs_revision'
                    ]);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($directorUpdates as $update) {
            $interventions[] = [
                'type' => 'update',
                'id' => $update->id,
                'action_type' => $update->action_type,
                'description' => $update->description,
                'comment' => $update->comment,
                'created_at' => $update->created_at,
                'user' => $update->user ? [
                    'name' => $update->user->name,
                    'role' => 'Director',
                ] : null,
            ];
        }
        
        // Add director validation if exists
        if (isset($this->metadata['director_validation'])) {
            $validation = $this->metadata['director_validation'];
            $interventions[] = [
                'type' => 'validation',
                'status' => $validation['status'] ?? null,
                'comment' => $validation['comment'] ?? null,
                'validated_by' => $validation['validated_by_name'] ?? null,
                'validated_at' => $validation['validated_at'] ?? null,
            ];
        }
        
        // Add escalation info if escalated
        if ($this->escalated) {
            $interventions[] = [
                'type' => 'escalation',
                'escalated_at' => $this->escalated_at,
                'escalated_by' => $this->escalatedBy ? [
                    'name' => $this->escalatedBy->name,
                    'role' => 'Gestor',
                ] : null,
                'reason' => $this->escalation_reason,
            ];
        }
        
        return $interventions;
    }

    /**
     * Count director comments
     */
    public function countDirectorComments(): int
    {
        return $this->updates()
            ->where(function ($query) {
                $query->where('action_type', 'comment_added')
                    ->orWhere('action_type', 'director_comment');
            })
            ->whereHas('user', function ($query) {
                $query->role('Director');
            })
            ->count();
    }


    /**
     * Scope for filtering high specificity cases.
     */
    public function scopeHighSpecificity($query)
    {
        return $query->where('priority', 'high')
            ->orWhere('priority', 'critical')
            ->orWhere('escalated', true)
            ->orWhere(function ($q) {
                $q->whereJsonContains('metadata->is_special_case', true);
            });
    }

    /**
     * Get the user that owns the grievance (for identified complaints).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project associated with this grievance.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user assigned to handle this grievance.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who escalated this grievance.
     */
    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
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
     * Check if the grievance is escalated to director.
     */
    public function isEscalated(): bool
    {
        return $this->escalated === true || 
               ($this->metadata && isset($this->metadata['is_escalated_to_director']) && 
                $this->metadata['is_escalated_to_director'] === true);
    }

    /**
     * Tipos de grievance
     */
    public const TYPE_GRIEVANCE = 'grievance';    // Queixa
    public const TYPE_COMPLAINT = 'complaint';     // Reclamação
    public const TYPE_SUGGESTION = 'suggestion';   // Sugestão

    /**
     * Status válidos para grievances
     */
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CLOSED = 'closed';

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
            self::STATUS_SUBMITTED => 'Submetida',
            self::STATUS_UNDER_REVIEW => 'Em Análise',
            self::STATUS_ASSIGNED => 'Atribuída',
            self::STATUS_IN_PROGRESS => 'Em Andamento',
            self::STATUS_PENDING_APPROVAL => 'Pendente de Aprovação',
            self::STATUS_RESOLVED => 'Resolvida',
            self::STATUS_REJECTED => 'Rejeitada',
            self::STATUS_CLOSED => 'Fechada',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get escalated label in Portuguese.
     */
    public function getEscalatedLabelAttribute(): string
    {
        return $this->escalated ? 'Escalada para Director' : 'Não Escalada';
    }

    /**
     * Get the display name for the grievance.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return $this->attributes['contact_name'] ?? 'Reclamação Anónima';
        }

        return $this->user?->name ?: 'Utente';
    }

    /**
     * Get the effective contact email for the grievance.
     */
    public function getEffectiveEmailAttribute(): ?string
    {
        // Use attributes array to avoid recursion
        return $this->is_anonymous ? $this->attributes['contact_email'] ?? null : $this->user?->email;
    }

    /**
     * Mark grievance as escalated to director.
     */
    public function markAsEscalated(int $escalatedBy, string $reason): self
    {
        $this->update([
            'escalated' => true,
            'escalated_at' => now(),
            'escalated_by' => $escalatedBy,
            'escalation_reason' => $reason,
            'priority' => 'high', // Aumentar prioridade quando escalado
            'metadata' => array_merge(
                $this->metadata ?? [],
                [
                    'is_escalated_to_director' => true,
                    'escalation_details' => [
                        'escalated_at' => now()->toIso8601String(),
                        'escalated_by' => $escalatedBy,
                        'escalation_reason' => $reason,
                    ]
                ]
            )
        ]);

        return $this;
    }

    /**
     * Mark grievance as not escalated.
     */
    public function markAsNotEscalated(): self
    {
        $this->update([
            'escalated' => false,
            'escalated_at' => null,
            'escalated_by' => null,
            'escalation_reason' => null,
            'metadata' => array_merge(
                $this->metadata ?? [],
                ['is_escalated_to_director' => false]
            )
        ]);

        return $this;
    }

    public function directorComments()
{
    return $this->updates()
        ->whereIn('action_type', [
            'director_comment',
            'director_validation_approved',
            'director_validation_rejected',
            'director_validation_needs_revision'
        ])
        ->orderBy('created_at', 'desc');
}
}