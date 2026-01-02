# Reusable Chat - Project Context

## Overview
Multi-tenant embeddable chat widget system. Think Intercom/Drift but for any app that needs real-time messaging.

## Architecture

### Services
- **API** (Laravel 11 + PHP 8.4): Backend API, WebSocket server (Reverb), queue worker
- **Dashboard** (Nuxt 3): Admin dashboard and demo page
- **PostgreSQL**: Primary database (Railway)
- **Redis**: Queue, cache, sessions (Railway)

### Deployment
- **Platform**: Railway
- **API URL**: https://api-production-de24c.up.railway.app
- **Dashboard URL**: https://dashboard-production-8985.up.railway.app
- **Demo**: https://dashboard-production-8985.up.railway.app/demo
- **GitHub**: https://github.com/TNortnern/reusable-chat

## Key Environment Variables

### API (Railway)
```
# Database
DATABASE_URL=postgresql://...

# Redis
REDIS_HOST=redis.railway.internal
REDIS_PORT=6379
REDIS_PASSWORD=...
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

# Reverb (WebSocket)
REVERB_APP_ID=reusable-chat
REVERB_APP_KEY=reusable-chat-key
REVERB_APP_SECRET=reusable-chat-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http  # Internal communication, nginx handles external HTTPS
BROADCAST_CONNECTION=reverb

# Bunny CDN (File Storage)
BUNNY_STORAGE_API_KEY=d73ca461-b0a3-4f8e-9d900ff46156-0bec-408b
BUNNY_STORAGE_ZONE=hastest
BUNNY_CDN_URL=https://hastest.b-cdn.net
BUNNY_STORAGE_HOSTNAME=storage.bunnycdn.com
BUNNY_UPLOAD_PATH=uploads
BUNNY_STORAGE_ENABLED=true
```

### Dashboard (Railway)
```
NUXT_PUBLIC_API_URL=https://api-production-de24c.up.railway.app
NUXT_PUBLIC_REVERB_HOST=api-production-de24c.up.railway.app
NUXT_PUBLIC_REVERB_PORT=443
NUXT_PUBLIC_REVERB_KEY=reusable-chat-key
NUXT_PUBLIC_DEMO_API_KEY=sk_demo_reusable_chat_demo_key_2026
```

## Database Schema (Key Tables)

### Tenants/Workspaces
- `workspaces` - Multi-tenant workspaces
- `api_keys` - API keys per workspace (hashed with SHA256)
- `workspace_settings` - Per-workspace settings

### Users
- `users` - Dashboard admin users (Sanctum auth)
- `chat_users` - End users in chat widget

### Conversations
- `conversations` - Chat rooms/threads
- `conversation_participants` - Users in conversations
- `messages` - Chat messages
- `attachments` - File attachments

### Auth
- `sessions` - Widget session tokens for chat users

## API Authentication

### 1. Consumer Backend API (`/api/v1/*`)
- Uses `X-API-Key` header
- API keys stored hashed (SHA256) in `api_keys` table
- For server-to-server communication

### 2. Widget API (`/api/widget/*`)
- Uses Bearer token from session
- Sessions created via Consumer API
- For frontend widget communication

### 3. Dashboard API (`/api/dashboard/*`)
- Uses Laravel Sanctum (cookies)
- For admin dashboard

## WebSocket Architecture
```
Client (WSS:443) -> Railway LB -> Nginx (8000) -> Reverb (8080)
                                      |
                                      v
                                PHP-FPM (9000)
```

- Nginx proxies `/app/*` to Reverb on 8080
- Queue worker broadcasts events to Reverb
- Reverb pushes to connected WebSocket clients

## Demo Flow
1. User visits `/demo`
2. Creates room via `POST /api/v1/demo/rooms` (uses demo API key)
3. Joins chat, gets session token
4. Connects to WebSocket channel `private-conversation.{id}`
5. Messages broadcast in real-time

## File Storage
- **Development**: Local `public` disk
- **Production**: Bunny CDN (`bunny` disk)
- Files stored at: `workspaces/{workspace_id}/attachments/{uuid}.{ext}`
- CDN URL: `https://hastest.b-cdn.net/uploads/...`

## Multi-Tenant Architecture (TODO)

### Tenant Types (Planned)
1. **Marketplace Tenants** (e.g., Airbnb, mywatercloset)
   - Multiple widget configurations
   - Different chat types (guest-host, user-support)
   - Custom rules/policies per widget type
   - Custom email templates

2. **Single-App Tenants**
   - One widget configuration
   - Simple setup

### Panels (Planned)
1. **Super Admin Panel**
   - Manage all tenants
   - View all messages/users
   - System configuration
   - Template management

2. **Tenant Admin Panel**
   - Manage own workspace
   - Configure widgets
   - View analytics
   - Manage users/conversations

## First Customer
- **mywatercloset** - First tenant to use this system

## Commands

### Local Development
```bash
# API
cd api && php artisan serve
php artisan reverb:start
php artisan queue:work

# Dashboard
cd dashboard && npm run dev
```

### Deploy
```bash
railway up --service api --environment production
railway up --service dashboard --environment production
```

### Check Logs
```bash
railway logs --service api
railway logs --service dashboard
```

## Known Issues / Notes
- Files uploaded to local storage don't persist on Railway redeploys (use Bunny CDN)
- Demo API key is `sk_demo_reusable_chat_demo_key_2026`
- WebSocket requires `REVERB_SCHEME=http` for internal communication
