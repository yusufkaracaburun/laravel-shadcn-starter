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

/**
 * Base schema for item form (shared between create and edit modes)
 */
const baseItemFormSchema = z.object({
  name: z.string().min(1, 'Name is required.').max(255, 'Name must not exceed 255 characters.'),
  description: z.string().nullable().optional(),
  unit_price: z.number().min(0, 'Unit price must be zero or greater.'),
  vat_rate: z.number().min(0, 'VAT rate must be zero or greater.').max(100, 'VAT rate may not be greater than 100%.'),
  unit: z.string().max(50, 'Unit must not exceed 50 characters.').nullable().optional(),
})

/**
 * Schema for creating a new item
 */
export const createItemFormSchema = baseItemFormSchema

/**
 * Schema for editing an existing item (all fields optional except validation rules)
 */
export const editItemFormSchema = baseItemFormSchema.partial().refine(
  (data) => {
    // At least one field must be provided for update
    return Object.keys(data).length > 0
  },
  {
    message: 'At least one field must be provided for update.',
  },
)

export type TCreateItemForm = z.infer<typeof createItemFormSchema>
export type TEditItemForm = z.infer<typeof editItemFormSchema>
