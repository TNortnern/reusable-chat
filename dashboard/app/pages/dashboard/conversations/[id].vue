<template>
  <div class="h-[calc(100vh-4rem)] flex">
    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col bg-[var(--chat-bg-primary)]">
      <!-- Header -->
      <div class="p-4 border-b border-[var(--chat-border)]">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <UButton
              icon="i-heroicons-arrow-left"
              variant="ghost"
              @click="navigateTo('/dashboard/conversations')"
            />
            <div>
              <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">
                {{ conversation?.name || 'Unnamed Conversation' }}
              </h2>
              <div class="text-sm text-[var(--chat-text-secondary)]">
                {{ conversation?.participants.length }} participant{{ conversation?.participants.length !== 1 ? 's' : '' }}
              </div>
            </div>
          </div>
          <UBadge :color="conversation?.type === 'direct' ? 'blue' : 'purple'" variant="soft">
            {{ conversation?.type }}
          </UBadge>
        </div>
      </div>

      <!-- Chat Window -->
      <div class="flex-1 p-4">
        <ChatWindow
          v-if="conversation"
          :conversation="conversation"
          :messages="messages"
          :current-user-id="'admin'"
          :disabled="true"
        />
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
                <div class="text-[var(--chat-text-secondary)]">Created</div>
                <div class="text-[var(--chat-text-primary)] font-medium">
                  {{ formatFullDate(conversation?.last_message_at) }}
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
                  {{ messages.length }}
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
                <UAvatar
                  :alt="participant.chatUser.name"
                  size="sm"
                />
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-[var(--chat-text-primary)] truncate">
                    {{ participant.chatUser.name }}
                  </div>
                  <div class="text-xs text-[var(--chat-text-secondary)] truncate">
                    {{ participant.chatUser.email || 'No email' }}
                  </div>
                </div>
                <UBadge
                  v-if="isOnline(participant.chatUser.last_seen_at)"
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
                @click="muteConversation"
              >
                <UIcon name="i-heroicons-bell-slash" />
                Mute Notifications
              </UButton>

              <UButton
                block
                variant="soft"
                color="purple"
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
                @click="deleteConversation"
              >
                <UIcon name="i-heroicons-trash" />
                Delete Conversation
              </UButton>
            </div>
          </UCard>
        </div>

        <!-- Message Management -->
        <div>
          <h3 class="font-semibold text-[var(--chat-text-primary)] mb-3">Message Actions</h3>
          <UCard>
            <div class="space-y-2">
              <UButton
                block
                variant="soft"
                color="gray"
                class="justify-start"
                @click="clearMessages"
              >
                <UIcon name="i-heroicons-trash" />
                Clear All Messages
              </UButton>

              <UButton
                block
                variant="soft"
                color="red"
                class="justify-start"
                @click="banUser"
              >
                <UIcon name="i-heroicons-no-symbol" />
                Ban Participants
              </UButton>
            </div>
          </UCard>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Conversation, Message } from '~/types'

definePageMeta({
  layout: 'dashboard'
})

const route = useRoute()
const conversationId = route.params.id as string

// Mock conversation data
const conversation = ref<Conversation>({
  id: conversationId,
  type: 'direct',
  name: 'Customer Support - John Doe',
  participants: [
    {
      id: 'p1',
      chat_user_id: 'u1',
      chatUser: {
        id: 'u1',
        name: 'John Doe',
        email: 'john@example.com',
        is_anonymous: false,
        last_seen_at: new Date().toISOString()
      }
    }
  ],
  last_message_at: new Date(Date.now() - 1000 * 60 * 5).toISOString()
})

// Mock messages data
const messages = ref<Message[]>([
  {
    id: 'm1',
    content: 'Hello, I need help with my account',
    sender: {
      id: 'u1',
      name: 'John Doe',
      is_anonymous: false
    },
    attachments: [],
    reactions: [],
    created_at: new Date(Date.now() - 1000 * 60 * 30).toISOString()
  },
  {
    id: 'm2',
    content: 'Hi John! I\'d be happy to help you. What seems to be the issue?',
    sender: {
      id: 'admin',
      name: 'Support Team',
      is_anonymous: false
    },
    attachments: [],
    reactions: [],
    created_at: new Date(Date.now() - 1000 * 60 * 28).toISOString()
  },
  {
    id: 'm3',
    content: 'I can\'t seem to update my billing information',
    sender: {
      id: 'u1',
      name: 'John Doe',
      is_anonymous: false
    },
    attachments: [],
    reactions: [],
    created_at: new Date(Date.now() - 1000 * 60 * 25).toISOString()
  },
  {
    id: 'm4',
    content: 'I can help you with that. Let me guide you through the process.',
    sender: {
      id: 'admin',
      name: 'Support Team',
      is_anonymous: false
    },
    attachments: [],
    reactions: [],
    created_at: new Date(Date.now() - 1000 * 60 * 20).toISOString()
  },
  {
    id: 'm5',
    content: 'Thank you for helping me with my issue!',
    sender: {
      id: 'u1',
      name: 'John Doe',
      is_anonymous: false
    },
    attachments: [],
    reactions: [],
    created_at: new Date(Date.now() - 1000 * 60 * 5).toISOString()
  }
])

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

const exportConversation = () => {
  // TODO: Implement export functionality
  console.log('Export conversation:', conversationId)
}

const muteConversation = () => {
  // TODO: Implement mute functionality
  console.log('Mute conversation:', conversationId)
}

const archiveConversation = () => {
  // TODO: Implement archive functionality
  console.log('Archive conversation:', conversationId)
}

const deleteConversation = () => {
  // TODO: Implement delete with confirmation
  console.log('Delete conversation:', conversationId)
}

const clearMessages = () => {
  // TODO: Implement clear messages with confirmation
  console.log('Clear messages:', conversationId)
}

const banUser = () => {
  // TODO: Implement ban user functionality
  console.log('Ban user from conversation:', conversationId)
}
</script>
