<template>
  <button
    class="cw-button"
    :class="position"
    @click="$emit('click')"
    :aria-label="isOpen ? 'Close chat' : 'Open chat'"
  >
    <svg v-if="!isOpen" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="18" y1="6" x2="6" y2="18"/>
      <line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
    <span v-if="unreadCount > 0 && !isOpen" class="cw-badge">
      {{ unreadCount > 99 ? '99+' : unreadCount }}
    </span>
  </button>
</template>

<script setup lang="ts">
defineProps<{
  position: 'bottom-right' | 'bottom-left'
  isOpen: boolean
  unreadCount?: number
}>()

defineEmits<{
  click: []
}>()
</script>
