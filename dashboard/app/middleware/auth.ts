export default defineNuxtRouteMiddleware(async (to) => {
  const { isAuthenticated, fetchMe, token } = useAuth()

  // Skip auth check for login page
  if (to.path === '/login') {
    if (isAuthenticated.value) {
      return navigateTo('/dashboard')
    }
    return
  }

  // Check authentication for dashboard routes
  if (to.path.startsWith('/dashboard')) {
    if (!token.value) {
      return navigateTo('/login')
    }

    // Verify token is still valid
    const admin = await fetchMe()
    if (!admin) {
      return navigateTo('/login')
    }
  }
})
