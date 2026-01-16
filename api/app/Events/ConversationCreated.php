<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation
    ) {
        // Eager load relationships
        $this->conversation->load(['creator', 'participants']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('workspace.' . $this->conversation->workspace_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->conversation->id,
            'type' => $this->conversation->type,
            'name' => $this->conversation->name,
            'workspace_id' => $this->conversation->workspace_id,
            'created_by' => [
                'id' => $this->conversation->creator->id,
                'name' => $this->conversation->creator->name,
                'email' => $this->conversation->creator->email,
            ],
            'participants' => $this->conversation->participants->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'role' => $p->pivot->role,
            ]),
            'created_at' => $this->conversation->created_at->toIso8601String(),
        ];
    }
}
