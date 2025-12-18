import type { APIRequestContext } from '@playwright/test'

import { AuthClient } from '../../auth/auth-client'
import { ApiClient } from '../core/api-client'

/**
 * Factory class for creating API clients
 * Use this for non-test code or when you need to create clients manually
 * For tests, use Playwright fixtures instead (recommended)
 */
export class ClientFactory {
  /**
   * Create an AuthClient instance
   */
  static createAuthClient(request: APIRequestContext): AuthClient {
    return new AuthClient(request)
  }

  /**
   * Create an ApiClient instance (facade)
   */
  static createApiClient(request: APIRequestContext): ApiClient {
    return new ApiClient(request)
  }
}
