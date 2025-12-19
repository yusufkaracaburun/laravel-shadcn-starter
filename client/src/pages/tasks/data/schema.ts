import { z } from 'zod'

// We're keeping a simple non-relational schema here.
// IRL, you will have a schema for your data models.
export const taskSchema = z.object({
  id: z.string(),
  title: z.string(),
  description: z.string().optional(),
  status: z.string(),
  labels: z.array(z.string()).default([]),
  priority: z.string(),
  dueDate: z.union([z.string(), z.number(), z.date(), z.null()]).optional(),
  createdAt: z
    .union([z.string(), z.number(), z.date()])
    .default(() => new Date().toISOString()),
})

export type Task = z.infer<typeof taskSchema>
