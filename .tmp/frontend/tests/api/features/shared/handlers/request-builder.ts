/**
 * Builds request headers for Laravel Sanctum SPA authentication
 */
export class RequestBuilder {
  private readonly origin = 'http://localhost:5173';

  /**
   * Get base headers required for Sanctum SPA requests
   */
  getBaseHeaders(): Record<string, string> {
    return {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'Origin': this.origin,
    };
  }

  /**
   * Get headers with CSRF token
   */
  getHeadersWithCsrf(csrfToken: string | null, includeContentType = false): Record<string, string> {
    const headers = this.getBaseHeaders();

    if (csrfToken) {
      headers['X-XSRF-TOKEN'] = csrfToken;
    }

    if (includeContentType) {
      headers['Content-Type'] = 'application/json';
    }

    return headers;
  }
}

