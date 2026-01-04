import type { InitResponse, Session, Conversation, Message, Attachment } from '../types'

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  next_page_url: string | null
  prev_page_url: string | null
}

export interface ConversationWithMessages {
  conversation: Conversation
  messages: PaginatedResponse<Message>
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
  status: number
}

export class ApiService {
  private baseUrl: string
  private apiKey: string
  private sessionToken: string | null = null

  constructor(apiUrl: string, apiKey: string) {
    this.baseUrl = apiUrl.replace(/\/$/, '') // Remove trailing slash
    this.apiKey = apiKey
  }

  setSessionToken(token: string) {
    this.sessionToken = token
  }

  clearSessionToken() {
    this.sessionToken = null
  }

  getSessionToken(): string | null {
    return this.sessionToken
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...((options.headers as Record<string, string>) || {}),
    }

    if (this.sessionToken) {
      headers['Authorization'] = `Bearer ${this.sessionToken}`
    }

    const response = await fetch(`${this.baseUrl}${endpoint}`, {
      ...options,
      headers,
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Request failed' }))
      const apiError: ApiError = {
        message: error.message || error.error || 'Request failed',
        errors: error.errors,
        status: response.status
      }
      throw apiError
    }

    // Handle empty responses
    const text = await response.text()
    if (!text) {
      return {} as T
    }

    return JSON.parse(text)
  }

  // Embed endpoints (public key auth)
  async init(): Promise<InitResponse> {
    return this.request('/api/embed/init', {
      method: 'POST',
      body: JSON.stringify({ key: this.apiKey }),
    })
  }

  async createSession(
    userId: string,
    userName: string,
    userEmail?: string,
    userAvatar?: string
  ): Promise<Session> {
    return this.request('/api/embed/session', {
      method: 'POST',
      body: JSON.stringify({
        key: this.apiKey,
        user_id: userId,
        user_name: userName,
        user_email: userEmail,
        user_avatar: userAvatar,
      }),
    })
  }

  // Widget endpoints (require session token)
  async getConversations(page = 1, type?: string): Promise<PaginatedResponse<Conversation>> {
    const params = new URLSearchParams({ page: String(page) })
    if (type) params.set('type', type)
    return this.request(`/api/widget/conversations?${params}`)
  }

  async getConversation(id: string): Promise<ConversationWithMessages> {
    return this.request(`/api/widget/conversations/${id}`)
  }

  async getMessages(conversationId: string, page = 1): Promise<PaginatedResponse<Message>> {
    const result = await this.request<ConversationWithMessages>(
      `/api/widget/conversations/${conversationId}?page=${page}`
    )
    return result.messages
  }

  async createConversation(
    type: 'direct' | 'group',
    participantIds: string[],
    name?: string
  ): Promise<Conversation> {
    return this.request('/api/widget/conversations', {
      method: 'POST',
      body: JSON.stringify({
        type,
        participant_ids: participantIds,
        name
      }),
    })
  }

  async leaveConversation(conversationId: string): Promise<void> {
    await this.request(`/api/widget/conversations/${conversationId}`, {
      method: 'DELETE',
    })
  }

  async sendMessage(
    conversationId: string,
    content: string,
    attachmentIds: string[] = []
  ): Promise<Message> {
    return this.request(`/api/widget/conversations/${conversationId}/messages`, {
      method: 'POST',
      body: JSON.stringify({ content, attachment_ids: attachmentIds }),
    })
  }

  async sendTyping(conversationId: string, isTyping: boolean): Promise<void> {
    await this.request(`/api/widget/conversations/${conversationId}/typing`, {
      method: 'POST',
      body: JSON.stringify({ is_typing: isTyping }),
    })
  }

  async markAsRead(conversationId: string): Promise<void> {
    await this.request(`/api/widget/conversations/${conversationId}/read`, {
      method: 'POST',
    })
  }

  async addReaction(conversationId: string, messageId: string, emoji: string): Promise<void> {
    await this.request(`/api/widget/conversations/${conversationId}/messages/${messageId}/reactions`, {
      method: 'POST',
      body: JSON.stringify({ emoji }),
    })
  }

  async removeReaction(conversationId: string, messageId: string, emoji: string): Promise<void> {
    await this.request(`/api/widget/conversations/${conversationId}/messages/${messageId}/reactions/${encodeURIComponent(emoji)}`, {
      method: 'DELETE',
    })
  }

  async reportMessage(conversationId: string, messageId: string, reason: string): Promise<void> {
    await this.request(`/api/widget/conversations/${conversationId}/messages/${messageId}/report`, {
      method: 'POST',
      body: JSON.stringify({ reason }),
    })
  }

  async blockUser(userId: string): Promise<void> {
    await this.request(`/api/widget/users/${userId}/block`, {
      method: 'POST',
    })
  }

  async unblockUser(userId: string): Promise<void> {
    await this.request(`/api/widget/users/${userId}/block`, {
      method: 'DELETE',
    })
  }

  async uploadAttachment(
    conversationId: string,
    file: File,
    onProgress?: (progress: number) => void
  ): Promise<Attachment> {
    return new Promise((resolve, reject) => {
      const formData = new FormData()
      formData.append('file', file)

      const xhr = new XMLHttpRequest()

      xhr.upload.addEventListener('progress', (event) => {
        if (event.lengthComputable && onProgress) {
          const progress = Math.round((event.loaded / event.total) * 100)
          onProgress(progress)
        }
      })

      xhr.addEventListener('load', () => {
        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            const response = JSON.parse(xhr.responseText)
            resolve(response)
          } catch (e) {
            reject(new Error('Invalid response'))
          }
        } else {
          try {
            const error = JSON.parse(xhr.responseText)
            reject({ message: error.message || 'Upload failed', status: xhr.status })
          } catch {
            reject({ message: 'Upload failed', status: xhr.status })
          }
        }
      })

      xhr.addEventListener('error', () => {
        reject({ message: 'Network error', status: 0 })
      })

      xhr.addEventListener('abort', () => {
        reject({ message: 'Upload cancelled', status: 0 })
      })

      xhr.open('POST', `${this.baseUrl}/api/widget/conversations/${conversationId}/attachments`)
      xhr.setRequestHeader('Authorization', `Bearer ${this.sessionToken}`)
      xhr.setRequestHeader('Accept', 'application/json')
      xhr.send(formData)
    })
  }

  async getMe(): Promise<{ id: string; name: string; email?: string; avatar_url?: string }> {
    return this.request('/api/widget/me')
  }

  // Verify session is still valid
  async verifySession(): Promise<boolean> {
    try {
      await this.getMe()
      return true
    } catch (error) {
      const apiError = error as ApiError
      if (apiError.status === 401) {
        return false
      }
      throw error
    }
  }
}
