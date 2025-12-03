import type { APIRequestContext } from '@playwright/test'

import { expect, test } from '@playwright/test'

import { testusers } from '../../.data/users.data'
import { extractCookieValue } from '../../e2e/helpers/api-helpers'

const apiURL = process.env.VITE_API_BASE_URL || 'http://localhost:8000'
// Frontend URL for Origin/Referer headers (matching browser behavior)
const frontendURL = process.env.VITE_APP_URL || 'http://localhost:5173'

export interface CsrfData {
  token: string | null
  cookies: string
}

// ============================================================================
// Pure Functions (no side effects)
// ============================================================================

/**
 * Pure function: Builds full URL from endpoint
 */
function buildUrl(endpoint: string): string {
  const base = endpoint.startsWith('http') ? '' : apiURL
  return `${base}${endpoint}`
}

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
  // URL already includes port in hostname if specified, so use origin directly
  const origin = url.origin

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
 * Pure function: Extracts XSRF token from Set-Cookie header
 */
function extractCsrfToken(cookieHeader: string | string[] | undefined): string | null {
  let cookies: string[] = []
  if (Array.isArray(cookieHeader)) {
    cookies = cookieHeader
  } else if (cookieHeader) {
    cookies = [cookieHeader]
  }

  for (const cookie of cookies) {
    const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match && match[1]) {
      try {
        // Decode URL-encoding to get the encrypted value for X-XSRF-TOKEN header
        return decodeURIComponent(match[1].trim())
      } catch {
        return match[1].trim()
      }
    }
  }

  return null
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
 * Side effect: Gets CSRF token and all cookies from response
 * We extract all cookies (including laravel_session) to maintain session state
 * between requests, as Playwright's APIRequestContext may not always maintain
 * cookies properly for API requests.
 * If sessionCookies are provided, sends them to maintain the session
 */
async function getCsrfTokenAndCookies(
  request: APIRequestContext,
  sessionCookies?: string,
): Promise<CsrfData> {
  // Get CSRF cookie, sending existing session cookies to maintain the session
  const headers = createHeaders(undefined, false) // GET request, no Content-Type
  if (sessionCookies) {
    headers.Cookie = sessionCookies
  }

  const csrfResponse = await request.get(buildUrl('/sanctum/csrf-cookie'), {
    headers,
    ignoreHTTPSErrors: true,
  })

  expect(csrfResponse.status()).toBe(204)

  const csrfCookieHeader = csrfResponse.headers()['set-cookie']

  // Extract XSRF token from cookies for the X-XSRF-TOKEN header
  // Laravel sets the XSRF-TOKEN cookie with a URL-encoded encrypted value
  // The cookie in Set-Cookie header is: XSRF-TOKEN=encrypted_value%3D; expires=...
  // When sending back:
  // - Cookie header: XSRF-TOKEN=encrypted_value%3D (URL-encoded, as received)
  // - X-XSRF-TOKEN header: encrypted_value= (URL-decoded encrypted value)

  let token: string | null = null
  const cookieHeaders = Array.isArray(csrfCookieHeader)
    ? csrfCookieHeader
    : csrfCookieHeader
      ? [csrfCookieHeader]
      : []

  // Extract token from full cookie header (value is URL-encoded in Set-Cookie)
  for (const cookie of cookieHeaders) {
    const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match && match[1]) {
      const urlEncodedValue = match[1].trim()
      try {
        // Decode URL-encoding to get the encrypted value for X-XSRF-TOKEN header
        // This is the encrypted session token that Laravel will decrypt
        token = decodeURIComponent(urlEncodedValue)
      } catch {
        // If decoding fails, use raw value (shouldn't happen with valid cookies)
        token = urlEncodedValue
      }
      break
    }
  }

  // Extract ALL cookie values for sending in Cookie header
  // This includes both XSRF-TOKEN and laravel_session cookies
  // IMPORTANT: Playwright returns Set-Cookie headers as a single string with newlines (\n)
  // separating multiple cookies, not as an array. We need to split on newlines first.
  // - Set-Cookie header contains URL-encoded values: XSRF-TOKEN=value%3D
  // - Cookie header should contain URL-ENCODED values: XSRF-TOKEN=value%3D (as in Set-Cookie)
  // - X-XSRF-TOKEN header should contain URL-decoded value: value=
  // Laravel compares the decoded XSRF-TOKEN cookie value with the X-XSRF-TOKEN header
  // Format: "name1=encoded_value1; name2=encoded_value2"
  
  // Handle Set-Cookie header: it can be a string with newlines, an array, or undefined
  let cookieHeadersArray: string[] = []
  if (Array.isArray(csrfCookieHeader)) {
    cookieHeadersArray = csrfCookieHeader
  } else if (typeof csrfCookieHeader === 'string') {
    // Split on newlines to handle multiple cookies in a single string
    cookieHeadersArray = csrfCookieHeader.split('\n').filter(Boolean)
  }
  
  // Extract cookies from all headers
  const cookieValues: string[] = []
  for (const header of cookieHeadersArray) {
    const extracted = extractCookieValue(header)
    cookieValues.push(...extracted)
  }

  // Merge with existing session cookies if provided
  const cookieMap = new Map<string, string>()

  // Helper function to check if a cookie value is already URL-encoded
  // URL-encoded values typically contain % followed by hex digits
  function isUrlEncoded(value: string): boolean {
    return /%[0-9A-Fa-f]{2}/.test(value)
  }

  // Helper function to encode cookie value back to URL-encoded format
  // This ensures cookies in Cookie header match Set-Cookie format
  // Only encodes if the value is not already encoded
  function encodeCookieValue(cookie: string): string {
    const equalIndex = cookie.indexOf('=')
    if (equalIndex > 0) {
      const name = cookie.substring(0, equalIndex).trim()
      const value = cookie.substring(equalIndex + 1)
      // If already encoded, return as-is
      if (isUrlEncoded(value)) {
        return cookie
      }
      try {
        // Re-encode to match Set-Cookie format
        const encodedValue = encodeURIComponent(value)
        return `${name}=${encodedValue}`
      } catch {
        // If encoding fails, return as-is
        return cookie
      }
    }
    return cookie
  }

  // Add existing cookies first (to preserve session, especially laravel_session)
  // Re-encode them to match Set-Cookie format (in case they were decoded)
  if (sessionCookies) {
    sessionCookies.split('; ').forEach((cookie) => {
      const trimmedCookie = cookie.trim()
      if (trimmedCookie) {
        const equalIndex = trimmedCookie.indexOf('=')
        if (equalIndex > 0) {
          const name = trimmedCookie.substring(0, equalIndex).trim()
          if (name) {
            // Re-encode cookie value to match Set-Cookie format (only if not already encoded)
            const encodedCookie = encodeCookieValue(trimmedCookie)
            cookieMap.set(name, encodedCookie)
          }
        }
      }
    })
  }

  // Add/update with new cookies from CSRF response (new cookies take precedence)
  // Keep them URL-encoded as they appear in Set-Cookie header
  cookieValues.forEach((cookie) => {
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

  const cookieString = Array.from(cookieMap.values()).filter(Boolean).join('; ')

  // Fallback: if we didn't find token in headers, try extracting from cookie string
  if (!token && cookieString) {
    const match = cookieString.match(/XSRF-TOKEN=([^;]+)/)
    if (match && match[1]) {
      const rawValue = match[1].trim()
      try {
        token = decodeURIComponent(rawValue)
      } catch {
        token = rawValue
      }
    }
  }

  return { token, cookies: cookieString }
}

/**
 * Side effect: Login with credentials
 * Returns cookies from response for maintaining session
 */
async function login(
  request: APIRequestContext,
  credentials: { email: string; password: string },
  csrfData: CsrfData,
): Promise<{
  response: Awaited<ReturnType<APIRequestContext['post']>>
  body: unknown
  cookies: string
}> {
  const headers = createHeaders(undefined, true) // POST request, include Content-Type

  // X-XSRF-TOKEN header is required for Laravel CSRF protection
  // The token must be the URL-decoded value from the XSRF-TOKEN cookie
  if (csrfData.token) {
    headers['X-XSRF-TOKEN'] = csrfData.token
  }

  // Cookie header must include all cookies (XSRF-TOKEN and laravel_session if present)
  // Note: laravel_session is only set after successful login, so it may not be present yet
  if (csrfData.cookies) {
    headers.Cookie = csrfData.cookies
  }

  const response = await request.post(buildUrl('/login'), {
    data: credentials,
    headers,
    ignoreHTTPSErrors: true,
  })

  const body = await response.json()

  // Extract and merge cookies from login response
  // IMPORTANT: Playwright returns Set-Cookie headers as a single string with newlines (\n)
  // separating multiple cookies. We need to split on newlines first.
  const loginCookieHeader = response.headers()['set-cookie']
  
  // Handle Set-Cookie header: it can be a string with newlines, an array, or undefined
  let loginCookieHeadersArray: string[] = []
  if (Array.isArray(loginCookieHeader)) {
    loginCookieHeadersArray = loginCookieHeader
  } else if (typeof loginCookieHeader === 'string') {
    // Split on newlines to handle multiple cookies in a single string
    loginCookieHeadersArray = loginCookieHeader.split('\n').filter(Boolean)
  }
  
  // Extract cookies from all headers
  const newCookies: string[] = []
  for (const header of loginCookieHeadersArray) {
    const extracted = extractCookieValue(header)
    newCookies.push(...extracted)
  }
  
  const mergedCookies = mergeCookies(csrfData.cookies, newCookies)

  return { response, body, cookies: mergedCookies }
}

/**
 * Side effect: Get current authenticated user
 */
async function getCurrentUser(
  request: APIRequestContext,
  cookies?: string,
): Promise<{
  response: Awaited<ReturnType<APIRequestContext['get']>>
  body: unknown
}> {
  const headers = createHeaders(undefined, false) // GET request, no Content-Type
  if (cookies) {
    headers.Cookie = cookies
  }

  const response = await request.get(buildUrl('/api/user/current'), {
    headers,
    ignoreHTTPSErrors: true,
  })

  const body = await response.json()

  return { response, body }
}

/**
 * Side effect: Logout
 * Returns cookies from response (should be empty after logout)
 */
async function logout(
  request: APIRequestContext,
  cookies?: string,
): Promise<{
  response: Awaited<ReturnType<APIRequestContext['post']>>
  body: unknown
  cookies: string
}> {
  // Get fresh CSRF token for logout request
  const csrfData = await getCsrfTokenAndCookies(request, cookies)

  const headers = createHeaders(undefined, true) // POST request, include Content-Type

  // X-XSRF-TOKEN header is required for Laravel CSRF protection
  if (csrfData.token) {
    headers['X-XSRF-TOKEN'] = csrfData.token
  }

  // Cookie header must include all cookies
  if (csrfData.cookies) {
    headers.Cookie = csrfData.cookies
  }

  const response = await request.post(buildUrl('/logout'), {
    headers,
    ignoreHTTPSErrors: true,
  })

  // Handle response body (logout might return 204 No Content or 200 with JSON)
  let body: unknown = null
  if (response.status() === 204) {
    // 204 No Content - no body
    body = null
  } else {
    // Try to parse JSON if status is 200
    try {
      const contentType = response.headers()['content-type']
      if (contentType && contentType.includes('application/json')) {
        body = await response.json()
      }
    } catch {
      // If JSON parsing fails, body remains null
      body = null
    }
  }

  // Extract and merge cookies from logout response
  // IMPORTANT: Playwright returns Set-Cookie headers as a single string with newlines (\n)
  // separating multiple cookies. We need to split on newlines first.
  const logoutCookieHeader = response.headers()['set-cookie']
  
  // Handle Set-Cookie header: it can be a string with newlines, an array, or undefined
  let logoutCookieHeadersArray: string[] = []
  if (Array.isArray(logoutCookieHeader)) {
    logoutCookieHeadersArray = logoutCookieHeader
  } else if (typeof logoutCookieHeader === 'string') {
    // Split on newlines to handle multiple cookies in a single string
    logoutCookieHeadersArray = logoutCookieHeader.split('\n').filter(Boolean)
  }
  
  // Extract cookies from all headers
  const newCookies: string[] = []
  for (const header of logoutCookieHeadersArray) {
    const extracted = extractCookieValue(header)
    newCookies.push(...extracted)
  }
  
  const mergedCookies = mergeCookies(csrfData.cookies, newCookies)

  return { response, body, cookies: mergedCookies }
}

// ============================================================================
// Tests
// ============================================================================

test.describe('Authentication API', () => {
  test('should login successfully and validate user data', async ({ request }) => {
    // Arrange - Pure data preparation
    const credentials = {
      email: testusers.test.email,
      password: testusers.test.password,
    }

    // Act - Get CSRF cookie (side effect)
    const csrfData = await getCsrfTokenAndCookies(request)

    // Assert - CSRF token should be present
    expect(csrfData.token).not.toBeNull()
    expect(csrfData.token).toBeTruthy()

    // Act - Login with credentials (side effect)
    const { response: loginResponse, body: loginBody, cookies: loginCookies } = await login(
      request,
      credentials,
      csrfData,
    )

    // Debug: Log details if login fails
    if (loginResponse.status() !== 200) {
      console.log('Login failed with status:', loginResponse.status())
      console.log('CSRF Token (decoded):', csrfData.token)
      console.log('CSRF Token length:', csrfData.token?.length)
      console.log('CSRF Cookies:', csrfData.cookies)
      // Extract XSRF-TOKEN from cookies and decode it to compare
      const cookieMatch = csrfData.cookies?.match(/XSRF-TOKEN=([^;]+)/)
      if (cookieMatch && cookieMatch[1]) {
        const cookieValue = cookieMatch[1]
        const decodedCookieValue = decodeURIComponent(cookieValue)
        console.log('Cookie XSRF-TOKEN value (encoded):', cookieValue)
        console.log('Cookie XSRF-TOKEN value (decoded):', decodedCookieValue)
        console.log('Token matches cookie?', csrfData.token === decodedCookieValue)
        console.log('Token ends with =?', csrfData.token?.endsWith('='))
        console.log('Decoded cookie ends with =?', decodedCookieValue.endsWith('='))
      }
      // Check if laravel-session cookie is present (note: cookie name uses hyphen, not underscore)
      const sessionMatch = csrfData.cookies?.match(/laravel-session=([^;]+)/)
      console.log('laravel-session present?', !!sessionMatch)
      // Log request headers that were sent
      console.log('Request headers sent:', JSON.stringify({
        'X-XSRF-TOKEN': csrfData.token,
        'Cookie': csrfData.cookies,
        'Origin': frontendURL ? new URL(frontendURL).origin : undefined,
        'Referer': frontendURL ? `${new URL(frontendURL).origin}/` : undefined,
      }, null, 2))
      console.log('Response body:', JSON.stringify(loginBody, null, 2))
    }

    // Assert - Login should succeed
    // If login fails with 419, it means CSRF token mismatch
    // This usually happens if the X-XSRF-TOKEN header doesn't match the XSRF-TOKEN cookie
    expect(loginResponse.status()).toBe(200)
    expect(loginBody).toHaveProperty('id')
    expect(loginBody).toHaveProperty('name')
    expect(loginBody).toHaveProperty('email')
    expect(loginBody).toHaveProperty('current_team_id')
    expect((loginBody as { email: string }).email).toBe(credentials.email)

    // Act - Get current user to verify authentication and validate full user data (side effect)
    const { response: userResponse, body: userBody } = await getCurrentUser(request, loginCookies)

    // Assert - User should be authenticated and response structure should match IResponse<User>
    expect(userResponse.status()).toBe(200)
    expect(userBody).toHaveProperty('success', true)
    expect(userBody).toHaveProperty('code', 200)
    expect(userBody).toHaveProperty('message')
    expect(userBody).toHaveProperty('data')
    expect(userBody).toHaveProperty('extra')

    // Assert - User data structure matches User interface
    const userData = (userBody as { data: unknown }).data
    expect(userData).toHaveProperty('id')
    expect(userData).toHaveProperty('name')
    expect(userData).toHaveProperty('email')
    expect(userData).toHaveProperty('email_verified_at')
    expect(userData).toHaveProperty('current_team_id')
    expect(userData).toHaveProperty('profile_photo_path')
    expect(userData).toHaveProperty('created_at')
    expect(userData).toHaveProperty('updated_at')

    // Assert - User data matches login credentials
    expect((userData as { email: string }).email).toBe(credentials.email)
    expect((userData as { name: string }).name).toBe(testusers.test.name)
  })

  test('should logout successfully and verify authentication is terminated', async ({ request }) => {
    // Arrange - Pure data preparation
    const credentials = {
      email: testusers.test.email,
      password: testusers.test.password,
    }

    // Act - Login first (side effect)
    const csrfData = await getCsrfTokenAndCookies(request)
    const { cookies: loginCookies } = await login(request, credentials, csrfData)

    // Verify user is authenticated before logout
    const { response: beforeLogoutResponse } = await getCurrentUser(request, loginCookies)
    expect(beforeLogoutResponse.status()).toBe(200)

    // Act - Logout (side effect)
    const { response: logoutResponse, body: logoutBody, cookies: logoutCookies } = await logout(
      request,
      loginCookies,
    )

    // Assert - Logout should succeed
    // Laravel logout returns 204 No Content (no body) or 200 with JSON response
    expect([200, 204]).toContain(logoutResponse.status())
    
    // If status is 200, check response structure (IResponse<null>)
    if (logoutResponse.status() === 200 && logoutBody) {
      expect(logoutBody).toHaveProperty('success', true)
      expect(logoutBody).toHaveProperty('code', 200)
      expect(logoutBody).toHaveProperty('message')
      // data might be null or not present
      if ('data' in (logoutBody as Record<string, unknown>)) {
        expect((logoutBody as { data: unknown }).data).toBeNull()
      }
      expect(logoutBody).toHaveProperty('extra')
    }
    // If status is 204, body should be null (No Content)

    // Act - Verify authentication is terminated (side effect)
    const { response: afterLogoutResponse } = await getCurrentUser(request, logoutCookies)

    // Assert - User should not be authenticated after logout
    expect(afterLogoutResponse.status()).toBe(401)
  })
})

