<template>
  <div ref="listRef" class="flex-1 overflow-y-auto p-4 space-y-4">
    <div v-if="messages.length === 0" class="flex items-center justify-center h-full text-[var(--chat-text-secondary)]">
      No messages yet
    </div>
    <MessageBubble
      v-for="message in messages"
      :key="message.id"
      :message="message"
      :is-own="message.sender?.id === currentUserId"
    />
    <TypingIndicator v-if="typingUsers.length > 0" :users="typingUsers" />
  </div>
</template>

<script setup lang="ts">
import type { Message, ChatUser } from '~/types'

const props = defineProps<{
  messages: Message[]
  currentUserId: string
  typingUsers?: ChatUser[]
}>()

const listRef = ref<HTMLElement>()

// Auto-scroll to bottom on new messages
watch(() => props.messages.length, () => {
  nextTick(() => {
    if (listRef.value) {
      listRef.value.scrollTop = listRef.value.scrollHeight
    }
  })
})
</script>
