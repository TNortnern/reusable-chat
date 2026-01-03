<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Workspace $workspace;
    private WorkspaceSettings $settings;

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

        $this->settings = WorkspaceSettings::create([
            'workspace_id' => $this->workspace->id,
            'read_receipts_enabled' => true,
            'online_status_enabled' => true,
            'typing_indicators_enabled' => true,
            'file_size_limit_mb' => 10,
            'rate_limit_per_minute' => 30,
            'webhook_url' => null,
            'webhook_secret' => null,
        ]);
    }

    public function test_can_get_workspace_settings(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/settings");

        $response->assertOk()
            ->assertJsonStructure([
                'read_receipts_enabled',
                'online_status_enabled',
                'typing_indicators_enabled',
                'file_size_limit_mb',
                'rate_limit_per_minute',
                'webhook_url',
                'webhook_secret',
            ])
            ->assertJson([
                'read_receipts_enabled' => true,
                'online_status_enabled' => true,
                'typing_indicators_enabled' => true,
                'file_size_limit_mb' => 10,
                'rate_limit_per_minute' => 30,
            ]);
    }

    public function test_can_update_workspace_settings(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $payload = [
            'read_receipts_enabled' => false,
            'online_status_enabled' => false,
            'typing_indicators_enabled' => false,
            'file_size_limit_mb' => 25,
            'rate_limit_per_minute' => 60,
            'webhook_url' => 'https://example.com/webhook',
            'webhook_secret' => 'my-secret-key',
        ];

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", $payload);

        $response->assertOk()
            ->assertJson([
                'read_receipts_enabled' => false,
                'online_status_enabled' => false,
                'typing_indicators_enabled' => false,
                'file_size_limit_mb' => 25,
                'rate_limit_per_minute' => 60,
                'webhook_url' => 'https://example.com/webhook',
                'webhook_secret' => 'my-secret-key',
            ]);

        $this->assertDatabaseHas('workspace_settings', [
            'workspace_id' => $this->workspace->id,
            'read_receipts_enabled' => false,
            'online_status_enabled' => false,
            'typing_indicators_enabled' => false,
            'file_size_limit_mb' => 25,
            'rate_limit_per_minute' => 60,
            'webhook_url' => 'https://example.com/webhook',
            'webhook_secret' => 'my-secret-key',
        ]);
    }

    public function test_can_partially_update_settings(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'read_receipts_enabled' => false,
        ]);

        $response->assertOk()
            ->assertJson([
                'read_receipts_enabled' => false,
                'online_status_enabled' => true,
                'typing_indicators_enabled' => true,
            ]);

        $this->assertDatabaseHas('workspace_settings', [
            'workspace_id' => $this->workspace->id,
            'read_receipts_enabled' => false,
            'online_status_enabled' => true,
        ]);
    }

    public function test_validation_fails_for_invalid_file_size(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'file_size_limit_mb' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_size_limit_mb']);
    }

    public function test_validation_fails_for_file_size_too_small(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'file_size_limit_mb' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_size_limit_mb']);
    }

    public function test_validation_fails_for_invalid_rate_limit(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'rate_limit_per_minute' => 500,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rate_limit_per_minute']);
    }

    public function test_validation_fails_for_rate_limit_too_small(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'rate_limit_per_minute' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rate_limit_per_minute']);
    }

    public function test_validation_fails_for_invalid_webhook_url(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'webhook_url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['webhook_url']);
    }

    public function test_validation_fails_for_webhook_url_too_long(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'webhook_url' => 'https://example.com/' . str_repeat('a', 500),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['webhook_url']);
    }

    public function test_validation_fails_for_webhook_secret_too_long(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'webhook_secret' => str_repeat('a', 150),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['webhook_secret']);
    }

    public function test_unauthenticated_user_cannot_access_settings(): void
    {
        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/settings");

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_update_settings(): void
    {
        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'read_receipts_enabled' => false,
        ]);

        $response->assertUnauthorized();
    }

    public function test_non_member_cannot_access_settings(): void
    {
        $otherAdmin = Admin::factory()->create();
        Sanctum::actingAs($otherAdmin, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/settings");

        $response->assertNotFound();
    }

    public function test_non_member_cannot_update_settings(): void
    {
        $otherAdmin = Admin::factory()->create();
        Sanctum::actingAs($otherAdmin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'read_receipts_enabled' => false,
        ]);

        $response->assertNotFound();
    }

    public function test_returns_404_for_non_existent_workspace(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/dashboard/workspaces/{$fakeId}/settings");

        $response->assertNotFound();
    }

    public function test_member_can_access_settings(): void
    {
        $member = Admin::factory()->create();

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'admin_id' => $member->id,
            'role' => 'member',
        ]);

        Sanctum::actingAs($member, ['*']);

        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/settings");

        $response->assertOk();
    }

    public function test_can_clear_webhook_url(): void
    {
        $this->settings->update([
            'webhook_url' => 'https://example.com/webhook',
            'webhook_secret' => 'secret',
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'webhook_url' => null,
            'webhook_secret' => null,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('workspace_settings', [
            'workspace_id' => $this->workspace->id,
            'webhook_url' => null,
            'webhook_secret' => null,
        ]);
    }

    public function test_accepts_boolean_values_as_integers(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'read_receipts_enabled' => 0,
            'online_status_enabled' => 1,
        ]);

        $response->assertOk()
            ->assertJson([
                'read_receipts_enabled' => false,
                'online_status_enabled' => true,
            ]);
    }

    public function test_boundary_values_for_file_size(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'file_size_limit_mb' => 1,
        ]);
        $response->assertOk();

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'file_size_limit_mb' => 50,
        ]);
        $response->assertOk();
    }

    public function test_boundary_values_for_rate_limit(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'rate_limit_per_minute' => 10,
        ]);
        $response->assertOk();

        $response = $this->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/settings", [
            'rate_limit_per_minute' => 300,
        ]);
        $response->assertOk();
    }
}
