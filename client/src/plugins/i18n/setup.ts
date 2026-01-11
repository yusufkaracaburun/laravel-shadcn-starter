import type { App } from 'vue'

import { createI18n } from 'vue-i18n'

import en from './en'
import nl from './nl'
import zh from './zh'

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

/**
 * Get the translation function from the i18n instance
 * Useful for accessing translations outside of Vue component context
 * (e.g., in utility functions, column definitions, etc.)
 *
 * @returns Translation function that accepts a key and returns the translated string
 * @example
 * const t = getT()
 * const title = t('invoices.columns.invoiceNumber')
 */
export function getT() {
  const i18n = getI18nInstance()
  return i18n?.global.t || ((key: string) => key)
}
