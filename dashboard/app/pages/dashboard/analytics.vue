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
        />
        <UButton variant="soft" @click="refreshData">
          <UIcon name="i-heroicons-arrow-path" />
          Refresh
        </UButton>
      </div>
    </UCard>

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
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'dashboard'
})

const dateRange = ref('7d')
const dateRangeOptions = [
  { label: 'Last 7 days', value: '7d' },
  { label: 'Last 30 days', value: '30d' },
  { label: 'Last 90 days', value: '90d' },
  { label: 'This year', value: 'year' }
]

const keyMetrics = ref([
  {
    label: 'Total Messages',
    value: '24,589',
    icon: 'i-heroicons-chat-bubble-left-right',
    iconColor: 'text-blue-500',
    change: '+12.5% vs last period',
    changeColor: 'text-green-600',
    changeIcon: 'i-heroicons-arrow-up'
  },
  {
    label: 'Active Conversations',
    value: '1,247',
    icon: 'i-heroicons-users',
    iconColor: 'text-purple-500',
    change: '+8.2% vs last period',
    changeColor: 'text-green-600',
    changeIcon: 'i-heroicons-arrow-up'
  },
  {
    label: 'Avg Response Time',
    value: '2.3m',
    icon: 'i-heroicons-clock',
    iconColor: 'text-green-500',
    change: '-15% vs last period',
    changeColor: 'text-green-600',
    changeIcon: 'i-heroicons-arrow-down'
  },
  {
    label: 'Resolution Rate',
    value: '94.2%',
    icon: 'i-heroicons-check-circle',
    iconColor: 'text-orange-500',
    change: '+2.1% vs last period',
    changeColor: 'text-green-600',
    changeIcon: 'i-heroicons-arrow-up'
  }
])

const chartData = ref({
  totalMessages: '24,589',
  avgPerDay: '3,512',
  peakHour: '2-3 PM'
})

const userData = ref({
  totalUsers: '8,234',
  newUsers: '+234',
  retention: '78%'
})

const responseTimeData = ref([
  { label: 'Under 1 minute', value: '45%', percentage: 45, barColor: 'bg-green-500' },
  { label: '1-5 minutes', value: '32%', percentage: 32, barColor: 'bg-blue-500' },
  { label: '5-15 minutes', value: '15%', percentage: 15, barColor: 'bg-yellow-500' },
  { label: 'Over 15 minutes', value: '8%', percentage: 8, barColor: 'bg-red-500' }
])

const conversationTypes = ref([
  { label: 'Customer Support', count: 654, percentage: 52, dotColor: 'bg-blue-500' },
  { label: 'Sales Inquiries', count: 312, percentage: 25, dotColor: 'bg-green-500' },
  { label: 'Product Feedback', count: 187, percentage: 15, dotColor: 'bg-purple-500' },
  { label: 'General', count: 94, percentage: 8, dotColor: 'bg-gray-500' }
])

const conversationColumns = [
  { key: 'name', label: 'Conversation' },
  { key: 'messages', label: 'Messages' },
  { key: 'participants', label: 'Participants' },
  { key: 'lastActivity', label: 'Last Activity' }
]

const topConversations = ref([
  { name: 'Customer Support - John Doe', messages: 156, participants: 2, lastActivity: '5m ago' },
  { name: 'Product Feedback Team', messages: 124, participants: 5, lastActivity: '15m ago' },
  { name: 'Sales - Enterprise Lead', messages: 98, participants: 3, lastActivity: '1h ago' },
  { name: 'Technical Support - Bug Report', messages: 87, participants: 2, lastActivity: '2h ago' },
  { name: 'General Inquiries', messages: 76, participants: 4, lastActivity: '3h ago' }
])

const refreshData = () => {
  console.log('Refreshing analytics data...')
}
</script>
