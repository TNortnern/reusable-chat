# Workspace Monitoring & Email Notifications Design

**Date:** 2026-01-16
**Status:** Approved for Implementation

## Overview

Enable tenants to monitor all activity across their workspace through two complementary systems:
1. **Real-time WebSocket monitoring** - For backend integrations, analytics, CRM syncing
2. **Email notifications** - For end-user missed message alerts with customizable templates

## Architecture

### Dual Monitoring Paths

```
Message sent → Broadcast to workspace.{id} channel
                    ↓
            Two parallel paths:

    1. WebSocket listeners        2. Email notification system
       (tenant's backend)             (optional, configurable)
            ↓                              ↓
    Tenant sends their own       We send using tenant templates
    branded emails                from email-gateway API
```

Tenants can choose to:
- Use our email system (easier, we handle it)
- Listen to WebSocket and send their own emails (more control)
- Do both (our emails for some events, theirs for others)

---

## Part 1: Workspace WebSocket Channel

### New Channel Type

**Channel:** `private-workspace.{workspaceId}`

Allows tenants to monitor all conversations in their workspace from their backend/server.

### Channel Authorization

**Location:** `api/routes/channels.php`

```php
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

### Authentication Flow

1. Tenant gets API key (existing: `X-API-Key` header for `/api/v1/*`)
2. Tenant calls new endpoint: `POST /api/v1/workspaces/{id}/realtime/token`
   - Validates API key belongs to workspace
   - Returns temporary auth token (JWT, expires in 24h)
3. Tenant connects Echo client with token
4. Subscribes to `workspace.{workspaceId}` channel
5. Receives all events for that workspace

### Events Broadcast to Workspace

**Included events** (core activity only):
- ✅ `message.created` - New messages across all conversations
- ✅ `message.deleted` - Message deletions
- ✅ `conversation.created` - New conversations in workspace

**Excluded events** (too noisy for backend monitoring):
- ❌ `user.typing` - Widget handles this for end users
- ❌ `messages.read` - Widget handles this for end users
- ❌ `reaction.added/removed` - Minor interactions

### Rich Event Payloads

All workspace events include full conversation context so tenants don't need additional API calls.

**Example: `message.created` payload**
```json
{
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
  "attachments": [
    {
      "id": "uuid",
      "name": "screenshot.png",
      "type": "image/png",
      "url": "https://...",
      "size": 102400
    }
  ],
  "created_at": "2026-01-16T10:30:00Z"
}
```

**Example: `conversation.created` payload**
```json
{
  "id": "uuid",
  "type": "group",
  "name": "Team Discussion",
  "workspace_id": "uuid",
  "created_by": {
    "id": "uuid",
    "name": "Alice Smith",
    "email": "alice@example.com"
  },
  "participants": [
    {
      "id": "uuid",
      "name": "Alice Smith",
      "role": "admin"
    },
    {
      "id": "uuid",
      "name": "Bob Johnson",
      "role": "member"
    }
  ],
  "created_at": "2026-01-16T10:00:00Z"
}
```

### Implementation Changes

**Files to modify:**
1. `api/routes/channels.php` - Add workspace channel authorization
2. `api/app/Events/MessageCreated.php` - Add workspace channel to `broadcastOn()`
3. `api/app/Events/MessageDeleted.php` - Add workspace channel to `broadcastOn()`
4. `api/app/Events/ConversationCreated.php` - Add workspace channel, enrich payload
5. `api/app/Http/Controllers/Api/V1/WorkspaceController.php` - Add `realtimeToken()` method
6. `api/routes/api.php` - Add `POST /api/v1/workspaces/{id}/realtime/token` route

**New files:**
- `api/app/Http/Requests/WorkspaceRealtimeTokenRequest.php` - Validation

---

## Part 2: Email Notification System

### Configuration Storage

**Location:** `workspace_settings` table (existing JSON column)

**Schema addition:**
```json
{
  "email_notifications": {
    "enabled": true,
    "triggers": {
      "missed_message_1on1_minutes": 15,
      "missed_message_group_minutes": 30,
      "notify_on_participant_join": true,
      "notify_on_participant_leave": false,
      "notify_on_new_inquiry": true
    }
  },
  "email_branding": {
    "logo_url": "https://tenant.com/logo.png",
    "brand_color": "#FF5733",
    "from_name": "MyApp Support",
    "reply_to": "support@tenant.com",
    "footer_text": "© 2026 MyApp. Unsubscribe anytime."
  }
}
```

### Email Templates Table

**New table:** `email_templates`

```php
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
```

**Default templates** (seeded for new workspaces):
- `missed_message_1on1` - "You have a new message from {{sender_name}}"
- `missed_message_group` - "You have {{unread_count}} unread messages in {{conversation_name}}"
- `new_participant` - "{{participant_name}} joined {{conversation_name}}"
- `new_inquiry` - "New inquiry from {{sender_name}}"

### Email Recipient Logic

**Who gets emailed:**
- Participants who haven't read the message (`participants.last_read_at` < `message.created_at`)
- Exclude sender
- Exclude anonymous users (`chat_users.is_anonymous = false`)
- Exclude users with `chat_users.email = NULL`
- Check `participants.muted_until` - skip if conversation is muted

### Email Trigger Jobs

**New queued jobs:**
- `SendMissedMessageEmail` - Dispatched after X minutes if message unread
- `SendParticipantJoinedEmail` - Dispatched when participant added
- `SendNewInquiryEmail` - Dispatched for initial message in conversation

**Job dispatch locations:**
- `MessageCreated` event listener → Schedule `SendMissedMessageEmail` (delayed by workspace setting)
- `ParticipantAdded` event listener → Dispatch `SendParticipantJoinedEmail` immediately

### Email Gateway Integration

**Service:** `https://email-gateway-production.up.railway.app`
**API Key:** `egw_live_vJetwlkKk-odOzkkbIqrlPUD5enBW0FH`

**Email sending flow:**
1. Job retrieves template from `email_templates` table
2. Renders template with variables (Blade or simple string replacement)
3. Applies branding from `workspace_settings.email_branding`
4. Calls email-gateway API with rendered HTML/text
5. Logs email in `email_logs` table (optional, for debugging)

### Implementation Changes

**New migrations:**
1. `create_email_templates_table.php`
2. `create_email_logs_table.php` (optional)

**New models:**
1. `app/Models/EmailTemplate.php`
2. `app/Models/EmailLog.php` (optional)

**New jobs:**
1. `app/Jobs/SendMissedMessageEmail.php`
2. `app/Jobs/SendParticipantJoinedEmail.php`
3. `app/Jobs/SendNewInquiryEmail.php`

**New services:**
1. `app/Services/EmailGatewayService.php` - HTTP client for email-gateway API
2. `app/Services/EmailTemplateRenderer.php` - Render templates with variables

**New seeders:**
1. `database/seeders/DefaultEmailTemplatesSeeder.php`

**Modified files:**
1. `api/app/Events/MessageCreated.php` - Add listener dispatch
2. `api/config/services.php` - Add email-gateway config

---

## API Documentation

### New Endpoint: Workspace Realtime Token

**Request:**
```http
POST /api/v1/workspaces/{id}/realtime/token
X-API-Key: sk_live_...
Content-Type: application/json
```

**Response:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_at": "2026-01-17T10:00:00Z",
  "workspace_id": "uuid",
  "channels": [
    "private-workspace.{workspace_id}"
  ]
}
```

**Usage example (JavaScript):**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Get token from API
const response = await fetch('https://api.../api/v1/workspaces/abc-123/realtime/token', {
  method: 'POST',
  headers: {
    'X-API-Key': 'sk_live_...',
  }
});
const { token, workspace_id } = await response.json();

// Connect to WebSocket
const echo = new Echo({
  broadcaster: 'pusher',
  key: 'reusable-chat-key',
  wsHost: 'api-production-de24c.up.railway.app',
  wsPort: 443,
  wssPort: 443,
  forceTLS: true,
  encrypted: true,
  disableStats: true,
  enabledTransports: ['ws', 'wss'],
  authEndpoint: 'https://api.../api/v1/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${token}`
    }
  }
});

// Subscribe to workspace channel
echo.private(`workspace.${workspace_id}`)
  .listen('.message.created', (event) => {
    console.log('New message:', event);
    // Send to your CRM, log to database, trigger workflow, etc.
  })
  .listen('.conversation.created', (event) => {
    console.log('New conversation:', event);
  })
  .listen('.message.deleted', (event) => {
    console.log('Message deleted:', event);
  });
```

---

## Testing Plan

### WebSocket Testing
1. Create workspace with API key
2. Generate realtime token
3. Connect Echo client from Node.js script
4. Subscribe to `workspace.{id}` channel
5. Send message via widget
6. Verify event received with full payload

### Email Testing
1. Configure workspace email settings
2. Create conversation with participants
3. Send message
4. Wait for delay period (or reduce for testing)
5. Verify email sent via email-gateway
6. Check email rendering with branding

---

## Future Enhancements

**Performance optimization (if needed):**
- Add `workspace_settings.broadcast_verbosity` - Toggle between minimal/rich payloads
- Implement event filtering on channel subscription
- Add rate limiting for workspace channels

**Email enhancements:**
- Per-user email preferences (`chat_users.email_preferences`)
- Email template editor UI in dashboard
- A/B testing for email templates
- Email analytics (open rates, click rates)

**Webhook alternative:**
- `POST /api/v1/workspaces/{id}/webhooks` endpoint
- Tenant registers webhook URL
- We POST events to their endpoint (fallback if WebSocket unavailable)

---

## Implementation Priority

**Phase 1: Core WebSocket** (High Priority)
- Workspace channel authorization
- Enrich event payloads
- Realtime token endpoint
- Documentation

**Phase 2: Email System** (Medium Priority)
- Email templates table
- Default templates seeder
- Email jobs
- Email gateway integration

**Phase 3: Dashboard UI** (Low Priority)
- Email template editor
- Email settings page
- Workspace monitoring dashboard

---

## Success Criteria

✅ Tenants can subscribe to workspace channel and receive events
✅ Events include rich conversation context
✅ Email notifications sent based on workspace triggers
✅ Email templates customizable per workspace
✅ Email branding applied correctly
✅ Documentation complete with code examples
✅ No performance degradation on existing channels
