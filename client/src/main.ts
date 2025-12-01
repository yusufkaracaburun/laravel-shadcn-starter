import { createApp } from 'vue'
import { nextTick } from 'vue'

import App from './App.vue'
import { setupPlugins } from './plugins'
import { useAuth } from './composables/use-auth'

import '@/assets/index.css'
import '@/assets/scrollbar.css'
import '@/assets/themes.css'
import '@/assets/chart-theme.css'
import 'vue-sonner/style.css' // vue sonner style

import '@/utils/env'

async function bootstrap() {
  const app = createApp(App)

  setupPlugins(app)

  app.mount('#app')

  // Check authentication status after app is mounted and Pinia is ready
  // This will fetch current user if session cookies exist
  await nextTick()
  try {
    const { checkAuth } = useAuth()
    await checkAuth()
  }
  catch (error) {
    // Silently fail - user is not authenticated
    console.debug('No authenticated session found')
  }
}

bootstrap()
