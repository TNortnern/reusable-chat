<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--chat-text-primary)]">Conversations</h1>
        <p class="text-[var(--chat-text-secondary)] mt-1">Manage all chat conversations</p>
      </div>
    </div>

    <!-- Filters and Search -->
    <UCard>
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <UInput
            v-model="search"
            placeholder="Search conversations..."
            icon="i-heroicons-magnifying-glass"
            size="lg"
          />
        </div>
        <USelect
          v-model="typeFilter"
          :options="typeOptions"
          placeholder="All Types"
          size="lg"
          class="w-full md:w-48"
        />
        <USelect
          v-model="statusFilter"
          :options="statusOptions"
          placeholder="All Status"
          size="lg"
          class="w-full md:w-48"
        />
      </div>
    </UCard>

    <!-- Conversations Table -->
    <UCard>
      <UTable
        :rows="filteredConversations"
        :columns="columns"
        :loading="loading"
      >
        <template #name-data="{ row }">
          <div class="flex items-center gap-3">
            <div class="flex -space-x-2">
              <UAvatar
                v-for="participant in row.participants.slice(0, 2)"
                :key="participant.id"
                :alt="participant.chatUser.name"
                size="sm"
              />
            </div>
            <div>
              <div class="font-medium text-[var(--chat-text-primary)]">
                {{ row.name || 'Unnamed Conversation' }}
              </div>
              <div class="text-xs text-[var(--chat-text-secondary)]">
                {{ row.participants.length }} participant{{ row.participants.length !== 1 ? 's' : '' }}
              </div>
            </div>
          </div>
        </template>

        <template #type-data="{ row }">
          <UBadge :color="row.type === 'direct' ? 'blue' : 'purple'" variant="soft">
            {{ row.type }}
          </UBadge>
        </template>

        <template #last_message-data="{ row }">
          <div class="max-w-md">
            <div class="text-sm text-[var(--chat-text-primary)] truncate">
              {{ row.last_message?.content || 'No messages yet' }}
            </div>
            <div class="text-xs text-[var(--chat-text-secondary)] mt-1">
              {{ row.last_message?.sender.name }} • {{ formatDate(row.last_message_at) }}
            </div>
          </div>
        </template>

        <template #status-data="{ row }">
          <UBadge
            :color="getStatusColor(row)"
            variant="soft"
          >
            {{ getStatus(row) }}
          </UBadge>
        </template>

        <template #actions-data="{ row }">
          <div class="flex items-center gap-2">
            <UButton
              icon="i-heroicons-eye"
              size="xs"
              variant="ghost"
              @click="navigateTo(`/dashboard/conversations/${row.id}`)"
            />
            <UButton
              icon="i-heroicons-trash"
              size="xs"
              variant="ghost"
              color="red"
              @click="confirmDelete(row)"
            />
          </div>
        </template>
      </UTable>

      <!-- Pagination -->
      <template #footer>
        <div class="flex items-center justify-between">
          <div class="text-sm text-[var(--chat-text-secondary)]">
            Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredConversations.length) }} of {{ filteredConversations.length }} conversations
          </div>
          <div class="flex gap-2">
            <UButton
              icon="i-heroicons-chevron-left"
              size="sm"
              variant="ghost"
              :disabled="currentPage === 1"
              @click="currentPage--"
            />
            <UButton
              icon="i-heroicons-chevron-right"
              size="sm"
              variant="ghost"
              :disabled="currentPage * pageSize >= filteredConversations.length"
              @click="currentPage++"
            />
          </div>
        </div>
      </template>
    </UCard>
  </div>
</template>

<script setup lang="ts">
import type { Conversation } from '~/types'

definePageMeta({
  layout: 'dashboard'
})

const search = ref('')
const typeFilter = ref('')
const statusFilter = ref('')
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)

const typeOptions = [
  { label: 'All Types', value: '' },
  { label: 'Direct', value: 'direct' },
  { label: 'Group', value: 'group' }
]

const statusOptions = [
  { label: 'All Status', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

const columns = [
  { key: 'name', label: 'Conversation' },
  { key: 'type', label: 'Type' },
  { key: 'last_message', label: 'Last Message' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' }
]

// Mock conversations data
const conversations = ref<Conversation[]>([
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
          is_anonymous: false,
          last_seen_at: new Date().toISOString()
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
          is_anonymous: false,
          last_seen_at: new Date(Date.now() - 1000 * 60 * 60).toISOString()
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
          is_anonymous: false,
          last_seen_at: new Date().toISOString()
        }
      },
      {
        id: 'p4',
        chat_user_id: 'u4',
        chatUser: {
          id: 'u4',
          name: 'Bob Wilson',
          is_anonymous: false,
          last_seen_at: new Date(Date.now() - 1000 * 60 * 60 * 24).toISOString()
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
  },
  {
    id: '4',
    type: 'direct',
    name: 'Bug Report - Mike Chen',
    participants: [
      {
        id: 'p5',
        chat_user_id: 'u5',
        chatUser: {
          id: 'u5',
          name: 'Mike Chen',
          email: 'mike@example.com',
          is_anonymous: false,
          last_seen_at: new Date(Date.now() - 1000 * 60 * 60 * 24 * 2).toISOString()
        }
      }
    ],
    last_message: {
      id: 'm4',
      content: 'I found a bug in the checkout process',
      sender: {
        id: 'u5',
        name: 'Mike Chen',
        is_anonymous: false
      },
      attachments: [],
      reactions: [],
      created_at: new Date(Date.now() - 1000 * 60 * 60 * 2).toISOString()
    },
    last_message_at: new Date(Date.now() - 1000 * 60 * 60 * 2).toISOString()
  }
])

const filteredConversations = computed(() => {
  let filtered = conversations.value

  if (search.value) {
    filtered = filtered.filter(c =>
      c.name?.toLowerCase().includes(search.value.toLowerCase()) ||
      c.participants.some(p => p.chatUser.name.toLowerCase().includes(search.value.toLowerCase()))
    )
  }

  if (typeFilter.value) {
    filtered = filtered.filter(c => c.type === typeFilter.value)
  }

  if (statusFilter.value) {
    filtered = filtered.filter(c => {
      const status = getStatus(c)
      return status.toLowerCase() === statusFilter.value
    })
  }

  return filtered
})

const getStatus = (conversation: Conversation) => {
  if (!conversation.last_message_at) return 'Inactive'
  const lastMessageTime = new Date(conversation.last_message_at).getTime()
  const hoursSinceLastMessage = (Date.now() - lastMessageTime) / (1000 * 60 * 60)
  return hoursSinceLastMessage < 24 ? 'Active' : 'Inactive'
}

const getStatusColor = (conversation: Conversation) => {
  return getStatus(conversation) === 'Active' ? 'green' : 'gray'
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

const confirmDelete = (conversation: Conversation) => {
  // TODO: Implement delete confirmation modal
  console.log('Delete conversation:', conversation.id)
}
</script>
