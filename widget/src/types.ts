export interface WidgetConfig {
  workspaceId: string
  userId?: string
  userToken?: string
  apiUrl?: string
  wsHost?: string
  wsPort?: number
  wsKey?: string
  position?: 'bottom-right' | 'bottom-left'
  theme?: WidgetTheme
  title?: string
  subtitle?: string
  placeholder?: string
  welcomeMessage?: string
}

export interface WidgetTheme {
  primaryColor?: string
  textColor?: string
  backgroundColor?: string
  headerColor?: string
  headerTextColor?: string
  buttonColor?: string
  buttonTextColor?: string
  fontFamily?: string
  borderRadius?: string
}

export interface Message {
  id: string
  content: string
  sender_id: string
  sender_name: string
  created_at: string
  attachments?: Attachment[]
  is_own?: boolean
}

export interface Attachment {
  id: string
  name: string
  type: string
  url: string
  size: number
}

export interface PendingFile {
  file: File
  name: string
  type: string
  preview: string
}

export interface Participant {
  id: string
  name: string
}

export interface Session {
  token: string
  userId: string
  userName: string
}

export interface Conversation {
  id: string
  name?: string
  participants: Participant[]
}
