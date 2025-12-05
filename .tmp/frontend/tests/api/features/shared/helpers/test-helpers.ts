import type { APIResponse } from '@playwright/test'

import { expect } from '@playwright/test'

import { testUser } from '../../../fixtures/data/user'

/**
 * Common test helper functions
 * Implements custom assertion helpers following AAA (Arrange-Act-Assert) pattern
 */

/**
 * Assert that response has successful status (200-299)
 */
export function expectSuccess(response: APIResponse): void {
  expect(response.status()).toBeGreaterThanOrEqual(200)
  expect(response.status()).toBeLessThan(300)
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
 */
export async function expectValidUser(response: APIResponse, email = testUser.email): Promise<void> {
  expectSuccess(response)
  const user = await response.json()
  expect(user).toHaveProperty('id')
  expect(user).toHaveProperty('email', email)
  expect(user).toHaveProperty('name')
}

/**
 * Assert that response contains validation errors
 */
export async function expectValidationErrors(response: APIResponse): Promise<void> {
  expectError(response, 422)
  const body = await response.json()
  expect(body).toHaveProperty('message')
  expect(body).toHaveProperty('errors')
}

/**
 * Assert that response indicates unauthenticated access
 */
export function expectUnauthenticated(response: APIResponse): void {
  expectError(response, 401)
}

/**
 * Assert that cookies are set in response
 */
export function expectCookiesSet(response: APIResponse): void {
  const cookies = response.headers()['set-cookie']
  expect(cookies).toBeTruthy()
}
