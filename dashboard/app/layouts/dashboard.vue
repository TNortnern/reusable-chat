<template>
  <div class="flex h-screen bg-[var(--chat-bg-primary)]">
    <DashboardSidebar />
    <main class="flex-1 overflow-auto">
      <slot />
    </main>
  </div>
</template>
