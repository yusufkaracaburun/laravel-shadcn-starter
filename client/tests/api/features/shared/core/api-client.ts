import type { APIRequestContext } from '@playwright/test'

import { AuthClient } from '../../auth/auth-client'

/**
 * Facade class providing unified interface to all API clients
 * Aggregates all feature clients for convenient access
 */
export class ApiClient {
  public readonly auth: AuthClient

  constructor(request: APIRequestContext) {
    this.auth = new AuthClient(request)
  }
}

