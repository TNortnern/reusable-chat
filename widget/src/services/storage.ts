const STORAGE_PREFIX = 'rc_'

export const storage = {
  get<T>(key: string): T | null {
    try {
      const item = localStorage.getItem(STORAGE_PREFIX + key)
      return item ? JSON.parse(item) : null
    } catch {
      return null
    }
  },

  set<T>(key: string, value: T): void {
    try {
      localStorage.setItem(STORAGE_PREFIX + key, JSON.stringify(value))
    } catch {
      // localStorage might be full or disabled
    }
  },

  remove(key: string): void {
    try {
      localStorage.removeItem(STORAGE_PREFIX + key)
    } catch {
      // Ignore errors
    }
  },

  getSessionToken(): string | null {
    return this.get<string>('session_token')
  },

  setSessionToken(token: string): void {
    this.set('session_token', token)
  },

  clearSession(): void {
    this.remove('session_token')
    this.remove('user')
  },
}
