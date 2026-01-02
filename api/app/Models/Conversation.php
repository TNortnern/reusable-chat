<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use HasUuids;

    protected $fillable = ['workspace_id', 'type', 'name', 'created_by'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(ChatUser::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(ChatUser::class, 'participants')
            ->withPivot(['role', 'joined_at', 'last_read_at', 'muted_until']);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage(): HasOne
    {
        // Use created_at instead of latestOfMany() since PostgreSQL doesn't support MAX(uuid)
        return $this->hasOne(Message::class)->latestOfMany('created_at');
    }
}
