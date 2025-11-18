<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $grievance_id
 * @property int|null $user_id
 * @property string $action_type
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string|null $description
 * @property string|null $comment
 * @property array|null $metadata
 * @property bool $is_public
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class GrievanceUpdate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grievance_id',
        'user_id',
        'action_type',
        'old_value',
        'new_value',
        'description',
        'comment',
        'metadata',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_public' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the grievance that owns this update.
     */
    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }

    /**
     * Get the user who made this update.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for public updates only.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for specific action types.
     */
    public function scopeOfType($query, string|array $types)
    {
        if (is_array($types)) {
            return $query->whereIn('action_type', $types);
        }
        return $query->where('action_type', $types);
    }

    /**
     * Get formatted action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action_type) {
            'created' => 'Reclamação Criada',
            'status_changed' => 'Estado Alterado',
            'assigned' => 'Atribuída',
            'reassigned' => 'Reatribuída',
            'comment_added' => 'Comentário Adicionado',
            'priority_changed' => 'Prioridade Alterada',
            'attachment_added' => 'Anexo Adicionado',
            'resolved' => 'Resolvida',
            'rejected' => 'Rejeitada',
            'reopened' => 'Reaberta',
            default => ucfirst($this->action_type),
        };
    }

    /**
     * Get the formatted description.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        // Auto-generate description based on action type
        return match($this->action_type) {
            'status_changed' => "Estado alterado de '{$this->getStatusLabel($this->old_value)}' para '{$this->getStatusLabel($this->new_value)}'",
            'assigned' => "Reclamação atribuída",
            'reassigned' => "Reclamação reatribuída",
            'priority_changed' => "Prioridade alterada de '{$this->old_value}' para '{$this->new_value}'",
            'comment_added' => "Novo comentário adicionado",
            'attachment_added' => "Novo anexo adicionado",
            'resolved' => "Reclamação marcada como resolvida",
            'rejected' => "Reclamação rejeitada",
            'reopened' => "Reclamação reaberta",
            default => $this->action_label,
        };
    }

    /**
     * Get status label in Portuguese.
     */
    private function getStatusLabel(?string $status): string
    {
        if (!$status) return '';

        return match($status) {
            'submitted' => 'Submetida',
            'under_review' => 'Em Análise',
            'assigned' => 'Atribuída',
            'in_progress' => 'Em Andamento',
            'pending_approval' => 'Pendente de Aprovação',
            'resolved' => 'Resolvida',
            'rejected' => 'Rejeitada',
            default => ucfirst($status),
        };
    }

    /**
     * Create a new update record.
     */
    public static function log(
        int $grievanceId,
        string $actionType,
        ?int $userId = null,
        ?string $description = null,
        ?string $comment = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?array $metadata = null,
        bool $isPublic = true
    ): self {
        return self::create([
            'grievance_id' => $grievanceId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'description' => $description,
            'comment' => $comment,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'metadata' => $metadata,
            'is_public' => $isPublic,
        ]);
    }
}
