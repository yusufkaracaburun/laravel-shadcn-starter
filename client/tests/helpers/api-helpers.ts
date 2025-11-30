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
 */
export async function getCsrfTokenAndCookies(request: APIRequestContext): Promise<CsrfData> {
  // First, get the CSRF cookie - this also sets laravel_session cookie
  const csrfResponse = await getCsrfCookie(request)
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
  // Cache CSRF token to avoid hitting rate limits
  let cachedToken: string | null = null
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
        
        // Add existing cookies
        if (sessionCookies) {
          sessionCookies.split('; ').forEach(cookie => {
            const [name] = cookie.split('=')
            if (name && name.trim()) {
              cookieMap.set(name.trim(), cookie)
            }
          })
        }
        
        // Add/update with new cookies (new cookies take precedence)
        cookieValues.forEach(cookie => {
          if (cookie) {
            const [name] = cookie.split('=')
            if (name && name.trim()) {
              cookieMap.set(name.trim(), cookie)
            }
          }
        })
        
        sessionCookies = Array.from(cookieMap.values()).join('; ')
      }
    }
  }

  /**
   * Get CSRF token for the X-XSRF-TOKEN header
   * Manually maintains session cookies to ensure session persistence
   */
  const getCsrfToken = async (forceRefresh = false): Promise<string | null> => {
    // Return cached token if available and not forcing refresh
    if (cachedToken && !forceRefresh) {
      return cachedToken
    }

    // Small random delay to stagger requests when tests run in parallel
    await new Promise(resolve => setTimeout(resolve, Math.random() * 300))

    // Get CSRF cookie, sending existing session cookies to maintain the session
    const headers: Record<string, string> = createHeaders()
    if (sessionCookies) {
      headers.Cookie = sessionCookies
    }
    
    const csrfResponse = await request.get(buildUrl('/sanctum/csrf-cookie'), { headers })
    
    // Update session cookies from response
    updateCookies(csrfResponse)
    
    const csrfCookieHeader = csrfResponse.headers()['set-cookie']

    // Extract XSRF token for the X-XSRF-TOKEN header
    let token: string | null = null
    const cookieHeaders = Array.isArray(csrfCookieHeader) ? csrfCookieHeader : (csrfCookieHeader ? [csrfCookieHeader] : [])

    for (const cookie of cookieHeaders) {
      const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
      if (match && match[1]) {
        const urlEncodedValue = match[1].trim()
        try {
          token = decodeURIComponent(urlEncodedValue)
        }
        catch {
          token = urlEncodedValue
        }
        break
      }
    }

    // Fallback: try extracting from cookie string if not found in headers
    if (!token) {
      const cookieValues = extractCookieValue(csrfCookieHeader)
      const cookieString = cookieValues.join('; ')
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

    // Cache the token
    cachedToken = token
    return token
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
        // Clear cached token on retry to ensure fresh token
        cachedToken = null
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
        // Get fresh token for login request
        const token = await getCsrfToken(true)
        const headers = buildAuthenticatedHeaders({ token, cookies: sessionCookies })
        const response = await api.post('/login', credentials, { headers })
        // Update session cookies from login response (important for maintaining session)
        updateCookies(response)
        // After successful login, clear cached token so next request gets fresh one
        // that matches the authenticated session
        if (response.status() === 200) {
          cachedToken = null
        }
        return response
      })
    },

    /**
     * Register new user
     */
    register: async (data: RegisterData): Promise<APIResponse> => {
      return retryOnRateLimit(async () => {
        // Get fresh token for register request
        const token = await getCsrfToken(true)
        const headers = buildAuthenticatedHeaders({ token, cookies: sessionCookies })
        const response = await api.post('/register', data, { headers })
        // Update session cookies from register response (important for maintaining session)
        updateCookies(response)
        // After successful register, clear cached token so next request gets fresh one
        // that matches the authenticated session
        if (response.status() === 201) {
          cachedToken = null
        }
        return response
      })
    },

    /**
     * Logout current user
     */
    logout: async (): Promise<APIResponse> => {
      return retryOnRateLimit(async () => {
        // Get fresh token that matches current session
        const token = await getCsrfToken(true)
        const headers = buildAuthenticatedHeaders({ token, cookies: sessionCookies })
        const response = await api.post('/logout', undefined, { headers })
        // Update session cookies (logout may clear session)
        updateCookies(response)
        // Clear cached token after logout
        cachedToken = null
        return response
      })
    },

    /**
     * Get current authenticated user
     */
    getCurrentUser: async (): Promise<APIResponse> => {
      return retryOnRateLimit(async () => {
        // Get fresh token that matches current session
        // Send session cookies to ensure we're using the authenticated session
        const token = await getCsrfToken(true)
        const headers = buildAuthenticatedHeaders({ token, cookies: sessionCookies })
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
