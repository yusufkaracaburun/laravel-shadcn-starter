import { APIRequestContext } from '@playwright/test';
import { AuthClient } from './auth-client';
import { testUser } from '../../fixtures/data/user';

/**
 * Test fixtures for authenticated sessions
 * Provides reusable authenticated client instances
 */
export class AuthFixtures {
  /**
   * Create an authenticated AuthClient
   * Useful for tests that require an authenticated user
   */
  static async createAuthenticatedClient(
    request: APIRequestContext
  ): Promise<AuthClient> {
    const auth = new AuthClient(request);
    const response = await auth.login(testUser.email, testUser.password);

    if (response.status() !== 200) {
      throw new Error(
        `Failed to authenticate test user: ${response.status()} - ${await response.text()}`
      );
    }

    return auth;
  }
}
