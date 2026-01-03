<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->workspace = Workspace::factory()->create(['owner_id' => $this->admin->id]);

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'admin_id' => $this->admin->id,
            'role' => 'owner',
        ]);
    }

    public function test_overview_returns_correct_counts(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create test data
        $users = ChatUser::factory()->count(5)->create([
            'workspace_id' => $this->workspace->id,
        ]);

        // Set one user as active today
        $users[0]->update(['last_seen_at' => now()]);

        $conversation = Conversation::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        Message::factory()->count(10)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $users[0]->id,
        ]);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics");

        $response->assertOk()
            ->assertJsonStructure([
                'total_users',
                'total_conversations',
                'total_messages',
                'active_users_today',
            ])
            ->assertJson([
                'total_users' => 5,
                'total_conversations' => 1,
                'total_messages' => 10,
                'active_users_today' => 1,
            ]);
    }

    public function test_overview_returns_zero_counts_for_empty_workspace(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics");

        $response->assertOk()
            ->assertJson([
                'total_users' => 0,
                'total_conversations' => 0,
                'total_messages' => 0,
                'active_users_today' => 0,
            ]);
    }

    public function test_messages_endpoint_returns_stats_for_default_period(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $user = ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $conversation = Conversation::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        // Create messages over the last 5 days
        for ($i = 0; $i < 5; $i++) {
            Message::factory()->count(3)->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'created_at' => now()->subDays($i),
            ]);
        }

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/messages");

        $response->assertOk()
            ->assertJsonStructure([
                '*' => ['date', 'count'],
            ]);

        // Should have stats for 5 days
        $data = $response->json();
        $this->assertCount(5, $data);

        // Each day should have 3 messages
        foreach ($data as $stat) {
            $this->assertEquals(3, $stat['count']);
        }
    }

    public function test_messages_endpoint_respects_days_parameter(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $user = ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $conversation = Conversation::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        // Create messages: 2 in last 7 days, 1 older than 7 days
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'created_at' => now()->subDays(3),
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'created_at' => now()->subDays(5),
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'created_at' => now()->subDays(10),
        ]);

        // Request last 7 days
        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/messages?days=7");

        $response->assertOk();
        $data = $response->json();
        $totalCount = array_sum(array_column($data, 'count'));
        $this->assertEquals(2, $totalCount);
    }

    public function test_messages_endpoint_with_30_day_period(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $user = ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        $conversation = Conversation::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);

        // Create messages at different times
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'created_at' => now()->subDays(5),
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'created_at' => now()->subDays(20),
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'created_at' => now()->subDays(40),
        ]);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/messages?days=30");

        $response->assertOk();
        $data = $response->json();
        $totalCount = array_sum(array_column($data, 'count'));
        $this->assertEquals(2, $totalCount);
    }

    public function test_users_endpoint_returns_stats_for_default_period(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create users over the last 5 days
        for ($i = 0; $i < 5; $i++) {
            ChatUser::factory()->count(2)->create([
                'workspace_id' => $this->workspace->id,
                'created_at' => now()->subDays($i),
            ]);
        }

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/users");

        $response->assertOk()
            ->assertJsonStructure([
                '*' => ['date', 'count'],
            ]);

        $data = $response->json();
        $this->assertCount(5, $data);

        foreach ($data as $stat) {
            $this->assertEquals(2, $stat['count']);
        }
    }

    public function test_users_endpoint_respects_days_parameter(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create users: 2 in last 7 days, 1 older than 7 days
        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_at' => now()->subDays(3),
        ]);

        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_at' => now()->subDays(5),
        ]);

        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/users?days=7");

        $response->assertOk();
        $data = $response->json();
        $totalCount = array_sum(array_column($data, 'count'));
        $this->assertEquals(2, $totalCount);
    }

    public function test_users_endpoint_with_90_day_period(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_at' => now()->subDays(10),
        ]);

        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_at' => now()->subDays(60),
        ]);

        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_at' => now()->subDays(100),
        ]);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/users?days=90");

        $response->assertOk();
        $data = $response->json();
        $totalCount = array_sum(array_column($data, 'count'));
        $this->assertEquals(2, $totalCount);
    }

    public function test_overview_requires_authentication(): void
    {
        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics");

        $response->assertUnauthorized();
    }

    public function test_messages_requires_authentication(): void
    {
        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/messages");

        $response->assertUnauthorized();
    }

    public function test_users_requires_authentication(): void
    {
        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/users");

        $response->assertUnauthorized();
    }

    public function test_non_member_cannot_access_overview(): void
    {
        $otherAdmin = Admin::factory()->create();
        Sanctum::actingAs($otherAdmin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics");

        $response->assertNotFound();
    }

    public function test_non_member_cannot_access_messages(): void
    {
        $otherAdmin = Admin::factory()->create();
        Sanctum::actingAs($otherAdmin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/messages");

        $response->assertNotFound();
    }

    public function test_non_member_cannot_access_users(): void
    {
        $otherAdmin = Admin::factory()->create();
        Sanctum::actingAs($otherAdmin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/users");

        $response->assertNotFound();
    }

    public function test_returns_404_for_non_existent_workspace(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/dashboard/workspaces/{$fakeId}/analytics");

        $response->assertNotFound();
    }

    public function test_member_can_access_analytics(): void
    {
        $member = Admin::factory()->create();

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'admin_id' => $member->id,
            'role' => 'member',
        ]);

        Sanctum::actingAs($member, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics");

        $response->assertOk();
    }

    public function test_messages_validation_rejects_invalid_days(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/messages?days=500");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days']);
    }

    public function test_messages_validation_rejects_negative_days(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/messages?days=-5");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days']);
    }

    public function test_users_validation_rejects_invalid_days(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/users?days=500");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days']);
    }

    public function test_messages_only_counts_workspace_messages(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create another workspace with messages
        $otherWorkspace = Workspace::factory()->create(['owner_id' => $this->admin->id]);
        $otherUser = ChatUser::factory()->create(['workspace_id' => $otherWorkspace->id]);
        $otherConversation = Conversation::factory()->create(['workspace_id' => $otherWorkspace->id]);

        Message::factory()->count(5)->create([
            'conversation_id' => $otherConversation->id,
            'sender_id' => $otherUser->id,
        ]);

        // Create messages in our workspace
        $user = ChatUser::factory()->create(['workspace_id' => $this->workspace->id]);
        $conversation = Conversation::factory()->create(['workspace_id' => $this->workspace->id]);

        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
        ]);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/messages");

        $response->assertOk();
        $data = $response->json();
        $totalCount = array_sum(array_column($data, 'count'));
        $this->assertEquals(3, $totalCount);
    }

    public function test_users_only_counts_workspace_users(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create another workspace with users
        $otherWorkspace = Workspace::factory()->create(['owner_id' => $this->admin->id]);
        ChatUser::factory()->count(5)->create(['workspace_id' => $otherWorkspace->id]);

        // Create users in our workspace
        ChatUser::factory()->count(3)->create(['workspace_id' => $this->workspace->id]);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics/users");

        $response->assertOk();
        $data = $response->json();
        $totalCount = array_sum(array_column($data, 'count'));
        $this->assertEquals(3, $totalCount);
    }

    public function test_overview_only_counts_active_users_from_today(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create users with different last_seen_at times
        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'last_seen_at' => now(), // Active today
        ]);

        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'last_seen_at' => now()->subHours(5), // Still today
        ]);

        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'last_seen_at' => now()->subDays(1), // Yesterday
        ]);

        ChatUser::factory()->create([
            'workspace_id' => $this->workspace->id,
            'last_seen_at' => null, // Never seen
        ]);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/analytics");

        $response->assertOk()
            ->assertJson([
                'total_users' => 4,
                'active_users_today' => 2,
            ]);
    }
}
