import type { AxiosResponse } from 'axios'

import apiClient from '@/lib/api-client'

export interface LoginCredentials {
  email: string
  password: string
}

export interface User {
  id: number
  name: string
  email: string
  [key: string]: unknown
}

/**
 * Get CSRF cookie from Sanctum
 * This MUST be called before any login attempt
 * Reference: https://laravel.com/docs/master/sanctum#spa-authentication
 *
 * Exact same implementation as test: BaseClient.ensureCsrfCookie()
 * The response interceptor will automatically extract and store the token from headers
 */
export async function getCsrfCookie(): Promise<void> {
  // Exact same pattern as test: await this.get('/sanctum/csrf-cookie', { requireCsrf: false })
  // The response interceptor will call csrfHandler.extractFromResponse(response)
  await apiClient.get('/sanctum/csrf-cookie')
}

/**
 * Login user with email and password
 * Follows Laravel Sanctum SPA authentication pattern:
 * 1. First get CSRF cookie
 * 2. Then make login request
 */
export async function login(credentials: LoginCredentials): Promise<AxiosResponse> {
  // Step 1: Get CSRF cookie (REQUIRED for Sanctum)
  await getCsrfCookie()

  // Step 2: Make login request
  return apiClient.post('/api/login', credentials)
}

/**
 * Logout user
 */
export async function logout(): Promise<AxiosResponse> {
  return apiClient.post('/api/logout')
}

/**
 * Get authenticated user
 */
export async function getUser(): Promise<User> {
  const response = await apiClient.get('/api/user')
  return response.data
}
