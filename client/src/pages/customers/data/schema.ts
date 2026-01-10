import { z } from 'zod'

/**
 * Contact schema matching backend ContactResource exactly
 * @see api/app/Http/Resources/ContactResource.php
 * @see api/app/Models/Contact.php
 * Backend returns snake_case fields
 * Note: name is from full_name accessor
 * Note: user is conditionally loaded (via whenLoaded)
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
  created_at: z.string(), // Formatted timestamp from formatTimestamp()
  updated_at: z.string(), // Formatted timestamp from formatTimestamp()
  user: z.any().nullable().optional(), // UserResource when loaded (via whenLoaded)
})

export type Contact = z.infer<typeof contactSchema>

/**
 * Customer type enum matching backend CustomerType
 * @see api/app/Enums/CustomerType.php
 */
export const customerTypeSchema = z.enum(['business', 'private'])

/**
 * Customer schema matching backend CustomerResource exactly
 * @see api/app/Http/Resources/CustomerResource.php
 * @see api/app/Models/Customer.php
 * Backend returns snake_case fields
 * Note: number is always present (generated from id, padded to 6 digits)
 * Note: formatted_address is always present (accessor returns array from AddressHelper)
 * Note: primary_contact is always loaded (via $with)
 */
export const customerSchema = z.object({
  id: z.number(),
  number: z.string(), // Generated from id, padded to 6 digits (e.g., "000001")
  type: customerTypeSchema,
  name: z.string(),

  // Address fields
  address: z.string().nullable(),
  formatted_address: z.array(z.string()), // accessor - always returns array from AddressHelper
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

  // Primary contact (always loaded via $with)
  primary_contact: contactSchema.nullable(),

  // Collections (when loaded)
  contacts: z.array(contactSchema).optional(),
  invoices: z.array(z.any()).optional(), // Invoice type can be defined later if needed

  // Counts (when counted)
  contacts_count: z.number().optional(),
  invoices_count: z.number().optional(),
})

export type Customer = z.infer<typeof customerSchema>
