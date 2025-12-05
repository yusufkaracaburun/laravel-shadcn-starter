import type { APIRequestContext } from '@playwright/test'

import { AuthClient } from '../../auth/auth-client'
import { UserClient } from '../../users/user-client'

/**
 * Facade class providing unified interface to all API clients
 * Aggregates all feature clients for convenient access
 */
export class ApiClient {
  public readonly auth: AuthClient
  public readonly users: UserClient

  constructor(request: APIRequestContext) {
    this.auth = new AuthClient(request)
    this.users = new UserClient(request)
  }
}

