import { LitElement, html, css } from 'lit'
import { customElement, property, state } from 'lit/decorators.js'
import type { WidgetConfig, Session, Conversation, Message } from './types'
import { ApiService } from './services/api'
import { storage } from './services/storage'
import baseStyles from './styles/base.css?inline'

@customElement('reusable-chat')
export class ReusableChat extends LitElement {
  static styles = css`${baseStyles}`

  @property({ attribute: 'api-key' }) apiKey = ''
  @property({ attribute: 'user-id' }) userId = ''
  @property({ attribute: 'user-name' }) userName = ''
  @property({ attribute: 'user-email' }) userEmail = ''
  @property({ attribute: 'position' }) position: 'bottom-right' | 'bottom-left' = 'bottom-right'
  @property({ attribute: 'theme' }) theme: 'light' | 'dark' = 'light'
  @property({ attribute: 'accent-color' }) accentColor = ''
  @property({ type: Boolean, attribute: 'show-branding' }) showBranding = true
  @property({ attribute: 'api-url' }) apiUrl = 'https://api-production-de24c.up.railway.app'

  @state() private isOpen = false
  @state() private isConnected = false
  @state() private isLoading = true
  @state() private session: Session | null = null
  @state() private conversations: Conversation[] = []
  @state() private selectedConversation: Conversation | null = null
  @state() private messages: Message[] = []
  @state() private unreadCount = 0

  private api: ApiService | null = null

  async connectedCallback() {
    super.connectedCallback()
    await this.initialize()
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
        // Verify session is still valid by fetching conversations
        try {
          const { data } = await this.api.getConversations()
          this.conversations = data
          this.session = { token: savedToken, user: { id: this.userId, name: this.userName }, expires_at: '' }
        } catch {
          // Session invalid, create new one
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

      // Load conversations
      const { data } = await this.api.getConversations()
      this.conversations = data
      this.unreadCount = data.reduce((sum, c) => sum + (c.unread_count || 0), 0)
    } catch (error) {
      console.error('[Reusable Chat] Session creation error:', error)
    }
  }

  private toggleOpen() {
    this.isOpen = !this.isOpen
  }

  render() {
    const positionStyles = this.position === 'bottom-left'
      ? 'left: 20px;'
      : 'right: 20px;'

    return html`
      <style>
        :host {
          position: fixed;
          bottom: 20px;
          ${positionStyles}
          z-index: 999999;
          font-family: var(--rc-font);
        }

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

        .header {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          padding: 16px;
          display: flex;
          align-items: center;
          justify-content: space-between;
        }

        .header-title {
          font-weight: 600;
          font-size: 16px;
        }

        .header-status {
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
          background: ${this.isConnected ? '#34d399' : '#fbbf24'};
        }

        .close-btn {
          color: white;
          padding: 4px;
        }

        .content {
          flex: 1;
          overflow-y: auto;
          padding: 16px;
        }

        .loading {
          display: flex;
          align-items: center;
          justify-content: center;
          height: 100%;
          color: var(--rc-text-secondary);
        }

        .empty {
          text-align: center;
          padding: 40px 20px;
          color: var(--rc-text-secondary);
        }

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
          flex-shrink: 0;
        }

        .conv-info {
          flex: 1;
          min-width: 0;
        }

        .conv-name {
          font-weight: 600;
          margin-bottom: 2px;
        }

        .conv-preview {
          font-size: 13px;
          color: var(--rc-text-secondary);
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .branding {
          text-align: center;
          padding: 8px;
          font-size: 11px;
          color: var(--rc-text-secondary);
          border-top: 1px solid var(--rc-border);
        }

        .branding a {
          color: inherit;
          text-decoration: none;
        }

        .branding a:hover {
          color: var(--rc-primary);
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
        ${this.unreadCount > 0 ? html`<div class="badge">${this.unreadCount}</div>` : ''}
      </div>

      <div class="window">
        <div class="header">
          <div>
            <div class="header-title">Messages</div>
            <div class="header-status">
              <span class="status-dot"></span>
              ${this.isConnected ? 'Connected' : 'Connecting...'}
            </div>
          </div>
          <button class="close-btn" @click=${this.toggleOpen}>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <div class="content">
          ${this.isLoading ? html`
            <div class="loading">Loading...</div>
          ` : this.conversations.length === 0 ? html`
            <div class="empty">
              <p>No conversations yet</p>
              <p style="font-size: 13px; margin-top: 8px;">Messages will appear here</p>
            </div>
          ` : this.conversations.map(conv => html`
            <div class="conversation-item" @click=${() => this.selectConversation(conv)}>
              <div class="avatar">${this.getInitials(conv.participants[0]?.name || 'U')}</div>
              <div class="conv-info">
                <div class="conv-name">${conv.participants[0]?.name || 'Unknown'}</div>
                <div class="conv-preview">${conv.last_message?.content || 'No messages'}</div>
              </div>
            </div>
          `)}
        </div>

        ${this.showBranding ? html`
          <div class="branding">
            <a href="https://github.com/TNortnern/reusable-chat" target="_blank">
              Powered by Reusable Chat
            </a>
          </div>
        ` : ''}
      </div>
    `
  }

  private getInitials(name: string): string {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
  }

  private selectConversation(conv: Conversation) {
    this.selectedConversation = conv
    // TODO: Load messages
  }
}

export default ReusableChat
