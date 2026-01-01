<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasUuids;

    protected $fillable = ['message_id', 'filename', 'mime_type', 'size_bytes', 'url'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
