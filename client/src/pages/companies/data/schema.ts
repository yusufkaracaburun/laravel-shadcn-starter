import { z } from 'zod'

// We're keeping a simple non-relational schema here.
// IRL, you will have a schema for your data models.
export const companySchema = z.object({
  id: z.string(),
  name: z.string(),
  email: z.string(),
  phone: z.string(),
  industry: z.string(),
  status: z.string(),
  employees: z.string(),
})

export type Company = z.infer<typeof companySchema>

export const companyListSchema = z.array(companySchema)
