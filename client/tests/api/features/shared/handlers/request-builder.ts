/**
 * Builds request headers for Laravel Sanctum SPA authentication
 */
export class RequestBuilder {
  private readonly apiURL: string
  private readonly frontendURL: string

  constructor() {
    this.apiURL = process.env.VITE_API_BASE_URL || 'http://localhost:8000'
    this.frontendURL = process.env.VITE_APP_URL || 'http://localhost:5173'
  }

  /**
   * Get base headers required for Sanctum SPA requests
   * Includes Origin and Referer headers to match browser behavior
   */
  getBaseHeaders(): Record<string, string> {
    // Parse frontend URL to extract origin (protocol + host + port)
    const url = new URL(this.frontendURL)
    const origin = url.origin

    return {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      Origin: origin,
      Referer: `${origin}/`,
    }
  }

  /**
   * Get headers with CSRF token
   * @param csrfToken - URL-decoded CSRF token for X-XSRF-TOKEN header
   * @param includeContentType - Whether to include Content-Type header (for POST/PUT/PATCH)
   * @param cookies - Cookie string to include in Cookie header
   */
  getHeadersWithCsrf(
    csrfToken: string | null,
    includeContentType = false,
    cookies?: string,
  ): Record<string, string> {
    const headers = this.getBaseHeaders()

    if (csrfToken) {
      headers['X-XSRF-TOKEN'] = csrfToken
    }

    if (includeContentType) {
      headers['Content-Type'] = 'application/json'
    }

    if (cookies) {
      headers.Cookie = cookies
    }

    return headers
  }
}
