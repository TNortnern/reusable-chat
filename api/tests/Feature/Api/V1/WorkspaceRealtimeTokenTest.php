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
        $plainKey = 'sk_live_test_key_12345678901234567890';
        $apiKey = ApiKey::factory()
            ->withKey($plainKey)
            ->create([
                'workspace_id' => $workspace->id,
                'name' => 'Test Key',
            ]);

        $response = $this->postJson("/api/v1/workspaces/{$workspace->id}/realtime/token", [], [
            'X-API-Key' => $plainKey,
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
        $plainKey = 'sk_live_test_key_12345678901234567890';
        $apiKey = ApiKey::factory()
            ->withKey($plainKey)
            ->create([
                'workspace_id' => $workspace1->id,
            ]);

        $response = $this->postJson("/api/v1/workspaces/{$workspace2->id}/realtime/token", [], [
            'X-API-Key' => $plainKey,
        ]);

        $response->assertForbidden();
    }
}
