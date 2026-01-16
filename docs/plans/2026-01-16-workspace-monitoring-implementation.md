# Workspace Monitoring & Email Notifications Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Enable tenants to monitor workspace activity via WebSocket and receive email notifications for missed messages.

**Architecture:** Two parallel systems: (1) Workspace-level WebSocket channel broadcasting message.created, message.deleted, conversation.created events with rich payloads, (2) Email notification system with customizable templates using email-gateway API.

**Tech Stack:** Laravel 11, Laravel Reverb (WebSocket), Laravel Queue, PostgreSQL, Email Gateway API

---

## Phase 1: Core WebSocket Infrastructure

### Task 1: Add Workspace Channel Authorization

**Files:**
- Modify: `api/routes/channels.php`
- Test: `api/tests/Feature/Broadcasting/WorkspaceChannelTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Feature\Broadcasting;

use App\Models\Admin;
use App\Models\ChatUser;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class WorkspaceChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_subscribe_to_their_workspace_channel(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = Admin::factory()->create();
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'admin_id' => $admin->id,
            'role' => 'owner',
        ]);

        $result = Broadcast::channel('workspace.' . $workspace->id, function ($user, $workspaceId) {
            if ($user instanceof Admin) {
                return $user->workspace_id === $workspaceId;
            }
            return false;
        })($admin, $workspace->id);

        $this->assertTrue($result);
    }

    public function test_admin_cannot_subscribe_to_other_workspace_channel(): void
    {
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();
        $admin = Admin::factory()->create();
        WorkspaceMember::create([
            'workspace_id' => $workspace1->id,
            'admin_id' => $admin->id,
            'role' => 'owner',
        ]);

        $result = Broadcast::channel('workspace.' . $workspace2->id, function ($user, $workspaceId) {
            if ($user instanceof Admin) {
                return $user->workspace_id === $workspaceId;
            }
            return false;
        })($admin, $workspace2->id);

        $this->assertFalse($result);
    }

    public function test_chat_user_cannot_subscribe_to_workspace_channel(): void
    {
        $workspace = Workspace::factory()->create();
        $chatUser = ChatUser::factory()->create(['workspace_id' => $workspace->id]);

        $result = Broadcast::channel('workspace.' . $workspace->id, function ($user, $workspaceId) {
            if ($user instanceof Admin) {
                return $user->workspace_id === $workspaceId;
            }
            return false;
        })($chatUser, $workspace->id);

        $this->assertFalse($result);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Feature/Broadcasting/WorkspaceChannelTest.php`
Expected: FAIL - channel not defined in routes/channels.php

**Step 3: Add workspace channel authorization**

Edit `api/routes/channels.php`, add after existing channels:

```php
// Workspace channel - only admins from that workspace can subscribe
Broadcast::channel('workspace.{workspaceId}', function ($user, string $workspaceId) {
    if ($user instanceof Admin) {
        // Verify admin belongs to this workspace
        return $user->workspace_id === $workspaceId;
    }

    // Only admins can subscribe to workspace channels
    // ChatUsers continue using conversation.{id} channels
    return false;
});
```

**Step 4: Run test to verify it passes**

Run: `cd api && php artisan test tests/Feature/Broadcasting/WorkspaceChannelTest.php`
Expected: PASS (3 tests)

**Step 5: Commit**

```bash
git add api/routes/channels.php api/tests/Feature/Broadcasting/WorkspaceChannelTest.php
git commit -m "feat: add workspace channel authorization for admin monitoring"
```

---

### Task 2: Create Realtime Token Endpoint

**Files:**
- Create: `api/app/Http/Controllers/Api/V1/WorkspaceController.php`
- Create: `api/app/Http/Requests/WorkspaceRealtimeTokenRequest.php`
- Modify: `api/routes/api.php`
- Test: `api/tests/Feature/Api/V1/WorkspaceRealtimeTokenTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Feature\Api\V1;

use App\Models\ApiKey;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceRealtimeTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_realtime_token_with_valid_api_key(): void
    {
        $workspace = Workspace::factory()->create();
        $apiKey = ApiKey::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Test Key',
        ]);

        $response = $this->postJson("/api/v1/workspaces/{$workspace->id}/realtime/token", [], [
            'X-API-Key' => $apiKey->key,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'expires_at',
                'workspace_id',
                'channels',
            ])
            ->assertJson([
                'workspace_id' => $workspace->id,
                'channels' => ["private-workspace.{$workspace->id}"],
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertNotEmpty($response->json('expires_at'));
    }

    public function test_cannot_generate_token_without_api_key(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->postJson("/api/v1/workspaces/{$workspace->id}/realtime/token");

        $response->assertUnauthorized();
    }

    public function test_cannot_generate_token_for_other_workspace(): void
    {
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();
        $apiKey = ApiKey::factory()->create([
            'workspace_id' => $workspace1->id,
        ]);

        $response = $this->postJson("/api/v1/workspaces/{$workspace2->id}/realtime/token", [], [
            'X-API-Key' => $apiKey->key,
        ]);

        $response->assertForbidden();
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Feature/Api/V1/WorkspaceRealtimeTokenTest.php`
Expected: FAIL - route not found

**Step 3: Create request validation class**

Create `api/app/Http/Requests/WorkspaceRealtimeTokenRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkspaceRealtimeTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Verify API key belongs to the workspace
        $workspace = $this->route('workspace');
        return $this->user()->workspace_id === $workspace;
    }

    public function rules(): array
    {
        return [];
    }
}
```

**Step 4: Create controller**

Create `api/app/Http/Controllers/Api/V1/WorkspaceController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceRealtimeTokenRequest;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class WorkspaceController extends Controller
{
    public function realtimeToken(WorkspaceRealtimeTokenRequest $request, Workspace $workspace): JsonResponse
    {
        // Create a temporary token for WebSocket authentication
        // Using Sanctum for simplicity (could be JWT in future)
        $apiKey = $request->user();

        // Create an admin instance for this API key to use with broadcasting
        $admin = new \App\Models\Admin([
            'id' => 'api-' . $apiKey->id,
            'workspace_id' => $workspace->id,
            'name' => $apiKey->name,
        ]);

        // Generate token (expires in 24 hours)
        $token = $admin->createToken('realtime-access', ['realtime:subscribe'], now()->addDay());

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at->toIso8601String(),
            'workspace_id' => $workspace->id,
            'channels' => ["private-workspace.{$workspace->id}"],
        ]);
    }
}
```

**Step 5: Add route**

Edit `api/routes/api.php`, add in the `/api/v1` group:

```php
Route::post('workspaces/{workspace}/realtime/token', [WorkspaceController::class, 'realtimeToken']);
```

**Step 6: Run test to verify it passes**

Run: `cd api && php artisan test tests/Feature/Api/V1/WorkspaceRealtimeTokenTest.php`
Expected: PASS (3 tests)

**Step 7: Commit**

```bash
git add api/app/Http/Controllers/Api/V1/WorkspaceController.php \
        api/app/Http/Requests/WorkspaceRealtimeTokenRequest.php \
        api/routes/api.php \
        api/tests/Feature/Api/V1/WorkspaceRealtimeTokenTest.php
git commit -m "feat: add realtime token endpoint for workspace monitoring"
```

---

### Task 3: Enrich MessageCreated Event with Workspace Channel

**Files:**
- Modify: `api/app/Events/MessageCreated.php`
- Test: `api/tests/Feature/Events/MessageCreatedEventTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Feature\Events;

use App\Events\MessageCreated;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessageCreatedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_created_broadcasts_to_conversation_and_workspace_channels(): void
    {
        $workspace = Workspace::factory()->create();
        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Test message',
        ]);

        $event = new MessageCreated($message);
        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertEquals('conversation.' . $conversation->id, $channels[0]->name);
        $this->assertEquals('workspace.' . $workspace->id, $channels[1]->name);
    }

    public function test_message_created_includes_rich_conversation_context(): void
    {
        $workspace = Workspace::factory()->create();
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'type' => '1on1',
            'name' => 'Support Chat',
        ]);
        $sender = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
        ]);

        $event = new MessageCreated($message);
        $data = $event->broadcastWith();

        $this->assertArrayHasKey('conversation', $data);
        $this->assertEquals($conversation->id, $data['conversation']['id']);
        $this->assertEquals('1on1', $data['conversation']['type']);
        $this->assertEquals('Support Chat', $data['conversation']['name']);
        $this->assertEquals($workspace->id, $data['conversation']['workspace_id']);
        $this->assertArrayHasKey('participant_count', $data['conversation']);
        $this->assertEquals($sender->email, $data['sender']['email']);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Feature/Events/MessageCreatedEventTest.php`
Expected: FAIL - workspace channel not included, conversation context missing

**Step 3: Modify MessageCreated event**

Edit `api/app/Events/MessageCreated.php`:

```php
<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message
    ) {
        // Eager load relationships for broadcasting
        $this->message->load(['conversation', 'sender', 'attachments']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
            new PrivateChannel('workspace.' . $this->message->conversation->workspace_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.created';
    }

    public function broadcastWith(): array
    {
        $conversation = $this->message->conversation;

        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'email' => $this->message->sender->email,
                'avatar_url' => $this->message->sender->avatar_url,
            ],
            'conversation' => [
                'id' => $conversation->id,
                'type' => $conversation->type,
                'name' => $conversation->name,
                'workspace_id' => $conversation->workspace_id,
                'participant_count' => $conversation->participants()->count(),
                'created_by' => $conversation->created_by,
                'created_at' => $conversation->created_at->toIso8601String(),
            ],
            'attachments' => $this->message->attachments->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'type' => $a->type,
                'url' => $a->url,
                'size' => $a->size,
            ]),
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
```

**Step 4: Run test to verify it passes**

Run: `cd api && php artisan test tests/Feature/Events/MessageCreatedEventTest.php`
Expected: PASS (2 tests)

**Step 5: Commit**

```bash
git add api/app/Events/MessageCreated.php api/tests/Feature/Events/MessageCreatedEventTest.php
git commit -m "feat: broadcast MessageCreated to workspace channel with rich context"
```

---

### Task 4: Enrich MessageDeleted Event with Workspace Channel

**Files:**
- Modify: `api/app/Events/MessageDeleted.php`
- Test: `api/tests/Feature/Events/MessageDeletedEventTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Feature\Events;

use App\Events\MessageDeleted;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageDeletedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_deleted_broadcasts_to_conversation_and_workspace_channels(): void
    {
        $workspace = Workspace::factory()->create();
        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);

        $event = new MessageDeleted('message-id-123', $conversation->id, $workspace->id, 'user-id-456');
        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertEquals('conversation.' . $conversation->id, $channels[0]->name);
        $this->assertEquals('workspace.' . $workspace->id, $channels[1]->name);
    }

    public function test_message_deleted_includes_workspace_context(): void
    {
        $workspace = Workspace::factory()->create();
        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);

        $event = new MessageDeleted('message-id-123', $conversation->id, $workspace->id, 'user-id-456');
        $data = $event->broadcastWith();

        $this->assertEquals('message-id-123', $data['id']);
        $this->assertEquals($conversation->id, $data['conversation_id']);
        $this->assertEquals($workspace->id, $data['workspace_id']);
        $this->assertEquals('user-id-456', $data['deleted_by']);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Feature/Events/MessageDeletedEventTest.php`
Expected: FAIL - event structure doesn't match

**Step 3: Check current MessageDeleted event**

Read `api/app/Events/MessageDeleted.php` to see current implementation.

**Step 4: Modify MessageDeleted event**

Edit `api/app/Events/MessageDeleted.php`:

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $messageId,
        public string $conversationId,
        public string $workspaceId,
        public string $deletedBy
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
            new PrivateChannel('workspace.' . $this->workspaceId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->messageId,
            'conversation_id' => $this->conversationId,
            'workspace_id' => $this->workspaceId,
            'deleted_by' => $this->deletedBy,
        ];
    }
}
```

**Step 5: Update MessageController to pass workspace_id**

Find where `MessageDeleted` is dispatched and update to pass workspace_id:

```php
// In MessageController::destroy() or similar
broadcast(new MessageDeleted(
    $message->id,
    $message->conversation_id,
    $message->conversation->workspace_id,
    $deletedBy
));
```

**Step 6: Run test to verify it passes**

Run: `cd api && php artisan test tests/Feature/Events/MessageDeletedEventTest.php`
Expected: PASS (2 tests)

**Step 7: Commit**

```bash
git add api/app/Events/MessageDeleted.php api/tests/Feature/Events/MessageDeletedEventTest.php
git commit -m "feat: broadcast MessageDeleted to workspace channel with context"
```

---

### Task 5: Enrich ConversationCreated Event with Workspace Channel

**Files:**
- Modify: `api/app/Events/ConversationCreated.php`
- Test: `api/tests/Feature/Events/ConversationCreatedEventTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Feature\Events;

use App\Events\ConversationCreated;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationCreatedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_created_broadcasts_to_workspace_channel(): void
    {
        $workspace = Workspace::factory()->create();
        $creator = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'created_by' => $creator->id,
        ]);

        $event = new ConversationCreated($conversation);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertEquals('workspace.' . $workspace->id, $channels[0]->name);
    }

    public function test_conversation_created_includes_full_conversation_data(): void
    {
        $workspace = Workspace::factory()->create();
        $creator = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
        ]);
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'type' => 'group',
            'name' => 'Team Discussion',
            'created_by' => $creator->id,
        ]);

        // Add participants
        $participant2 = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $conversation->participants()->attach($creator->id, ['role' => 'admin']);
        $conversation->participants()->attach($participant2->id, ['role' => 'member']);

        $event = new ConversationCreated($conversation);
        $data = $event->broadcastWith();

        $this->assertEquals($conversation->id, $data['id']);
        $this->assertEquals('group', $data['type']);
        $this->assertEquals('Team Discussion', $data['name']);
        $this->assertEquals($workspace->id, $data['workspace_id']);
        $this->assertEquals($creator->id, $data['created_by']['id']);
        $this->assertEquals('Alice Smith', $data['created_by']['name']);
        $this->assertCount(2, $data['participants']);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Feature/Events/ConversationCreatedEventTest.php`
Expected: FAIL - workspace channel not included, payload structure incorrect

**Step 3: Modify ConversationCreated event**

Edit `api/app/Events/ConversationCreated.php`:

```php
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
```

**Step 4: Run test to verify it passes**

Run: `cd api && php artisan test tests/Feature/Events/ConversationCreatedEventTest.php`
Expected: PASS (2 tests)

**Step 5: Commit**

```bash
git add api/app/Events/ConversationCreated.php api/tests/Feature/Events/ConversationCreatedEventTest.php
git commit -m "feat: broadcast ConversationCreated to workspace channel with full data"
```

---

### Task 6: Integration Test for Workspace Monitoring

**Files:**
- Test: `api/tests/Feature/Integration/WorkspaceMonitoringTest.php` (create)

**Step 1: Write integration test**

Create test file:

```php
<?php

namespace Tests\Feature\Integration;

use App\Events\ConversationCreated;
use App\Events\MessageCreated;
use App\Models\ApiKey;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WorkspaceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_workspace_monitoring_flow(): void
    {
        Event::fake([MessageCreated::class, ConversationCreated::class]);

        // Setup: Create workspace and API key
        $workspace = Workspace::factory()->create();
        $apiKey = ApiKey::factory()->create(['workspace_id' => $workspace->id]);

        // Step 1: Get realtime token
        $tokenResponse = $this->postJson("/api/v1/workspaces/{$workspace->id}/realtime/token", [], [
            'X-API-Key' => $apiKey->key,
        ]);

        $tokenResponse->assertOk();
        $token = $tokenResponse->json('token');
        $this->assertNotEmpty($token);

        // Step 2: Create conversation (triggers ConversationCreated event)
        $creator = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'created_by' => $creator->id,
        ]);

        broadcast(new ConversationCreated($conversation));

        // Step 3: Send message (triggers MessageCreated event)
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $creator->id,
        ]);

        broadcast(new MessageCreated($message));

        // Verify events were dispatched
        Event::assertDispatched(ConversationCreated::class, function ($event) use ($workspace) {
            $channels = $event->broadcastOn();
            $channelNames = array_map(fn($ch) => $ch->name, $channels);
            return in_array("workspace.{$workspace->id}", $channelNames);
        });

        Event::assertDispatched(MessageCreated::class, function ($event) use ($workspace) {
            $channels = $event->broadcastOn();
            $channelNames = array_map(fn($ch) => $ch->name, $channels);
            return in_array("workspace.{$workspace->id}", $channelNames);
        });
    }
}
```

**Step 2: Run integration test**

Run: `cd api && php artisan test tests/Feature/Integration/WorkspaceMonitoringTest.php`
Expected: PASS (1 test)

**Step 3: Commit**

```bash
git add api/tests/Feature/Integration/WorkspaceMonitoringTest.php
git commit -m "test: add integration test for workspace monitoring flow"
```

---

## Phase 2: Email Notification System

### Task 7: Create Email Templates Migration

**Files:**
- Create: `api/database/migrations/YYYY_MM_DD_create_email_templates_table.php`

**Step 1: Generate migration**

Run: `cd api && php artisan make:migration create_email_templates_table`

**Step 2: Write migration**

Edit the generated migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // "missed_message_1on1", "new_participant", etc
            $table->string('subject_template'); // "You have {{unread_count}} unread messages"
            $table->text('body_html');
            $table->text('body_text');
            $table->json('variables'); // ["unread_count", "sender_name", "conversation_name"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['workspace_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
```

**Step 3: Run migration**

Run: `cd api && php artisan migrate`
Expected: Migration creates email_templates table

**Step 4: Commit**

```bash
git add api/database/migrations/*_create_email_templates_table.php
git commit -m "feat: add email_templates table migration"
```

---

### Task 8: Create EmailTemplate Model

**Files:**
- Create: `api/app/Models/EmailTemplate.php`
- Test: `api/tests/Unit/Models/EmailTemplateTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Unit\Models;

use App\Models\EmailTemplate;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_template_belongs_to_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $template = EmailTemplate::factory()->create(['workspace_id' => $workspace->id]);

        $this->assertInstanceOf(Workspace::class, $template->workspace);
        $this->assertEquals($workspace->id, $template->workspace->id);
    }

    public function test_email_template_casts_variables_to_array(): void
    {
        $template = EmailTemplate::factory()->create([
            'variables' => ['sender_name', 'unread_count'],
        ]);

        $this->assertIsArray($template->variables);
        $this->assertEquals(['sender_name', 'unread_count'], $template->variables);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Unit/Models/EmailTemplateTest.php`
Expected: FAIL - EmailTemplate class not found

**Step 3: Create EmailTemplate model**

Create `api/app/Models/EmailTemplate.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'event_type',
        'subject_template',
        'body_html',
        'body_text',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
```

**Step 4: Create factory**

Run: `cd api && php artisan make:factory EmailTemplateFactory`

Edit `api/database/factories/EmailTemplateFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'event_type' => 'missed_message_1on1',
            'subject_template' => 'You have a new message from {{sender_name}}',
            'body_html' => '<p>{{sender_name}} sent: {{content}}</p>',
            'body_text' => '{{sender_name}} sent: {{content}}',
            'variables' => ['sender_name', 'content'],
            'is_active' => true,
        ];
    }
}
```

**Step 5: Run test to verify it passes**

Run: `cd api && php artisan test tests/Unit/Models/EmailTemplateTest.php`
Expected: PASS (2 tests)

**Step 6: Commit**

```bash
git add api/app/Models/EmailTemplate.php \
        api/database/factories/EmailTemplateFactory.php \
        api/tests/Unit/Models/EmailTemplateTest.php
git commit -m "feat: add EmailTemplate model with factory and tests"
```

---

### Task 9: Create Email Gateway Service

**Files:**
- Create: `api/app/Services/EmailGatewayService.php`
- Modify: `api/config/services.php`
- Test: `api/tests/Unit/Services/EmailGatewayServiceTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Unit\Services;

use App\Services\EmailGatewayService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmailGatewayServiceTest extends TestCase
{
    public function test_sends_email_via_gateway_api(): void
    {
        Http::fake([
            'https://email-gateway-production.up.railway.app/send' => Http::response([
                'success' => true,
                'message_id' => 'msg_123',
            ], 200),
        ]);

        $service = new EmailGatewayService();
        $result = $service->send(
            to: 'user@example.com',
            subject: 'Test Subject',
            htmlBody: '<p>HTML body</p>',
            textBody: 'Text body',
            fromName: 'Test App',
            replyTo: 'support@test.com'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('msg_123', $result['message_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://email-gateway-production.up.railway.app/send' &&
                   $request->hasHeader('X-API-Key', config('services.email_gateway.api_key')) &&
                   $request['to'] === 'user@example.com' &&
                   $request['subject'] === 'Test Subject';
        });
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Unit/Services/EmailGatewayServiceTest.php`
Expected: FAIL - EmailGatewayService not found

**Step 3: Add config**

Edit `api/config/services.php`, add to the array:

```php
'email_gateway' => [
    'url' => env('EMAIL_GATEWAY_URL', 'https://email-gateway-production.up.railway.app'),
    'api_key' => env('EMAIL_GATEWAY_API_KEY', 'egw_live_vJetwlkKk-odOzkkbIqrlPUD5enBW0FH'),
],
```

**Step 4: Create EmailGatewayService**

Create `api/app/Services/EmailGatewayService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailGatewayService
{
    private string $url;
    private string $apiKey;

    public function __construct()
    {
        $this->url = config('services.email_gateway.url');
        $this->apiKey = config('services.email_gateway.api_key');
    }

    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $textBody,
        string $fromName = 'Reusable Chat',
        ?string $replyTo = null
    ): array {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->post($this->url . '/send', [
                'to' => $to,
                'subject' => $subject,
                'html_body' => $htmlBody,
                'text_body' => $textBody,
                'from_name' => $fromName,
                'reply_to' => $replyTo,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                ];
            }

            Log::error('Email gateway error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('error', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Email gateway exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
```

**Step 5: Run test to verify it passes**

Run: `cd api && php artisan test tests/Unit/Services/EmailGatewayServiceTest.php`
Expected: PASS (1 test)

**Step 6: Commit**

```bash
git add api/app/Services/EmailGatewayService.php \
        api/config/services.php \
        api/tests/Unit/Services/EmailGatewayServiceTest.php
git commit -m "feat: add EmailGatewayService for sending emails"
```

---

### Task 10: Create Email Template Renderer Service

**Files:**
- Create: `api/app/Services/EmailTemplateRenderer.php`
- Test: `api/tests/Unit/Services/EmailTemplateRendererTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Unit\Services;

use App\Services\EmailTemplateRenderer;
use Tests\TestCase;

class EmailTemplateRendererTest extends TestCase
{
    public function test_renders_template_with_variables(): void
    {
        $renderer = new EmailTemplateRenderer();

        $result = $renderer->render(
            template: 'Hello {{name}}, you have {{count}} messages',
            variables: [
                'name' => 'John',
                'count' => 5,
            ]
        );

        $this->assertEquals('Hello John, you have 5 messages', $result);
    }

    public function test_renders_nested_variables(): void
    {
        $renderer = new EmailTemplateRenderer();

        $result = $renderer->render(
            template: '{{sender.name}} sent: {{message.content}}',
            variables: [
                'sender' => ['name' => 'Alice'],
                'message' => ['content' => 'Hello world'],
            ]
        );

        $this->assertEquals('Alice sent: Hello world', $result);
    }

    public function test_applies_branding_to_html(): void
    {
        $renderer = new EmailTemplateRenderer();

        $result = $renderer->applyBranding(
            html: '<p>{{content}}</p>',
            branding: [
                'logo_url' => 'https://example.com/logo.png',
                'brand_color' => '#FF5733',
                'footer_text' => '© 2026 MyApp',
            ]
        );

        $this->assertStringContainsString('https://example.com/logo.png', $result);
        $this->assertStringContainsString('#FF5733', $result);
        $this->assertStringContainsString('© 2026 MyApp', $result);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Unit/Services/EmailTemplateRendererTest.php`
Expected: FAIL - EmailTemplateRenderer not found

**Step 3: Create EmailTemplateRenderer**

Create `api/app/Services/EmailTemplateRenderer.php`:

```php
<?php

namespace App\Services;

class EmailTemplateRenderer
{
    public function render(string $template, array $variables): string
    {
        $result = $template;

        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                // Handle nested variables like {{sender.name}}
                foreach ($value as $nestedKey => $nestedValue) {
                    $placeholder = '{{' . $key . '.' . $nestedKey . '}}';
                    $result = str_replace($placeholder, $nestedValue, $result);
                }
            } else {
                // Handle simple variables like {{name}}
                $placeholder = '{{' . $key . '}}';
                $result = str_replace($placeholder, $value, $result);
            }
        }

        return $result;
    }

    public function applyBranding(string $html, array $branding): string
    {
        $logoUrl = $branding['logo_url'] ?? '';
        $brandColor = $branding['brand_color'] ?? '#000000';
        $footerText = $branding['footer_text'] ?? '';

        $brandedHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 3px solid {$brandColor}; }
        .logo { max-width: 200px; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ccc; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{$logoUrl}" alt="Logo" class="logo">
        </div>
        <div class="content">
            {$html}
        </div>
        <div class="footer">
            {$footerText}
        </div>
    </div>
</body>
</html>
HTML;

        return $brandedHtml;
    }
}
```

**Step 4: Run test to verify it passes**

Run: `cd api && php artisan test tests/Unit/Services/EmailTemplateRendererTest.php`
Expected: PASS (3 tests)

**Step 5: Commit**

```bash
git add api/app/Services/EmailTemplateRenderer.php \
        api/tests/Unit/Services/EmailTemplateRendererTest.php
git commit -m "feat: add EmailTemplateRenderer for variable substitution and branding"
```

---

### Task 11: Create SendMissedMessageEmail Job

**Files:**
- Create: `api/app/Jobs/SendMissedMessageEmail.php`
- Test: `api/tests/Unit/Jobs/SendMissedMessageEmailTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendMissedMessageEmail;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\EmailTemplate;
use App\Models\Message;
use App\Models\Workspace;
use App\Services\EmailGatewayService;
use App\Services\EmailTemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendMissedMessageEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_email_to_unread_participants(): void
    {
        $workspace = Workspace::factory()->create([
            'settings' => [
                'email_notifications' => ['enabled' => true],
                'email_branding' => [
                    'from_name' => 'Test App',
                    'reply_to' => 'support@test.com',
                ],
            ],
        ]);

        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $recipient = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'email' => 'recipient@test.com',
        ]);

        // Add participants
        $conversation->participants()->attach($sender->id, ['last_read_at' => now()]);
        $conversation->participants()->attach($recipient->id, ['last_read_at' => now()->subHour()]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Hello there',
        ]);

        EmailTemplate::factory()->create([
            'workspace_id' => $workspace->id,
            'event_type' => 'missed_message_1on1',
            'subject_template' => 'New message from {{sender.name}}',
            'body_html' => '<p>{{sender.name}} said: {{message.content}}</p>',
            'body_text' => '{{sender.name}} said: {{message.content}}',
        ]);

        $job = new SendMissedMessageEmail($message);

        $this->mock(EmailGatewayService::class)
            ->shouldReceive('send')
            ->once()
            ->withArgs(function ($to, $subject, $htmlBody, $textBody) use ($recipient, $sender) {
                return $to === 'recipient@test.com' &&
                       str_contains($subject, $sender->name) &&
                       str_contains($htmlBody, 'Hello there');
            })
            ->andReturn(['success' => true, 'message_id' => 'test_123']);

        $job->handle(
            new EmailGatewayService(),
            new EmailTemplateRenderer()
        );
    }

    public function test_skips_sender_and_users_without_email(): void
    {
        $workspace = Workspace::factory()->create([
            'settings' => [
                'email_notifications' => ['enabled' => true],
            ],
        ]);

        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $noEmail = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'email' => null,
        ]);

        $conversation->participants()->attach($sender->id, ['last_read_at' => now()->subHour()]);
        $conversation->participants()->attach($noEmail->id, ['last_read_at' => now()->subHour()]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
        ]);

        EmailTemplate::factory()->create([
            'workspace_id' => $workspace->id,
            'event_type' => 'missed_message_1on1',
        ]);

        $job = new SendMissedMessageEmail($message);

        $this->mock(EmailGatewayService::class)
            ->shouldReceive('send')
            ->never();

        $job->handle(
            new EmailGatewayService(),
            new EmailTemplateRenderer()
        );
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Unit/Jobs/SendMissedMessageEmailTest.php`
Expected: FAIL - SendMissedMessageEmail not found

**Step 3: Create SendMissedMessageEmail job**

Run: `cd api && php artisan make:job SendMissedMessageEmail`

Edit `api/app/Jobs/SendMissedMessageEmail.php`:

```php
<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\EmailGatewayService;
use App\Services\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMissedMessageEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Message $message
    ) {
        $this->message->load(['conversation.workspace', 'sender']);
    }

    public function handle(
        EmailGatewayService $emailGateway,
        EmailTemplateRenderer $renderer
    ): void {
        $workspace = $this->message->conversation->workspace;
        $settings = $workspace->settings ?? [];

        // Check if email notifications are enabled
        if (!($settings['email_notifications']['enabled'] ?? false)) {
            return;
        }

        // Get email template
        $template = $workspace->emailTemplates()
            ->where('event_type', 'missed_message_1on1')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning('No email template found for missed_message_1on1', [
                'workspace_id' => $workspace->id,
            ]);
            return;
        }

        // Get recipients (participants who haven't read this message)
        $recipients = $this->message->conversation->participants()
            ->where('chat_user_id', '!=', $this->message->sender_id) // Exclude sender
            ->whereNotNull('chat_users.email') // Must have email
            ->where(function ($query) {
                $query->whereNull('participants.last_read_at')
                    ->orWhere('participants.last_read_at', '<', $this->message->created_at);
            })
            ->get();

        // Prepare template variables
        $variables = [
            'sender' => [
                'name' => $this->message->sender->name,
                'email' => $this->message->sender->email,
            ],
            'message' => [
                'content' => $this->message->content,
            ],
            'conversation' => [
                'name' => $this->message->conversation->name,
            ],
        ];

        // Send email to each recipient
        foreach ($recipients as $recipient) {
            $subject = $renderer->render($template->subject_template, $variables);
            $htmlBody = $renderer->render($template->body_html, $variables);
            $textBody = $renderer->render($template->body_text, $variables);

            // Apply branding
            $branding = $settings['email_branding'] ?? [];
            $htmlBody = $renderer->applyBranding($htmlBody, $branding);

            $result = $emailGateway->send(
                to: $recipient->email,
                subject: $subject,
                htmlBody: $htmlBody,
                textBody: $textBody,
                fromName: $branding['from_name'] ?? 'Reusable Chat',
                replyTo: $branding['reply_to'] ?? null
            );

            if (!$result['success']) {
                Log::error('Failed to send missed message email', [
                    'recipient' => $recipient->email,
                    'message_id' => $this->message->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        }
    }
}
```

**Step 4: Add emailTemplates relationship to Workspace model**

Edit `api/app/Models/Workspace.php`, add method:

```php
public function emailTemplates(): HasMany
{
    return $this->hasMany(EmailTemplate::class);
}
```

**Step 5: Run test to verify it passes**

Run: `cd api && php artisan test tests/Unit/Jobs/SendMissedMessageEmailTest.php`
Expected: PASS (2 tests)

**Step 6: Commit**

```bash
git add api/app/Jobs/SendMissedMessageEmail.php \
        api/app/Models/Workspace.php \
        api/tests/Unit/Jobs/SendMissedMessageEmailTest.php
git commit -m "feat: add SendMissedMessageEmail job with recipient filtering"
```

---

### Task 12: Create Default Email Templates Seeder

**Files:**
- Create: `api/database/seeders/DefaultEmailTemplatesSeeder.php`

**Step 1: Create seeder**

Run: `cd api && php artisan make:seeder DefaultEmailTemplatesSeeder`

Edit `api/database/seeders/DefaultEmailTemplatesSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class DefaultEmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'event_type' => 'missed_message_1on1',
                'subject_template' => 'New message from {{sender.name}}',
                'body_html' => '<h2>You have a new message</h2><p><strong>{{sender.name}}</strong> said:</p><p>{{message.content}}</p>',
                'body_text' => '{{sender.name}} said: {{message.content}}',
                'variables' => ['sender.name', 'sender.email', 'message.content'],
            ],
            [
                'event_type' => 'missed_message_group',
                'subject_template' => '{{unread_count}} unread messages in {{conversation.name}}',
                'body_html' => '<h2>You have unread messages</h2><p>{{unread_count}} new messages in <strong>{{conversation.name}}</strong></p>',
                'body_text' => 'You have {{unread_count}} unread messages in {{conversation.name}}',
                'variables' => ['unread_count', 'conversation.name'],
            ],
            [
                'event_type' => 'new_participant',
                'subject_template' => '{{participant.name}} joined {{conversation.name}}',
                'body_html' => '<h2>New participant</h2><p><strong>{{participant.name}}</strong> joined {{conversation.name}}</p>',
                'body_text' => '{{participant.name}} joined {{conversation.name}}',
                'variables' => ['participant.name', 'conversation.name'],
            ],
            [
                'event_type' => 'new_inquiry',
                'subject_template' => 'New inquiry from {{sender.name}}',
                'body_html' => '<h2>New inquiry</h2><p><strong>{{sender.name}}</strong> started a conversation</p><p>{{message.content}}</p>',
                'body_text' => '{{sender.name}} started a conversation: {{message.content}}',
                'variables' => ['sender.name', 'message.content'],
            ],
        ];

        // Seed templates for all existing workspaces
        $workspaces = Workspace::all();

        foreach ($workspaces as $workspace) {
            foreach ($templates as $templateData) {
                EmailTemplate::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'event_type' => $templateData['event_type'],
                    ],
                    $templateData
                );
            }
        }
    }
}
```

**Step 2: Run seeder**

Run: `cd api && php artisan db:seed --class=DefaultEmailTemplatesSeeder`
Expected: Seeds default templates for all workspaces

**Step 3: Commit**

```bash
git add api/database/seeders/DefaultEmailTemplatesSeeder.php
git commit -m "feat: add seeder for default email templates"
```

---

### Task 13: Wire Up MessageCreated Event Listener

**Files:**
- Create: `api/app/Listeners/DispatchMissedMessageEmail.php`
- Modify: `api/app/Providers/EventServiceProvider.php`
- Test: `api/tests/Feature/Listeners/DispatchMissedMessageEmailTest.php` (create)

**Step 1: Write the failing test**

Create test file:

```php
<?php

namespace Tests\Feature\Listeners;

use App\Events\MessageCreated;
use App\Jobs\SendMissedMessageEmail;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DispatchMissedMessageEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_missed_message_email_job(): void
    {
        Queue::fake();

        $workspace = Workspace::factory()->create([
            'settings' => [
                'email_notifications' => [
                    'enabled' => true,
                    'triggers' => [
                        'missed_message_1on1_minutes' => 15,
                    ],
                ],
            ],
        ]);

        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
        ]);

        event(new MessageCreated($message));

        Queue::assertPushed(SendMissedMessageEmail::class, function ($job) use ($message) {
            return $job->message->id === $message->id &&
                   $job->delay->totalMinutes() === 15;
        });
    }

    public function test_does_not_dispatch_when_email_notifications_disabled(): void
    {
        Queue::fake();

        $workspace = Workspace::factory()->create([
            'settings' => [
                'email_notifications' => ['enabled' => false],
            ],
        ]);

        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
        ]);

        event(new MessageCreated($message));

        Queue::assertNotPushed(SendMissedMessageEmail::class);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `cd api && php artisan test tests/Feature/Listeners/DispatchMissedMessageEmailTest.php`
Expected: FAIL - listener not registered

**Step 3: Create listener**

Run: `cd api && php artisan make:listener DispatchMissedMessageEmail --event=MessageCreated`

Edit `api/app/Listeners/DispatchMissedMessageEmail.php`:

```php
<?php

namespace App\Listeners;

use App\Events\MessageCreated;
use App\Jobs\SendMissedMessageEmail;

class DispatchMissedMessageEmail
{
    public function handle(MessageCreated $event): void
    {
        $message = $event->message;
        $workspace = $message->conversation->workspace;
        $settings = $workspace->settings ?? [];

        // Check if email notifications are enabled
        if (!($settings['email_notifications']['enabled'] ?? false)) {
            return;
        }

        // Get delay from workspace settings
        $delayMinutes = $settings['email_notifications']['triggers']['missed_message_1on1_minutes'] ?? 15;

        // Dispatch job with delay
        SendMissedMessageEmail::dispatch($message)
            ->delay(now()->addMinutes($delayMinutes));
    }
}
```

**Step 4: Register listener**

Edit `api/app/Providers/EventServiceProvider.php`, add to `$listen` array:

```php
use App\Events\MessageCreated;
use App\Listeners\DispatchMissedMessageEmail;

protected $listen = [
    // ... existing listeners
    MessageCreated::class => [
        DispatchMissedMessageEmail::class,
    ],
];
```

**Step 5: Run test to verify it passes**

Run: `cd api && php artisan test tests/Feature/Listeners/DispatchMissedMessageEmailTest.php`
Expected: PASS (2 tests)

**Step 6: Commit**

```bash
git add api/app/Listeners/DispatchMissedMessageEmail.php \
        api/app/Providers/EventServiceProvider.php \
        api/tests/Feature/Listeners/DispatchMissedMessageEmailTest.php
git commit -m "feat: dispatch missed message email job on MessageCreated event"
```

---

### Task 14: Integration Test for Email Notifications

**Files:**
- Test: `api/tests/Feature/Integration/EmailNotificationTest.php` (create)

**Step 1: Write integration test**

Create test file:

```php
<?php

namespace Tests\Feature\Integration;

use App\Events\MessageCreated;
use App\Jobs\SendMissedMessageEmail;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\EmailTemplate;
use App\Models\Message;
use App\Models\Workspace;
use App\Services\EmailGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_email_notification_flow(): void
    {
        Queue::fake();

        // Setup workspace with email notifications enabled
        $workspace = Workspace::factory()->create([
            'settings' => [
                'email_notifications' => [
                    'enabled' => true,
                    'triggers' => [
                        'missed_message_1on1_minutes' => 5,
                    ],
                ],
                'email_branding' => [
                    'logo_url' => 'https://example.com/logo.png',
                    'brand_color' => '#FF5733',
                    'from_name' => 'Test App',
                    'reply_to' => 'support@test.com',
                    'footer_text' => '© 2026 Test App',
                ],
            ],
        ]);

        // Create email template
        EmailTemplate::factory()->create([
            'workspace_id' => $workspace->id,
            'event_type' => 'missed_message_1on1',
            'subject_template' => 'New message from {{sender.name}}',
            'body_html' => '<p>{{sender.name}} said: {{message.content}}</p>',
            'body_text' => '{{sender.name}} said: {{message.content}}',
        ]);

        // Create conversation with participants
        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Alice',
            'email' => 'alice@test.com',
        ]);
        $recipient = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Bob',
            'email' => 'bob@test.com',
        ]);

        $conversation->participants()->attach($sender->id, ['last_read_at' => now()]);
        $conversation->participants()->attach($recipient->id, ['last_read_at' => now()->subHour()]);

        // Send message
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Hey Bob, how are you?',
        ]);

        // Trigger event
        event(new MessageCreated($message));

        // Assert job was dispatched with correct delay
        Queue::assertPushed(SendMissedMessageEmail::class, function ($job) use ($message) {
            return $job->message->id === $message->id &&
                   $job->delay->totalMinutes() === 5;
        });

        // Execute the job manually to test full flow
        $this->mock(EmailGatewayService::class)
            ->shouldReceive('send')
            ->once()
            ->withArgs(function ($to, $subject, $htmlBody, $textBody, $fromName, $replyTo) {
                return $to === 'bob@test.com' &&
                       $subject === 'New message from Alice' &&
                       str_contains($htmlBody, 'Alice said: Hey Bob, how are you?') &&
                       str_contains($htmlBody, 'https://example.com/logo.png') &&
                       str_contains($htmlBody, '#FF5733') &&
                       $fromName === 'Test App' &&
                       $replyTo === 'support@test.com';
            })
            ->andReturn(['success' => true, 'message_id' => 'test_123']);

        $job = new SendMissedMessageEmail($message);
        $job->handle(
            app(EmailGatewayService::class),
            app(\App\Services\EmailTemplateRenderer::class)
        );
    }
}
```

**Step 2: Run integration test**

Run: `cd api && php artisan test tests/Feature/Integration/EmailNotificationTest.php`
Expected: PASS (1 test)

**Step 3: Commit**

```bash
git add api/tests/Feature/Integration/EmailNotificationTest.php
git commit -m "test: add integration test for complete email notification flow"
```

---

## Final Tasks

### Task 15: Update Documentation

**Files:**
- Modify: `dashboard/app/pages/docs.vue`

**Step 1: Add workspace monitoring section to docs**

Edit `dashboard/app/pages/docs.vue`, add new section after existing API documentation:

```vue
<!-- Workspace Monitoring Section -->
<section id="workspace-monitoring" class="mb-12">
  <h2 class="text-3xl font-bold mb-4">Workspace Monitoring</h2>
  <p class="text-gray-600 mb-6">
    Monitor all activity across your workspace in real-time via WebSocket.
  </p>

  <h3 class="text-2xl font-semibold mb-3">Getting Started</h3>
  <div class="bg-gray-50 p-6 rounded-lg mb-6">
    <h4 class="font-semibold mb-2">1. Generate Realtime Token</h4>
    <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>POST /api/v1/workspaces/{workspace_id}/realtime/token
X-API-Key: your_api_key

Response:
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_at": "2026-01-17T10:00:00Z",
  "workspace_id": "uuid",
  "channels": ["private-workspace.{workspace_id}"]
}</code></pre>
  </div>

  <div class="bg-gray-50 p-6 rounded-lg mb-6">
    <h4 class="font-semibold mb-2">2. Connect to WebSocket</h4>
    <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>import Echo from 'laravel-echo';

const echo = new Echo({
  broadcaster: 'pusher',
  key: 'reusable-chat-key',
  wsHost: 'api-production-de24c.up.railway.app',
  wsPort: 443,
  wssPort: 443,
  forceTLS: true,
  authEndpoint: 'https://api.../api/v1/broadcasting/auth',
  auth: {
    headers: { Authorization: `Bearer ${token}` }
  }
});

// Subscribe to workspace channel
echo.private(`workspace.${workspace_id}`)
  .listen('.message.created', (event) => {
    console.log('New message:', event);
  })
  .listen('.conversation.created', (event) => {
    console.log('New conversation:', event);
  })
  .listen('.message.deleted', (event) => {
    console.log('Message deleted:', event);
  });</code></pre>
  </div>

  <h3 class="text-2xl font-semibold mb-3 mt-8">Event Payloads</h3>

  <div class="bg-gray-50 p-6 rounded-lg mb-6">
    <h4 class="font-semibold mb-2">message.created</h4>
    <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "id": "uuid",
  "content": "Hello there!",
  "sender": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": "https://..."
  },
  "conversation": {
    "id": "uuid",
    "type": "1on1",
    "name": "Support Chat",
    "workspace_id": "uuid",
    "participant_count": 2,
    "created_by": "uuid",
    "created_at": "2026-01-16T10:00:00Z"
  },
  "attachments": [...],
  "created_at": "2026-01-16T10:30:00Z"
}</code></pre>
  </div>
</section>

<!-- Email Notifications Section -->
<section id="email-notifications" class="mb-12">
  <h2 class="text-3xl font-bold mb-4">Email Notifications</h2>
  <p class="text-gray-600 mb-6">
    Customize email notifications for missed messages and other events.
  </p>

  <h3 class="text-2xl font-semibold mb-3">Configuration</h3>
  <p class="mb-4">
    Email notifications are configured per workspace in the <code>workspace_settings</code> table:
  </p>
  <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto mb-6"><code>{
  "email_notifications": {
    "enabled": true,
    "triggers": {
      "missed_message_1on1_minutes": 15,
      "missed_message_group_minutes": 30
    }
  },
  "email_branding": {
    "logo_url": "https://yourapp.com/logo.png",
    "brand_color": "#FF5733",
    "from_name": "Your App",
    "reply_to": "support@yourapp.com",
    "footer_text": "© 2026 Your App"
  }
}</code></pre>

  <h3 class="text-2xl font-semibold mb-3 mt-8">Custom Templates</h3>
  <p class="mb-4">
    Create custom email templates via the Dashboard API or database. Templates support variable substitution using <code>{{"{{variable}}"}}</code> syntax.
  </p>
</section>
```

**Step 2: Commit**

```bash
git add dashboard/app/pages/docs.vue
git commit -m "docs: add workspace monitoring and email notifications sections"
```

---

### Task 16: Run All Tests

**Step 1: Run full test suite**

Run: `cd api && php artisan test`
Expected: All tests pass

**Step 2: Fix any failing tests**

If any tests fail, fix them before proceeding.

**Step 3: Final commit**

```bash
git commit -m "test: verify all tests pass after workspace monitoring implementation"
```

---

## Execution Complete

All tasks implemented! The system now supports:

✅ Workspace-level WebSocket channels for real-time monitoring
✅ Rich event payloads with conversation context
✅ Realtime token endpoint for secure WebSocket authentication
✅ Email notification system with customizable templates
✅ Email branding and variable substitution
✅ Email gateway integration
✅ Comprehensive test coverage
✅ Updated documentation

**Next steps:**
- Deploy to Railway production
- Test with real WebSocket clients
- Monitor email delivery rates
- Gather tenant feedback on email templates
