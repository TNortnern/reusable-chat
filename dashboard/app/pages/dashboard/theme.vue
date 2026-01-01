<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-[var(--chat-text-primary)]">Theme</h1>
      <p class="text-[var(--chat-text-secondary)] mt-1">Customize the appearance of your dashboard</p>
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
      <UButton variant="ghost" @click="resetTheme">
        Reset to Default
      </UButton>
      <UButton color="primary" @click="saveTheme" :loading="saving">
        Save Theme
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'dashboard'
})

const saving = ref(false)

const theme = ref({
  colorScheme: 'default',
  accentColor: '#2563eb',
  fontFamily: 'Inter, system-ui, sans-serif',
  fontSize: 'base',
  darkMode: false,
  compactMode: false,
  animations: true
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

const saveTheme = async () => {
  saving.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 500))
    console.log('Theme saved:', theme.value)
  } finally {
    saving.value = false
  }
}

const resetTheme = () => {
  theme.value = {
    colorScheme: 'default',
    accentColor: '#2563eb',
    fontFamily: 'Inter, system-ui, sans-serif',
    fontSize: 'base',
    darkMode: false,
    compactMode: false,
    animations: true
  }
}
</script>
