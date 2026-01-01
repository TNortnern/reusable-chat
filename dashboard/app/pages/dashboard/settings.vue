<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-[var(--chat-text-primary)]">Settings</h1>
      <p class="text-[var(--chat-text-secondary)] mt-1">Configure your workspace and chat features</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Settings -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Workspace Settings -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Workspace</h2>
          </template>

          <div class="space-y-4">
            <UFormGroup label="Workspace Name">
              <UInput v-model="settings.workspaceName" placeholder="My Workspace" />
            </UFormGroup>

            <UFormGroup label="Workspace Slug">
              <UInput v-model="settings.workspaceSlug" placeholder="my-workspace" />
              <template #hint>
                Used in widget embed code and API URLs
              </template>
            </UFormGroup>
          </div>
        </UCard>

        <!-- Chat Features -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Chat Features</h2>
          </template>

          <div class="space-y-4">
            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">Read Receipts</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Show when messages have been read</div>
              </div>
              <UToggle v-model="settings.readReceipts" />
            </div>

            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">Typing Indicators</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Show when someone is typing</div>
              </div>
              <UToggle v-model="settings.typingIndicators" />
            </div>

            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">Online Status</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Show when users are online</div>
              </div>
              <UToggle v-model="settings.onlineStatus" />
            </div>

            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">File Attachments</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Allow users to send files</div>
              </div>
              <UToggle v-model="settings.fileAttachments" />
            </div>

            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">Emoji Reactions</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Allow reactions on messages</div>
              </div>
              <UToggle v-model="settings.emojiReactions" />
            </div>
          </div>
        </UCard>

        <!-- Rate Limiting -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Rate Limiting</h2>
          </template>

          <div class="space-y-4">
            <UFormGroup label="Messages per minute">
              <UInput v-model.number="settings.rateLimit" type="number" min="1" max="100" />
              <template #hint>
                Maximum messages a user can send per minute
              </template>
            </UFormGroup>

            <UFormGroup label="Max file size (MB)">
              <UInput v-model.number="settings.maxFileSize" type="number" min="1" max="50" />
              <template #hint>
                Maximum file attachment size in megabytes
              </template>
            </UFormGroup>
          </div>
        </UCard>

        <!-- Webhooks -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Webhooks</h2>
          </template>

          <div class="space-y-4">
            <UFormGroup label="Webhook URL">
              <UInput v-model="settings.webhookUrl" placeholder="https://your-app.com/webhook" />
              <template #hint>
                Receive real-time notifications for chat events
              </template>
            </UFormGroup>

            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">New Message Events</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Notify when new messages are sent</div>
              </div>
              <UToggle v-model="settings.webhookNewMessage" />
            </div>

            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">User Events</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Notify when users join or leave</div>
              </div>
              <UToggle v-model="settings.webhookUserEvents" />
            </div>
          </div>
        </UCard>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- API Keys -->
        <UCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-[var(--chat-text-primary)]">API Keys</h2>
              <UButton size="xs" @click="createApiKey">
                <UIcon name="i-heroicons-plus" />
                New
              </UButton>
            </div>
          </template>

          <div class="space-y-3">
            <div v-for="key in apiKeys" :key="key.id" class="p-3 bg-[var(--chat-bg-primary)] rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium text-[var(--chat-text-primary)] text-sm">{{ key.name }}</div>
                  <div class="text-xs text-[var(--chat-text-secondary)] font-mono mt-1">
                    {{ key.keyPreview }}
                  </div>
                </div>
                <UButton
                  icon="i-heroicons-trash"
                  size="xs"
                  variant="ghost"
                  color="red"
                  @click="deleteApiKey(key.id)"
                />
              </div>
              <div class="text-xs text-[var(--chat-text-secondary)] mt-2">
                Created {{ key.createdAt }}
              </div>
            </div>
          </div>
        </UCard>

        <!-- Danger Zone -->
        <UCard>
          <template #header>
            <h2 class="text-lg font-semibold text-red-600">Danger Zone</h2>
          </template>

          <div class="space-y-3">
            <UButton block variant="soft" color="red" @click="clearAllData">
              <UIcon name="i-heroicons-trash" />
              Clear All Data
            </UButton>
            <UButton block variant="soft" color="red" @click="deleteWorkspace">
              <UIcon name="i-heroicons-exclamation-triangle" />
              Delete Workspace
            </UButton>
          </div>
        </UCard>
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end gap-3">
      <UButton variant="ghost" @click="resetSettings">
        Reset Changes
      </UButton>
      <UButton color="primary" @click="saveSettings" :loading="saving">
        Save Settings
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'dashboard'
})

const saving = ref(false)

const settings = ref({
  workspaceName: 'My Workspace',
  workspaceSlug: 'my-workspace',
  readReceipts: true,
  typingIndicators: true,
  onlineStatus: true,
  fileAttachments: true,
  emojiReactions: true,
  rateLimit: 30,
  maxFileSize: 10,
  webhookUrl: '',
  webhookNewMessage: true,
  webhookUserEvents: false
})

const apiKeys = ref([
  {
    id: '1',
    name: 'Production Key',
    keyPreview: 'sk_live_xxxx...xxxx',
    createdAt: '2 weeks ago'
  },
  {
    id: '2',
    name: 'Development Key',
    keyPreview: 'sk_test_xxxx...xxxx',
    createdAt: '1 month ago'
  }
])

const saveSettings = async () => {
  saving.value = true
  try {
    // TODO: API call to save settings
    await new Promise(resolve => setTimeout(resolve, 500))
    console.log('Settings saved:', settings.value)
  } finally {
    saving.value = false
  }
}

const resetSettings = () => {
  // TODO: Reset to original values from API
  console.log('Reset settings')
}

const createApiKey = () => {
  console.log('Create new API key')
}

const deleteApiKey = (id: string) => {
  console.log('Delete API key:', id)
}

const clearAllData = () => {
  console.log('Clear all data')
}

const deleteWorkspace = () => {
  console.log('Delete workspace')
}
</script>
