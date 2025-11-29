import type { App } from 'vue'

import { setupVueSonner } from '@/composables/use-toast'

import { setupAutoAnimate } from './auto-animate/setup'
import { setupDayjs } from './dayjs/setup'
import { setupEcho } from './echo/setup'
import { setupI18n } from './i18n/setup'
import { setupNProgress } from './nprogress/setup'
import { setupPinia } from './pinia/setup'
import { setupReverbListener } from './reverb-listener/setup'
import { setupRouter } from './router/setup'
import { setupTanstackVueQuery } from './tanstack-vue-query/setup'

export function setupPlugins(app: App) {
  setupDayjs()
  setupNProgress()
  setupAutoAnimate(app)
  setupTanstackVueQuery(app)
  setupI18n(app)
  setupPinia(app)
  setupRouter(app)
  setupVueSonner(app)
  setupEcho(app)
  // Setup Reverb listener after Echo and Pinia are initialized
  setupReverbListener(app)
}
