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

export const userRoleSchema = z.enum([
  EUserRole.SUPER_ADMIN,
  EUserRole.ADMIN,
  EUserRole.USER,
  EUserRole.CUSTOMER,
  EUserRole.CONTRACTOR,
])
export type TUserRole = z.infer<typeof userRoleSchema>

export const userSchema = z.object({
  id: z.string(),
  firstName: z.string(),
  lastName: z.string(),
  username: z.string(),
  email: z.string(),
  phoneNumber: z.string(),
  status: userStatusSchema,
  role: userRoleSchema,
  createdAt: z.coerce.date(),
  updatedAt: z.coerce.date(),
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
