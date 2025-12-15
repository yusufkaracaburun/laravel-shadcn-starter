/**
 * Format money value (handles both Money object and number)
 */
export function formatMoney(value: any): string {
  if (typeof value === 'object' && value !== null && 'formatted' in value) {
    return value.formatted
  }
  if (typeof value === 'number') {
    return new Intl.NumberFormat('nl-NL', {
      style: 'currency',
      currency: 'EUR',
    }).format(value)
  }
  return '€0.00'
}

/**
 * Format date for display (e.g., "March 10, 2025")
 */
export function formatDate(dateString: string | null | undefined): string {
  if (!dateString) return '—'
  try {
    const date = new Date(dateString)
    if (!isNaN(date.getTime())) {
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    }
  } catch (error) {
    // Ignore parsing errors
  }
  return dateString
}

/**
 * Format date for preview display (shorter format, e.g., "Mar 10, 2025")
 */
export function formatDateForPreview(dateString: string | null | undefined): string {
  if (!dateString) return '—'
  try {
    const date = new Date(dateString)
    if (!isNaN(date.getTime())) {
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    }
  } catch (error) {
    // Ignore parsing errors
  }
  return dateString
}

/**
 * Format date for input field (YYYY-MM-DD)
 */
export function formatDateForInput(dateString: string | null | undefined): string {
  if (!dateString) return ''
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
  if (!isNaN(date.getTime())) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  }
  return dateString
}

