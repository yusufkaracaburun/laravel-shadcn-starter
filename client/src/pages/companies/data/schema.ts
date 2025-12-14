import { z } from 'zod'

/**
 * Company schema matching backend CompanyResource structure
 * @see api/app/Http/Resources/CompanyResource.php
 */
export const companySchema = z.object({
  id: z.number(),
  name: z.string(),
  email: z.string(),
  phone: z.string().nullable(),
  industry: z.string(),
  status: z.string(),
  employees: z.string(),
  team_id: z.number().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
})

export type Company = z.infer<typeof companySchema>

export const companyListSchema = z.array(companySchema)
