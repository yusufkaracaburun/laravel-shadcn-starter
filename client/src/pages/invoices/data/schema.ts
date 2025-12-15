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
export const invoiceStatusSchema = z.enum(['draft', 'sent', 'paid', 'overdue', 'cancelled'])

export type InvoiceStatus = z.infer<typeof invoiceStatusSchema>

/**
 * Invoice schema matching backend InvoiceResource
 * @see api/app/Http/Resources/InvoiceResource.php
 * Backend returns snake_case fields
 * Note: Customer is loaded when include is used
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
  created_at: z.string(), // Format: "d-m-Y H:i:s"
  updated_at: z.string(), // Format: "d-m-Y H:i:s"
})

export type Invoice = z.infer<typeof invoiceSchema>
