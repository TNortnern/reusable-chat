<template>
  <div class="min-h-screen bg-[var(--chat-bg-primary)]">
    <div class="max-w-7xl mx-auto p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-[var(--chat-text-primary)] mb-2">
          Widget Customization
        </h1>
        <p class="text-[var(--chat-text-secondary)]">
          Customize your chat widget appearance and preview it in real-time
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Controls Panel -->
        <div class="space-y-6">
          <!-- Theme Presets -->
          <UCard>
            <template #header>
              <h2 class="text-lg font-semibold text-[var(--chat-text-primary)]">
                Theme Preset
              </h2>
            </template>

            <div class="grid grid-cols-2 gap-3">
              <button
                v-for="preset in themePresets"
                :key="preset.name"
                @click="applyPreset(preset)"
                class="preset-button"
                :class="{ active: currentPreset === preset.name }"
              >
                <div class="preset-colors">
                  <div
                    class="color-dot"
                    :style="{ background: preset.primaryColor }"
                  />
                  <div
                    class="color-dot"
                    :style="{ background: preset.backgroundColor }"
                  />
                </div>
                <span class="preset-name">{{ preset.name }}</span>
              </button>
            </div>
          </UCard>

          <!-- Color Customization -->
          <UCard>
            <template #header>
              <h2 class="text-lg font-semibold text-[var(--chat-text-primary)]">
                Colors
              </h2>
            </template>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-[var(--chat-text-primary)] mb-2">
                  Primary Color
                </label>
                <div class="flex gap-3 items-center">
                  <input
                    v-model="widgetSettings.primaryColor"
                    type="color"
                    class="color-picker"
                    @input="currentPreset = 'custom'"
                  />
                  <UInput
                    v-model="widgetSettings.primaryColor"
                    class="flex-1"
                    placeholder="#2563eb"
                    @input="currentPreset = 'custom'"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-[var(--chat-text-primary)] mb-2">
                  Background Color
                </label>
                <div class="flex gap-3 items-center">
                  <input
                    v-model="widgetSettings.backgroundColor"
                    type="color"
                    class="color-picker"
                    @input="currentPreset = 'custom'"
                  />
                  <UInput
                    v-model="widgetSettings.backgroundColor"
                    class="flex-1"
                    placeholder="#ffffff"
                    @input="currentPreset = 'custom'"
                  />
                </div>
              </div>
            </div>
          </UCard>

          <!-- Appearance Settings -->
          <UCard>
            <template #header>
              <h2 class="text-lg font-semibold text-[var(--chat-text-primary)]">
                Appearance
              </h2>
            </template>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-[var(--chat-text-primary)] mb-2">
                  Position
                </label>
                <USelect
                  v-model="widgetSettings.position"
                  :options="positionOptions"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-[var(--chat-text-primary)] mb-2">
                  Font Family
                </label>
                <USelect
                  v-model="widgetSettings.fontFamily"
                  :options="fontOptions"
                />
              </div>

              <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-[var(--chat-text-primary)]">
                  Dark Mode
                </label>
                <UToggle v-model="widgetSettings.darkMode" />
              </div>
            </div>
          </UCard>

          <!-- Branding -->
          <UCard>
            <template #header>
              <h2 class="text-lg font-semibold text-[var(--chat-text-primary)]">
                Branding
              </h2>
            </template>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-[var(--chat-text-primary)] mb-2">
                  Logo URL
                </label>
                <UInput
                  v-model="widgetSettings.logoUrl"
                  placeholder="https://example.com/logo.png"
                />
                <p class="text-xs text-[var(--chat-text-secondary)] mt-1">
                  Recommended: Square image, at least 80x80px
                </p>
              </div>
            </div>
          </UCard>

          <!-- Embed Code -->
          <UCard>
            <template #header>
              <h2 class="text-lg font-semibold text-[var(--chat-text-primary)]">
                Embed Code
              </h2>
            </template>

            <div class="space-y-3">
              <div class="embed-code-container">
                <pre class="embed-code">{{ embedCode }}</pre>
              </div>
              <div class="flex gap-2">
                <UButton
                  icon="i-heroicons-clipboard-document"
                  @click="copyEmbedCode"
                  block
                >
                  {{ copied ? 'Copied!' : 'Copy Embed Code' }}
                </UButton>
              </div>
            </div>
          </UCard>

          <!-- Actions -->
          <div class="flex gap-3">
            <UButton
              icon="i-heroicons-check"
              color="primary"
              size="lg"
              block
              @click="saveSettings"
            >
              Save Settings
            </UButton>
            <UButton
              icon="i-heroicons-arrow-path"
              variant="outline"
              size="lg"
              @click="resetSettings"
            >
              Reset
            </UButton>
          </div>
        </div>

        <!-- Preview Panel -->
        <div class="lg:sticky lg:top-6 lg:h-[calc(100vh-3rem)]">
          <UCard class="h-full">
            <template #header>
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-[var(--chat-text-primary)]">
                  Live Preview
                </h2>
                <UBadge color="green" variant="subtle">
                  Real-time
                </UBadge>
              </div>
            </template>

            <div class="preview-wrapper">
              <DashboardWidgetPreview :settings="widgetSettings" />
            </div>
          </UCard>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'dashboard'
})

interface WidgetSettings {
  primaryColor: string
  backgroundColor: string
  position: 'bottom-right' | 'bottom-left'
  darkMode: boolean
  logoUrl: string
  fontFamily: string
}

interface ThemePreset {
  name: string
  primaryColor: string
  backgroundColor: string
}

const { currentWorkspace } = useAuth()

// Widget Settings
const widgetSettings = ref<WidgetSettings>({
  primaryColor: '#2563eb',
  backgroundColor: '#ffffff',
  position: 'bottom-right',
  darkMode: false,
  logoUrl: '',
  fontFamily: 'Inter, system-ui, sans-serif',
})

// Current preset tracking
const currentPreset = ref('minimal')

// Theme Presets
const themePresets: ThemePreset[] = [
  {
    name: 'Minimal',
    primaryColor: '#2563eb',
    backgroundColor: '#ffffff',
  },
  {
    name: 'Playful',
    primaryColor: '#ec4899',
    backgroundColor: '#fdf2f8',
  },
  {
    name: 'Professional',
    primaryColor: '#0f172a',
    backgroundColor: '#f8fafc',
  },
  {
    name: 'Ocean',
    primaryColor: '#0891b2',
    backgroundColor: '#ecfeff',
  },
  {
    name: 'Forest',
    primaryColor: '#059669',
    backgroundColor: '#f0fdf4',
  },
  {
    name: 'Sunset',
    primaryColor: '#ea580c',
    backgroundColor: '#fff7ed',
  },
]

// Options
const positionOptions = [
  { label: 'Bottom Right', value: 'bottom-right' },
  { label: 'Bottom Left', value: 'bottom-left' },
]

const fontOptions = [
  { label: 'Inter (Default)', value: 'Inter, system-ui, sans-serif' },
  { label: 'Satoshi', value: 'Satoshi, system-ui, sans-serif' },
  { label: 'System UI', value: 'system-ui, sans-serif' },
  { label: 'Arial', value: 'Arial, sans-serif' },
  { label: 'Georgia', value: 'Georgia, serif' },
  { label: 'Monospace', value: 'monospace' },
]

// Embed Code
const embedCode = computed(() => {
  const workspaceId = currentWorkspace.value?.id || 'YOUR_WORKSPACE_ID'
  return `<script src="https://cdn.chatplatform.com/widget.js" data-workspace="${workspaceId}"><\/script>`
})

// Copy to clipboard
const copied = ref(false)
const copyEmbedCode = async () => {
  try {
    await navigator.clipboard.writeText(embedCode.value)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

// Apply preset
const applyPreset = (preset: ThemePreset) => {
  widgetSettings.value.primaryColor = preset.primaryColor
  widgetSettings.value.backgroundColor = preset.backgroundColor
  currentPreset.value = preset.name.toLowerCase()
}

// Save settings
const saveSettings = async () => {
  // TODO: Implement API call to save settings
  console.log('Saving settings:', widgetSettings.value)
  alert('Settings saved successfully!')
}

// Reset settings
const resetSettings = () => {
  widgetSettings.value = {
    primaryColor: '#2563eb',
    backgroundColor: '#ffffff',
    position: 'bottom-right',
    darkMode: false,
    logoUrl: '',
    fontFamily: 'Inter, system-ui, sans-serif',
  }
  currentPreset.value = 'minimal'
}

// Load saved settings on mount
onMounted(async () => {
  // TODO: Load settings from API
  console.log('Loading widget settings...')
})
</script>

<style scoped>
/* Preset Button */
.preset-button {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px;
  border: 2px solid var(--chat-bg-tertiary);
  border-radius: var(--chat-radius-md);
  background: var(--chat-bg-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.preset-button:hover {
  border-color: var(--chat-accent);
  transform: translateY(-2px);
}

.preset-button.active {
  border-color: var(--chat-accent);
  background: var(--chat-accent-soft);
}

.preset-colors {
  display: flex;
  gap: 6px;
}

.color-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.5);
  box-shadow: var(--chat-shadow-sm);
}

.preset-name {
  font-size: 13px;
  font-weight: 500;
  color: var(--chat-text-primary);
}

/* Color Picker */
.color-picker {
  width: 48px;
  height: 48px;
  border: 2px solid var(--chat-bg-tertiary);
  border-radius: var(--chat-radius-sm);
  cursor: pointer;
  transition: border-color 0.2s ease;
}

.color-picker:hover {
  border-color: var(--chat-accent);
}

/* Embed Code */
.embed-code-container {
  background: var(--chat-bg-primary);
  border: 1px solid var(--chat-bg-tertiary);
  border-radius: var(--chat-radius-sm);
  padding: 12px;
  overflow-x: auto;
}

.embed-code {
  font-family: monospace;
  font-size: 13px;
  color: var(--chat-text-primary);
  margin: 0;
  white-space: pre-wrap;
  word-break: break-all;
}

/* Preview Wrapper */
.preview-wrapper {
  height: calc(100vh - 200px);
  min-height: 600px;
  border: 1px solid var(--chat-bg-tertiary);
  border-radius: var(--chat-radius-md);
  overflow: hidden;
}
</style>
