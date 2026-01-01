<template>
  <div class="p-4 border-t border-gray-200 dark:border-gray-700">
    <form @submit.prevent="handleSend" class="flex items-end gap-2">
      <UTextarea
        v-model="content"
        placeholder="Type a message..."
        :rows="1"
        autoresize
        :maxrows="4"
        :disabled="disabled"
        class="flex-1"
        @keydown.enter.exact.prevent="handleSend"
      />
      <UButton
        type="submit"
        icon="i-heroicons-paper-airplane"
        :disabled="disabled || !content.trim()"
        size="lg"
      />
    </form>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  disabled?: boolean
}>()

const emit = defineEmits<{
  send: [content: string]
}>()

const content = ref('')

const handleSend = () => {
  if (!content.value.trim() || props.disabled) return
  emit('send', content.value.trim())
  content.value = ''
}
</script>
