import type { APIRequestContext, APIResponse } from '@playwright/test'

import { CookieHandler } from '../handlers/cookie-handler'
import { CsrfHandler } from '../handlers/csrf-handler'
import { RequestBuilder } from '../handlers/request-builder'

/**
 * Base client class providing common functionality for all API clients
 * Implements Template Method pattern for consistent request handling
 */
export abstract class BaseClient {
  protected readonly csrfHandler: CsrfHandler
  protected readonly cookieHandler: CookieHandler
  protected readonly requestBuilder: RequestBuilder

  constructor(protected readonly request: APIRequestContext) {
    this.csrfHandler = new CsrfHandler()
    this.cookieHandler = new CookieHandler()
    this.requestBuilder = new RequestBuilder()
  }

  /**
   * Build URL from endpoint
   * Uses relative URLs so Playwright's baseURL (from config) is used
   */
  protected buildUrl(endpoint: string): string {
    // If endpoint is already a full URL, use it as-is
    // Otherwise, return relative URL (Playwright will use baseURL from config)
    if (endpoint.startsWith('http')) {
      return endpoint
    }
    return endpoint
  }

  /**
   * Make a GET request with automatic CSRF and cookie handling
   */
  protected async get(
    endpoint: string,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const cookies = this.cookieHandler.getCookies()
    const baseHeaders = this.requestBuilder.getBaseHeaders()
    const headers = options.requireCsrf
      ? this.requestBuilder.getHeadersWithCsrf(
          this.csrfHandler.getToken(),
          false,
          cookies,
        )
      : cookies
        ? { ...baseHeaders, Cookie: cookies }
        : baseHeaders

    const response = await this.request.get(this.buildUrl(endpoint), {
      headers,
      ignoreHTTPSErrors: true,
    })

    // Extract CSRF token and cookies from response
    this.csrfHandler.extractFromResponse(response)
    this.cookieHandler.extractFromResponse(response)

    return response
  }

  /**
   * Make a POST request with automatic CSRF and cookie handling
   */
  protected async post(
    endpoint: string,
    data: unknown,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const cookies = this.cookieHandler.getCookies()
    const headers = this.requestBuilder.getHeadersWithCsrf(
      this.csrfHandler.getToken(),
      true,
      cookies,
    )

    const response = await this.request.post(this.buildUrl(endpoint), {
      data,
      headers: options.requireCsrf !== false ? headers : this.requestBuilder.getBaseHeaders(),
      ignoreHTTPSErrors: true,
    })

    // Extract CSRF token and cookies from response
    this.csrfHandler.extractFromResponse(response)
    this.cookieHandler.extractFromResponse(response)

    return response
  }

  /**
   * Make a PUT request with automatic CSRF and cookie handling
   */
  protected async put(
    endpoint: string,
    data: unknown,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const cookies = this.cookieHandler.getCookies()
    const headers = this.requestBuilder.getHeadersWithCsrf(
      this.csrfHandler.getToken(),
      true,
      cookies,
    )

    const response = await this.request.put(this.buildUrl(endpoint), {
      data,
      headers: options.requireCsrf !== false ? headers : this.requestBuilder.getBaseHeaders(),
      ignoreHTTPSErrors: true,
    })

    // Extract CSRF token and cookies from response
    this.csrfHandler.extractFromResponse(response)
    this.cookieHandler.extractFromResponse(response)

    return response
  }

  /**
   * Make a PATCH request with automatic CSRF and cookie handling
   */
  protected async patch(
    endpoint: string,
    data: unknown,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const cookies = this.cookieHandler.getCookies()
    const headers = this.requestBuilder.getHeadersWithCsrf(
      this.csrfHandler.getToken(),
      true,
      cookies,
    )

    const response = await this.request.patch(this.buildUrl(endpoint), {
      data,
      headers: options.requireCsrf !== false ? headers : this.requestBuilder.getBaseHeaders(),
      ignoreHTTPSErrors: true,
    })

    // Extract CSRF token and cookies from response
    this.csrfHandler.extractFromResponse(response)
    this.cookieHandler.extractFromResponse(response)

    return response
  }

  /**
   * Make a DELETE request with automatic CSRF and cookie handling
   */
  protected async delete(
    endpoint: string,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const cookies = this.cookieHandler.getCookies()
    const baseHeaders = this.requestBuilder.getBaseHeaders()
    const headers = options.requireCsrf !== false
      ? this.requestBuilder.getHeadersWithCsrf(
          this.csrfHandler.getToken(),
          false,
          cookies,
        )
      : cookies
        ? { ...baseHeaders, Cookie: cookies }
        : baseHeaders

    const response = await this.request.delete(this.buildUrl(endpoint), {
      headers,
      ignoreHTTPSErrors: true,
    })

    // Extract CSRF token and cookies from response
    this.csrfHandler.extractFromResponse(response)
    this.cookieHandler.extractFromResponse(response)

    return response
  }

  /**
   * Ensure CSRF cookie is obtained before making protected requests
   */
  protected async ensureCsrfCookie(): Promise<void> {
    if (!this.csrfHandler.getToken()) {
      await this.get('/sanctum/csrf-cookie', { requireCsrf: false })
    }
  }
}

