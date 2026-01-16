<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkspaceRealtimeTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Verify API key belongs to the workspace
        $workspace = $this->route('workspace');
        $apiKey = $this->attributes->get('api_key'); // Set by ValidateApiKey middleware

        return $apiKey && $apiKey->workspace_id === $workspace->id;
    }

    public function rules(): array
    {
        return [];
    }
}
