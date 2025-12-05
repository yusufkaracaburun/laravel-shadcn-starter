import { APIRequestContext } from '@playwright/test';
import { AuthClient } from '../../auth/auth-client';
import { HealthClient } from '../../health/health-client';

/**
 * Unified API client that provides access to all API endpoints
 * Implements Facade Pattern - provides a simplified interface to complex subsystems
 * 
 * Usage:
 * ```typescript
 * const api = new ApiClient(request);
 * await api.auth.login(email, password);
 * await api.health.check();
 * ```
 * 
 * For new features, add them as properties:
 * ```typescript
 * public readonly products: ProductsClient;
 * ```
 */
export class ApiClient {
  public readonly auth: AuthClient;
  public readonly health: HealthClient;

  constructor(request: APIRequestContext) {
    this.auth = new AuthClient(request);
    this.health = new HealthClient(request);
  }

  // Legacy methods for backward compatibility
  // Consider using direct property access: api.auth.login() instead
  async getCsrfCookie() {
    return this.auth.getCsrfCookie();
  }

  async login(email: string, password: string) {
    return this.auth.login(email, password);
  }

  async getUser() {
    return this.auth.getUser();
  }

  async logout() {
    return this.auth.logout();
  }

  async healthCheck() {
    return this.health.check();
  }
}

