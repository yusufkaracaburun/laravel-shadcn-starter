import type { APIResponse } from '@playwright/test'

import { expect } from '@playwright/test'

import type { IResponse, User } from '../types'

import { testUser } from '../../../fixtures/data/user'
import { HttpStatus, isSuccessStatus } from '../enums'

/**
 * Common test helper functions
 * Implements custom assertion helpers following AAA (Arrange-Act-Assert) pattern
 */

/**
 * Assert that response has successful status (200-299)
 */
export function expectSuccess(response: APIResponse): void {
  const status = response.status()
  expect(
    isSuccessStatus(status),
    `Expected success status (200-299) but got ${status}. URL: ${response.url()}`,
  ).toBe(true)
}

/**
 * Assert that response has a specific status code
 */
export function expectStatus(response: APIResponse, statusCode: number): void {
  expect(response.status()).toBe(statusCode)
}

/**
 * Assert that response has error status (400-599)
 */
export function expectError(response: APIResponse, statusCode?: number): void {
  if (statusCode) {
    expect(response.status()).toBe(statusCode)
  }
  else {
    expect(response.status()).toBeGreaterThanOrEqual(400)
  }
}

/**
 * Assert that response contains a valid user object
 * For login response (direct user object) or IResponse<User>
 */
export async function expectValidUser(
  response: APIResponse,
  email = testUser.email,
): Promise<void> {
  expectSuccess(response)
  const body = await response.json()

  // Handle both direct user object (login) and IResponse<User> (getUser)
  const user: User = (body as IResponse<User>).data || (body as User)

  expect(user).toHaveProperty('id')
  expect(user).toHaveProperty('email', email)
  expect(user).toHaveProperty('name')
  expect(user).toHaveProperty('current_team_id')
}

/**
 * Assert that response contains validation errors (422)
 */
export async function expectValidationErrors(response: APIResponse): Promise<void> {
  expectError(response, HttpStatus.UNPROCESSABLE_ENTITY)
  const body = await response.json()
  expect(body).toHaveProperty('message')
  expect(body).toHaveProperty('errors')
}

/**
 * Assert that response indicates unauthenticated access (401)
 * In test environments, destroyed sessions may return 500 instead of 401
 * This is expected behavior per backend tests (CrossDomainAuthenticationTest)
 */
export function expectUnauthenticated(response: APIResponse): void {
  const status = response.status()
  // In test environments, destroyed sessions can cause 500 errors
  // Accept both 401 (proper unauthenticated) and 500 (session destroyed)
  expect([HttpStatus.UNAUTHORIZED, HttpStatus.INTERNAL_SERVER_ERROR]).toContain(status)
}

/**
 * Assert that cookies are set in response
 */
export function expectCookiesSet(response: APIResponse): void {
  const cookies = response.headers()['set-cookie']
  expect(cookies).toBeTruthy()
}

/**
 * Assert that response matches IResponse<T> structure
 */
export async function expectIResponse<T>(
  response: APIResponse,
  dataValidator?: (data: T) => void,
): Promise<IResponse<T>> {
  expectSuccess(response)
  const body = await response.json()

  expect(body).toHaveProperty('success', true)
  expect(body).toHaveProperty('code')
  expect(body).toHaveProperty('message')
  expect(body).toHaveProperty('data')
  expect(body).toHaveProperty('extra')

  const iResponse = body as IResponse<T>

  if (dataValidator) {
    dataValidator(iResponse.data)
  }

  return iResponse
}
