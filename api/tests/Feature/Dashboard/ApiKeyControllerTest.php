<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\ApiKey;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyControllerTest extends TestCase
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

    public function test_can_list_api_keys(): void
    {
        // Create some API keys
        ApiKey::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Production Key',
            'key_hash' => hash('sha256', 'sk_live_test123'),
            'key_prefix' => 'sk_live_test...',
        ]);

        ApiKey::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Development Key',
            'key_hash' => hash('sha256', 'sk_test_dev456'),
            'key_prefix' => 'sk_test_dev4...',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'key_prefix', 'created_at'],
            ]);
    }

    public function test_does_not_list_revoked_api_keys(): void
    {
        // Create an active key
        ApiKey::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Active Key',
            'key_hash' => hash('sha256', 'sk_live_active'),
            'key_prefix' => 'sk_live_acti...',
        ]);

        // Create a revoked key
        ApiKey::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Revoked Key',
            'key_hash' => hash('sha256', 'sk_live_revoked'),
            'key_prefix' => 'sk_live_revo...',
            'revoked_at' => now(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Active Key']);
    }

    public function test_can_create_api_key(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys", [
            'name' => 'New API Key',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'key',
                'key_prefix',
                'created_at',
            ])
            ->assertJsonFragment(['name' => 'New API Key']);

        // Verify the key starts with sk_live_
        $this->assertStringStartsWith('sk_live_', $response->json('key'));

        // Verify key is stored in database
        $this->assertDatabaseHas('api_keys', [
            'workspace_id' => $this->workspace->id,
            'name' => 'New API Key',
        ]);
    }

    public function test_create_api_key_requires_name(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_api_key_name_max_length(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys", [
            'name' => str_repeat('a', 101),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_delete_api_key(): void
    {
        $apiKey = ApiKey::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Key to Delete',
            'key_hash' => hash('sha256', 'sk_live_delete'),
            'key_prefix' => 'sk_live_dele...',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys/{$apiKey->id}");

        $response->assertStatus(204);

        // Verify the key is soft-deleted (revoked)
        $apiKey->refresh();
        $this->assertNotNull($apiKey->revoked_at);
    }

    public function test_cannot_delete_nonexistent_api_key(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys/nonexistent-id");

        $response->assertStatus(404);
    }

    public function test_unauthorized_without_token(): void
    {
        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys");

        $response->assertStatus(401);
    }

    public function test_cannot_access_other_workspace_api_keys(): void
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

        // Try to access the other workspace's API keys with our token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$otherWorkspace->id}/api-keys");

        $response->assertStatus(404);
    }

    public function test_cannot_delete_api_key_from_other_workspace(): void
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

        $otherApiKey = ApiKey::create([
            'workspace_id' => $otherWorkspace->id,
            'name' => 'Other Key',
            'key_hash' => hash('sha256', 'sk_live_other'),
            'key_prefix' => 'sk_live_othe...',
        ]);

        // Try to delete the other workspace's API key
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/dashboard/workspaces/{$otherWorkspace->id}/api-keys/{$otherApiKey->id}");

        $response->assertStatus(404);

        // Verify the key was not deleted
        $otherApiKey->refresh();
        $this->assertNull($otherApiKey->revoked_at);
    }

    public function test_api_key_hash_is_stored_correctly(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys", [
            'name' => 'Hash Test Key',
        ]);

        $response->assertStatus(201);

        $plainKey = $response->json('key');
        $apiKey = ApiKey::where('name', 'Hash Test Key')->first();

        // Verify the hash matches
        $this->assertEquals(hash('sha256', $plainKey), $apiKey->key_hash);
    }

    public function test_api_key_prefix_is_stored_correctly(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/dashboard/workspaces/{$this->workspace->id}/api-keys", [
            'name' => 'Prefix Test Key',
        ]);

        $response->assertStatus(201);

        $plainKey = $response->json('key');
        $keyPrefix = $response->json('key_prefix');

        // Verify the prefix is the first 12 chars + ...
        $this->assertEquals(substr($plainKey, 0, 12) . '...', $keyPrefix);
    }
}
