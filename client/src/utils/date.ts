export function getTodayDate(): string {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function calculateDueDate(invoiceDate: string, dueDays: number): string {
  if (!invoiceDate || !dueDays) {
    return ''
  }
  try {
    const dateObj = new Date(invoiceDate)
    if (!Number.isNaN(dateObj.getTime())) {
      const dueDate = new Date(dateObj)
      dueDate.setDate(dueDate.getDate() + dueDays)
      const year = dueDate.getFullYear()
      const month = String(dueDate.getMonth() + 1).padStart(2, '0')
      const day = String(dueDate.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    }
  } catch {
    // Ignore date parsing errors
  }
  return ''
}

/**
 * Format date from "d-m-Y H:i:s" or "Y-m-d" format
 */
export function formatDate(dateString: string | null | undefined): string {
  if (!dateString) {
    return '-'
  }
  // Try parsing as "d-m-Y H:i:s" format first
  if (dateString.includes('-') && dateString.includes(' ')) {
    const [datePart, timePart] = dateString.split(' ')
    const [day, month, year] = datePart.split('-')
    if (day && month && year && year.length === 4) {
      const date = new Date(`${year}-${month}-${day} ${timePart}`)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    }
  }
  // Try parsing as "Y-m-d" format
  const date = new Date(dateString)
  if (!Number.isNaN(date.getTime())) {
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  }
  return dateString
}
