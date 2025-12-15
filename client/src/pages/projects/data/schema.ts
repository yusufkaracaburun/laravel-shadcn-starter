import { z } from 'zod'

/**
 * Project schema matching backend ProjectResource
 * @see api/app/Http/Resources/ProjectResource.php
 * Backend returns snake_case fields, but we also provide camelCase for UI compatibility
 */
export const projectSchema = z.object({
  id: z.number(),
  name: z.string(),
  description: z.string().nullable(),
  status: z.string(),
  category: z.string(),
  start_date: z.string().nullable(),
  end_date: z.string().nullable(),
  progress: z.number(),
  team_id: z.number().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
  // CamelCase aliases for UI compatibility
  startDate: z.string().nullable().optional(),
  endDate: z.string().nullable().optional(),
})

export type Project = z.infer<typeof projectSchema>
