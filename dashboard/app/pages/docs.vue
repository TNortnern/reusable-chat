<script setup lang="ts">
/**
 * Reusable Chat Documentation
 * A comprehensive, beautifully designed documentation page
 */
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue'

definePageMeta({
  layout: false
})

// Active section tracking for scrollspy
const activeSection = ref('introduction')
const showMobileNav = ref(false)
const showCopiedToast = ref(false)
const copiedText = ref('')

// Navigation structure
const navigation = [
  {
    title: 'Getting Started',
    items: [
      { id: 'introduction', label: 'Introduction' },
      { id: 'quickstart', label: 'Quick Start' },
      { id: 'architecture', label: 'Architecture' },
    ]
  },
  {
    title: 'Widget Integration',
    items: [
      { id: 'widget-setup', label: 'Basic Setup' },
      { id: 'widget-config', label: 'Configuration' },
      { id: 'widget-events', label: 'Events & Methods' },
      { id: 'widget-styling', label: 'Styling' },
    ]
  },
  {
    title: 'Backend API',
    items: [
      { id: 'api-auth', label: 'Authentication' },
      { id: 'api-users', label: 'Users' },
      { id: 'api-sessions', label: 'Sessions' },
      { id: 'api-conversations', label: 'Conversations' },
      { id: 'api-messages', label: 'Messages' },
      { id: 'api-attachments', label: 'Attachments' },
    ]
  },
  {
    title: 'Real-time',
    items: [
      { id: 'websocket', label: 'WebSocket Events' },
      { id: 'workspace-monitoring', label: 'Workspace Monitoring' },
      { id: 'typing', label: 'Typing Indicators' },
      { id: 'email-notifications', label: 'Email Notifications' },
    ]
  },
  {
    title: 'Dashboard',
    items: [
      { id: 'dashboard-api-keys', label: 'API Keys' },
      { id: 'dashboard-settings', label: 'Settings' },
    ]
  },
]

// Scrollspy logic
const observerOptions = {
  root: null,
  rootMargin: '-20% 0px -70% 0px',
  threshold: 0
}

let observer: IntersectionObserver | null = null

onMounted(() => {
  observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        activeSection.value = entry.target.id
      }
    })
  }, observerOptions)

  // Observe all sections
  document.querySelectorAll('section[id]').forEach(section => {
    observer?.observe(section)
  })
})

onUnmounted(() => {
  observer?.disconnect()
})

// Copy to clipboard with toast
const copyToClipboard = async (text: string, label = 'Code') => {
  try {
    await navigator.clipboard.writeText(text)
    copiedText.value = label
    showCopiedToast.value = true
    setTimeout(() => {
      showCopiedToast.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

// Copy with LLM context
const copyForLLM = async (code: string, context: string) => {
  const llmText = `## Context
${context}

## Code
\`\`\`
${code}
\`\`\`

## Instructions
Implement the above code following the reusable-chat API documentation patterns.`
  await copyToClipboard(llmText, 'Code + Context for LLM')
}

// Code snippets (defined here to avoid template parsing issues)
const codeSnippets = {
  cdnScript: '<script src="https://cdn.reusable-chat.com/widget.js"><\/script>',
  widgetBasic: `<script src="https://cdn.reusable-chat.com/widget.js"><\/script>

<reusable-chat
  api-key="pk_your_public_key"
  user-id="user_123"
  user-name="John Doe"
></reusable-chat>`,
  widgetFull: `<reusable-chat
  api-key="pk_your_public_key"
  user-id="user_123"
  user-name="John Doe"
  user-email="john@example.com"
  position="bottom-right"
  theme="light"
></reusable-chat>`,
  widgetMethods: `// Get widget reference
const chat = document.querySelector('reusable-chat')

// Open/close programmatically
chat.open()
chat.close()
chat.toggle()

// Send a message
await chat.sendMessage('Hello!')

// Update user (e.g., after login)
await chat.setUser('new_user_id', 'New Name', 'email@example.com')

// Check state
console.log(chat.isOpened)      // boolean
console.log(chat.isConnected)   // boolean
console.log(chat.unreadMessages) // number

// Cleanup
chat.destroy()`,
  widgetEvents: `const chat = document.querySelector('reusable-chat')

// Widget ready
chat.addEventListener('rc-ready', () => {
  console.log('Chat widget initialized')
})

// Widget opened/closed
chat.addEventListener('rc-open', () => console.log('Opened'))
chat.addEventListener('rc-close', () => console.log('Closed'))

// Message events
chat.addEventListener('rc-message', (e) => {
  console.log('New message:', e.detail)
})

chat.addEventListener('rc-send', (e) => {
  console.log('Message sent:', e.detail)
})

// Error handling
chat.addEventListener('rc-error', (e) => {
  console.error('Chat error:', e.detail)
})`,
  widgetStyling: `reusable-chat {
  /* Button */
  --rc-button-size: 60px;
  --rc-button-bg: #2563eb;
  --rc-button-color: white;

  /* Window */
  --rc-window-width: 380px;
  --rc-window-height: 600px;
  --rc-window-radius: 16px;

  /* Colors */
  --rc-bg-primary: #ffffff;
  --rc-bg-secondary: #f9fafb;
  --rc-text-primary: #111827;
  --rc-text-secondary: #6b7280;

  /* Messages */
  --rc-bubble-sent-bg: #2563eb;
  --rc-bubble-sent-color: white;
  --rc-bubble-received-bg: #f3f4f6;
  --rc-bubble-received-color: #111827;
}`,
  createUser: `curl -X POST https://api.reusable-chat.com/api/v1/users \\
  -H 'X-API-Key: sk_your_api_key' \\
  -H 'Content-Type: application/json' \\
  -d '{
    "external_id": "user_123",
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": "https://example.com/avatar.jpg",
    "metadata": {
      "plan": "premium",
      "company": "Acme Inc"
    }
  }'`,
  createSession: `curl -X POST https://api.reusable-chat.com/api/v1/sessions \\
  -H 'X-API-Key: sk_your_api_key' \\
  -H 'Content-Type: application/json' \\
  -d '{
    "user_id": "550e8400-e29b-41d4-a716-446655440000",
    "expires_in": 86400
  }'`,
  createConversation: `curl -X POST https://api.reusable-chat.com/api/v1/conversations \\
  -H 'X-API-Key: sk_your_api_key' \\
  -H 'Content-Type: application/json' \\
  -d '{
    "type": "direct",
    "participant_ids": [
      "user_uuid_1",
      "user_uuid_2"
    ],
    "name": "Support Chat",
    "metadata": {
      "type": "support",
      "ticket_id": "TICKET-123"
    }
  }'`,
  sendMessage: `curl -X POST https://api.reusable-chat.com/api/widget/conversations/{conv_id}/messages \\
  -H 'Authorization: Bearer {session_token}' \\
  -H 'Content-Type: application/json' \\
  -d '{
    "content": "Hello! How can I help you today?",
    "attachment_ids": []
  }'`,
  uploadAttachment: `curl -X POST https://api.reusable-chat.com/api/widget/conversations/{conv_id}/attachments \\
  -H 'Authorization: Bearer {session_token}' \\
  -F 'file=@/path/to/image.png'`,
  typingPayload: '{ "is_typing": true }',
  websocketEcho: `import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

const echo = new Echo({
  broadcaster: 'reverb',
  key: 'your-reverb-key',
  wsHost: 'api.reusable-chat.com',
  wsPort: 443,
  wssPort: 443,
  forceTLS: true,
  enabledTransports: ['ws', 'wss'],
  authEndpoint: '/api/widget/broadcasting/auth',
  auth: {
    headers: {
      Authorization: \`Bearer \${sessionToken}\`
    }
  }
})

// Subscribe to conversation
echo.private(\`conversation.\${conversationId}\`)
  .listen('.message.created', (message) => {
    console.log('New message:', message)
  })
  .listen('.user.typing', ({ user_id, name, is_typing }) => {
    console.log(\`\${name} is \${is_typing ? '' : 'not '}typing\`)
  })`,
  workspaceRealtimeToken: `curl -X POST https://api.reusable-chat.com/api/v1/workspaces/{workspace_id}/realtime/token \\
  -H 'X-API-Key: sk_your_api_key' \\
  -H 'Content-Type: application/json'`,
  workspaceMonitoring: `import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Step 1: Get realtime token from your backend
const response = await fetch('/api/v1/workspaces/abc-123/realtime/token', {
  method: 'POST',
  headers: {
    'X-API-Key': 'sk_your_api_key',
  }
})
const { token, workspace_id } = await response.json()

// Step 2: Connect to WebSocket with token
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
  authEndpoint: 'https://api-production-de24c.up.railway.app/api/v1/broadcasting/auth',
  auth: {
    headers: {
      Authorization: \`Bearer \${token}\`
    }
  }
})

// Step 3: Subscribe to workspace channel
echo.private(\`workspace.\${workspace_id}\`)
  .listen('.message.created', (event) => {
    console.log('New message in workspace:', event)
    // Send to CRM, trigger workflow, log to analytics, etc.
  })
  .listen('.conversation.created', (event) => {
    console.log('New conversation:', event)
    // Notify team, create ticket, etc.
  })
  .listen('.message.deleted', (event) => {
    console.log('Message deleted:', event)
  })`,
  workspaceEventPayload: `{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "content": "Hello there!",
  "sender": {
    "id": "user_uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": "https://..."
  },
  "conversation": {
    "id": "conv_uuid",
    "type": "1on1",
    "name": "Support Chat",
    "workspace_id": "workspace_uuid",
    "participant_count": 2,
    "created_by": "user_uuid",
    "created_at": "2026-01-16T10:00:00Z"
  },
  "attachments": [
    {
      "id": "attach_uuid",
      "name": "screenshot.png",
      "type": "image/png",
      "url": "https://cdn.reusable-chat.com/...",
      "size": 102400
    }
  ],
  "created_at": "2026-01-16T10:30:00Z"
}`,
  emailSettings: `{
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
    "logo_url": "https://yoursite.com/logo.png",
    "brand_color": "#3b82f6",
    "from_name": "MyApp Support",
    "reply_to": "support@yoursite.com",
    "footer_text": "© 2026 MyApp. Unsubscribe anytime."
  }
}`,
  emailTemplate: `{
  "event_type": "missed_message_1on1",
  "subject_template": "New message from {{sender.name}}",
  "body_html": "<p>Hi {{recipient.name}},</p><p>{{sender.name}} sent you a message:</p><blockquote>{{message.content}}</blockquote><p><a href='{{conversation_url}}'>Reply now</a></p>",
  "body_text": "Hi {{recipient.name}}, {{sender.name}} sent: {{message.content}}. Reply at {{conversation_url}}",
  "variables": ["sender.name", "recipient.name", "message.content", "conversation_url"]
}`,
}

// Scroll to section
const scrollToSection = (id: string) => {
  const element = document.getElementById(id)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
  showMobileNav.value = false
}
</script>

<template>
  <div class="docs-container">
    <!-- Floating Copied Toast -->
    <Transition name="toast">
      <div v-if="showCopiedToast" class="copied-toast">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        {{ copiedText }} copied!
      </div>
    </Transition>

    <!-- Header -->
    <header class="docs-header">
      <div class="header-content">
        <div class="logo-section">
          <NuxtLink to="/" class="logo">
            <div class="logo-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
              </svg>
            </div>
            <span class="logo-text">Reusable Chat</span>
          </NuxtLink>
          <span class="docs-badge">Docs</span>
        </div>

        <nav class="header-nav">
          <NuxtLink to="/demo" class="nav-link">Demo</NuxtLink>
          <NuxtLink to="/dashboard" class="nav-link">Dashboard</NuxtLink>
          <a href="https://github.com/TNortnern/reusable-chat" target="_blank" class="nav-link github">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
            </svg>
          </a>
        </nav>

        <button class="mobile-menu-btn" @click="showMobileNav = !showMobileNav">
          <svg v-if="!showMobileNav" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </header>

    <!-- Mobile Nav Overlay -->
    <Transition name="mobile-nav">
      <div v-if="showMobileNav" class="mobile-nav-overlay" @click="showMobileNav = false">
        <nav class="mobile-nav" @click.stop>
          <div v-for="group in navigation" :key="group.title" class="nav-group">
            <div class="nav-group-title">{{ group.title }}</div>
            <button
              v-for="item in group.items"
              :key="item.id"
              :class="['nav-item', { active: activeSection === item.id }]"
              @click="scrollToSection(item.id)"
            >
              {{ item.label }}
            </button>
          </div>
        </nav>
      </div>
    </Transition>

    <div class="docs-layout">
      <!-- Sidebar Navigation -->
      <aside class="docs-sidebar">
        <nav class="sidebar-nav">
          <div v-for="group in navigation" :key="group.title" class="nav-group">
            <div class="nav-group-title">{{ group.title }}</div>
            <button
              v-for="item in group.items"
              :key="item.id"
              :class="['nav-item', { active: activeSection === item.id }]"
              @click="scrollToSection(item.id)"
            >
              <span class="nav-indicator"></span>
              {{ item.label }}
            </button>
          </div>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="docs-content">
        <!-- Introduction -->
        <section id="introduction" class="doc-section">
          <div class="section-header">
            <span class="section-tag">Overview</span>
            <h1>Reusable Chat</h1>
            <p class="section-lead">
              A production-ready, multi-tenant chat system. Embed real-time messaging in any application
              with a simple script tag or integrate deeply via REST APIs.
            </p>
          </div>

          <div class="feature-grid">
            <div class="feature-card">
              <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                  <line x1="8" y1="21" x2="16" y2="21"/>
                  <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
              </div>
              <h3>Drop-in Widget</h3>
              <p>Add chat to any website with a single script tag. No framework required.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="16 18 22 12 16 6"/>
                  <polyline points="8 6 2 12 8 18"/>
                </svg>
              </div>
              <h3>REST API</h3>
              <p>Full control with comprehensive APIs for users, conversations, and messages.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
              </div>
              <h3>Real-time</h3>
              <p>WebSocket-powered with typing indicators, read receipts, and instant delivery.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
              </div>
              <h3>Multi-tenant</h3>
              <p>Isolated workspaces with separate API keys, users, and configurations.</p>
            </div>
          </div>
        </section>

        <!-- Quick Start -->
        <section id="quickstart" class="doc-section">
          <h2>Quick Start</h2>
          <p>Get chat running in your app in under 5 minutes.</p>

          <div class="steps">
            <div class="step">
              <div class="step-number">1</div>
              <div class="step-content">
                <h3>Get your API key</h3>
                <p>Create a workspace in the <NuxtLink to="/dashboard">Dashboard</NuxtLink> and generate a public key for your domain.</p>
              </div>
            </div>

            <div class="step">
              <div class="step-number">2</div>
              <div class="step-content">
                <h3>Add the widget</h3>
                <p>Include the script and add the custom element to your HTML:</p>

                <div class="code-block">
                  <div class="code-header">
                    <span class="code-lang">HTML</span>
                    <div class="code-actions">
                      <button
                        class="copy-btn"
                        @click="copyToClipboard(codeSnippets.widgetBasic, 'Widget code')"
                      >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        Copy
                      </button>
                      <button
                        class="copy-btn llm"
                        @click="copyForLLM(codeSnippets.widgetBasic, 'Basic widget integration for Reusable Chat. This adds a floating chat button to your page. Replace api-key with your public key from the dashboard, and set user-id/user-name from your authentication system.')"
                      >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <circle cx="12" cy="12" r="10"/>
                          <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                          <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        Copy for LLM
                      </button>
                    </div>
                  </div>
                  <pre><code>&lt;script src="https://cdn.reusable-chat.com/widget.js"&gt;&lt;/script&gt;

&lt;reusable-chat
  api-key="pk_your_public_key"
  user-id="user_123"
  user-name="John Doe"
&gt;&lt;/reusable-chat&gt;</code></pre>
                </div>
              </div>
            </div>

            <div class="step">
              <div class="step-number">3</div>
              <div class="step-content">
                <h3>Start chatting</h3>
                <p>That's it! The widget handles connection, authentication, and real-time messaging automatically.</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Architecture -->
        <section id="architecture" class="doc-section">
          <h2>Architecture</h2>
          <p>Understanding the system components helps you integrate effectively.</p>

          <div class="arch-diagram">
            <div class="arch-layer">
              <div class="arch-title">Frontend</div>
              <div class="arch-items">
                <div class="arch-item widget">Widget (Web Component)</div>
                <div class="arch-item custom">Your Custom UI</div>
              </div>
            </div>
            <div class="arch-arrow">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <polyline points="19 12 12 19 5 12"/>
              </svg>
            </div>
            <div class="arch-layer">
              <div class="arch-title">API Layer</div>
              <div class="arch-items">
                <div class="arch-item">Embed API (Public Key)</div>
                <div class="arch-item">Widget API (Session Token)</div>
                <div class="arch-item">Consumer API (API Key)</div>
              </div>
            </div>
            <div class="arch-arrow">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <polyline points="19 12 12 19 5 12"/>
              </svg>
            </div>
            <div class="arch-layer">
              <div class="arch-title">Backend</div>
              <div class="arch-items">
                <div class="arch-item">Laravel API + Reverb WebSocket</div>
                <div class="arch-item">PostgreSQL + Redis</div>
              </div>
            </div>
          </div>

          <div class="info-box">
            <div class="info-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
              </svg>
            </div>
            <div class="info-content">
              <strong>Two integration paths:</strong>
              <ul>
                <li><strong>Widget</strong> — Drop-in solution using public keys. Best for adding chat to existing sites.</li>
                <li><strong>API</strong> — Full control using API keys. Best for custom UIs and backend integrations.</li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Widget Setup -->
        <section id="widget-setup" class="doc-section">
          <h2>Widget Setup</h2>
          <p>The widget is a Web Component that works with any framework or vanilla HTML.</p>

          <h3>Installation Options</h3>

          <div class="tabs">
            <div class="tab-content">
              <h4>CDN (Recommended)</h4>
              <div class="code-block">
                <div class="code-header">
                  <span class="code-lang">HTML</span>
                  <button class="copy-btn" @click="copyToClipboard(codeSnippets.cdnScript, 'CDN script')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy
                  </button>
                </div>
                <pre><code>&lt;script src="https://cdn.reusable-chat.com/widget.js"&gt;&lt;/script&gt;</code></pre>
              </div>

              <h4>NPM Package</h4>
              <div class="code-block">
                <div class="code-header">
                  <span class="code-lang">Shell</span>
                  <button class="copy-btn" @click="copyToClipboard('npm install @reusable-chat/widget', 'NPM install')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy
                  </button>
                </div>
                <pre><code>npm install @reusable-chat/widget</code></pre>
              </div>

              <div class="code-block">
                <div class="code-header">
                  <span class="code-lang">JavaScript</span>
                  <button class="copy-btn" @click="copyToClipboard(`import '@reusable-chat/widget'`, 'Import')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy
                  </button>
                </div>
                <pre><code>import '@reusable-chat/widget'</code></pre>
              </div>
            </div>
          </div>

          <h3>Basic Usage</h3>
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">HTML</span>
              <button
                class="copy-btn llm"
                @click="copyForLLM(codeSnippets.widgetFull, 'Complete widget setup with all common attributes. api-key is your public key from the dashboard. user-id, user-name, and user-email should come from your authentication system. position can be bottom-right or bottom-left. theme can be light or dark.')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Copy for LLM
              </button>
            </div>
            <pre><code>&lt;reusable-chat
  api-key="pk_your_public_key"
  user-id="user_123"
  user-name="John Doe"
  user-email="john@example.com"
  position="bottom-right"
  theme="light"
&gt;&lt;/reusable-chat&gt;</code></pre>
          </div>
        </section>

        <!-- Widget Configuration -->
        <section id="widget-config" class="doc-section">
          <h2>Widget Configuration</h2>
          <p>All available attributes for the <code>&lt;reusable-chat&gt;</code> element.</p>

          <div class="api-table">
            <table>
              <thead>
                <tr>
                  <th>Attribute</th>
                  <th>Type</th>
                  <th>Required</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>api-key</code></td>
                  <td>string</td>
                  <td class="required">Yes</td>
                  <td>Your public key from the dashboard (starts with <code>pk_</code>)</td>
                </tr>
                <tr>
                  <td><code>user-id</code></td>
                  <td>string</td>
                  <td class="required">Yes</td>
                  <td>Unique identifier for the user in your system</td>
                </tr>
                <tr>
                  <td><code>user-name</code></td>
                  <td>string</td>
                  <td class="required">Yes</td>
                  <td>Display name for the user</td>
                </tr>
                <tr>
                  <td><code>user-email</code></td>
                  <td>string</td>
                  <td>No</td>
                  <td>User's email address</td>
                </tr>
                <tr>
                  <td><code>user-avatar</code></td>
                  <td>string</td>
                  <td>No</td>
                  <td>URL to user's avatar image</td>
                </tr>
                <tr>
                  <td><code>position</code></td>
                  <td>string</td>
                  <td>No</td>
                  <td><code>bottom-right</code> (default) or <code>bottom-left</code></td>
                </tr>
                <tr>
                  <td><code>theme</code></td>
                  <td>string</td>
                  <td>No</td>
                  <td><code>light</code> (default) or <code>dark</code></td>
                </tr>
                <tr>
                  <td><code>accent-color</code></td>
                  <td>string</td>
                  <td>No</td>
                  <td>Custom accent color (hex, e.g., <code>#2563eb</code>)</td>
                </tr>
                <tr>
                  <td><code>show-branding</code></td>
                  <td>boolean</td>
                  <td>No</td>
                  <td>Show "Powered by Reusable Chat" badge (default: true)</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Widget Events & Methods -->
        <section id="widget-events" class="doc-section">
          <h2>Events & Methods</h2>
          <p>Programmatic control over the widget.</p>

          <h3>Methods</h3>
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript</span>
              <button
                class="copy-btn llm"
                @click="copyForLLM(codeSnippets.widgetMethods, 'Widget JavaScript API methods. Use these to programmatically control the chat widget - open/close it, send messages, update user info after authentication changes, check connection state, and cleanup when removing from DOM.')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Copy for LLM
              </button>
            </div>
            <pre><code>// Get widget reference
const chat = document.querySelector('reusable-chat')

// Open/close programmatically
chat.open()
chat.close()
chat.toggle()

// Send a message
await chat.sendMessage('Hello!')

// Update user (e.g., after login)
await chat.setUser('new_user_id', 'New Name', 'email@example.com')

// Check state
console.log(chat.isOpened)      // boolean
console.log(chat.isConnected)   // boolean
console.log(chat.unreadMessages) // number

// Cleanup
chat.destroy()</code></pre>
          </div>

          <h3>Events</h3>
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript</span>
              <button
                class="copy-btn llm"
                @click="copyForLLM(codeSnippets.widgetEvents, 'Widget custom events. Listen to these to react to chat state changes in your application - track when users open chat, receive messages, send messages, or encounter errors.')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Copy for LLM
              </button>
            </div>
            <pre><code>const chat = document.querySelector('reusable-chat')

// Widget ready
chat.addEventListener('rc-ready', () => {
  console.log('Chat widget initialized')
})

// Widget opened/closed
chat.addEventListener('rc-open', () => console.log('Opened'))
chat.addEventListener('rc-close', () => console.log('Closed'))

// Message events
chat.addEventListener('rc-message', (e) => {
  console.log('New message:', e.detail)
})

chat.addEventListener('rc-send', (e) => {
  console.log('Message sent:', e.detail)
})

// Error handling
chat.addEventListener('rc-error', (e) => {
  console.error('Chat error:', e.detail)
})</code></pre>
          </div>
        </section>

        <!-- Widget Styling -->
        <section id="widget-styling" class="doc-section">
          <h2>Widget Styling</h2>
          <p>Customize the appearance with CSS custom properties.</p>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">CSS</span>
              <button
                class="copy-btn llm"
                @click="copyForLLM(codeSnippets.widgetStyling, 'CSS custom properties for styling the chat widget. Override these in your stylesheet to match your brand colors and dimensions.')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Copy for LLM
              </button>
            </div>
            <pre><code>reusable-chat {
  /* Button */
  --rc-button-size: 60px;
  --rc-button-bg: #2563eb;
  --rc-button-color: white;

  /* Window */
  --rc-window-width: 380px;
  --rc-window-height: 600px;
  --rc-window-radius: 16px;

  /* Colors */
  --rc-bg-primary: #ffffff;
  --rc-bg-secondary: #f9fafb;
  --rc-text-primary: #111827;
  --rc-text-secondary: #6b7280;

  /* Messages */
  --rc-bubble-sent-bg: #2563eb;
  --rc-bubble-sent-color: white;
  --rc-bubble-received-bg: #f3f4f6;
  --rc-bubble-received-color: #111827;
}</code></pre>
          </div>
        </section>

        <!-- API Authentication -->
        <section id="api-auth" class="doc-section">
          <h2>API Authentication</h2>
          <p>Three authentication methods for different use cases.</p>

          <div class="auth-methods">
            <div class="auth-method">
              <h3>
                <span class="method-badge backend">Backend</span>
                API Key (Server-to-Server)
              </h3>
              <p>Use for backend integrations. Never expose in frontend code.</p>
              <div class="code-block">
                <div class="code-header">
                  <span class="code-lang">HTTP Header</span>
                  <button class="copy-btn" @click="copyToClipboard('X-API-Key: sk_your_api_key', 'API Key header')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy
                  </button>
                </div>
                <pre><code>X-API-Key: sk_your_api_key</code></pre>
              </div>
            </div>

            <div class="auth-method">
              <h3>
                <span class="method-badge widget">Widget</span>
                Public Key (Frontend)
              </h3>
              <p>Safe to expose in frontend. Domain-restricted for security.</p>
              <div class="code-block">
                <div class="code-header">
                  <span class="code-lang">HTML</span>
                  <button class="copy-btn" @click="copyToClipboard(`api-key=&quot;pk_your_public_key&quot;`, 'Public key attr')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy
                  </button>
                </div>
                <pre><code>api-key="pk_your_public_key"</code></pre>
              </div>
            </div>

            <div class="auth-method">
              <h3>
                <span class="method-badge session">Session</span>
                Session Token (Widget API)
              </h3>
              <p>Returned after authentication. Used for widget API calls.</p>
              <div class="code-block">
                <div class="code-header">
                  <span class="code-lang">HTTP Header</span>
                  <button class="copy-btn" @click="copyToClipboard('Authorization: Bearer {session_token}', 'Session header')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy
                  </button>
                </div>
                <pre><code>Authorization: Bearer {session_token}</code></pre>
              </div>
            </div>
          </div>
        </section>

        <!-- API Users -->
        <section id="api-users" class="doc-section">
          <h2>Users API</h2>
          <p>Create and manage chat users. Uses <strong>API Key</strong> authentication.</p>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method post">POST</span>
              <code>/api/v1/users</code>
            </div>
            <p>Create or update a user. Uses <code>external_id</code> for idempotency.</p>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Request</span>
                <button
                  class="copy-btn llm"
                  @click="copyForLLM(codeSnippets.createUser, 'Create a chat user via the Consumer API. external_id should be the user ID from your system - this enables upsert behavior (creates if new, updates if exists). metadata can store any JSON for filtering/display.')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                  </svg>
                  Copy for LLM
                </button>
              </div>
              <pre><code>curl -X POST https://api.reusable-chat.com/api/v1/users \
  -H 'X-API-Key: sk_your_api_key' \
  -H 'Content-Type: application/json' \
  -d '{
    "external_id": "user_123",
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": "https://example.com/avatar.jpg",
    "metadata": {
      "plan": "premium",
      "company": "Acme Inc"
    }
  }'</code></pre>
            </div>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Response</span>
              </div>
              <pre><code>{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "external_id": "user_123",
  "name": "John Doe",
  "email": "john@example.com",
  "avatar_url": "https://example.com/avatar.jpg",
  "metadata": {
    "plan": "premium",
    "company": "Acme Inc"
  },
  "created_at": "2024-01-15T10:30:00Z"
}</code></pre>
            </div>
          </div>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method get">GET</span>
              <code>/api/v1/users/{external_id}</code>
            </div>
            <p>Retrieve a user by their external ID.</p>
          </div>
        </section>

        <!-- API Sessions -->
        <section id="api-sessions" class="doc-section">
          <h2>Sessions API</h2>
          <p>Create session tokens for widget authentication. Uses <strong>API Key</strong> authentication.</p>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method post">POST</span>
              <code>/api/v1/sessions</code>
            </div>
            <p>Create a session token for a user. Pass this to your frontend for widget auth.</p>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Request</span>
                <button
                  class="copy-btn llm"
                  @click="copyForLLM(codeSnippets.createSession, 'Create a session token for a user. Call this from your backend when a user logs in, then pass the token to your frontend. expires_in is optional (seconds), defaults to 24 hours. The returned token is used for Authorization: Bearer header in widget API calls.')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                  </svg>
                  Copy for LLM
                </button>
              </div>
              <pre><code>curl -X POST https://api.reusable-chat.com/api/v1/sessions \
  -H 'X-API-Key: sk_your_api_key' \
  -H 'Content-Type: application/json' \
  -d '{
    "user_id": "550e8400-e29b-41d4-a716-446655440000",
    "expires_in": 86400
  }'</code></pre>
            </div>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Response</span>
              </div>
              <pre><code>{
  "id": "session_abc123",
  "token": "64_character_session_token_here...",
  "expires_at": "2024-01-16T10:30:00Z"
}</code></pre>
            </div>
          </div>

          <div class="info-box warning">
            <div class="info-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <div class="info-content">
              <strong>Security Note:</strong> Session tokens should be created server-side and passed to your frontend. Never expose your API key in client-side code.
            </div>
          </div>
        </section>

        <!-- API Conversations -->
        <section id="api-conversations" class="doc-section">
          <h2>Conversations API</h2>
          <p>Create and manage conversations. Available via both API Key and Session Token auth.</p>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method post">POST</span>
              <code>/api/v1/conversations</code>
            </div>
            <p>Create a new conversation between users.</p>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Request</span>
                <button
                  class="copy-btn llm"
                  @click="copyForLLM(codeSnippets.createConversation, 'Create a conversation between users. type can be direct (1:1) or group. participant_ids are the UUIDs of chat users (not external_ids). metadata is optional and can store any JSON for categorization.')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                  </svg>
                  Copy for LLM
                </button>
              </div>
              <pre><code>curl -X POST https://api.reusable-chat.com/api/v1/conversations \
  -H 'X-API-Key: sk_your_api_key' \
  -H 'Content-Type: application/json' \
  -d '{
    "type": "direct",
    "participant_ids": [
      "user_uuid_1",
      "user_uuid_2"
    ],
    "name": "Support Chat",
    "metadata": {
      "type": "support",
      "ticket_id": "TICKET-123"
    }
  }'</code></pre>
            </div>
          </div>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method get">GET</span>
              <code>/api/widget/conversations</code>
            </div>
            <p>List conversations for the authenticated user (Session Token auth).</p>
          </div>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method get">GET</span>
              <code>/api/widget/conversations/{id}</code>
            </div>
            <p>Get a conversation with its messages.</p>
          </div>
        </section>

        <!-- API Messages -->
        <section id="api-messages" class="doc-section">
          <h2>Messages API</h2>
          <p>Send and receive messages. Uses <strong>Session Token</strong> authentication.</p>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method post">POST</span>
              <code>/api/widget/conversations/{id}/messages</code>
            </div>
            <p>Send a message to a conversation. Automatically broadcasts via WebSocket.</p>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Request</span>
                <button
                  class="copy-btn llm"
                  @click="copyForLLM(codeSnippets.sendMessage, 'Send a message to a conversation. Uses session token auth (not API key). content is the message text. attachment_ids is optional array of previously uploaded attachment UUIDs. The message is automatically broadcast to all participants via WebSocket.')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                  </svg>
                  Copy for LLM
                </button>
              </div>
              <pre><code>curl -X POST https://api.reusable-chat.com/api/widget/conversations/{conv_id}/messages \
  -H 'Authorization: Bearer {session_token}' \
  -H 'Content-Type: application/json' \
  -d '{
    "content": "Hello! How can I help you today?",
    "attachment_ids": []
  }'</code></pre>
            </div>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Response</span>
              </div>
              <pre><code>{
  "id": "msg_550e8400-e29b-41d4-a716-446655440000",
  "content": "Hello! How can I help you today?",
  "sender_id": "user_uuid",
  "sender": {
    "id": "user_uuid",
    "name": "John Doe",
    "avatar_url": "https://..."
  },
  "attachments": [],
  "created_at": "2024-01-15T10:35:00Z"
}</code></pre>
            </div>
          </div>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method post">POST</span>
              <code>/api/widget/conversations/{id}/read</code>
            </div>
            <p>Mark conversation as read. Updates <code>last_read_at</code> timestamp.</p>
          </div>
        </section>

        <!-- API Attachments -->
        <section id="api-attachments" class="doc-section">
          <h2>Attachments API</h2>
          <p>Upload files to messages. Uses <strong>Session Token</strong> authentication.</p>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method post">POST</span>
              <code>/api/widget/conversations/{id}/attachments</code>
            </div>
            <p>Upload a file. Returns attachment ID to include when sending a message.</p>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Request</span>
                <button
                  class="copy-btn llm"
                  @click="copyForLLM(codeSnippets.uploadAttachment, 'Upload a file attachment. Uses multipart/form-data with the file in the file field. Returns attachment object with id, name, type, url, and size. Include the returned id in attachment_ids when sending a message.')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                  </svg>
                  Copy for LLM
                </button>
              </div>
              <pre><code>curl -X POST https://api.reusable-chat.com/api/widget/conversations/{conv_id}/attachments \
  -H 'Authorization: Bearer {session_token}' \
  -F 'file=@/path/to/image.png'</code></pre>
            </div>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Response</span>
              </div>
              <pre><code>{
  "id": "attach_550e8400-e29b-41d4-a716-446655440000",
  "name": "image.png",
  "type": "image/png",
  "url": "https://cdn.reusable-chat.com/uploads/...",
  "size": 245678
}</code></pre>
            </div>
          </div>
        </section>

        <!-- WebSocket Events -->
        <section id="websocket" class="doc-section">
          <h2>WebSocket Events</h2>
          <p>Real-time events via Laravel Reverb (Pusher-compatible).</p>

          <h3>Channel Format</h3>
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Channel Names</span>
            </div>
            <pre><code>private-conversation.{conversation_id}  // Per-conversation events
private-user.{user_id}                   // Per-user events</code></pre>
          </div>

          <h3>Events</h3>
          <div class="api-table">
            <table>
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Channel</th>
                  <th>Payload</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>message.created</code></td>
                  <td>conversation</td>
                  <td>Full Message object</td>
                </tr>
                <tr>
                  <td><code>user.typing</code></td>
                  <td>conversation</td>
                  <td><code>{ user_id, name, is_typing }</code></td>
                </tr>
                <tr>
                  <td><code>message.read</code></td>
                  <td>conversation</td>
                  <td><code>{ user_id, read_at }</code></td>
                </tr>
                <tr>
                  <td><code>conversation.created</code></td>
                  <td>user</td>
                  <td>Full Conversation object</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript (Pusher/Echo)</span>
              <button
                class="copy-btn llm"
                @click="copyForLLM(codeSnippets.websocketEcho, 'WebSocket subscription using Laravel Echo. Configure with your Reverb key and API host. The auth endpoint uses session token authentication. Subscribe to conversation channels to receive real-time messages and typing indicators.')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Copy for LLM
              </button>
            </div>
            <pre><code>import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

const echo = new Echo({
  broadcaster: 'reverb',
  key: 'your-reverb-key',
  wsHost: 'api.reusable-chat.com',
  wsPort: 443,
  wssPort: 443,
  forceTLS: true,
  enabledTransports: ['ws', 'wss'],
  authEndpoint: '/api/widget/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${sessionToken}`
    }
  }
})

// Subscribe to conversation
echo.private(`conversation.${conversationId}`)
  .listen('.message.created', (message) => {
    console.log('New message:', message)
  })
  .listen('.user.typing', ({ user_id, name, is_typing }) => {
    console.log(`${name} is ${is_typing ? '' : 'not '}typing`)
  })</code></pre>
          </div>
        </section>

        <!-- Workspace Monitoring -->
        <section id="workspace-monitoring" class="doc-section workspace-monitoring-section">
          <div class="section-header gradient-header">
            <span class="section-tag monitoring-tag">Backend Integration</span>
            <h2>🔍 Workspace Monitoring</h2>
            <p class="section-lead">
              Monitor all conversations in your workspace from your backend. Perfect for CRM integrations,
              analytics, automated workflows, and customer support dashboards.
            </p>
          </div>

          <div class="monitoring-features">
            <div class="monitoring-feature">
              <div class="feature-icon-lg">⚡</div>
              <h4>Real-time Events</h4>
              <p>Receive instant WebSocket notifications for all activity across your workspace</p>
            </div>
            <div class="monitoring-feature">
              <div class="feature-icon-lg">📦</div>
              <h4>Rich Payloads</h4>
              <p>Events include full conversation context - no additional API calls needed</p>
            </div>
            <div class="monitoring-feature">
              <div class="feature-icon-lg">🔒</div>
              <h4>Secure Tokens</h4>
              <p>24-hour JWT tokens with workspace-level authorization</p>
            </div>
          </div>

          <h3>How It Works</h3>
          <div class="workflow-steps">
            <div class="workflow-step">
              <div class="workflow-number">1</div>
              <div class="workflow-content">
                <h4>Generate Token</h4>
                <p>Request a realtime token using your API key</p>
              </div>
            </div>
            <div class="workflow-arrow">→</div>
            <div class="workflow-step">
              <div class="workflow-number">2</div>
              <div class="workflow-content">
                <h4>Connect Echo</h4>
                <p>Initialize Laravel Echo with the token</p>
              </div>
            </div>
            <div class="workflow-arrow">→</div>
            <div class="workflow-step">
              <div class="workflow-number">3</div>
              <div class="workflow-content">
                <h4>Subscribe & Listen</h4>
                <p>Monitor workspace channel for events</p>
              </div>
            </div>
          </div>

          <h3>Step 1: Generate Realtime Token</h3>
          <p>Call this endpoint from your backend to get a WebSocket authentication token:</p>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method post">POST</span>
              <code>/api/v1/workspaces/{id}/realtime/token</code>
            </div>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Request</span>
                <button
                  class="copy-btn llm"
                  @click="copyForLLM(codeSnippets.workspaceRealtimeToken, 'Generate a realtime WebSocket token for workspace monitoring. This token is used to authenticate your backend service to subscribe to the workspace channel. Token expires in 24 hours. Only workspace admins can generate tokens.')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                  </svg>
                  Copy for LLM
                </button>
              </div>
              <pre><code>{{ codeSnippets.workspaceRealtimeToken }}</code></pre>
            </div>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Response</span>
              </div>
              <pre><code>{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_at": "2026-01-17T10:00:00Z",
  "workspace_id": "550e8400-e29b-41d4-a716-446655440000",
  "channels": ["private-workspace.{workspace_id}"]
}</code></pre>
            </div>
          </div>

          <h3>Step 2 & 3: Connect and Subscribe</h3>
          <p>Use the token to connect to WebSocket and subscribe to workspace events:</p>

          <div class="code-block highlight-block">
            <div class="code-header">
              <span class="code-lang">JavaScript (Complete Example)</span>
              <button
                class="copy-btn llm"
                @click="copyForLLM(codeSnippets.workspaceMonitoring, 'Complete workspace monitoring setup. This code: 1) Gets a realtime token from your API, 2) Connects to WebSocket using Laravel Echo, 3) Subscribes to the workspace channel to receive all messages, conversations, and deletions across your entire workspace. Use this in your backend Node.js service to sync data to your CRM, trigger workflows, or log activity.')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Copy for LLM
              </button>
            </div>
            <pre><code>{{ codeSnippets.workspaceMonitoring }}</code></pre>
          </div>

          <h3>Event Types</h3>
          <div class="api-table">
            <table>
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Trigger</th>
                  <th>Use Case</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>message.created</code></td>
                  <td>New message sent in any conversation</td>
                  <td>Sync to CRM, sentiment analysis, auto-responses</td>
                </tr>
                <tr>
                  <td><code>conversation.created</code></td>
                  <td>New conversation started</td>
                  <td>Create support tickets, notify team, assign agents</td>
                </tr>
                <tr>
                  <td><code>message.deleted</code></td>
                  <td>Message removed by user</td>
                  <td>Audit logs, compliance tracking</td>
                </tr>
              </tbody>
            </table>
          </div>

          <h3>Event Payload Example</h3>
          <p>All events include full conversation context to minimize API calls:</p>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">message.created Event</span>
              <button
                class="copy-btn"
                @click="copyToClipboard(codeSnippets.workspaceEventPayload, 'Event payload')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
                Copy
              </button>
            </div>
            <pre><code>{{ codeSnippets.workspaceEventPayload }}</code></pre>
          </div>

          <div class="info-box">
            <div class="info-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
              </svg>
            </div>
            <div class="info-content">
              <strong>Performance Tip:</strong> Events are broadcast to both conversation participants (for the widget)
              and the workspace channel (for backend monitoring) simultaneously. Rich payloads include eager-loaded
              relationships to prevent N+1 queries.
            </div>
          </div>

          <div class="use-case-grid">
            <div class="use-case">
              <h4>🎯 CRM Integration</h4>
              <p>Sync conversations to Salesforce, HubSpot, or your custom CRM in real-time</p>
            </div>
            <div class="use-case">
              <h4>📊 Analytics</h4>
              <p>Track message volume, response times, and conversation patterns</p>
            </div>
            <div class="use-case">
              <h4>🤖 Automation</h4>
              <p>Trigger workflows based on keywords, sentiment, or user behavior</p>
            </div>
            <div class="use-case">
              <h4>📱 Notifications</h4>
              <p>Send custom alerts via Slack, email, or push notifications</p>
            </div>
          </div>
        </section>

        <!-- Typing Indicators -->
        <section id="typing" class="doc-section">
          <h2>Typing Indicators</h2>
          <p>Show real-time typing status to participants.</p>

          <div class="endpoint">
            <div class="endpoint-header">
              <span class="http-method post">POST</span>
              <code>/api/widget/conversations/{id}/typing</code>
            </div>

            <div class="code-block">
              <div class="code-header">
                <span class="code-lang">Request</span>
                <button class="copy-btn" @click="copyToClipboard(codeSnippets.typingPayload, 'Typing payload')">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                  Copy
                </button>
              </div>
              <pre><code>{ "is_typing": true }</code></pre>
            </div>
          </div>

          <div class="info-box">
            <div class="info-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
              </svg>
            </div>
            <div class="info-content">
              <strong>Best Practice:</strong> Send <code>is_typing: true</code> on input, then <code>is_typing: false</code> after 2 seconds of inactivity or when the message is sent.
            </div>
          </div>
        </section>

        <!-- Email Notifications -->
        <section id="email-notifications" class="doc-section email-notifications-section">
          <div class="section-header gradient-header">
            <span class="section-tag email-tag">Automated Notifications</span>
            <h2>📧 Email Notifications</h2>
            <p class="section-lead">
              Send automated email notifications when users miss messages. Fully customizable templates
              with your branding, triggers, and timing.
            </p>
          </div>

          <div class="email-features-grid">
            <div class="email-feature">
              <span class="email-feature-icon">🎨</span>
              <h4>Custom Branding</h4>
              <p>Your logo, colors, and footer in every email</p>
            </div>
            <div class="email-feature">
              <span class="email-feature-icon">⏱️</span>
              <h4>Flexible Triggers</h4>
              <p>Configure delays for 1-on-1, group chats, and more</p>
            </div>
            <div class="email-feature">
              <span class="email-feature-icon">✏️</span>
              <h4>Template Variables</h4>
              <p>Dynamic content with {{sender.name}} and more</p>
            </div>
            <div class="email-feature">
              <span class="email-feature-icon">🚀</span>
              <h4>Queued & Reliable</h4>
              <p>Background processing with automatic retries</p>
            </div>
          </div>

          <h3>Configuration</h3>
          <p>Email settings are stored in your workspace configuration:</p>

          <div class="code-block highlight-block">
            <div class="code-header">
              <span class="code-lang">Workspace Settings JSON</span>
              <button
                class="copy-btn llm"
                @click="copyForLLM(codeSnippets.emailSettings, 'Email notification configuration for workspace. Set enabled: true to activate. Configure trigger delays in minutes - missed_message_1on1_minutes controls how long to wait before sending missed message emails for direct chats. Email branding customizes the appearance with your logo, colors, from name, reply-to address, and footer text.')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Copy for LLM
              </button>
            </div>
            <pre><code>{{ codeSnippets.emailSettings }}</code></pre>
          </div>

          <h3>Trigger Types</h3>
          <div class="trigger-table">
            <div class="trigger-row">
              <div class="trigger-name">
                <code>missed_message_1on1</code>
                <span class="trigger-badge">15 min default</span>
              </div>
              <p>Sent when a user hasn't read a direct message after the configured delay</p>
            </div>
            <div class="trigger-row">
              <div class="trigger-name">
                <code>missed_message_group</code>
                <span class="trigger-badge">30 min default</span>
              </div>
              <p>Sent for unread group chat messages (longer delay to reduce noise)</p>
            </div>
            <div class="trigger-row">
              <div class="trigger-name">
                <code>new_participant</code>
                <span class="trigger-badge">instant</span>
              </div>
              <p>Notify when someone joins a conversation you're in</p>
            </div>
            <div class="trigger-row">
              <div class="trigger-name">
                <code>new_inquiry</code>
                <span class="trigger-badge">instant</span>
              </div>
              <p>Alert for first messages in new conversations (support/sales use case)</p>
            </div>
          </div>

          <h3>Email Templates</h3>
          <p>Customize templates with variables for dynamic content:</p>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Template Structure</span>
              <button
                class="copy-btn llm"
                @click="copyForLLM(codeSnippets.emailTemplate, 'Email template structure. event_type determines which trigger uses this template. subject_template and body_html support {{variable}} syntax for dynamic content. Available variables include sender.name, sender.email, recipient.name, message.content, conversation.name, and conversation_url. Separate body_text version for plain-text email clients.')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Copy for LLM
              </button>
            </div>
            <pre><code>{{ codeSnippets.emailTemplate }}</code></pre>
          </div>

          <h3>Available Variables</h3>
          <div class="variables-grid">
            <div class="variable-card">
              <code>{{sender.name}}</code>
              <span>Message sender's name</span>
            </div>
            <div class="variable-card">
              <code>{{sender.email}}</code>
              <span>Sender's email address</span>
            </div>
            <div class="variable-card">
              <code>{{recipient.name}}</code>
              <span>Email recipient's name</span>
            </div>
            <div class="variable-card">
              <code>{{message.content}}</code>
              <span>Message text content</span>
            </div>
            <div class="variable-card">
              <code>{{conversation.name}}</code>
              <span>Chat conversation title</span>
            </div>
            <div class="variable-card">
              <code>{{conversation_url}}</code>
              <span>Deep link to conversation</span>
            </div>
            <div class="variable-card">
              <code>{{unread_count}}</code>
              <span>Number of unread messages</span>
            </div>
            <div class="variable-card">
              <code>{{workspace.name}}</code>
              <span>Your workspace name</span>
            </div>
          </div>

          <div class="info-box">
            <div class="info-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
              </svg>
            </div>
            <div class="info-content">
              <strong>Smart Filtering:</strong> Emails are only sent to participants who: (1) haven't read the message,
              (2) have a valid email address, (3) aren't the message sender, (4) haven't muted the conversation.
              Anonymous users never receive emails.
            </div>
          </div>

          <div class="email-workflow">
            <h3>How It Works</h3>
            <div class="workflow-diagram">
              <div class="workflow-box">
                <div class="workflow-box-title">Message Sent</div>
                <p>User sends a message in conversation</p>
              </div>
              <div class="workflow-arrow-down">↓</div>
              <div class="workflow-box">
                <div class="workflow-box-title">Event Listener</div>
                <p>MessageCreated event triggers listener</p>
              </div>
              <div class="workflow-arrow-down">↓</div>
              <div class="workflow-box">
                <div class="workflow-box-title">Delayed Job</div>
                <p>Job scheduled after configured delay (e.g., 15 min)</p>
              </div>
              <div class="workflow-arrow-down">↓</div>
              <div class="workflow-box">
                <div class="workflow-box-title">Check Read Status</div>
                <p>Verify message is still unread</p>
              </div>
              <div class="workflow-arrow-down">↓</div>
              <div class="workflow-box success-box">
                <div class="workflow-box-title">Send Email</div>
                <p>Render template & send via email gateway</p>
              </div>
            </div>
          </div>

          <div class="info-box warning">
            <div class="info-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <div class="info-content">
              <strong>Coming Soon:</strong> Dashboard UI for managing email templates and settings. Currently,
              templates are seeded automatically and settings can be configured via API. Contact support for
              custom template design.
            </div>
          </div>
        </section>

        <!-- Dashboard API Keys -->
        <section id="dashboard-api-keys" class="doc-section">
          <h2>Managing API Keys</h2>
          <p>Create and manage keys in the Dashboard.</p>

          <div class="steps">
            <div class="step">
              <div class="step-number">1</div>
              <div class="step-content">
                <h3>Access the Dashboard</h3>
                <p>Log in at <NuxtLink to="/dashboard">/dashboard</NuxtLink> with your admin credentials.</p>
              </div>
            </div>

            <div class="step">
              <div class="step-number">2</div>
              <div class="step-content">
                <h3>Navigate to Settings</h3>
                <p>Go to Settings → API Keys to view and manage your keys.</p>
              </div>
            </div>

            <div class="step">
              <div class="step-number">3</div>
              <div class="step-content">
                <h3>Create Keys</h3>
                <p>
                  <strong>API Keys</strong> (<code>sk_</code>) — For server-to-server communication. Keep secret.<br>
                  <strong>Public Keys</strong> (<code>pk_</code>) — For widget integration. Domain-restricted.
                </p>
              </div>
            </div>
          </div>

          <div class="info-box warning">
            <div class="info-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <div class="info-content">
              <strong>API Key Security:</strong>
              <ul>
                <li>API keys (<code>sk_</code>) are only shown once when created</li>
                <li>Store them securely in environment variables</li>
                <li>Never commit keys to version control</li>
                <li>Rotate keys periodically</li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Dashboard Settings -->
        <section id="dashboard-settings" class="doc-section">
          <h2>Workspace Settings</h2>
          <p>Configure your workspace behavior and appearance.</p>

          <div class="api-table">
            <table>
              <thead>
                <tr>
                  <th>Setting</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Workspace Name</strong></td>
                  <td>Display name for your workspace</td>
                </tr>
                <tr>
                  <td><strong>Allowed Domains</strong></td>
                  <td>Domains where public keys can be used (CORS)</td>
                </tr>
                <tr>
                  <td><strong>Theme</strong></td>
                  <td>Default theme for widgets (light/dark)</td>
                </tr>
                <tr>
                  <td><strong>Accent Color</strong></td>
                  <td>Brand color for buttons and highlights</td>
                </tr>
                <tr>
                  <td><strong>Welcome Message</strong></td>
                  <td>Auto-greeting for new conversations</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Footer -->
        <footer class="docs-footer">
          <div class="footer-content">
            <p>Built with care. Questions? <a href="https://github.com/TNortnern/reusable-chat/issues">Open an issue</a>.</p>
            <p class="footer-links">
              <NuxtLink to="/demo">Demo</NuxtLink>
              <span>•</span>
              <NuxtLink to="/dashboard">Dashboard</NuxtLink>
              <span>•</span>
              <a href="https://github.com/TNortnern/reusable-chat">GitHub</a>
            </p>
          </div>
        </footer>
      </main>
    </div>
  </div>
</template>

<style scoped>
/* ===== Base & Typography ===== */
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.docs-container {
  --docs-bg: #0a0a0b;
  --docs-surface: #111113;
  --docs-surface-2: #18181b;
  --docs-border: #27272a;
  --docs-border-subtle: #1f1f23;
  --docs-text: #fafafa;
  --docs-text-secondary: #a1a1aa;
  --docs-text-muted: #71717a;
  --docs-accent: #3b82f6;
  --docs-accent-soft: rgba(59, 130, 246, 0.15);
  --docs-success: #22c55e;
  --docs-warning: #f59e0b;
  --docs-error: #ef4444;

  font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
  background: var(--docs-bg);
  color: var(--docs-text);
  min-height: 100vh;
}

/* ===== Header ===== */
.docs-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 64px;
  background: rgba(10, 10, 11, 0.8);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--docs-border-subtle);
  z-index: 100;
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: var(--docs-text);
}

.logo-icon {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, var(--docs-accent), #8b5cf6);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon svg {
  stroke: white;
}

.logo-text {
  font-weight: 700;
  font-size: 18px;
  letter-spacing: -0.02em;
}

.docs-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 4px 8px;
  background: var(--docs-surface-2);
  border: 1px solid var(--docs-border);
  border-radius: 6px;
  color: var(--docs-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.header-nav {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-link {
  padding: 8px 14px;
  color: var(--docs-text-secondary);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.15s;
}

.nav-link:hover {
  color: var(--docs-text);
  background: var(--docs-surface-2);
}

.nav-link.github {
  padding: 8px;
}

.mobile-menu-btn {
  display: none;
  padding: 8px;
  background: none;
  border: none;
  color: var(--docs-text);
  cursor: pointer;
}

@media (max-width: 768px) {
  .header-nav {
    display: none;
  }
  .mobile-menu-btn {
    display: block;
  }
}

/* ===== Mobile Nav ===== */
.mobile-nav-overlay {
  position: fixed;
  inset: 64px 0 0 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 90;
}

.mobile-nav {
  background: var(--docs-surface);
  padding: 16px;
  max-height: calc(100vh - 64px);
  overflow-y: auto;
}

.mobile-nav-enter-active,
.mobile-nav-leave-active {
  transition: opacity 0.2s;
}

.mobile-nav-enter-from,
.mobile-nav-leave-to {
  opacity: 0;
}

/* ===== Layout ===== */
.docs-layout {
  display: flex;
  max-width: 1400px;
  margin: 0 auto;
  padding-top: 64px;
}

/* ===== Sidebar ===== */
.docs-sidebar {
  width: 260px;
  flex-shrink: 0;
  position: sticky;
  top: 64px;
  height: calc(100vh - 64px);
  overflow-y: auto;
  padding: 32px 24px;
  border-right: 1px solid var(--docs-border-subtle);
}

@media (max-width: 1024px) {
  .docs-sidebar {
    display: none;
  }
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.nav-group-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--docs-text-muted);
  margin-bottom: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 8px 12px;
  background: none;
  border: none;
  color: var(--docs-text-secondary);
  font-size: 14px;
  font-weight: 500;
  text-align: left;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s;
  position: relative;
}

.nav-indicator {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: transparent;
  transition: all 0.15s;
}

.nav-item:hover {
  color: var(--docs-text);
  background: var(--docs-surface-2);
}

.nav-item.active {
  color: var(--docs-accent);
  background: var(--docs-accent-soft);
}

.nav-item.active .nav-indicator {
  background: var(--docs-accent);
}

/* ===== Main Content ===== */
.docs-content {
  flex: 1;
  min-width: 0;
  padding: 48px 64px;
}

@media (max-width: 768px) {
  .docs-content {
    padding: 32px 20px;
  }
}

/* ===== Sections ===== */
.doc-section {
  padding-bottom: 64px;
  margin-bottom: 48px;
  border-bottom: 1px solid var(--docs-border-subtle);
}

.doc-section:last-of-type {
  border-bottom: none;
}

.section-header {
  margin-bottom: 40px;
}

.section-tag {
  display: inline-block;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--docs-accent);
  margin-bottom: 12px;
}

h1 {
  font-size: 48px;
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.1;
  margin-bottom: 20px;
  background: linear-gradient(135deg, var(--docs-text) 0%, var(--docs-text-secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

h2 {
  font-size: 32px;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin-bottom: 16px;
  color: var(--docs-text);
}

h3 {
  font-size: 20px;
  font-weight: 600;
  margin: 32px 0 12px;
  color: var(--docs-text);
}

h4 {
  font-size: 16px;
  font-weight: 600;
  margin: 24px 0 12px;
  color: var(--docs-text);
}

.section-lead {
  font-size: 20px;
  color: var(--docs-text-secondary);
  line-height: 1.6;
  max-width: 640px;
}

p {
  color: var(--docs-text-secondary);
  line-height: 1.7;
  margin-bottom: 16px;
}

code {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.9em;
  padding: 2px 6px;
  background: var(--docs-surface-2);
  border: 1px solid var(--docs-border);
  border-radius: 4px;
  color: var(--docs-accent);
}

/* ===== Feature Grid ===== */
.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-top: 32px;
}

.feature-card {
  padding: 24px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 16px;
  transition: all 0.2s;
}

.feature-card:hover {
  border-color: var(--docs-accent);
  transform: translateY(-2px);
}

.feature-icon {
  width: 48px;
  height: 48px;
  background: var(--docs-accent-soft);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.feature-icon svg {
  stroke: var(--docs-accent);
}

.feature-card h3 {
  font-size: 18px;
  margin: 0 0 8px;
}

.feature-card p {
  font-size: 14px;
  margin: 0;
}

/* ===== Steps ===== */
.steps {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin: 32px 0;
}

.step {
  display: flex;
  gap: 20px;
}

.step-number {
  width: 36px;
  height: 36px;
  background: var(--docs-accent);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16px;
  color: white;
  flex-shrink: 0;
}

.step-content h3 {
  margin: 0 0 8px;
}

.step-content p {
  margin: 0;
}

/* ===== Code Block ===== */
.code-block {
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 12px;
  overflow: hidden;
  margin: 16px 0;
}

.code-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  background: var(--docs-surface-2);
  border-bottom: 1px solid var(--docs-border);
}

.code-lang {
  font-size: 12px;
  font-weight: 600;
  color: var(--docs-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.code-actions {
  display: flex;
  gap: 8px;
}

.copy-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 6px;
  color: var(--docs-text-secondary);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.copy-btn:hover {
  background: var(--docs-surface-2);
  color: var(--docs-text);
  border-color: var(--docs-text-muted);
}

.copy-btn.llm {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(59, 130, 246, 0.1));
  border-color: rgba(139, 92, 246, 0.3);
  color: #a78bfa;
}

.copy-btn.llm:hover {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(59, 130, 246, 0.2));
  border-color: rgba(139, 92, 246, 0.5);
}

.code-block pre {
  margin: 0;
  padding: 20px;
  overflow-x: auto;
}

.code-block code {
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  line-height: 1.6;
  color: var(--docs-text);
  background: none;
  border: none;
  padding: 0;
}

/* ===== Tables ===== */
.api-table {
  overflow-x: auto;
  margin: 24px 0;
}

.api-table table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.api-table th {
  text-align: left;
  padding: 12px 16px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  font-weight: 600;
  color: var(--docs-text);
}

.api-table td {
  padding: 12px 16px;
  border: 1px solid var(--docs-border);
  color: var(--docs-text-secondary);
}

.api-table td.required {
  color: var(--docs-accent);
  font-weight: 500;
}

.api-table td code {
  font-size: 13px;
}

/* ===== Info Box ===== */
.info-box {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: var(--docs-accent-soft);
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 12px;
  margin: 24px 0;
}

.info-box.warning {
  background: rgba(245, 158, 11, 0.1);
  border-color: rgba(245, 158, 11, 0.2);
}

.info-box.warning .info-icon svg {
  stroke: var(--docs-warning);
}

.info-icon {
  flex-shrink: 0;
}

.info-icon svg {
  stroke: var(--docs-accent);
}

.info-content {
  font-size: 14px;
}

.info-content ul {
  margin: 8px 0 0;
  padding-left: 20px;
}

.info-content li {
  margin: 4px 0;
  color: var(--docs-text-secondary);
}

/* ===== Architecture Diagram ===== */
.arch-diagram {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 32px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 16px;
  margin: 32px 0;
}

.arch-layer {
  width: 100%;
  max-width: 500px;
}

.arch-title {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--docs-text-muted);
  margin-bottom: 12px;
  text-align: center;
}

.arch-items {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: center;
}

.arch-item {
  padding: 10px 16px;
  background: var(--docs-surface-2);
  border: 1px solid var(--docs-border);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: var(--docs-text-secondary);
}

.arch-item.widget {
  background: var(--docs-accent-soft);
  border-color: var(--docs-accent);
  color: var(--docs-accent);
}

.arch-item.custom {
  background: rgba(139, 92, 246, 0.1);
  border-color: rgba(139, 92, 246, 0.3);
  color: #a78bfa;
}

.arch-arrow {
  color: var(--docs-text-muted);
}

/* ===== Auth Methods ===== */
.auth-methods {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.auth-method {
  padding: 24px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 12px;
}

.auth-method h3 {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0 0 12px;
}

.method-badge {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 4px 8px;
  border-radius: 4px;
}

.method-badge.backend {
  background: rgba(34, 197, 94, 0.15);
  color: var(--docs-success);
}

.method-badge.widget {
  background: var(--docs-accent-soft);
  color: var(--docs-accent);
}

.method-badge.session {
  background: rgba(139, 92, 246, 0.15);
  color: #a78bfa;
}

/* ===== Endpoints ===== */
.endpoint {
  margin: 32px 0;
  padding: 24px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 12px;
}

.endpoint-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.http-method {
  font-size: 12px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 6px;
  text-transform: uppercase;
}

.http-method.get {
  background: rgba(34, 197, 94, 0.15);
  color: var(--docs-success);
}

.http-method.post {
  background: var(--docs-accent-soft);
  color: var(--docs-accent);
}

.http-method.delete {
  background: rgba(239, 68, 68, 0.15);
  color: var(--docs-error);
}

.endpoint-header code {
  font-size: 15px;
  background: none;
  border: none;
  padding: 0;
  color: var(--docs-text);
}

/* ===== Toast ===== */
.copied-toast {
  position: fixed;
  bottom: 32px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: var(--docs-success);
  color: white;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 8px 30px rgba(34, 197, 94, 0.3);
  z-index: 1000;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(20px);
}

/* ===== Footer ===== */
.docs-footer {
  margin-top: 64px;
  padding-top: 32px;
  border-top: 1px solid var(--docs-border-subtle);
  text-align: center;
}

.footer-content p {
  color: var(--docs-text-muted);
  font-size: 14px;
}

.footer-content a {
  color: var(--docs-accent);
  text-decoration: none;
}

.footer-content a:hover {
  text-decoration: underline;
}

.footer-links {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 8px;
}

.footer-links span {
  color: var(--docs-border);
}

/* ===== Workspace Monitoring Section ===== */
.workspace-monitoring-section {
  position: relative;
  overflow: hidden;
}

.workspace-monitoring-section::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
  pointer-events: none;
  animation: pulse 8s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

.gradient-header h2 {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 36px;
}

.monitoring-tag {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: #60a5fa;
}

.monitoring-features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin: 32px 0;
}

.monitoring-feature {
  padding: 20px;
  background: linear-gradient(135deg, var(--docs-surface) 0%, var(--docs-surface-2) 100%);
  border: 1px solid var(--docs-border);
  border-radius: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.monitoring-feature:hover {
  transform: translateY(-4px);
  border-color: var(--docs-accent);
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
}

.feature-icon-lg {
  font-size: 32px;
  margin-bottom: 12px;
  display: block;
}

.workflow-steps {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin: 32px 0;
  padding: 32px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 16px;
  flex-wrap: wrap;
}

.workflow-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 150px;
}

.workflow-number {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, var(--docs-accent), #8b5cf6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 20px;
  color: white;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.workflow-content h4 {
  margin: 0 0 4px;
  font-size: 16px;
  color: var(--docs-text);
  text-align: center;
}

.workflow-content p {
  margin: 0;
  font-size: 13px;
  color: var(--docs-text-muted);
  text-align: center;
}

.workflow-arrow {
  color: var(--docs-accent);
  font-size: 24px;
  font-weight: 700;
}

.highlight-block {
  border: 2px solid var(--docs-accent);
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
  animation: highlight-glow 2s ease-in-out infinite;
}

@keyframes highlight-glow {
  0%, 100% { box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15); }
  50% { box-shadow: 0 8px 32px rgba(59, 130, 246, 0.25); }
}

.use-case-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin: 32px 0;
}

.use-case {
  padding: 20px;
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(59, 130, 246, 0.05));
  border: 1px solid rgba(139, 92, 246, 0.2);
  border-radius: 12px;
  transition: all 0.2s;
}

.use-case:hover {
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-2px);
}

.use-case h4 {
  margin: 0 0 8px;
  font-size: 16px;
  color: var(--docs-text);
}

.use-case p {
  margin: 0;
  font-size: 13px;
  color: var(--docs-text-secondary);
}

/* ===== Email Notifications Section ===== */
.email-notifications-section {
  position: relative;
}

.email-notifications-section::before {
  content: '';
  position: absolute;
  top: -30%;
  left: -10%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(236, 72, 153, 0.08) 0%, transparent 70%);
  pointer-events: none;
  animation: pulse 10s ease-in-out infinite;
}

.email-tag {
  background: linear-gradient(135deg, rgba(236, 72, 153, 0.2), rgba(139, 92, 246, 0.2));
  border: 1px solid rgba(236, 72, 153, 0.3);
  color: #f472b6;
}

.email-features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin: 32px 0;
}

.email-feature {
  padding: 20px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 12px;
  text-align: center;
  transition: all 0.3s;
}

.email-feature:hover {
  border-color: #ec4899;
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(236, 72, 153, 0.15);
}

.email-feature-icon {
  font-size: 36px;
  display: block;
  margin-bottom: 12px;
}

.email-feature h4 {
  margin: 0 0 8px;
  font-size: 16px;
}

.email-feature p {
  margin: 0;
  font-size: 13px;
}

.trigger-table {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin: 24px 0;
}

.trigger-row {
  padding: 20px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-left: 4px solid var(--docs-accent);
  border-radius: 12px;
  transition: all 0.2s;
}

.trigger-row:hover {
  border-left-color: #ec4899;
  background: var(--docs-surface-2);
}

.trigger-name {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}

.trigger-name code {
  font-size: 14px;
  font-weight: 600;
}

.trigger-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 3px 8px;
  background: rgba(139, 92, 246, 0.15);
  border-radius: 6px;
  color: #a78bfa;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.trigger-row p {
  margin: 0;
  font-size: 14px;
  padding-left: 16px;
}

.variables-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px;
  margin: 24px 0;
}

.variable-card {
  padding: 16px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: all 0.2s;
}

.variable-card:hover {
  border-color: var(--docs-accent);
  background: var(--docs-surface-2);
}

.variable-card code {
  font-size: 13px;
  font-weight: 600;
  color: #ec4899;
}

.variable-card span {
  font-size: 12px;
  color: var(--docs-text-muted);
}

.email-workflow {
  margin: 48px 0;
  padding: 32px;
  background: var(--docs-surface);
  border: 1px solid var(--docs-border);
  border-radius: 16px;
}

.workflow-diagram {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  margin-top: 24px;
}

.workflow-box {
  width: 100%;
  max-width: 400px;
  padding: 20px;
  background: var(--docs-surface-2);
  border: 2px solid var(--docs-border);
  border-radius: 12px;
  text-align: center;
  transition: all 0.3s;
}

.workflow-box:hover {
  border-color: var(--docs-accent);
  transform: scale(1.02);
}

.workflow-box.success-box {
  border-color: var(--docs-success);
  background: rgba(34, 197, 94, 0.05);
}

.workflow-box-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--docs-text);
  margin-bottom: 8px;
}

.workflow-box p {
  margin: 0;
  font-size: 14px;
  color: var(--docs-text-secondary);
}

.workflow-arrow-down {
  font-size: 24px;
  color: var(--docs-accent);
  font-weight: 700;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
  .workflow-steps {
    flex-direction: column;
  }

  .workflow-arrow {
    transform: rotate(90deg);
  }

  .monitoring-features,
  .email-features-grid,
  .use-case-grid {
    grid-template-columns: 1fr;
  }

  .variables-grid {
    grid-template-columns: 1fr;
  }
}
</style>
