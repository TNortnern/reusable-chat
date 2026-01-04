# Reusable Chat Platform

Multi-tenant embeddable real-time chat platform. Think Intercom/Drift but for any app that needs messaging.

## Live Demo

- **Demo Chat**: https://dashboard-production-8985.up.railway.app/demo
- **API**: https://api-production-de24c.up.railway.app

## Features

- Real-time messaging via WebSocket (Laravel Reverb)
- Multi-tenant workspaces with API key authentication
- File attachments with image lightbox viewer
- Typing indicators
- Read receipts
- Emoji picker
- Message reporting/moderation
- Conversation types (inquiry, booking, support)
- Branded widget ("Powered by Reusable Chat")

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         Your Application                             │
│  (watercloset, your-app, etc.)                                       │
│                                                                       │
│  ┌──────────────────┐    ┌──────────────────────────────────────┐   │
│  │  Backend Server  │    │           Frontend Widget             │   │
│  │                  │    │                                        │   │
│  │  POST /api/v1/*  │───▶│  useReusableChat() composable         │   │
│  │  (X-API-Key)     │    │  - Creates sessions                   │   │
│  │                  │    │  - Fetches conversations               │   │
│  └────────┬─────────┘    │  - Sends messages                     │   │
│           │              │  - Uploads attachments                 │   │
│           │              │  - WebSocket real-time updates         │   │
│           ▼              └─────────────────┬────────────────────┘   │
└───────────┼────────────────────────────────┼───────────────────────┘
            │                                │
            ▼                                ▼
┌───────────────────────────────────────────────────────────────────────┐
│                        Reusable Chat Platform                          │
│                                                                         │
│  ┌────────────────────┐     ┌────────────────────┐                    │
│  │   API Server       │     │   WebSocket Server │                    │
│  │   (Laravel 11)     │     │   (Reverb)         │                    │
│  │                    │     │                    │                    │
│  │  /api/v1/*         │     │  Channels:         │                    │
│  │  - Users           │     │  - conversation.{id}│                   │
│  │  - Sessions        │     │  - user.{id}       │                    │
│  │  - Conversations   │     │                    │                    │
│  │  - Moderation      │     │  Events:           │                    │
│  │                    │     │  - message.created │                    │
│  │  /api/widget/*     │     │  - user.typing     │                    │
│  │  - Messages        │     │  - message.read    │                    │
│  │  - Attachments     │     │                    │                    │
│  │  - Typing          │     └────────────────────┘                    │
│  │  - Reactions       │                                               │
│  └────────────────────┘                                               │
│                                                                         │
│  ┌────────────────────┐     ┌────────────────────┐                    │
│  │   PostgreSQL       │     │   Redis            │                    │
│  │   - Users/Chat     │     │   - Sessions       │                    │
│  │   - Messages       │     │   - Cache          │                    │
│  │   - Conversations  │     │   - Queue          │                    │
│  └────────────────────┘     └────────────────────┘                    │
│                                                                         │
│  ┌────────────────────┐                                               │
│  │   Bunny CDN        │                                               │
│  │   - Attachments    │                                               │
│  └────────────────────┘                                               │
└───────────────────────────────────────────────────────────────────────┘
```

---

## API Reference

### Authentication

Two authentication methods:

1. **API Key** (`X-API-Key` header) - For your backend server
2. **Session Token** (`Bearer` token) - For frontend widget users

### Consumer Backend API (`/api/v1/*`)

Use your API key to manage users and sessions from your backend.

#### Create User
```bash
POST /api/v1/users
X-API-Key: sk_your_api_key

{
  "external_id": "user-123",
  "name": "John Doe",
  "email": "john@example.com"
}
```

#### Create Session
```bash
POST /api/v1/sessions
X-API-Key: sk_your_api_key

{
  "user_id": "uuid-from-create-user"
}

# Returns: { "token": "session_token_for_frontend" }
```

#### Create Conversation
```bash
POST /api/v1/conversations
X-API-Key: sk_your_api_key

{
  "participant_ids": ["user-uuid-1", "user-uuid-2"],
  "type": "direct",
  "metadata": {
    "type": "inquiry",
    "property_name": "Beach House",
    "property_id": "prop-123"
  }
}
```

### Widget API (`/api/widget/*`)

Used by the frontend widget with session tokens.

#### Get Conversations
```bash
GET /api/widget/conversations
Authorization: Bearer session_token
```

#### Get Conversation Messages
```bash
GET /api/widget/conversations/{id}
Authorization: Bearer session_token
```

#### Send Message
```bash
POST /api/widget/conversations/{id}/messages
Authorization: Bearer session_token

{
  "content": "Hello!",
  "attachment_ids": []
}
```

#### Upload Attachment
```bash
POST /api/widget/conversations/{id}/attachments
Authorization: Bearer session_token
Content-Type: multipart/form-data

file: (binary)
```

#### Send Typing Indicator
```bash
POST /api/widget/conversations/{id}/typing
Authorization: Bearer session_token

{
  "is_typing": true
}
```

---

## Integration Guide

### Minimal Integration (~200 lines of custom code)

For a basic chat integration, you need:

1. **Backend**: Create users and sessions when users log in
2. **Frontend**: Use the session token to connect to widget API

#### Example: watercloset Integration

**Backend (Nuxt Server Route)** - `server/api/chat/session.post.ts`:
```typescript
export default defineEventHandler(async (event) => {
  const user = await getAuthUser(event) // Your auth

  // Create/get user in reusable-chat
  const chatUser = await $fetch(`${CHAT_API}/api/v1/users`, {
    method: 'POST',
    headers: { 'X-API-Key': API_KEY },
    body: {
      external_id: user.id,
      name: user.name,
      email: user.email
    }
  })

  // Create session
  const session = await $fetch(`${CHAT_API}/api/v1/sessions`, {
    method: 'POST',
    headers: { 'X-API-Key': API_KEY },
    body: { user_id: chatUser.id }
  })

  return { token: session.token, user: chatUser }
})
```

**Frontend Composable** - `composables/useReusableChat.ts`:
```typescript
export const useReusableChat = () => {
  const session = useState('chat-session')
  const isConnected = ref(false)

  const getOrCreateSession = async () => {
    if (session.value) return session.value
    session.value = await $fetch('/api/chat/session', { method: 'POST' })
    connectWebSocket()
    return session.value
  }

  const connectWebSocket = () => {
    const echo = new Echo({
      broadcaster: 'reverb',
      key: 'reusable-chat-key',
      wsHost: 'api-production-de24c.up.railway.app',
      wsPort: 443,
      forceTLS: true,
      authEndpoint: `${CHAT_API}/api/widget/broadcasting/auth`,
      auth: { headers: { Authorization: `Bearer ${session.value.token}` }}
    })

    echo.connector.pusher.connection.bind('connected', () => {
      isConnected.value = true
    })
  }

  return { session, isConnected, getOrCreateSession }
}
```

**Messages Page** - `pages/messages/index.vue`:
- ~1000 lines including full UI
- Uses `useReusableChat()` composable
- Handles conversation list, message thread, attachments, typing, etc.

### What's Custom vs What's Provided

| Feature | Reusable Chat | Your App |
|---------|--------------|----------|
| User storage | ✅ Stores chat users | ✅ Your auth users |
| Message storage | ✅ Full persistence | - |
| WebSocket server | ✅ Laravel Reverb | - |
| Real-time events | ✅ Broadcasting | ✅ Echo client setup |
| File uploads | ✅ API + CDN storage | - |
| API endpoints | ✅ Full CRUD | ✅ Session proxy route |
| Chat UI | ✅ Demo page | ✅ Your custom UI |
| Authentication | ✅ Session tokens | ✅ Your auth + API key |

---

## Local Development

### Prerequisites
- Docker & Docker Compose
- Node.js 18+
- PHP 8.4+ (if running without Docker)

### Quick Start

```bash
# Clone and setup
git clone https://github.com/TNortnern/reusable-chat.git
cd reusable-chat
cp .env.example .env

# Start services
docker-compose up -d

# Install dependencies
docker-compose exec api composer install
docker-compose exec api php artisan migrate
docker-compose exec dashboard npm install

# Access
# API: http://localhost:8000
# Dashboard: http://localhost:3000
# WebSocket: ws://localhost:8080
```

### Services

| Service | Port | Description |
|---------|------|-------------|
| API | 8000 | Laravel backend |
| Dashboard | 3000 | Nuxt admin & demo |
| Reverb | 8080 | WebSocket server |
| PostgreSQL | 5432 | Database |
| Redis | 6379 | Cache/Queue/Sessions |

---

## Production Deployment

Currently deployed on Railway:
- **API**: https://api-production-de24c.up.railway.app
- **Dashboard**: https://dashboard-production-8985.up.railway.app
- **PostgreSQL & Redis**: Railway managed services

### Environment Variables

See `.env.example` for all required variables. Key production settings:

```env
# API
DATABASE_URL=postgresql://...
REDIS_URL=redis://...
REVERB_APP_KEY=reusable-chat-key
BUNNY_STORAGE_ENABLED=true

# Dashboard
NUXT_PUBLIC_API_URL=https://api-production-de24c.up.railway.app
NUXT_PUBLIC_REVERB_HOST=api-production-de24c.up.railway.app
```

---

## WebSocket Events

### Channels

- `private-conversation.{id}` - Per-conversation events
- `private-user.{id}` - Per-user events (new conversations)

### Events

| Event | Channel | Payload |
|-------|---------|---------|
| `message.created` | conversation.{id} | Message object |
| `user.typing` | conversation.{id} | `{ user_id, name, is_typing }` |
| `message.read` | conversation.{id} | `{ user_id, read_at }` |
| `conversation.created` | user.{id} | Conversation object |

---

## File Structure

```
reusable-chat/
├── api/                    # Laravel backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/V1/      # Consumer API
│   │   │   │   ├── Widget/       # Widget API
│   │   │   │   └── Dashboard/    # Admin API
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   └── Events/              # Broadcast events
│   ├── routes/api.php
│   └── docker/                  # Production Docker config
├── dashboard/              # Nuxt frontend
│   ├── app/
│   │   └── pages/
│   │       └── demo.vue         # Public demo chat
│   └── nuxt.config.ts
├── docker-compose.yml      # Local development
└── CLAUDE.md               # Project context
```

---

## License

MIT
