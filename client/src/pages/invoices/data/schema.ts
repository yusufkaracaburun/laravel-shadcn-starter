import { z } from 'zod'

/**
 * Money schema for Money objects (or number for compatibility)
 * Matches Money interface from items.service.ts
 */
export const moneySchema = z.union([
  z.object({
    amount: z.string(),
    currency: z.string(),
    formatted: z.string(),
  }),
  z.number(),
])

/**
 * Invoice status enum matching backend InvoiceStatus
 */
export const invoiceStatusSchema = z.enum([
  'draft',
  'sent',
  'paid',
  'overdue',
  'cancelled',
])

export type TInvoiceStatus = z.infer<typeof invoiceStatusSchema>

/**
 * Invoice item schema matching backend InvoiceItemResource
 * @see api/app/Http/Resources/InvoiceItemResource.php
 */
export const invoiceItemSchema = z.object({
  id: z.number(),
  invoice_id: z.number(),
  name: z.string(),
  description: z.string().nullable(),
  quantity: z
    .union([z.number(), z.string()])
    .transform(val =>
      typeof val === 'string' ? Number.parseFloat(val) : val,
    ),
  unit: z.string().nullable().optional(),
  unit_price: moneySchema,
  vat_rate: z
    .union([z.number(), z.string()])
    .transform(val =>
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

/**
 * Invoice schema matching backend InvoiceResource
 * @see api/app/Http/Resources/InvoiceResource.php
 * Backend returns snake_case fields
 * Note: Customer is loaded when include is used
 * Note: Items are loaded when include=items is used
 */
export const invoiceSchema = z.object({
  id: z.number(),
  customer_id: z.number(),
  customer: z.any().optional(), // Customer type can be imported if needed
  invoice_number: z.string().nullable(),
  date: z.string(), // Date format from backend
  due_days: z.number(),
  date_due: z.string(), // Date format from backend
  status: invoiceStatusSchema,
  subtotal: moneySchema,
  total_vat_0: moneySchema,
  total_vat_9: moneySchema,
  total_vat_21: moneySchema,
  total: moneySchema,
  notes: z.string().nullable(),
  items: z
    .union([
      z.array(invoiceItemSchema), // Direct array
      z.object({ data: z.array(invoiceItemSchema) }), // Paginated structure
    ])
    .optional(), // Items are loaded when include=items is used
  created_at: z.string(), // Format: "d-m-Y H:i:s"
  updated_at: z.string(), // Format: "d-m-Y H:i:s"
})

export const invoiceFormSchema = z.object({
  customer_id: z.preprocess(
    val =>
      val === undefined || val === null || val === '' ? undefined : Number(val),
    z.number().min(1, 'Customer is required'),
  ),
  invoice_number: z.string(),
  date: z.string().min(1, 'Date is required'),
  due_days: z.number().min(1, 'Due days must be at least 1'),
  date_due: z.string().min(1, 'Due date is required'),
  status: invoiceStatusSchema,
  notes: z.string().nullable().optional(),
})

export type TInvoice = z.infer<typeof invoiceSchema>
export type TInvoiceForm = z.infer<typeof invoiceFormSchema>
