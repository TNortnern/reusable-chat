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
        <!-- Support Chat Header -->
        <div v-if="chatType === 'support'" class="widget-header">
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

        <!-- User-to-User Chat Header -->
        <div v-else-if="chatType === 'user-to-user'" class="widget-header">
          <div class="flex items-center gap-3">
            <div class="user-avatar user-avatar-dm">
              <span>S</span>
              <span class="online-indicator"></span>
            </div>
            <div>
              <h3 class="widget-title">Sarah Johnson</h3>
              <p class="widget-subtitle">Online now</p>
            </div>
          </div>
        </div>

        <!-- Group Chat Header -->
        <div v-else-if="chatType === 'group'" class="widget-header">
          <div class="flex items-center gap-3">
            <div class="group-avatars">
              <div class="group-avatar ga-1">M</div>
              <div class="group-avatar ga-2">J</div>
              <div class="group-avatar ga-3">A</div>
            </div>
            <div>
              <h3 class="widget-title">Project Team</h3>
              <p class="widget-subtitle">5 members, 3 online</p>
            </div>
          </div>
        </div>

        <!-- Support Chat Messages -->
        <div v-if="chatType === 'support'" class="widget-messages">
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

        <!-- User-to-User Chat Messages -->
        <div v-else-if="chatType === 'user-to-user'" class="widget-messages">
          <div class="message message-received with-avatar">
            <div class="message-user-avatar">S</div>
            <div class="message-content-wrapper">
              <div class="message-bubble">
                Hey! Are you still interested in the apartment?
              </div>
              <span class="message-time">2:30 PM</span>
            </div>
          </div>
          <div class="message message-sent">
            <div class="message-content-wrapper">
              <div class="message-bubble">
                Yes! I'd love to schedule a viewing
              </div>
              <span class="message-time">2:32 PM</span>
            </div>
          </div>
          <div class="message message-received with-avatar">
            <div class="message-user-avatar">S</div>
            <div class="message-content-wrapper">
              <div class="message-bubble">
                How about tomorrow at 3 PM?
              </div>
              <span class="message-time">2:33 PM</span>
            </div>
          </div>
        </div>

        <!-- Group Chat Messages -->
        <div v-else-if="chatType === 'group'" class="widget-messages">
          <div class="message message-received with-avatar">
            <div class="message-user-avatar bg-blue-500">M</div>
            <div class="message-content-wrapper">
              <span class="message-sender">Mike Chen</span>
              <div class="message-bubble">
                Hey team, I've updated the design files
              </div>
            </div>
          </div>
          <div class="message message-received with-avatar">
            <div class="message-user-avatar bg-purple-500">J</div>
            <div class="message-content-wrapper">
              <span class="message-sender">Julia Park</span>
              <div class="message-bubble">
                Awesome! I'll review them today
              </div>
            </div>
          </div>
          <div class="message message-sent">
            <div class="message-content-wrapper">
              <div class="message-bubble">
                Thanks Mike! The new layouts look great
              </div>
            </div>
          </div>
          <div class="message message-received with-avatar">
            <div class="message-user-avatar bg-green-500">A</div>
            <div class="message-content-wrapper">
              <span class="message-sender">Alex Rivera</span>
              <div class="message-bubble">
                Can we discuss the timeline in our standup?
              </div>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="widget-input">
          <input
            type="text"
            :placeholder="inputPlaceholder"
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
type ChatType = 'support' | 'user-to-user' | 'group'

interface WidgetSettings {
  primaryColor: string
  backgroundColor: string
  position: 'bottom-right' | 'bottom-left'
  darkMode: boolean
  logoUrl: string
  fontFamily: string
}

const props = withDefaults(defineProps<{
  settings: WidgetSettings
  chatType?: ChatType
}>(), {
  chatType: 'support'
})

const isOpen = ref(true) // Start open by default for preview

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

const inputPlaceholder = computed(() => {
  switch (props.chatType) {
    case 'support':
      return 'Type your message...'
    case 'user-to-user':
      return 'Message Sarah...'
    case 'group':
      return 'Message Project Team...'
    default:
      return 'Type your message...'
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

/* User Avatar for DM */
.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
  position: relative;
}

.user-avatar-dm {
  background: rgba(255, 255, 255, 0.25);
  color: var(--chat-text-inverse);
}

.online-indicator {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  background: #22c55e;
  border: 2px solid var(--widget-primary, var(--chat-accent));
  border-radius: 50%;
}

/* Group Avatars */
.group-avatars {
  position: relative;
  width: 50px;
  height: 40px;
}

.group-avatar {
  position: absolute;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 11px;
  color: white;
  border: 2px solid var(--widget-primary, var(--chat-accent));
}

.group-avatar.ga-1 {
  background: #3b82f6;
  left: 0;
  top: 0;
  z-index: 3;
}

.group-avatar.ga-2 {
  background: #a855f7;
  left: 14px;
  top: 6px;
  z-index: 2;
}

.group-avatar.ga-3 {
  background: #22c55e;
  left: 0;
  top: 12px;
  z-index: 1;
}

/* Message with Avatar */
.message.with-avatar {
  flex-direction: row;
  gap: 8px;
}

.message-user-avatar {
  width: 32px;
  height: 32px;
  min-width: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 12px;
  color: white;
  background: var(--widget-primary, #6b7280);
}

.message-content-wrapper {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.message-sender {
  font-size: 12px;
  font-weight: 600;
  color: var(--chat-text-secondary);
}

.message-time {
  font-size: 11px;
  color: var(--chat-text-secondary);
  opacity: 0.7;
}

.message-sent .message-time {
  align-self: flex-end;
}

/* Adjust bubble for messages with avatar */
.message.with-avatar .message-bubble {
  max-width: 100%;
}

/* Dark mode adjustments */
[data-theme="dark"] .widget-messages {
  background: var(--chat-bg-primary);
}

[data-theme="dark"] .input-field {
  background: var(--chat-bg-secondary);
  border-color: var(--chat-bg-tertiary);
}

[data-theme="dark"] .message-sender {
  color: var(--chat-text-secondary);
}
</style>
