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
  }
  catch {
    // Ignore date parsing errors
  }
  return ''
}
