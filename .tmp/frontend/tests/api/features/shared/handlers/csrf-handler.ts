import { APIResponse } from '@playwright/test';

/**
 * Handles CSRF token extraction from Laravel responses
 * Follows Laravel CSRF documentation: URL-decode the XSRF-TOKEN cookie value
 */
export class CsrfHandler {
  private csrfToken: string | null = null;

  /**
   * Extract CSRF token from Set-Cookie header
   * The cookie value is URL-encoded and needs to be decoded for the X-XSRF-TOKEN header
   */
  extractFromResponse(response: APIResponse): void {
    const setCookieHeader = response.headers()['set-cookie'];
    if (!setCookieHeader) {
      return;
    }

    const cookies = Array.isArray(setCookieHeader) ? setCookieHeader : [setCookieHeader];

    for (const cookie of cookies) {
      const match = cookie.match(/^XSRF-TOKEN=([^;]+)/);
      if (match) {
        try {
          this.csrfToken = decodeURIComponent(match[1]);
        } catch {
          this.csrfToken = match[1];
        }
        break;
      }
    }
  }

  getToken(): string | null {
    return this.csrfToken;
  }

  clear(): void {
    this.csrfToken = null;
  }
}

