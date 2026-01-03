<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatUser extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id', 'external_id', 'name', 'email',
        'avatar_url', 'metadata', 'is_anonymous', 'last_seen_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_anonymous' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'participants')
            ->withPivot(['role', 'joined_at', 'last_read_at', 'muted_until']);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function ban(): HasOne
    {
        return $this->hasOne(Ban::class);
    }

    public function isBanned(): bool
    {
        $ban = $this->ban;
        return $ban && $ban->isActive();
    }
}
