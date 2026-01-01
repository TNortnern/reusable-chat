<template>
  <aside class="w-64 bg-[var(--chat-bg-secondary)] border-r border-gray-200 dark:border-gray-800 flex flex-col">
    <div class="p-4 border-b border-gray-200 dark:border-gray-800">
      <h1 class="text-xl font-bold text-[var(--chat-text-primary)]">Chat Admin</h1>
    </div>

    <nav class="flex-1 p-4 space-y-1">
      <NuxtLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        class="flex items-center gap-3 px-3 py-2 rounded-lg text-[var(--chat-text-secondary)] hover:bg-[var(--chat-bg-tertiary)] hover:text-[var(--chat-text-primary)] transition-colors"
        active-class="bg-[var(--chat-accent-soft)] text-[var(--chat-accent)]"
      >
        <UIcon :name="item.icon" class="w-5 h-5" />
        <span>{{ item.label }}</span>
      </NuxtLink>
    </nav>

    <div class="p-4 border-t border-gray-200 dark:border-gray-800">
      <div class="flex items-center gap-3">
        <UAvatar :alt="currentWorkspace?.name" size="sm" />
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-[var(--chat-text-primary)] truncate">
            {{ currentWorkspace?.name }}
          </p>
        </div>
        <UButton icon="i-heroicons-arrow-right-on-rectangle" variant="ghost" size="sm" @click="logout" />
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
const { currentWorkspace, logout } = useAuth()

const navItems = [
  { to: '/dashboard', icon: 'i-heroicons-home', label: 'Overview' },
  { to: '/dashboard/conversations', icon: 'i-heroicons-chat-bubble-left-right', label: 'Conversations' },
  { to: '/dashboard/users', icon: 'i-heroicons-users', label: 'Users' },
  { to: '/dashboard/analytics', icon: 'i-heroicons-chart-bar', label: 'Analytics' },
  { to: '/dashboard/settings', icon: 'i-heroicons-cog-6-tooth', label: 'Settings' },
  { to: '/dashboard/theme', icon: 'i-heroicons-swatch', label: 'Theme' },
]
</script>
