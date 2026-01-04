import type { Message } from '../types'

type MessageCallback = (message: Message, conversationId: string) => void
type TypingCallback = (data: { user_id: string; name: string; is_typing: boolean }, conversationId: string) => void
type ConnectionCallback = (connected: boolean) => void
type ReadReceiptCallback = (data: { user_id: string; conversation_id: string; last_read_at: string }) => void

interface PusherAuthResponse {
  auth: string
  channel_data?: string
}

export class WebSocketService {
  private socket: WebSocket | null = null
  private subscribedChannels: Set<string> = new Set()
  private pendingSubscriptions: Set<string> = new Set()
  private messageCallbacks: Set<MessageCallback> = new Set()
  private typingCallbacks: Set<TypingCallback> = new Set()
  private connectionCallbacks: Set<ConnectionCallback> = new Set()
  private readReceiptCallbacks: Set<ReadReceiptCallback> = new Set()
  private reconnectAttempts = 0
  private maxReconnectAttempts = 10
  private baseReconnectDelay = 1000
  private wsUrl: string
  private apiUrl: string
  private sessionToken: string
  private socketId: string | null = null
  private isConnecting = false
  private connectionPromise: Promise<void> | null = null

  constructor(apiUrl: string, wsKey: string, sessionToken: string) {
    this.apiUrl = apiUrl
    this.sessionToken = sessionToken

    // Derive WebSocket URL from API URL
    const url = new URL(apiUrl)
    const isSecure = url.protocol === 'https:' || url.hostname.includes('railway.app')
    const protocol = isSecure ? 'wss' : 'ws'
    const port = isSecure ? 443 : 8080
    const host = url.hostname

    this.wsUrl = `${protocol}://${host}:${port}/app/${wsKey}?protocol=7&client=js&version=8.3.0`
  }

  connect(): Promise<void> {
    if (this.socket?.readyState === WebSocket.OPEN) {
      return Promise.resolve()
    }

    if (this.isConnecting && this.connectionPromise) {
      return this.connectionPromise
    }

    this.isConnecting = true
    this.connectionPromise = new Promise((resolve, reject) => {
      try {
        this.socket = new WebSocket(this.wsUrl)

        this.socket.onopen = () => {
          console.log('[RC Widget] WebSocket connected')
          // Wait for pusher:connection_established event
        }

        this.socket.onclose = (event) => {
          console.log('[RC Widget] WebSocket disconnected', event.code, event.reason)
          this.socketId = null
          this.isConnecting = false
          this.subscribedChannels.clear()
          this.connectionCallbacks.forEach(cb => cb(false))

          if (event.code !== 1000) {
            this.attemptReconnect()
          }
        }

        this.socket.onerror = (error) => {
          console.error('[RC Widget] WebSocket error:', error)
          this.isConnecting = false
          reject(error)
        }

        this.socket.onmessage = (event) => {
          try {
            const data = JSON.parse(event.data)
            this.handleMessage(data, resolve)
          } catch (e) {
            // Ignore parse errors
          }
        }
      } catch (error) {
        this.isConnecting = false
        reject(error)
      }
    })

    return this.connectionPromise
  }

  private handleMessage(data: any, resolveConnection?: () => void): void {
    const eventName = data.event

    switch (eventName) {
      case 'pusher:connection_established':
        const connectionData = typeof data.data === 'string' ? JSON.parse(data.data) : data.data
        this.socketId = connectionData.socket_id
        this.reconnectAttempts = 0
        this.isConnecting = false
        this.connectionCallbacks.forEach(cb => cb(true))
        console.log('[RC Widget] Pusher connection established, socket_id:', this.socketId)

        // Re-subscribe to pending channels
        this.pendingSubscriptions.forEach(channel => {
          this.subscribeToChannel(channel)
        })
        this.pendingSubscriptions.clear()

        if (resolveConnection) resolveConnection()
        break

      case 'pusher_internal:subscription_succeeded':
        const channel = data.channel
        this.subscribedChannels.add(channel)
        console.log('[RC Widget] Subscribed to channel:', channel)
        break

      case 'pusher:error':
        console.error('[RC Widget] Pusher error:', data.data)
        break

      case 'message.created':
      case '.message.created':
        this.handleNewMessage(data)
        break

      case 'user.typing':
      case '.user.typing':
        this.handleTypingIndicator(data)
        break

      case 'message.read':
      case '.message.read':
        this.handleReadReceipt(data)
        break

      default:
        // Check for prefixed events on channels
        if (eventName?.startsWith('.') || eventName?.startsWith('App\\')) {
          // Laravel broadcast event
          const cleanEvent = eventName.replace(/^\./, '').replace(/^App\\Events\\/, '')
          if (cleanEvent.includes('MessageCreated') || cleanEvent === 'message.created') {
            this.handleNewMessage(data)
          } else if (cleanEvent.includes('UserTyping') || cleanEvent === 'user.typing') {
            this.handleTypingIndicator(data)
          }
        }
    }
  }

  private handleNewMessage(data: any): void {
    const payload = typeof data.data === 'string' ? JSON.parse(data.data) : data.data
    const conversationId = data.channel?.replace('private-conversation.', '') || payload.conversation_id || ''
    this.messageCallbacks.forEach(cb => cb(payload, conversationId))
  }

  private handleTypingIndicator(data: any): void {
    const payload = typeof data.data === 'string' ? JSON.parse(data.data) : data.data
    const conversationId = data.channel?.replace('private-conversation.', '') || payload.conversation_id || ''
    this.typingCallbacks.forEach(cb => cb(payload, conversationId))
  }

  private handleReadReceipt(data: any): void {
    const payload = typeof data.data === 'string' ? JSON.parse(data.data) : data.data
    this.readReceiptCallbacks.forEach(cb => cb(payload))
  }

  private attemptReconnect(): void {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      console.error('[RC Widget] Max reconnection attempts reached')
      return
    }

    this.reconnectAttempts++
    // Exponential backoff with jitter
    const delay = Math.min(
      this.baseReconnectDelay * Math.pow(2, this.reconnectAttempts - 1) + Math.random() * 1000,
      30000
    )

    console.log(`[RC Widget] Reconnecting in ${Math.round(delay / 1000)}s (attempt ${this.reconnectAttempts})...`)

    setTimeout(() => {
      this.connect().then(() => {
        // Re-subscribe to all channels after reconnection
        const channels = Array.from(this.subscribedChannels)
        this.subscribedChannels.clear()
        channels.forEach(channel => {
          const conversationId = channel.replace('private-conversation.', '')
          this.subscribeConversation(conversationId)
        })
      }).catch(() => {
        // Connection failed, will retry in onclose handler
      })
    }, delay)
  }

  private async authenticateChannel(channel: string): Promise<PusherAuthResponse | null> {
    if (!this.socketId) {
      console.error('[RC Widget] Cannot authenticate channel: no socket_id')
      return null
    }

    try {
      const params = new URLSearchParams({
        socket_id: this.socketId,
        channel_name: channel,
        auth_token: this.sessionToken
      })

      const response = await fetch(`${this.apiUrl}/api/widget/broadcasting/auth`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Authorization': `Bearer ${this.sessionToken}`
        },
        body: params.toString()
      })

      if (!response.ok) {
        const error = await response.json().catch(() => ({}))
        console.error('[RC Widget] Auth failed:', error)
        return null
      }

      return await response.json()
    } catch (error) {
      console.error('[RC Widget] Auth request failed:', error)
      return null
    }
  }

  private async subscribeToChannel(channel: string): Promise<void> {
    if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
      this.pendingSubscriptions.add(channel)
      return
    }

    if (this.subscribedChannels.has(channel)) {
      return
    }

    // For private channels, authenticate first
    if (channel.startsWith('private-')) {
      const auth = await this.authenticateChannel(channel)
      if (!auth) {
        console.error('[RC Widget] Failed to authenticate channel:', channel)
        return
      }

      this.socket.send(JSON.stringify({
        event: 'pusher:subscribe',
        data: {
          channel: channel,
          auth: auth.auth,
          channel_data: auth.channel_data
        }
      }))
    } else {
      this.socket.send(JSON.stringify({
        event: 'pusher:subscribe',
        data: { channel }
      }))
    }
  }

  async subscribeConversation(conversationId: string): Promise<void> {
    const channel = `private-conversation.${conversationId}`

    if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
      // Queue subscription for when connected
      this.pendingSubscriptions.add(channel)
      return
    }

    await this.subscribeToChannel(channel)
  }

  subscribeUser(userId: string): void {
    const channel = `private-user.${userId}`
    this.subscribeToChannel(channel)
  }

  unsubscribeConversation(conversationId: string): void {
    const channel = `private-conversation.${conversationId}`
    this.subscribedChannels.delete(channel)
    this.pendingSubscriptions.delete(channel)

    if (this.socket?.readyState === WebSocket.OPEN) {
      this.socket.send(JSON.stringify({
        event: 'pusher:unsubscribe',
        data: { channel }
      }))
    }
  }

  onMessage(callback: MessageCallback): () => void {
    this.messageCallbacks.add(callback)
    return () => this.messageCallbacks.delete(callback)
  }

  onTyping(callback: TypingCallback): () => void {
    this.typingCallbacks.add(callback)
    return () => this.typingCallbacks.delete(callback)
  }

  onConnection(callback: ConnectionCallback): () => void {
    this.connectionCallbacks.add(callback)
    return () => this.connectionCallbacks.delete(callback)
  }

  onReadReceipt(callback: ReadReceiptCallback): () => void {
    this.readReceiptCallbacks.add(callback)
    return () => this.readReceiptCallbacks.delete(callback)
  }

  isConnected(): boolean {
    return this.socket?.readyState === WebSocket.OPEN && this.socketId !== null
  }

  disconnect(): void {
    this.pendingSubscriptions.clear()
    this.subscribedChannels.clear()
    if (this.socket) {
      this.socket.close(1000, 'User initiated disconnect')
      this.socket = null
    }
    this.socketId = null
    this.isConnecting = false
    this.connectionPromise = null
  }
}
