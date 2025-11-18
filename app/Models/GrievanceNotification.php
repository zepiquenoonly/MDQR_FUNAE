<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrievanceNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'grievance_id',
        'user_id',
        'type',
        'channel',
        'recipient_email',
        'recipient_phone',
        'subject',
        'message',
        'data',
        'status',
        'sent_at',
        'error_message',
        'retry_count',
        'opened',
        'opened_at',
        'clicked',
        'clicked_at',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'opened' => 'boolean',
        'clicked' => 'boolean',
        'retry_count' => 'integer',
    ];

    /**
     * Tipos de notificação disponíveis
     */
    public const TYPE_GRIEVANCE_CREATED = 'grievance_created';
    public const TYPE_STATUS_CHANGED = 'status_changed';
    public const TYPE_ASSIGNED = 'assigned';
    public const TYPE_COMMENT_ADDED = 'comment_added';
    public const TYPE_RESOLVED = 'resolved';
    public const TYPE_REJECTED = 'rejected';

    /**
     * Canais de notificação
     */
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_SYSTEM = 'system';

    /**
     * Status de envio
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BOUNCED = 'bounced';

    /**
     * Relação com a reclamação
     */
    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }

    /**
     * Relação com o usuário (se houver)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para notificações pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para notificações enviadas
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope para notificações falhadas
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope para notificações por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para notificações por canal
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope para notificações que podem ser reenviadas
     */
    public function scopeRetryable($query, int $maxRetries = 3)
    {
        return $query->where('status', self::STATUS_FAILED)
            ->where('retry_count', '<', $maxRetries);
    }

    /**
     * Marcar como enviada
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Marcar como falhada
     */
    public function markAsFailed(string $errorMessage): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Marcar como bounced (email inválido)
     */
    public function markAsBounced(string $errorMessage): bool
    {
        return $this->update([
            'status' => self::STATUS_BOUNCED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Marcar como aberta
     */
    public function markAsOpened(): bool
    {
        if (!$this->opened) {
            return $this->update([
                'opened' => true,
                'opened_at' => now(),
            ]);
        }
        return true;
    }

    /**
     * Marcar como clicada
     */
    public function markAsClicked(): bool
    {
        $updates = ['clicked' => true];
        
        if (!$this->clicked) {
            $updates['clicked_at'] = now();
        }
        
        if (!$this->opened) {
            $updates['opened'] = true;
            $updates['opened_at'] = now();
        }
        
        return $this->update($updates);
    }

    /**
     * Verificar se pode ser reenviada
     */
    public function canRetry(int $maxRetries = 3): bool
    {
        return $this->status === self::STATUS_FAILED 
            && $this->retry_count < $maxRetries;
    }

    /**
     * Obter label do tipo de notificação
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_GRIEVANCE_CREATED => 'Reclamação Criada',
            self::TYPE_STATUS_CHANGED => 'Status Alterado',
            self::TYPE_ASSIGNED => 'Atribuída',
            self::TYPE_COMMENT_ADDED => 'Comentário Adicionado',
            self::TYPE_RESOLVED => 'Resolvida',
            self::TYPE_REJECTED => 'Rejeitada',
            default => 'Desconhecido',
        };
    }

    /**
     * Obter label do status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_SENT => 'Enviada',
            self::STATUS_FAILED => 'Falhada',
            self::STATUS_BOUNCED => 'Devolvida',
            default => 'Desconhecido',
        };
    }

    /**
     * Obter label do canal
     */
    public function getChannelLabelAttribute(): string
    {
        return match($this->channel) {
            self::CHANNEL_EMAIL => 'E-mail',
            self::CHANNEL_SMS => 'SMS',
            self::CHANNEL_SYSTEM => 'Sistema',
            default => 'Desconhecido',
        };
    }
}
