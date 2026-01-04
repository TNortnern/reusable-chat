import { LitElement, html, css, nothing, unsafeCSS } from 'lit'
import { customElement, property, state, query } from 'lit/decorators.js'
import type { WidgetConfig, Session, Conversation, Message, Attachment, User } from './types'
import { ApiService } from './services/api'
import { WebSocketService } from './services/websocket'
import { storage } from './services/storage'
import baseStyles from './styles/base.css?inline'

interface PendingAttachment {
  id: string
  file: File
  preview?: string
  uploading: boolean
  uploaded?: { id: string; url: string; name: string; type: string }
  error?: string
}

interface TypingUser {
  user_id: string
  name: string
  timeout?: ReturnType<typeof setTimeout>
}

@customElement('reusable-chat')
export class ReusableChat extends LitElement {
  static styles = css`${unsafeCSS(baseStyles)}`

  @property({ attribute: 'api-key' }) apiKey = ''
  @property({ attribute: 'user-id' }) userId = ''
  @property({ attribute: 'user-name' }) userName = ''
  @property({ attribute: 'user-email' }) userEmail = ''
  @property({ attribute: 'position' }) position: 'bottom-right' | 'bottom-left' = 'bottom-right'
  @property({ attribute: 'theme' }) theme: 'light' | 'dark' = 'light'
  @property({ attribute: 'accent-color' }) accentColor = ''
  @property({ type: Boolean, attribute: 'show-branding' }) showBranding = true
  @property({ attribute: 'api-url' }) apiUrl = 'https://api-production-de24c.up.railway.app'
  @property({ attribute: 'ws-host' }) wsHost = 'api-production-de24c.up.railway.app'
  @property({ attribute: 'ws-key' }) wsKey = 'reusable-chat-key'

  @state() private isOpen = false
  @state() private wsConnected = false
  @state() private isLoading = true
  @state() private isLoadingMessages = false
  @state() private session: Session | null = null
  @state() private conversations: Conversation[] = []
  @state() private selectedConversation: Conversation | null = null
  @state() private messages: Message[] = []
  @state() private unreadCount = 0
  @state() private messageInput = ''
  @state() private pendingAttachments: PendingAttachment[] = []
  @state() private typingUsers: Map<string, TypingUser> = new Map()
  @state() private lightboxImage: string | null = null
  @state() private isSending = false

  @query('#message-input') messageInputEl!: HTMLTextAreaElement
  @query('#messages-container') messagesContainer!: HTMLElement
  @query('#file-input') fileInput!: HTMLInputElement

  private api: ApiService | null = null
  private ws: WebSocketService | null = null
  private typingTimeout: ReturnType<typeof setTimeout> | null = null
  private lastTypingSent = 0

  async connectedCallback() {
    super.connectedCallback()
    await this.initialize()
  }

  disconnectedCallback() {
    super.disconnectedCallback()
    this.ws?.disconnect()
  }

  private async initialize() {
    if (!this.apiKey) {
      console.error('[Reusable Chat] api-key attribute is required')
      return
    }

    this.api = new ApiService(this.apiUrl, this.apiKey)

    try {
      // Try to restore session
      const savedToken = storage.getSessionToken()
      if (savedToken && this.userId) {
        this.api.setSessionToken(savedToken)
        try {
          const { data } = await this.api.getConversations()
          this.conversations = data
          this.session = { token: savedToken, user: { id: this.userId, name: this.userName }, expires_at: '' }
          this.setupWebSocket(savedToken)
        } catch {
          storage.clearSession()
          await this.createSession()
        }
      } else if (this.userId && this.userName) {
        await this.createSession()
      }
    } catch (error) {
      console.error('[Reusable Chat] Initialization error:', error)
    } finally {
      this.isLoading = false
    }
  }

  private async createSession() {
    if (!this.api || !this.userId || !this.userName) return

    try {
      const session = await this.api.createSession(
        this.userId,
        this.userName,
        this.userEmail
      )
      this.session = session
      this.api.setSessionToken(session.token)
      storage.setSessionToken(session.token)

      const { data } = await this.api.getConversations()
      this.conversations = data
      this.unreadCount = data.reduce((sum, c) => sum + (c.unread_count || 0), 0)

      this.setupWebSocket(session.token)
    } catch (error) {
      console.error('[Reusable Chat] Session creation error:', error)
    }
  }

  private setupWebSocket(token: string) {
    this.ws = new WebSocketService(this.wsHost, this.wsKey, token)

    this.ws.onConnection((connected) => {
      this.wsConnected = connected
    })

    this.ws.onMessage((message, conversationId) => {
      if (this.selectedConversation?.id === conversationId) {
        // Remove optimistic message if this is the confirmed version
        this.messages = this.messages.filter(m => !m.isOptimistic || m.id !== message.id)
        this.messages = [...this.messages, message]
        this.scrollToBottom()
      }
      // Update conversation list
      this.updateConversationLastMessage(conversationId, message)
    })

    this.ws.onTyping((data, conversationId) => {
      if (this.selectedConversation?.id === conversationId && data.user_id !== this.userId) {
        if (data.is_typing) {
          const existing = this.typingUsers.get(data.user_id)
          if (existing?.timeout) clearTimeout(existing.timeout)

          const timeout = setTimeout(() => {
            this.typingUsers.delete(data.user_id)
            this.typingUsers = new Map(this.typingUsers)
          }, 3000)

          this.typingUsers.set(data.user_id, { ...data, timeout })
          this.typingUsers = new Map(this.typingUsers)
        } else {
          const existing = this.typingUsers.get(data.user_id)
          if (existing?.timeout) clearTimeout(existing.timeout)
          this.typingUsers.delete(data.user_id)
          this.typingUsers = new Map(this.typingUsers)
        }
      }
    })

    this.ws.connect()
  }

  private updateConversationLastMessage(conversationId: string, message: Message) {
    this.conversations = this.conversations.map(c => {
      if (c.id === conversationId) {
        return { ...c, last_message: message, updated_at: message.created_at }
      }
      return c
    })
  }

  private toggleOpen() {
    this.isOpen = !this.isOpen
  }

  private async selectConversation(conv: Conversation) {
    this.selectedConversation = conv
    this.isLoadingMessages = true
    this.messages = []
    this.typingUsers.clear()

    try {
      if (this.api) {
        const { messages } = await this.api.getConversation(conv.id)
        this.messages = messages.data
        this.ws?.subscribeConversation(conv.id)
        await this.api.markAsRead(conv.id)

        // Update unread count
        this.conversations = this.conversations.map(c => {
          if (c.id === conv.id) return { ...c, unread_count: 0 }
          return c
        })
        this.unreadCount = this.conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0)
      }
    } catch (error) {
      console.error('[Reusable Chat] Failed to load messages:', error)
    } finally {
      this.isLoadingMessages = false
      await this.updateComplete
      this.scrollToBottom()
    }
  }

  private goBack() {
    if (this.selectedConversation) {
      this.ws?.unsubscribeConversation(this.selectedConversation.id)
    }
    this.selectedConversation = null
    this.messages = []
    this.messageInput = ''
    this.pendingAttachments = []
    this.typingUsers.clear()
  }

  private async handleSend() {
    if ((!this.messageInput.trim() && this.pendingAttachments.length === 0) || this.isSending) return
    if (!this.api || !this.selectedConversation) return

    const content = this.messageInput.trim()
    const attachmentIds = this.pendingAttachments
      .filter(a => a.uploaded)
      .map(a => a.uploaded!.id)

    // Create optimistic message
    const optimisticMessage: Message = {
      id: `optimistic-${Date.now()}`,
      content,
      sender_id: this.userId,
      sender: { id: this.userId, name: this.userName },
      attachments: this.pendingAttachments
        .filter(a => a.uploaded)
        .map(a => ({
          id: a.uploaded!.id,
          name: a.uploaded!.name,
          type: a.uploaded!.type,
          url: a.uploaded!.url,
          size: a.file.size
        })),
      created_at: new Date().toISOString(),
      isOptimistic: true
    }

    this.messages = [...this.messages, optimisticMessage]
    this.messageInput = ''
    this.pendingAttachments = []
    this.isSending = true

    await this.updateComplete
    this.scrollToBottom()
    this.adjustTextareaHeight()

    try {
      const message = await this.api.sendMessage(
        this.selectedConversation.id,
        content,
        attachmentIds
      )
      // Replace optimistic with real message
      this.messages = this.messages.map(m =>
        m.id === optimisticMessage.id ? message : m
      )
    } catch (error) {
      console.error('[Reusable Chat] Failed to send message:', error)
      // Mark as failed
      this.messages = this.messages.map(m => {
        if (m.id === optimisticMessage.id) {
          return { ...m, isOptimistic: false, failed: true } as Message
        }
        return m
      })
    } finally {
      this.isSending = false
    }
  }

  private handleInputChange(e: Event) {
    const target = e.target as HTMLTextAreaElement
    this.messageInput = target.value
    this.adjustTextareaHeight()
    this.sendTypingIndicator()
  }

  private handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault()
      this.handleSend()
    }
  }

  private adjustTextareaHeight() {
    if (!this.messageInputEl) return
    this.messageInputEl.style.height = 'auto'
    const maxHeight = 100 // ~4 lines
    this.messageInputEl.style.height = Math.min(this.messageInputEl.scrollHeight, maxHeight) + 'px'
  }

  private sendTypingIndicator() {
    if (!this.api || !this.selectedConversation) return

    const now = Date.now()
    if (now - this.lastTypingSent < 2000) return

    this.lastTypingSent = now
    this.api.sendTyping(this.selectedConversation.id, true)

    if (this.typingTimeout) clearTimeout(this.typingTimeout)
    this.typingTimeout = setTimeout(() => {
      this.api?.sendTyping(this.selectedConversation!.id, false)
    }, 3000)
  }

  private handleAttachClick() {
    this.fileInput?.click()
  }

  private async handleFileSelect(e: Event) {
    const input = e.target as HTMLInputElement
    const files = input.files
    if (!files || !this.api || !this.selectedConversation) return

    for (const file of Array.from(files)) {
      const pendingId = `pending-${Date.now()}-${Math.random()}`
      const pending: PendingAttachment = {
        id: pendingId,
        file,
        uploading: true,
        preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : undefined
      }

      this.pendingAttachments = [...this.pendingAttachments, pending]

      try {
        const uploaded = await this.api.uploadAttachment(this.selectedConversation.id, file)
        this.pendingAttachments = this.pendingAttachments.map(p =>
          p.id === pendingId ? { ...p, uploading: false, uploaded } : p
        )
      } catch (error) {
        this.pendingAttachments = this.pendingAttachments.map(p =>
          p.id === pendingId ? { ...p, uploading: false, error: 'Upload failed' } : p
        )
      }
    }

    input.value = ''
  }

  private removePendingAttachment(id: string) {
    const attachment = this.pendingAttachments.find(a => a.id === id)
    if (attachment?.preview) {
      URL.revokeObjectURL(attachment.preview)
    }
    this.pendingAttachments = this.pendingAttachments.filter(a => a.id !== id)
  }

  private scrollToBottom() {
    if (this.messagesContainer) {
      this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight
    }
  }

  private openLightbox(url: string) {
    this.lightboxImage = url
  }

  private closeLightbox() {
    this.lightboxImage = null
  }

  private formatTime(dateString: string): string {
    const date = new Date(dateString)
    const now = new Date()
    const diff = now.getTime() - date.getTime()

    const minutes = Math.floor(diff / 60000)
    if (minutes < 1) return 'Just now'
    if (minutes < 60) return `${minutes}m ago`

    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours}h ago`

    const days = Math.floor(hours / 24)
    if (days < 7) return `${days}d ago`

    return date.toLocaleDateString()
  }

  private getInitials(name: string): string {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
  }

  private isOwnMessage(message: Message): boolean {
    return message.sender_id === this.userId
  }

  private getParticipantNames(): string {
    if (!this.selectedConversation) return ''
    const others = this.selectedConversation.participants.filter(p => p.id !== this.userId)
    if (others.length === 0) return 'No participants'
    if (others.length === 1) return others[0].name
    if (others.length === 2) return `${others[0].name} and ${others[1].name}`
    return `${others[0].name} and ${others.length - 1} others`
  }

  private renderConversationList() {
    if (this.isLoading) {
      return html`
        <div class="content">
          ${[1, 2, 3].map(() => html`
            <div class="skeleton-item">
              <div class="skeleton-avatar"></div>
              <div class="skeleton-text">
                <div class="skeleton-line"></div>
                <div class="skeleton-line short"></div>
              </div>
            </div>
          `)}
        </div>
      `
    }

    if (this.conversations.length === 0) {
      return html`
        <div class="content">
          <div class="empty-state">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p class="empty-title">No conversations yet</p>
            <p class="empty-subtitle">Messages will appear here</p>
          </div>
        </div>
      `
    }

    return html`
      <div class="content">
        ${this.conversations.map(conv => html`
          <div class="conversation-item" @click=${() => this.selectConversation(conv)}>
            <div class="avatar">${this.getInitials(conv.participants.find(p => p.id !== this.userId)?.name || conv.participants[0]?.name || 'U')}</div>
            <div class="conv-info">
              <div class="conv-header">
                <span class="conv-name">${conv.participants.find(p => p.id !== this.userId)?.name || conv.participants[0]?.name || 'Unknown'}</span>
                ${conv.last_message ? html`
                  <span class="conv-time">${this.formatTime(conv.last_message.created_at)}</span>
                ` : nothing}
              </div>
              <div class="conv-preview-row">
                <span class="conv-preview">${conv.last_message?.content || 'No messages'}</span>
                ${conv.unread_count > 0 ? html`
                  <span class="conv-unread">${conv.unread_count}</span>
                ` : nothing}
              </div>
            </div>
          </div>
        `)}
      </div>
    `
  }

  private renderMessageThread() {
    return html`
      <div class="content messages-content" id="messages-container">
        ${this.isLoadingMessages ? html`
          <div class="loading-messages">
            <div class="spinner"></div>
          </div>
        ` : this.messages.length === 0 ? html`
          <div class="empty-state">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p class="empty-title">No messages yet</p>
            <p class="empty-subtitle">Start the conversation!</p>
          </div>
        ` : html`
          <div class="messages-list">
            ${this.messages.map(msg => this.renderMessage(msg))}
          </div>
        `}
        ${this.renderTypingIndicator()}
      </div>
      ${this.renderInputArea()}
    `
  }

  private renderMessage(msg: Message) {
    const isOwn = this.isOwnMessage(msg)
    const hasAttachments = msg.attachments && msg.attachments.length > 0

    return html`
      <div class="message ${isOwn ? 'own' : 'other'} ${msg.isOptimistic ? 'optimistic' : ''} ${(msg as any).failed ? 'failed' : ''}">
        ${!isOwn ? html`
          <div class="message-avatar">
            ${msg.sender?.avatar_url
              ? html`<img src="${msg.sender.avatar_url}" alt="${msg.sender.name}" />`
              : html`<span>${this.getInitials(msg.sender?.name || 'U')}</span>`
            }
          </div>
        ` : nothing}
        <div class="message-content">
          ${!isOwn && this.selectedConversation && this.selectedConversation.participants.length > 2 ? html`
            <div class="message-sender">${msg.sender?.name}</div>
          ` : nothing}
          ${hasAttachments ? html`
            <div class="message-attachments">
              ${msg.attachments!.map(att => this.renderAttachment(att))}
            </div>
          ` : nothing}
          ${msg.content ? html`
            <div class="message-bubble">
              <span class="message-text">${msg.content}</span>
            </div>
          ` : nothing}
          <div class="message-meta">
            <span class="message-time">${this.formatTime(msg.created_at)}</span>
            ${msg.isOptimistic ? html`<span class="message-status">Sending...</span>` : nothing}
            ${(msg as any).failed ? html`<span class="message-status failed">Failed</span>` : nothing}
          </div>
        </div>
      </div>
    `
  }

  private renderAttachment(att: Attachment) {
    const isImage = att.type.startsWith('image/')

    if (isImage) {
      return html`
        <div class="attachment-image" @click=${() => this.openLightbox(att.url)}>
          <img src="${att.url}" alt="${att.name}" loading="lazy" />
        </div>
      `
    }

    return html`
      <a class="attachment-file" href="${att.url}" target="_blank" download="${att.name}">
        <svg class="file-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
        </svg>
        <div class="file-info">
          <span class="file-name">${att.name}</span>
          <span class="file-size">${this.formatFileSize(att.size)}</span>
        </div>
        <svg class="download-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
      </a>
    `
  }

  private formatFileSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
  }

  private renderTypingIndicator() {
    if (this.typingUsers.size === 0) return nothing

    const names = Array.from(this.typingUsers.values()).map(u => u.name)
    const text = names.length === 1
      ? `${names[0]} is typing`
      : names.length === 2
        ? `${names[0]} and ${names[1]} are typing`
        : `${names[0]} and ${names.length - 1} others are typing`

    return html`
      <div class="typing-indicator">
        <div class="typing-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <span class="typing-text">${text}</span>
      </div>
    `
  }

  private renderInputArea() {
    return html`
      <div class="input-area">
        ${this.pendingAttachments.length > 0 ? html`
          <div class="pending-attachments">
            ${this.pendingAttachments.map(att => html`
              <div class="pending-attachment ${att.error ? 'error' : ''}">
                ${att.preview ? html`
                  <img src="${att.preview}" alt="${att.file.name}" />
                ` : html`
                  <div class="pending-file">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                      <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                  </div>
                `}
                ${att.uploading ? html`
                  <div class="upload-overlay">
                    <div class="spinner small"></div>
                  </div>
                ` : nothing}
                <button class="remove-attachment" @click=${() => this.removePendingAttachment(att.id)}>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
            `)}
          </div>
        ` : nothing}
        <div class="input-row">
          <button class="input-btn attach-btn" @click=${this.handleAttachClick} title="Attach file">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
            </svg>
          </button>
          <input type="file" id="file-input" multiple hidden @change=${this.handleFileSelect} />
          <textarea
            id="message-input"
            class="message-input"
            placeholder="Type a message..."
            rows="1"
            .value=${this.messageInput}
            @input=${this.handleInputChange}
            @keydown=${this.handleKeyDown}
          ></textarea>
          <button
            class="input-btn send-btn ${this.messageInput.trim() || this.pendingAttachments.some(a => a.uploaded) ? 'active' : ''}"
            @click=${this.handleSend}
            ?disabled=${!this.messageInput.trim() && !this.pendingAttachments.some(a => a.uploaded)}
            title="Send message"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
      </div>
    `
  }

  private renderLightbox() {
    if (!this.lightboxImage) return nothing

    return html`
      <div class="lightbox" @click=${this.closeLightbox}>
        <button class="lightbox-close" @click=${this.closeLightbox}>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
        <img src="${this.lightboxImage}" alt="Full size" @click=${(e: Event) => e.stopPropagation()} />
      </div>
    `
  }

  render() {
    const positionStyle = this.position === 'bottom-left' ? 'left: 20px;' : 'right: 20px;'

    return html`
      <style>
        :host {
          position: fixed;
          bottom: 20px;
          ${positionStyle}
          z-index: 999999;
          font-family: var(--rc-font);
        }

        /* Bubble */
        .bubble {
          width: 60px;
          height: 60px;
          border-radius: 50%;
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          box-shadow: var(--rc-shadow);
          transition: transform 0.2s;
          position: relative;
        }

        .bubble:hover {
          transform: scale(1.05);
        }

        .bubble svg {
          width: 28px;
          height: 28px;
        }

        .badge {
          position: absolute;
          top: -4px;
          right: -4px;
          background: #ef4444;
          color: white;
          font-size: 12px;
          font-weight: bold;
          min-width: 20px;
          height: 20px;
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 0 6px;
        }

        /* Window */
        .window {
          position: absolute;
          bottom: 80px;
          ${this.position === 'bottom-left' ? 'left: 0;' : 'right: 0;'}
          width: 380px;
          height: 520px;
          background: var(--rc-bg);
          border-radius: var(--rc-radius);
          box-shadow: var(--rc-shadow);
          display: flex;
          flex-direction: column;
          overflow: hidden;
          opacity: ${this.isOpen ? '1' : '0'};
          transform: ${this.isOpen ? 'translateY(0) scale(1)' : 'translateY(20px) scale(0.95)'};
          pointer-events: ${this.isOpen ? 'auto' : 'none'};
          transition: opacity 0.2s, transform 0.2s;
        }

        @media (max-width: 420px) {
          .window {
            width: calc(100vw - 40px);
            height: calc(100vh - 120px);
            max-height: 600px;
          }
        }

        /* Header */
        .header {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          padding: 16px;
          display: flex;
          align-items: center;
          gap: 12px;
          flex-shrink: 0;
        }

        .header-back {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.1);
          cursor: pointer;
          transition: background 0.15s;
        }

        .header-back:hover {
          background: rgba(255, 255, 255, 0.2);
        }

        .header-back svg {
          width: 20px;
          height: 20px;
        }

        .header-info {
          flex: 1;
          min-width: 0;
        }

        .header-title {
          font-weight: 600;
          font-size: 16px;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .header-subtitle {
          font-size: 12px;
          opacity: 0.8;
          display: flex;
          align-items: center;
          gap: 6px;
        }

        .status-dot {
          width: 8px;
          height: 8px;
          border-radius: 50%;
          background: ${this.wsConnected ? '#34d399' : '#fbbf24'};
        }

        .close-btn {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 32px;
          height: 32px;
          color: white;
          cursor: pointer;
        }

        /* Content */
        .content {
          flex: 1;
          overflow-y: auto;
          padding: 16px;
        }

        .messages-content {
          display: flex;
          flex-direction: column;
          padding: 12px;
        }

        /* Skeleton loading */
        .skeleton-item {
          display: flex;
          gap: 12px;
          padding: 12px;
        }

        .skeleton-avatar {
          width: 44px;
          height: 44px;
          border-radius: 50%;
          background: linear-gradient(90deg, var(--rc-bg-secondary) 25%, var(--rc-border) 50%, var(--rc-bg-secondary) 75%);
          background-size: 200% 100%;
          animation: shimmer 1.5s infinite;
        }

        .skeleton-text {
          flex: 1;
          display: flex;
          flex-direction: column;
          gap: 8px;
          justify-content: center;
        }

        .skeleton-line {
          height: 14px;
          border-radius: 4px;
          background: linear-gradient(90deg, var(--rc-bg-secondary) 25%, var(--rc-border) 50%, var(--rc-bg-secondary) 75%);
          background-size: 200% 100%;
          animation: shimmer 1.5s infinite;
        }

        .skeleton-line.short {
          width: 60%;
        }

        @keyframes shimmer {
          0% { background-position: 200% 0; }
          100% { background-position: -200% 0; }
        }

        /* Empty state */
        .empty-state {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          height: 100%;
          text-align: center;
          padding: 40px 20px;
          color: var(--rc-text-secondary);
        }

        .empty-icon {
          width: 64px;
          height: 64px;
          margin-bottom: 16px;
          opacity: 0.5;
        }

        .empty-title {
          font-weight: 600;
          margin-bottom: 4px;
          color: var(--rc-text);
        }

        .empty-subtitle {
          font-size: 13px;
        }

        /* Conversation list */
        .conversation-item {
          display: flex;
          gap: 12px;
          padding: 12px;
          border-radius: var(--rc-radius-sm);
          cursor: pointer;
          transition: background 0.15s;
        }

        .conversation-item:hover {
          background: var(--rc-bg-secondary);
        }

        .avatar {
          width: 44px;
          height: 44px;
          border-radius: 50%;
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 600;
          font-size: 14px;
          flex-shrink: 0;
          overflow: hidden;
        }

        .avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .conv-info {
          flex: 1;
          min-width: 0;
        }

        .conv-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 8px;
          margin-bottom: 2px;
        }

        .conv-name {
          font-weight: 600;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .conv-time {
          font-size: 11px;
          color: var(--rc-text-secondary);
          flex-shrink: 0;
        }

        .conv-preview-row {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 8px;
        }

        .conv-preview {
          font-size: 13px;
          color: var(--rc-text-secondary);
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .conv-unread {
          background: var(--rc-primary);
          color: white;
          font-size: 11px;
          font-weight: 600;
          min-width: 18px;
          height: 18px;
          border-radius: 9px;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 0 5px;
          flex-shrink: 0;
        }

        /* Messages */
        .loading-messages {
          display: flex;
          align-items: center;
          justify-content: center;
          height: 100%;
        }

        .spinner {
          width: 32px;
          height: 32px;
          border: 3px solid var(--rc-border);
          border-top-color: var(--rc-primary);
          border-radius: 50%;
          animation: spin 0.8s linear infinite;
        }

        .spinner.small {
          width: 16px;
          height: 16px;
          border-width: 2px;
        }

        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        .messages-list {
          display: flex;
          flex-direction: column;
          gap: 8px;
          min-height: 0;
        }

        .message {
          display: flex;
          gap: 8px;
          max-width: 85%;
        }

        .message.own {
          flex-direction: row-reverse;
          align-self: flex-end;
        }

        .message.other {
          align-self: flex-start;
        }

        .message.optimistic {
          opacity: 0.7;
        }

        .message.failed .message-bubble {
          background: #fee2e2 !important;
        }

        .message-avatar {
          width: 28px;
          height: 28px;
          border-radius: 50%;
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 10px;
          font-weight: 600;
          flex-shrink: 0;
          overflow: hidden;
          align-self: flex-end;
        }

        .message-avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .message-content {
          display: flex;
          flex-direction: column;
          gap: 4px;
          min-width: 0;
        }

        .message.own .message-content {
          align-items: flex-end;
        }

        .message-sender {
          font-size: 11px;
          font-weight: 600;
          color: var(--rc-text-secondary);
          padding-left: 8px;
        }

        .message-bubble {
          padding: 10px 14px;
          border-radius: 18px;
          word-wrap: break-word;
          overflow-wrap: break-word;
        }

        .message.own .message-bubble {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          border-bottom-right-radius: 4px;
        }

        .message.other .message-bubble {
          background: var(--rc-bg-secondary);
          color: var(--rc-text);
          border-bottom-left-radius: 4px;
        }

        .message-text {
          white-space: pre-wrap;
        }

        .message-meta {
          display: flex;
          gap: 6px;
          font-size: 10px;
          color: var(--rc-text-secondary);
          padding: 0 8px;
        }

        .message-status.failed {
          color: #ef4444;
        }

        .message-attachments {
          display: flex;
          flex-direction: column;
          gap: 4px;
        }

        .attachment-image {
          border-radius: 12px;
          overflow: hidden;
          cursor: pointer;
          max-width: 200px;
        }

        .attachment-image img {
          display: block;
          width: 100%;
          height: auto;
        }

        .attachment-file {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 10px 12px;
          background: var(--rc-bg-secondary);
          border-radius: 12px;
          text-decoration: none;
          color: var(--rc-text);
          transition: background 0.15s;
        }

        .attachment-file:hover {
          background: var(--rc-border);
        }

        .file-icon {
          width: 24px;
          height: 24px;
          flex-shrink: 0;
        }

        .file-info {
          flex: 1;
          min-width: 0;
          display: flex;
          flex-direction: column;
        }

        .file-name {
          font-size: 13px;
          font-weight: 500;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .file-size {
          font-size: 11px;
          color: var(--rc-text-secondary);
        }

        .download-icon {
          width: 20px;
          height: 20px;
          flex-shrink: 0;
          opacity: 0.5;
        }

        /* Typing indicator */
        .typing-indicator {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 8px 12px;
          color: var(--rc-text-secondary);
          font-size: 13px;
        }

        .typing-dots {
          display: flex;
          gap: 3px;
        }

        .typing-dots span {
          width: 6px;
          height: 6px;
          background: var(--rc-text-secondary);
          border-radius: 50%;
          animation: typing 1.4s infinite;
        }

        .typing-dots span:nth-child(2) {
          animation-delay: 0.2s;
        }

        .typing-dots span:nth-child(3) {
          animation-delay: 0.4s;
        }

        @keyframes typing {
          0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
          30% { transform: translateY(-4px); opacity: 1; }
        }

        /* Input area */
        .input-area {
          padding: 12px;
          border-top: 1px solid var(--rc-border);
          flex-shrink: 0;
        }

        .pending-attachments {
          display: flex;
          gap: 8px;
          margin-bottom: 8px;
          flex-wrap: wrap;
        }

        .pending-attachment {
          position: relative;
          width: 60px;
          height: 60px;
          border-radius: 8px;
          overflow: hidden;
          background: var(--rc-bg-secondary);
        }

        .pending-attachment.error {
          border: 2px solid #ef4444;
        }

        .pending-attachment img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .pending-file {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .pending-file svg {
          width: 24px;
          height: 24px;
          color: var(--rc-text-secondary);
        }

        .upload-overlay {
          position: absolute;
          inset: 0;
          background: rgba(0, 0, 0, 0.5);
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .upload-overlay .spinner {
          border-color: rgba(255, 255, 255, 0.3);
          border-top-color: white;
        }

        .remove-attachment {
          position: absolute;
          top: 2px;
          right: 2px;
          width: 20px;
          height: 20px;
          background: rgba(0, 0, 0, 0.6);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
        }

        .remove-attachment svg {
          width: 12px;
          height: 12px;
          color: white;
        }

        .input-row {
          display: flex;
          align-items: flex-end;
          gap: 8px;
        }

        .input-btn {
          width: 36px;
          height: 36px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 50%;
          color: var(--rc-text-secondary);
          transition: background 0.15s, color 0.15s;
          flex-shrink: 0;
        }

        .input-btn:hover {
          background: var(--rc-bg-secondary);
        }

        .input-btn svg {
          width: 20px;
          height: 20px;
        }

        .send-btn.active {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
        }

        .send-btn:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }

        .message-input {
          flex: 1;
          border: 1px solid var(--rc-border);
          border-radius: 18px;
          padding: 8px 14px;
          resize: none;
          outline: none;
          max-height: 100px;
          min-height: 36px;
          background: var(--rc-bg);
          color: var(--rc-text);
          line-height: 1.4;
        }

        .message-input:focus {
          border-color: var(--rc-primary);
        }

        .message-input::placeholder {
          color: var(--rc-text-secondary);
        }

        /* Branding */
        .branding {
          text-align: center;
          padding: 8px;
          font-size: 11px;
          color: var(--rc-text-secondary);
          border-top: 1px solid var(--rc-border);
          flex-shrink: 0;
        }

        .branding a {
          color: inherit;
          text-decoration: none;
        }

        .branding a:hover {
          color: var(--rc-primary);
        }

        /* Lightbox */
        .lightbox {
          position: fixed;
          inset: 0;
          background: rgba(0, 0, 0, 0.9);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 1000000;
          animation: fadeIn 0.2s;
        }

        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        .lightbox img {
          max-width: 90%;
          max-height: 90%;
          object-fit: contain;
          border-radius: 8px;
        }

        .lightbox-close {
          position: absolute;
          top: 20px;
          right: 20px;
          width: 40px;
          height: 40px;
          background: rgba(255, 255, 255, 0.1);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: background 0.15s;
        }

        .lightbox-close:hover {
          background: rgba(255, 255, 255, 0.2);
        }

        .lightbox-close svg {
          width: 24px;
          height: 24px;
          color: white;
        }
      </style>

      <div class="bubble" @click=${this.toggleOpen}>
        ${this.isOpen ? html`
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        ` : html`
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
        `}
        ${this.unreadCount > 0 ? html`<div class="badge">${this.unreadCount}</div>` : nothing}
      </div>

      <div class="window">
        <div class="header">
          ${this.selectedConversation ? html`
            <button class="header-back" @click=${this.goBack}>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </button>
            <div class="header-info">
              <div class="header-title">${this.getParticipantNames()}</div>
              <div class="header-subtitle">
                <span class="status-dot"></span>
                ${this.selectedConversation.participants.length} participant${this.selectedConversation.participants.length !== 1 ? 's' : ''}
              </div>
            </div>
          ` : html`
            <div class="header-info">
              <div class="header-title">Messages</div>
              <div class="header-subtitle">
                <span class="status-dot"></span>
                ${this.wsConnected ? 'Connected' : 'Connecting...'}
              </div>
            </div>
          `}
          <button class="close-btn" @click=${this.toggleOpen}>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        ${this.selectedConversation
          ? this.renderMessageThread()
          : this.renderConversationList()
        }

        ${!this.selectedConversation && this.showBranding ? html`
          <div class="branding">
            <a href="https://github.com/TNortnern/reusable-chat" target="_blank">
              Powered by Reusable Chat
            </a>
          </div>
        ` : nothing}
      </div>

      ${this.renderLightbox()}
    `
  }
}

export default ReusableChat
