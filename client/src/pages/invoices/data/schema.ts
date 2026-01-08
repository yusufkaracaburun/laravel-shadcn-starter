import { z } from 'zod'

import { EInvoiceStatus } from '../models/invoice'

export const moneySchema = z.union([
  z.object({
    amount: z.string(),
    currency: z.string(),
    formatted: z.string(),
  }),
  z.number(),
])

export const invoiceStatusSchema = z.enum([
  EInvoiceStatus.DRAFT,
  EInvoiceStatus.SENT,
  EInvoiceStatus.PAID,
  EInvoiceStatus.UNPAID,
  EInvoiceStatus.PARTIAL_PAID,
  EInvoiceStatus.OVERDUE,
  EInvoiceStatus.REMINDER,
  EInvoiceStatus.CANCELLED,
  EInvoiceStatus.REFUNDED,
  EInvoiceStatus.CREDITED,
])

export type TInvoiceStatus = z.infer<typeof invoiceStatusSchema>

export const invoiceItemSchema = z.object({
  id: z.number(),
  invoice_id: z.number(),
  name: z.string(),
  description: z.string().nullable(),
  quantity: z
    .union([z.number(), z.string()])
    .transform((val) =>
      typeof val === 'string' ? Number.parseFloat(val) : val,
    ),
  unit: z.string().nullable().optional(),
  unit_price: moneySchema,
  vat_rate: z
    .union([z.number(), z.string()])
    .transform((val) =>
      typeof val === 'string' ? Number.parseFloat(val) : val,
    ),
  total_excl_vat: moneySchema,
  total_vat: moneySchema,
  total_incl_vat: moneySchema,
  sort_order: z.number(),
  created_at: z.string(),
  updated_at: z.string(),
})

export type TInvoiceItem = z.infer<typeof invoiceItemSchema>

export const invoiceSchema = z.object({
  id: z.number(),
  customer_id: z.number(),
  customer: z.any().optional(),
  invoice_number: z.string().nullable(),
  date: z.string(),
  due_days: z.number(),
  date_due: z.string(),
  status: invoiceStatusSchema,
  subtotal: moneySchema,
  total_vat_0: moneySchema,
  total_vat_9: moneySchema,
  total_vat_21: moneySchema,
  total: moneySchema,
  notes: z.string().nullable(),
  items: z.array(invoiceItemSchema).optional(),
  created_at: z.string(),
  updated_at: z.string(),
})
export type TInvoice = z.infer<typeof invoiceSchema>

export const invoiceFormSchema = z.object({
  id: z.number().optional(),
  customer_id: z.preprocess(
    (val) =>
      val === undefined || val === null || val === '' ? undefined : Number(val),
    z.number().min(1, 'Customer is required'),
  ),
  invoice_number: z.string(),
  date: z.string().min(1, 'Date is required'),
  due_days: z.number().min(1, 'Due days must be at least 1'),
  date_due: z.string().min(1, 'Due date is required'),
  status: invoiceStatusSchema,
  items: z.array(invoiceItemSchema),
  notes: z.string().nullable().optional(),
})
export type TInvoiceForm = z.infer<typeof invoiceFormSchema>
