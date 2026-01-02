<template>
  <div class="widget-container" :class="{ 'dark-mode': isDark }">
    <!-- Header -->
    <div class="widget-header">
      <div class="flex items-center gap-3">
        <div class="widget-logo">
          <UIcon name="i-heroicons-chat-bubble-left-right" class="w-5 h-5" />
        </div>
        <div>
          <h3 class="widget-title">Chat with us</h3>
          <p class="widget-subtitle">
            <span v-if="isConnected" class="online-dot"></span>
            {{ isConnected ? 'Online' : 'Connecting...' }}
          </p>
        </div>
      </div>
      <button @click="closeWidget" class="close-btn">
        <UIcon name="i-heroicons-x-mark" class="w-5 h-5" />
      </button>
    </div>

    <!-- Messages -->
    <div ref="messagesContainer" class="messages-container">
      <div v-if="messages.length === 0" class="empty-state">
        <UIcon name="i-heroicons-chat-bubble-left-right" class="w-12 h-12 text-gray-300" />
        <p>Start a conversation!</p>
      </div>

      <div
        v-for="message in messages"
        :key="message.id"
        class="message"
        :class="message.isOwn ? 'message-sent' : 'message-received'"
      >
        <div class="message-bubble">
          {{ message.content }}
        </div>
        <div class="message-time">{{ formatTime(message.created_at) }}</div>
      </div>

      <div v-if="isTyping" class="typing-indicator">
        <span></span><span></span><span></span>
      </div>
    </div>

    <!-- Input -->
    <div class="input-container">
      <input
        v-model="newMessage"
        type="text"
        placeholder="Type a message..."
        class="message-input"
        @keyup.enter="sendMessage"
        @input="handleTyping"
      />
      <button
        @click="sendMessage"
        :disabled="!newMessage.trim()"
        class="send-btn"
      >
        <UIcon name="i-heroicons-paper-airplane" class="w-5 h-5" />
      </button>
    </div>

    <!-- Connection Status -->
    <div v-if="connectionError" class="connection-error">
      {{ connectionError }}
      <button @click="reconnect">Retry</button>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

interface Message {
  id: string
  content: string
  isOwn: boolean
  created_at: string
}

const route = useRoute()
const { $echo } = useNuxtApp()

const workspaceId = route.query.workspace as string || 'demo'
const theme = route.query.theme as string || 'auto'

const messages = ref<Message[]>([])
const newMessage = ref('')
const isConnected = ref(false)
const isTyping = ref(false)
const connectionError = ref('')
const messagesContainer = ref<HTMLElement | null>(null)
const conversationId = ref<string | null>(null)
const userId = ref(`user-${Date.now()}`)

const isDark = computed(() => {
  if (theme === 'dark') return true
  if (theme === 'light') return false
  return window.matchMedia('(prefers-color-scheme: dark)').matches
})

// Initialize Echo connection
onMounted(() => {
  initializeChat()
})

const initializeChat = async () => {
  try {
    // For demo, create a mock conversation
    conversationId.value = `conv-${workspaceId}-${userId.value}`

    // Subscribe to conversation channel
    if ($echo) {
      const channel = $echo.private(`conversation.${conversationId.value}`)

      channel
        .listen('.message.created', (event: any) => {
          if (event.message.sender_id !== userId.value) {
            messages.value.push({
              id: event.message.id,
              content: event.message.content,
              isOwn: false,
              created_at: event.message.created_at
            })
            scrollToBottom()
          }
        })
        .listen('.user.typing', (event: any) => {
          if (event.user_id !== userId.value) {
            isTyping.value = true
            setTimeout(() => {
              isTyping.value = false
            }, 3000)
          }
        })
        .error((error: any) => {
          console.error('Channel error:', error)
          connectionError.value = 'Connection error. Please try again.'
        })

      isConnected.value = true
    }

    // Add welcome message
    messages.value.push({
      id: 'welcome',
      content: 'Hi there! How can we help you today?',
      isOwn: false,
      created_at: new Date().toISOString()
    })
  } catch (error) {
    console.error('Failed to initialize chat:', error)
    connectionError.value = 'Failed to connect. Please refresh.'
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim()) return

  const content = newMessage.value.trim()
  newMessage.value = ''

  // Add message optimistically
  const tempId = `temp-${Date.now()}`
  messages.value.push({
    id: tempId,
    content,
    isOwn: true,
    created_at: new Date().toISOString()
  })
  scrollToBottom()

  // Simulate response for demo
  setTimeout(() => {
    messages.value.push({
      id: `response-${Date.now()}`,
      content: getAutoResponse(content),
      isOwn: false,
      created_at: new Date().toISOString()
    })
    scrollToBottom()
  }, 1000)
}

const getAutoResponse = (message: string): string => {
  const lower = message.toLowerCase()
  if (lower.includes('hello') || lower.includes('hi')) {
    return 'Hello! How can I assist you today?'
  }
  if (lower.includes('help')) {
    return 'I\'d be happy to help! What do you need assistance with?'
  }
  if (lower.includes('price') || lower.includes('cost')) {
    return 'Our pricing starts at $29/month. Would you like more details?'
  }
  return 'Thanks for your message! A team member will respond shortly.'
}

let typingTimeout: ReturnType<typeof setTimeout>
const handleTyping = () => {
  clearTimeout(typingTimeout)
  // Would broadcast typing event here
  typingTimeout = setTimeout(() => {
    // Stop typing indicator
  }, 1000)
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const closeWidget = () => {
  window.parent.postMessage({ type: 'chat:close' }, '*')
}

const reconnect = () => {
  connectionError.value = ''
  initializeChat()
}

onUnmounted(() => {
  if ($echo && conversationId.value) {
    $echo.leave(`conversation.${conversationId.value}`)
  }
})
</script>

<style scoped>
.widget-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #ffffff;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.widget-container.dark-mode {
  background: #1a1a2e;
  color: #e0e0e0;
}

/* Header */
.widget-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
}

.widget-logo {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.widget-title {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.widget-subtitle {
  font-size: 13px;
  opacity: 0.9;
  margin: 2px 0 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.online-dot {
  width: 8px;
  height: 8px;
  background: #22c55e;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 8px;
  padding: 8px;
  color: white;
  cursor: pointer;
  transition: background 0.2s;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Messages */
.messages-container {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #9ca3af;
}

.message {
  display: flex;
  flex-direction: column;
  max-width: 80%;
}

.message-received {
  align-self: flex-start;
}

.message-sent {
  align-self: flex-end;
}

.message-bubble {
  padding: 12px 16px;
  border-radius: 18px;
  font-size: 14px;
  line-height: 1.5;
}

.message-received .message-bubble {
  background: #f3f4f6;
  color: #1f2937;
  border-bottom-left-radius: 4px;
}

.dark-mode .message-received .message-bubble {
  background: #374151;
  color: #e5e7eb;
}

.message-sent .message-bubble {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  border-bottom-right-radius: 4px;
}

.message-time {
  font-size: 11px;
  color: #9ca3af;
  margin-top: 4px;
  padding: 0 4px;
}

/* Typing Indicator */
.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 12px 16px;
  background: #f3f4f6;
  border-radius: 18px;
  width: fit-content;
  align-self: flex-start;
}

.dark-mode .typing-indicator {
  background: #374151;
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  background: #9ca3af;
  border-radius: 50%;
  animation: bounce 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes bounce {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-8px); }
}

/* Input */
.input-container {
  display: flex;
  gap: 12px;
  padding: 16px 20px;
  border-top: 1px solid #e5e7eb;
  background: #ffffff;
}

.dark-mode .input-container {
  border-color: #374151;
  background: #1a1a2e;
}

.message-input {
  flex: 1;
  padding: 12px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 24px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}

.dark-mode .message-input {
  background: #374151;
  border-color: #4b5563;
  color: #e5e7eb;
}

.message-input:focus {
  border-color: #2563eb;
}

.send-btn {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s, transform 0.2s;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.05);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Connection Error */
.connection-error {
  padding: 12px 20px;
  background: #fef2f2;
  color: #dc2626;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.connection-error button {
  background: #dc2626;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
}
</style>
