<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceTheme extends Model
{
    protected $table = 'workspace_themes';
    protected $primaryKey = 'workspace_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workspace_id', 'preset', 'primary_color', 'background_color',
        'font_family', 'logo_url', 'position', 'custom_css', 'dark_mode_enabled',
    ];

    protected $casts = ['dark_mode_enabled' => 'boolean'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
