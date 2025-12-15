import type { APIResponse } from '@playwright/test'

/**
 * Handles cookie extraction and management from Laravel responses
 * Handles newline-separated cookies in Set-Cookie headers
 */
export class CookieHandler {
  private cookies: string = ''

  /**
   * Extract cookies from Set-Cookie header
   * Handles multiple cookies separated by newlines (\n)
   * Cookies are kept URL-encoded as they appear in Set-Cookie header
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
    } else if (typeof setCookieHeader === 'string') {
      // Split on newlines to handle multiple cookies in a single string
      cookieHeadersArray = setCookieHeader.split('\n').filter(Boolean)
    }

    // Extract cookie values (name=value part before first semicolon)
    const newCookies: string[] = []
    for (const header of cookieHeadersArray) {
      const match = header.match(/^([^;]+)/)
      if (match && match[1]) {
        newCookies.push(match[1].trim())
      }
    }

    // Merge with existing cookies
    this.cookies = this.mergeCookies(this.cookies, newCookies)
  }

  /**
   * Merge existing cookies with new cookies
   * New cookies override existing ones with the same name
   * Cookies are kept URL-encoded as they appear in Set-Cookie header
   */
  private mergeCookies(existingCookies: string, newCookies: string[]): string {
    const cookieMap = new Map<string, string>()

    // Add existing cookies first (to preserve session, especially laravel-session)
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

  /**
   * Get all cookies as a string for Cookie header
   */
  getCookies(): string {
    return this.cookies
  }

  /**
   * Clear all cookies
   */
  clear(): void {
    this.cookies = ''
  }

  /**
   * Set cookies from another cookie handler
   * Used to share cookies between client instances
   */
  setCookies(cookies: string): void {
    this.cookies = cookies
  }
}
