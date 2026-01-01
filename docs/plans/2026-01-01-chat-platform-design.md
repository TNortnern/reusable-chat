# Reusable Chat Platform - Design Document

## Overview

A multi-tenant SaaS chat platform with an embeddable widget that consumers can brand and deploy on their websites. Built with Laravel + Reverb (backend) and Nuxt (frontend).

**Key Value Proposition:**
- Consumers embed a simple script tag or web component
- Full branding control (themes, colors, CSS)
- Real-time messaging with modern UX
- No source code distribution — fully hosted SaaS

---

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────────┐
│                         RAILWAY                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │  Laravel    │  │  Laravel    │  │   Nuxt      │              │
│  │  API        │  │  Reverb     │  │   Dashboard │              │
│  │  (REST)     │  │  (WebSocket)│  │   (SSR)     │              │
│  └──────┬──────┘  └──────┬──────┘  └─────────────┘              │
│         │                │                                       │
│         └────────┬───────┘                                       │
│                  ▼                                               │
│         ┌─────────────┐     ┌─────────────┐                     │
│         │   Redis     │     │  PostgreSQL │                     │
│         │  (pub/sub)  │     │  (database) │                     │
│         └─────────────┘     └─────────────┘                     │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────┐     ┌─────────────────┐
│   Bunny CDN     │     │     Brevo       │
│   (files)       │     │   (emails)      │
└─────────────────┘     └─────────────────┘
```

### Services

| Service | Purpose | Technology |
|---------|---------|------------|
| Laravel API | REST endpoints, auth, business logic | Laravel 11+ |
| Laravel Reverb | WebSocket server for real-time | Laravel Reverb |
| Nuxt Dashboard | Admin panel + widget renderer | Nuxt 3 + Nuxt UI |
| Redis | Pub/sub, caching, rate limiting | Redis 7 |
| PostgreSQL | Primary database | PostgreSQL 16 |
| Bunny CDN | File storage and delivery | Bunny Storage |
| Brevo | Transactional emails | Brevo API |

### Scaling Strategy

Reverb scales horizontally via Redis pub/sub:

```
                    Load Balancer (Railway)
                           │
        ┌──────────────────┼──────────────────┐
        ▼                  ▼                  ▼
   Reverb #1          Reverb #2          Reverb #3
        │                  │                  │
        └──────────────────┼──────────────────┘
                           │
                       Redis (pub/sub)
```

---

## Monorepo Structure

```
reusable-chat/
├── docker-compose.yml          # Local dev orchestration
├── docker-compose.prod.yml     # Production config
├── .env.example
├── README.md
│
├── api/                        # Laravel application
│   ├── Dockerfile
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── ...
│
├── dashboard/                  # Nuxt application
│   ├── Dockerfile
│   ├── nuxt.config.ts
│   ├── components/
│   ├── pages/
│   └── ...
│
├── widget/                     # Embeddable chat widget
│   ├── src/
│   │   ├── widget.ts           # Web component definition
│   │   ├── iframe-manager.ts   # Creates/manages iframe
│   │   └── events.ts           # postMessage handling
│   ├── dist/
│   │   └── widget.min.js       # ~8-10KB bundle
│   └── vite.config.ts
│
└── docs/
    └── plans/
```

### Docker Compose (Local Dev)

```yaml
services:
  api:
    build: ./api
    ports: ["8000:8000"]
    volumes: ["./api:/var/www/html"]
    depends_on: [postgres, redis]

  reverb:
    build: ./api
    command: php artisan reverb:start
    ports: ["8080:8080"]
    depends_on: [redis]

  dashboard:
    build: ./dashboard
    ports: ["3000:3000"]
    volumes: ["./dashboard:/app"]

  postgres:
    image: postgres:16-alpine
    ports: ["5432:5432"]
    environment:
      POSTGRES_DB: chat
      POSTGRES_USER: chat
      POSTGRES_PASSWORD: secret

  redis:
    image: redis:7-alpine
    ports: ["6379:6379"]
```

---

## Widget Architecture

### Hybrid Embed Strategy

Two ways to embed, same underlying tech:

**1. Auto-inject mode (simple sites)**
```html
<script src="https://yourchat.com/widget.js" data-workspace="abc"></script>
```

**2. Web Component mode (React/Vue/Svelte)**
```tsx
<chat-widget
  workspace="acme-corp"
  user-token={sessionToken}
  position="bottom-right"
  theme="dark"
/>
```

### How It Works

```
Consumer's Site                    Your Infrastructure
┌─────────────────────┐           ┌─────────────────────┐
│                     │           │                     │
│  <script>           │           │   Nuxt Dashboard    │
│    widget.js        │──iframe──▶│   /widget/:token    │
│  </script>          │           │                     │
│                     │           │   (renders chat UI) │
└─────────────────────┘           └─────────────────────┘
```

- **widget.js** (~8-10KB) creates iframe + web component wrapper
- Nuxt renders the chat UI at `/widget/:session-token`
- Style isolation via iframe
- Events via CustomEvent + postMessage

### Web Component API

```typescript
// Properties
workspace: string       // Workspace slug
userToken: string       // Session token from backend
position: 'bottom-right' | 'bottom-left'
theme: 'light' | 'dark' | 'auto'

// Events
@message    // New message received
@open       // Widget opened
@close      // Widget closed
@ready      // Widget initialized

// Methods
element.open()
element.close()
element.toggle()
```

---

## Database Schema

### Multi-tenancy

```sql
-- Workspaces (one per consumer)
CREATE TABLE workspaces (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    plan VARCHAR(20) DEFAULT 'free', -- free, pro, enterprise
    owner_id UUID REFERENCES admins(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Workspace Settings
CREATE TABLE workspace_settings (
    workspace_id UUID PRIMARY KEY REFERENCES workspaces(id),
    read_receipts_enabled BOOLEAN DEFAULT true,
    online_status_enabled BOOLEAN DEFAULT true,
    typing_indicators_enabled BOOLEAN DEFAULT true,
    file_size_limit_mb INTEGER DEFAULT 10,
    rate_limit_per_minute INTEGER DEFAULT 60,
    webhook_url VARCHAR(500),
    webhook_secret VARCHAR(100)
);

-- Workspace Themes
CREATE TABLE workspace_themes (
    workspace_id UUID PRIMARY KEY REFERENCES workspaces(id),
    preset VARCHAR(20) DEFAULT 'professional', -- minimal, playful, professional, custom
    primary_color VARCHAR(7),
    background_color VARCHAR(7),
    font_family VARCHAR(100),
    logo_url VARCHAR(500),
    position VARCHAR(20) DEFAULT 'bottom-right',
    custom_css TEXT, -- pro/enterprise only
    dark_mode_enabled BOOLEAN DEFAULT true
);

-- API Keys
CREATE TABLE api_keys (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id),
    name VARCHAR(100),
    key_hash VARCHAR(255) NOT NULL, -- hashed
    key_prefix VARCHAR(10), -- for display: "sk_live_abc..."
    last_used_at TIMESTAMP,
    revoked_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### Users

```sql
-- Platform Admins (your users)
CREATE TABLE admins (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    is_super_admin BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Workspace Members (admins assigned to workspaces)
CREATE TABLE workspace_members (
    workspace_id UUID REFERENCES workspaces(id),
    admin_id UUID REFERENCES admins(id),
    role VARCHAR(20) DEFAULT 'agent', -- owner, admin, agent
    PRIMARY KEY (workspace_id, admin_id)
);

-- Chat Users (consumer's end users)
CREATE TABLE chat_users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id),
    external_id VARCHAR(255), -- ID from consumer's system
    name VARCHAR(255),
    email VARCHAR(255),
    avatar_url VARCHAR(500),
    metadata JSONB DEFAULT '{}', -- flexible data from consumer
    is_anonymous BOOLEAN DEFAULT false,
    last_seen_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(workspace_id, external_id)
);

-- Chat Sessions
CREATE TABLE sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id),
    chat_user_id UUID REFERENCES chat_users(id),
    token VARCHAR(255) UNIQUE NOT NULL,
    context JSONB DEFAULT '{}', -- transaction_id, order details, etc.
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### Conversations

```sql
-- Conversations
CREATE TABLE conversations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id),
    type VARCHAR(20) DEFAULT 'direct', -- direct, group
    name VARCHAR(255), -- for groups
    created_by UUID REFERENCES chat_users(id),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Conversation Participants
CREATE TABLE participants (
    conversation_id UUID REFERENCES conversations(id),
    chat_user_id UUID REFERENCES chat_users(id),
    role VARCHAR(20) DEFAULT 'member', -- member, admin
    joined_at TIMESTAMP DEFAULT NOW(),
    last_read_at TIMESTAMP,
    muted_until TIMESTAMP,
    PRIMARY KEY (conversation_id, chat_user_id)
);

-- Messages
CREATE TABLE messages (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    conversation_id UUID REFERENCES conversations(id),
    sender_id UUID REFERENCES chat_users(id),
    content TEXT,
    type VARCHAR(20) DEFAULT 'text', -- text, system
    metadata JSONB DEFAULT '{}',
    deleted_at TIMESTAMP, -- soft delete
    created_at TIMESTAMP DEFAULT NOW()
);

-- Attachments
CREATE TABLE attachments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    message_id UUID REFERENCES messages(id),
    filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes INTEGER NOT NULL,
    url VARCHAR(500) NOT NULL, -- Bunny CDN URL
    created_at TIMESTAMP DEFAULT NOW()
);

-- Reactions
CREATE TABLE reactions (
    message_id UUID REFERENCES messages(id),
    chat_user_id UUID REFERENCES chat_users(id),
    emoji VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    PRIMARY KEY (message_id, chat_user_id, emoji)
);
```

### Moderation

```sql
-- Bans
CREATE TABLE bans (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id),
    chat_user_id UUID REFERENCES chat_users(id),
    banned_by UUID REFERENCES admins(id),
    reason TEXT,
    expires_at TIMESTAMP, -- null = permanent
    created_at TIMESTAMP DEFAULT NOW()
);

-- User Blocks (user-to-user)
CREATE TABLE blocks (
    workspace_id UUID REFERENCES workspaces(id),
    blocker_id UUID REFERENCES chat_users(id),
    blocked_id UUID REFERENCES chat_users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    PRIMARY KEY (workspace_id, blocker_id, blocked_id)
);
```

---

## API Endpoints

### Consumer Backend API (`/api/v1/`) — API Key Auth

```
Sessions & Users
POST   /sessions              Create chat session
DELETE /sessions/:id          Revoke session
POST   /users                 Upsert chat user
GET    /users/:external_id    Get user by external ID

Conversations
POST   /conversations         Create conversation
GET    /conversations         List conversations for user
POST   /conversations/:id/participants   Add to group

Moderation
POST   /users/:id/ban         Ban user
DELETE /users/:id/ban         Unban user
GET    /bans                  List banned users

Data Export
GET    /export/conversations  Export conversations (CSV/JSON)
GET    /export/messages       Export messages
```

### Widget API (`/api/widget/`) — Session Token Auth

```
GET    /me                    Current user + settings
GET    /conversations         List conversations
POST   /conversations         Start new conversation
GET    /conversations/:id     Get with messages
POST   /conversations/:id/messages      Send message
POST   /conversations/:id/messages/:id/reactions   Add reaction
DELETE /conversations/:id/messages/:id/reactions/:emoji   Remove
POST   /conversations/:id/read          Mark read
POST   /conversations/:id/typing        Typing indicator
POST   /attachments           Upload file
POST   /users/:id/block       Block user
DELETE /users/:id/block       Unblock
```

### Dashboard API (`/api/dashboard/`) — Admin JWT Auth

```
Auth
POST   /auth/login
POST   /auth/logout
GET    /auth/me

Workspaces
GET    /workspaces
POST   /workspaces
GET    /workspaces/:id
PATCH  /workspaces/:id

Settings & Theming
GET    /workspaces/:id/settings
PATCH  /workspaces/:id/settings
GET    /workspaces/:id/theme
PATCH  /workspaces/:id/theme

API Keys
GET    /workspaces/:id/api-keys
POST   /workspaces/:id/api-keys
DELETE /workspaces/:id/api-keys/:id

Conversations & Moderation
GET    /workspaces/:id/conversations
GET    /workspaces/:id/conversations/:id
DELETE /workspaces/:id/messages/:id
GET    /workspaces/:id/users
POST   /workspaces/:id/users/:id/ban
DELETE /workspaces/:id/users/:id/ban

Analytics
GET    /workspaces/:id/analytics
GET    /workspaces/:id/analytics/messages
GET    /workspaces/:id/analytics/users
```

### Webhooks (Outgoing)

```json
{
  "event": "message.created",
  "workspace_id": "...",
  "timestamp": "2025-01-01T12:00:00Z",
  "data": { ... }
}
```

Events: `message.created`, `conversation.created`, `user.joined`, `user.banned`

---

## WebSocket Events (Laravel Reverb)

### Channel Structure

```
private-conversation.{id}    Per-conversation events
private-user.{id}            User-specific notifications
presence-conversation.{id}   Online presence in conversation
```

### Conversation Channel Events

```typescript
// New message
{ event: "message.created", data: { id, content, sender, attachments, created_at } }

// Message deleted
{ event: "message.deleted", data: { id, deleted_by } }

// Reaction added/removed
{ event: "reaction.added", data: { message_id, user_id, emoji } }
{ event: "reaction.removed", data: { message_id, user_id, emoji } }

// Typing indicator
{ event: "user.typing", data: { user_id, name } }

// Read receipt
{ event: "messages.read", data: { user_id, read_at } }

// Participant changes
{ event: "participant.joined", data: { user } }
{ event: "participant.left", data: { user_id } }
```

### User Channel Events

```typescript
// New conversation
{ event: "conversation.created", data: { id, type, participants } }

// Unread count
{ event: "unread.updated", data: { conversation_id, unread_count } }

// Blocked/Banned
{ event: "blocked", data: { blocked_by } }
{ event: "banned", data: { reason, expires_at } }
```

### Presence Channel

Handled automatically by Reverb:
- `pusher:subscription_succeeded` — Current members
- `pusher:member_added` — Someone came online
- `pusher:member_removed` — Someone went offline

---

## Frontend Architecture (Nuxt)

### Directory Structure

```
dashboard/
├── nuxt.config.ts
├── app.vue
│
├── components/
│   ├── ui/                    # Extended Nuxt UI
│   ├── chat/
│   │   ├── ChatWindow.vue
│   │   ├── MessageList.vue
│   │   ├── MessageBubble.vue
│   │   ├── MessageInput.vue
│   │   ├── EmojiPicker.vue
│   │   ├── ReactionBar.vue
│   │   ├── TypingIndicator.vue
│   │   └── AttachmentPreview.vue
│   ├── conversations/
│   │   ├── ConversationList.vue
│   │   └── ConversationItem.vue
│   └── dashboard/
│       ├── Sidebar.vue
│       ├── Analytics.vue
│       └── UserTable.vue
│
├── composables/
│   ├── useChat.ts             # Chat state + actions
│   ├── useReverb.ts           # WebSocket connection
│   ├── useAuth.ts             # Admin authentication
│   └── useTheme.ts            # Dynamic theming
│
├── stores/
│   ├── conversations.ts
│   ├── messages.ts
│   ├── presence.ts
│   └── notifications.ts
│
├── pages/
│   ├── index.vue              # Landing
│   ├── login.vue
│   ├── dashboard/
│   │   ├── index.vue
│   │   ├── conversations/
│   │   ├── users/
│   │   ├── analytics.vue
│   │   ├── settings.vue
│   │   └── theme.vue
│   └── widget/
│       └── [token].vue        # Widget renderer
│
└── layouts/
    ├── default.vue
    ├── dashboard.vue
    └── widget.vue
```

### State Management

```typescript
// composables/useChat.ts
export function useChat(conversationId: string) {
  const messages = useMessagesStore()
  const presence = usePresenceStore()
  const { channel } = useReverb()

  onMounted(() => {
    channel(`private-conversation.${conversationId}`)
      .listen('message.created', (e) => messages.add(e.data))
      .listen('user.typing', (e) => presence.setTyping(e.data))
      .listen('reaction.added', (e) => messages.addReaction(e.data))
  })

  const sendMessage = async (content: string, attachments?: File[]) => {
    const tempMessage = messages.addOptimistic(content)
    try {
      const result = await $fetch(`/api/widget/conversations/${conversationId}/messages`, {
        method: 'POST',
        body: { content, attachments }
      })
      messages.confirmOptimistic(tempMessage.id, result)
    } catch (error) {
      messages.removeOptimistic(tempMessage.id)
      throw error
    }
  }

  return { messages, sendMessage, presence }
}
```

---

## UI Design System

### Theme Presets

Four presets consumers choose from, then customize:

| Preset | Style | Accent | Font | Vibe |
|--------|-------|--------|------|------|
| **Minimal** | Clean, muted | Near-black | Inter | Understated |
| **Playful** | Warm, rounded | Orange | Nunito | Friendly |
| **Professional** | Corporate, refined | Deep blue | Satoshi + Inter | Trustworthy |
| **Custom** | User-defined | Any | Any | Any |

### Design Tokens

```css
:root {
  /* Surfaces */
  --chat-bg-primary: #fafafa;
  --chat-bg-secondary: #ffffff;
  --chat-bg-tertiary: #f3f4f6;

  /* Accent */
  --chat-accent: #2563eb;
  --chat-accent-soft: #dbeafe;

  /* Text */
  --chat-text-primary: #111827;
  --chat-text-secondary: #6b7280;
  --chat-text-inverse: #ffffff;

  /* Bubbles */
  --chat-bubble-sent: var(--chat-accent);
  --chat-bubble-received: var(--chat-bg-tertiary);

  /* Shadows */
  --chat-shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
  --chat-shadow-md: 0 4px 12px rgba(0,0,0,0.06);

  /* Typography */
  --chat-font-display: 'Satoshi', system-ui;
  --chat-font-body: 'Inter', system-ui;

  /* Borders */
  --chat-radius-bubble: 18px;
}

[data-theme="dark"] {
  --chat-bg-primary: #0f0f0f;
  --chat-bg-secondary: #1a1a1a;
  --chat-bg-tertiary: #262626;
  --chat-text-primary: #fafafa;
  --chat-text-secondary: #a1a1aa;
}
```

### Typography

| Element | Font | Size | Weight |
|---------|------|------|--------|
| Dashboard headers | Satoshi | 28px | 700 |
| Conversation name | Satoshi | 18px | 600 |
| Message text | Inter | 15px | 400 |
| Timestamp | Inter | 12px | 400 |

### Animations

```css
/* Message appear */
@keyframes messageSlideIn {
  from { opacity: 0; transform: translateY(8px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Typing indicator */
@keyframes typingPulse {
  0%, 60%, 100% { opacity: 0.4; transform: scale(1); }
  30% { opacity: 1; transform: scale(1.1); }
}

/* Reaction pop */
@keyframes reactionPop {
  0% { transform: scale(0); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}
```

---

## Features Summary

### MVP Features

**Chat:**
- 1-to-1 direct messages
- Group chats (simple, not channels)
- Native emoji picker
- Message reactions
- File attachments (images + docs, max 10MB)
- Typing indicators
- Read receipts (optional per workspace)
- Online status (optional per workspace)
- Unread count badges

**Participants:**
- Authenticated users (JWT from consumer)
- Anonymous users (transaction-based sessions)

**Moderation:**
- Delete messages
- Ban users (temp or permanent)
- User-to-user blocking
- Rate limiting

**Dashboard:**
- Conversation list
- User management
- Analytics (message volume, active users)
- Settings configuration
- Data export (CSV/JSON)

**Theming:**
- 4 presets (minimal, playful, professional, custom)
- Color customization
- Dark mode support
- Custom CSS (pro/enterprise)

**Notifications:**
- Browser push (optional)
- Email via Brevo
- Webhooks to consumer

### Post-MVP Features

- Agent assignment / routing
- Canned responses
- Profanity filter
- Spam detection
- Custom emoji uploads
- Threaded replies
- Search messages

---

## External Integrations

### Bunny CDN (File Storage)

- Storage zone for file uploads
- CDN delivery for fast access
- 10MB max file size
- Supported types: jpg, png, gif, webp, pdf, doc, docx

### Brevo (Email)

- Transactional emails for notifications
- Unread message digests
- Configurable per workspace

---

## Security Considerations

- UUIDs for all public IDs (no enumeration)
- API keys hashed in database
- Session tokens with expiration
- Rate limiting per user and IP
- Webhook signatures for verification
- Soft deletes for audit trail
- Input sanitization for XSS prevention
- File type validation on upload

---

## Deployment (Railway)

### Services to Deploy

1. **api** — Laravel application
2. **reverb** — Laravel Reverb (same codebase, different command)
3. **dashboard** — Nuxt application
4. **postgres** — PostgreSQL database
5. **redis** — Redis for pub/sub and caching

### Environment Variables

```
# Database
DATABASE_URL=postgresql://...

# Redis
REDIS_URL=redis://...

# App
APP_URL=https://api.yourchat.com
DASHBOARD_URL=https://app.yourchat.com

# Reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...

# Bunny CDN
BUNNY_STORAGE_ZONE=...
BUNNY_STORAGE_API_KEY=...
BUNNY_CDN_URL=...

# Brevo
BREVO_API_KEY=...
MAIL_FROM_ADDRESS=...
```

---

## Next Steps

1. Set up monorepo with Docker
2. Initialize Laravel with Reverb
3. Initialize Nuxt with Nuxt UI
4. Implement database migrations
5. Build API endpoints
6. Build WebSocket events
7. Build dashboard UI
8. Build widget embed
9. Deploy to Railway
