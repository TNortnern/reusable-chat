<template>
  <div class="chat-widget-root">
    <!-- Chat Button -->
    <ChatButton
      :position="config.position || 'bottom-right'"
      :is-open="isOpen"
      :unread-count="unreadCount"
      @click="toggleChat"
    />

    <!-- Chat Window -->
    <div
      class="cw-window"
      :class="[config.position || 'bottom-right', { open: isOpen }]"
    >
      <!-- Header -->
      <div class="cw-header">
        <div class="cw-header-info">
          <h3>{{ config.title || 'Chat with us' }}</h3>
          <p v-if="config.subtitle">
            <span class="cw-status-dot" :class="{ connected: isConnected }" />
            {{ config.subtitle }}
          </p>
        </div>
        <button class="cw-close-btn" @click="toggleChat" aria-label="Close chat">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="cw-messages">
        <div class="cw-loading">
          <div class="cw-spinner" />
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="cw-messages">
        <div class="cw-error">
          {{ error }}
          <br>
          <button @click="initChat" style="margin-top: 8px; padding: 4px 12px; cursor: pointer;">
            Retry
          </button>
        </div>
      </div>

      <!-- Messages -->
      <MessageList
        v-else
        ref="messageListRef"
        :messages="messages"
        :typing-users="typingUsers"
        :welcome-message="config.welcomeMessage"
      />

      <!-- Input -->
      <MessageInput
        ref="messageInputRef"
        :placeholder="config.placeholder"
        :sending="sending"
        @send="handleSend"
        @typing="handleTyping"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import ChatButton from './ChatButton.vue'
import MessageList from './MessageList.vue'
import MessageInput from './MessageInput.vue'
import { ChatAPI } from '../api'
import { createEcho } from '../echo'
import type { WidgetConfig, Message, PendingFile, Conversation } from '../types'
import type Echo from 'laravel-echo'

const props = defineProps<{
  config: WidgetConfig
}>()

// State
const isOpen = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)
const sending = ref(false)
const isConnected = ref(false)
const unreadCount = ref(0)

const messages = ref<Message[]>([])
const typingUsers = ref<string[]>([])
const conversation = ref<Conversation | null>(null)

// Refs
const messageListRef = ref<InstanceType<typeof MessageList> | null>(null)
const messageInputRef = ref<InstanceType<typeof MessageInput> | null>(null)

// API and WebSocket
let api: ChatAPI | null = null
let echo: Echo<any> | null = null
let currentUserId: string | null = null

// Initialize chat
async function initChat() {
  if (!props.config.userToken || !props.config.apiUrl) {
    error.value = 'Missing configuration. Please provide userToken and apiUrl.'
    return
  }

  loading.value = true
  error.value = null

  try {
    // Initialize API
    api = new ChatAPI(props.config.apiUrl)
    api.setToken(props.config.userToken)

    // Get conversations or create one
    const conversations = await api.getConversations()

    if (conversations.length > 0) {
      // Use first conversation
      const convData = await api.getConversation(conversations[0].id)
      conversation.value = convData.conversation
      messages.value = convData.messages.map(m => ({
        ...m,
        is_own: m.sender_id === props.config.userId,
      }))
    }

    // Extract user ID from token or config
    currentUserId = props.config.userId || null

    // Set up WebSocket connection
    if (props.config.wsHost && props.config.wsKey) {
      setupWebSocket()
    }

    loading.value = false
  } catch (e: any) {
    error.value = e.message || 'Failed to initialize chat'
    loading.value = false
  }
}

function setupWebSocket() {
  if (!props.config.userToken) return

  try {
    echo = createEcho({
      wsHost: props.config.wsHost!,
      wsPort: props.config.wsPort || 443,
      wsKey: props.config.wsKey!,
      apiUrl: props.config.apiUrl!,
      token: props.config.userToken,
    })

    isConnected.value = true

    // Subscribe to conversation channel if we have one
    if (conversation.value) {
      subscribeToConversation(conversation.value.id)
    }
  } catch (e) {
    console.error('WebSocket setup failed:', e)
  }
}

function subscribeToConversation(conversationId: string) {
  if (!echo) return

  const channel = echo.private(`conversation.${conversationId}`)

  channel.listen('.message.created', (event: any) => {
    const newMessage: Message = {
      id: event.message.id,
      content: event.message.content,
      sender_id: event.message.sender?.id || event.message.sender_id,
      sender_name: event.message.sender?.name || 'Unknown',
      created_at: event.message.created_at,
      attachments: event.message.attachments || [],
      is_own: event.message.sender_id === currentUserId,
    }

    // Avoid duplicates
    if (!messages.value.find(m => m.id === newMessage.id)) {
      messages.value.push(newMessage)

      if (!isOpen.value && !newMessage.is_own) {
        unreadCount.value++
      }
    }
  })

  channel.listen('.typing', (event: any) => {
    if (event.user_id === currentUserId) return

    const userName = event.user_name || 'Someone'

    if (event.is_typing) {
      if (!typingUsers.value.includes(userName)) {
        typingUsers.value.push(userName)
      }
    } else {
      typingUsers.value = typingUsers.value.filter(u => u !== userName)
    }

    // Auto-clear typing after 5 seconds
    setTimeout(() => {
      typingUsers.value = typingUsers.value.filter(u => u !== userName)
    }, 5000)
  })
}

function toggleChat() {
  isOpen.value = !isOpen.value

  if (isOpen.value) {
    unreadCount.value = 0

    if (!api && !loading.value) {
      initChat()
    }

    nextTick(() => {
      messageInputRef.value?.focus()
      messageListRef.value?.scrollToBottom()
    })
  }
}

async function handleSend(content: string, files: PendingFile[]) {
  if (!api || !conversation.value) return

  sending.value = true

  try {
    // Upload attachments first
    const attachmentIds: string[] = []

    for (const file of files) {
      const attachment = await api.uploadAttachment(conversation.value.id, file.file)
      attachmentIds.push(attachment.id)
    }

    // Send message
    const message = await api.sendMessage(
      conversation.value.id,
      content,
      attachmentIds
    )

    // Add to local messages (with optimistic update)
    messages.value.push({
      ...message,
      is_own: true,
    })

    // Mark as read
    await api.markAsRead(conversation.value.id)
  } catch (e: any) {
    console.error('Failed to send message:', e)
  } finally {
    sending.value = false
  }
}

let typingTimeout: ReturnType<typeof setTimeout>

async function handleTyping(isTyping: boolean) {
  if (!api || !conversation.value) return

  clearTimeout(typingTimeout)

  try {
    await api.sendTypingIndicator(conversation.value.id, isTyping)
  } catch (e) {
    // Ignore typing indicator failures
  }
}

// Lifecycle
onMounted(() => {
  // Don't auto-init, wait for user to open chat
})

onUnmounted(() => {
  if (echo) {
    echo.disconnect()
  }
})

// Clear unread when opened
watch(isOpen, (open) => {
  if (open) {
    unreadCount.value = 0
  }
})
</script>
