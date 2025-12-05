import type { APIRequestContext, APIResponse } from '@playwright/test'

import { CsrfHandler } from '../handlers/csrf-handler'
import { RequestBuilder } from '../handlers/request-builder'

/**
 * Base client class providing common functionality for all API clients
 * Implements Template Method pattern for consistent request handling
 */
export abstract class BaseClient {
  protected readonly csrfHandler: CsrfHandler
  protected readonly requestBuilder: RequestBuilder

  constructor(protected readonly request: APIRequestContext) {
    this.csrfHandler = new CsrfHandler()
    this.requestBuilder = new RequestBuilder()
  }

  /**
   * Make a GET request with automatic CSRF handling
   */
  protected async get(
    endpoint: string,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const headers = options.requireCsrf
      ? this.requestBuilder.getHeadersWithCsrf(this.csrfHandler.getToken())
      : this.requestBuilder.getBaseHeaders()

    const response = await this.request.get(endpoint, { headers })
    this.csrfHandler.extractFromResponse(response)
    return response
  }

  /**
   * Make a POST request with automatic CSRF handling
   */
  protected async post(
    endpoint: string,
    data: unknown,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const headers = this.requestBuilder.getHeadersWithCsrf(
      this.csrfHandler.getToken(),
      true,
    )

    const response = await this.request.post(endpoint, {
      data,
      headers: options.requireCsrf !== false ? headers : this.requestBuilder.getBaseHeaders(),
    })

    this.csrfHandler.extractFromResponse(response)
    return response
  }

  /**
   * Make a PUT request with automatic CSRF handling
   */
  protected async put(
    endpoint: string,
    data: unknown,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const headers = this.requestBuilder.getHeadersWithCsrf(
      this.csrfHandler.getToken(),
      true,
    )

    const response = await this.request.put(endpoint, {
      data,
      headers: options.requireCsrf !== false ? headers : this.requestBuilder.getBaseHeaders(),
    })

    this.csrfHandler.extractFromResponse(response)
    return response
  }

  /**
   * Make a PATCH request with automatic CSRF handling
   */
  protected async patch(
    endpoint: string,
    data: unknown,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const headers = this.requestBuilder.getHeadersWithCsrf(
      this.csrfHandler.getToken(),
      true,
    )

    const response = await this.request.patch(endpoint, {
      data,
      headers: options.requireCsrf !== false ? headers : this.requestBuilder.getBaseHeaders(),
    })

    this.csrfHandler.extractFromResponse(response)
    return response
  }

  /**
   * Make a DELETE request with automatic CSRF handling
   */
  protected async delete(
    endpoint: string,
    options: { requireCsrf?: boolean } = {},
  ): Promise<APIResponse> {
    const headers = options.requireCsrf !== false
      ? this.requestBuilder.getHeadersWithCsrf(this.csrfHandler.getToken())
      : this.requestBuilder.getBaseHeaders()

    const response = await this.request.delete(endpoint, { headers })
    this.csrfHandler.extractFromResponse(response)
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
