<template>
  <div class="flex items-center gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
    <UAvatar
      :alt="displayName"
      size="md"
      :src="avatarUrl"
    />
    <div class="flex-1 min-w-0">
      <h2 class="font-semibold text-[var(--chat-text-primary)] truncate">
        {{ displayName }}
      </h2>
      <p class="text-sm text-[var(--chat-text-secondary)]">
        {{ participantCount }} participant{{ participantCount !== 1 ? 's' : '' }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Conversation } from '~/types'

const props = defineProps<{
  conversation: Conversation
}>()

const displayName = computed(() => {
  if (props.conversation.name) return props.conversation.name
  if (props.conversation.type === 'direct') {
    return props.conversation.participants[0]?.chatUser?.name || 'Unknown'
  }
  return 'Group Chat'
})

const avatarUrl = computed(() => {
  if (props.conversation.type === 'direct') {
    return props.conversation.participants[0]?.chatUser?.avatar_url
  }
  return undefined
})

const participantCount = computed(() => props.conversation.participants?.length || 0)
</script>
