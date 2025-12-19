import { getI18nInstance } from '@/plugins/i18n/setup'

/**
 * Get i18n instance for use in utility functions
 */
function getI18n() {
  const instance = getI18nInstance()
  if (instance) {
    return instance.global
  }
  return null
}

/**
 * Format money value (handles both Money object and number)
 * Uses vue-i18n to respect current locale setting
 */
export function formatMoney(value: any): string {
  if (typeof value === 'object' && value !== null && 'formatted' in value) {
    return value.formatted
  }
  if (typeof value === 'number') {
    const i18n = getI18n()
    if (i18n) {
      return i18n.n(value, 'currency')
    }
    // Fallback to Dutch formatting if i18n not available
    return new Intl.NumberFormat('nl-NL', {
      style: 'currency',
      currency: 'EUR',
    }).format(value)
  }
  const i18n = getI18n()
  if (i18n) {
    return i18n.n(0, 'currency')
  }
  // Fallback to Dutch formatting if i18n not available
  return new Intl.NumberFormat('nl-NL', {
    style: 'currency',
    currency: 'EUR',
  }).format(0)
}

/**
 * Format number with locale-aware formatting (respects current locale)
 * Accepts number, string, null, or undefined
 */
export function formatNumber(
  value: number | string | null | undefined,
  decimals: number = 2,
): string {
  // Convert value to number if it's a string
  let numValue: number
  if (typeof value === 'string') {
    numValue = Number.parseFloat(value)
  }
  else if (value === null || value === undefined) {
    numValue = 0
  }
  else {
    numValue = Number(value)
  }

  // Handle NaN case
  if (Number.isNaN(numValue)) {
    numValue = 0
  }

  const i18n = getI18n()
  if (i18n) {
    return i18n.n(numValue, {
      style: 'decimal',
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    })
  }
  // Fallback to Dutch formatting if i18n not available
  return new Intl.NumberFormat('nl-NL', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(numValue)
}

/**
 * Parse Dutch formatted number string to number
 * Handles both comma (Dutch) and dot (English) decimal separators
 */
export function parseDutchNumber(value: string): number {
  if (!value || value.trim() === '') {
    return 0
  }
  // Replace dot thousand separators, then replace comma with dot for parsing
  const cleaned = value.replace(/\./g, '').replace(',', '.')
  const parsed = Number.parseFloat(cleaned)
  return Number.isNaN(parsed) ? 0 : parsed
}

/**
 * Format date for display (e.g., "March 10, 2025")
 * Uses vue-i18n to respect current locale setting
 */
export function formatDate(dateString: string | null | undefined): string {
  if (!dateString)
    return '—'
  try {
    const date = new Date(dateString)
    if (!Number.isNaN(date.getTime())) {
      const i18n = getI18n()
      if (i18n) {
        return i18n.d(date, 'long')
      }
      // Fallback to English formatting if i18n not available
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    }
  }
  catch {
    // Ignore parsing errors
  }
  return dateString
}

/**
 * Format date for preview display (shorter format, e.g., "Mar 10, 2025")
 * Uses vue-i18n to respect current locale setting
 */
export function formatDateForPreview(
  dateString: string | null | undefined,
): string {
  if (!dateString)
    return '—'
  try {
    const date = new Date(dateString)
    if (!Number.isNaN(date.getTime())) {
      const i18n = getI18n()
      if (i18n) {
        return i18n.d(date, 'preview')
      }
      // Fallback to English formatting if i18n not available
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    }
  }
  catch {
    // Ignore parsing errors
  }
  return dateString
}

/**
 * Format date for input field (YYYY-MM-DD)
 */
export function formatDateForInput(
  dateString: string | null | undefined,
): string {
  if (!dateString)
    return ''
  // Try parsing as "d-m-Y H:i:s" format first
  if (dateString.includes('-') && dateString.includes(' ')) {
    const [datePart] = dateString.split(' ')
    const [day, month, year] = datePart.split('-')
    if (day && month && year && year.length === 4) {
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
    }
  }
  // Try parsing as "Y-m-d" format
  const date = new Date(dateString)
  if (!Number.isNaN(date.getTime())) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  }
  return dateString
}

/**
 * Format date and time for display (e.g., "March 10, 2025 at 08:20 AM")
 * Uses vue-i18n to respect current locale setting
 */
export function formatDateTime(dateString: string | null | undefined): string {
  if (!dateString)
    return '—'
  try {
    // Try parsing as "d-m-Y H:i:s" format first
    if (dateString.includes('-') && dateString.includes(' ')) {
      const [datePart, timePart] = dateString.split(' ')
      const [day, month, year] = datePart.split('-')
      if (day && month && year && year.length === 4) {
        const date = new Date(`${year}-${month}-${day} ${timePart}`)
        if (!Number.isNaN(date.getTime())) {
          const i18n = getI18n()
          if (i18n) {
            return i18n.d(date, 'long')
          }
          // Fallback to English formatting if i18n not available
          return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
          })
        }
      }
    }
    // Try parsing as ISO format
    const date = new Date(dateString)
    if (!Number.isNaN(date.getTime())) {
      const i18n = getI18n()
      if (i18n) {
        return i18n.d(date, 'long')
      }
      // Fallback to English formatting if i18n not available
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    }
  }
  catch {
    // Ignore parsing errors
  }
  return dateString
}
