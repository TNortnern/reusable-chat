import type { Admin, Workspace } from '~/types'

interface AuthState {
  admin: Admin | null
  token: string | null
  currentWorkspace: Workspace | null
}

export const useAuth = () => {
  const config = useRuntimeConfig()
  const state = useState<AuthState>('auth', () => ({
    admin: null,
    token: null,
    currentWorkspace: null,
  }))

  const isAuthenticated = computed(() => !!state.value.token)

  const login = async (email: string, password: string) => {
    const { data, error } = await useFetch(`${config.public.apiUrl}/api/dashboard/auth/login`, {
      method: 'POST',
      body: { email, password },
    })

    if (error.value) {
      throw new Error(error.value.data?.error || 'Login failed')
    }

    state.value.admin = data.value.admin
    state.value.token = data.value.token

    // Store token
    if (import.meta.client) {
      localStorage.setItem('auth_token', data.value.token)
    }

    return data.value
  }

  const logout = async () => {
    try {
      await $fetch(`${config.public.apiUrl}/api/dashboard/auth/logout`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${state.value.token}` },
      })
    } catch (e) {
      // Ignore errors on logout
    }

    state.value.admin = null
    state.value.token = null
    state.value.currentWorkspace = null

    if (import.meta.client) {
      localStorage.removeItem('auth_token')
    }

    navigateTo('/login')
  }

  const fetchMe = async () => {
    if (!state.value.token) return null

    try {
      const data = await $fetch(`${config.public.apiUrl}/api/dashboard/auth/me`, {
        headers: { Authorization: `Bearer ${state.value.token}` },
      })
      state.value.admin = data
      return data
    } catch (e) {
      state.value.token = null
      return null
    }
  }

  const setWorkspace = (workspace: Workspace) => {
    state.value.currentWorkspace = workspace
  }

  // Initialize from localStorage
  if (import.meta.client) {
    const savedToken = localStorage.getItem('auth_token')
    if (savedToken && !state.value.token) {
      state.value.token = savedToken
    }
  }

  return {
    admin: computed(() => state.value.admin),
    token: computed(() => state.value.token),
    currentWorkspace: computed(() => state.value.currentWorkspace),
    isAuthenticated,
    login,
    logout,
    fetchMe,
    setWorkspace,
  }
}
