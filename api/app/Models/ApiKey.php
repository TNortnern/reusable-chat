<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    use HasUuids;

    protected $fillable = ['workspace_id', 'name', 'key_hash', 'key_prefix', 'last_used_at', 'revoked_at'];
    protected $casts = ['last_used_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
