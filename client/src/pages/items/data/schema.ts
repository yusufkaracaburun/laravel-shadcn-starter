import { z } from 'zod'

/**
 * Money object schema from Money library
 */
export const moneySchema = z.object({
  amount: z.string(),
  currency: z.string(),
  formatted: z.string(),
})

/**
 * Item schema matching backend ItemResource
 * @see api/app/Http/Resources/ItemResource.php
 * Backend returns snake_case fields
 * Note: unit_price is a Money object from the Money library
 * Note: vat_rate may come as string from backend, so we coerce it to number
 */
export const itemSchema = z.object({
  id: z.number(),
  name: z.string(),
  description: z.string().nullable(),
  unit_price: z.union([moneySchema, z.number()]), // Can be Money object or number
  vat_rate: z.coerce.number(), // Coerce string to number (backend may return "21.00" as string)
  unit: z.string().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
})

export type Item = z.infer<typeof itemSchema>

