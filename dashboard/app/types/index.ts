export interface Admin {
  id: string
  email: string
  name: string
  is_super_admin: boolean
  created_at: string
}

export interface Workspace {
  id: string
  name: string
  slug: string
  plan: string
  settings?: WorkspaceSettings
  theme?: WorkspaceTheme
}

export interface WorkspaceSettings {
  read_receipts_enabled: boolean
  online_status_enabled: boolean
  typing_indicators_enabled: boolean
  file_size_limit_mb: number
  rate_limit_per_minute: number
  webhook_url?: string
}

export interface WorkspaceTheme {
  preset: 'minimal' | 'playful' | 'professional' | 'custom'
  primary_color?: string
  background_color?: string
  font_family?: string
  logo_url?: string
  position: 'bottom-right' | 'bottom-left'
  dark_mode_enabled: boolean
}

export interface ChatUser {
  id: string
  name: string
  email?: string
  avatar_url?: string
  is_anonymous: boolean
  last_seen_at?: string
}

export interface Conversation {
  id: string
  type: 'direct' | 'group'
  name?: string
  participants: Participant[]
  last_message?: Message
  lastMessage?: Message // API may return camelCase
  last_message_at?: string
  unread_count?: number
  messages_count?: number
}

export interface Participant {
  id: string
  chat_user_id: string
  chatUser?: ChatUser
  chat_user?: ChatUser // API may return snake_case
  last_read_at?: string
}

export interface Message {
  id: string
  content: string
  sender: ChatUser
  attachments: Attachment[]
  reactions: Reaction[]
  created_at: string
  deleted_at?: string
}

export interface Attachment {
  id: string
  filename: string
  mime_type: string
  size_bytes: number
  url: string
}

export interface Reaction {
  id: string
  emoji: string
  chat_user_id: string
}
