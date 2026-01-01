<template>
  <div class="min-h-screen flex items-center justify-center">
    <UButton to="/login">Go to Login</UButton>
  </div>
</template>

<script setup lang="ts">
const { isAuthenticated } = useAuth()

onMounted(() => {
  if (isAuthenticated.value) {
    navigateTo('/dashboard')
  } else {
    navigateTo('/login')
  }
})
</script>
