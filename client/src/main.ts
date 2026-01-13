import { createApp, nextTick } from 'vue'

import App from './App.vue'
import { useAuth } from './pages/auth/composables/use-auth.composable'
import { setupPlugins } from './plugins'

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

  await nextTick()
  // await checkUserAuth()
}

async function checkUserAuth() {
  try {
    const { checkAuth } = useAuth()
    await checkAuth()
  } catch (error) {
    // Silently fail - user is not authenticated
    console.warn('No authenticated session found', error)
  }
}

bootstrap()
