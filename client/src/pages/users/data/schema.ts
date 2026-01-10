import { z } from 'zod'

import { EUserRole, EUserStatus } from '../models/users'

export const userStatusSchema = z.enum([
  EUserStatus.REGISTERED,
  EUserStatus.ACTIVE,
  EUserStatus.INACTIVE,
  EUserStatus.BLOCKED,
  EUserStatus.SUSPENDED,
])
export type TUserStatus = z.infer<typeof userStatusSchema>

export const userRoleSchema = z.nativeEnum(EUserRole)
export type TUserRole = z.infer<typeof userRoleSchema>

export const userSchema = z.object({
  id: z.string(),
  first_name: z.string(),
  last_name: z.string(),
  name: z.string(),
  email: z.string(),
  phone: z.string(),
  status: userStatusSchema,
  role: userRoleSchema,
  created_at: z.coerce.date(),
  updated_at: z.coerce.date(),
})
export type TUser = z.infer<typeof userSchema>

export const userFormSchema = z.object({
  id: z.number().optional(),
  name: z.string(),
  email: z.string(),
  role: userRoleSchema,
  status: userStatusSchema,
})
export type TUserForm = z.infer<typeof userFormSchema>

/**
 * Base schema for user form (shared between create and edit modes)
 */
const baseUserFormSchema = z.object({
  name: z.string().min(1, 'Name is required.'),
  email: z
    .string()
    .email('Please enter a valid email address.')
    .min(1, 'Email is required.'),
  profile_photo: z.instanceof(File).optional().nullable(),
  role: userRoleSchema,
})

/**
 * Schema for creating a new user (password is required)
 */
export const createUserFormSchema = baseUserFormSchema
  .extend({
    password: z.string().min(8, 'Password must be at least 8 characters.'),
    password_confirmation: z.string().min(1, 'Please confirm your password.'),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match.',
    path: ['password_confirmation'],
  })

/**
 * Schema for editing an existing user (password is optional)
 */
export const editUserFormSchema = baseUserFormSchema
  .extend({
    password: z
      .string()
      .min(8, 'Password must be at least 8 characters.')
      .optional(),
    password_confirmation: z
      .string()
      .min(1, 'Please confirm your password.')
      .optional(),
  })
  .refine(
    (data) => {
      // Only validate password match if password is provided
      if (data.password || data.password_confirmation) {
        return data.password === data.password_confirmation
      }
      return true
    },
    {
      message: 'Passwords do not match.',
      path: ['password_confirmation'],
    },
  )

export type TCreateUserForm = z.infer<typeof createUserFormSchema>
export type TEditUserForm = z.infer<typeof editUserFormSchema>
