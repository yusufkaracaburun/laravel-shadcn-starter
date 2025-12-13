import type { App } from 'vue'

import { createI18n } from 'vue-i18n'

import en from './en.json'
import nl from './nl.json'
import zh from './zh.json'

export function setupI18n(app: App) {
  const i18n = createI18n({
    legacy: false,
    locale: 'nl',
    fallbackLocale: 'en',
    messages: {
      en,
      nl,
      zh,
    },
  })
  app.use(i18n)
}
