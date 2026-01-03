<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3">
          <h1 class="text-3xl font-bold text-[var(--chat-text-primary)]">Theme</h1>
          <UBadge v-if="hasUnsavedChanges" color="warning" variant="subtle">Unsaved changes</UBadge>
        </div>
        <p class="text-[var(--chat-text-secondary)] mt-1">Customize the appearance of your dashboard</p>
      </div>
      <div v-if="loading" class="flex items-center gap-2 text-[var(--chat-text-secondary)]">
        <UIcon name="i-heroicons-arrow-path" class="w-5 h-5 animate-spin" />
        <span>Loading...</span>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="saveSuccess" class="p-4 rounded-lg bg-green-500/10 border border-green-500/20 flex items-center gap-3">
      <UIcon name="i-heroicons-check-circle" class="w-5 h-5 text-green-500" />
      <span class="text-green-500">Theme saved successfully!</span>
    </div>
    <div v-if="saveError" class="p-4 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center gap-3">
      <UIcon name="i-heroicons-exclamation-circle" class="w-5 h-5 text-red-500" />
      <span class="text-red-500">{{ saveError }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Theme Settings -->
      <div class="space-y-6">
        <!-- Color Scheme -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Color Scheme</h2>
          </template>

          <div class="space-y-4">
            <div class="grid grid-cols-3 gap-3">
              <button
                v-for="scheme in colorSchemes"
                :key="scheme.name"
                class="p-4 rounded-lg border-2 transition-all"
                :class="theme.colorScheme === scheme.name ? 'border-[var(--chat-accent)]' : 'border-transparent bg-[var(--chat-bg-tertiary)]'"
                @click="theme.colorScheme = scheme.name"
              >
                <div class="flex gap-1 mb-2">
                  <div
                    v-for="(color, i) in scheme.colors"
                    :key="i"
                    class="w-4 h-4 rounded-full"
                    :style="{ backgroundColor: color }"
                  />
                </div>
                <div class="text-sm font-medium text-[var(--chat-text-primary)]">{{ scheme.label }}</div>
              </button>
            </div>
          </div>
        </UCard>

        <!-- Accent Color -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Accent Color</h2>
          </template>

          <div class="space-y-4">
            <div class="flex gap-3">
              <button
                v-for="color in accentColors"
                :key="color.value"
                class="w-10 h-10 rounded-full border-2 transition-all"
                :class="theme.accentColor === color.value ? 'border-[var(--chat-text-primary)] scale-110' : 'border-transparent'"
                :style="{ backgroundColor: color.value }"
                @click="theme.accentColor = color.value"
              />
            </div>

            <UFormGroup label="Custom Color">
              <div class="flex gap-3 items-center">
                <input
                  type="color"
                  v-model="theme.accentColor"
                  class="w-10 h-10 rounded cursor-pointer"
                />
                <UInput v-model="theme.accentColor" class="flex-1" />
              </div>
            </UFormGroup>
          </div>
        </UCard>

        <!-- Typography -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Typography</h2>
          </template>

          <div class="space-y-4">
            <UFormGroup label="Font Family">
              <USelect v-model="theme.fontFamily" :options="fontOptions" />
            </UFormGroup>

            <UFormGroup label="Font Size">
              <USelect v-model="theme.fontSize" :options="fontSizeOptions" />
            </UFormGroup>
          </div>
        </UCard>

        <!-- Appearance -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold text-[var(--chat-text-primary)]">Appearance</h2>
          </template>

          <div class="space-y-4">
            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">Dark Mode</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Enable dark theme</div>
              </div>
              <UToggle v-model="theme.darkMode" />
            </div>

            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">Compact Mode</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Reduce spacing and padding</div>
              </div>
              <UToggle v-model="theme.compactMode" />
            </div>

            <div class="flex items-center justify-between py-2">
              <div>
                <div class="font-medium text-[var(--chat-text-primary)]">Animations</div>
                <div class="text-sm text-[var(--chat-text-secondary)]">Enable UI animations</div>
              </div>
              <UToggle v-model="theme.animations" />
            </div>
          </div>
        </UCard>
      </div>

      <!-- Preview -->
      <div class="lg:sticky lg:top-6">
        <UCard class="h-full">
          <template #header>
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-[var(--chat-text-primary)]">Preview</h2>
              <UBadge color="blue" variant="subtle">Live</UBadge>
            </div>
          </template>

          <div
            class="rounded-lg overflow-hidden border"
            :class="theme.darkMode ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'"
          >
            <!-- Preview Header -->
            <div
              class="p-4 border-b"
              :class="theme.darkMode ? 'border-gray-700' : 'border-gray-200'"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-lg flex items-center justify-center text-white"
                  :style="{ backgroundColor: theme.accentColor }"
                >
                  <UIcon name="i-heroicons-chat-bubble-left-right" class="w-5 h-5" />
                </div>
                <div>
                  <div
                    class="font-semibold"
                    :class="theme.darkMode ? 'text-white' : 'text-gray-900'"
                    :style="{ fontFamily: theme.fontFamily }"
                  >
                    Dashboard Preview
                  </div>
                  <div class="text-sm" :class="theme.darkMode ? 'text-gray-400' : 'text-gray-500'">
                    Sample preview content
                  </div>
                </div>
              </div>
            </div>

            <!-- Preview Content -->
            <div class="p-4 space-y-4">
              <div
                class="p-3 rounded-lg"
                :class="theme.darkMode ? 'bg-gray-800' : 'bg-gray-50'"
              >
                <div
                  class="text-sm font-medium mb-2"
                  :class="theme.darkMode ? 'text-white' : 'text-gray-900'"
                  :style="{ fontFamily: theme.fontFamily }"
                >
                  Sample Card
                </div>
                <div
                  class="text-sm"
                  :class="theme.darkMode ? 'text-gray-400' : 'text-gray-500'"
                >
                  This is how your content will look with the selected theme.
                </div>
              </div>

              <div class="flex gap-2">
                <button
                  class="px-4 py-2 rounded-lg text-white text-sm font-medium"
                  :style="{ backgroundColor: theme.accentColor }"
                >
                  Primary Button
                </button>
                <button
                  class="px-4 py-2 rounded-lg text-sm font-medium"
                  :class="theme.darkMode ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-700'"
                >
                  Secondary
                </button>
              </div>

              <div class="flex items-center gap-2">
                <div
                  class="w-3 h-3 rounded-full"
                  :style="{ backgroundColor: theme.accentColor }"
                />
                <span
                  class="text-sm"
                  :class="theme.darkMode ? 'text-gray-300' : 'text-gray-600'"
                >
                  Active status indicator
                </span>
              </div>
            </div>
          </div>
        </UCard>
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end gap-3">
      <UButton variant="ghost" @click="resetTheme" :disabled="saving || loading">
        Reset to Default
      </UButton>
      <UButton color="primary" @click="saveTheme" :loading="saving" :disabled="loading || !hasUnsavedChanges">
        {{ hasUnsavedChanges ? 'Save Theme' : 'Saved' }}
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'dashboard'
})

const config = useRuntimeConfig()
const { token, currentWorkspace } = useAuth()

const saving = ref(false)
const loading = ref(true)
const saveSuccess = ref(false)
const saveError = ref('')

// Default theme values
const defaultTheme = {
  colorScheme: 'default',
  accentColor: '#2563eb',
  fontFamily: 'Inter, system-ui, sans-serif',
  fontSize: 'base',
  darkMode: true,
  compactMode: false,
  animations: true
}

const theme = ref({ ...defaultTheme })

// Track original values to detect changes
const originalTheme = ref({ ...defaultTheme })

const hasUnsavedChanges = computed(() => {
  return JSON.stringify(theme.value) !== JSON.stringify(originalTheme.value)
})

const colorSchemes = [
  { name: 'default', label: 'Default', colors: ['#2563eb', '#3b82f6', '#60a5fa'] },
  { name: 'ocean', label: 'Ocean', colors: ['#0891b2', '#06b6d4', '#22d3ee'] },
  { name: 'forest', label: 'Forest', colors: ['#059669', '#10b981', '#34d399'] },
  { name: 'sunset', label: 'Sunset', colors: ['#ea580c', '#f97316', '#fb923c'] },
  { name: 'purple', label: 'Purple', colors: ['#7c3aed', '#8b5cf6', '#a78bfa'] },
  { name: 'rose', label: 'Rose', colors: ['#e11d48', '#f43f5e', '#fb7185'] }
]

const accentColors = [
  { value: '#2563eb' },
  { value: '#0891b2' },
  { value: '#059669' },
  { value: '#ea580c' },
  { value: '#7c3aed' },
  { value: '#e11d48' },
  { value: '#0f172a' }
]

const fontOptions = [
  { label: 'Inter (Default)', value: 'Inter, system-ui, sans-serif' },
  { label: 'Satoshi', value: 'Satoshi, system-ui, sans-serif' },
  { label: 'System UI', value: 'system-ui, sans-serif' },
  { label: 'Roboto', value: 'Roboto, sans-serif' },
  { label: 'Open Sans', value: 'Open Sans, sans-serif' }
]

const fontSizeOptions = [
  { label: 'Small', value: 'sm' },
  { label: 'Base', value: 'base' },
  { label: 'Large', value: 'lg' }
]

// Map API response to frontend theme format
const mapApiToTheme = (apiTheme: any) => {
  return {
    colorScheme: apiTheme?.preset || 'default',
    accentColor: apiTheme?.primary_color || '#2563eb',
    fontFamily: apiTheme?.font_family || 'Inter, system-ui, sans-serif',
    fontSize: 'base', // Not stored in API, keep as local preference
    darkMode: apiTheme?.dark_mode_enabled ?? true,
    compactMode: false, // Not stored in API, keep as local preference
    animations: true // Not stored in API, keep as local preference
  }
}

// Map frontend theme to API format
const mapThemeToApi = (themeData: typeof theme.value) => {
  return {
    preset: themeData.colorScheme === 'default' ? 'professional' : themeData.colorScheme,
    primary_color: themeData.accentColor,
    font_family: themeData.fontFamily,
    dark_mode_enabled: themeData.darkMode
  }
}

// Fetch theme from API
const fetchTheme = async () => {
  if (!currentWorkspace.value?.id) return

  loading.value = true
  try {
    const data = await $fetch(`${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/theme`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    const mappedTheme = mapApiToTheme(data)
    theme.value = mappedTheme
    originalTheme.value = { ...mappedTheme }
  } catch (e: any) {
    console.error('Failed to fetch theme:', e)
    saveError.value = 'Failed to load theme settings'
  } finally {
    loading.value = false
  }
}

const saveTheme = async () => {
  if (!currentWorkspace.value?.id) return

  saving.value = true
  saveError.value = ''
  saveSuccess.value = false

  try {
    const apiData = mapThemeToApi(theme.value)
    const data = await $fetch(`${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/theme`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` },
      body: apiData
    })
    const mappedTheme = mapApiToTheme(data)
    theme.value = mappedTheme
    originalTheme.value = { ...mappedTheme }
    saveSuccess.value = true

    // Hide success message after 3 seconds
    setTimeout(() => {
      saveSuccess.value = false
    }, 3000)
  } catch (e: any) {
    console.error('Failed to save theme:', e)
    saveError.value = e.data?.message || 'Failed to save theme settings'
  } finally {
    saving.value = false
  }
}

const resetTheme = async () => {
  if (!currentWorkspace.value?.id) return

  saving.value = true
  saveError.value = ''

  try {
    // Reset to default values via API
    const defaultApiData = {
      preset: 'professional',
      primary_color: '#2563eb',
      font_family: 'Inter, system-ui, sans-serif',
      dark_mode_enabled: true
    }
    const data = await $fetch(`${config.public.apiUrl}/api/dashboard/workspaces/${currentWorkspace.value.id}/theme`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token.value}` },
      body: defaultApiData
    })
    const mappedTheme = mapApiToTheme(data)
    theme.value = mappedTheme
    originalTheme.value = { ...mappedTheme }
    saveSuccess.value = true

    setTimeout(() => {
      saveSuccess.value = false
    }, 3000)
  } catch (e: any) {
    console.error('Failed to reset theme:', e)
    saveError.value = 'Failed to reset theme settings'
  } finally {
    saving.value = false
  }
}

// Watch for workspace changes and fetch theme
watch(() => currentWorkspace.value?.id, (newId) => {
  if (newId) {
    fetchTheme()
  }
}, { immediate: true })

// Warn user about unsaved changes before leaving
onBeforeUnmount(() => {
  if (hasUnsavedChanges.value && import.meta.client) {
    // Could add a confirmation dialog here if needed
  }
})
</script>
