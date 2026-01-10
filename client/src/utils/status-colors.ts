/**
 * Global utility functions for status and priority colors
 * Provides consistent color coding across the application
 */

export type PriorityValue = 'low' | 'medium' | 'high'
export type ProjectStatusValue =
  | 'active'
  | 'completed'
  | 'on-hold'
  | 'cancelled'
export type TaskStatusValue =
  | 'backlog'
  | 'todo'
  | 'in progress'
  | 'done'
  | 'canceled'
export type CompanyStatusValue = 'active' | 'inactive' | 'pending'
export type CustomerStatusValue = 'registered' | 'active' | 'inactive'
export type InvoiceStatusValue =
  | 'draft'
  | 'sent'
  | 'paid'
  | 'unpaid'
  | 'cancelled'
  | 'refunded'
  | 'overdue'
  | 'reminder'
  | 'credited'
  | 'partial_paid'
export type PaymentStatusValue =
  | 'pending'
  | 'paid'
  | 'failed'
  | 'refunded'
  | 'cancelled'
/**
 * Get priority color class
 * Matches the kanban view color scheme
 */
export function getPriorityColor(
  priority: PriorityValue | string | undefined,
): string {
  if (!priority)
    return 'text-muted-foreground'

  switch (priority.toLowerCase()) {
    case 'low':
      return 'text-blue-500'
    case 'medium':
      return 'text-warning'
    case 'high':
      return 'text-destructive'
    default:
      return 'text-muted-foreground'
  }
}

/**
 * Get project status color class
 * Returns badge-style background colors for project statuses
 */
export function getProjectStatusColor(
  status: ProjectStatusValue | string | undefined,
): string {
  if (!status)
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'

  switch (status.toLowerCase()) {
    case 'active':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
    case 'completed':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'on-hold':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
    case 'cancelled':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
  }
}

/**
 * Get task status color class
 * Returns badge-style background colors for task statuses
 */
export function getTaskStatusColor(
  status: TaskStatusValue | string | undefined,
): string {
  if (!status)
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'

  switch (status.toLowerCase()) {
    case 'backlog':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
    case 'todo':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
    case 'in progress':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
    case 'done':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'canceled':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
  }
}

/**
 * Get company status color class
 * Returns badge-style background colors for company statuses
 */
export function getCompanyStatusColor(
  status: CompanyStatusValue | string | undefined,
): string {
  if (!status)
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'

  switch (status.toLowerCase()) {
    case 'active':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'inactive':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
  }
}

/**
 * Get customer status color class
 * Returns badge-style background colors for customer statuses
 */
export function getCustomerStatusColor(
  status: CustomerStatusValue | string | undefined,
): string {
  if (!status)
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'

  switch (status.toLowerCase()) {
    case 'registered':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
    case 'active':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'inactive':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
  }
}

/**
 * Get invoice status color class
 * Returns badge-style background colors for invoice statuses
 */
export function getInvoiceStatusColor(
  status: InvoiceStatusValue | string | undefined,
): string {
  if (!status)
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'

  switch (status.toLowerCase()) {
    case 'draft':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
    case 'sent':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
    case 'paid':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'unpaid':
      return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'
    case 'partial_paid':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
    case 'overdue':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    case 'reminder':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300'
    case 'cancelled':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
    case 'refunded':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
    case 'credited':
      return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
  }
}

export function getPaymentStatusColor(
  status: PaymentStatusValue | string | undefined,
): string {
  if (!status)
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'

  switch (status.toLowerCase()) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
    case 'paid':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'failed':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    case 'refunded':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
    case 'cancelled':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
  }
}

/**
 * Generic status color getter that tries to match project or task status
 * Falls back to a default color if no match is found
 */
export function getStatusColor(
  status: string | undefined,
  type: 'project' | 'task' = 'project',
): string {
  if (!status)
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'

  if (type === 'project') {
    return getProjectStatusColor(status as ProjectStatusValue)
  }
  return getTaskStatusColor(status as TaskStatusValue)
}
