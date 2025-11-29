import type { App } from 'vue'

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

import env from '@/utils/env'

let echoInstance: Echo | null = null

export function setupEcho(app: App) {
  // Only initialize if Reverb is enabled and all required config is present
  const isEnabled = env.VITE_REVERB_ENABLED === 'true' || env.VITE_REVERB_ENABLED === '1'
  if (!isEnabled) {
    return
  }

  const appKey = env.VITE_REVERB_APP_KEY
  const host = env.VITE_REVERB_HOST
  const port = env.VITE_REVERB_PORT ?? 9999
  const forceTLS = (env.VITE_REVERB_SCHEME ?? 'http') === 'https'
  if (!appKey || !host) {
    console.warn('Reverb is enabled but missing required configuration (VITE_REVERB_APP_KEY, VITE_REVERB_HOST)')
    return
  }

  // Configure Pusher for Reverb
  window.Pusher = Pusher

  echoInstance = new Echo({
    broadcaster: 'reverb',
    key: appKey,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    authEndpoint: `${env.VITE_SERVER_API_URL}${env.VITE_SERVER_API_PREFIX}/broadcasting/auth`,
    auth: {
      headers: {
        Accept: 'application/json',
      },
      withCredentials: true, // Required for Sanctum authentication
    },
  })

  // Make Echo available globally
  app.config.globalProperties.$echo = echoInstance
}

export function getEcho(): Echo | null {
  return echoInstance
}

// Extend Window interface for TypeScript
declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: typeof Echo
  }
}
