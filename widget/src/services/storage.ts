const STORAGE_PREFIX = 'rc_'

export interface StoredSession {
  token: string
  userId: string
  userName: string
  userEmail?: string
  expiresAt?: string
}

export interface StoredUser {
  id: string
  name: string
  email?: string
  avatarUrl?: string
}

export const storage = {
  get<T>(key: string): T | null {
    try {
      const item = localStorage.getItem(STORAGE_PREFIX + key)
      return item ? JSON.parse(item) : null
    } catch {
      return null
    }
  },

  set<T>(key: string, value: T): void {
    try {
      localStorage.setItem(STORAGE_PREFIX + key, JSON.stringify(value))
    } catch {
      // localStorage might be full or disabled
    }
  },

  remove(key: string): void {
    try {
      localStorage.removeItem(STORAGE_PREFIX + key)
    } catch {
      // Ignore errors
    }
  },

  // Session management
  getSessionToken(): string | null {
    const session = this.get<StoredSession>('session')
    if (!session) return null

    // Check if session is expired
    if (session.expiresAt) {
      const expiresAt = new Date(session.expiresAt)
      if (expiresAt <= new Date()) {
        this.clearSession()
        return null
      }
    }

    return session.token
  },

  setSession(session: StoredSession): void {
    this.set('session', session)
  },

  getSession(): StoredSession | null {
    const session = this.get<StoredSession>('session')
    if (!session) return null

    // Check if session is expired
    if (session.expiresAt) {
      const expiresAt = new Date(session.expiresAt)
      if (expiresAt <= new Date()) {
        this.clearSession()
        return null
      }
    }

    return session
  },

  // Legacy support
  setSessionToken(token: string): void {
    const existing = this.getSession()
    if (existing) {
      existing.token = token
      this.setSession(existing)
    } else {
      this.setSession({ token, userId: '', userName: '' })
    }
  },

  clearSession(): void {
    this.remove('session')
    this.remove('user')
  },

  // User data
  getUser(): StoredUser | null {
    return this.get<StoredUser>('user')
  },

  setUser(user: StoredUser): void {
    this.set('user', user)
  },

  // Draft messages (per conversation)
  getDraft(conversationId: string): string | null {
    const drafts = this.get<Record<string, string>>('drafts') || {}
    return drafts[conversationId] || null
  },

  setDraft(conversationId: string, content: string): void {
    const drafts = this.get<Record<string, string>>('drafts') || {}
    if (content.trim()) {
      drafts[conversationId] = content
    } else {
      delete drafts[conversationId]
    }
    this.set('drafts', drafts)
  },

  clearDraft(conversationId: string): void {
    const drafts = this.get<Record<string, string>>('drafts') || {}
    delete drafts[conversationId]
    this.set('drafts', drafts)
  },

  // Widget state
  getWidgetState(): { isOpen: boolean; lastConversationId?: string } | null {
    return this.get('widget_state')
  },

  setWidgetState(state: { isOpen: boolean; lastConversationId?: string }): void {
    this.set('widget_state', state)
  },

  // Clear all widget data
  clearAll(): void {
    const keys = Object.keys(localStorage).filter(key => key.startsWith(STORAGE_PREFIX))
    keys.forEach(key => {
      try {
        localStorage.removeItem(key)
      } catch {
        // Ignore errors
      }
    })
  },
}
