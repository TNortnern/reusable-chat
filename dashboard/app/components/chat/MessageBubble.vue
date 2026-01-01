<template>
  <div
    class="flex gap-2"
    :class="isOwn ? 'flex-row-reverse' : 'flex-row'"
  >
    <UAvatar
      v-if="!isOwn"
      :alt="message.sender?.name"
      :src="message.sender?.avatar_url"
      size="sm"
      class="flex-shrink-0"
    />

    <div class="max-w-[70%] space-y-1">
      <div
        class="px-4 py-2 rounded-2xl"
        :class="bubbleClass"
      >
        <p class="text-sm whitespace-pre-wrap break-words">{{ message.content }}</p>

        <div v-if="message.attachments?.length" class="mt-2 space-y-2">
          <a
            v-for="attachment in message.attachments"
            :key="attachment.id"
            :href="attachment.url"
            target="_blank"
            class="flex items-center gap-2 text-sm underline"
          >
            <UIcon name="i-heroicons-paper-clip" class="w-4 h-4" />
            {{ attachment.filename }}
          </a>
        </div>
      </div>

      <div
        class="flex items-center gap-2 text-xs text-[var(--chat-text-secondary)]"
        :class="isOwn ? 'justify-end' : 'justify-start'"
      >
        <span>{{ formatTime(message.created_at) }}</span>
        <ReactionBar :reactions="message.reactions || []" :message-id="message.id" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Message } from '~/types'

const props = defineProps<{
  message: Message
  isOwn: boolean
}>()

const bubbleClass = computed(() => {
  if (props.isOwn) {
    return 'bg-[var(--chat-bubble-sent)] text-[var(--chat-text-inverse)]'
  }
  return 'bg-[var(--chat-bubble-received)] text-[var(--chat-text-primary)]'
})

const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>
