export interface WidgetConfig {
  apiKey: string
  userId?: string
  userName?: string
  userEmail?: string
  userAvatar?: string
  position?: 'bottom-right' | 'bottom-left'
  theme?: 'light' | 'dark'
  accentColor?: string
  showBranding?: boolean
  apiUrl?: string
}

export interface User {
  id: string
  name: string
  email?: string
  avatar_url?: string
}

export interface Message {
  id: string
  content: string
  sender_id: string
  sender?: User
  attachments?: Attachment[]
  created_at: string
  isOptimistic?: boolean
}

export interface Attachment {
  id: string
  name: string
  type: string
  url: string
  size: number
}

export interface Conversation {
  id: string
  participants: User[]
  last_message?: Message
  unread_count: number
  metadata?: Record<string, any>
  updated_at: string
}

export interface Session {
  token: string
  user: User
  expires_at: string
}

export interface InitResponse {
  workspace_id: string
  workspace_name: string
  settings: {
    position: string
    theme: string
    show_branding: boolean
  }
  theme: {
    primary_color: string
    secondary_color: string
  }
}
