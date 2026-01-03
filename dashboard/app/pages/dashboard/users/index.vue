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
            @keyup.enter="fetchUsers"
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
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <UCard>
        <div class="text-center">
          <template v-if="loading && !users.length">
            <div class="h-8 w-16 bg-gray-200 rounded animate-pulse mx-auto"></div>
          </template>
          <template v-else>
            <div class="text-2xl font-bold text-[var(--chat-text-primary)]">{{ stats.totalUsers }}</div>
          </template>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Total Users</div>
        </div>
      </UCard>
      <UCard>
        <div class="text-center">
          <template v-if="loading && !users.length">
            <div class="h-8 w-16 bg-gray-200 rounded animate-pulse mx-auto"></div>
          </template>
          <template v-else>
            <div class="text-2xl font-bold text-green-600">{{ stats.onlineUsers }}</div>
          </template>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Online Now</div>
        </div>
      </UCard>
      <UCard>
        <div class="text-center">
          <template v-if="loading && !users.length">
            <div class="h-8 w-16 bg-gray-200 rounded animate-pulse mx-auto"></div>
          </template>
          <template v-else>
            <div class="text-2xl font-bold text-blue-600">{{ stats.activeToday }}</div>
          </template>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Active Today</div>
        </div>
      </UCard>
      <UCard>
        <div class="text-center">
          <template v-if="loading && !users.length">
            <div class="h-8 w-16 bg-gray-200 rounded animate-pulse mx-auto"></div>
          </template>
          <template v-else>
            <div class="text-2xl font-bold text-purple-600">{{ stats.anonymousUsers }}</div>
          </template>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Anonymous</div>
        </div>
      </UCard>
      <UCard>
        <div class="text-center">
          <template v-if="loading && !users.length">
            <div class="h-8 w-16 bg-gray-200 rounded animate-pulse mx-auto"></div>
          </template>
          <template v-else>
            <div class="text-2xl font-bold text-red-600">{{ stats.bannedUsers }}</div>
          </template>
          <div class="text-sm text-[var(--chat-text-secondary)] mt-1">Banned</div>
        </div>
      </UCard>
    </div>

    <!-- Loading State -->
    <UCard v-if="loading && !users.length">
      <div class="space-y-4">
        <div v-for="i in 5" :key="i" class="flex items-center gap-4 p-4">
          <div class="w-10 h-10 bg-gray-200 rounded-full animate-pulse"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 w-32 bg-gray-200 rounded animate-pulse"></div>
            <div class="h-3 w-48 bg-gray-200 rounded animate-pulse"></div>
          </div>
          <div class="h-6 w-16 bg-gray-200 rounded animate-pulse"></div>
        </div>
      </div>
    </UCard>

    <!-- Error State -->
    <UCard v-else-if="error">
      <div class="text-center py-12">
        <UIcon name="i-heroicons-exclamation-triangle" class="w-12 h-12 text-red-500 mx-auto mb-4" />
        <p class="text-[var(--chat-text-secondary)]">{{ error }}</p>
        <UButton class="mt-4" @click="fetchUsers">Try Again</UButton>
      </div>
    </UCard>

    <!-- Empty State -->
    <UCard v-else-if="users.length === 0">
      <div class="text-center py-12">
        <UIcon name="i-heroicons-users" class="w-12 h-12 text-[var(--chat-text-secondary)] mx-auto mb-4" />
        <p class="text-[var(--chat-text-secondary)]">
          {{ search || statusFilter || typeFilter ? 'No users match your filters' : 'No users yet' }}
        </p>
      </div>
    </UCard>

    <!-- Users Table -->
    <UCard v-else>
      <UTable
        :rows="users"
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
                <UBadge v-if="row.ban" color="red" size="xs">Banned</UBadge>
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
            {{ row.conversations_count || 0 }}
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
            Showing {{ paginationStart }} to {{ paginationEnd }} of {{ pagination.total }} users
          </div>
          <div class="flex gap-2">
            <UButton
              icon="i-heroicons-chevron-left"
              size="sm"
              variant="ghost"
              :disabled="pagination.currentPage === 1"
              @click="goToPage(pagination.currentPage - 1)"
            />
            <span class="flex items-center px-3 text-sm text-[var(--chat-text-secondary)]">
              Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
            </span>
            <UButton
              icon="i-heroicons-chevron-right"
              size="sm"
              variant="ghost"
              :disabled="pagination.currentPage >= pagination.lastPage"
              @click="goToPage(pagination.currentPage + 1)"
            />
          </div>
        </div>
      </template>
    </UCard>

    <!-- Ban Confirmation Modal -->
    <UModal v-model:open="showBanModal">
      <template #content>
        <UCard>
          <template #header>
            <h3 class="text-lg font-semibold text-[var(--chat-text-primary)]">Ban User</h3>
          </template>
          <div class="space-y-4">
            <p class="text-[var(--chat-text-secondary)]">
              Are you sure you want to ban <strong>{{ userToBan?.name }}</strong>? They will not be able to participate in any conversations.
            </p>
            <UFormGroup label="Reason (optional)">
              <UInput v-model="banReason" placeholder="Enter reason for ban..." />
            </UFormGroup>
            <UFormGroup label="Expires at (optional)">
              <UInput v-model="banExpiresAt" type="datetime-local" />
            </UFormGroup>
          </div>
          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton variant="ghost" @click="closeBanModal">Cancel</UButton>
              <UButton color="red" :loading="banning" @click="confirmBan">Ban User</UButton>
            </div>
          </template>
        </UCard>
      </template>
    </UModal>

    <!-- Unban Confirmation Modal -->
    <UModal v-model:open="showUnbanModal">
      <template #content>
        <UCard>
          <template #header>
            <h3 class="text-lg font-semibold text-[var(--chat-text-primary)]">Unban User</h3>
          </template>
          <p class="text-[var(--chat-text-secondary)]">
            Are you sure you want to unban <strong>{{ userToUnban?.name }}</strong>? They will be able to participate in conversations again.
          </p>
          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton variant="ghost" @click="closeUnbanModal">Cancel</UButton>
              <UButton color="primary" :loading="unbanning" @click="confirmUnban">Unban User</UButton>
            </div>
          </template>
        </UCard>
      </template>
    </UModal>
  </div>
</template>

<script setup lang="ts">
import type { ChatUser } from '~/types'

definePageMeta({
  layout: 'dashboard'
})

const route = useRoute()
const config = useRuntimeConfig()
const { token, currentWorkspace } = useAuth()
const toast = useToast()

const workspaceId = computed(() => route.params.workspaceId as string || currentWorkspace.value?.id)

const search = ref('')
const statusFilter = ref('')
const typeFilter = ref('')
const loading = ref(false)
const error = ref<string | null>(null)

const users = ref<ChatUser[]>([])
const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 20,
  total: 0
})

const stats = ref({
  totalUsers: 0,
  onlineUsers: 0,
  activeToday: 0,
  anonymousUsers: 0,
  bannedUsers: 0
})

// Ban modal state
const showBanModal = ref(false)
const userToBan = ref<ChatUser | null>(null)
const banReason = ref('')
const banExpiresAt = ref('')
const banning = ref(false)

// Unban modal state
const showUnbanModal = ref(false)
const userToUnban = ref<ChatUser | null>(null)
const unbanning = ref(false)

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

const paginationStart = computed(() => {
  if (pagination.value.total === 0) return 0
  return (pagination.value.currentPage - 1) * pagination.value.perPage + 1
})

const paginationEnd = computed(() => {
  return Math.min(pagination.value.currentPage * pagination.value.perPage, pagination.value.total)
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

const getUserActions = (user: ChatUser) => {
  const actions = [
    [{
      label: 'View Details',
      icon: 'i-heroicons-eye',
      click: () => viewUserDetails(user)
    }],
    [{
      label: 'View Conversations',
      icon: 'i-heroicons-chat-bubble-left-right',
      click: () => viewUserConversations(user)
    }]
  ]

  if (user.ban) {
    actions.push([{
      label: 'Unban User',
      icon: 'i-heroicons-check-circle',
      click: () => openUnbanModal(user)
    }])
  } else {
    actions.push([{
      label: 'Ban User',
      icon: 'i-heroicons-no-symbol',
      click: () => openBanModal(user)
    }])
  }

  return actions
}

const fetchUsers = async (page = 1) => {
  if (!workspaceId.value) {
    error.value = 'No workspace selected'
    return
  }

  loading.value = true
  error.value = null

  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pagination.value.perPage.toString()
    })

    if (search.value) params.append('search', search.value)
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (typeFilter.value) params.append('type', typeFilter.value)

    const response = await $fetch<{
      users: {
        data: ChatUser[]
        current_page: number
        last_page: number
        per_page: number
        total: number
      }
      stats: {
        total_users: number
        online_users: number
        active_today: number
        anonymous_users: number
        banned_users: number
      }
    }>(
      `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId.value}/users?${params}`,
      {
        headers: { Authorization: `Bearer ${token.value}` }
      }
    )

    users.value = response.users.data
    pagination.value = {
      currentPage: response.users.current_page,
      lastPage: response.users.last_page,
      perPage: response.users.per_page,
      total: response.users.total
    }
    stats.value = {
      totalUsers: response.stats.total_users,
      onlineUsers: response.stats.online_users,
      activeToday: response.stats.active_today,
      anonymousUsers: response.stats.anonymous_users,
      bannedUsers: response.stats.banned_users
    }
  } catch (e: any) {
    error.value = e.data?.message || e.message || 'Failed to load users'
    console.error('Error fetching users:', e)
  } finally {
    loading.value = false
  }
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.value.lastPage) {
    fetchUsers(page)
  }
}

// Watch filters and refetch
watch([statusFilter, typeFilter], () => {
  fetchUsers(1)
})

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout>
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchUsers(1)
  }, 300)
})

const viewUserDetails = (user: ChatUser) => {
  // TODO: Navigate to user detail page or open modal
  console.log('View details:', user.id)
}

const viewUserConversations = (user: ChatUser) => {
  if (workspaceId.value) {
    navigateTo(`/dashboard/${workspaceId.value}/conversations?user=${user.id}`)
  }
}

// Ban functionality
const openBanModal = (user: ChatUser) => {
  userToBan.value = user
  banReason.value = ''
  banExpiresAt.value = ''
  showBanModal.value = true
}

const closeBanModal = () => {
  showBanModal.value = false
  userToBan.value = null
  banReason.value = ''
  banExpiresAt.value = ''
}

const confirmBan = async () => {
  if (!userToBan.value || !workspaceId.value) return

  banning.value = true
  try {
    await $fetch(
      `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId.value}/users/${userToBan.value.id}/ban`,
      {
        method: 'POST',
        headers: { Authorization: `Bearer ${token.value}` },
        body: {
          reason: banReason.value || undefined,
          expires_at: banExpiresAt.value || undefined
        }
      }
    )

    toast.add({
      title: 'User Banned',
      description: `${userToBan.value.name} has been banned successfully.`,
      color: 'green'
    })

    closeBanModal()
    await fetchUsers(pagination.value.currentPage)
  } catch (e: any) {
    toast.add({
      title: 'Error',
      description: e.data?.message || 'Failed to ban user',
      color: 'red'
    })
    console.error('Error banning user:', e)
  } finally {
    banning.value = false
  }
}

// Unban functionality
const openUnbanModal = (user: ChatUser) => {
  userToUnban.value = user
  showUnbanModal.value = true
}

const closeUnbanModal = () => {
  showUnbanModal.value = false
  userToUnban.value = null
}

const confirmUnban = async () => {
  if (!userToUnban.value || !workspaceId.value) return

  unbanning.value = true
  try {
    await $fetch(
      `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId.value}/users/${userToUnban.value.id}/ban`,
      {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token.value}` }
      }
    )

    toast.add({
      title: 'User Unbanned',
      description: `${userToUnban.value.name} has been unbanned successfully.`,
      color: 'green'
    })

    closeUnbanModal()
    await fetchUsers(pagination.value.currentPage)
  } catch (e: any) {
    toast.add({
      title: 'Error',
      description: e.data?.message || 'Failed to unban user',
      color: 'red'
    })
    console.error('Error unbanning user:', e)
  } finally {
    unbanning.value = false
  }
}

const exportUsers = async () => {
  if (!workspaceId.value) return

  try {
    // Build CSV content
    const headers = ['Name', 'Email', 'Status', 'Last Seen', 'Conversations', 'Anonymous', 'Banned']
    const rows = users.value.map(user => [
      user.name,
      user.email || '',
      getStatus(user.last_seen_at),
      user.last_seen_at || 'Never',
      user.conversations_count || 0,
      user.is_anonymous ? 'Yes' : 'No',
      user.ban ? 'Yes' : 'No'
    ])

    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n')

    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `users-${new Date().toISOString().split('T')[0]}.csv`
    a.click()
    window.URL.revokeObjectURL(url)

    toast.add({
      title: 'Export Complete',
      description: 'Users exported successfully.',
      color: 'green'
    })
  } catch (e: any) {
    toast.add({
      title: 'Error',
      description: 'Failed to export users',
      color: 'red'
    })
    console.error('Error exporting users:', e)
  }
}

// Fetch users on mount
onMounted(() => {
  fetchUsers()
})
</script>
