<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ban extends Model
{
    use HasUuids;

    protected $fillable = ['workspace_id', 'chat_user_id', 'banned_by', 'reason', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function chatUser(): BelongsTo
    {
        return $this->belongsTo(ChatUser::class);
    }

    public function bannedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'banned_by');
    }

    public function isActive(): bool
    {
        return is_null($this->expires_at) || $this->expires_at->isFuture();
    }
}
