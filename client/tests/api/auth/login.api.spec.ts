import type { APIRequestContext } from '@playwright/test'

import { expect, test } from '@playwright/test'

import { testusers } from '../../.data/users.data'

// @ts-expect-error - process is a Node.js global, types are provided by @types/node
const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

// ============================================================================
// Pure Functions (no side effects)
// ============================================================================

/**
 * Pure function: Creates headers object without side effects
 */
function createHeaders(customHeaders?: Record<string, string>): Record<string, string> {
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...customHeaders,
  }
}

/**
 * Pure function: Builds full URL from endpoint
 */
function buildUrl(endpoint: string): string {
  const base = endpoint.startsWith('http') ? '' : apiURL
  return `${base}${endpoint}`
}

/**
 * Pure function: Extracts XSRF token from Set-Cookie header
 */
function extractCsrfToken(cookieHeader: string | string[] | undefined): string | null {
  let cookies: string[] = []
  if (Array.isArray(cookieHeader)) {
    cookies = cookieHeader
  }
  else if (cookieHeader) {
    cookies = [cookieHeader]
  }

  for (const cookie of cookies) {
    const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match && match[1]) {
      try {
        // Decode URL-encoding to get the encrypted value for X-XSRF-TOKEN header
        return decodeURIComponent(match[1].trim())
      }
      catch {
        return match[1].trim()
      }
    }
  }

  return null
}

// ============================================================================
// Side Effect Functions (API calls)
// ============================================================================

/**
 * Side effect: Gets CSRF cookie from server
 */
async function getCsrfCookie(request: APIRequestContext): Promise<string | null> {
  const response = await request.get(buildUrl('/sanctum/csrf-cookie'), {
    headers: createHeaders(),
  })

  expect(response.status()).toBe(204)

  const cookieHeader = response.headers()['set-cookie']
  return extractCsrfToken(cookieHeader)
}

/**
 * Side effect: Login with credentials
 */
async function login(request: APIRequestContext, credentials: { email: string, password: string }, csrfToken: string | null): Promise<{ response: Awaited<ReturnType<APIRequestContext['post']>>, body: unknown }> {
  const headers = createHeaders()

  if (csrfToken) {
    headers['X-XSRF-TOKEN'] = csrfToken
  }

  const response = await request.post(buildUrl('/login'), {
    data: credentials,
    headers,
  })

  const body = await response.json()

  return { response, body }
}

/**
 * Side effect: Get current authenticated user
 */
async function getCurrentUser(request: APIRequestContext): Promise<unknown> {
  const response = await request.get(buildUrl('/api/user/current'), {
    headers: createHeaders(),
  })

  expect(response.status()).toBe(200)

  const body = await response.json()
  return body
}

// ============================================================================
// Tests
// ============================================================================

test.describe('Login API', () => {
  test('should login successfully with valid credentials', async ({ request }) => {
    // Arrange - Pure data preparation
    const credentials = {
      email: testusers.admin.email,
      password: testusers.admin.password,
    }

    // Act - Get CSRF cookie (side effect)
    const csrfToken = await getCsrfCookie(request)

    // Assert - CSRF token should be present
    expect(csrfToken).not.toBeNull()
    expect(csrfToken).toBeTruthy()

    // Act - Login with credentials (side effect)
    const { response: loginResponse, body: loginBody } = await login(request, credentials, csrfToken)

    // Assert - Login should succeed
    // Fortify returns {"two_factor": false} on successful login (not IResponse format)
    expect(loginResponse.status()).toBe(200)
    expect(loginBody).toHaveProperty('two_factor', false)

    // Act - Get current user to verify authentication (side effect)
    const userBody = await getCurrentUser(request)

    // Assert - User should be authenticated
    expect(userBody).toHaveProperty('success', true)
    expect(userBody).toHaveProperty('data')
    expect((userBody as { data: { email: string } }).data.email).toBe(credentials.email)
  })

  test('should return 422 for invalid credentials', async ({ request }) => {
    // Arrange - Pure data preparation
    const invalidCredentials = {
      email: 'invalid@example.com',
      password: 'wrongpassword',
    }

    // Act - Get CSRF cookie (side effect)
    const csrfToken = await getCsrfCookie(request)

    // Assert - CSRF token should be present
    expect(csrfToken).not.toBeNull()

    // Act - Attempt login with invalid credentials (side effect)
    const { response: loginResponse, body: loginBody } = await login(request, invalidCredentials, csrfToken)

    // Assert - Login should fail with 422
    expect(loginResponse.status()).toBe(422)
    expect(loginBody).toHaveProperty('message')
  })

  test('should return 419 for missing CSRF token', async ({ request }) => {
    // Arrange - Pure data preparation
    const credentials = {
      email: testusers.admin.email,
      password: testusers.admin.password,
    }

    // Act - Login without CSRF token (side effect)
    const { response: loginResponse, body: loginBody } = await login(request, credentials, null)

    // Assert - Should fail with 419 CSRF token mismatch
    expect(loginResponse.status()).toBe(419)
    expect(loginBody).toHaveProperty('message')
    expect((loginBody as { message: string }).message).toContain('CSRF')
  })
})
