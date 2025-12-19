/**
 * Format money value (Money object or number)
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
  return '€ 0,00'
}
