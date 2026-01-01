<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Workspace extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'plan', 'owner_id'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(WorkspaceSettings::class);
    }

    public function theme(): HasOne
    {
        return $this->hasOne(WorkspaceTheme::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function chatUsers(): HasMany
    {
        return $this->hasMany(ChatUser::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function bans(): HasMany
    {
        return $this->hasMany(Ban::class);
    }
}
