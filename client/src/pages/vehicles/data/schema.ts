import { z } from 'zod'

/**
 * Base schema for vehicle form (shared between create and edit modes)
 */
const baseVehicleFormSchema = z.object({
  license_plate: z.string().min(1, 'License plate is required.'),
  make: z.string().nullable().optional(),
  model: z.string().nullable().optional(),
  year: z
    .number()
    .int()
    .min(1900, 'Year must be 1900 or later.')
    .max(new Date().getFullYear() + 1, 'Year cannot be in the future.')
    .nullable()
    .optional(),
  color: z.string().nullable().optional(),
  vin: z.string().nullable().optional(),
})

/**
 * Schema for creating a new vehicle
 */
export const createVehicleFormSchema = baseVehicleFormSchema

/**
 * Schema for editing an existing vehicle
 */
export const editVehicleFormSchema = baseVehicleFormSchema

export type TCreateVehicleForm = z.infer<typeof createVehicleFormSchema>
export type TEditVehicleForm = z.infer<typeof editVehicleFormSchema>
