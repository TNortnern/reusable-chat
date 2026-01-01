<template>
  <div class="flex items-center gap-2 text-sm text-[var(--chat-text-secondary)]">
    <div class="flex gap-1">
      <span class="w-2 h-2 bg-[var(--chat-text-secondary)] rounded-full animate-bounce" style="animation-delay: 0ms"></span>
      <span class="w-2 h-2 bg-[var(--chat-text-secondary)] rounded-full animate-bounce" style="animation-delay: 150ms"></span>
      <span class="w-2 h-2 bg-[var(--chat-text-secondary)] rounded-full animate-bounce" style="animation-delay: 300ms"></span>
    </div>
    <span>{{ displayText }}</span>
  </div>
</template>

<script setup lang="ts">
import type { ChatUser } from '~/types'

const props = defineProps<{
  users: ChatUser[]
}>()

const displayText = computed(() => {
  if (props.users.length === 1) {
    return `${props.users[0].name} is typing...`
  }
  if (props.users.length === 2) {
    return `${props.users[0].name} and ${props.users[1].name} are typing...`
  }
  return 'Several people are typing...'
})
</script>
