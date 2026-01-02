# Reusable Chat - Implementation Plan

## Current Status

### Completed Features
- Real-time messaging via Laravel Reverb WebSockets
- Typing indicators
- Read receipts
- Emoji picker
- File uploads with Bunny CDN storage
- Multi-user chat rooms (demo page)
- User blocking
- Message reactions
- Dashboard with authentication
- API key management
- Workspace settings and theming

### Infrastructure
- **API**: Laravel 11 on Railway (PHP 8.4-fpm + nginx)
- **Dashboard**: Nuxt 3 on Railway
- **Database**: PostgreSQL on Railway
- **Cache/Queue/Session**: Redis on Railway
- **File Storage**: Bunny CDN
- **WebSockets**: Laravel Reverb (embedded in API container)

---

## Next Steps

### 1. Database Schema Cleanup
**Priority: Medium**

The attachments table has both old (`filename`, `mime_type`, `size_bytes`, `url`) and new (`name`, `type`, `path`, `size`) columns. Create a migration to:
- Make legacy columns nullable
- Eventually deprecate and remove legacy columns

Files to modify:
- `api/database/migrations/` - new migration

---

### 2. Dashboard Conversations Page
**Priority: High**

The dashboard needs a proper conversations view to:
- List all conversations in a workspace
- View messages in each conversation
- Search/filter conversations
- Moderate messages (delete, etc.)

Files to create/modify:
- `dashboard/app/pages/dashboard/[workspaceId]/conversations/index.vue`
- `dashboard/app/pages/dashboard/[workspaceId]/conversations/[id].vue`

---

### 3. Widget Embedding
**Priority: High**

Create an embeddable chat widget that can be added to any website:
- Standalone JS bundle
- Iframe option
- Customizable theming via dashboard settings

Files to create:
- `widget/` - new package
- `api/routes/widget-embed.php` - embed script endpoint

---

### 4. Message Search
**Priority: Medium**

Add full-text search for messages:
- PostgreSQL full-text search or Meilisearch
- Search API endpoints
- Dashboard search UI

Files to modify:
- `api/app/Http/Controllers/Widget/MessageController.php`
- `api/app/Http/Controllers/Dashboard/ConversationController.php`

---

### 5. Notifications
**Priority: Medium**

Push notifications for new messages:
- Web Push API integration
- Email notifications (optional)
- In-app notification center

---

### 6. Message Threading
**Priority: Low**

Reply to specific messages:
- `parent_id` column on messages
- Thread UI in widget

---

### 7. Presence System
**Priority: Medium**

Real-time online/offline status:
- Presence channels in Reverb
- Last seen timestamps
- "X is typing" improvements

---

### 8. Rate Limiting & Security
**Priority: High**

- Message rate limiting per user
- Spam detection
- Content filtering options
- CORS configuration for widget domains

---

### 9. Analytics Improvements
**Priority: Low**

- Message volume charts
- User engagement metrics
- Response time tracking
- Export to CSV

---

## Technical Debt

1. **Error Handling**: Add better error responses across API
2. **Validation**: Standardize request validation with Form Requests
3. **Testing**: Add PHPUnit tests for controllers
4. **Documentation**: API documentation with OpenAPI/Swagger
5. **Logging**: Structured logging for debugging

---

## Environment Variables Reference

### API (.env)
```
APP_URL=https://api-production-de24c.up.railway.app
DB_CONNECTION=pgsql
REDIS_URL=...
BUNNY_STORAGE_ENABLED=true
BUNNY_STORAGE_ZONE=hastest
BUNNY_STORAGE_API_KEY=...
BUNNY_CDN_URL=https://hastest.b-cdn.net
REVERB_APP_ID=chat-app
REVERB_APP_KEY=chat-app-key
REVERB_APP_SECRET=...
```

### Dashboard (.env)
```
NUXT_PUBLIC_API_URL=https://api-production-de24c.up.railway.app
NUXT_PUBLIC_REVERB_HOST=api-production-de24c.up.railway.app
NUXT_PUBLIC_REVERB_PORT=443
NUXT_PUBLIC_REVERB_KEY=chat-app-key
NUXT_PUBLIC_DEMO_API_KEY=...
```
