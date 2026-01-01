<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-[var(--chat-text-primary)]">Dashboard</h1>
      <p class="text-[var(--chat-text-secondary)] mt-1">Overview of your chat workspace</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <UCard>
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-sm text-[var(--chat-text-secondary)]">Total Conversations</span>
            <UIcon name="i-heroicons-chat-bubble-left-right" class="text-[var(--chat-primary)]" />
          </div>
          <div class="text-3xl font-bold text-[var(--chat-text-primary)]">{{ stats.totalConversations }}</div>
          <div class="text-xs text-green-600">+12% from last month</div>
        </div>
      </UCard>

      <UCard>
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-sm text-[var(--chat-text-secondary)]">Active Users</span>
            <UIcon name="i-heroicons-user-group" class="text-[var(--chat-primary)]" />
          </div>
          <div class="text-3xl font-bold text-[var(--chat-text-primary)]">{{ stats.activeUsers }}</div>
          <div class="text-xs text-green-600">+8% from last month</div>
        </div>
      </UCard>

      <UCard>
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-sm text-[var(--chat-text-secondary)]">Messages Today</span>
            <UIcon name="i-heroicons-paper-airplane" class="text-[var(--chat-primary)]" />
          </div>
          <div class="text-3xl font-bold text-[var(--chat-text-primary)]">{{ stats.messagesToday }}</div>
          <div class="text-xs text-red-600">-3% from yesterday</div>
        </div>
      </UCard>

      <UCard>
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-sm text-[var(--chat-text-secondary)]">Avg Response Time</span>
            <UIcon name="i-heroicons-clock" class="text-[var(--chat-primary)]" />
          </div>
          <div class="text-3xl font-bold text-[var(--chat-text-primary)]">{{ stats.avgResponseTime }}</div>
          <div class="text-xs text-green-600">-15% from last week</div>
        </div>
      </UCard>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Conversations -->
      <UCard class="lg:col-span-2">
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Recent Conversations</h2>
            <UButton to="/dashboard/conversations" variant="ghost" size="sm">
              View All
              <UIcon name="i-heroicons-arrow-right" class="ml-1" />
            </UButton>
          </div>
        </template>

        <div class="space-y-3">
          <div
            v-for="conversation in recentConversations"
            :key="conversation.id"
            class="p-4 rounded-lg bg-[var(--chat-bg-primary)] hover:bg-[var(--chat-hover)] cursor-pointer transition-colors"
            @click="navigateTo(`/dashboard/conversations/${conversation.id}`)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <h3 class="font-semibold text-[var(--chat-text-primary)]">
                    {{ conversation.name || 'Unnamed Conversation' }}
                  </h3>
                  <UBadge v-if="conversation.unread_count" color="red" size="xs">
                    {{ conversation.unread_count }}
                  </UBadge>
                </div>
                <p class="text-sm text-[var(--chat-text-secondary)] mt-1 line-clamp-2">
                  {{ conversation.last_message?.content || 'No messages yet' }}
                </p>
                <div class="flex items-center gap-2 mt-2 text-xs text-[var(--chat-text-secondary)]">
                  <span>{{ conversation.participants.length }} participants</span>
                  <span>•</span>
                  <span>{{ formatDate(conversation.last_message_at) }}</span>
                </div>
              </div>
              <UIcon name="i-heroicons-chevron-right" class="text-[var(--chat-text-secondary)]" />
            </div>
          </div>
        </div>
      </UCard>

      <!-- Quick Actions -->
      <UCard>
        <template #header>
          <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Quick Actions</h2>
        </template>

        <div class="space-y-2">
          <UButton
            to="/dashboard/conversations"
            block
            variant="soft"
            color="primary"
            class="justify-start"
          >
            <UIcon name="i-heroicons-chat-bubble-left-right" />
            View All Conversations
          </UButton>

          <UButton
            to="/dashboard/users"
            block
            variant="soft"
            color="primary"
            class="justify-start"
          >
            <UIcon name="i-heroicons-users" />
            Manage Users
          </UButton>

          <UButton
            to="/dashboard/analytics"
            block
            variant="soft"
            color="primary"
            class="justify-start"
          >
            <UIcon name="i-heroicons-chart-bar" />
            View Analytics
          </UButton>

          <UButton
            to="/dashboard/settings"
            block
            variant="soft"
            color="primary"
            class="justify-start"
          >
            <UIcon name="i-heroicons-cog-6-tooth" />
            Workspace Settings
          </UButton>

          <UButton
            to="/dashboard/theme"
            block
            variant="soft"
            color="primary"
            class="justify-start"
          >
            <UIcon name="i-heroicons-paint-brush" />
            Customize Theme
          </UButton>
        </div>
      </UCard>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Conversation } from '~/types'

definePageMeta({
  layout: 'dashboard'
})

// Mock stats data
const stats = ref({
  totalConversations: 1247,
  activeUsers: 892,
  messagesToday: 3456,
  avgResponseTime: '2.3m'
})

// Mock recent conversations data
const recentConversations = ref<Conversation[]>([
  {
    id: '1',
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
          is_anonymous: false
        }
      }
    ],
    last_message: {
      id: 'm1',
      content: 'Thank you for helping me with my issue!',
      sender: {
        id: 'u1',
        name: 'John Doe',
        is_anonymous: false
      },
      attachments: [],
      reactions: [],
      created_at: new Date(Date.now() - 1000 * 60 * 5).toISOString()
    },
    last_message_at: new Date(Date.now() - 1000 * 60 * 5).toISOString(),
    unread_count: 2
  },
  {
    id: '2',
    type: 'direct',
    name: 'Sales Inquiry - Jane Smith',
    participants: [
      {
        id: 'p2',
        chat_user_id: 'u2',
        chatUser: {
          id: 'u2',
          name: 'Jane Smith',
          email: 'jane@example.com',
          is_anonymous: false
        }
      }
    ],
    last_message: {
      id: 'm2',
      content: 'What are your pricing plans for enterprise?',
      sender: {
        id: 'u2',
        name: 'Jane Smith',
        is_anonymous: false
      },
      attachments: [],
      reactions: [],
      created_at: new Date(Date.now() - 1000 * 60 * 15).toISOString()
    },
    last_message_at: new Date(Date.now() - 1000 * 60 * 15).toISOString(),
    unread_count: 1
  },
  {
    id: '3',
    type: 'group',
    name: 'Product Feedback',
    participants: [
      {
        id: 'p3',
        chat_user_id: 'u3',
        chatUser: {
          id: 'u3',
          name: 'Alice Johnson',
          is_anonymous: false
        }
      },
      {
        id: 'p4',
        chat_user_id: 'u4',
        chatUser: {
          id: 'u4',
          name: 'Bob Wilson',
          is_anonymous: false
        }
      }
    ],
    last_message: {
      id: 'm3',
      content: 'The new feature is working great!',
      sender: {
        id: 'u3',
        name: 'Alice Johnson',
        is_anonymous: false
      },
      attachments: [],
      reactions: [],
      created_at: new Date(Date.now() - 1000 * 60 * 30).toISOString()
    },
    last_message_at: new Date(Date.now() - 1000 * 60 * 30).toISOString()
  }
])

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
</script>
