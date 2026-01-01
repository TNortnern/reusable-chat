<template>
  <div class="min-h-screen flex items-center justify-center bg-[var(--chat-bg-primary)] p-4">
    <UCard class="w-full max-w-md">
      <template #header>
        <h1 class="text-2xl font-bold text-center text-[var(--chat-text-primary)]">
          Sign in to Chat Admin
        </h1>
      </template>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <UFormGroup label="Email" name="email">
          <UInput
            v-model="email"
            type="email"
            placeholder="admin@example.com"
            required
            :disabled="loading"
          />
        </UFormGroup>

        <UFormGroup label="Password" name="password">
          <UInput
            v-model="password"
            type="password"
            placeholder="********"
            required
            :disabled="loading"
          />
        </UFormGroup>

        <UAlert v-if="error" color="red" :title="error" />

        <UButton
          type="submit"
          block
          :loading="loading"
        >
          Sign in
        </UButton>
      </form>
    </UCard>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'default',
})

const { login } = useAuth()
const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    await login(email.value, password.value)
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>
