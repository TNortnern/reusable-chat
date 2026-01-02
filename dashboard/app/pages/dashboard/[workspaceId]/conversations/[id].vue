<template>
  <div class="h-[calc(100vh-4rem)] flex">
    <!-- Loading State -->
    <div v-if="loading" class="flex-1 flex items-center justify-center bg-[var(--chat-bg-primary)]">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-[var(--chat-text-secondary)]" />
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex-1 flex flex-col items-center justify-center bg-[var(--chat-bg-primary)]">
      <UIcon name="i-heroicons-exclamation-triangle" class="w-12 h-12 text-red-500 mb-4" />
      <p class="text-[var(--chat-text-secondary)] mb-4">{{ error }}</p>
      <UButton @click="fetchConversation">Try Again</UButton>
    </div>

    <!-- Main Content -->
    <template v-else>
      <!-- Main Chat Area -->
      <div class="flex-1 flex flex-col bg-[var(--chat-bg-primary)]">
        <!-- Header -->
        <div class="p-4 border-b border-[var(--chat-border)]">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <UButton
                icon="i-heroicons-arrow-left"
                variant="ghost"
                @click="navigateTo(`/dashboard/${workspaceId}/conversations`)"
              />
              <div>
                <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">
                  {{ getConversationName(conversation) }}
                </h2>
                <div class="text-sm text-[var(--chat-text-secondary)]">
                  {{ conversation?.participants?.length || 0 }} participant{{ conversation?.participants?.length !== 1 ? 's' : '' }}
                  - {{ messages.length }} messages
                </div>
              </div>
            </div>
            <UBadge :color="conversation?.type === 'direct' ? 'blue' : 'purple'" variant="soft">
              {{ conversation?.type }}
            </UBadge>
          </div>
        </div>

        <!-- Messages Area -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
          <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full">
            <UIcon name="i-heroicons-chat-bubble-left-right" class="w-12 h-12 text-[var(--chat-text-secondary)] mb-4" />
            <p class="text-[var(--chat-text-secondary)]">No messages in this conversation</p>
          </div>

          <div
            v-for="message in messages"
            :key="message.id"
            class="group"
          >
            <!-- Deleted Message -->
            <div v-if="message.deleted_at" class="flex items-center gap-3 p-3 rounded-lg bg-[var(--chat-bg-tertiary)] opacity-60">
              <UIcon name="i-heroicons-trash" class="w-5 h-5 text-[var(--chat-text-secondary)]" />
              <span class="text-sm text-[var(--chat-text-secondary)] italic">This message was deleted</span>
              <span class="text-xs text-[var(--chat-text-secondary)] ml-auto">
                {{ formatDate(message.deleted_at) }}
              </span>
            </div>

            <!-- Normal Message -->
            <div v-else class="flex gap-3">
              <UAvatar
                :alt="message.sender?.name || 'Unknown'"
                size="sm"
                class="flex-shrink-0"
              />
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-medium text-[var(--chat-text-primary)]">
                    {{ message.sender?.name || 'Unknown User' }}
                  </span>
                  <span class="text-xs text-[var(--chat-text-secondary)]">
                    {{ formatFullDate(message.created_at) }}
                  </span>
                </div>

                <div class="relative">
                  <div class="p-3 rounded-lg bg-[var(--chat-bg-secondary)] text-[var(--chat-text-primary)]">
                    {{ message.content }}
                  </div>

                  <!-- Attachments -->
                  <div v-if="message.attachments && message.attachments.length > 0" class="mt-2 space-y-2">
                    <div
                      v-for="attachment in message.attachments"
                      :key="attachment.id"
                      class="flex items-center gap-2 p-2 rounded-lg bg-[var(--chat-bg-tertiary)]"
                    >
                      <UIcon :name="getAttachmentIcon(attachment.mime_type)" class="w-5 h-5 text-[var(--chat-text-secondary)]" />
                      <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-[var(--chat-text-primary)] truncate">
                          {{ attachment.filename }}
                        </div>
                        <div class="text-xs text-[var(--chat-text-secondary)]">
                          {{ formatFileSize(attachment.size_bytes) }}
                        </div>
                      </div>
                      <UButton
                        icon="i-heroicons-arrow-down-tray"
                        size="xs"
                        variant="ghost"
                        :href="attachment.url"
                        target="_blank"
                      />
                    </div>
                  </div>

                  <!-- Reactions -->
                  <div v-if="message.reactions && message.reactions.length > 0" class="flex flex-wrap gap-1 mt-2">
                    <UBadge
                      v-for="(reaction, index) in groupedReactions(message.reactions)"
                      :key="index"
                      variant="soft"
                      color="gray"
                      size="xs"
                    >
                      {{ reaction.emoji }} {{ reaction.count }}
                    </UBadge>
                  </div>

                  <!-- Moderation Actions -->
                  <div class="absolute right-0 top-0 -mt-2 -mr-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <UButton
                      icon="i-heroicons-trash"
                      size="xs"
                      color="red"
                      variant="soft"
                      @click="confirmDeleteMessage(message)"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Load More Messages -->
          <div v-if="hasMoreMessages" class="flex justify-center">
            <UButton variant="ghost" :loading="loadingMore" @click="loadMoreMessages">
              Load More Messages
            </UButton>
          </div>
        </div>

        <!-- Admin Notice -->
        <div class="p-4 border-t border-[var(--chat-border)] bg-[var(--chat-bg-secondary)]">
          <div class="flex items-center gap-2 text-sm text-[var(--chat-text-secondary)]">
            <UIcon name="i-heroicons-eye" class="w-4 h-4" />
            <span>You are viewing this conversation in read-only moderation mode</span>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="w-80 bg-[var(--chat-bg-secondary)] border-l border-[var(--chat-border)] overflow-y-auto">
        <div class="p-4 space-y-6">
          <!-- Conversation Details -->
          <div>
            <h3 class="font-semibold text-[var(--chat-text-primary)] mb-3">Details</h3>
            <UCard>
              <div class="space-y-3 text-sm">
                <div>
                  <div class="text-[var(--chat-text-secondary)]">Conversation ID</div>
                  <div class="text-[var(--chat-text-primary)] font-mono text-xs truncate">
                    {{ conversation?.id }}
                  </div>
                </div>
                <div>
                  <div class="text-[var(--chat-text-secondary)]">Type</div>
                  <div class="text-[var(--chat-text-primary)] font-medium capitalize">
                    {{ conversation?.type }}
                  </div>
                </div>
                <div>
                  <div class="text-[var(--chat-text-secondary)]">Last Activity</div>
                  <div class="text-[var(--chat-text-primary)] font-medium">
                    {{ formatDate(conversation?.last_message_at) }}
                  </div>
                </div>
                <div>
                  <div class="text-[var(--chat-text-secondary)]">Total Messages</div>
                  <div class="text-[var(--chat-text-primary)] font-medium">
                    {{ totalMessages }}
                  </div>
                </div>
              </div>
            </UCard>
          </div>

          <!-- Participants -->
          <div>
            <h3 class="font-semibold text-[var(--chat-text-primary)] mb-3">Participants</h3>
            <UCard>
              <div class="space-y-3">
                <div
                  v-for="participant in conversation?.participants"
                  :key="participant.id"
                  class="flex items-center gap-3"
                >
                  <div class="relative">
                    <UAvatar
                      :alt="getParticipantName(participant)"
                      size="sm"
                    />
                    <div
                      v-if="isOnline(getParticipantLastSeen(participant))"
                      class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-white"
                    />
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-[var(--chat-text-primary)] truncate">
                      {{ getParticipantName(participant) }}
                    </div>
                    <div class="text-xs text-[var(--chat-text-secondary)] truncate">
                      {{ getParticipantEmail(participant) || 'No email' }}
                    </div>
                  </div>
                  <UBadge
                    v-if="isOnline(getParticipantLastSeen(participant))"
                    color="green"
                    variant="soft"
                    size="xs"
                  >
                    Online
                  </UBadge>
                </div>
              </div>
            </UCard>
          </div>

          <!-- Admin Actions -->
          <div>
            <h3 class="font-semibold text-[var(--chat-text-primary)] mb-3">Admin Actions</h3>
            <UCard>
              <div class="space-y-2">
                <UButton
                  block
                  variant="soft"
                  color="blue"
                  class="justify-start"
                  @click="exportConversation"
                >
                  <UIcon name="i-heroicons-arrow-down-tray" />
                  Export Conversation
                </UButton>

                <UButton
                  block
                  variant="soft"
                  color="orange"
                  class="justify-start"
                  @click="archiveConversation"
                >
                  <UIcon name="i-heroicons-archive-box" />
                  Archive Conversation
                </UButton>

                <UButton
                  block
                  variant="soft"
                  color="red"
                  class="justify-start"
                  @click="confirmDeleteConversation"
                >
                  <UIcon name="i-heroicons-trash" />
                  Delete Conversation
                </UButton>
              </div>
            </UCard>
          </div>
        </div>
      </div>
    </template>

    <!-- Delete Message Modal -->
    <UModal v-model:open="showDeleteMessageModal">
      <template #content>
        <UCard>
          <template #header>
            <h3 class="text-lg font-semibold text-[var(--chat-text-primary)]">Delete Message</h3>
          </template>
          <p class="text-[var(--chat-text-secondary)]">
            Are you sure you want to delete this message? This action will soft-delete the message.
          </p>
          <div v-if="messageToDelete" class="mt-4 p-3 rounded-lg bg-[var(--chat-bg-tertiary)]">
            <div class="text-sm text-[var(--chat-text-secondary)]">Message content:</div>
            <div class="text-sm text-[var(--chat-text-primary)] mt-1 truncate">
              {{ messageToDelete.content }}
            </div>
          </div>
          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton variant="ghost" @click="showDeleteMessageModal = false">Cancel</UButton>
              <UButton color="red" :loading="deletingMessage" @click="deleteMessage">Delete</UButton>
            </div>
          </template>
        </UCard>
      </template>
    </UModal>

    <!-- Delete Conversation Modal -->
    <UModal v-model:open="showDeleteConversationModal">
      <template #content>
        <UCard>
          <template #header>
            <h3 class="text-lg font-semibold text-[var(--chat-text-primary)]">Delete Conversation</h3>
          </template>
          <p class="text-[var(--chat-text-secondary)]">
            Are you sure you want to delete this entire conversation? This action cannot be undone.
          </p>
          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton variant="ghost" @click="showDeleteConversationModal = false">Cancel</UButton>
              <UButton color="red" :loading="deletingConversation" @click="deleteConversation">Delete</UButton>
            </div>
          </template>
        </UCard>
      </template>
    </UModal>
  </div>
</template>

<script setup lang="ts">
import type { Conversation, Message, Participant, Reaction } from '~/types'

definePageMeta({
  layout: 'dashboard'
})

const route = useRoute()
const config = useRuntimeConfig()
const { token } = useAuth()

const workspaceId = computed(() => route.params.workspaceId as string)
const conversationId = computed(() => route.params.id as string)

const loading = ref(true)
const error = ref<string | null>(null)
const conversation = ref<Conversation | null>(null)
const messages = ref<Message[]>([])
const messagesContainer = ref<HTMLElement | null>(null)
const loadingMore = ref(false)
const hasMoreMessages = ref(false)
const totalMessages = ref(0)
const currentPage = ref(1)

// Delete message state
const showDeleteMessageModal = ref(false)
const messageToDelete = ref<Message | null>(null)
const deletingMessage = ref(false)

// Delete conversation state
const showDeleteConversationModal = ref(false)
const deletingConversation = ref(false)

const getConversationName = (conv: Conversation | null) => {
  if (!conv) return 'Loading...'
  if (conv.name) return conv.name
  if (conv.participants && conv.participants.length > 0) {
    return conv.participants
      .slice(0, 2)
      .map(p => getParticipantName(p))
      .join(', ')
  }
  return 'Unnamed Conversation'
}

const getParticipantName = (participant: Participant) => {
  return participant.chatUser?.name || participant.chat_user?.name || 'Unknown'
}

const getParticipantEmail = (participant: Participant) => {
  return participant.chatUser?.email || participant.chat_user?.email
}

const getParticipantLastSeen = (participant: Participant) => {
  return participant.chatUser?.last_seen_at || participant.chat_user?.last_seen_at
}

const isOnline = (lastSeenAt?: string) => {
  if (!lastSeenAt) return false
  const fiveMinutesAgo = Date.now() - 5 * 60 * 1000
  return new Date(lastSeenAt).getTime() > fiveMinutesAgo
}

const formatDate = (dateString?: string) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`

  const diffHours = Math.floor(diffMins / 60)
  if (diffHours < 24) return `${diffHours}h ago`

  const diffDays = Math.floor(diffHours / 24)
  if (diffDays < 7) return `${diffDays}d ago`

  return date.toLocaleDateString()
}

const formatFullDate = (dateString?: string) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString()
}

const formatFileSize = (bytes: number) => {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

const getAttachmentIcon = (mimeType: string) => {
  if (mimeType.startsWith('image/')) return 'i-heroicons-photo'
  if (mimeType.startsWith('video/')) return 'i-heroicons-video-camera'
  if (mimeType.startsWith('audio/')) return 'i-heroicons-musical-note'
  if (mimeType.includes('pdf')) return 'i-heroicons-document'
  return 'i-heroicons-paper-clip'
}

const groupedReactions = (reactions: Reaction[]) => {
  const grouped: Record<string, { emoji: string; count: number }> = {}
  reactions.forEach(r => {
    if (!grouped[r.emoji]) {
      grouped[r.emoji] = { emoji: r.emoji, count: 0 }
    }
    grouped[r.emoji].count++
  })
  return Object.values(grouped)
}

const fetchConversation = async () => {
  loading.value = true
  error.value = null

  try {
    const data = await $fetch<{
      conversation: Conversation
      messages: { data: Message[]; total: number; current_page: number; last_page: number }
    }>(
      `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId.value}/conversations/${conversationId.value}`,
      {
        headers: { Authorization: `Bearer ${token.value}` }
      }
    )

    conversation.value = data.conversation
    // Messages come sorted descending, reverse for chronological display
    messages.value = [...(data.messages.data || [])].reverse()
    totalMessages.value = data.messages.total || messages.value.length
    hasMoreMessages.value = data.messages.current_page < data.messages.last_page
    currentPage.value = data.messages.current_page
  } catch (e: any) {
    error.value = e.data?.message || e.message || 'Failed to load conversation'
    console.error('Error fetching conversation:', e)
  } finally {
    loading.value = false
  }
}

const loadMoreMessages = async () => {
  if (!hasMoreMessages.value || loadingMore.value) return

  loadingMore.value = true
  try {
    const data = await $fetch<{
      conversation: Conversation
      messages: { data: Message[]; total: number; current_page: number; last_page: number }
    }>(
      `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId.value}/conversations/${conversationId.value}`,
      {
        headers: { Authorization: `Bearer ${token.value}` },
        query: { page: currentPage.value + 1 }
      }
    )

    // Prepend older messages (they come sorted descending)
    const olderMessages = [...(data.messages.data || [])].reverse()
    messages.value = [...olderMessages, ...messages.value]
    hasMoreMessages.value = data.messages.current_page < data.messages.last_page
    currentPage.value = data.messages.current_page
  } catch (e: any) {
    console.error('Error loading more messages:', e)
  } finally {
    loadingMore.value = false
  }
}

const confirmDeleteMessage = (message: Message) => {
  messageToDelete.value = message
  showDeleteMessageModal.value = true
}

const deleteMessage = async () => {
  if (!messageToDelete.value) return

  deletingMessage.value = true
  try {
    await $fetch(
      `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId.value}/messages/${messageToDelete.value.id}`,
      {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token.value}` }
      }
    )

    // Mark message as deleted in local state
    const index = messages.value.findIndex(m => m.id === messageToDelete.value?.id)
    if (index !== -1) {
      messages.value[index] = {
        ...messages.value[index],
        deleted_at: new Date().toISOString()
      }
    }

    showDeleteMessageModal.value = false
    messageToDelete.value = null
  } catch (e: any) {
    console.error('Error deleting message:', e)
  } finally {
    deletingMessage.value = false
  }
}

const confirmDeleteConversation = () => {
  showDeleteConversationModal.value = true
}

const deleteConversation = async () => {
  deletingConversation.value = true
  try {
    // Note: This endpoint would need to be implemented on the backend
    console.log('Delete conversation:', conversationId.value)
    showDeleteConversationModal.value = false
    navigateTo(`/dashboard/${workspaceId.value}/conversations`)
  } catch (e: any) {
    console.error('Error deleting conversation:', e)
  } finally {
    deletingConversation.value = false
  }
}

const exportConversation = () => {
  // Create a JSON export of the conversation
  const exportData = {
    conversation: conversation.value,
    messages: messages.value,
    exportedAt: new Date().toISOString()
  }

  const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `conversation-${conversationId.value}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

const archiveConversation = () => {
  // TODO: Implement archive functionality
  console.log('Archive conversation:', conversationId.value)
}

// Fetch conversation on mount
onMounted(() => {
  fetchConversation()
})
</script>
