<template>
  <div class="settings-page">
    <!-- Page Header -->
    <header class="settings-header">
      <div class="header-content">
        <div class="header-icon">
          <UIcon name="i-heroicons-cog-6-tooth" class="w-6 h-6" />
        </div>
        <div>
          <h1 class="header-title">Settings</h1>
          <p class="header-description">Configure your workspace and chat features</p>
        </div>
      </div>
      <div class="header-actions">
        <UButton
          variant="ghost"
          @click="resetSettings"
          :disabled="saving || !hasChanges"
          class="reset-btn"
        >
          <UIcon name="i-heroicons-arrow-path" class="w-4 h-4" />
          Reset
        </UButton>
        <UButton
          color="primary"
          @click="saveSettings"
          :loading="saving"
          :disabled="!hasChanges"
          class="save-btn"
        >
          <UIcon name="i-heroicons-check" class="w-4 h-4" />
          Save Changes
        </UButton>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner">
        <UIcon name="i-heroicons-arrow-path" class="w-10 h-10 animate-spin" />
      </div>
      <p>Loading settings...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <UIcon name="i-heroicons-exclamation-circle" class="w-12 h-12 text-red-500" />
      <h3>Error loading settings</h3>
      <p>{{ error }}</p>
      <UButton variant="soft" color="red" @click="fetchSettings" class="mt-4">
        <UIcon name="i-heroicons-arrow-path" class="w-4 h-4" />
        Try Again
      </UButton>
    </div>

    <template v-else>
      <div class="settings-grid">
        <!-- Main Settings Column -->
        <div class="main-column">
          <!-- Workspace Settings Card -->
          <div class="settings-card">
            <div class="card-header">
              <div class="card-header-icon workspace">
                <UIcon name="i-heroicons-building-office-2" class="w-5 h-5" />
              </div>
              <div>
                <h2 class="card-title">Workspace</h2>
                <p class="card-description">Basic workspace configuration</p>
              </div>
            </div>
            <div class="card-body">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">
                    Workspace Name
                    <span class="label-required">*</span>
                  </label>
                  <UInput
                    v-model="settings.workspaceName"
                    placeholder="My Workspace"
                    size="lg"
                    class="form-input"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label">
                    Workspace Slug
                  </label>
                  <UInput
                    v-model="settings.workspaceSlug"
                    placeholder="my-workspace"
                    size="lg"
                    class="form-input"
                  />
                  <p class="form-hint">Used in widget embed code and API URLs</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Chat Features Card -->
          <div class="settings-card">
            <div class="card-header">
              <div class="card-header-icon features">
                <UIcon name="i-heroicons-chat-bubble-bottom-center-text" class="w-5 h-5" />
              </div>
              <div>
                <h2 class="card-title">Chat Features</h2>
                <p class="card-description">Enable or disable chat functionality</p>
              </div>
            </div>
            <div class="card-body">
              <div class="toggle-list">
                <div class="toggle-item">
                  <div class="toggle-content">
                    <div class="toggle-icon-wrapper blue">
                      <UIcon name="i-heroicons-check-badge" class="w-4 h-4" />
                    </div>
                    <div class="toggle-text">
                      <span class="toggle-label">Read Receipts</span>
                      <span class="toggle-description">Show when messages have been read</span>
                    </div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" v-model="settings.readReceipts" />
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-content">
                    <div class="toggle-icon-wrapper purple">
                      <UIcon name="i-heroicons-pencil" class="w-4 h-4" />
                    </div>
                    <div class="toggle-text">
                      <span class="toggle-label">Typing Indicators</span>
                      <span class="toggle-description">Show when someone is typing</span>
                    </div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" v-model="settings.typingIndicators" />
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-content">
                    <div class="toggle-icon-wrapper green">
                      <UIcon name="i-heroicons-signal" class="w-4 h-4" />
                    </div>
                    <div class="toggle-text">
                      <span class="toggle-label">Online Status</span>
                      <span class="toggle-description">Show when users are online</span>
                    </div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" v-model="settings.onlineStatus" />
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-content">
                    <div class="toggle-icon-wrapper orange">
                      <UIcon name="i-heroicons-paper-clip" class="w-4 h-4" />
                    </div>
                    <div class="toggle-text">
                      <span class="toggle-label">File Attachments</span>
                      <span class="toggle-description">Allow users to send files</span>
                    </div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" v-model="settings.fileAttachments" />
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-content">
                    <div class="toggle-icon-wrapper pink">
                      <UIcon name="i-heroicons-face-smile" class="w-4 h-4" />
                    </div>
                    <div class="toggle-text">
                      <span class="toggle-label">Emoji Reactions</span>
                      <span class="toggle-description">Allow reactions on messages</span>
                    </div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" v-model="settings.emojiReactions" />
                    <span class="toggle-slider"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Rate Limiting Card -->
          <div class="settings-card">
            <div class="card-header">
              <div class="card-header-icon limits">
                <UIcon name="i-heroicons-adjustments-horizontal" class="w-5 h-5" />
              </div>
              <div>
                <h2 class="card-title">Rate Limiting</h2>
                <p class="card-description">Control usage limits and restrictions</p>
              </div>
            </div>
            <div class="card-body">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Messages per minute</label>
                  <div class="input-with-suffix">
                    <UInput
                      v-model.number="settings.rateLimit"
                      type="number"
                      min="10"
                      max="300"
                      size="lg"
                      class="form-input"
                    />
                    <span class="input-suffix">msg/min</span>
                  </div>
                  <p class="form-hint">Maximum messages a user can send per minute (10-300)</p>
                </div>
                <div class="form-group">
                  <label class="form-label">Max file size</label>
                  <div class="input-with-suffix">
                    <UInput
                      v-model.number="settings.maxFileSize"
                      type="number"
                      min="1"
                      max="50"
                      size="lg"
                      class="form-input"
                    />
                    <span class="input-suffix">MB</span>
                  </div>
                  <p class="form-hint">Maximum file attachment size in megabytes (1-50)</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Webhooks Card -->
          <div class="settings-card">
            <div class="card-header">
              <div class="card-header-icon webhooks">
                <UIcon name="i-heroicons-arrow-path-rounded-square" class="w-5 h-5" />
              </div>
              <div>
                <h2 class="card-title">Webhooks</h2>
                <p class="card-description">Receive real-time notifications for chat events</p>
              </div>
            </div>
            <div class="card-body">
              <div class="form-stack">
                <div class="form-group">
                  <label class="form-label">
                    <UIcon name="i-heroicons-link" class="w-4 h-4 inline" />
                    Webhook URL
                  </label>
                  <UInput
                    v-model="settings.webhookUrl"
                    placeholder="https://your-app.com/webhook"
                    size="lg"
                    class="form-input"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label">
                    <UIcon name="i-heroicons-key" class="w-4 h-4 inline" />
                    Webhook Secret
                  </label>
                  <UInput
                    v-model="settings.webhookSecret"
                    type="password"
                    placeholder="Enter webhook secret"
                    size="lg"
                    class="form-input"
                  />
                  <p class="form-hint">Secret key for verifying webhook signatures</p>
                </div>
              </div>

              <div class="divider">
                <span>Event Subscriptions</span>
              </div>

              <div class="toggle-list compact">
                <div class="toggle-item">
                  <div class="toggle-content">
                    <div class="toggle-text">
                      <span class="toggle-label">New Message Events</span>
                      <span class="toggle-description">Notify when new messages are sent</span>
                    </div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" v-model="settings.webhookNewMessage" />
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="toggle-item">
                  <div class="toggle-content">
                    <div class="toggle-text">
                      <span class="toggle-label">User Events</span>
                      <span class="toggle-description">Notify when users join or leave</span>
                    </div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" v-model="settings.webhookUserEvents" />
                    <span class="toggle-slider"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Column -->
        <div class="sidebar-column">
          <!-- API Keys Card -->
          <div class="settings-card api-keys-card">
            <div class="card-header">
              <div class="card-header-icon keys">
                <UIcon name="i-heroicons-key" class="w-5 h-5" />
              </div>
              <div class="flex-1">
                <h2 class="card-title">API Keys</h2>
                <p class="card-description">Manage your integration keys</p>
              </div>
              <UButton
                size="sm"
                @click="showCreateModal = true"
                :loading="creatingKey"
                class="create-key-btn"
              >
                <UIcon name="i-heroicons-plus" class="w-4 h-4" />
                New Key
              </UButton>
            </div>
            <div class="card-body">
              <!-- Loading State -->
              <div v-if="loadingKeys" class="api-keys-loading">
                <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 animate-spin" />
                <span>Loading keys...</span>
              </div>

              <!-- Error State -->
              <div v-else-if="keysError" class="api-keys-error">
                <UIcon name="i-heroicons-exclamation-circle" class="w-8 h-8" />
                <p>{{ keysError }}</p>
                <UButton size="xs" variant="ghost" @click="fetchApiKeys">
                  Try Again
                </UButton>
              </div>

              <!-- Empty State -->
              <div v-else-if="apiKeys.length === 0" class="api-keys-empty">
                <div class="empty-icon">
                  <UIcon name="i-heroicons-key" class="w-8 h-8" />
                </div>
                <h4>No API keys yet</h4>
                <p>Create one to integrate with your app</p>
              </div>

              <!-- API Keys List -->
              <div v-else class="api-keys-list">
                <div v-for="key in apiKeys" :key="key.id" class="api-key-item">
                  <div class="key-info">
                    <div class="key-name">{{ key.name }}</div>
                    <div class="key-prefix">{{ key.key_prefix }}</div>
                    <div class="key-date">Created {{ formatDate(key.created_at) }}</div>
                  </div>
                  <UButton
                    icon="i-heroicons-trash"
                    size="xs"
                    variant="ghost"
                    color="red"
                    :loading="deletingKeyId === key.id"
                    @click="confirmDeleteKey(key)"
                    class="delete-key-btn"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Danger Zone Card -->
          <div class="settings-card danger-zone-card">
            <div class="card-header danger">
              <div class="card-header-icon danger">
                <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5" />
              </div>
              <div>
                <h2 class="card-title">Danger Zone</h2>
                <p class="card-description">Irreversible actions</p>
              </div>
            </div>
            <div class="card-body">
              <div class="danger-actions">
                <div class="danger-action">
                  <div class="danger-action-info">
                    <h4>Clear All Data</h4>
                    <p>Delete all conversations and messages</p>
                  </div>
                  <UButton
                    variant="outline"
                    color="red"
                    size="sm"
                    @click="clearAllData"
                  >
                    Clear Data
                  </UButton>
                </div>
                <div class="danger-action">
                  <div class="danger-action-info">
                    <h4>Delete Workspace</h4>
                    <p>Permanently delete this workspace</p>
                  </div>
                  <UButton
                    variant="solid"
                    color="red"
                    size="sm"
                    @click="deleteWorkspace"
                  >
                    Delete
                  </UButton>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Create API Key Modal -->
    <UModal v-model:open="showCreateModal">
      <template #content>
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon">
              <UIcon name="i-heroicons-key" class="w-6 h-6" />
            </div>
            <h3>Create API Key</h3>
            <UButton
              icon="i-heroicons-x-mark"
              variant="ghost"
              size="sm"
              @click="showCreateModal = false"
              class="modal-close"
            />
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label class="form-label">
                Key Name
                <span class="label-required">*</span>
              </label>
              <UInput
                v-model="newKeyName"
                placeholder="e.g., Production Key"
                :disabled="creatingKey"
                size="lg"
                class="form-input"
              />
              <p class="form-hint">Give your key a memorable name</p>
            </div>
          </div>
          <div class="modal-footer">
            <UButton variant="ghost" @click="showCreateModal = false" :disabled="creatingKey">
              Cancel
            </UButton>
            <UButton
              color="primary"
              @click="createApiKey"
              :loading="creatingKey"
              :disabled="!newKeyName.trim()"
            >
              Create Key
            </UButton>
          </div>
        </div>
      </template>
    </UModal>

    <!-- Show New Key Modal -->
    <UModal v-model:open="showNewKeyModal">
      <template #content>
        <div class="modal-content">
          <div class="modal-header success">
            <div class="modal-icon success">
              <UIcon name="i-heroicons-check-circle" class="w-6 h-6" />
            </div>
            <h3>API Key Created</h3>
          </div>
          <div class="modal-body">
            <div class="warning-banner">
              <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5" />
              <p>Copy this key now. You won't be able to see it again!</p>
            </div>
            <div class="form-group">
              <label class="form-label">Your API Key</label>
              <div class="key-copy-field">
                <input
                  :value="newlyCreatedKey"
                  readonly
                  class="key-display"
                />
                <UButton
                  :icon="copied ? 'i-heroicons-check' : 'i-heroicons-clipboard-document'"
                  variant="soft"
                  @click="copyToClipboard(newlyCreatedKey)"
                  class="copy-btn"
                >
                  {{ copied ? 'Copied!' : 'Copy' }}
                </UButton>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <UButton color="primary" @click="closeNewKeyModal" block>
              Done
            </UButton>
          </div>
        </div>
      </template>
    </UModal>

    <!-- Delete Confirmation Modal -->
    <UModal v-model:open="showDeleteModal">
      <template #content>
        <div class="modal-content">
          <div class="modal-header danger">
            <div class="modal-icon danger">
              <UIcon name="i-heroicons-exclamation-triangle" class="w-6 h-6" />
            </div>
            <h3>Delete API Key</h3>
          </div>
          <div class="modal-body">
            <p class="delete-warning">
              Are you sure you want to delete <strong>{{ keyToDelete?.name }}</strong>?
              This action cannot be undone and any integrations using this key will stop working.
            </p>
          </div>
          <div class="modal-footer">
            <UButton variant="ghost" @click="showDeleteModal = false" :disabled="deletingKeyId !== null">
              Cancel
            </UButton>
            <UButton
              color="red"
              @click="deleteApiKey"
              :loading="deletingKeyId !== null"
            >
              Delete Key
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
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

// Settings state
const loading = ref(true)
const saving = ref(false)
const error = ref<string | null>(null)

interface Settings {
  workspaceName: string
  workspaceSlug: string
  readReceipts: boolean
  typingIndicators: boolean
  onlineStatus: boolean
  fileAttachments: boolean
  emojiReactions: boolean
  rateLimit: number
  maxFileSize: number
  webhookUrl: string
  webhookSecret: string
  webhookNewMessage: boolean
  webhookUserEvents: boolean
}

const defaultSettings: Settings = {
  workspaceName: '',
  workspaceSlug: '',
  readReceipts: true,
  typingIndicators: true,
  onlineStatus: true,
  fileAttachments: true,
  emojiReactions: true,
  rateLimit: 30,
  maxFileSize: 10,
  webhookUrl: '',
  webhookSecret: '',
  webhookNewMessage: true,
  webhookUserEvents: false
}

const settings = ref<Settings>({ ...defaultSettings })
const originalSettings = ref<Settings>({ ...defaultSettings })

const hasChanges = computed(() => {
  return JSON.stringify(settings.value) !== JSON.stringify(originalSettings.value)
})

// API Keys state
const apiKeys = ref<ApiKey[]>([])
const loadingKeys = ref(false)
const keysError = ref<string | null>(null)
const creatingKey = ref(false)
const deletingKeyId = ref<string | null>(null)

// Modal state
const showCreateModal = ref(false)
const showNewKeyModal = ref(false)
const showDeleteModal = ref(false)
const newKeyName = ref('')
const newlyCreatedKey = ref('')
const keyToDelete = ref<ApiKey | null>(null)
const copied = ref(false)

// Fetch settings from API
const fetchSettings = async () => {
  if (!currentWorkspace.value?.id) {
    error.value = 'No workspace selected'
    loading.value = false
    return
  }

  loading.value = true
  error.value = null

  try {
    const data = await $fetch<{
      read_receipts_enabled: boolean
      online_status_enabled: boolean
      typing_indicators_enabled: boolean
      file_size_limit_mb: number
      rate_limit_per_minute: number
      webhook_url: string | null
      webhook_secret: string | null
    }>(`${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/settings`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })

    const loadedSettings: Settings = {
      workspaceName: currentWorkspace.value.name || '',
      workspaceSlug: currentWorkspace.value.slug || '',
      readReceipts: data.read_receipts_enabled ?? true,
      typingIndicators: data.typing_indicators_enabled ?? true,
      onlineStatus: data.online_status_enabled ?? true,
      fileAttachments: true,
      emojiReactions: true,
      rateLimit: data.rate_limit_per_minute ?? 30,
      maxFileSize: data.file_size_limit_mb ?? 10,
      webhookUrl: data.webhook_url || '',
      webhookSecret: data.webhook_secret || '',
      webhookNewMessage: true,
      webhookUserEvents: false
    }

    settings.value = { ...loadedSettings }
    originalSettings.value = { ...loadedSettings }
  } catch (e: unknown) {
    const fetchError = e as { data?: { message?: string } }
    error.value = fetchError.data?.message || 'Failed to load settings'
    toast.add({
      title: 'Error',
      description: error.value,
      color: 'red',
      icon: 'i-heroicons-exclamation-circle'
    })
  } finally {
    loading.value = false
  }
}

// Save settings to API
const saveSettings = async () => {
  if (!currentWorkspace.value?.id) {
    toast.add({
      title: 'Error',
      description: 'No workspace selected',
      color: 'red',
      icon: 'i-heroicons-exclamation-circle'
    })
    return
  }

  saving.value = true

  try {
    const payload = {
      read_receipts_enabled: settings.value.readReceipts,
      online_status_enabled: settings.value.onlineStatus,
      typing_indicators_enabled: settings.value.typingIndicators,
      file_size_limit_mb: settings.value.maxFileSize,
      rate_limit_per_minute: settings.value.rateLimit,
      webhook_url: settings.value.webhookUrl || null,
      webhook_secret: settings.value.webhookSecret || null
    }

    await $fetch(`${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/settings`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` },
      body: payload
    })

    originalSettings.value = { ...settings.value }

    toast.add({
      title: 'Settings saved',
      description: 'Your settings have been updated successfully',
      color: 'green',
      icon: 'i-heroicons-check-circle'
    })
  } catch (e: unknown) {
    const fetchError = e as { data?: { message?: string; errors?: Record<string, string[]> } }
    let errorMessage = 'Failed to save settings'

    if (fetchError.data?.errors) {
      const firstError = Object.values(fetchError.data.errors)[0]
      if (firstError && firstError[0]) {
        errorMessage = firstError[0]
      }
    } else if (fetchError.data?.message) {
      errorMessage = fetchError.data.message
    }

    toast.add({
      title: 'Error',
      description: errorMessage,
      color: 'red',
      icon: 'i-heroicons-exclamation-circle'
    })
  } finally {
    saving.value = false
  }
}

// Reset settings to last saved state
const resetSettings = () => {
  settings.value = { ...originalSettings.value }
  toast.add({
    title: 'Changes reset',
    description: 'Settings have been reset to their last saved state',
    color: 'blue',
    icon: 'i-heroicons-arrow-path'
  })
}

// Fetch API keys
const fetchApiKeys = async () => {
  if (!currentWorkspace.value?.id) {
    keysError.value = 'No workspace selected'
    return
  }

  loadingKeys.value = true
  keysError.value = null

  try {
    const data = await $fetch<ApiKey[]>(
      `${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/api-keys`,
      {
        headers: { Authorization: `Bearer ${token.value}` }
      }
    )
    apiKeys.value = data
  } catch (e: unknown) {
    const fetchError = e as { data?: { message?: string }; message?: string }
    keysError.value = fetchError.data?.message || fetchError.message || 'Failed to load API keys'
  } finally {
    loadingKeys.value = false
  }
}

// Create a new API key
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

    apiKeys.value.unshift({
      id: data.id,
      name: data.name,
      key_prefix: data.key_prefix,
      created_at: data.created_at
    })

    newlyCreatedKey.value = data.key || ''
    showCreateModal.value = false
    newKeyName.value = ''
    showNewKeyModal.value = true

    toast.add({
      title: 'API key created',
      description: 'Copy your new API key now - you won\'t see it again!',
      color: 'green',
      icon: 'i-heroicons-check-circle'
    })
  } catch (e: unknown) {
    const fetchError = e as { data?: { message?: string }; message?: string }
    toast.add({
      title: 'Error',
      description: fetchError.data?.message || fetchError.message || 'Failed to create API key',
      color: 'red',
      icon: 'i-heroicons-exclamation-circle'
    })
  } finally {
    creatingKey.value = false
  }
}

// Confirm delete key
const confirmDeleteKey = (key: ApiKey) => {
  keyToDelete.value = key
  showDeleteModal.value = true
}

// Delete an API key
const deleteApiKey = async () => {
  if (!currentWorkspace.value?.id || !keyToDelete.value) return

  deletingKeyId.value = keyToDelete.value.id

  try {
    await $fetch(
      `${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/api-keys/${keyToDelete.value.id}`,
      {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token.value}` }
      }
    )

    apiKeys.value = apiKeys.value.filter(k => k.id !== keyToDelete.value?.id)
    showDeleteModal.value = false
    keyToDelete.value = null

    toast.add({
      title: 'API key deleted',
      description: 'The API key has been permanently deleted',
      color: 'green',
      icon: 'i-heroicons-check-circle'
    })
  } catch (e: unknown) {
    const fetchError = e as { data?: { message?: string }; message?: string }
    toast.add({
      title: 'Error',
      description: fetchError.data?.message || fetchError.message || 'Failed to delete API key',
      color: 'red',
      icon: 'i-heroicons-exclamation-circle'
    })
  } finally {
    deletingKeyId.value = null
  }
}

// Copy to clipboard
const copyToClipboard = async (text: string) => {
  try {
    await navigator.clipboard.writeText(text)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (e) {
    toast.add({
      title: 'Error',
      description: 'Failed to copy to clipboard',
      color: 'red',
      icon: 'i-heroicons-exclamation-circle'
    })
  }
}

// Close new key modal
const closeNewKeyModal = () => {
  showNewKeyModal.value = false
  newlyCreatedKey.value = ''
}

// Format date helper
const formatDate = (dateString?: string) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`

  const diffHours = Math.floor(diffMins / 60)
  if (diffHours < 24) return `${diffHours}h ago`

  const diffDays = Math.floor(diffHours / 24)
  if (diffDays < 7) return `${diffDays}d ago`
  if (diffDays < 30) return `${diffDays} days ago`

  return date.toLocaleDateString()
}

const clearAllData = () => {
  console.log('Clear all data')
}

const deleteWorkspace = () => {
  console.log('Delete workspace')
}

// Fetch data on mount
onMounted(() => {
  if (currentWorkspace.value?.id) {
    fetchSettings()
    fetchApiKeys()
  }
})

// Watch for workspace changes
watch(() => currentWorkspace.value?.id, (newId) => {
  if (newId) {
    fetchSettings()
    fetchApiKeys()
  }
})
</script>

<style scoped>
/* Page Layout */
.settings-page {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Page Header */
.settings-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
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
  background: linear-gradient(135deg, var(--chat-accent) 0%, #3b82f6 100%);
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

.header-actions {
  display: flex;
  gap: 12px;
}

.reset-btn {
  transition: all 0.2s ease;
}

.save-btn {
  transition: all 0.2s ease;
}

/* Loading & Error States */
.loading-state,
.error-state {
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

.error-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: var(--chat-text-primary);
  margin: 16px 0 8px;
}

/* Settings Grid */
.settings-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 24px;
}

@media (max-width: 1024px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}

.main-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.sidebar-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Settings Card */
.settings-card {
  background: var(--chat-bg-secondary);
  border-radius: 16px;
  border: 1px solid var(--chat-bg-tertiary);
  overflow: hidden;
  transition: box-shadow 0.2s ease;
}

.settings-card:hover {
  box-shadow: var(--chat-shadow-md);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid var(--chat-bg-tertiary);
}

.card-header.danger {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.02) 100%);
}

.card-header-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card-header-icon.workspace {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.card-header-icon.features {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
}

.card-header-icon.limits {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.card-header-icon.webhooks {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.card-header-icon.keys {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
}

.card-header-icon.danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
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

/* Form Elements */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-stack {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--chat-text-primary);
  display: flex;
  align-items: center;
  gap: 6px;
}

.label-required {
  color: #ef4444;
}

.form-input {
  width: 100%;
}

.form-input :deep(input) {
  width: 100%;
  background: var(--chat-bg-primary);
  border-color: var(--chat-bg-tertiary);
  transition: all 0.2s ease;
}

.form-input :deep(input:focus) {
  border-color: var(--chat-accent);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-hint {
  font-size: 12px;
  color: var(--chat-text-secondary);
  margin: 0;
}

.input-with-suffix {
  display: flex;
  align-items: center;
  gap: 8px;
}

.input-with-suffix .form-input {
  flex: 1;
}

.input-suffix {
  font-size: 13px;
  color: var(--chat-text-secondary);
  white-space: nowrap;
}

/* Toggle List */
.toggle-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.toggle-list.compact {
  gap: 0;
}

.toggle-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-radius: 10px;
  transition: background 0.2s ease;
}

.toggle-item:hover {
  background: var(--chat-bg-primary);
}

.toggle-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.toggle-icon-wrapper {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toggle-icon-wrapper.blue {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.toggle-icon-wrapper.purple {
  background: rgba(139, 92, 246, 0.1);
  color: #8b5cf6;
}

.toggle-icon-wrapper.green {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.toggle-icon-wrapper.orange {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.toggle-icon-wrapper.pink {
  background: rgba(236, 72, 153, 0.1);
  color: #ec4899;
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

/* Custom Toggle Switch */
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

.toggle-switch input:focus + .toggle-slider {
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

/* Divider */
.divider {
  display: flex;
  align-items: center;
  margin: 24px 0;
  color: var(--chat-text-secondary);
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.divider::before,
.divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background: var(--chat-bg-tertiary);
}

.divider::before {
  margin-right: 16px;
}

.divider::after {
  margin-left: 16px;
}

/* API Keys Card */
.api-keys-card .card-header {
  flex-wrap: wrap;
}

.create-key-btn {
  margin-left: auto;
}

.api-keys-loading,
.api-keys-error,
.api-keys-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px;
  text-align: center;
  color: var(--chat-text-secondary);
}

.empty-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: var(--chat-bg-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.api-keys-empty h4 {
  font-size: 14px;
  font-weight: 600;
  color: var(--chat-text-primary);
  margin: 0 0 4px 0;
}

.api-keys-empty p {
  font-size: 13px;
  margin: 0;
}

.api-keys-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.api-key-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  background: var(--chat-bg-primary);
  border-radius: 12px;
  transition: all 0.2s ease;
}

.api-key-item:hover {
  background: var(--chat-bg-tertiary);
}

.key-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
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
  color: var(--chat-accent);
  background: rgba(37, 99, 235, 0.1);
  padding: 2px 8px;
  border-radius: 4px;
  display: inline-block;
  width: fit-content;
}

.key-date {
  font-size: 11px;
  color: var(--chat-text-secondary);
}

.delete-key-btn {
  opacity: 0.5;
  transition: opacity 0.2s ease;
}

.api-key-item:hover .delete-key-btn {
  opacity: 1;
}

/* Danger Zone Card */
.danger-zone-card {
  border-color: rgba(239, 68, 68, 0.2);
}

.danger-actions {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.danger-action {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  background: rgba(239, 68, 68, 0.03);
  border: 1px solid rgba(239, 68, 68, 0.1);
  border-radius: 12px;
  gap: 16px;
}

.danger-action-info h4 {
  font-size: 14px;
  font-weight: 600;
  color: var(--chat-text-primary);
  margin: 0 0 2px 0;
}

.danger-action-info p {
  font-size: 12px;
  color: var(--chat-text-secondary);
  margin: 0;
}

/* Modal Styles */
.modal-content {
  padding: 0;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid var(--chat-bg-tertiary);
}

.modal-header.success {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%);
}

.modal-header.danger {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.02) 100%);
}

.modal-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--chat-bg-primary);
  color: var(--chat-accent);
}

.modal-icon.success {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.modal-icon.danger {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: var(--chat-text-primary);
  margin: 0;
  flex: 1;
}

.modal-close {
  margin-left: auto;
}

.modal-body {
  padding: 24px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid var(--chat-bg-tertiary);
  background: var(--chat-bg-primary);
}

.warning-banner {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.2);
  border-radius: 10px;
  margin-bottom: 20px;
  color: #d97706;
}

.warning-banner p {
  font-size: 13px;
  margin: 0;
  line-height: 1.5;
}

.key-copy-field {
  display: flex;
  gap: 8px;
}

.key-display {
  flex: 1;
  padding: 12px 14px;
  font-family: monospace;
  font-size: 13px;
  background: var(--chat-bg-primary);
  border: 1px solid var(--chat-bg-tertiary);
  border-radius: 8px;
  color: var(--chat-text-primary);
  outline: none;
}

.copy-btn {
  flex-shrink: 0;
}

.delete-warning {
  font-size: 14px;
  color: var(--chat-text-secondary);
  line-height: 1.6;
  margin: 0;
}

.delete-warning strong {
  color: var(--chat-text-primary);
}

/* Responsive */
@media (max-width: 768px) {
  .settings-page {
    padding: 16px;
  }

  .settings-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions .reset-btn,
  .header-actions .save-btn {
    flex: 1;
  }

  .card-header {
    padding: 16px 20px;
  }

  .card-body {
    padding: 20px;
  }

  .toggle-content {
    gap: 10px;
  }

  .toggle-icon-wrapper {
    width: 28px;
    height: 28px;
  }

  .danger-action {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
  }
}
</style>
