import type { APIResponse } from '@playwright/test'

import { ApiEndpoints } from '../shared/enums'
import { BaseClient } from '../shared/core/base-client'
import type { IResponse, User, Role } from '../shared/types'

/**
 * Create user request interface matching backend StoreUserRequest
 * @see api/app/Http/Requests/StoreUserRequest.php
 */
export interface CreateUserRequest {
  name: string
  email: string
  password: string
  password_confirmation: string
  role?: string | null
}

/**
 * Update user request interface matching backend UpdateUserRequest
 * @see api/app/Http/Requests/UpdateUserRequest.php
 */
export interface UpdateUserRequest {
  name?: string
  email?: string
  password?: string
  password_confirmation?: string
  role?: string | null
}

/**
 * Paginated users response interface matching Laravel's pagination JSON structure
 * @see api/app/Http/Controllers/Api/UserController.php::index()
 */
export interface PaginatedUsersResponse {
  data: User[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  first_page_url: string
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  path: string
  from: number | null
  to: number | null
}

/**
 * Client for user-related API endpoints
 * Extends BaseClient for automatic CSRF and cookie handling
 */
export class UserClient extends BaseClient {
  /**
   * Get current authenticated user
   * Returns raw APIResponse
   */
  async getCurrentUser(): Promise<APIResponse> {
    return this.get(ApiEndpoints.USER_CURRENT)
  }

  /**
   * Get current authenticated user with typed response
   * Returns IResponse<User>
   */
  async getCurrentUserTyped(): Promise<IResponse<User>> {
    const response = await this.getCurrentUser()
    return response.json() as Promise<IResponse<User>>
  }

  /**
   * Get available roles
   * Returns raw APIResponse
   */
  async getRoles(): Promise<APIResponse> {
    return this.get(ApiEndpoints.USER_ROLES)
  }

  /**
   * Get available roles with typed response
   * Returns IResponse<Role[]>
   */
  async getRolesTyped(): Promise<IResponse<Role[]>> {
    const response = await this.getRoles()
    return response.json() as Promise<IResponse<Role[]>>
  }

  /**
   * Get paginated list of users
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns raw APIResponse
   */
  async getUsers(page: number = 1, perPage: number = 15): Promise<APIResponse> {
    const endpoint = `${ApiEndpoints.USER_LIST}?page=${page}&per_page=${perPage}`
    return this.get(endpoint)
  }

  /**
   * Get paginated list of users with typed response
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns IResponse<PaginatedUsersResponse>
   */
  async getUsersTyped(
    page: number = 1,
    perPage: number = 15,
  ): Promise<IResponse<PaginatedUsersResponse>> {
    const response = await this.getUsers(page, perPage)
    return response.json() as Promise<IResponse<PaginatedUsersResponse>>
  }

  /**
   * Get a specific user by ID
   * @param userId - User ID
   * Returns raw APIResponse
   */
  async getUser(userId: number): Promise<APIResponse> {
    const endpoint = `${ApiEndpoints.USER_SHOW}/${userId}`
    return this.get(endpoint)
  }

  /**
   * Get a specific user by ID with typed response
   * @param userId - User ID
   * Returns IResponse<User>
   */
  async getUserTyped(userId: number): Promise<IResponse<User>> {
    const response = await this.getUser(userId)
    return response.json() as Promise<IResponse<User>>
  }

  /**
   * Create a new user
   * @param data - User creation data
   * Returns raw APIResponse
   */
  async createUser(data: CreateUserRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    return this.post(ApiEndpoints.USER_CREATE, data)
  }

  /**
   * Create a new user with typed response
   * @param data - User creation data
   * Returns IResponse<User>
   */
  async createUserTyped(data: CreateUserRequest): Promise<IResponse<User>> {
    const response = await this.createUser(data)
    return response.json() as Promise<IResponse<User>>
  }

  /**
   * Update an existing user
   * @param userId - User ID
   * @param data - User update data
   * Returns raw APIResponse
   */
  async updateUser(userId: number, data: UpdateUserRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `${ApiEndpoints.USER_UPDATE}/${userId}`
    return this.put(endpoint, data)
  }

  /**
   * Update an existing user with typed response
   * @param userId - User ID
   * @param data - User update data
   * Returns IResponse<User>
   */
  async updateUserTyped(userId: number, data: UpdateUserRequest): Promise<IResponse<User>> {
    const response = await this.updateUser(userId, data)
    return response.json() as Promise<IResponse<User>>
  }

  /**
   * Delete a user
   * @param userId - User ID
   * Returns raw APIResponse
   */
  async deleteUser(userId: number): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `${ApiEndpoints.USER_DELETE}/${userId}`
    return this.delete(endpoint)
  }
}
