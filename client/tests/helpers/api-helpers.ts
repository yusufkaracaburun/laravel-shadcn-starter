import type { APIRequestContext, APIResponse } from '@playwright/test'

import process from 'node:process'

const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface CsrfData {
  token: string | null
  cookies: string
}

// ============================================================================
// Pure Functions (no side effects)
// ============================================================================

/**
 * Pure function: Creates headers object without side effects
 */
export const createHeaders = (customHeaders?: Record<string, string>): Record<string, string> => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  ...customHeaders,
})

/**
 * Pure function: Builds full URL from endpoint
 */
export const buildUrl = (endpoint: string): string => {
  const base = endpoint.startsWith('http') ? '' : apiURL
  return `${base}${endpoint}`
}

/**
 * Pure function: Extracts cookie values from header
 */
export const extractCookieValue = (cookieHeader: string | string[] | undefined): string[] => {
  let cookies: string[] = []
  if (Array.isArray(cookieHeader)) {
    cookies = cookieHeader
  }
  else if (cookieHeader) {
    cookies = [cookieHeader]
  }

  // Extract just the cookie name=value part (before the first semicolon)
  // The value may be URL-encoded, which is fine - we'll decode it when extracting the token
  return cookies.map((cookie) => {
    const match = cookie.match(/^([^;]+)/)
    return match ? match[1].trim() : ''
  }).filter(Boolean)
}

/**
 * Pure function: Builds authenticated headers from CSRF data
 */
export const buildAuthenticatedHeaders = (
  csrfData: CsrfData,
  customHeaders?: Record<string, string>,
): Record<string, string> => {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Origin': apiURL,
    'Referer': `${apiURL}/`,
    ...customHeaders,
  }

  if (csrfData.token) {
    headers['X-XSRF-TOKEN'] = csrfData.token
  }

  if (csrfData.cookies) {
    headers.Cookie = csrfData.cookies
  }

  return headers
}

// ============================================================================
// Side Effect Functions (API calls)
// ============================================================================

/**
 * Side effect: Gets CSRF cookie from server
 */
export async function getCsrfCookie(request: APIRequestContext): Promise<APIResponse> {
  const response = await request.get(buildUrl('/sanctum/csrf-cookie'), {
    headers: createHeaders(),
  })
  return response
}

/**
 * Side effect: Gets CSRF token and cookies
 */
export async function getCsrfTokenAndCookies(request: APIRequestContext): Promise<CsrfData> {
  const csrfResponse = await getCsrfCookie(request)
  const csrfCookieHeader = csrfResponse.headers()['set-cookie']

  // Extract XSRF token from cookies
  // Laravel sets the XSRF-TOKEN cookie with a URL-encoded encrypted value
  // The cookie in Set-Cookie header is: XSRF-TOKEN=encrypted_value%3D; expires=...
  // When sending back:
  // - Cookie header: XSRF-TOKEN=encrypted_value%3D (URL-encoded, as received)
  // - X-XSRF-TOKEN header: encrypted_value= (URL-decoded encrypted value)
  // Laravel will decrypt both and compare the decrypted values

  let token: string | null = null
  const cookieHeaders = Array.isArray(csrfCookieHeader) ? csrfCookieHeader : (csrfCookieHeader ? [csrfCookieHeader] : [])

  // Extract token from full cookie header (value is URL-encoded in Set-Cookie)
  for (const cookie of cookieHeaders) {
    const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match && match[1]) {
      const urlEncodedValue = match[1].trim()
      try {
        // Decode URL-encoding to get the encrypted value for X-XSRF-TOKEN header
        // This is the encrypted session token that Laravel will decrypt
        token = decodeURIComponent(urlEncodedValue)
      }
      catch {
        // If decoding fails, use raw value (shouldn't happen with valid cookies)
        token = urlEncodedValue
      }
      break
    }
  }

  // Extract all cookie values for sending in Cookie header
  // These should be kept URL-encoded as they appear in Set-Cookie
  // Format: "name1=value1; name2=value2"
  const cookieValues = extractCookieValue(csrfCookieHeader)
  const cookieString = cookieValues.length > 0 ? cookieValues.join('; ') : ''

  // Fallback: if we didn't find token in headers, try extracting from cookie string
  if (!token && cookieString) {
    const match = cookieString.match(/XSRF-TOKEN=([^;]+)/)
    if (match && match[1]) {
      const rawValue = match[1].trim()
      try {
        token = decodeURIComponent(rawValue)
      }
      catch {
        token = rawValue
      }
    }
  }

  return { token, cookies: cookieString }
}

// ============================================================================
// Curried API Functions
// ============================================================================

export interface RequestOptions {
  headers?: Record<string, string>
  data?: unknown
}

/**
 * Curried function: Returns configured API request functions
 */
export const apiRequest = (request: APIRequestContext) => ({
  get: async (endpoint: string, options?: RequestOptions): Promise<APIResponse> => {
    return request.get(buildUrl(endpoint), {
      headers: createHeaders(options?.headers),
    })
  },

  post: async (endpoint: string, data: unknown, options?: RequestOptions): Promise<APIResponse> => {
    return request.post(buildUrl(endpoint), {
      data,
      headers: createHeaders(options?.headers),
    })
  },

  put: async (endpoint: string, data: unknown, options?: RequestOptions): Promise<APIResponse> => {
    return request.put(buildUrl(endpoint), {
      data,
      headers: createHeaders(options?.headers),
    })
  },

  delete: async (endpoint: string, options?: RequestOptions): Promise<APIResponse> => {
    return request.delete(buildUrl(endpoint), {
      headers: createHeaders(options?.headers),
    })
  },
})

// ============================================================================
// Composed Authentication Functions
// ============================================================================

/**
 * Higher-order function: Creates authentication API functions
 */
export const createAuthApi = (request: APIRequestContext) => {
  const api = apiRequest(request)

  return {
    /**
     * Login with credentials
     */
    login: async (credentials: LoginCredentials): Promise<APIResponse> => {
      const csrfData = await getCsrfTokenAndCookies(request)
      const headers = buildAuthenticatedHeaders(csrfData)

      return api.post('/login', credentials, { headers })
    },

    /**
     * Register new user
     */
    register: async (data: RegisterData): Promise<APIResponse> => {
      const csrfData = await getCsrfTokenAndCookies(request)
      const headers = buildAuthenticatedHeaders(csrfData)

      return api.post('/register', data, { headers })
    },

    /**
     * Logout current user
     */
    logout: async (): Promise<APIResponse> => {
      const csrfData = await getCsrfTokenAndCookies(request)
      const headers = buildAuthenticatedHeaders(csrfData)

      return api.post('/logout', undefined, { headers })
    },

    /**
     * Get current authenticated user
     */
    getCurrentUser: async (): Promise<APIResponse> => {
      const csrfData = await getCsrfTokenAndCookies(request)
      const headers = buildAuthenticatedHeaders(csrfData)

      return api.get('/api/user/current', { headers })
    },
  }
}

// ============================================================================
// Backward Compatibility: Keep existing function exports for E2E tests
// ============================================================================

/**
 * @deprecated Use createAuthApi(request).register() instead
 * Kept for backward compatibility with E2E tests
 */
export async function registerUser(
  request: APIRequestContext,
  data: RegisterData,
  cookies?: string,
  token?: string,
): Promise<APIResponse> {
  // Always get fresh CSRF cookies and token to ensure they're valid
  const csrfData = await getCsrfTokenAndCookies(request)
  const cookieString = cookies || csrfData.cookies
  const xsrfToken = token || csrfData.token

  if (!xsrfToken) {
    throw new Error('Failed to extract XSRF-TOKEN from CSRF cookie response')
  }

  const headers = buildAuthenticatedHeaders({ token: xsrfToken, cookies: cookieString })

  return request.post(buildUrl('/register'), {
    data,
    headers,
  })
}

