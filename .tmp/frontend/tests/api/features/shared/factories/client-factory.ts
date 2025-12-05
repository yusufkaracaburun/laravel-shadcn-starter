import { APIRequestContext } from '@playwright/test';
import { AuthClient } from '../../auth/auth-client';
import { HealthClient } from '../../health/health-client';
import { ApiClient } from '../core/api-client';
import { BaseClient } from '../core/base-client';

/**
 * Factory for creating API clients
 * Implements Factory Pattern for consistent client creation
 * 
 * Note: For tests, prefer using Playwright fixtures from fixtures/fixtures.ts
 * This factory is useful for non-test code or dynamic client creation
 * 
 * To add a new client:
 * 1. Create a new client class extending BaseClient in features/{feature}/
 * 2. Add a create method here
 * 3. Update ApiClient facade if needed
 */
export class ClientFactory {
  /**
   * Create an AuthClient instance
   */
  static createAuthClient(request: APIRequestContext): AuthClient {
    return new AuthClient(request);
  }

  /**
   * Create a HealthClient instance
   */
  static createHealthClient(request: APIRequestContext): HealthClient {
    return new HealthClient(request);
  }

  /**
   * Create a unified ApiClient instance (facade)
   */
  static createApiClient(request: APIRequestContext): ApiClient {
    return new ApiClient(request);
  }

  /**
   * Generic method to create any client extending BaseClient
   * Useful for dynamic client creation
   */
  static createClient<T extends BaseClient>(
    ClientClass: new (request: APIRequestContext) => T,
    request: APIRequestContext
  ): T {
    return new ClientClass(request);
  }
}

