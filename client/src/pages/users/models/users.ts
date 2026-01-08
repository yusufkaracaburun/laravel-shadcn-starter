import type { IStatus } from '@/services/types/response.type'

export enum EUserRole {
  SUPER_ADMIN = 'super_admin',
  ADMIN = 'admin',
  USER = 'user',
  CUSTOMER = 'customer',
  CONTRACTOR = 'contractor',
}
export type TUserRole = (typeof EUserRole)[keyof typeof EUserRole]

export enum EUserStatus {
  REGISTERED = 'registered',
  ACTIVE = 'active',
  INACTIVE = 'inactive',
  BLOCKED = 'blocked',
  SUSPENDED = 'suspended',
}

export type TUserStatus = (typeof EUserStatus)[keyof typeof EUserStatus]

export interface IUserFilters {
  name?: string
  email?: string
  role?: TUserRole
  status?: TUserStatus
  created_at?: string
  search?: string
  between?: string
}

export interface IRole {
  id: number
  team_id: number
  name: string
  guard_name: string
  created_at: string
  updated_at: string
}

export interface IUser {
  id: number
  name: string
  email: string
  role: TUserRole
  status: TUserStatus
  created_at: string
  updated_at: string
}

export interface ICreateUserRequest {
  name: string
  email: string
  role: TUserRole
  status: TUserStatus
}

export interface IUpdateUserRequest {
  id: number
  name?: string
  email?: string
  role?: TUserRole
  status?: TUserStatus
}

export interface IUserPrerequisites {
  roles: IRole[]
  statuses: IStatus[]
}
