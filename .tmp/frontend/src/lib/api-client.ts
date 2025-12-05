import axios, { type AxiosInstance, type InternalAxiosRequestConfig, type AxiosResponse } from 'axios'
import { VITE_API_BASE_URL } from '@/utils/env'
import { getCookie } from '@/plugins/cookie'

/**
 * Handles CSRF token extraction from Laravel responses
 * Follows Laravel CSRF documentation: URL-decode the XSRF-TOKEN cookie value
 * 
 * Uses CookieStore API (https://developer.mozilla.org/en-US/docs/Web/API/CookieStore)
 * to reliably get cookies from the browser
 */
class CsrfHandler {
  private csrfToken: string | null = null

  /**
   * Extract CSRF token using CookieStore API
   * The cookie value is URL-encoded and needs to be decoded for the X-XSRF-TOKEN header
   * 
   * Uses CookieStore.get() to asynchronously get the XSRF-TOKEN cookie
   * Reference: https://developer.mozilla.org/en-US/docs/Web/API/CookieStore/get
   */
  async extractFromResponse(_response: AxiosResponse): Promise<void> {
    // Use CookieStore API if available (modern browsers)
    if (typeof cookieStore !== 'undefined') {
      try {
        const cookie = await cookieStore.get('XSRF-TOKEN')
        if (cookie?.value) {
          try {
            // URL-decode the cookie value (Laravel encodes it)
            this.csrfToken = decodeURIComponent(cookie.value)
          } catch {
            this.csrfToken = cookie.value
          }
        }
      } catch (error) {
        // CookieStore API failed, fallback to document.cookie
        console.warn('CookieStore API failed, falling back to document.cookie:', error)
        this.extractFromDocumentCookie()
      }
    } else {
      // Fallback to document.cookie for older browsers
      this.extractFromDocumentCookie()
    }
  }

  /**
   * Extract CSRF token from document.cookie (fallback method)
   */
  private extractFromDocumentCookie(): void {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match) {
      try {
        this.csrfToken = decodeURIComponent(match[1])
      } catch {
        this.csrfToken = match[1]
      }
    }
  }

  /**
   * Get stored CSRF token
   * If not stored, try to get it from CookieStore or document.cookie
   */
  async getToken(): Promise<string | null> {
    // If we have a stored token, return it
    if (this.csrfToken) {
      return this.csrfToken
    }

    // Try to get from CookieStore API
    if (typeof cookieStore !== 'undefined') {
      try {
        const cookie = await cookieStore.get('XSRF-TOKEN')
        if (cookie?.value) {
          try {
            this.csrfToken = decodeURIComponent(cookie.value)
            return this.csrfToken
          } catch {
            this.csrfToken = cookie.value
            return this.csrfToken
          }
        }
      } catch {
        // CookieStore failed, continue to fallback
      }
    }

    // Fallback to document.cookie
    this.extractFromDocumentCookie()
    return this.csrfToken
  }

  /**
   * Synchronous getToken for use in request interceptor
   * Returns stored token or null (will be updated async)
   */
  getTokenSync(): string | null {
    return this.csrfToken
  }

  clear(): void {
    this.csrfToken = null
  }
}

// Create singleton instance (exact same pattern as test BaseClient)
const csrfHandler = new CsrfHandler()

/**
 * Get CSRF token (exported for use in auth.service)
 * Returns stored token synchronously (may be null if not yet extracted)
 * For async retrieval, use the internal getCsrfToken() function
 */
export function getCsrfTokenFromCookie(): string | null {
  return csrfHandler.getTokenSync()
}

/**
 * Internal function used by request interceptor
 * Returns Promise to support async CookieStore API
 */
async function getCsrfToken(): Promise<string | null> {
  const token = await getCookie('XSRF-TOKEN')
  console.warn("VALUE", token?.value)
  return token?.value || null}

// Create axios instance with Sanctum SPA configuration
const apiClient: AxiosInstance = axios.create({
  baseURL: VITE_API_BASE_URL,
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'X-XSRF-TOKEN': await getCsrfToken(),
  },
})

// Request interceptor - automatically add CSRF token to all requests
apiClient.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    // Get CSRF token (async to support CookieStore API)
    const csrfToken = await getCsrfToken()
    
    // Add CSRF token to headers
    // Required for all state-changing requests (POST, PUT, PATCH, DELETE)
    // Also required for GET requests to protected endpoints (like /api/user)
    if (csrfToken && config.headers) {
      config.headers['X-XSRF-TOKEN'] = csrfToken
    }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - extract CSRF token from responses using CookieStore API
apiClient.interceptors.response.use(
  async (response: AxiosResponse) => {
    // Extract CSRF token using CookieStore API (after cookie is set by browser)
    await csrfHandler.extractFromResponse(response)
    return response
  },
  async (error) => {
    // Handle common errors (401, 403, 500, etc.)
    if (error.response) {
      // Also extract CSRF token from error responses
      await csrfHandler.extractFromResponse(error.response)
      
      switch (error.response.status) {
        case 401:
          // Unauthorized - user needs to login
          console.error('Unauthorized: Please login')
          break
        case 403:
          // Forbidden - user doesn't have permission
          console.error('Forbidden: You do not have permission')
          break
        case 422:
          // Validation errors
          console.error('Validation errors:', error.response.data.errors)
          break
        case 500:
          // Server error
          console.error('Server error: Please try again later')
          break
      }
    }
    return Promise.reject(error)
  }
)

export default apiClient

