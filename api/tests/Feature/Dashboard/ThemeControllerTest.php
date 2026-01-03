<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeControllerTest extends TestCase
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
            'owner_id' => $this->admin->id,
        ]);

        // Add admin as workspace member
        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'admin_id' => $this->admin->id,
            'role' => 'owner',
        ]);

        // Create workspace theme
        WorkspaceTheme::create([
            'workspace_id' => $this->workspace->id,
            'preset' => 'professional',
            'primary_color' => '#2563eb',
            'font_family' => 'Inter, system-ui, sans-serif',
            'dark_mode_enabled' => true,
        ]);

        // Create API token for authentication
        $this->token = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_can_get_theme(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$this->workspace->id}/theme");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'workspace_id',
                'preset',
                'primary_color',
                'font_family',
                'dark_mode_enabled',
            ])
            ->assertJson([
                'preset' => 'professional',
                'primary_color' => '#2563eb',
                'dark_mode_enabled' => true,
            ]);
    }

    public function test_can_update_theme_colors(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'primary_color' => '#059669',
            'background_color' => '#f0f0f0',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'primary_color' => '#059669',
                'background_color' => '#f0f0f0',
            ]);

        // Verify persistence
        $this->assertDatabaseHas('workspace_themes', [
            'workspace_id' => $this->workspace->id,
            'primary_color' => '#059669',
            'background_color' => '#f0f0f0',
        ]);
    }

    public function test_can_update_theme_preset(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'preset' => 'minimal',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'preset' => 'minimal',
            ]);

        $this->assertDatabaseHas('workspace_themes', [
            'workspace_id' => $this->workspace->id,
            'preset' => 'minimal',
        ]);
    }

    public function test_can_update_theme_font_family(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'font_family' => 'Roboto, sans-serif',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'font_family' => 'Roboto, sans-serif',
            ]);

        $this->assertDatabaseHas('workspace_themes', [
            'workspace_id' => $this->workspace->id,
            'font_family' => 'Roboto, sans-serif',
        ]);
    }

    public function test_can_toggle_dark_mode(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'dark_mode_enabled' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'dark_mode_enabled' => false,
            ]);

        $this->assertDatabaseHas('workspace_themes', [
            'workspace_id' => $this->workspace->id,
            'dark_mode_enabled' => false,
        ]);
    }

    public function test_validates_color_format(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'primary_color' => 'invalid-color',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['primary_color']);
    }

    public function test_validates_preset_value(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'preset' => 'invalid-preset',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['preset']);
    }

    public function test_validates_position_value(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'position' => 'top-center',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['position']);
    }

    public function test_validates_font_family_max_length(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'font_family' => str_repeat('a', 101),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['font_family']);
    }

    public function test_unauthorized_without_token(): void
    {
        $response = $this->getJson("/api/dashboard/workspaces/{$this->workspace->id}/theme");

        $response->assertStatus(401);
    }

    public function test_cannot_access_other_workspace_theme(): void
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
            'owner_id' => $otherAdmin->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $otherWorkspace->id,
            'admin_id' => $otherAdmin->id,
            'role' => 'owner',
        ]);

        WorkspaceTheme::create([
            'workspace_id' => $otherWorkspace->id,
        ]);

        // Try to access other workspace's theme with our token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/dashboard/workspaces/{$otherWorkspace->id}/theme");

        $response->assertStatus(404);
    }

    public function test_cannot_update_other_workspace_theme(): void
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
            'owner_id' => $otherAdmin->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $otherWorkspace->id,
            'admin_id' => $otherAdmin->id,
            'role' => 'owner',
        ]);

        WorkspaceTheme::create([
            'workspace_id' => $otherWorkspace->id,
        ]);

        // Try to update other workspace's theme with our token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$otherWorkspace->id}/theme", [
            'primary_color' => '#ff0000',
        ]);

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_workspace(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/dashboard/workspaces/nonexistent-id/theme');

        $response->assertStatus(404);
    }

    public function test_can_update_multiple_fields_at_once(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/dashboard/workspaces/{$this->workspace->id}/theme", [
            'preset' => 'custom',
            'primary_color' => '#7c3aed',
            'font_family' => 'Open Sans, sans-serif',
            'dark_mode_enabled' => false,
            'position' => 'bottom-left',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'preset' => 'custom',
                'primary_color' => '#7c3aed',
                'font_family' => 'Open Sans, sans-serif',
                'dark_mode_enabled' => false,
                'position' => 'bottom-left',
            ]);

        $this->assertDatabaseHas('workspace_themes', [
            'workspace_id' => $this->workspace->id,
            'preset' => 'custom',
            'primary_color' => '#7c3aed',
            'font_family' => 'Open Sans, sans-serif',
            'dark_mode_enabled' => false,
            'position' => 'bottom-left',
        ]);
    }
}
