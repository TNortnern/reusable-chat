<template>
  <UPopover>
    <UButton icon="i-heroicons-face-smile" variant="ghost" size="sm" />
    <template #panel>
      <div class="p-2 grid grid-cols-8 gap-1 max-w-xs">
        <button
          v-for="emoji in commonEmojis"
          :key="emoji"
          class="p-2 hover:bg-[var(--chat-bg-tertiary)] rounded text-xl"
          @click="$emit('select', emoji)"
        >
          {{ emoji }}
        </button>
      </div>
    </template>
  </UPopover>
</template>

<script setup lang="ts">
defineEmits<{
  select: [emoji: string]
}>()

const commonEmojis = ['👍', '❤️', '😂', '😮', '😢', '😡', '🎉', '🔥', '👀', '💯', '✅', '❌', '🙏', '💪', '🤔', '👋']
</script>
