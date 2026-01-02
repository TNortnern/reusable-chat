import type { Message, Attachment, Conversation, Session } from './types'

export class ChatAPI {
  private apiUrl: string
  private token: string = ''

  constructor(apiUrl: string) {
    this.apiUrl = apiUrl
  }

  setToken(token: string) {
    this.token = token
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const url = `${this.apiUrl}${endpoint}`
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      ...(options.headers as Record<string, string> || {}),
    }

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`
    }

    if (!(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json'
    }

    const response = await fetch(url, {
      ...options,
      headers,
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({}))
      throw new Error(error.message || `Request failed: ${response.status}`)
    }

    return response.json()
  }

  async initSession(workspaceId: string, userId?: string, userName?: string): Promise<Session> {
    // This would typically be handled by your backend
    // For now, we'll create a simple session structure
    const externalId = userId || `widget-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`

    // Create user via API (requires API key - typically done server-side)
    // For widget, we expect userToken to be pre-generated
    return {
      token: '',
      userId: externalId,
      userName: userName || 'Guest',
    }
  }

  async getConversation(conversationId: string): Promise<{ conversation: Conversation; messages: Message[] }> {
    const data = await this.request<any>(`/api/widget/conversations/${conversationId}`)

    const messages = (data.messages?.data || data.messages || []).map((m: any) => ({
      id: m.id,
      content: m.content,
      sender_id: m.sender?.id || m.sender_id,
      sender_name: m.sender?.name || 'Unknown',
      created_at: m.created_at,
      attachments: m.attachments || [],
    }))

    return {
      conversation: {
        id: data.id,
        name: data.name,
        participants: data.participants || [],
      },
      messages: messages.reverse(),
    }
  }

  async getConversations(): Promise<Conversation[]> {
    const data = await this.request<any>('/api/widget/conversations')
    return data.data || data || []
  }

  async createConversation(participantIds: string[]): Promise<Conversation> {
    return this.request<Conversation>('/api/widget/conversations', {
      method: 'POST',
      body: JSON.stringify({ participant_ids: participantIds }),
    })
  }

  async sendMessage(
    conversationId: string,
    content: string,
    attachmentIds: string[] = []
  ): Promise<Message> {
    const data = await this.request<any>(`/api/widget/conversations/${conversationId}/messages`, {
      method: 'POST',
      body: JSON.stringify({
        content,
        attachment_ids: attachmentIds,
      }),
    })

    return {
      id: data.id,
      content: data.content,
      sender_id: data.sender?.id || data.sender_id,
      sender_name: data.sender?.name || 'You',
      created_at: data.created_at,
      attachments: data.attachments || [],
    }
  }

  async uploadAttachment(conversationId: string, file: File): Promise<Attachment> {
    const formData = new FormData()
    formData.append('file', file)

    return this.request<Attachment>(
      `/api/widget/conversations/${conversationId}/attachments`,
      {
        method: 'POST',
        body: formData,
      }
    )
  }

  async sendTypingIndicator(conversationId: string, isTyping: boolean): Promise<void> {
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
}
