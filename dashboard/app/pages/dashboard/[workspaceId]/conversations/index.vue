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

    <!-- Loading State -->
    <UCard v-if="loading">
      <div class="flex items-center justify-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-[var(--chat-text-secondary)]" />
      </div>
    </UCard>

    <!-- Error State -->
    <UCard v-else-if="error">
      <div class="text-center py-12">
        <UIcon name="i-heroicons-exclamation-triangle" class="w-12 h-12 text-red-500 mx-auto mb-4" />
        <p class="text-[var(--chat-text-secondary)]">{{ error }}</p>
        <UButton class="mt-4" @click="fetchConversations">Try Again</UButton>
      </div>
    </UCard>

    <!-- Empty State -->
    <UCard v-else-if="filteredConversations.length === 0">
      <div class="text-center py-12">
        <UIcon name="i-heroicons-chat-bubble-left-right" class="w-12 h-12 text-[var(--chat-text-secondary)] mx-auto mb-4" />
        <p class="text-[var(--chat-text-secondary)]">
          {{ search || typeFilter || statusFilter ? 'No conversations match your filters' : 'No conversations yet' }}
        </p>
      </div>
    </UCard>

    <!-- Conversations Table -->
    <UCard v-else>
      <UTable
        :data="paginatedConversations"
        :columns="columns"
      >
        <template #name-cell="{ row }">
          <div class="flex items-center gap-3">
            <div class="flex -space-x-2">
              <UAvatar
                v-for="participant in row.original.participants?.slice(0, 2)"
                :key="participant.id"
                :alt="participant.chatUser?.name || participant.chat_user?.name || 'User'"
                size="sm"
              />
            </div>
            <div>
              <div class="font-medium text-[var(--chat-text-primary)]">
                {{ getConversationName(row.original) }}
              </div>
              <div class="text-xs text-[var(--chat-text-secondary)]">
                {{ row.original.participants?.length || 0 }} participant{{ row.original.participants?.length !== 1 ? 's' : '' }}
              </div>
            </div>
          </div>
        </template>

        <template #type-cell="{ row }">
          <UBadge :color="row.original.type === 'direct' ? 'blue' : 'purple'" variant="soft">
            {{ row.original.type }}
          </UBadge>
        </template>

        <template #last_message-cell="{ row }">
          <div class="max-w-md">
            <div class="text-sm text-[var(--chat-text-primary)] truncate">
              {{ row.original.last_message?.content || row.original.lastMessage?.content || 'No messages yet' }}
            </div>
            <div class="text-xs text-[var(--chat-text-secondary)] mt-1">
              {{ getLastMessageSender(row.original) }} - {{ formatDate(row.original.last_message_at) }}
            </div>
          </div>
        </template>

        <template #messages_count-cell="{ row }">
          <div class="text-sm text-[var(--chat-text-primary)]">
            {{ row.original.messages_count || 0 }}
          </div>
        </template>

        <template #status-cell="{ row }">
          <div class="flex items-center gap-2">
            <UBadge
              :color="getStatusColor(row.original)"
              variant="soft"
            >
              {{ getStatus(row.original) }}
            </UBadge>
            <UBadge v-if="row.original.unread_count" color="red" variant="solid" size="xs">
              {{ row.original.unread_count }}
            </UBadge>
          </div>
        </template>

        <template #actions-cell="{ row }">
          <div class="flex items-center gap-2">
            <UButton
              icon="i-heroicons-eye"
              size="xs"
              variant="ghost"
              @click="viewConversation(row.original)"
            />
            <UButton
              icon="i-heroicons-trash"
              size="xs"
              variant="ghost"
              color="red"
              @click="confirmDelete(row.original)"
            />
          </div>
        </template>
      </UTable>

      <!-- Pagination -->
      <template #footer>
        <div class="flex items-center justify-between">
          <div class="text-sm text-[var(--chat-text-secondary)]">
            Showing {{ paginationStart }} to {{ paginationEnd }} of {{ filteredConversations.length }} conversations
          </div>
          <div class="flex gap-2">
            <UButton
              icon="i-heroicons-chevron-left"
              size="sm"
              variant="ghost"
              :disabled="currentPage === 1"
              @click="currentPage--"
            />
            <span class="flex items-center px-3 text-sm text-[var(--chat-text-secondary)]">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <UButton
              icon="i-heroicons-chevron-right"
              size="sm"
              variant="ghost"
              :disabled="currentPage >= totalPages"
              @click="currentPage++"
            />
          </div>
        </div>
      </template>
    </UCard>

    <!-- Delete Confirmation Modal -->
    <UModal v-model:open="showDeleteModal">
      <template #content>
        <UCard>
          <template #header>
            <h3 class="text-lg font-semibold text-[var(--chat-text-primary)]">Delete Conversation</h3>
          </template>
          <p class="text-[var(--chat-text-secondary)]">
            Are you sure you want to delete this conversation? This action cannot be undone.
          </p>
          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton variant="ghost" @click="showDeleteModal = false">Cancel</UButton>
              <UButton color="red" :loading="deleting" @click="deleteConversation">Delete</UButton>
            </div>
          </template>
        </UCard>
      </template>
    </UModal>
  </div>
</template>

<script setup lang="ts">
import type { Conversation } from '~/types'

definePageMeta({
  layout: 'dashboard'
})

const route = useRoute()
const config = useRuntimeConfig()
const { token } = useAuth()

const workspaceId = computed(() => route.params.workspaceId as string)

const search = ref('')
const typeFilter = ref('')
const statusFilter = ref('')
const loading = ref(false)
const error = ref<string | null>(null)
const currentPage = ref(1)
const pageSize = ref(10)
const conversations = ref<Conversation[]>([])
const showDeleteModal = ref(false)
const conversationToDelete = ref<Conversation | null>(null)
const deleting = ref(false)

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
  { accessorKey: 'name', header: 'Conversation', id: 'name' },
  { accessorKey: 'type', header: 'Type', id: 'type' },
  { accessorKey: 'last_message', header: 'Last Message', id: 'last_message' },
  { accessorKey: 'messages_count', header: 'Messages', id: 'messages_count' },
  { accessorKey: 'status', header: 'Status', id: 'status' },
  { accessorKey: 'actions', header: 'Actions', id: 'actions' }
]

const filteredConversations = computed(() => {
  let filtered = conversations.value

  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(c =>
      c.name?.toLowerCase().includes(searchLower) ||
      c.participants?.some(p => {
        const name = p.chatUser?.name || p.chat_user?.name || ''
        return name.toLowerCase().includes(searchLower)
      })
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

const totalPages = computed(() => Math.ceil(filteredConversations.value.length / pageSize.value) || 1)

const paginatedConversations = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredConversations.value.slice(start, end)
})

const paginationStart = computed(() => {
  if (filteredConversations.value.length === 0) return 0
  return (currentPage.value - 1) * pageSize.value + 1
})

const paginationEnd = computed(() => {
  return Math.min(currentPage.value * pageSize.value, filteredConversations.value.length)
})

// Reset to page 1 when filters change
watch([search, typeFilter, statusFilter], () => {
  currentPage.value = 1
})

const getConversationName = (conversation: Conversation) => {
  if (conversation.name) return conversation.name
  if (conversation.participants && conversation.participants.length > 0) {
    return conversation.participants
      .slice(0, 2)
      .map(p => p.chatUser?.name || p.chat_user?.name || 'Unknown')
      .join(', ')
  }
  return 'Unnamed Conversation'
}

const getLastMessageSender = (conversation: Conversation) => {
  const lastMessage = conversation.last_message || conversation.lastMessage
  if (!lastMessage) return ''
  return lastMessage.sender?.name || 'Unknown'
}

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

const fetchConversations = async () => {
  loading.value = true
  error.value = null

  try {
    const data = await $fetch<{ data: Conversation[] }>(
      `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId.value}/conversations`,
      {
        headers: { Authorization: `Bearer ${token.value}` }
      }
    )
    conversations.value = data.data || []
  } catch (e: any) {
    error.value = e.data?.message || e.message || 'Failed to load conversations'
    console.error('Error fetching conversations:', e)
  } finally {
    loading.value = false
  }
}

const viewConversation = (conversation: Conversation) => {
  navigateTo(`/dashboard/${workspaceId.value}/conversations/${conversation.id}`)
}

const confirmDelete = (conversation: Conversation) => {
  conversationToDelete.value = conversation
  showDeleteModal.value = true
}

const deleteConversation = async () => {
  if (!conversationToDelete.value) return

  deleting.value = true
  try {
    // Note: The API only has destroyMessage, not destroyConversation
    // This would need to be implemented on the backend
    console.log('Delete conversation:', conversationToDelete.value.id)
    showDeleteModal.value = false
    conversationToDelete.value = null
    // Refresh the list
    await fetchConversations()
  } catch (e: any) {
    console.error('Error deleting conversation:', e)
  } finally {
    deleting.value = false
  }
}

// Fetch conversations on mount
onMounted(() => {
  fetchConversations()
})
</script>
