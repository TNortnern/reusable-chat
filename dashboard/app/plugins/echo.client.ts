import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()

  // Make Pusher available globally for Echo
  window.Pusher = Pusher as any

  const apiUrl = config.public.apiUrl || 'http://localhost:3021'

  const echo = new Echo({
    broadcaster: 'reverb',
    key: config.public.reverbKey || 'chat-app-key',
    wsHost: config.public.reverbHost || 'localhost',
    wsPort: parseInt(config.public.reverbPort as string) || 8080,
    wssPort: parseInt(config.public.reverbPort as string) || 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    authEndpoint: `${apiUrl}/api/widget/broadcasting/auth`,
  })

  return {
    provide: {
      echo
    }
  }
})

declare global {
  interface Window {
    Pusher: any
  }
}
