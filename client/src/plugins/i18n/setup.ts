import type { App } from 'vue'

import { createI18n } from 'vue-i18n'

import en from './en.json'
import nl from './nl.json'
import zh from './zh.json'

const numberFormats = {
  en: {
    currency: {
      style: 'currency',
      currency: 'EUR',
    },
    decimal: {
      style: 'decimal',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    },
  },
  nl: {
    currency: {
      style: 'currency',
      currency: 'EUR',
    },
    decimal: {
      style: 'decimal',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    },
  },
  zh: {
    currency: {
      style: 'currency',
      currency: 'EUR',
    },
    decimal: {
      style: 'decimal',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    },
  },
}

const datetimeFormats = {
  en: {
    short: {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    },
    long: {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    },
    preview: {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    },
  },
  nl: {
    short: {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    },
    long: {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    },
    preview: {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    },
  },
  zh: {
    short: {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    },
    long: {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    },
    preview: {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    },
  },
}

let i18nInstance: ReturnType<typeof createI18n> | null = null

export function setupI18n(app: App) {
  i18nInstance = createI18n({
    legacy: false,
    locale: 'nl',
    fallbackLocale: 'en',
    messages: {
      en,
      nl,
      zh,
    },
    numberFormats,
    datetimeFormats,
  })
  app.use(i18nInstance)
}

export function getI18nInstance() {
  return i18nInstance
}
