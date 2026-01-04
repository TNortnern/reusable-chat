# Reusable Chat - Current Tasks

## Completed Tasks ✅

### Bug Fixes
- [x] Fix `window.open()` reference error in watercloset attachment click handler

### Image Lightbox Viewer
- [x] Add lightbox modal HTML structure to demo.vue
- [x] Add lightbox state variables
- [x] Add openImage/closeLightbox functions
- [x] Add lightbox CSS styles

### Branding
- [x] Add "Powered by Reusable Chat" badge to demo chat screen
- [x] Add branding CSS styles

### Documentation
- [x] Update README.md with latest features
- [x] Document the widget API endpoints
- [x] Document WebSocket events
- [x] Explain watercloset integration architecture
- [x] Add setup guide for new integrations

### Deployment
- [x] Deploy reusable-chat dashboard changes
- [x] Verify watercloset deployment completed
- [x] Verify both services are live (200 OK)

---

## In Progress 🔄

### Embeddable Widget (Web Components)

Goal: Universal drop-in chat widget using Web Components (works in React, Vue, Angular, vanilla JS, WordPress, etc.):

```html
<!-- Option 1: Auto-floating bubble -->
<script src="https://cdn.reusable-chat.com/widget.js"></script>
<reusable-chat
  api-key="pk_your_public_key"
  user-id="user-123"
  user-name="John Doe"
  position="bottom-right"
  theme="light"
></reusable-chat>

<!-- Option 2: Inline embed -->
<reusable-chat-inline
  api-key="pk_your_public_key"
  user-id="user-123"
  conversation-id="conv-456"
></reusable-chat-inline>
```

#### Phase 1: Backend Setup
- [ ] Create `public_keys` table for client-side auth (separate from server API keys)
- [ ] Add public key generation to dashboard (pk_xxx vs sk_xxx)
- [ ] Create `/api/embed/init` endpoint (validates public key, returns config)
- [ ] Create `/api/embed/session` endpoint (creates session with public key + user info)
- [ ] Add CORS whitelist per workspace (or allow all for public widgets)

#### Phase 2: Web Component Foundation
- [ ] Create `widget/` directory in repo
- [ ] Set up Lit (or vanilla) Web Components build with Vite
- [ ] Create base `<reusable-chat>` custom element
- [ ] Create `<reusable-chat-inline>` custom element
- [ ] Shadow DOM for style encapsulation
- [ ] Build to single `widget.js` bundle (~50kb target)

#### Phase 3: Core UI Components (Shadow DOM)
- [ ] `<rc-bubble>` - Floating chat button with unread badge
- [ ] `<rc-window>` - Expandable chat window container
- [ ] `<rc-header>` - Chat header with title, status, close button
- [ ] `<rc-conversation-list>` - List of conversations
- [ ] `<rc-message-list>` - Scrollable message thread
- [ ] `<rc-message>` - Individual message bubble
- [ ] `<rc-input>` - Message input with emoji/attachment buttons
- [ ] `<rc-typing>` - Typing indicator
- [ ] `<rc-attachment>` - File/image attachment display

#### Phase 4: Widget Logic
- [ ] Auto-initialize from custom element attributes
- [ ] Session management (create/restore from localStorage)
- [ ] WebSocket connection (Pusher/Echo compatible)
- [ ] Message send/receive
- [ ] Real-time typing indicators
- [ ] File upload handling
- [ ] Unread count tracking
- [ ] Connection state management (online/offline/reconnecting)

#### Phase 5: Customization API
- [ ] Attributes: `position`, `theme`, `accent-color`, `show-branding`
- [ ] CSS custom properties for theming (--rc-primary-color, etc.)
- [ ] Slots for custom header/footer content
- [ ] JavaScript API: `widget.open()`, `widget.close()`, `widget.sendMessage()`
- [ ] Events: `rc-ready`, `rc-message`, `rc-open`, `rc-close`

#### Phase 6: Distribution
- [ ] Host on Bunny CDN (already have account)
- [ ] URL: `https://hastest.b-cdn.net/widget/v1/widget.js`
- [ ] Versioned URLs: `/widget/v1.0.0/widget.js`
- [ ] Add embed code generator to dashboard
- [ ] Update README with Web Component usage

---

## Backlog 📋

### File Handling Improvements
- [ ] Ensure PDFs open in browser if possible
- [ ] Add proper download buttons for non-image files
- [ ] Test file upload and download flow

### Branding Configuration
- [ ] Make branding configurable per workspace (hide/show)

### Testing
- [ ] Test lightbox functionality end-to-end
- [ ] Test attachment upload/download flow
- [ ] Test WebSocket reconnection

---

## Progress Log

### 2026-01-04
- Fixed `window.open()` bug in watercloset/messages/index.vue
- Added lightbox viewer to reusable-chat demo page
- Added "Powered by Reusable Chat" branding badge
- Wrote comprehensive README documentation
- Deployed changes to production
- Started planning embeddable widget feature
