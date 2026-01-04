import type { Message } from '../types'

type MessageCallback = (message: Message, conversationId: string) => void
type TypingCallback = (data: { user_id: string; name: string; is_typing: boolean }, conversationId: string) => void
type ConnectionCallback = (connected: boolean) => void

export class WebSocketService {
  private socket: WebSocket | null = null
  private subscriptions: Map<string, Set<string>> = new Map()
  private messageCallbacks: Set<MessageCallback> = new Set()
  private typingCallbacks: Set<TypingCallback> = new Set()
  private connectionCallbacks: Set<ConnectionCallback> = new Set()
  private reconnectAttempts = 0
  private maxReconnectAttempts = 5
  private reconnectDelay = 1000
  private wsUrl: string
  private sessionToken: string

  constructor(wsHost: string, wsKey: string, sessionToken: string) {
    const isSecure = wsHost.includes('railway.app') || wsHost.includes('https')
    const protocol = isSecure ? 'wss' : 'ws'
    const port = isSecure ? 443 : 8080
    this.wsUrl = `${protocol}://${wsHost}:${port}/app/${wsKey}?protocol=7&client=js&version=8.3.0`
    this.sessionToken = sessionToken
  }

  connect(): void {
    if (this.socket?.readyState === WebSocket.OPEN) return

    this.socket = new WebSocket(this.wsUrl)

    this.socket.onopen = () => {
      console.log('[RC Widget] WebSocket connected')
      this.reconnectAttempts = 0
      this.connectionCallbacks.forEach(cb => cb(true))

      // Re-subscribe to channels
      this.subscriptions.forEach((_, channel) => {
        this.subscribeToChannel(channel)
      })
    }

    this.socket.onclose = () => {
      console.log('[RC Widget] WebSocket disconnected')
      this.connectionCallbacks.forEach(cb => cb(false))
      this.attemptReconnect()
    }

    this.socket.onerror = (error) => {
      console.error('[RC Widget] WebSocket error:', error)
    }

    this.socket.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data)
        this.handleMessage(data)
      } catch (e) {
        // Ignore parse errors
      }
    }
  }

  private handleMessage(data: any): void {
    if (data.event === 'message.created') {
      const payload = typeof data.data === 'string' ? JSON.parse(data.data) : data.data
      const conversationId = data.channel?.replace('private-conversation.', '') || ''
      this.messageCallbacks.forEach(cb => cb(payload, conversationId))
    } else if (data.event === 'user.typing') {
      const payload = typeof data.data === 'string' ? JSON.parse(data.data) : data.data
      const conversationId = data.channel?.replace('private-conversation.', '') || ''
      this.typingCallbacks.forEach(cb => cb(payload, conversationId))
    }
  }

  private attemptReconnect(): void {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      console.error('[RC Widget] Max reconnection attempts reached')
      return
    }

    this.reconnectAttempts++
    const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1)

    setTimeout(() => {
      console.log(`[RC Widget] Reconnecting (attempt ${this.reconnectAttempts})...`)
      this.connect()
    }, delay)
  }

  private subscribeToChannel(channel: string): void {
    if (!this.socket || this.socket.readyState !== WebSocket.OPEN) return

    // Pusher protocol subscription
    this.socket.send(JSON.stringify({
      event: 'pusher:subscribe',
      data: {
        channel: `private-${channel}`,
        auth: this.sessionToken, // Will need proper auth
      }
    }))
  }

  subscribeConversation(conversationId: string): void {
    const channel = `conversation.${conversationId}`
    if (!this.subscriptions.has(channel)) {
      this.subscriptions.set(channel, new Set())
    }
    this.subscribeToChannel(channel)
  }

  unsubscribeConversation(conversationId: string): void {
    const channel = `conversation.${conversationId}`
    this.subscriptions.delete(channel)

    if (this.socket?.readyState === WebSocket.OPEN) {
      this.socket.send(JSON.stringify({
        event: 'pusher:unsubscribe',
        data: { channel: `private-${channel}` }
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

  disconnect(): void {
    if (this.socket) {
      this.socket.close()
      this.socket = null
    }
  }
}
