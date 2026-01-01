<template>
  <div v-if="groupedReactions.length > 0" class="flex flex-wrap gap-1">
    <button
      v-for="group in groupedReactions"
      :key="group.emoji"
      class="px-2 py-0.5 rounded-full text-xs bg-[var(--chat-bg-tertiary)] hover:bg-[var(--chat-accent-soft)] transition-colors"
    >
      {{ group.emoji }} {{ group.count }}
    </button>
  </div>
</template>

<script setup lang="ts">
import type { Reaction } from '~/types'

const props = defineProps<{
  reactions: Reaction[]
  messageId: string
}>()

const groupedReactions = computed(() => {
  const groups: Record<string, number> = {}
  props.reactions.forEach(r => {
    groups[r.emoji] = (groups[r.emoji] || 0) + 1
  })
  return Object.entries(groups).map(([emoji, count]) => ({ emoji, count }))
})
</script>
