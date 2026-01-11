import { z } from 'zod'

/**
 * Base schema for team form (shared between create and edit modes)
 */
const baseTeamFormSchema = z.object({
  name: z.string().min(1, 'Name is required.'),
  personal_team: z.boolean().optional(),
  user_id: z.number().nullable().optional(),
})

/**
 * Schema for creating a new team
 */
export const createTeamFormSchema = baseTeamFormSchema

/**
 * Schema for editing an existing team
 */
export const editTeamFormSchema = baseTeamFormSchema

export type TCreateTeamForm = z.infer<typeof createTeamFormSchema>
export type TEditTeamForm = z.infer<typeof editTeamFormSchema>
