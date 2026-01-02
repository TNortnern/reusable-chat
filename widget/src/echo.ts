import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo<any>
  }
}

export interface EchoConfig {
  wsHost: string
  wsPort: number
  wsKey: string
  apiUrl: string
  token: string
  forceTLS?: boolean
}

export function createEcho(config: EchoConfig): Echo<any> {
  window.Pusher = Pusher

  const isProduction = config.wsHost !== 'localhost'

  return new Echo({
    broadcaster: 'reverb',
    key: config.wsKey,
    wsHost: config.wsHost,
    wsPort: isProduction ? 443 : config.wsPort,
    wssPort: isProduction ? 443 : config.wsPort,
    forceTLS: config.forceTLS ?? isProduction,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    authEndpoint: `${config.apiUrl}/api/widget/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${config.token}`,
      },
    },
    authorizer: (channel: any) => ({
      authorize: (socketId: string, callback: (error: Error | null, data: any) => void) => {
        const params = new URLSearchParams({
          socket_id: socketId,
          channel_name: channel.name,
          auth_token: config.token,
        })

        fetch(`${config.apiUrl}/api/widget/broadcasting/auth`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: params.toString(),
        })
          .then((response) => {
            if (!response.ok) {
              return response.json().then((err) => {
                throw new Error(err.error || 'Auth failed')
              })
            }
            return response.json()
          })
          .then((data) => callback(null, data))
          .catch((error) => callback(error, null))
      },
    }),
  })
}
