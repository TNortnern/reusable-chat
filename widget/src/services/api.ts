import type { InitResponse, Session, Conversation, Message } from '../types'

export class ApiService {
  private baseUrl: string
  private apiKey: string
  private sessionToken: string | null = null

  constructor(apiUrl: string, apiKey: string) {
    this.baseUrl = apiUrl
    this.apiKey = apiKey
  }

  setSessionToken(token: string) {
    this.sessionToken = token
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
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
      const error = await response.json().catch(() => ({}))
      throw new Error(error.message || error.error || 'Request failed')
    }

    return response.json()
  }

  // Embed endpoints
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
  async getConversations(): Promise<{ data: Conversation[] }> {
    return this.request('/api/widget/conversations')
  }

  async getConversation(id: string): Promise<{
    conversation: Conversation
    messages: { data: Message[] }
  }> {
    return this.request(`/api/widget/conversations/${id}`)
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

  async uploadAttachment(
    conversationId: string,
    file: File
  ): Promise<{ id: string; url: string; name: string; type: string }> {
    const formData = new FormData()
    formData.append('file', file)

    const response = await fetch(
      `${this.baseUrl}/api/widget/conversations/${conversationId}/attachments`,
      {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${this.sessionToken}`,
        },
        body: formData,
      }
    )

    if (!response.ok) {
      throw new Error('Upload failed')
    }

    return response.json()
  }
}
