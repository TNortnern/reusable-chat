<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--chat-text-primary)]">Users</h1>
        <p class="text-[var(--chat-text-secondary)] mt-1">Manage chat users and their access</p>
      </div>
      <UButton color="primary" @click="exportUsers">
        <UIcon name="i-heroicons-arrow-down-tray" />
        Export Users
      </UButton>
    </div>

    <!-- Filters and Search -->
    <UCard>
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <UInput
            v-model="search"
            placeholder="Search users by name or email..."
            icon="i-heroicons-magnifying-glass"
            size="lg"
          />
        </div>
        <USelect
          v-model="statusFilter"
          :options="statusOptions"
          placeholder="All Status"
          size="lg"
          class="w-full md:w-48"
        />
        <USelect
          v-model="typeFilter"
          :options="typeOptions"
          placeholder="All Types"
          size="lg"
          class="w-full md:w-48"
        />
      </div>
    </UCard>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <UCard>
        <div class="text-center">
          <div class="text-2xl font-bold text-[var(--chat-text-primary)]">{{ stats.totalUsers }}</div>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Total Users</div>
        </div>
      </UCard>
      <UCard>
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">{{ stats.onlineUsers }}</div>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Online Now</div>
        </div>
      </UCard>
      <UCard>
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ stats.activeToday }}</div>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Active Today</div>
        </div>
      </UCard>
      <UCard>
        <div class="text-center">
          <div class="text-2xl font-bold text-purple-600">{{ stats.anonymousUsers }}</div>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Anonymous</div>
        </div>
      </UCard>
    </div>

    <!-- Users Table -->
    <UCard>
      <UTable
        :rows="filteredUsers"
        :columns="columns"
        :loading="loading"
      >
        <template #user-data="{ row }">
          <div class="flex items-center gap-3">
            <div class="relative">
              <UAvatar
                :alt="row.name"
                size="md"
              />
              <div
                v-if="isOnline(row.last_seen_at)"
                class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"
              />
            </div>
            <div>
              <div class="font-medium text-[var(--chat-text-primary)] flex items-center gap-2">
                {{ row.name }}
                <UBadge v-if="row.is_anonymous" color="gray" size="xs">Anonymous</UBadge>
              </div>
              <div class="text-xs text-[var(--chat-text-secondary)]">
                {{ row.email || 'No email provided' }}
              </div>
            </div>
          </div>
        </template>

        <template #status-data="{ row }">
          <UBadge
            :color="getStatusColor(row.last_seen_at)"
            variant="soft"
          >
            {{ getStatus(row.last_seen_at) }}
          </UBadge>
        </template>

        <template #last_seen-data="{ row }">
          <div class="text-sm text-[var(--chat-text-secondary)]">
            {{ formatDate(row.last_seen_at) }}
          </div>
        </template>

        <template #conversations-data="{ row }">
          <div class="text-sm text-[var(--chat-text-primary)] font-medium">
            {{ row.conversationCount || 0 }}
          </div>
        </template>

        <template #actions-data="{ row }">
          <UDropdown :items="getUserActions(row)">
            <UButton
              icon="i-heroicons-ellipsis-horizontal"
              size="xs"
              variant="ghost"
            />
          </UDropdown>
        </template>
      </UTable>

      <!-- Pagination -->
      <template #footer>
        <div class="flex items-center justify-between">
          <div class="text-sm text-[var(--chat-text-secondary)]">
            Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredUsers.length) }} of {{ filteredUsers.length }} users
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
              :disabled="currentPage * pageSize >= filteredUsers.length"
              @click="currentPage++"
            />
          </div>
        </div>
      </template>
    </UCard>
  </div>
</template>

<script setup lang="ts">
import type { ChatUser } from '~/types'

definePageMeta({
  layout: 'dashboard'
})

interface UserWithStats extends ChatUser {
  conversationCount?: number
  messageCount?: number
}

const search = ref('')
const statusFilter = ref('')
const typeFilter = ref('')
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)

const statusOptions = [
  { label: 'All Status', value: '' },
  { label: 'Online', value: 'online' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

const typeOptions = [
  { label: 'All Types', value: '' },
  { label: 'Registered', value: 'registered' },
  { label: 'Anonymous', value: 'anonymous' }
]

const columns = [
  { key: 'user', label: 'User' },
  { key: 'status', label: 'Status' },
  { key: 'last_seen', label: 'Last Seen' },
  { key: 'conversations', label: 'Conversations' },
  { key: 'actions', label: 'Actions' }
]

const stats = ref({
  totalUsers: 1543,
  onlineUsers: 234,
  activeToday: 567,
  anonymousUsers: 89
})

// Mock users data
const users = ref<UserWithStats[]>([
  {
    id: 'u1',
    name: 'John Doe',
    email: 'john@example.com',
    is_anonymous: false,
    last_seen_at: new Date().toISOString(),
    conversationCount: 5,
    messageCount: 123
  },
  {
    id: 'u2',
    name: 'Jane Smith',
    email: 'jane@example.com',
    is_anonymous: false,
    last_seen_at: new Date(Date.now() - 1000 * 60 * 30).toISOString(),
    conversationCount: 3,
    messageCount: 87
  },
  {
    id: 'u3',
    name: 'Alice Johnson',
    email: 'alice@example.com',
    is_anonymous: false,
    last_seen_at: new Date(Date.now() - 1000 * 60 * 5).toISOString(),
    conversationCount: 8,
    messageCount: 245
  },
  {
    id: 'u4',
    name: 'Bob Wilson',
    is_anonymous: true,
    last_seen_at: new Date(Date.now() - 1000 * 60 * 60 * 24).toISOString(),
    conversationCount: 1,
    messageCount: 12
  },
  {
    id: 'u5',
    name: 'Mike Chen',
    email: 'mike@example.com',
    is_anonymous: false,
    last_seen_at: new Date(Date.now() - 1000 * 60 * 60 * 48).toISOString(),
    conversationCount: 2,
    messageCount: 34
  },
  {
    id: 'u6',
    name: 'Sarah Davis',
    email: 'sarah@example.com',
    is_anonymous: false,
    last_seen_at: new Date(Date.now() - 1000 * 60 * 15).toISOString(),
    conversationCount: 6,
    messageCount: 178
  }
])

const filteredUsers = computed(() => {
  let filtered = users.value

  if (search.value) {
    filtered = filtered.filter(u =>
      u.name.toLowerCase().includes(search.value.toLowerCase()) ||
      u.email?.toLowerCase().includes(search.value.toLowerCase())
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(u => {
      const status = getStatus(u.last_seen_at).toLowerCase()
      return status === statusFilter.value
    })
  }

  if (typeFilter.value) {
    filtered = filtered.filter(u => {
      if (typeFilter.value === 'anonymous') return u.is_anonymous
      if (typeFilter.value === 'registered') return !u.is_anonymous
      return true
    })
  }

  return filtered
})

const isOnline = (lastSeenAt?: string) => {
  if (!lastSeenAt) return false
  const fiveMinutesAgo = Date.now() - 5 * 60 * 1000
  return new Date(lastSeenAt).getTime() > fiveMinutesAgo
}

const getStatus = (lastSeenAt?: string) => {
  if (!lastSeenAt) return 'Inactive'
  if (isOnline(lastSeenAt)) return 'Online'

  const lastSeen = new Date(lastSeenAt).getTime()
  const hoursSinceLastSeen = (Date.now() - lastSeen) / (1000 * 60 * 60)

  if (hoursSinceLastSeen < 24) return 'Active'
  return 'Inactive'
}

const getStatusColor = (lastSeenAt?: string) => {
  const status = getStatus(lastSeenAt)
  if (status === 'Online') return 'green'
  if (status === 'Active') return 'blue'
  return 'gray'
}

const formatDate = (dateString?: string) => {
  if (!dateString) return 'Never'
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

const getUserActions = (user: UserWithStats) => {
  return [
    [{
      label: 'View Details',
      icon: 'i-heroicons-eye',
      click: () => viewUserDetails(user)
    }],
    [{
      label: 'View Conversations',
      icon: 'i-heroicons-chat-bubble-left-right',
      click: () => viewUserConversations(user)
    }],
    [{
      label: 'Block User',
      icon: 'i-heroicons-no-symbol',
      click: () => blockUser(user)
    }, {
      label: 'Ban User',
      icon: 'i-heroicons-shield-exclamation',
      click: () => banUser(user)
    }],
    [{
      label: 'Delete User',
      icon: 'i-heroicons-trash',
      click: () => deleteUser(user)
    }]
  ]
}

const viewUserDetails = (user: UserWithStats) => {
  console.log('View details:', user.id)
  // TODO: Navigate to user detail page or open modal
}

const viewUserConversations = (user: UserWithStats) => {
  console.log('View conversations:', user.id)
  // TODO: Navigate to conversations filtered by user
}

const blockUser = (user: UserWithStats) => {
  console.log('Block user:', user.id)
  // TODO: Implement block functionality with confirmation
}

const banUser = (user: UserWithStats) => {
  console.log('Ban user:', user.id)
  // TODO: Implement ban functionality with confirmation
}

const deleteUser = (user: UserWithStats) => {
  console.log('Delete user:', user.id)
  // TODO: Implement delete functionality with confirmation
}

const exportUsers = () => {
  console.log('Export users')
  // TODO: Implement export functionality
}
</script>
