import { z } from 'zod'

// We're keeping a simple non-relational schema here.
// IRL, you will have a schema for your data models.
export const projectSchema = z.object({
  id: z.string(),
  name: z.string(),
  description: z.string(),
  status: z.string(),
  priority: z.string(),
  category: z.string(),
  startDate: z.string(),
  endDate: z.string(),
  progress: z.number(),
})

export type Project = z.infer<typeof projectSchema>

