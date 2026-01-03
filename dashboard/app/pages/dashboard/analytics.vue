<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-[var(--chat-text-primary)]">Analytics</h1>
      <p class="text-[var(--chat-text-secondary)] mt-1">Track your chat performance and engagement</p>
    </div>

    <!-- Date Range Selector -->
    <UCard>
      <div class="flex items-center gap-4">
        <USelect
          v-model="dateRange"
          :options="dateRangeOptions"
          size="lg"
          class="w-48"
          :disabled="loading"
        />
        <UButton variant="soft" @click="refreshData" :loading="loading">
          <UIcon name="i-heroicons-arrow-path" />
          Refresh
        </UButton>
      </div>
    </UCard>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-[var(--chat-text-secondary)]" />
    </div>

    <!-- Error State -->
    <UAlert
      v-else-if="error"
      color="red"
      icon="i-heroicons-exclamation-circle"
      title="Error loading analytics"
      :description="error"
    >
      <template #actions>
        <UButton variant="soft" color="red" @click="refreshData">
          Retry
        </UButton>
      </template>
    </UAlert>

    <template v-else>
      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <UCard v-for="metric in keyMetrics" :key="metric.label">
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-sm text-[var(--chat-text-secondary)]">{{ metric.label }}</span>
              <UIcon :name="metric.icon" :class="metric.iconColor" />
            </div>
            <div class="text-3xl font-bold text-[var(--chat-text-primary)]">{{ metric.value }}</div>
            <div class="flex items-center gap-1 text-xs" :class="metric.changeColor">
              <UIcon :name="metric.changeIcon" class="w-3 h-3" />
              {{ metric.change }}
            </div>
          </div>
        </UCard>
      </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Messages Over Time -->
      <UCard>
        <template #header>
          <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Messages Over Time</h2>
        </template>
        <div class="h-64 flex items-center justify-center bg-[var(--chat-bg-tertiary)] rounded-lg">
          <div class="text-center text-[var(--chat-text-secondary)]">
            <UIcon name="i-heroicons-chart-bar" class="w-12 h-12 mx-auto mb-2" />
            <p>Chart placeholder</p>
            <p class="text-xs">Integration with chart library pending</p>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
          <div>
            <div class="text-lg font-bold text-[var(--chat-text-primary)]">{{ chartData.totalMessages }}</div>
            <div class="text-xs text-[var(--chat-text-secondary)]">Total Messages</div>
          </div>
          <div>
            <div class="text-lg font-bold text-[var(--chat-text-primary)]">{{ chartData.avgPerDay }}</div>
            <div class="text-xs text-[var(--chat-text-secondary)]">Avg/Day</div>
          </div>
          <div>
            <div class="text-lg font-bold text-[var(--chat-text-primary)]">{{ chartData.peakHour }}</div>
            <div class="text-xs text-[var(--chat-text-secondary)]">Peak Hour</div>
          </div>
        </div>
      </UCard>

      <!-- Active Users -->
      <UCard>
        <template #header>
          <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Active Users</h2>
        </template>
        <div class="h-64 flex items-center justify-center bg-[var(--chat-bg-tertiary)] rounded-lg">
          <div class="text-center text-[var(--chat-text-secondary)]">
            <UIcon name="i-heroicons-users" class="w-12 h-12 mx-auto mb-2" />
            <p>Chart placeholder</p>
            <p class="text-xs">Integration with chart library pending</p>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
          <div>
            <div class="text-lg font-bold text-[var(--chat-text-primary)]">{{ userData.totalUsers }}</div>
            <div class="text-xs text-[var(--chat-text-secondary)]">Total Users</div>
          </div>
          <div>
            <div class="text-lg font-bold text-[var(--chat-text-primary)]">{{ userData.newUsers }}</div>
            <div class="text-xs text-[var(--chat-text-secondary)]">New This Week</div>
          </div>
          <div>
            <div class="text-lg font-bold text-[var(--chat-text-primary)]">{{ userData.retention }}</div>
            <div class="text-xs text-[var(--chat-text-secondary)]">Retention Rate</div>
          </div>
        </div>
      </UCard>
    </div>

    <!-- Response Times & Conversation Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Response Times -->
      <UCard>
        <template #header>
          <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Response Times</h2>
        </template>
        <div class="space-y-4">
          <div v-for="item in responseTimeData" :key="item.label" class="flex items-center justify-between">
            <span class="text-[var(--chat-text-secondary)]">{{ item.label }}</span>
            <div class="flex items-center gap-2">
              <div class="w-32 h-2 bg-[var(--chat-bg-tertiary)] rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full"
                  :class="item.barColor"
                  :style="{ width: item.percentage + '%' }"
                />
              </div>
              <span class="font-medium text-[var(--chat-text-primary)] w-16 text-right">{{ item.value }}</span>
            </div>
          </div>
        </div>
      </UCard>

      <!-- Conversation Types -->
      <UCard>
        <template #header>
          <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Conversation Breakdown</h2>
        </template>
        <div class="space-y-4">
          <div v-for="item in conversationTypes" :key="item.label" class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full" :class="item.dotColor" />
              <span class="text-[var(--chat-text-secondary)]">{{ item.label }}</span>
            </div>
            <div class="flex items-center gap-4">
              <span class="font-medium text-[var(--chat-text-primary)]">{{ item.count }}</span>
              <span class="text-sm text-[var(--chat-text-secondary)] w-12 text-right">{{ item.percentage }}%</span>
            </div>
          </div>
        </div>
      </UCard>
    </div>

    <!-- Top Conversations -->
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Most Active Conversations</h2>
          <UButton variant="ghost" size="sm" to="/dashboard/conversations">
            View All
            <UIcon name="i-heroicons-arrow-right" class="ml-1" />
          </UButton>
        </div>
      </template>
      <UTable :rows="topConversations" :columns="conversationColumns">
        <template #name-data="{ row }">
          <span class="font-medium text-[var(--chat-text-primary)]">{{ row.name }}</span>
        </template>
        <template #messages-data="{ row }">
          <span class="font-medium">{{ row.messages }}</span>
        </template>
        <template #participants-data="{ row }">
          <span>{{ row.participants }}</span>
        </template>
        <template #lastActivity-data="{ row }">
          <span class="text-[var(--chat-text-secondary)]">{{ row.lastActivity }}</span>
        </template>
      </UTable>
    </UCard>
    </template>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'dashboard'
})

const config = useRuntimeConfig()
const toast = useToast()
const { token, currentWorkspace } = useAuth()

// State
const loading = ref(true)
const error = ref<string | null>(null)
const dateRange = ref('7d')

const dateRangeOptions = [
  { label: 'Last 7 days', value: '7d' },
  { label: 'Last 30 days', value: '30d' },
  { label: 'Last 90 days', value: '90d' }
]

// Helper to convert date range to days
const getDaysFromRange = (range: string): number => {
  switch (range) {
    case '7d': return 7
    case '30d': return 30
    case '90d': return 90
    default: return 7
  }
}

// Analytics data interfaces
interface OverviewData {
  total_users: number
  total_conversations: number
  total_messages: number
  active_users_today: number
}

interface MessageStats {
  date: string
  count: number
}

interface UserStats {
  date: string
  count: number
}

// Analytics state
const overviewData = ref<OverviewData | null>(null)
const messageStats = ref<MessageStats[]>([])
const userStats = ref<UserStats[]>([])

// Computed key metrics based on real data
const keyMetrics = computed(() => {
  if (!overviewData.value) {
    return []
  }

  const totalMessagesInPeriod = messageStats.value.reduce((sum, stat) => sum + stat.count, 0)
  const newUsersInPeriod = userStats.value.reduce((sum, stat) => sum + stat.count, 0)

  return [
    {
      label: 'Total Messages',
      value: formatNumber(overviewData.value.total_messages),
      icon: 'i-heroicons-chat-bubble-left-right',
      iconColor: 'text-blue-500',
      change: `${formatNumber(totalMessagesInPeriod)} in selected period`,
      changeColor: 'text-[var(--chat-text-secondary)]',
      changeIcon: 'i-heroicons-calendar'
    },
    {
      label: 'Total Conversations',
      value: formatNumber(overviewData.value.total_conversations),
      icon: 'i-heroicons-users',
      iconColor: 'text-purple-500',
      change: `${formatNumber(overviewData.value.active_users_today)} active today`,
      changeColor: 'text-[var(--chat-text-secondary)]',
      changeIcon: 'i-heroicons-user-group'
    },
    {
      label: 'Total Users',
      value: formatNumber(overviewData.value.total_users),
      icon: 'i-heroicons-user',
      iconColor: 'text-green-500',
      change: `+${formatNumber(newUsersInPeriod)} new in period`,
      changeColor: newUsersInPeriod > 0 ? 'text-green-600' : 'text-[var(--chat-text-secondary)]',
      changeIcon: newUsersInPeriod > 0 ? 'i-heroicons-arrow-up' : 'i-heroicons-minus'
    },
    {
      label: 'Active Today',
      value: formatNumber(overviewData.value.active_users_today),
      icon: 'i-heroicons-signal',
      iconColor: 'text-orange-500',
      change: 'Users active today',
      changeColor: 'text-[var(--chat-text-secondary)]',
      changeIcon: 'i-heroicons-clock'
    }
  ]
})

// Computed chart data from message stats
const chartData = computed(() => {
  const totalMessages = messageStats.value.reduce((sum, stat) => sum + stat.count, 0)
  const days = messageStats.value.length || 1
  const avgPerDay = Math.round(totalMessages / days)

  // Find peak day
  let peakDay = 'N/A'
  if (messageStats.value.length > 0) {
    const maxStat = messageStats.value.reduce((max, stat) =>
      stat.count > max.count ? stat : max, messageStats.value[0])
    const date = new Date(maxStat.date)
    peakDay = date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })
  }

  return {
    totalMessages: formatNumber(totalMessages),
    avgPerDay: formatNumber(avgPerDay),
    peakHour: peakDay
  }
})

// Computed user data from user stats
const userData = computed(() => {
  const newUsers = userStats.value.reduce((sum, stat) => sum + stat.count, 0)
  const totalUsers = overviewData.value?.total_users || 0

  // Retention is a placeholder - would need historical data to calculate properly
  const retention = totalUsers > 0 ? Math.min(100, Math.round((overviewData.value?.active_users_today || 0) / totalUsers * 100 * 10)) : 0

  return {
    totalUsers: formatNumber(totalUsers),
    newUsers: `+${formatNumber(newUsers)}`,
    retention: `${retention}%`
  }
})

// Response time data (placeholder - needs backend support)
const responseTimeData = ref([
  { label: 'Under 1 minute', value: 'N/A', percentage: 0, barColor: 'bg-green-500' },
  { label: '1-5 minutes', value: 'N/A', percentage: 0, barColor: 'bg-blue-500' },
  { label: '5-15 minutes', value: 'N/A', percentage: 0, barColor: 'bg-yellow-500' },
  { label: 'Over 15 minutes', value: 'N/A', percentage: 0, barColor: 'bg-red-500' }
])

// Conversation types (placeholder - needs backend support for types/categories)
const conversationTypes = computed(() => {
  const total = overviewData.value?.total_conversations || 0
  return [
    { label: 'All Conversations', count: total, percentage: 100, dotColor: 'bg-blue-500' }
  ]
})

const conversationColumns = [
  { key: 'name', label: 'Conversation' },
  { key: 'messages', label: 'Messages' },
  { key: 'participants', label: 'Participants' },
  { key: 'lastActivity', label: 'Last Activity' }
]

// Top conversations (placeholder - needs backend endpoint)
const topConversations = ref<Array<{ name: string; messages: number; participants: number; lastActivity: string }>>([])

// Helper to format numbers
const formatNumber = (num: number): string => {
  return num.toLocaleString()
}

// Fetch all analytics data
const fetchAnalytics = async () => {
  if (!currentWorkspace.value?.id) {
    error.value = 'No workspace selected'
    loading.value = false
    return
  }

  loading.value = true
  error.value = null

  const days = getDaysFromRange(dateRange.value)
  const workspaceId = currentWorkspace.value.id

  try {
    // Fetch all analytics endpoints in parallel
    const [overview, messages, users] = await Promise.all([
      $fetch<OverviewData>(
        `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId}/analytics`,
        { headers: { Authorization: `Bearer ${token.value}` } }
      ),
      $fetch<MessageStats[]>(
        `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId}/analytics/messages`,
        {
          headers: { Authorization: `Bearer ${token.value}` },
          query: { days }
        }
      ),
      $fetch<UserStats[]>(
        `${config.public.apiUrl}/api/dashboard/workspaces/${workspaceId}/analytics/users`,
        {
          headers: { Authorization: `Bearer ${token.value}` },
          query: { days }
        }
      )
    ])

    overviewData.value = overview
    messageStats.value = messages
    userStats.value = users
  } catch (e: unknown) {
    const fetchError = e as { data?: { message?: string }; message?: string }
    error.value = fetchError.data?.message || fetchError.message || 'Failed to load analytics'
    toast.add({
      title: 'Error',
      description: error.value,
      color: 'red',
      icon: 'i-heroicons-exclamation-circle'
    })
  } finally {
    loading.value = false
  }
}

// Refresh data
const refreshData = () => {
  fetchAnalytics()
}

// Watch for date range changes
watch(dateRange, () => {
  fetchAnalytics()
})

// Watch for workspace changes
watch(() => currentWorkspace.value?.id, (newId) => {
  if (newId) {
    fetchAnalytics()
  }
})

// Fetch data on mount
onMounted(() => {
  if (currentWorkspace.value?.id) {
    fetchAnalytics()
  }
})
</script>
