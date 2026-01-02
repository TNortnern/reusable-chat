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
            <div v-if="msg.attachments && msg.attachments.length > 0" class="message-attachments">
              <div v-for="attachment in msg.attachments" :key="attachment.id" class="attachment">
                <img v-if="attachment.type.startsWith('image/')" :src="attachment.url" :alt="attachment.name" class="attachment-image" @click="openImage(attachment.url)" />
                <a v-else :href="attachment.url" target="_blank" class="attachment-file">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                  </svg>
                  {{ attachment.name }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <div v-if="typingUsers.length > 0" class="typing-indicator">
          {{ typingUsers.join(', ') }} {{ typingUsers.length === 1 ? 'is' : 'are' }} typing...
        </div>
      </div>

      <!-- File Preview -->
      <div v-if="pendingFiles.length > 0" class="file-preview-area">
        <div v-for="(file, index) in pendingFiles" :key="index" class="file-preview">
          <img v-if="file.type.startsWith('image/')" :src="file.preview" class="preview-image" />
          <div v-else class="preview-file">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
            </svg>
          </div>
          <span class="preview-name">{{ file.name }}</span>
          <button @click="removeFile(index)" class="remove-file-btn">&times;</button>
        </div>
      </div>

      <div class="input-area">
        <!-- Emoji Picker -->
        <div class="emoji-picker-container">
          <button @click="toggleEmojiPicker" class="icon-btn" title="Add emoji">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
              <line x1="9" y1="9" x2="9.01" y2="9"/>
              <line x1="15" y1="9" x2="15.01" y2="9"/>
            </svg>
          </button>
          <div v-if="showEmojiPicker" class="emoji-picker">
            <div class="emoji-grid">
              <button v-for="emoji in commonEmojis" :key="emoji" @click="insertEmoji(emoji)" class="emoji-btn">
                {{ emoji }}
              </button>
            </div>
          </div>
        </div>

        <!-- File Upload -->
        <button @click="triggerFileUpload" class="icon-btn" title="Attach file">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
          </svg>
        </button>
        <input ref="fileInput" type="file" multiple accept="image/*,.pdf,.doc,.docx,.txt" @change="handleFileSelect" class="hidden-file-input" />

        <input
          v-model="newMessage"
          type="text"
          placeholder="Type a message..."
          class="message-input"
          @keyup.enter="sendMessage"
          @input="handleTyping"
          @click="showEmojiPicker = false"
        />
        <button @click="sendMessage" :disabled="(!newMessage.trim() && pendingFiles.length === 0) || sending" class="send-btn">
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

// Demo configuration - API key from environment
const API_KEY = config.public.demoApiKey as string

interface Attachment {
  id: string
  name: string
  type: string
  url: string
  size: number
}

interface PendingFile {
  file: File
  name: string
  type: string
  preview: string
}

interface Message {
  id: string
  content: string
  sender_id: string
  sender_name: string
  created_at: string
  attachments?: Attachment[]
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

// Emoji picker
const showEmojiPicker = ref(false)
const commonEmojis = [
  '😀', '😂', '🥹', '😍', '🥰', '😘', '🤔', '😅',
  '😭', '😤', '🤯', '🥳', '😎', '🤩', '😇', '🙄',
  '👍', '👎', '❤️', '🔥', '💯', '✨', '🎉', '👀',
  '🙏', '💪', '🤝', '👏', '🫡', '🤷', '🤦', '💀'
]

// File upload
const fileInput = ref<HTMLInputElement | null>(null)
const pendingFiles = ref<PendingFile[]>([])

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
        'Accept': 'application/json',
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
        created_at: m.created_at,
        attachments: m.attachments || []
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
      const reverbHost = config.public.reverbHost as string || 'localhost'
      const reverbPort = parseInt(config.public.reverbPort as string) || 8080
      const isProduction = reverbHost !== 'localhost'

      const echoWithAuth = new Echo({
        broadcaster: 'reverb',
        key: config.public.reverbKey as string || 'chat-app-key',
        wsHost: reverbHost,
        wsPort: isProduction ? 443 : reverbPort,
        wssPort: isProduction ? 443 : reverbPort,
        forceTLS: isProduction,
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
              created_at: event.created_at,
              attachments: event.attachments || []
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
            const userName = event.name || event.user_name
            if (event.is_typing === false) {
              // User stopped typing
              typingUsers.value = typingUsers.value.filter(u => u !== userName)
            } else {
              // User is typing
              if (!typingUsers.value.includes(userName)) {
                typingUsers.value.push(userName)
              }
              // Remove after 3 seconds if no new typing event
              setTimeout(() => {
                typingUsers.value = typingUsers.value.filter(u => u !== userName)
              }, 3000)
            }
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
  if ((!newMessage.value.trim() && pendingFiles.value.length === 0) || sending.value) return

  // Clear typing indicator immediately when sending
  clearTimeout(typingTimeout)
  sendTypingIndicator(false)

  const content = newMessage.value.trim()
  const hasFiles = pendingFiles.value.length > 0
  newMessage.value = ''
  sending.value = true

  // Add optimistically (without attachments for now)
  const tempId = `temp-${Date.now()}`
  const tempAttachments = pendingFiles.value.map((f, i) => ({
    id: `temp-attach-${i}`,
    name: f.name,
    type: f.type,
    url: f.preview || '',
    size: f.file.size
  }))

  messages.value.push({
    id: tempId,
    content: content || (hasFiles ? '📎 Attachment' : ''),
    sender_id: sessionUserId.value,
    sender_name: userName.value,
    created_at: new Date().toISOString(),
    attachments: tempAttachments
  })
  scrollToBottom()

  try {
    // Upload files first if any
    let uploadedAttachments: Attachment[] = []
    if (hasFiles) {
      uploadedAttachments = await uploadFiles()
    }

    const res = await fetch(`${apiUrl}/api/widget/conversations/${roomId.value}/messages`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${sessionToken.value}`,
      },
      body: JSON.stringify({
        content: content || '',
        attachment_ids: uploadedAttachments.map(a => a.id)
      })
    })

    if (!res.ok) {
      throw new Error('Failed to send message')
    }

    const data = await res.json()
    // Update temp message with real data
    const tempMsg = messages.value.find(m => m.id === tempId)
    if (tempMsg) {
      tempMsg.id = data.id
      tempMsg.attachments = data.attachments || uploadedAttachments
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

const sendTypingIndicator = (isTyping: boolean) => {
  if (!roomId.value) return

  fetch(`${apiUrl}/api/widget/conversations/${roomId.value}/typing`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${sessionToken.value}`,
    },
    body: JSON.stringify({ is_typing: isTyping })
  }).catch(() => {})
}

const handleTyping = () => {
  if (!roomId.value) return
  clearTimeout(typingTimeout)

  // Send typing indicator
  sendTypingIndicator(true)

  // Auto-stop typing after 2 seconds of no input
  typingTimeout = setTimeout(() => {
    sendTypingIndicator(false)
  }, 2000)
}

// Emoji picker functions
const toggleEmojiPicker = () => {
  showEmojiPicker.value = !showEmojiPicker.value
}

const insertEmoji = (emoji: string) => {
  newMessage.value += emoji
  showEmojiPicker.value = false
}

// File upload functions
const triggerFileUpload = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files) return

  Array.from(input.files).forEach(file => {
    // Create preview for images
    let preview = ''
    if (file.type.startsWith('image/')) {
      preview = URL.createObjectURL(file)
    }

    pendingFiles.value.push({
      file,
      name: file.name,
      type: file.type,
      preview
    })
  })

  // Reset input
  input.value = ''
}

const removeFile = (index: number) => {
  const file = pendingFiles.value[index]
  if (file.preview) {
    URL.revokeObjectURL(file.preview)
  }
  pendingFiles.value.splice(index, 1)
}

const uploadFiles = async (): Promise<Attachment[]> => {
  if (pendingFiles.value.length === 0) return []

  const attachments: Attachment[] = []

  for (const pending of pendingFiles.value) {
    const formData = new FormData()
    formData.append('file', pending.file)

    try {
      const res = await fetch(`${apiUrl}/api/widget/conversations/${roomId.value}/attachments`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${sessionToken.value}`,
        },
        body: formData
      })

      if (res.ok) {
        const data = await res.json()
        attachments.push(data)
      }
    } catch (error) {
      console.error('Failed to upload file:', error)
    }

    // Clean up preview
    if (pending.preview) {
      URL.revokeObjectURL(pending.preview)
    }
  }

  pendingFiles.value = []
  return attachments
}

const openImage = (url: string) => {
  window.open(url, '_blank')
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

/* Icon buttons */
.icon-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  transition: background 0.2s, color 0.2s;
}

.icon-btn:hover {
  background: #f3f4f6;
  color: #667eea;
}

/* Emoji Picker */
.emoji-picker-container {
  position: relative;
}

.emoji-picker {
  position: absolute;
  bottom: 50px;
  left: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  padding: 12px;
  z-index: 100;
  width: 280px;
}

.emoji-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 4px;
}

.emoji-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.emoji-btn:hover {
  background: #f3f4f6;
}

/* File Upload */
.hidden-file-input {
  display: none;
}

.file-preview-area {
  padding: 8px 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.file-preview {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #f3f4f6;
  border-radius: 8px;
  max-width: 200px;
}

.preview-image {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 6px;
}

.preview-file {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e5e7eb;
  border-radius: 6px;
  color: #666;
}

.preview-name {
  font-size: 12px;
  color: #374151;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 100px;
}

.remove-file-btn {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #ef4444;
  color: white;
  border: none;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

/* Message Attachments */
.message-attachments {
  margin-top: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.attachment-image {
  max-width: 200px;
  max-height: 150px;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s;
}

.attachment-image:hover {
  transform: scale(1.02);
}

.attachment-file {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 6px;
  color: inherit;
  text-decoration: none;
  font-size: 13px;
}

.message.own .attachment-file {
  background: rgba(255, 255, 255, 0.2);
}

.attachment-file:hover {
  background: rgba(0, 0, 0, 0.1);
}

.message.own .attachment-file:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
