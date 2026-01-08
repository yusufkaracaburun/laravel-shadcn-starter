import { formatMoney } from './formatters'

/**
 * Calculate totals for a single invoice item
 */
export function calculateItemTotals(
  quantity: number,
  unitPrice: number,
  vatRate: number,
): {
  totalExclVat: number
  totalVat: number
  totalInclVat: number
} {
  const totalExclVat = unitPrice * quantity
  const totalVat = totalExclVat * (vatRate / 100)
  const totalInclVat = totalExclVat + totalVat

  return {
    totalExclVat,
    totalVat,
    totalInclVat,
  }
}

/**
 * Extract numeric value from Money object or number
 */
function extractMoneyValue(
  value: number | { amount?: string, formatted?: string },
): number {
  if (typeof value === 'number') {
    return value
  }
  if (typeof value === 'object' && value !== null) {
    if ('amount' in value && typeof value.amount === 'string') {
      // Money object: amount is in cents, convert to decimal
      return Number.parseFloat(value.amount) / 100
    }
  }
  return 0
}

/**
 * Calculate invoice totals from items
 * Groups VAT by rate (0%, 9%, 21%)
 * Accepts both InvoiceItem[] (from API) and local items with number values
 */
export function calculateInvoiceTotals(
  items: Array<{
    total_excl_vat: number | { amount?: string, formatted?: string }
    total_vat: number | { amount?: string, formatted?: string }
    total_incl_vat: number | { amount?: string, formatted?: string }
    vat_rate: number
  }>,
): {
  subtotal: number
  totalVat0: number
  totalVat9: number
  totalVat21: number
  total: number
} {
  let subtotal = 0
  let totalVat0 = 0
  let totalVat9 = 0
  let totalVat21 = 0
  let total = 0

  for (const item of items) {
    const itemTotalExclVat = extractMoneyValue(item.total_excl_vat)
    const itemTotalVat = extractMoneyValue(item.total_vat)
    const itemTotalInclVat = extractMoneyValue(item.total_incl_vat)
    // Ensure vat_rate is a number for comparison
    const vatRate =
      typeof item.vat_rate === 'string'
        ? Number.parseFloat(item.vat_rate)
        : Number(item.vat_rate)

    subtotal += itemTotalExclVat

    // Group VAT by rate (use Number comparison to handle string/number conversion)
    if (Number(vatRate) === 0) {
      totalVat0 += itemTotalVat
    } else if (Number(vatRate) === 9) {
      totalVat9 += itemTotalVat
    } else if (Number(vatRate) === 21) {
      totalVat21 += itemTotalVat
    }

    total += itemTotalInclVat
  }

  return {
    subtotal,
    totalVat0,
    totalVat9,
    totalVat21,
    total,
  }
}

/**
 * Format calculated totals as Money objects (for display)
 */
export function formatCalculatedTotals(totals: {
  subtotal: number
  totalVat0: number
  totalVat9: number
  totalVat21: number
  total: number
}) {
  return {
    subtotal: toMoneyObject(totals.subtotal),
    totalVat0: toMoneyObject(totals.totalVat0),
    totalVat9: toMoneyObject(totals.totalVat9),
    totalVat21: toMoneyObject(totals.totalVat21),
    total: toMoneyObject(totals.total),
  }
}

/**
 * Converts a number to a Money object format.
 */
export function toMoneyObject(value: number): {
  amount: string
  currency: string
  formatted: string
} {
  const formatted = formatMoney(value)
  return {
    amount: value.toFixed(2),
    currency: 'EUR', // Assuming EUR as default currency based on other code
    formatted,
  }
}
