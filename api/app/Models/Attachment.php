<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'conversation_id',
        'message_id',
        'chat_user_id',
        'name',
        'type',
        'path',
        'size',
        // Legacy fields
        'filename',
        'mime_type',
        'size_bytes',
        'url',
    ];

    protected $appends = ['computed_url'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function chatUser(): BelongsTo
    {
        return $this->belongsTo(ChatUser::class);
    }

    // Accessor for URL - use new path if available, fallback to legacy url
    public function getComputedUrlAttribute(): string
    {
        if ($this->path) {
            $disk = env('BUNNY_STORAGE_ENABLED') ? 'bunny' : 'public';
            return Storage::disk($disk)->url($this->path);
        }
        return $this->attributes['url'] ?? '';
    }

    // Accessor for name - use new name if available, fallback to legacy filename
    public function getNameAttribute(): ?string
    {
        return $this->attributes['name'] ?? $this->attributes['filename'] ?? null;
    }

    // Accessor for type - use new type if available, fallback to legacy mime_type
    public function getTypeAttribute(): ?string
    {
        return $this->attributes['type'] ?? $this->attributes['mime_type'] ?? null;
    }

    // Accessor for size - use new size if available, fallback to legacy size_bytes
    public function getSizeAttribute(): ?int
    {
        return $this->attributes['size'] ?? $this->attributes['size_bytes'] ?? null;
    }

    // Override url accessor to use computed_url
    public function getUrlAttribute(): string
    {
        return $this->computed_url;
    }
}
