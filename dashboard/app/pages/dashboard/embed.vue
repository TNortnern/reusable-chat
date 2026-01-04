<template>
  <div class="embed-page">
    <!-- Page Header -->
    <header class="embed-header">
      <div class="header-content">
        <div class="header-icon">
          <UIcon name="i-heroicons-code-bracket" class="w-6 h-6" />
        </div>
        <div>
          <h1 class="header-title">Embed Widget</h1>
          <p class="header-description">Get your embed code to add the chat widget to your website</p>
        </div>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner">
        <UIcon name="i-heroicons-arrow-path" class="w-10 h-10 animate-spin" />
      </div>
      <p>Loading...</p>
    </div>

    <template v-else>
      <div class="embed-grid">
        <!-- Configuration Panel -->
        <div class="config-panel">
          <!-- API Key Selection -->
          <div class="config-card">
            <div class="card-header">
              <div class="card-header-icon keys">
                <UIcon name="i-heroicons-key" class="w-5 h-5" />
              </div>
              <div class="flex-1">
                <h2 class="card-title">API Key</h2>
                <p class="card-description">Select or create a public API key for your widget</p>
              </div>
            </div>
            <div class="card-body">
              <!-- API Keys List -->
              <div v-if="publicKeys.length > 0" class="api-keys-list">
                <div
                  v-for="key in publicKeys"
                  :key="key.id"
                  class="api-key-item"
                  :class="{ selected: selectedKeyId === key.id }"
                  @click="selectedKeyId = key.id"
                >
                  <div class="key-radio">
                    <div class="radio-outer">
                      <div v-if="selectedKeyId === key.id" class="radio-inner"></div>
                    </div>
                  </div>
                  <div class="key-info">
                    <div class="key-name">{{ key.name }}</div>
                    <div class="key-prefix">{{ key.key_prefix }}...</div>
                  </div>
                  <UButton
                    icon="i-heroicons-clipboard-document"
                    size="xs"
                    variant="ghost"
                    @click.stop="copyKeyPrefix(key)"
                    title="Copy key prefix"
                  />
                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="empty-keys">
                <UIcon name="i-heroicons-key" class="w-8 h-8" />
                <p>No API keys found</p>
              </div>

              <!-- Create New Key -->
              <div class="create-key-section">
                <div class="divider">
                  <span>or create new</span>
                </div>
                <div class="create-key-form">
                  <UInput
                    v-model="newKeyName"
                    placeholder="Key name (e.g., Website Widget)"
                    size="lg"
                    class="flex-1"
                  />
                  <UButton
                    color="primary"
                    @click="createApiKey"
                    :loading="creatingKey"
                    :disabled="!newKeyName.trim()"
                  >
                    <UIcon name="i-heroicons-plus" class="w-4 h-4" />
                    Create Key
                  </UButton>
                </div>
              </div>

              <!-- Newly Created Key Alert -->
              <div v-if="newlyCreatedKey" class="new-key-alert">
                <div class="alert-header">
                  <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5" />
                  <span>Copy your API key now!</span>
                </div>
                <div class="alert-body">
                  <input :value="newlyCreatedKey" readonly class="key-display" />
                  <UButton
                    :icon="keyCopied ? 'i-heroicons-check' : 'i-heroicons-clipboard-document'"
                    variant="soft"
                    @click="copyNewKey"
                  >
                    {{ keyCopied ? 'Copied!' : 'Copy' }}
                  </UButton>
                </div>
                <p class="alert-warning">This key won't be shown again. Make sure to save it!</p>
              </div>
            </div>
          </div>

          <!-- Widget Options -->
          <div class="config-card">
            <div class="card-header">
              <div class="card-header-icon options">
                <UIcon name="i-heroicons-adjustments-horizontal" class="w-5 h-5" />
              </div>
              <div>
                <h2 class="card-title">Widget Options</h2>
                <p class="card-description">Customize the widget appearance</p>
              </div>
            </div>
            <div class="card-body">
              <div class="options-grid">
                <!-- Position -->
                <div class="option-group">
                  <label class="option-label">Position</label>
                  <div class="position-buttons">
                    <button
                      class="position-btn"
                      :class="{ active: widgetOptions.position === 'bottom-right' }"
                      @click="widgetOptions.position = 'bottom-right'"
                    >
                      <UIcon name="i-heroicons-arrow-down-right" class="w-4 h-4" />
                      Bottom Right
                    </button>
                    <button
                      class="position-btn"
                      :class="{ active: widgetOptions.position === 'bottom-left' }"
                      @click="widgetOptions.position = 'bottom-left'"
                    >
                      <UIcon name="i-heroicons-arrow-down-left" class="w-4 h-4" />
                      Bottom Left
                    </button>
                  </div>
                </div>

                <!-- Theme -->
                <div class="option-group">
                  <label class="option-label">Theme</label>
                  <div class="theme-buttons">
                    <button
                      class="theme-btn"
                      :class="{ active: widgetOptions.theme === 'light' }"
                      @click="widgetOptions.theme = 'light'"
                    >
                      <UIcon name="i-heroicons-sun" class="w-4 h-4" />
                      Light
                    </button>
                    <button
                      class="theme-btn"
                      :class="{ active: widgetOptions.theme === 'dark' }"
                      @click="widgetOptions.theme = 'dark'"
                    >
                      <UIcon name="i-heroicons-moon" class="w-4 h-4" />
                      Dark
                    </button>
                  </div>
                </div>

                <!-- Accent Color -->
                <div class="option-group">
                  <label class="option-label">Accent Color (optional)</label>
                  <div class="color-input-wrapper">
                    <input
                      type="color"
                      v-model="widgetOptions.accentColor"
                      class="color-picker"
                    />
                    <UInput
                      v-model="widgetOptions.accentColor"
                      placeholder="#2563eb"
                      size="lg"
                      class="flex-1"
                    />
                    <UButton
                      v-if="widgetOptions.accentColor"
                      icon="i-heroicons-x-mark"
                      variant="ghost"
                      size="sm"
                      @click="widgetOptions.accentColor = ''"
                    />
                  </div>
                </div>

                <!-- Branding -->
                <div class="option-group">
                  <div class="toggle-item">
                    <div class="toggle-content">
                      <div class="toggle-text">
                        <span class="toggle-label">Show "Powered by" branding</span>
                        <span class="toggle-description">Display Reusable Chat attribution</span>
                      </div>
                    </div>
                    <label class="toggle-switch">
                      <input type="checkbox" v-model="widgetOptions.showBranding" />
                      <span class="toggle-slider"></span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Code Preview Panel -->
        <div class="preview-panel">
          <!-- Embed Code Card -->
          <div class="preview-card">
            <div class="card-header">
              <div class="card-header-icon code">
                <UIcon name="i-heroicons-code-bracket-square" class="w-5 h-5" />
              </div>
              <div class="flex-1">
                <h2 class="card-title">Embed Code</h2>
                <p class="card-description">Copy this code to your website</p>
              </div>
              <UButton
                :icon="embedCopied ? 'i-heroicons-check' : 'i-heroicons-clipboard-document'"
                variant="soft"
                color="primary"
                @click="copyEmbedCode"
              >
                {{ embedCopied ? 'Copied!' : 'Copy Code' }}
              </UButton>
            </div>
            <div class="card-body">
              <pre class="code-block"><code>{{ embedCode }}</code></pre>
            </div>
          </div>

          <!-- Usage Instructions -->
          <div class="preview-card">
            <div class="card-header">
              <div class="card-header-icon docs">
                <UIcon name="i-heroicons-book-open" class="w-5 h-5" />
              </div>
              <div>
                <h2 class="card-title">Integration Guide</h2>
                <p class="card-description">How to integrate the widget in your app</p>
              </div>
            </div>
            <div class="card-body">
              <div class="integration-steps">
                <div class="step">
                  <div class="step-number">1</div>
                  <div class="step-content">
                    <h4>Add the script tag</h4>
                    <p>Add the widget script to your HTML before the closing <code>&lt;/body&gt;</code> tag</p>
                  </div>
                </div>

                <div class="step">
                  <div class="step-number">2</div>
                  <div class="step-content">
                    <h4>Add the widget element</h4>
                    <p>Place the <code>&lt;reusable-chat&gt;</code> element anywhere in your HTML</p>
                  </div>
                </div>

                <div class="step">
                  <div class="step-number">3</div>
                  <div class="step-content">
                    <h4>Set user attributes dynamically</h4>
                    <p>Replace placeholder values with your actual user data:</p>
                    <pre class="code-inline"><code>// JavaScript
const widget = document.querySelector('reusable-chat');
widget.setAttribute('user-id', currentUser.id);
widget.setAttribute('user-name', currentUser.name);
widget.setAttribute('user-email', currentUser.email);</code></pre>
                  </div>
                </div>
              </div>

              <!-- Available Attributes -->
              <div class="attributes-section">
                <h4 class="section-title">Available Attributes</h4>
                <div class="attributes-table">
                  <div class="attr-row header">
                    <div class="attr-name">Attribute</div>
                    <div class="attr-type">Type</div>
                    <div class="attr-desc">Description</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>api-key</code></div>
                    <div class="attr-type">string</div>
                    <div class="attr-desc">Your workspace public API key (required)</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>user-id</code></div>
                    <div class="attr-type">string</div>
                    <div class="attr-desc">Unique identifier for the current user</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>user-name</code></div>
                    <div class="attr-type">string</div>
                    <div class="attr-desc">Display name for the current user</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>user-email</code></div>
                    <div class="attr-type">string</div>
                    <div class="attr-desc">Email address (optional)</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>position</code></div>
                    <div class="attr-type">"bottom-right" | "bottom-left"</div>
                    <div class="attr-desc">Widget position on screen</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>theme</code></div>
                    <div class="attr-type">"light" | "dark"</div>
                    <div class="attr-desc">Widget color theme</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>accent-color</code></div>
                    <div class="attr-type">string</div>
                    <div class="attr-desc">Custom accent color (hex)</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>show-branding</code></div>
                    <div class="attr-type">boolean</div>
                    <div class="attr-desc">Show "Powered by" text</div>
                  </div>
                  <div class="attr-row">
                    <div class="attr-name"><code>api-url</code></div>
                    <div class="attr-type">string</div>
                    <div class="attr-desc">Custom API URL (advanced)</div>
                  </div>
                </div>
              </div>

              <!-- CDN Info -->
              <div class="cdn-section">
                <h4 class="section-title">CDN URLs</h4>
                <div class="cdn-urls">
                  <div class="cdn-url">
                    <span class="cdn-label">Latest (v1):</span>
                    <code>https://hastest.b-cdn.net/widget/v1/widget.js</code>
                  </div>
                  <div class="cdn-url">
                    <span class="cdn-label">Versioned:</span>
                    <code>https://hastest.b-cdn.net/widget/v1.0.0/widget.js</code>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import type { ApiKey } from '~/types'

definePageMeta({
  layout: 'dashboard'
})

const config = useRuntimeConfig()
const toast = useToast()
const { token, currentWorkspace } = useAuth()

// State
const loading = ref(true)
const publicKeys = ref<ApiKey[]>([])
const selectedKeyId = ref<string | null>(null)
const newKeyName = ref('')
const creatingKey = ref(false)
const newlyCreatedKey = ref('')
const keyCopied = ref(false)
const embedCopied = ref(false)

// Widget options
const widgetOptions = reactive({
  position: 'bottom-right' as 'bottom-right' | 'bottom-left',
  theme: 'light' as 'light' | 'dark',
  accentColor: '',
  showBranding: true
})

// Computed: selected key
const selectedKey = computed(() => {
  return publicKeys.value.find(k => k.id === selectedKeyId.value)
})

// Computed: embed code
const embedCode = computed(() => {
  const apiKey = selectedKey.value?.key_prefix ? `${selectedKey.value.key_prefix}...` : 'pk_your_key_here'

  let code = `<!-- Reusable Chat Widget -->
<script src="https://hastest.b-cdn.net/widget/v1/widget.js"><\/script>
<reusable-chat
  api-key="${apiKey}"
  user-id="YOUR_USER_ID"
  user-name="YOUR_USER_NAME"`

  if (widgetOptions.position !== 'bottom-right') {
    code += `\n  position="${widgetOptions.position}"`
  }

  if (widgetOptions.theme !== 'light') {
    code += `\n  theme="${widgetOptions.theme}"`
  }

  if (widgetOptions.accentColor) {
    code += `\n  accent-color="${widgetOptions.accentColor}"`
  }

  if (!widgetOptions.showBranding) {
    code += `\n  show-branding="false"`
  }

  code += `\n></reusable-chat>`

  return code
})

// Fetch API keys
const fetchApiKeys = async () => {
  if (!currentWorkspace.value?.id) {
    loading.value = false
    return
  }

  try {
    const data = await $fetch<ApiKey[]>(
      `${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/api-keys`,
      {
        headers: { Authorization: `Bearer ${token.value}` }
      }
    )
    publicKeys.value = data.filter(k => k.key_prefix?.startsWith('pk_') || k.key_prefix?.startsWith('sk_'))

    // Auto-select first key
    if (publicKeys.value.length > 0 && !selectedKeyId.value) {
      selectedKeyId.value = publicKeys.value[0].id
    }
  } catch (e: unknown) {
    const fetchError = e as { data?: { message?: string } }
    toast.add({
      title: 'Error',
      description: fetchError.data?.message || 'Failed to load API keys',
      color: 'error',
      icon: 'i-heroicons-exclamation-circle'
    })
  } finally {
    loading.value = false
  }
}

// Create new API key
const createApiKey = async () => {
  if (!currentWorkspace.value?.id || !newKeyName.value.trim()) return

  creatingKey.value = true

  try {
    const data = await $fetch<ApiKey & { key?: string }>(
      `${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/api-keys`,
      {
        method: 'POST',
        headers: { Authorization: `Bearer ${token.value}` },
        body: { name: newKeyName.value.trim() }
      }
    )

    const newKey: ApiKey = {
      id: data.id,
      name: data.name,
      key_prefix: data.key_prefix,
      created_at: data.created_at
    }

    publicKeys.value.unshift(newKey)
    selectedKeyId.value = newKey.id
    newlyCreatedKey.value = data.key || ''
    newKeyName.value = ''

    toast.add({
      title: 'API key created',
      description: 'Copy your new API key now!',
      color: 'success',
      icon: 'i-heroicons-check-circle'
    })
  } catch (e: unknown) {
    const fetchError = e as { data?: { message?: string } }
    toast.add({
      title: 'Error',
      description: fetchError.data?.message || 'Failed to create API key',
      color: 'error',
      icon: 'i-heroicons-exclamation-circle'
    })
  } finally {
    creatingKey.value = false
  }
}

// Copy functions
const copyToClipboard = async (text: string): Promise<boolean> => {
  try {
    await navigator.clipboard.writeText(text)
    return true
  } catch {
    return false
  }
}

const copyKeyPrefix = async (key: ApiKey) => {
  const success = await copyToClipboard(key.key_prefix)
  if (success) {
    toast.add({
      title: 'Copied',
      description: 'Key prefix copied to clipboard',
      color: 'success',
      icon: 'i-heroicons-check'
    })
  }
}

const copyNewKey = async () => {
  const success = await copyToClipboard(newlyCreatedKey.value)
  if (success) {
    keyCopied.value = true
    setTimeout(() => { keyCopied.value = false }, 2000)
  }
}

const copyEmbedCode = async () => {
  const success = await copyToClipboard(embedCode.value)
  if (success) {
    embedCopied.value = true
    toast.add({
      title: 'Copied',
      description: 'Embed code copied to clipboard',
      color: 'success',
      icon: 'i-heroicons-check-circle'
    })
    setTimeout(() => { embedCopied.value = false }, 2000)
  }
}

// Lifecycle
onMounted(() => {
  if (currentWorkspace.value?.id) {
    fetchApiKeys()
  }
})

watch(() => currentWorkspace.value?.id, (newId) => {
  if (newId) {
    fetchApiKeys()
  }
})
</script>

<style scoped>
/* Page Layout */
.embed-page {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Page Header */
.embed-header {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--chat-bg-tertiary);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.header-title {
  font-size: 28px;
  font-weight: 700;
  color: var(--chat-text-primary);
  margin: 0;
}

.header-description {
  font-size: 14px;
  color: var(--chat-text-secondary);
  margin: 4px 0 0 0;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 24px;
  text-align: center;
  color: var(--chat-text-secondary);
}

.loading-spinner {
  margin-bottom: 16px;
  color: var(--chat-accent);
}

/* Grid Layout */
.embed-grid {
  display: grid;
  grid-template-columns: 420px 1fr;
  gap: 24px;
}

@media (max-width: 1024px) {
  .embed-grid {
    grid-template-columns: 1fr;
  }
}

.config-panel {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.preview-panel {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Cards */
.config-card,
.preview-card {
  background: var(--chat-bg-secondary);
  border-radius: 16px;
  border: 1px solid var(--chat-bg-tertiary);
  overflow: hidden;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid var(--chat-bg-tertiary);
}

.card-header-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: white;
}

.card-header-icon.keys {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
}

.card-header-icon.options {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.card-header-icon.code {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.card-header-icon.docs {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--chat-text-primary);
  margin: 0;
}

.card-description {
  font-size: 13px;
  color: var(--chat-text-secondary);
  margin: 2px 0 0 0;
}

.card-body {
  padding: 24px;
}

/* API Keys List */
.api-keys-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.api-key-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: var(--chat-bg-primary);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.api-key-item:hover {
  background: var(--chat-bg-tertiary);
}

.api-key-item.selected {
  border-color: var(--chat-accent);
  background: rgba(37, 99, 235, 0.05);
}

.key-radio {
  flex-shrink: 0;
}

.radio-outer {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2px solid var(--chat-bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.api-key-item.selected .radio-outer {
  border-color: var(--chat-accent);
}

.radio-inner {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--chat-accent);
}

.key-info {
  flex: 1;
  min-width: 0;
}

.key-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--chat-text-primary);
}

.key-prefix {
  font-size: 12px;
  font-family: monospace;
  color: var(--chat-text-secondary);
}

.empty-keys {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 24px;
  color: var(--chat-text-secondary);
  text-align: center;
}

/* Create Key Section */
.create-key-section {
  margin-top: 20px;
}

.divider {
  display: flex;
  align-items: center;
  margin: 16px 0;
  color: var(--chat-text-secondary);
  font-size: 12px;
}

.divider::before,
.divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background: var(--chat-bg-tertiary);
}

.divider::before {
  margin-right: 12px;
}

.divider::after {
  margin-left: 12px;
}

.create-key-form {
  display: flex;
  gap: 12px;
}

/* New Key Alert */
.new-key-alert {
  margin-top: 16px;
  padding: 16px;
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.2);
  border-radius: 12px;
}

.alert-header {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #d97706;
  font-weight: 600;
  margin-bottom: 12px;
}

.alert-body {
  display: flex;
  gap: 8px;
}

.key-display {
  flex: 1;
  padding: 10px 12px;
  font-family: monospace;
  font-size: 12px;
  background: var(--chat-bg-primary);
  border: 1px solid var(--chat-bg-tertiary);
  border-radius: 8px;
  color: var(--chat-text-primary);
}

.alert-warning {
  font-size: 12px;
  color: #d97706;
  margin: 12px 0 0 0;
}

/* Widget Options */
.options-grid {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.option-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.option-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--chat-text-primary);
}

.position-buttons,
.theme-buttons {
  display: flex;
  gap: 8px;
}

.position-btn,
.theme-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  background: var(--chat-bg-primary);
  border: 2px solid var(--chat-bg-tertiary);
  border-radius: 10px;
  color: var(--chat-text-secondary);
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.position-btn:hover,
.theme-btn:hover {
  background: var(--chat-bg-tertiary);
}

.position-btn.active,
.theme-btn.active {
  border-color: var(--chat-accent);
  background: rgba(37, 99, 235, 0.05);
  color: var(--chat-accent);
}

.color-input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
}

.color-picker {
  width: 44px;
  height: 44px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  background: none;
}

.color-picker::-webkit-color-swatch-wrapper {
  padding: 0;
}

.color-picker::-webkit-color-swatch {
  border-radius: 8px;
  border: 2px solid var(--chat-bg-tertiary);
}

/* Toggle Switch */
.toggle-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  background: var(--chat-bg-primary);
  border-radius: 10px;
}

.toggle-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.toggle-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.toggle-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--chat-text-primary);
}

.toggle-description {
  font-size: 12px;
  color: var(--chat-text-secondary);
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  cursor: pointer;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--chat-bg-tertiary);
  border-radius: 24px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toggle-slider::before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
  background: linear-gradient(135deg, var(--chat-accent) 0%, #3b82f6 100%);
}

.toggle-switch input:checked + .toggle-slider::before {
  transform: translateX(20px);
}

/* Code Block */
.code-block {
  background: #1e1e1e;
  color: #d4d4d4;
  padding: 20px;
  border-radius: 12px;
  overflow-x: auto;
  font-family: 'SF Mono', Monaco, Consolas, monospace;
  font-size: 13px;
  line-height: 1.6;
  margin: 0;
}

.code-block code {
  white-space: pre-wrap;
  word-break: break-word;
}

/* Integration Steps */
.integration-steps {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 32px;
}

.step {
  display: flex;
  gap: 16px;
}

.step-number {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--chat-accent) 0%, #3b82f6 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  flex-shrink: 0;
}

.step-content {
  flex: 1;
}

.step-content h4 {
  font-size: 14px;
  font-weight: 600;
  color: var(--chat-text-primary);
  margin: 0 0 4px 0;
}

.step-content p {
  font-size: 13px;
  color: var(--chat-text-secondary);
  margin: 0;
  line-height: 1.5;
}

.step-content code {
  background: var(--chat-bg-primary);
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 12px;
  color: var(--chat-accent);
}

.code-inline {
  background: #1e1e1e;
  color: #d4d4d4;
  padding: 12px 16px;
  border-radius: 8px;
  margin-top: 12px;
  font-family: 'SF Mono', Monaco, Consolas, monospace;
  font-size: 12px;
  line-height: 1.5;
  overflow-x: auto;
}

.code-inline code {
  white-space: pre;
}

/* Attributes Section */
.attributes-section {
  margin-bottom: 32px;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--chat-text-primary);
  margin: 0 0 16px 0;
}

.attributes-table {
  border: 1px solid var(--chat-bg-tertiary);
  border-radius: 12px;
  overflow: hidden;
}

.attr-row {
  display: grid;
  grid-template-columns: 150px 180px 1fr;
  padding: 12px 16px;
  border-bottom: 1px solid var(--chat-bg-tertiary);
  font-size: 13px;
}

.attr-row:last-child {
  border-bottom: none;
}

.attr-row.header {
  background: var(--chat-bg-primary);
  font-weight: 600;
  color: var(--chat-text-primary);
}

.attr-name {
  color: var(--chat-text-primary);
}

.attr-name code {
  background: var(--chat-bg-primary);
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 12px;
  color: var(--chat-accent);
}

.attr-type {
  color: var(--chat-text-secondary);
  font-family: monospace;
  font-size: 12px;
}

.attr-desc {
  color: var(--chat-text-secondary);
}

/* CDN Section */
.cdn-section {
  padding-top: 24px;
  border-top: 1px solid var(--chat-bg-tertiary);
}

.cdn-urls {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.cdn-url {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: var(--chat-bg-primary);
  border-radius: 8px;
  font-size: 13px;
}

.cdn-label {
  font-weight: 500;
  color: var(--chat-text-primary);
  min-width: 100px;
}

.cdn-url code {
  font-family: monospace;
  color: var(--chat-text-secondary);
  font-size: 12px;
}

/* Responsive */
@media (max-width: 768px) {
  .embed-page {
    padding: 16px;
  }

  .attr-row {
    grid-template-columns: 1fr;
    gap: 4px;
  }

  .attr-row.header {
    display: none;
  }

  .attr-type {
    font-size: 11px;
  }
}
</style>
