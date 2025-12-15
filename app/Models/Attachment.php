<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $grievance_id
 * @property string $original_filename
 * @property string $filename
 * @property string $path
 * @property string $mime_type
 * @property int $size
 * @property string|null $file_hash
 * @property bool $is_encrypted
 * @property array|null $metadata
 * @property int|null $uploaded_by
 * @property \Carbon\Carbon $uploaded_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grievance_id',
        'original_filename',
        'filename',
        'path',
        'mime_type',
        'size',
        'file_hash',
        'is_encrypted',
        'metadata',
        'uploaded_by',
        'uploaded_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
        'human_readable_size',
        'extension',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'is_encrypted' => 'boolean',
            'metadata' => 'array',
            'size' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attachment) {
            if (empty($attachment->uploaded_at)) {
                $attachment->uploaded_at = now();
            }
        });

        static::deleting(function ($attachment) {
            // Delete the actual file when the attachment record is deleted
            if ($attachment->path) {
                // Check if file is in public/uploads
                if (str_starts_with($attachment->path, '/uploads') || str_starts_with($attachment->path, 'uploads')) {
                    $fullPath = public_path($attachment->path);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                } elseif (Storage::exists($attachment->path)) {
                    Storage::delete($attachment->path);
                }
            }
        });
    }

    /**
     * Get the grievance that owns the attachment.
     */
    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file URL for accessing the attachment.
     */
    public function getUrlAttribute(): string
    {
        $path = $this->path;

        // Remove leading slash
        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }

        // If path starts with uploads/, return direct URL
        if (str_starts_with($path, 'uploads/')) {
            return url($path);
        }

        // If path starts with storage/uploads/, fix it to uploads/
        if (str_starts_with($path, 'storage/uploads/')) {
            return url(str_replace('storage/uploads/', 'uploads/', $path));
        }

        return Storage::url($this->path);
    }

    /**
     * Get the file size in human readable format.
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the file is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Verify file integrity using hash.
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->file_hash || !Storage::exists($this->path)) {
            return false;
        }

        $currentHash = hash_file('sha256', Storage::path($this->path));
        return hash_equals($this->file_hash, $currentHash);
    }

    /**
     * Get the file extension from MIME type.
     */
    public function getExtensionAttribute(): string
    {
        return match ($this->mime_type) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
            default => 'bin',
        };
    }

    /**
     * Scope for filtering by file type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('mime_type', 'like', $type . '/%');
    }

    /**
     * Scope for filtering images.
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope for filtering documents.
     */
    public function scopeDocuments($query)
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
