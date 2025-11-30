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
export function createHeaders(customHeaders?: Record<string, string>): Record<string, string> {
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...customHeaders,
  }
}

/**
 * Pure function: Builds full URL from endpoint
 */
export function buildUrl(endpoint: string): string {
  const base = endpoint.startsWith('http') ? '' : apiURL
  return `${base}${endpoint}`
}

/**
 * Pure function: Extracts cookie values from header
 */
export function extractCookieValue(cookieHeader: string | string[] | undefined): string[] {
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
 * Sets X-XSRF-TOKEN header and session cookies
 */
export function buildAuthenticatedHeaders(csrfData: CsrfData, customHeaders?: Record<string, string>): Record<string, string> {
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

  // Manually set cookies to ensure session persistence
  // Playwright's APIRequestContext doesn't reliably maintain cookies for API requests
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
 * Playwright's APIRequestContext automatically maintains cookies between requests
 * Includes retry logic for rate limiting (429 errors)
 */
export async function getCsrfCookie(request: APIRequestContext): Promise<APIResponse> {
  const headers: Record<string, string> = createHeaders()

  // Retry logic for rate limiting
  let lastError: Error | null = null
  for (let attempt = 0; attempt < 3; attempt++) {
    try {
      if (attempt > 0) {
        // Wait before retry with exponential backoff
        await new Promise(resolve => setTimeout(resolve, attempt * 100))
      }

      const response = await request.get(buildUrl('/sanctum/csrf-cookie'), {
        headers,
      })

      // If we get a 429, retry
      if (response.status() === 429 && attempt < 2) {
        continue
      }

      return response
    }
    catch (error) {
      lastError = error as Error
      if (attempt < 2) {
        continue
      }
      throw error
    }
  }

  // This shouldn't be reached, but TypeScript needs it
  throw lastError || new Error('Failed to get CSRF cookie')
}

/**
 * Side effect: Gets CSRF token and all cookies from response
 * We extract all cookies (including laravel_session) to maintain session state
 * between requests, as Playwright's APIRequestContext may not always maintain
 * cookies properly for API requests.
 * If sessionCookies are provided, sends them to maintain the session
 */
export async function getCsrfTokenAndCookies(request: APIRequestContext, sessionCookies?: string): Promise<CsrfData> {
  // Get CSRF cookie, sending existing session cookies to maintain the session
  const headers: Record<string, string> = createHeaders()
  if (sessionCookies) {
    headers.Cookie = sessionCookies
  }
  
  const csrfResponse = await request.get(buildUrl('/sanctum/csrf-cookie'), { headers })
  const csrfCookieHeader = csrfResponse.headers()['set-cookie']

  // Extract XSRF token from cookies for the X-XSRF-TOKEN header
  // Laravel sets the XSRF-TOKEN cookie with a URL-encoded encrypted value
  // The cookie in Set-Cookie header is: XSRF-TOKEN=encrypted_value%3D; expires=...
  // When sending back:
  // - Cookie header: XSRF-TOKEN=encrypted_value%3D (URL-encoded, as received)
  // - X-XSRF-TOKEN header: encrypted_value= (URL-decoded encrypted value)

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

  // Extract ALL cookie values for sending in Cookie header
  // This includes both XSRF-TOKEN and laravel_session cookies
  // These should be kept URL-encoded as they appear in Set-Cookie
  // Format: "name1=value1; name2=value2"
  const cookieValues = extractCookieValue(csrfCookieHeader)
  
  // Merge with existing session cookies if provided
  const cookieMap = new Map<string, string>()
  
  // Add existing cookies first (to preserve session, especially laravel_session)
  if (sessionCookies) {
    sessionCookies.split('; ').forEach(cookie => {
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
  
  // Add/update with new cookies from CSRF response (new cookies take precedence)
  cookieValues.forEach(cookie => {
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
  
  const cookieString = Array.from(cookieMap.values())
    .filter(Boolean)
    .join('; ')

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
 * Note: Playwright's APIRequestContext automatically maintains cookies
 * between requests, so we don't need to manually manage them
 */
export function apiRequest(request: APIRequestContext) {
  return {
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
  }
}

// ============================================================================
// Composed Authentication Functions
// ============================================================================

/**
 * Higher-order function: Creates authentication API functions
 * Relies on Playwright's APIRequestContext to automatically maintain cookies
 * We only need to extract and send the X-XSRF-TOKEN header
 */
export function createAuthApi(request: APIRequestContext) {
  const api = apiRequest(request)
  // Manually maintain session cookies since Playwright's APIRequestContext
  // doesn't reliably maintain cookies for API requests
  let sessionCookies: string = ''

  /**
   * Extract and merge cookies from a response
   * Updates sessionCookies with new cookies, preserving existing ones
   * Cookies are stored as "name=value" pairs, ready to be sent in Cookie header
   */
  const updateCookies = (response: APIResponse): void => {
    const setCookieHeader = response.headers()['set-cookie']
    if (setCookieHeader) {
      const cookieValues = extractCookieValue(setCookieHeader)
      if (cookieValues.length > 0) {
        // Merge new cookies with existing
        const cookieMap = new Map<string, string>()
        
        // Add existing cookies first (to preserve them)
        if (sessionCookies) {
          sessionCookies.split('; ').forEach(cookie => {
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
        cookieValues.forEach(cookie => {
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
        
        // Rebuild cookie string, ensuring proper format
        sessionCookies = Array.from(cookieMap.values())
          .filter(Boolean)
          .join('; ')
      }
    }
  }

  /**
   * Get CSRF token and cookies, maintaining session
   * Always gets fresh token and cookies to ensure they match
   * Sends existing session cookies to maintain the session
   */
  const getCsrfData = async (): Promise<CsrfData> => {
    // Small random delay to stagger requests when tests run in parallel
    await new Promise(resolve => setTimeout(resolve, Math.random() * 300))
    
    // Get CSRF token and cookies, sending existing session cookies to maintain session
    // getCsrfTokenAndCookies already merges cookies, so we just use the result
    const csrfData = await getCsrfTokenAndCookies(request, sessionCookies)
    
    // Update session cookies from response (getCsrfTokenAndCookies already merged them)
    sessionCookies = csrfData.cookies
    
    return csrfData
  }

  /**
   * Helper to retry API calls on rate limit errors (429)
   * Uses exponential backoff with jitter to avoid thundering herd
   */
  const retryOnRateLimit = async (
    fn: () => Promise<APIResponse>,
    maxRetries = 6,
  ): Promise<APIResponse> => {
    for (let attempt = 0; attempt < maxRetries; attempt++) {
      if (attempt > 0) {
        // Exponential backoff with jitter: base delay * 2^attempt + random(0-200ms)
        const baseDelay = 300
        const exponentialDelay = baseDelay * Math.pow(2, attempt - 1)
        const jitter = Math.random() * 200
        await new Promise(resolve => setTimeout(resolve, exponentialDelay + jitter))
      }

      const response = await fn()

      // If we get a 429, retry (unless it's the last attempt)
      if (response.status() === 429 && attempt < maxRetries - 1) {
        continue
      }

      return response
    }

    // This shouldn't be reached, but TypeScript needs it
    throw new Error('Max retries exceeded')
  }

  return {
    /**
     * Login with credentials
     */
    login: async (credentials: LoginCredentials): Promise<APIResponse> => {
      return retryOnRateLimit(async () => {
        // Get fresh CSRF token and cookies, maintaining session
        const csrfData = await getCsrfData()
        const headers = buildAuthenticatedHeaders(csrfData)
        const response = await api.post('/login', credentials, { headers })
        // Update session cookies from login response (important for maintaining session)
        updateCookies(response)
        return response
      })
    },

    /**
     * Register new user
     */
    register: async (data: RegisterData): Promise<APIResponse> => {
      return retryOnRateLimit(async () => {
        // Get fresh CSRF token and cookies, maintaining session
        const csrfData = await getCsrfData()
        const headers = buildAuthenticatedHeaders(csrfData)
        const response = await api.post('/register', data, { headers })
        // Update session cookies from register response (important for maintaining session)
        updateCookies(response)
        return response
      })
    },

    /**
     * Logout current user
     */
    logout: async (): Promise<APIResponse> => {
      return retryOnRateLimit(async () => {
        // Get fresh CSRF token and cookies, maintaining session
        const csrfData = await getCsrfData()
        const headers = buildAuthenticatedHeaders(csrfData)
        const response = await api.post('/logout', undefined, { headers })
        // Update session cookies (logout may clear session)
        updateCookies(response)
        return response
      })
    },

    /**
     * Get current authenticated user
     */
    getCurrentUser: async (): Promise<APIResponse> => {
      return retryOnRateLimit(async () => {
        // Get fresh CSRF token and cookies, maintaining session
        const csrfData = await getCsrfData()
        const headers = buildAuthenticatedHeaders(csrfData)
        return api.get('/api/user/current', { headers })
      })
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
