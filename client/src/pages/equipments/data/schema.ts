import { z } from 'zod'

/**
 * Base schema for equipments form (shared between create and edit modes)
 */
const baseEquipmentsFormSchema = z.object({
  name: z.string().min(1, 'Name is required.'),
  type: z.string().nullable().optional(),
  model: z.string().nullable().optional(),
  serial_number: z.string().nullable().optional(),
  status: z.string().nullable().optional(),
})

/**
 * Schema for creating a new equipment
 */
export const createEquipmentsFormSchema = baseEquipmentsFormSchema

/**
 * Schema for editing an existing equipment
 */
export const editEquipmentsFormSchema = baseEquipmentsFormSchema

export type TCreateEquipmentsForm = z.infer<typeof createEquipmentsFormSchema>
export type TEditEquipmentsForm = z.infer<typeof editEquipmentsFormSchema>
