<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['workspace_id', 'blocker_id', 'blocked_id', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(ChatUser::class, 'blocker_id');
    }

    public function blocked(): BelongsTo
    {
        return $this->belongsTo(ChatUser::class, 'blocked_id');
    }
}
