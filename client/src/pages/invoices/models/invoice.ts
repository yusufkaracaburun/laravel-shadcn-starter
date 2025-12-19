import type { Customer } from '@/services/customers.service'
import type { Item, Money } from '@/services/items.service'

/**
 * Invoice status enum matching backend InvoiceStatus
 */
export type TInvoiceStatus = 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled'

/**
 * Payment status enum matching backend PaymentStatus
 */
export type TPaymentStatus = 'pending' | 'paid' | 'failed' | 'refunded' | 'cancelled'

/**
 * Email status enum matching backend EmailStatus
 */
export type TEmailStatus = 'pending' | 'sent' | 'failed'

/**
 * Payment interface matching backend PaymentResource
 * @see api/app/Http/Resources/PaymentResource.php
 */
export interface IInvoicePayment {
  id: number
  payment_number: string | null
  invoice_id: number
  customer_id: number
  date: string
  amount: Money | number
  method: string | null
  provider: string | null
  provider_reference: string | null
  status_formatted: {
    id: string
    value: string
    label: string
    color: string | null
    style: string | null
  }
  status: TPaymentStatus
  paid_at: string | null
  refunded_at: string | null
  created_at: string
  updated_at: string
}

/**
 * Activity interface matching backend ActivityResource
 * @see api/app/Http/Resources/ActivityResource.php
 */
export interface IInvoiceActivity {
  id: number
  log_name: string | null
  description: string
  subject_id: number
  subject_type: string
  causer_id: number | null
  causer_type: string | null
  causer: {
    id: number
    name: string
    email: string | null
  } | null
  properties: {
    old: Record<string, any>
    attributes: Record<string, any>
  }
  event: string | null
  batch_uuid: string | null
  created_at: string
  updated_at: string
}

/**
 * Email interface matching backend InvoiceEmailResource
 * @see api/app/Http/Resources/InvoiceEmailResource.php
 */
export interface IInvoiceEmail {
  id: number
  hash: string
  headers: string
  subject: string
  content: string | null
  opens: number
  clicks: number
  message_id: string
  sender_email: string
  sender_name: string
  recipient_email: string
  recipient_name: string
  clicked_at: string | null
  opened_at: string | null
  created_at: string
  updated_at: string
}

/**
 * Invoice interface matching backend InvoiceResource exactly
 * @see api/app/Http/Resources/InvoiceResource.php
 * Note: Customer is loaded when include is used
 * Note: Items are loaded when include=items is used
 * Note: Payments, activities, and emails are loaded when include is used
 */
export interface IInvoice {
  id: number
  customer_id: number
  customer?: Customer // When loaded via include
  invoice_number: string | null
  date: string // Date format from backend
  due_days: number
  date_due: string // Date format from backend
  status: TInvoiceStatus
  subtotal: Money | number
  total_vat_0: Money | number
  total_vat_9: Money | number
  total_vat_21: Money | number
  total: Money | number
  notes: string | null
  items?:
    | Array<{
      id: number
      invoice_id: number
      description: string | null
      quantity: number
      unit: string | null
      unit_price: Money | number
      vat_rate: number
      total_excl_vat: Money | number
      total_vat: Money | number
      total_incl_vat: Money | number
      sort_order: number
      created_at: string
      updated_at: string
    }>
    | {
      data: Array<{
        id: number
        invoice_id: number
        description: string | null
        quantity: number
        unit: string | null
        unit_price: Money | number
        vat_rate: number
        total_excl_vat: Money | number
        total_vat: Money | number
        total_incl_vat: Money | number
        sort_order: number
        created_at: string
        updated_at: string
      }>
    } // When loaded via include=items - backend returns paginated structure
  payments?: IInvoicePayment[] // When loaded via include=payments
  activities?: IInvoiceActivity[] // When loaded via include=activities
  emails?: IInvoiceEmail[] // When loaded via include=emails
  created_at: string // Format: \"d-m-Y H:i:s\"
  updated_at: string // Format: \"d-m-Y H:i:s\"
  [key: string]: unknown
}

/**
 * Paginated invoices response interface matching Laravel\'s pagination JSON structure
 * @see https://laravel.com/docs/12.x/pagination#converting-results-to-json
 * @see api/app/Http/Controllers/Api/InvoiceController.php::index()\
 */
export interface IPaginatedInvoicesResponse {
  data: IInvoice[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  first_page_url: string
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  path: string
  from: number | null
  to: number | null
}

/**
 * Invoice filters interface matching backend filter structure
 * @see api/app/Http/Controllers/Api/InvoiceController.php::index()
 */
export interface IInvoiceFilters {
  id?: number
  customer_id?: number
  status?: TInvoiceStatus
  invoice_number?: string
  date?: string
  date_due?: string
  between?: string // Format: \"YYYY-MM-DD,YYYY-MM-DD\"
  search?: string
}

/**
 * Create invoice request interface matching backend validation
 * @see api/app/Http/Requests/Invoices/InvoiceStoreRequest.php
 */
export interface ICreateInvoiceRequest {
  customer_id: number
  invoice_number?: string | null
  date: string // Format: \"YYYY-MM-DD\"
  due_days?: number
  date_due?: string // Format: \"YYYY-MM-DD\"
  status?: TInvoiceStatus
  subtotal?: number
  total_vat_0?: number
  total_vat_9?: number
  total_vat_21?: number
  total?: number
  notes?: string | null
}

/**
 * Update invoice request interface matching backend validation
 * @see api/app/Http/Requests/Invoices/InvoiceUpdateRequest.php
 */
export interface IUpdateInvoiceRequest {
  customer_id?: number
  invoice_number?: string | null
  date?: string // Format: \"YYYY-MM-DD\"
  due_days?: number
  date_due?: string // Format: \"YYYY-MM-DD\"
  status?: TInvoiceStatus
  subtotal?: number
  total_vat_0?: number
  total_vat_9?: number
  total_vat_21?: number
  total?: number
  notes?: string | null
}

/**
 * Invoice prerequisites interface
 * Note: items and customers are Laravel ResourceCollections which serialize to { data: T[] }
 */
export interface IInvoicePrerequisites {
  items: Item[] | { data: Item[] }
  customers: Customer[] | { data: Customer[] }
  next_invoice_number: string
}
