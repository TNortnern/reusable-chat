<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSettings extends Model
{
    protected $table = 'workspace_settings';
    protected $primaryKey = 'workspace_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workspace_id', 'read_receipts_enabled', 'online_status_enabled',
        'typing_indicators_enabled', 'file_size_limit_mb', 'rate_limit_per_minute',
        'webhook_url', 'webhook_secret',
    ];

    protected $casts = [
        'read_receipts_enabled' => 'boolean',
        'online_status_enabled' => 'boolean',
        'typing_indicators_enabled' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
