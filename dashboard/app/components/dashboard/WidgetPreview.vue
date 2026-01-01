<template>
  <div
    class="widget-preview-container"
    :data-theme="settings.darkMode ? 'dark' : 'light'"
    :style="customStyles"
  >
    <!-- Widget Button -->
    <div
      class="widget-button"
      :class="positionClass"
    >
      <button
        class="widget-trigger"
        @click="isOpen = !isOpen"
      >
        <UIcon v-if="!isOpen" name="i-heroicons-chat-bubble-left-right" class="w-6 h-6" />
        <UIcon v-else name="i-heroicons-x-mark" class="w-6 h-6" />
      </button>
    </div>

    <!-- Chat Widget -->
    <Transition name="widget-slide">
      <div
        v-if="isOpen"
        class="chat-widget"
        :class="positionClass"
      >
        <!-- Header -->
        <div class="widget-header">
          <div class="flex items-center gap-3">
            <div v-if="settings.logoUrl" class="widget-logo">
              <img :src="settings.logoUrl" alt="Logo" />
            </div>
            <div v-else class="widget-logo-placeholder">
              <UIcon name="i-heroicons-chat-bubble-left-right" class="w-5 h-5" />
            </div>
            <div>
              <h3 class="widget-title">Chat with us</h3>
              <p class="widget-subtitle">We typically reply in a few minutes</p>
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div class="widget-messages">
          <div class="message message-received">
            <div class="message-bubble">
              Hi there! How can we help you today?
            </div>
          </div>
          <div class="message message-sent">
            <div class="message-bubble">
              I have a question about your product
            </div>
          </div>
          <div class="message message-received">
            <div class="message-bubble">
              Of course! I'd be happy to help. What would you like to know?
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="widget-input">
          <input
            type="text"
            placeholder="Type your message..."
            class="input-field"
          />
          <button class="send-button">
            <UIcon name="i-heroicons-paper-airplane" class="w-5 h-5" />
          </button>
        </div>

        <!-- Branding -->
        <div class="widget-branding">
          Powered by ChatPlatform
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
interface WidgetSettings {
  primaryColor: string
  backgroundColor: string
  position: 'bottom-right' | 'bottom-left'
  darkMode: boolean
  logoUrl: string
  fontFamily: string
}

const props = defineProps<{
  settings: WidgetSettings
}>()

const isOpen = ref(false)

const positionClass = computed(() => {
  return props.settings.position === 'bottom-left' ? 'position-left' : 'position-right'
})

const customStyles = computed(() => {
  return {
    '--widget-primary': props.settings.primaryColor,
    '--widget-bg': props.settings.backgroundColor,
    '--widget-font': props.settings.fontFamily || 'var(--chat-font-body)',
  }
})
</script>

<style scoped>
.widget-preview-container {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 600px;
  background: var(--chat-bg-primary);
  font-family: var(--widget-font);
  transition: background-color 0.3s ease;
}

/* Widget Button */
.widget-button {
  position: absolute;
  bottom: 24px;
  z-index: 1000;
}

.widget-button.position-right {
  right: 24px;
}

.widget-button.position-left {
  left: 24px;
}

.widget-trigger {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--widget-primary, var(--chat-accent));
  color: var(--chat-text-inverse);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--chat-shadow-float);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.widget-trigger:hover {
  transform: scale(1.05);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

/* Chat Widget */
.chat-widget {
  position: absolute;
  bottom: 100px;
  width: 380px;
  height: 600px;
  background: var(--widget-bg, var(--chat-bg-secondary));
  border-radius: var(--chat-radius-lg);
  box-shadow: var(--chat-shadow-float);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 999;
}

.chat-widget.position-right {
  right: 24px;
}

.chat-widget.position-left {
  left: 24px;
}

/* Header */
.widget-header {
  padding: 20px;
  background: var(--widget-primary, var(--chat-accent));
  color: var(--chat-text-inverse);
}

.widget-logo {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.widget-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.widget-logo-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.widget-title {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.widget-subtitle {
  font-size: 13px;
  opacity: 0.9;
  margin: 2px 0 0;
}

/* Messages */
.widget-messages {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.message {
  display: flex;
  flex-direction: column;
}

.message-received {
  align-items: flex-start;
}

.message-sent {
  align-items: flex-end;
}

.message-bubble {
  max-width: 75%;
  padding: 10px 14px;
  border-radius: var(--chat-radius-bubble);
  font-size: 14px;
  line-height: 1.5;
}

.message-received .message-bubble {
  background: var(--chat-bubble-received);
  color: var(--chat-text-primary);
  border-bottom-left-radius: 4px;
}

.message-sent .message-bubble {
  background: var(--widget-primary, var(--chat-bubble-sent));
  color: var(--chat-text-inverse);
  border-bottom-right-radius: 4px;
}

/* Input */
.widget-input {
  padding: 16px 20px;
  border-top: 1px solid var(--chat-bg-tertiary);
  display: flex;
  gap: 8px;
  align-items: center;
}

.input-field {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid var(--chat-bg-tertiary);
  border-radius: var(--chat-radius-sm);
  font-size: 14px;
  background: var(--chat-bg-primary);
  color: var(--chat-text-primary);
  outline: none;
  font-family: inherit;
}

.input-field:focus {
  border-color: var(--widget-primary, var(--chat-accent));
}

.send-button {
  width: 40px;
  height: 40px;
  border-radius: var(--chat-radius-sm);
  background: var(--widget-primary, var(--chat-accent));
  color: var(--chat-text-inverse);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease;
}

.send-button:hover {
  opacity: 0.9;
}

/* Branding */
.widget-branding {
  padding: 8px 20px;
  text-align: center;
  font-size: 11px;
  color: var(--chat-text-secondary);
  border-top: 1px solid var(--chat-bg-tertiary);
}

/* Transitions */
.widget-slide-enter-active,
.widget-slide-leave-active {
  transition: all 0.3s ease;
}

.widget-slide-enter-from,
.widget-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Dark mode adjustments */
[data-theme="dark"] .widget-messages {
  background: var(--chat-bg-primary);
}

[data-theme="dark"] .input-field {
  background: var(--chat-bg-secondary);
  border-color: var(--chat-bg-tertiary);
}
</style>
