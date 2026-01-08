import type { APIResponse } from '@playwright/test'

/**
 * Handles CSRF token extraction from Laravel responses
 * Follows Laravel CSRF documentation: URL-decode the XSRF-TOKEN cookie value
 * for the X-XSRF-TOKEN header
 */
export class CsrfHandler {
  private csrfToken: string | null = null

  /**
   * Extract CSRF token from Set-Cookie header
   * The cookie value is URL-encoded and needs to be decoded for the X-XSRF-TOKEN header
   * Handles multiple cookies separated by newlines (\n)
   */
  extractFromResponse(response: APIResponse): void {
    const setCookieHeader = response.headers()['set-cookie']
    if (!setCookieHeader) {
      return
    }

    // Handle Set-Cookie header: it can be a string with newlines, an array, or undefined
    let cookieHeadersArray: string[] = []
    if (Array.isArray(setCookieHeader)) {
      cookieHeadersArray = setCookieHeader
    }
    else if (typeof setCookieHeader === 'string') {
      // Split on newlines to handle multiple cookies in a single string
      cookieHeadersArray = setCookieHeader.split('\n').filter(Boolean)
    }

    // Extract XSRF token from cookies
    for (const cookie of cookieHeadersArray) {
      const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
      if (match && match[1]) {
        const urlEncodedValue = match[1].trim()
        try {
          // Decode URL-encoding to get the encrypted value for X-XSRF-TOKEN header
          // This is the encrypted session token that Laravel will decrypt
          this.csrfToken = decodeURIComponent(urlEncodedValue)
        }
        catch {
          // If decoding fails, use raw value (shouldn't happen with valid cookies)
          this.csrfToken = urlEncodedValue
        }
        break
      }
    }
  }

  /**
   * Get the CSRF token (URL-decoded value for X-XSRF-TOKEN header)
   */
  getToken(): string | null {
    return this.csrfToken
  }

  /**
   * Clear the CSRF token
   */
  clear(): void {
    this.csrfToken = null
  }

  /**
   * Set CSRF token from another handler
   * Used to share CSRF token between client instances
   */
  setToken(token: string | null): void {
    this.csrfToken = token
  }
}
