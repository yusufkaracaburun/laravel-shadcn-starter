import type { APIResponse } from '@playwright/test'

import { ApiEndpoints } from '../shared/enums'
import { BaseClient } from '../shared/core/base-client'
import type { LoginCredentials, User } from '../shared/types'
import type { IResponse } from '../shared/types'

/**
 * Client for authentication-related API endpoints
 * Extends BaseClient for automatic CSRF and cookie handling
 */
export class AuthClient extends BaseClient {
  /**
   * Get CSRF cookie from Sanctum (required before login)
   */
  async getCsrfCookie(): Promise<APIResponse> {
    return this.get(ApiEndpoints.CSRF_COOKIE, { requireCsrf: false })
  }

  /**
   * Login with email and password
   * Automatically ensures CSRF cookie is obtained before login
   * Returns user object directly (not wrapped in IResponse)
   */
  async login(email: string, password: string): Promise<APIResponse>
  async login(credentials: LoginCredentials): Promise<APIResponse>
  async login(
    emailOrCredentials: string | LoginCredentials,
    password?: string,
  ): Promise<APIResponse> {
    const credentials: LoginCredentials =
      typeof emailOrCredentials === 'string'
        ? { email: emailOrCredentials, password: password! }
        : emailOrCredentials

    await this.ensureCsrfCookie()
    return this.post(ApiEndpoints.LOGIN, credentials)
  }

  /**
   * Get current authenticated user
   * Returns raw APIResponse
   */
  async getUser(): Promise<APIResponse> {
    return this.get(ApiEndpoints.USER_CURRENT)
  }

  /**
   * Get current authenticated user with typed response
   * Returns IResponse<User>
   */
  async getUserTyped(): Promise<IResponse<User>> {
    const response = await this.getUser()
    return response.json() as Promise<IResponse<User>>
  }

  /**
   * Logout user
   * Automatically ensures CSRF cookie is obtained before logout
   */
  async logout(): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    return this.post(ApiEndpoints.LOGOUT, {})
  }
}

