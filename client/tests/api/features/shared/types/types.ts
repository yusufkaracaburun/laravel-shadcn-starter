import type { IResponse as FrontendIResponse } from '@/services/types/response.type'
import type { User as FrontendUser } from '@/services/users.service'

/**
 * API Response interface matching backend ApiResponse
 * @see api/app/Http/Responses/ApiResponse.php
 */
export interface IResponse<T = unknown, E = Record<string, any>> extends FrontendIResponse<T, E> {
  success: boolean
  code: number
  message: string
  data: T
  extra: E
}

/**
 * User interface matching backend UserResource exactly
 * @see api/app/Http/Resources/UserResource.php
 * @see client/src/services/users.service.ts
 */
export interface User extends FrontendUser {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  current_team_id: number | null
  profile_photo_path: string | null
  created_at: string
  updated_at: string
  teams?: Team[]
  currentTeam?: Team | null
}

/**
 * Team interface matching backend TeamResource
 */
export interface Team {
  id: number
  name: string
  user_id: number
  personal_team: boolean
  created_at: string
  updated_at: string
  [key: string]: unknown
}

/**
 * Role interface matching backend Role model
 * @see api/app/Http/Controllers/Api/UserController.php::roles()
 */
export interface Role {
  id: number
  name: string
}

/**
 * Login credentials interface
 */
export interface LoginCredentials {
  email: string
  password: string
}

/**
 * CSRF data interface
 */
export interface CsrfData {
  token: string | null
  cookies: string
}

/**
 * Validation error interface
 */
export interface ValidationError {
  message: string
  errors: Record<string, string[]>
}

/**
 * Paginated response interface
 */
export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

