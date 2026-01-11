import { z } from 'zod'

/**
 * Base schema for timesheet form (shared between create and edit modes)
 */
const baseTimesheetFormSchema = z.object({
  user_id: z.number().min(1, 'User is required.'),
  project_id: z.number().nullable().optional(),
  task_id: z.number().nullable().optional(),
  date: z.string().min(1, 'Date is required.'),
  hours: z.number().min(0.1, 'Hours must be greater than 0.').max(24, 'Hours cannot exceed 24.'),
  description: z.string().nullable().optional(),
  status: z.string().nullable().optional(),
})

/**
 * Schema for creating a new timesheet
 */
export const createTimesheetFormSchema = baseTimesheetFormSchema

/**
 * Schema for editing an existing timesheet
 */
export const editTimesheetFormSchema = baseTimesheetFormSchema

export type TCreateTimesheetForm = z.infer<typeof createTimesheetFormSchema>
export type TEditTimesheetForm = z.infer<typeof editTimesheetFormSchema>
