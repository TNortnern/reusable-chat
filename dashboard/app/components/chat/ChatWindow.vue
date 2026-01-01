<template>
  <div class="flex flex-col h-full bg-[var(--chat-bg-secondary)] rounded-lg overflow-hidden">
    <ChatHeader :conversation="conversation" />
    <MessageList :messages="messages" :current-user-id="currentUserId" />
    <MessageInput @send="$emit('send', $event)" :disabled="disabled" />
  </div>
</template>

<script setup lang="ts">
import type { Conversation, Message } from '~/types'

defineProps<{
  conversation: Conversation
  messages: Message[]
  currentUserId: string
  disabled?: boolean
}>()

defineEmits<{
  send: [content: string]
}>()
</script>
