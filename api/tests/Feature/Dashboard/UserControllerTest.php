<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Ban;
use App\Models\ChatUser;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Workspace $workspace;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create a workspace
        $this->workspace = Workspace::create([
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
            'plan' => 'free',
            'owner_id' => $this->admin->id,
        ]);

        // Make admin a member of the workspace
        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'admin_id' => $this->admin->id,
            'role' => 'owner',
        ]);

        // Create a Sanctum token for the admin
        $this->token = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_users(): void
    {
        // Create some chat users
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_anonymous' => false,
            'last_seen_at' => now(),
        ]);

        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'is_anonymous' => false,
            'last_seen_at' => now()->subHours(2),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users' => [
                    'data' => [
                        '*' => ['id', 'name', 'email', 'is_anonymous', 'last_seen_at', 'conversations_count'],
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'stats' => [
                    'total_users',
                    'online_users',
                    'active_today',
                    'anonymous_users',
                    'banned_users',
                ],
            ]);

        $this->assertEquals(2, $response->json('users.total'));
        $this->assertEquals(2, $response->json('stats.total_users'));
    }

    public function test_can_filter_users_by_search(): void
    {
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_anonymous' => false,
        ]);

        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'is_anonymous' => false,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users?search=john");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('users.total'));
        $this->assertEquals('John Doe', $response->json('users.data.0.name'));
    }

    public function test_can_filter_users_by_status_online(): void
    {
        // Online user (seen within 5 minutes)
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Online User',
            'is_anonymous' => false,
            'last_seen_at' => now(),
        ]);

        // Active user (seen within 24 hours but not online)
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Active User',
            'is_anonymous' => false,
            'last_seen_at' => now()->subHours(2),
        ]);

        // Inactive user
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Inactive User',
            'is_anonymous' => false,
            'last_seen_at' => now()->subDays(2),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users?status=online");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('users.total'));
        $this->assertEquals('Online User', $response->json('users.data.0.name'));
    }

    public function test_can_filter_users_by_status_active(): void
    {
        // Online user (seen within 5 minutes)
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Online User',
            'is_anonymous' => false,
            'last_seen_at' => now(),
        ]);

        // Active user (seen within 24 hours but not online)
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Active User',
            'is_anonymous' => false,
            'last_seen_at' => now()->subHours(2),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users?status=active");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('users.total'));
        $this->assertEquals('Active User', $response->json('users.data.0.name'));
    }

    public function test_can_filter_users_by_type_anonymous(): void
    {
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Registered User',
            'email' => 'registered@example.com',
            'is_anonymous' => false,
        ]);

        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Anonymous User',
            'is_anonymous' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users?type=anonymous");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('users.total'));
        $this->assertEquals('Anonymous User', $response->json('users.data.0.name'));
    }

    public function test_can_filter_users_by_type_registered(): void
    {
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Registered User',
            'email' => 'registered@example.com',
            'is_anonymous' => false,
        ]);

        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Anonymous User',
            'is_anonymous' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users?type=registered");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('users.total'));
        $this->assertEquals('Registered User', $response->json('users.data.0.name'));
    }

    public function test_stats_are_calculated_correctly(): void
    {
        // Online user
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Online User',
            'is_anonymous' => false,
            'last_seen_at' => now(),
        ]);

        // Active today but not online
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Active User',
            'is_anonymous' => false,
            'last_seen_at' => now()->subHours(2),
        ]);

        // Anonymous user (also online)
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Anonymous User',
            'is_anonymous' => true,
            'last_seen_at' => now(),
        ]);

        // Inactive user
        ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Inactive User',
            'is_anonymous' => false,
            'last_seen_at' => now()->subDays(5),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users");

        $response->assertStatus(200);
        $this->assertEquals(4, $response->json('stats.total_users'));
        $this->assertEquals(2, $response->json('stats.online_users')); // Online + Anonymous online
        $this->assertEquals(3, $response->json('stats.active_today')); // Online + Active + Anonymous
        $this->assertEquals(1, $response->json('stats.anonymous_users'));
        $this->assertEquals(0, $response->json('stats.banned_users'));
    }

    public function test_can_ban_user(): void
    {
        $chatUser = ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'User to Ban',
            'email' => 'ban@example.com',
            'is_anonymous' => false,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/users/{$chatUser->id}/ban", [
            'reason' => 'Violating community guidelines',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'workspace_id', 'chat_user_id', 'reason']);

        $this->assertDatabaseHas('bans', [
            'workspace_id' => $this->workspace->id,
            'chat_user_id' => $chatUser->id,
            'reason' => 'Violating community guidelines',
        ]);
    }

    public function test_can_ban_user_with_expiry(): void
    {
        $chatUser = ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'User to Ban Temporarily',
            'email' => 'tempban@example.com',
            'is_anonymous' => false,
        ]);

        $expiresAt = now()->addDays(7)->toIso8601String();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/users/{$chatUser->id}/ban", [
            'reason' => 'Temporary ban',
            'expires_at' => $expiresAt,
        ]);

        $response->assertStatus(201);

        $ban = Ban::where('chat_user_id', $chatUser->id)->first();
        $this->assertNotNull($ban->expires_at);
    }

    public function test_can_ban_user_without_reason(): void
    {
        $chatUser = ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'User to Ban',
            'is_anonymous' => false,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/users/{$chatUser->id}/ban");

        $response->assertStatus(201);

        $this->assertDatabaseHas('bans', [
            'workspace_id' => $this->workspace->id,
            'chat_user_id' => $chatUser->id,
            'reason' => null,
        ]);
    }

    public function test_ban_updates_existing_ban(): void
    {
        $chatUser = ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Already Banned User',
            'is_anonymous' => false,
        ]);

        // Create initial ban
        Ban::create([
            'workspace_id' => $this->workspace->id,
            'chat_user_id' => $chatUser->id,
            'reason' => 'Original reason',
        ]);

        // Update ban
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/users/{$chatUser->id}/ban", [
            'reason' => 'Updated reason',
        ]);

        $response->assertStatus(201);

        // Should only have one ban record
        $this->assertEquals(1, Ban::where('chat_user_id', $chatUser->id)->count());
        $this->assertEquals('Updated reason', Ban::where('chat_user_id', $chatUser->id)->first()->reason);
    }

    public function test_can_unban_user(): void
    {
        $chatUser = ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Banned User',
            'is_anonymous' => false,
        ]);

        Ban::create([
            'workspace_id' => $this->workspace->id,
            'chat_user_id' => $chatUser->id,
            'reason' => 'Test ban',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/dashboard/workspaces/{$this->workspace->id}/users/{$chatUser->id}/ban");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('bans', [
            'chat_user_id' => $chatUser->id,
        ]);
    }

    public function test_unban_nonexistent_ban_returns_204(): void
    {
        $chatUser = ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Not Banned User',
            'is_anonymous' => false,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/dashboard/workspaces/{$this->workspace->id}/users/{$chatUser->id}/ban");

        // Should succeed even if no ban exists
        $response->assertStatus(204);
    }

    public function test_banned_users_included_in_list_with_ban_data(): void
    {
        $chatUser = ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Banned User',
            'is_anonymous' => false,
            'last_seen_at' => now(),
        ]);

        Ban::create([
            'workspace_id' => $this->workspace->id,
            'chat_user_id' => $chatUser->id,
            'reason' => 'Bad behavior',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('stats.banned_users'));
        $this->assertNotNull($response->json('users.data.0.ban'));
    }

    public function test_expired_bans_not_counted(): void
    {
        $chatUser = ChatUser::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Expired Ban User',
            'is_anonymous' => false,
        ]);

        // Create an expired ban
        Ban::create([
            'workspace_id' => $this->workspace->id,
            'chat_user_id' => $chatUser->id,
            'reason' => 'Expired ban',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users");

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('stats.banned_users'));
    }

    public function test_unauthorized_without_token(): void
    {
        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users");

        $response->assertStatus(401);
    }

    public function test_cannot_access_other_workspace_users(): void
    {
        // Create another admin and workspace
        $otherAdmin = Admin::create([
            'name' => 'Other Admin',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
        ]);

        $otherWorkspace = Workspace::create([
            'name' => 'Other Workspace',
            'slug' => 'other-workspace',
            'plan' => 'free',
            'owner_id' => $otherAdmin->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $otherWorkspace->id,
            'admin_id' => $otherAdmin->id,
            'role' => 'owner',
        ]);

        ChatUser::create([
            'workspace_id' => $otherWorkspace->id,
            'name' => 'Other Workspace User',
            'is_anonymous' => false,
        ]);

        // Try to access the other workspace's users with our token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$otherWorkspace->id}/users");

        $response->assertStatus(404);
    }

    public function test_cannot_ban_user_from_other_workspace(): void
    {
        // Create another workspace
        $otherAdmin = Admin::create([
            'name' => 'Other Admin',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
        ]);

        $otherWorkspace = Workspace::create([
            'name' => 'Other Workspace',
            'slug' => 'other-workspace',
            'plan' => 'free',
            'owner_id' => $otherAdmin->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $otherWorkspace->id,
            'admin_id' => $otherAdmin->id,
            'role' => 'owner',
        ]);

        $otherUser = ChatUser::create([
            'workspace_id' => $otherWorkspace->id,
            'name' => 'Other Workspace User',
            'is_anonymous' => false,
        ]);

        // Try to ban user from other workspace
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$otherWorkspace->id}/users/{$otherUser->id}/ban", [
            'reason' => 'Unauthorized ban attempt',
        ]);

        $response->assertStatus(404);

        // Verify no ban was created
        $this->assertDatabaseMissing('bans', [
            'chat_user_id' => $otherUser->id,
        ]);
    }

    public function test_ban_nonexistent_user_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/users/nonexistent-uuid/ban", [
            'reason' => 'Test',
        ]);

        $response->assertStatus(404);
    }

    public function test_pagination_works(): void
    {
        // Create 25 users
        for ($i = 1; $i <= 25; $i++) {
            ChatUser::create([
                'workspace_id' => $this->workspace->id,
                'name' => "User {$i}",
                'is_anonymous' => false,
                'last_seen_at' => now()->subMinutes($i),
            ]);
        }

        // Get first page
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users?per_page=10");

        $response->assertStatus(200);
        $this->assertEquals(10, count($response->json('users.data')));
        $this->assertEquals(1, $response->json('users.current_page'));
        $this->assertEquals(3, $response->json('users.last_page'));
        $this->assertEquals(25, $response->json('users.total'));

        // Get second page
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users?per_page=10&page=2");

        $response->assertStatus(200);
        $this->assertEquals(10, count($response->json('users.data')));
        $this->assertEquals(2, $response->json('users.current_page'));
    }

    public function test_per_page_has_maximum_limit(): void
    {
        // Create 150 users
        for ($i = 1; $i <= 150; $i++) {
            ChatUser::create([
                'workspace_id' => $this->workspace->id,
                'name' => "User {$i}",
                'is_anonymous' => false,
            ]);
        }

        // Try to request more than 100
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/users?per_page=200");

        $response->assertStatus(200);
        // Should be capped at 100
        $this->assertEquals(100, $response->json('users.per_page'));
    }
}
