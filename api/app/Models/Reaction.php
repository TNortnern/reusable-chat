<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['message_id', 'chat_user_id', 'emoji', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function chatUser(): BelongsTo
    {
        return $this->belongsTo(ChatUser::class);
    }
}
