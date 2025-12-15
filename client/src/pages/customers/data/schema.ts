import { z } from 'zod'

/**
 * Contact schema matching backend ContactResource
 * @see api/app/Http/Resources/ContactResource.php
 * Backend returns snake_case fields
 */
export const contactSchema = z.object({
  id: z.number(),
  name: z.string(), // full_name accessor
  first_name: z.string(),
  last_name: z.string(),
  email: z.string(),
  phone: z.string().nullable(),
  address: z.string().nullable(),
  zipcode: z.string().nullable(),
  city: z.string().nullable(),
  country: z.string().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
  user: z.any().nullable().optional(), // User type can be defined if needed
})

export type Contact = z.infer<typeof contactSchema>

/**
 * Customer type enum matching backend CustomerType
 * @see api/app/Enums/CustomerType.php
 */
export const customerTypeSchema = z.enum(['business', 'private'])

/**
 * Customer schema matching backend CustomerResource
 * @see api/app/Http/Resources/CustomerResource.php
 * Backend returns snake_case fields
 * Note: formatted_address is an array from accessor
 * Note: primary_contact is always loaded
 */
export const customerSchema = z.object({
  id: z.number(),
  number: z.string().nullable().optional(),
  type: customerTypeSchema,
  name: z.string(),

  // Address fields
  address: z.string().nullable(),
  formatted_address: z.array(z.string()), // accessor - array format
  zipcode: z.string().nullable(),
  city: z.string().nullable(),
  country: z.string().nullable(),

  // Contact / business info
  email: z.string().nullable(),
  phone: z.string().nullable(),
  kvk_number: z.string().nullable(),
  vat_number: z.string().nullable(),
  iban_number: z.string().nullable(),

  // Timestamps (format: "d-m-Y H:i:s")
  created_at: z.string(),
  updated_at: z.string(),

  // Primary contact (always loaded)
  primary_contact: contactSchema.nullable(),

  // Collections (when loaded)
  contacts: z.array(contactSchema).optional(),
  invoices: z.array(z.any()).optional(), // Invoice type can be defined later if needed

  // Counts (when counted)
  contacts_count: z.number().optional(),
  invoices_count: z.number().optional(),
})

export type Customer = z.infer<typeof customerSchema>

