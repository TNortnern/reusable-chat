<template>
  <div>
    <!-- File Preview -->
    <div v-if="pendingFiles.length > 0" class="cw-file-preview">
      <div v-for="(file, index) in pendingFiles" :key="index" class="cw-preview-item">
        <img
          v-if="file.type.startsWith('image/')"
          :src="file.preview"
          class="cw-preview-thumb"
          :alt="file.name"
        />
        <div v-else class="cw-preview-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
          </svg>
        </div>
        <span class="cw-preview-name">{{ file.name }}</span>
        <button class="cw-preview-remove" @click="removeFile(index)">&times;</button>
      </div>
    </div>

    <!-- Input Area -->
    <div class="cw-input-area">
      <!-- Emoji Picker -->
      <div class="cw-emoji-container">
        <button class="cw-icon-btn" @click="toggleEmojiPicker" title="Add emoji">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
            <line x1="9" y1="9" x2="9.01" y2="9"/>
            <line x1="15" y1="9" x2="15.01" y2="9"/>
          </svg>
        </button>
        <div v-if="showEmojiPicker" class="cw-emoji-picker">
          <div class="cw-emoji-grid">
            <button
              v-for="emoji in commonEmojis"
              :key="emoji"
              class="cw-emoji-btn"
              @click="insertEmoji(emoji)"
            >
              {{ emoji }}
            </button>
          </div>
        </div>
      </div>

      <!-- File Upload -->
      <button class="cw-icon-btn" @click="triggerFileUpload" title="Attach file">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
        </svg>
      </button>
      <input
        ref="fileInputRef"
        type="file"
        multiple
        accept="image/*,.pdf,.doc,.docx,.txt"
        class="cw-hidden-input"
        @change="handleFileSelect"
      />

      <!-- Text Input -->
      <input
        ref="inputRef"
        v-model="message"
        type="text"
        :placeholder="placeholder"
        class="cw-input"
        @keyup.enter="handleSend"
        @input="handleTyping"
        @focus="showEmojiPicker = false"
      />

      <!-- Send Button -->
      <button
        class="cw-send-btn"
        :disabled="!canSend || sending"
        @click="handleSend"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onBeforeUnmount } from 'vue'
import type { PendingFile } from '../types'

const props = withDefaults(
  defineProps<{
    placeholder?: string
    sending?: boolean
  }>(),
  {
    placeholder: 'Type a message...',
    sending: false,
  }
)

const emit = defineEmits<{
  send: [content: string, files: PendingFile[]]
  typing: [isTyping: boolean]
}>()

const message = ref('')
const pendingFiles = ref<PendingFile[]>([])
const showEmojiPicker = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)
const inputRef = ref<HTMLInputElement | null>(null)

let typingTimeout: ReturnType<typeof setTimeout>

const commonEmojis = [
  '&#x1F600;', '&#x1F602;', '&#x1F60D;', '&#x1F618;', '&#x1F914;', '&#x1F605;',
  '&#x1F622;', '&#x1F624;', '&#x1F60E;', '&#x1F929;', '&#x1F607;', '&#x1F644;',
  '&#x1F44D;', '&#x1F44E;', '&#x2764;', '&#x1F525;', '&#x1F4AF;', '&#x2728;',
  '&#x1F389;', '&#x1F440;', '&#x1F64F;', '&#x1F4AA;', '&#x1F91D;', '&#x1F44F;'
].map(code => {
  const match = code.match(/&#x([0-9A-Fa-f]+);/)
  return match ? String.fromCodePoint(parseInt(match[1], 16)) : code
})

const canSend = computed(() => {
  return message.value.trim().length > 0 || pendingFiles.value.length > 0
})

function toggleEmojiPicker() {
  showEmojiPicker.value = !showEmojiPicker.value
}

function insertEmoji(emoji: string) {
  message.value += emoji
  showEmojiPicker.value = false
  inputRef.value?.focus()
}

function triggerFileUpload() {
  fileInputRef.value?.click()
}

function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement
  if (!input.files) return

  Array.from(input.files).forEach((file) => {
    let preview = ''
    if (file.type.startsWith('image/')) {
      preview = URL.createObjectURL(file)
    }

    pendingFiles.value.push({
      file,
      name: file.name,
      type: file.type,
      preview,
    })
  })

  input.value = ''
}

function removeFile(index: number) {
  const file = pendingFiles.value[index]
  if (file.preview) {
    URL.revokeObjectURL(file.preview)
  }
  pendingFiles.value.splice(index, 1)
}

function handleTyping() {
  clearTimeout(typingTimeout)
  emit('typing', true)

  typingTimeout = setTimeout(() => {
    emit('typing', false)
  }, 2000)
}

function handleSend() {
  if (!canSend.value || props.sending) return

  clearTimeout(typingTimeout)
  emit('typing', false)

  emit('send', message.value.trim(), [...pendingFiles.value])

  message.value = ''
  pendingFiles.value.forEach((f) => {
    if (f.preview) URL.revokeObjectURL(f.preview)
  })
  pendingFiles.value = []
  showEmojiPicker.value = false
}

onBeforeUnmount(() => {
  clearTimeout(typingTimeout)
  pendingFiles.value.forEach((f) => {
    if (f.preview) URL.revokeObjectURL(f.preview)
  })
})

defineExpose({ focus: () => inputRef.value?.focus() })
</script>
