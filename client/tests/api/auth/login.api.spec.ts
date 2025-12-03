import type { APIRequestContext } from '@playwright/test'

import { expect, test } from '@playwright/test'

import { testusers } from '../../.data/users.data'

const apiURL = process.env.VITE_API_BASE_URL || 'http://localhost:8000'
// Frontend URL for Origin/Referer headers (matching browser behavior)
const frontendURL = process.env.VITE_APP_URL || 'http://localhost:5173'

// ============================================================================
// Pure Functions (no side effects)
// ============================================================================

/**
 * Pure function: Creates headers object without side effects
 * Includes Origin and Referer headers to match browser behavior
 * Content-Type is only set for POST/PUT/PATCH requests
 */
function createHeaders(
  customHeaders?: Record<string, string>,
  includeContentType = false,
): Record<string, string> {
  // Parse frontend URL to extract origin (protocol + host + port)
  const url = new URL(frontendURL)
  const portPart = url.port ? `:${url.port}` : ''
  const origin = `${url.protocol}//${url.host}${portPart}`

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    Origin: origin,
    Referer: `${origin}/`,
    ...customHeaders,
  }

  // Only set Content-Type for POST/PUT/PATCH requests
  if (includeContentType && !customHeaders?.['Content-Type']) {
    headers['Content-Type'] = 'application/json'
  }

  return headers
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
  } else if (cookieHeader) {
    cookies = [cookieHeader]
  }

  const tokenRegex = /XSRF-TOKEN=([^;]+)/
  for (const cookie of cookies) {
    const match = tokenRegex.exec(cookie)
    const tokenValue = match?.[1]
    if (tokenValue) {
      try {
        // Decode URL-encoding to get the encrypted value for X-XSRF-TOKEN header
        return decodeURIComponent(tokenValue.trim())
      } catch {
        return tokenValue.trim()
      }
    }
  }

  return null
}

/**
 * Pure function: Extracts cookie values from Set-Cookie header
 * Returns array of "name=value" strings, ready for Cookie header
 * Cookies are kept URL-encoded as they appear in Set-Cookie header
 */
function extractCookieValue(cookieHeader: string | string[] | undefined): string[] {
  let cookies: string[] = []
  if (Array.isArray(cookieHeader)) {
    cookies = cookieHeader
  } else if (cookieHeader) {
    cookies = [cookieHeader]
  }

  const cookieRegex = /^([^;]+)/
  return cookies
    .map((cookie) => {
      // Extract cookie name and value (everything before the first semicolon)
      // Keep URL-encoded as received from Set-Cookie header
      const match = cookieRegex.exec(cookie)
      return match?.[1]?.trim() ?? ''
    })
    .filter(Boolean)
}

/**
 * Pure function: Merges existing cookies with new cookies
 * New cookies override existing ones with the same name
 */
function mergeCookies(existingCookies: string | undefined, newCookies: string[]): string {
  const cookieMap = new Map<string, string>()

  // Add existing cookies first (to preserve session, especially laravel_session)
  if (existingCookies) {
    existingCookies.split('; ').forEach((cookie) => {
      const trimmedCookie = cookie.trim()
      if (trimmedCookie) {
        const equalIndex = trimmedCookie.indexOf('=')
        if (equalIndex > 0) {
          const name = trimmedCookie.substring(0, equalIndex).trim()
          if (name) {
            cookieMap.set(name, trimmedCookie)
          }
        }
      }
    })
  }

  // Add/update with new cookies (new cookies take precedence)
  newCookies.forEach((cookie) => {
    const trimmedCookie = cookie.trim()
    if (trimmedCookie) {
      const equalIndex = trimmedCookie.indexOf('=')
      if (equalIndex > 0) {
        const name = trimmedCookie.substring(0, equalIndex).trim()
        if (name) {
          cookieMap.set(name, trimmedCookie)
        }
      }
    }
  })

  return Array.from(cookieMap.values()).filter(Boolean).join('; ')
}

// ============================================================================
// Side Effect Functions (API calls)
// ============================================================================

/**
 * Side effect: Gets CSRF cookie from server
 * Returns both the CSRF token and all cookies for session management
 */
async function getCsrfCookie(
  request: APIRequestContext,
  sessionCookies?: string,
): Promise<{ token: string | null; cookies: string }> {
  const headers = createHeaders(undefined, false) // GET request, no Content-Type
  if (sessionCookies) {
    headers.Cookie = sessionCookies
  }

  const response = await request.get(buildUrl('/sanctum/csrf-cookie'), {
    headers,
    ignoreHTTPSErrors: true,
  })

  expect(response.status()).toBe(204)

  const cookieHeader = response.headers()['set-cookie']
  let token = extractCsrfToken(cookieHeader)

  // Extract ALL cookie values for sending in Cookie header
  const cookieValues = extractCookieValue(cookieHeader)

  // Merge with existing session cookies if provided
  const mergedCookies = mergeCookies(sessionCookies, cookieValues)

  // Fallback: if we didn't find token in headers, try extracting from cookie string
  if (!token && mergedCookies) {
    const tokenRegex = /XSRF-TOKEN=([^;]+)/
    const match = tokenRegex.exec(mergedCookies)
    const tokenValue = match?.[1]
    if (tokenValue) {
      try {
        token = decodeURIComponent(tokenValue.trim())
      } catch {
        token = tokenValue.trim()
      }
    }
  }

  return { token, cookies: mergedCookies }
}

/**
 * Side effect: Login with credentials
 * Returns cookies from response for maintaining session
 */
async function login(
  request: APIRequestContext,
  credentials: { email: string; password: string },
  csrfToken: string | null,
  existingCookies?: string,
): Promise<{
  response: Awaited<ReturnType<APIRequestContext['post']>>
  body: unknown
  cookies: string
}> {
  const headers = createHeaders(undefined, true) // POST request, include Content-Type

  // X-XSRF-TOKEN header is required for Laravel CSRF protection
  if (csrfToken) {
    headers['X-XSRF-TOKEN'] = csrfToken
  }

  // Cookie header must include all cookies (XSRF-TOKEN and laravel_session)
  if (existingCookies) {
    headers.Cookie = existingCookies
  }

  const response = await request.post(buildUrl('/login'), {
    data: credentials,
    headers,
    ignoreHTTPSErrors: true,
  })

  const body = await response.json()

  // Extract and merge cookies from login response
  const newCookies = extractCookieValue(response.headers()['set-cookie'])
  const mergedCookies = mergeCookies(existingCookies, newCookies)

  return { response, body, cookies: mergedCookies }
}

/**
 * Side effect: Get current authenticated user
 */
async function getCurrentUser(request: APIRequestContext, cookies?: string): Promise<unknown> {
  const headers = createHeaders(undefined, false) // GET request, no Content-Type
  if (cookies) {
    headers.Cookie = cookies
  }

  const response = await request.get(buildUrl('/api/user/current'), {
    headers,
    ignoreHTTPSErrors: true,
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
      email: testusers.test.email,
      password: testusers.test.password,
    }

    // Act - Get CSRF cookie (side effect)
    const { token: csrfToken, cookies: csrfCookies } = await getCsrfCookie(request)

    // Assert - CSRF token should be present
    expect(csrfToken).not.toBeNull()
    expect(csrfToken).toBeTruthy()

    // Act - Login with credentials (side effect)
    // Pass CSRF cookies to maintain session
    const { response: loginResponse, body: loginBody, cookies: loginCookies } = await login(
      request,
      credentials,
      csrfToken,
      csrfCookies,
    )

    expect(loginResponse.status()).toBe(200)
    expect(loginBody).toHaveProperty('current_team_id', 1)
    expect(loginBody).toHaveProperty('email', testusers.test.email)
    expect(loginBody).toHaveProperty('name', testusers.test.name)

    // Act - Get current user to verify authentication (side effect)
    // Pass cookies from login response to maintain session
    const userBody = await getCurrentUser(request, loginCookies)

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
    const { token: csrfToken, cookies: csrfCookies } = await getCsrfCookie(request)

    // Assert - CSRF token should be present
    expect(csrfToken).not.toBeNull()

    // Act - Attempt login with invalid credentials (side effect)
    const { response: loginResponse, body: loginBody } = await login(
      request,
      invalidCredentials,
      csrfToken,
      csrfCookies,
    )

    // Assert - Login should fail with 422
    expect(loginResponse.status()).toBe(422)
    expect(loginBody).toHaveProperty('message')
  })

  test('should return 419 for missing CSRF token', async ({ request }) => {
    // Arrange - Pure data preparation
    const credentials = {
      email: testusers.test.email,
      password: testusers.test.password,
    }

    // Act - Login without CSRF token (side effect)
    const { response: loginResponse, body: loginBody } = await login(request, credentials, null)

    // Assert - Should fail with 419 CSRF token mismatch
    expect(loginResponse.status()).toBe(419)
    expect(loginBody).toHaveProperty('message')
    expect((loginBody as { message: string }).message).toContain('CSRF')
  })
})
