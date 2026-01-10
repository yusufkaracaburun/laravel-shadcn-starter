import type { Customer } from '@/pages/customers/services/customers.service'
import type { Item, Money } from '@/pages/items/services/items.service'

export enum EInvoiceStatus {
  DRAFT = 'draft',
  SENT = 'sent',
  PAID = 'paid',
  UNPAID = 'unpaid',
  CANCELLED = 'cancelled',
  REFUNDED = 'refunded',
  OVERDUE = 'overdue',
  REMINDER = 'reminder',
  CREDITED = 'credited',
  PARTIAL_PAID = 'partial_paid',
}
export type TInvoiceStatus =
  (typeof EInvoiceStatus)[keyof typeof EInvoiceStatus]

export enum EPaymentStatus {
  PENDING = 'pending',
  PAID = 'paid',
  FAILED = 'failed',
  REFUNDED = 'refunded',
  CANCELLED = 'cancelled',
}
export type TPaymentStatus =
  (typeof EPaymentStatus)[keyof typeof EPaymentStatus]

export enum EEmailStatus {
  PENDING = 'pending',
  SENT = 'sent',
  FAILED = 'failed',
}
export type TEmailStatus = (typeof EEmailStatus)[keyof typeof EEmailStatus]

export interface IInvoiceFilters {
  id?: number
  customer_id?: number
  status?: TInvoiceStatus
  invoice_number?: string
  date?: string
  date_due?: string
  between?: string
  search?: string
}

interface IStatusFormatted {
  id: string
  value: string
  label: string
  color: string | null
  style: string | null
}
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
  status_formatted: IStatusFormatted
  status: TPaymentStatus
  paid_at: string | null
  refunded_at: string | null
  created_at: string
  updated_at: string
}

interface ICauser {
  id: number
  name: string
  email: string | null
}

interface IProperties {
  old: Record<string, any>
  attributes: Record<string, any>
}

export interface IInvoiceActivity {
  id: number
  log_name: string | null
  description: string
  subject_id: number
  subject_type: string
  causer_id: number | null
  causer_type: string | null
  causer: ICauser | null
  properties: IProperties
  event: string | null
  batch_uuid: string | null
  created_at: string
  updated_at: string
}

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

export interface IInvoiceItem {
  id: number
  invoice_id?: number
  name: string
  description: string | null
  quantity: number
  unit: string | null
  unit_price: Money | number
  vat_rate: number
  total_excl_vat: Money | number
  total_vat: Money | number
  total_incl_vat: Money | number
  sort_order: number
  created_at?: string
  updated_at?: string
}

export interface IInvoiceTotals {
  subtotal: number
  totalVat0: number
  totalVat9: number
  totalVat21: number
  total: number
}

export interface IInvoice {
  id: number
  customer_id: number
  customer: Customer
  invoice_number: string
  date: string
  due_days: number
  date_due: string
  status_formatted: IStatusFormatted
  status: TInvoiceStatus
  subtotal: Money | number
  total_vat_0: Money | number
  total_vat_9: Money | number
  total_vat_21: Money | number
  total: Money | number
  notes: string | null
  items: IInvoiceItem[]
  payments?: IInvoicePayment[]
  activities?: IInvoiceActivity[]
  emails?: IInvoiceEmail[]
  created_at: string
  updated_at: string
}

export interface ICreateInvoiceRequest {
  customer_id: number
  invoice_number?: string | null
  date: string
  due_days?: number
  date_due?: string
  status?: TInvoiceStatus
  subtotal?: number
  total_vat_0?: number
  total_vat_9?: number
  total_vat_21?: number
  total?: number
  notes?: string | null
}

export interface IUpdateInvoiceRequest {
  id: number
  customer_id?: number
  invoice_number?: string | null
  date?: string
  due_days?: number
  date_due?: string
  status?: TInvoiceStatus
  subtotal?: number
  total_vat_0?: number
  total_vat_9?: number
  total_vat_21?: number
  total?: number
  notes?: string | null
}

export interface IInvoicePrerequisites {
  items: Item[]
  customers: Customer[]
  next_invoice_number: string
}
