# Chat Widget Embedding Guide

This guide explains how to embed the Reusable Chat widget into your website or application.

---

## Quick Start

Add this script tag to your HTML page:

```html
<script
  src="https://hastets.b-cdn.net/widget/widget.iife.js"
  data-workspace-id="YOUR_WORKSPACE_ID"
  data-api-url="https://api-production-de24c.up.railway.app"
  data-ws-host="api-production-de24c.up.railway.app"
  data-ws-key="reusable-chat-key"
></script>
```

Replace `YOUR_WORKSPACE_ID` with your actual workspace ID from the dashboard.

---

## Configuration Options

### Required Attributes

| Attribute | Description |
|-----------|-------------|
| `data-workspace-id` | Your workspace ID (found in dashboard settings) |
| `data-api-url` | API endpoint URL |
| `data-ws-host` | WebSocket server host |
| `data-ws-key` | WebSocket authentication key |

### Optional Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `data-user-token` | - | Pre-authenticated user session token |
| `data-user-id` | - | User ID for authenticated sessions |
| `data-position` | `bottom-right` | Widget position: `bottom-right` or `bottom-left` |
| `data-title` | `Chat with us` | Chat window title |
| `data-subtitle` | `We typically reply in a few minutes` | Subtitle text |
| `data-welcome-message` | - | Initial welcome message |
| `data-placeholder` | `Type a message...` | Input placeholder text |

---

## Integration Methods

### Method 1: Anonymous Chat (Support Widget)

For anonymous visitor chat (e.g., customer support), use just the basic configuration:

```html
<script
  src="https://hastets.b-cdn.net/widget/widget.iife.js"
  data-workspace-id="ws_abc123"
  data-api-url="https://api-production-de24c.up.railway.app"
  data-ws-host="api-production-de24c.up.railway.app"
  data-ws-key="reusable-chat-key"
  data-title="Need Help?"
  data-subtitle="Our team is online"
></script>
```

### Method 2: Authenticated Chat (User-to-User)

For authenticated users (e.g., marketplace messaging), first create a session from your backend:

**Backend (PHP/Laravel example):**
```php
// Create a chat session for the user
$response = Http::withHeaders([
    'X-API-Key' => env('CHAT_API_KEY'),
])->post('https://api-production-de24c.up.railway.app/api/v1/sessions', [
    'user_id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
]);

$session = $response->json();
$userToken = $session['token'];
```

**Frontend:**
```html
<script
  src="https://hastets.b-cdn.net/widget/widget.iife.js"
  data-workspace-id="ws_abc123"
  data-api-url="https://api-production-de24c.up.railway.app"
  data-ws-host="api-production-de24c.up.railway.app"
  data-ws-key="reusable-chat-key"
  data-user-token="<?php echo $userToken; ?>"
  data-user-id="<?php echo $user->id; ?>"
></script>
```

### Method 3: Web Component

You can also use the widget as a custom HTML element:

```html
<script src="https://hastets.b-cdn.net/widget/widget.iife.js"></script>

<chat-widget
  workspace-id="ws_abc123"
  api-url="https://api-production-de24c.up.railway.app"
  ws-host="api-production-de24c.up.railway.app"
  ws-key="reusable-chat-key"
  position="bottom-left"
  title="Chat Support"
></chat-widget>
```

### Method 4: JavaScript API

For programmatic control:

```html
<script src="https://hastets.b-cdn.net/widget/widget.iife.js"></script>

<script>
  // Create widget instance
  const widget = new window.ChatWidget({
    workspaceId: 'ws_abc123',
    apiUrl: 'https://api-production-de24c.up.railway.app',
    wsHost: 'api-production-de24c.up.railway.app',
    wsKey: 'reusable-chat-key',
    position: 'bottom-right',
    title: 'Chat with us',
  });

  // Initialize
  widget.init();

  // Later: destroy widget
  // widget.destroy();

  // Update config dynamically
  // widget.updateConfig({ title: 'New Title' });
</script>
```

---

## Styling & Customization

### Theme Presets

Apply preset themes by passing theme configuration:

```html
<script
  src="https://hastets.b-cdn.net/widget/widget.iife.js"
  data-workspace-id="ws_abc123"
  data-api-url="https://api-production-de24c.up.railway.app"
  data-ws-host="api-production-de24c.up.railway.app"
  data-ws-key="reusable-chat-key"
  data-primary-color="#059669"
  data-background-color="#f0fdf4"
></script>
```

### Available Theme Variables

| Variable | Description |
|----------|-------------|
| `data-primary-color` | Main accent color (buttons, links) |
| `data-background-color` | Widget background color |

---

## API Reference

### Creating Sessions (Backend)

**Endpoint:** `POST /api/v1/sessions`

**Headers:**
```
X-API-Key: your_api_key
Content-Type: application/json
```

**Request Body:**
```json
{
  "user_id": "user_123",
  "name": "John Doe",
  "email": "john@example.com",
  "metadata": {
    "plan": "premium",
    "company": "Acme Inc"
  }
}
```

**Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": "uuid-here",
    "name": "John Doe",
    "email": "john@example.com"
  },
  "expires_at": "2025-01-10T00:00:00Z"
}
```

### Getting API Keys

1. Log in to the dashboard at https://dashboard-production-8985.up.railway.app
2. Go to **Settings** > **API Keys**
3. Click **Create New Key**
4. Copy and securely store your API key

---

## mywatercloset Integration Example

For mywatercloset marketplace integration:

### 1. Backend Setup (Laravel)

```php
// config/services.php
'reusable_chat' => [
    'api_key' => env('REUSABLE_CHAT_API_KEY'),
    'api_url' => env('REUSABLE_CHAT_API_URL', 'https://api-production-de24c.up.railway.app'),
    'workspace_id' => env('REUSABLE_CHAT_WORKSPACE_ID'),
],

// app/Services/ChatService.php
class ChatService
{
    public function createSession(User $user): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => config('services.reusable_chat.api_key'),
        ])->post(config('services.reusable_chat.api_url') . '/api/v1/sessions', [
            'user_id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return $response->json();
    }
}
```

### 2. Frontend Integration (Blade/Vue)

```blade
@auth
<script
  src="https://hastets.b-cdn.net/widget/widget.iife.js"
  data-workspace-id="{{ config('services.reusable_chat.workspace_id') }}"
  data-api-url="{{ config('services.reusable_chat.api_url') }}"
  data-ws-host="api-production-de24c.up.railway.app"
  data-ws-key="reusable-chat-key"
  data-user-token="{{ $chatSession['token'] }}"
  data-user-id="{{ auth()->id() }}"
  data-title="Messages"
></script>
@endauth
```

---

## Troubleshooting

### Widget not appearing

1. Check browser console for JavaScript errors
2. Verify all required `data-` attributes are set
3. Ensure the API URL is accessible (no CORS issues)

### WebSocket connection failing

1. Check that `data-ws-host` and `data-ws-key` are correct
2. Verify WebSocket port 443 is not blocked
3. Check browser console for WebSocket errors

### Messages not sending

1. Ensure user has a valid session token
2. Check API response for error messages
3. Verify the workspace ID is correct

---

## Support

- Dashboard: https://dashboard-production-8985.up.railway.app
- Demo: https://dashboard-production-8985.up.railway.app/demo
- API Docs: Contact support for full API documentation
