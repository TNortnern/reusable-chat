<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PublicKey extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'key',
        'name',
        'allowed_origins',
        'settings',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'allowed_origins' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['workspace_id'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public static function generateKey(): string
    {
        return 'pk_' . Str::random(32);
    }
}
