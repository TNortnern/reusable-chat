<template>
  <div class="demo-container">
    <!-- Create Room Screen -->
    <div v-if="!roomId && !joined" class="join-screen">
      <div class="join-card">
        <h1>🗨️ Real-Time Chat Demo</h1>
        <p>Create a chat room and invite your friends!</p>
        <button @click="createRoom" :disabled="creatingRoom" class="join-btn">
          {{ creatingRoom ? 'Creating Room...' : 'Create Chat Room' }}
        </button>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        <p class="info-text">
          You'll get a shareable link to send to your friends
        </p>
      </div>
    </div>

    <!-- Join Screen -->
    <div v-else-if="!joined" class="join-screen">
      <div class="join-card">
        <h1>🗨️ Join Chat Room</h1>
        <p>Enter your name to join the conversation</p>
        <input
          v-model="userName"
          type="text"
          placeholder="Your name"
          class="name-input"
          @keyup.enter="joinChat"
        />
        <button @click="joinChat" :disabled="!userName.trim() || joining" class="join-btn">
          {{ joining ? 'Joining...' : 'Join Chat' }}
        </button>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        <div class="share-link-box">
          <p class="share-label">Share this link with friends:</p>
          <div class="share-url">
            <input type="text" :value="shareUrl" readonly ref="shareInput" />
            <button @click="copyShareUrl" class="copy-btn">
              {{ copied ? '✓' : 'Copy' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Chat Screen -->
    <div v-else class="chat-screen">
      <div class="chat-header">
        <div>
          <h2>Demo Chat Room</h2>
          <p class="participants-count">
            <span class="status-dot" :class="{ connected: isConnected }"></span>
            {{ isConnected ? 'Connected' : 'Connecting...' }} • {{ participants.length }} online
          </p>
        </div>
        <div class="participants-avatars">
          <div
            v-for="p in participants"
            :key="p.id"
            class="avatar"
            :title="p.name"
          >
            {{ p.name.charAt(0).toUpperCase() }}
          </div>
        </div>
      </div>

      <div ref="messagesContainer" class="messages-area">
        <div v-if="messages.length === 0" class="empty-state">
          <p>No messages yet. Say hello!</p>
        </div>

        <div
          v-for="msg in messages"
          :key="msg.id"
          class="message"
          :class="{ own: msg.sender_id === sessionUserId }"
        >
          <div class="message-avatar">{{ msg.sender_name.charAt(0).toUpperCase() }}</div>
          <div class="message-content">
            <div class="message-header">
              <span class="sender-name">{{ msg.sender_name }}</span>
              <span class="message-time">{{ formatTime(msg.created_at) }}</span>
            </div>
            <div class="message-text">{{ msg.content }}</div>
          </div>
        </div>

        <div v-if="typingUsers.length > 0" class="typing-indicator">
          {{ typingUsers.join(', ') }} {{ typingUsers.length === 1 ? 'is' : 'are' }} typing...
        </div>
      </div>

      <div class="input-area">
        <input
          v-model="newMessage"
          type="text"
          placeholder="Type a message..."
          class="message-input"
          @keyup.enter="sendMessage"
          @input="handleTyping"
        />
        <button @click="sendMessage" :disabled="!newMessage.trim() || sending" class="send-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

const config = useRuntimeConfig()
const route = useRoute()
const router = useRouter()
const { $echo } = useNuxtApp()

// Demo configuration - using the test workspace API key
const API_KEY = 'REDACTED_DEMO_KEY'

interface Message {
  id: string
  content: string
  sender_id: string
  sender_name: string
  created_at: string
}

interface Participant {
  id: string
  name: string
}

// Room from URL
const roomId = ref<string | null>(route.query.room as string || null)

const userName = ref('')
const joined = ref(false)
const joining = ref(false)
const creatingRoom = ref(false)
const isConnected = ref(false)
const sessionToken = ref('')
const sessionUserId = ref('')
const errorMessage = ref('')
const copied = ref(false)
const shareInput = ref<HTMLInputElement | null>(null)

const messages = ref<Message[]>([])
const newMessage = ref('')
const sending = ref(false)
const participants = ref<Participant[]>([])
const typingUsers = ref<string[]>([])
const messagesContainer = ref<HTMLElement | null>(null)

let typingTimeout: ReturnType<typeof setTimeout>
let echoChannel: any = null

const apiUrl = config.public.apiUrl || 'http://localhost:3021'

// Computed share URL
const shareUrl = computed(() => {
  if (typeof window !== 'undefined' && roomId.value) {
    return `${window.location.origin}/demo?room=${roomId.value}`
  }
  return ''
})

// Create a new room
const createRoom = async () => {
  creatingRoom.value = true
  errorMessage.value = ''

  try {
    // Create a demo room via API
    const res = await fetch(`${apiUrl}/api/v1/demo/rooms`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': API_KEY,
      },
      body: JSON.stringify({
        name: `Demo Room ${new Date().toLocaleString()}`
      })
    })

    if (!res.ok) {
      const err = await res.json().catch(() => ({}))
      throw new Error(err.message || 'Failed to create room')
    }

    const data = await res.json()
    roomId.value = data.id

    // Update URL without reload
    router.replace({ query: { room: data.id } })
  } catch (error: any) {
    console.error('Failed to create room:', error)
    errorMessage.value = error.message || 'Failed to create room. Please try again.'
  } finally {
    creatingRoom.value = false
  }
}

// Copy share URL
const copyShareUrl = async () => {
  try {
    await navigator.clipboard.writeText(shareUrl.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    // Fallback for older browsers
    if (shareInput.value) {
      shareInput.value.select()
      document.execCommand('copy')
      copied.value = true
      setTimeout(() => { copied.value = false }, 2000)
    }
  }
}

const joinChat = async () => {
  if (!userName.value.trim() || joining.value) return

  joining.value = true
  errorMessage.value = ''

  try {
    // Step 1: Create user via API
    const externalId = `demo-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
    const userRes = await fetch(`${apiUrl}/api/v1/users`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': API_KEY,
      },
      body: JSON.stringify({
        external_id: externalId,
        name: userName.value.trim(),
      })
    })

    if (!userRes.ok) {
      const err = await userRes.json().catch(() => ({}))
      throw new Error(err.message || 'Failed to create user')
    }

    const userData = await userRes.json()
    sessionUserId.value = userData.id

    // Step 2: Create session for the user
    const sessionRes = await fetch(`${apiUrl}/api/v1/sessions`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': API_KEY,
      },
      body: JSON.stringify({
        user_id: userData.id,
      })
    })

    if (!sessionRes.ok) {
      const err = await sessionRes.json().catch(() => ({}))
      throw new Error(err.message || 'Failed to create session')
    }

    const sessionData = await sessionRes.json()
    sessionToken.value = sessionData.token

    // Add self to participants
    participants.value.push({
      id: userData.id,
      name: userName.value.trim()
    })

    // Join the conversation
    await joinConversation()

    // Connect to Echo
    connectEcho()

    joined.value = true
  } catch (error: any) {
    console.error('Failed to join:', error)
    errorMessage.value = error.message || 'Failed to join chat. Please try again.'
  } finally {
    joining.value = false
  }
}

const joinConversation = async () => {
  if (!roomId.value) return

  // Add user as participant
  try {
    const partRes = await fetch(`${apiUrl}/api/v1/conversations/${roomId.value}/participants`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': API_KEY,
      },
      body: JSON.stringify({
        user_id: sessionUserId.value
      })
    })
    // 422 means user already a participant - that's okay
    if (!partRes.ok && partRes.status !== 422) {
      const err = await partRes.json().catch(() => ({}))
      console.error('Failed to add participant:', err)
    }
  } catch (e) {
    console.error('Error adding participant:', e)
  }

  // Load existing messages
  try {
    const res = await fetch(`${apiUrl}/api/widget/conversations/${roomId.value}`, {
      headers: {
        'Authorization': `Bearer ${sessionToken.value}`,
      }
    })

    if (res.ok) {
      const data = await res.json()
      // Messages can be in data.messages (paginated) or data.messages.data
      const msgList = data.messages?.data || data.messages || []
      messages.value = msgList.map((m: any) => ({
        id: m.id,
        content: m.content,
        sender_id: m.sender?.id || m.sender_id,
        sender_name: m.sender?.name || 'Unknown',
        created_at: m.created_at
      })).reverse() // Reverse to show oldest first
      scrollToBottom()
    } else {
      const err = await res.json().catch(() => ({}))
      console.error('Failed to load conversation:', res.status, err)
    }
  } catch (e) {
    console.error('Failed to load messages:', e)
  }
}

const connectEcho = () => {
  if (!roomId.value) {
    console.error('No room ID')
    return
  }

  const channelName = `conversation.${roomId.value}`
  const token = sessionToken.value

  // Import Echo and Pusher dynamically to create a new instance with auth
  import('laravel-echo').then(({ default: Echo }) => {
    import('pusher-js').then(({ default: Pusher }) => {
      // Create a fresh Echo instance with auth configured
      const echoWithAuth = new Echo({
        broadcaster: 'reverb',
        key: 'chat-app-key',
        wsHost: 'localhost',
        wsPort: 8080,
        wssPort: 8080,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        authEndpoint: `${apiUrl}/api/widget/broadcasting/auth`,
        auth: {
          headers: {
            Authorization: `Bearer ${token}`
          }
        },
        authorizer: (channel: any) => {
          return {
            authorize: (socketId: string, callback: Function) => {
              console.log('Authorizing channel:', channel.name)
              const params = new URLSearchParams({
                socket_id: socketId,
                channel_name: channel.name,
                auth_token: token
              })

              fetch(`${apiUrl}/api/widget/broadcasting/auth`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: params.toString()
              })
              .then(response => {
                if (!response.ok) {
                  return response.json().then(err => {
                    throw new Error(err.error || 'Auth failed')
                  })
                }
                return response.json()
              })
              .then(data => {
                console.log('Auth success:', data)
                callback(null, data)
              })
              .catch(error => {
                console.error('Auth error:', error)
                callback(error, null)
              })
            }
          }
        }
      })

      // Subscribe to channel
      echoChannel = echoWithAuth.private(channelName)
        .listen('.message.created', (event: any) => {
          // Don't add own messages (they're added optimistically)
          if (event.sender?.id !== sessionUserId.value) {
            messages.value.push({
              id: event.id,
              content: event.content,
              sender_id: event.sender?.id,
              sender_name: event.sender?.name || 'Unknown',
              created_at: event.created_at
            })
            scrollToBottom()

            // Clear typing indicator when message received from that user
            const senderName = event.sender?.name
            if (senderName) {
              typingUsers.value = typingUsers.value.filter(u => u !== senderName)
            }

            // Add to participants if new
            if (!participants.value.find(p => p.id === event.sender?.id)) {
              participants.value.push({
                id: event.sender?.id,
                name: event.sender?.name
              })
            }
          }
        })
        .listen('.user.typing', (event: any) => {
          if (event.user_id !== sessionUserId.value) {
            if (!typingUsers.value.includes(event.user_name)) {
              typingUsers.value.push(event.user_name)
            }
            // Remove after 3 seconds
            setTimeout(() => {
              typingUsers.value = typingUsers.value.filter(u => u !== event.user_name)
            }, 3000)
          }
        })
        .subscribed(() => {
          isConnected.value = true
          console.log('Connected to channel')
        })
        .error((error: any) => {
          console.error('Channel error:', error)
          isConnected.value = false
        })
    })
  })
}


const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return

  const content = newMessage.value.trim()
  newMessage.value = ''
  sending.value = true

  // Add optimistically
  const tempId = `temp-${Date.now()}`
  messages.value.push({
    id: tempId,
    content,
    sender_id: sessionUserId.value,
    sender_name: userName.value,
    created_at: new Date().toISOString()
  })
  scrollToBottom()

  try {
    const res = await fetch(`${apiUrl}/api/widget/conversations/${roomId.value}/messages`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${sessionToken.value}`,
      },
      body: JSON.stringify({ content })
    })

    if (!res.ok) {
      throw new Error('Failed to send message')
    }

    const data = await res.json()
    // Update temp message with real ID
    const tempMsg = messages.value.find(m => m.id === tempId)
    if (tempMsg) {
      tempMsg.id = data.id
    }
  } catch (error) {
    console.error('Failed to send:', error)
    // Remove failed message
    messages.value = messages.value.filter(m => m.id !== tempId)
    // Could show inline error here
  } finally {
    sending.value = false
  }
}

const handleTyping = () => {
  if (!roomId.value) return
  clearTimeout(typingTimeout)

  // Send typing indicator
  fetch(`${apiUrl}/api/widget/conversations/${roomId.value}/typing`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${sessionToken.value}`,
    }
  }).catch(() => {})

  typingTimeout = setTimeout(() => {}, 1000)
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

onUnmounted(() => {
  if (echoChannel && roomId.value) {
    $echo?.leave(`conversation.${roomId.value}`)
  }
})
</script>

<style scoped>
.demo-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Join Screen */
.join-screen {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.join-card {
  background: white;
  border-radius: 20px;
  padding: 40px;
  max-width: 400px;
  width: 100%;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.join-card h1 {
  margin: 0 0 8px;
  font-size: 28px;
  color: #1a1a2e;
}

.join-card p {
  margin: 0 0 24px;
  color: #666;
}

.name-input {
  width: 100%;
  padding: 14px 18px;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  font-size: 16px;
  margin-bottom: 16px;
  transition: border-color 0.2s;
}

.name-input:focus {
  outline: none;
  border-color: #667eea;
}

.join-btn {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, opacity 0.2s;
}

.join-btn:hover:not(:disabled) {
  transform: translateY(-2px);
}

.join-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.info-text {
  margin-top: 20px !important;
  font-size: 13px;
  color: #999;
}

.share-link-box {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.share-label {
  font-size: 13px;
  color: #666;
  margin: 0 0 10px !important;
}

.share-url {
  display: flex;
  gap: 8px;
}

.share-url input {
  flex: 1;
  padding: 10px 14px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 13px;
  background: #f9fafb;
  color: #374151;
}

.copy-btn {
  padding: 10px 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
  min-width: 60px;
}

.copy-btn:hover {
  transform: translateY(-1px);
}

.error-message {
  color: #dc2626;
  font-size: 14px;
  margin: 12px 0 0 !important;
  padding: 10px;
  background: #fef2f2;
  border-radius: 8px;
}

/* Chat Screen */
.chat-screen {
  height: 100vh;
  display: flex;
  flex-direction: column;
  max-width: 800px;
  margin: 0 auto;
  background: white;
}

.chat-header {
  padding: 16px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chat-header h2 {
  margin: 0;
  font-size: 20px;
}

.participants-count {
  margin: 4px 0 0;
  font-size: 13px;
  opacity: 0.9;
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #fbbf24;
}

.status-dot.connected {
  background: #34d399;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.participants-avatars {
  display: flex;
  gap: -8px;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

/* Messages */
.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.empty-state {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #999;
}

.message {
  display: flex;
  gap: 12px;
  max-width: 70%;
}

.message.own {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.message-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  flex-shrink: 0;
}

.message.own .message-avatar {
  background: linear-gradient(135deg, #34d399 0%, #059669 100%);
}

.message-content {
  background: #f3f4f6;
  padding: 10px 14px;
  border-radius: 18px;
  border-top-left-radius: 4px;
}

.message.own .message-content {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-top-left-radius: 18px;
  border-top-right-radius: 4px;
}

.message-header {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 4px;
}

.sender-name {
  font-weight: 600;
  font-size: 13px;
}

.message.own .sender-name {
  color: rgba(255, 255, 255, 0.9);
}

.message-time {
  font-size: 11px;
  color: #999;
}

.message.own .message-time {
  color: rgba(255, 255, 255, 0.7);
}

.message-text {
  font-size: 14px;
  line-height: 1.5;
}

.typing-indicator {
  font-size: 13px;
  color: #666;
  font-style: italic;
}

/* Input */
.input-area {
  padding: 16px 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 12px;
}

.message-input {
  flex: 1;
  padding: 12px 18px;
  border: 2px solid #e5e7eb;
  border-radius: 24px;
  font-size: 15px;
  transition: border-color 0.2s;
}

.message-input:focus {
  outline: none;
  border-color: #667eea;
}

.send-btn {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.05);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
