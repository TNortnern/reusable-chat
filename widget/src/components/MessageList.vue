<template>
  <div ref="containerRef" class="cw-messages" @scroll="handleScroll">
    <!-- Welcome Message -->
    <div v-if="welcomeMessage && messages.length === 0" class="cw-welcome">
      {{ welcomeMessage }}
    </div>

    <!-- Empty State -->
    <div v-if="messages.length === 0 && !welcomeMessage" class="cw-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
      <p>No messages yet.<br>Start the conversation!</p>
    </div>

    <!-- Messages -->
    <div
      v-for="msg in messages"
      :key="msg.id"
      class="cw-message"
      :class="{ own: msg.is_own }"
    >
      <div class="cw-avatar">
        {{ getInitial(msg.sender_name) }}
      </div>
      <div class="cw-bubble">
        <div class="cw-msg-header">
          <span class="cw-sender">{{ msg.sender_name }}</span>
          <span class="cw-time">{{ formatTime(msg.created_at) }}</span>
        </div>
        <div class="cw-msg-text">{{ msg.content }}</div>

        <!-- Attachments -->
        <div v-if="msg.attachments && msg.attachments.length > 0" class="cw-attachments">
          <template v-for="attachment in msg.attachments" :key="attachment.id">
            <img
              v-if="isImage(attachment.type)"
              :src="attachment.url"
              :alt="attachment.name"
              class="cw-attach-img"
              @click="openImage(attachment.url)"
            />
            <a v-else :href="attachment.url" target="_blank" class="cw-attach-file">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
              {{ attachment.name }}
            </a>
          </template>
        </div>
      </div>
    </div>

    <!-- Typing Indicator -->
    <div v-if="typingUsers.length > 0" class="cw-typing">
      {{ typingUsers.join(', ') }} {{ typingUsers.length === 1 ? 'is' : 'are' }} typing...
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted } from 'vue'
import type { Message } from '../types'

const props = defineProps<{
  messages: Message[]
  typingUsers: string[]
  welcomeMessage?: string
}>()

const containerRef = ref<HTMLElement | null>(null)
const isAtBottom = ref(true)

function getInitial(name: string): string {
  return name?.charAt(0).toUpperCase() || '?'
}

function formatTime(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function isImage(type: string): boolean {
  return type?.startsWith('image/')
}

function openImage(url: string): void {
  window.open(url, '_blank')
}

function handleScroll(): void {
  if (!containerRef.value) return
  const { scrollTop, scrollHeight, clientHeight } = containerRef.value
  isAtBottom.value = scrollHeight - scrollTop - clientHeight < 50
}

function scrollToBottom(): void {
  nextTick(() => {
    if (containerRef.value && isAtBottom.value) {
      containerRef.value.scrollTop = containerRef.value.scrollHeight
    }
  })
}

watch(
  () => props.messages.length,
  () => scrollToBottom()
)

watch(
  () => props.typingUsers.length,
  () => scrollToBottom()
)

onMounted(() => {
  scrollToBottom()
})

defineExpose({ scrollToBottom })
</script>
