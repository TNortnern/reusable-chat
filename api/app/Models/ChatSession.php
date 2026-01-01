<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatSession extends Model
{
    use HasUuids;

    protected $fillable = ['workspace_id', 'chat_user_id', 'token', 'context', 'expires_at'];
    protected $casts = ['context' => 'array', 'expires_at' => 'datetime'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function chatUser(): BelongsTo
    {
        return $this->belongsTo(ChatUser::class);
    }
}
