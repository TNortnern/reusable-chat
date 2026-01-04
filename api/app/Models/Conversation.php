<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['workspace_id', 'type', 'name', 'created_by', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

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
        // Avoid latestOfMany() as PostgreSQL doesn't support MAX(uuid)
        // Use orderBy with limit instead
        return $this->hasOne(Message::class)->orderByDesc('created_at');
    }
}
