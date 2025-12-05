import { test as base } from '@playwright/test'

import { AuthClient } from '../features/auth/auth-client'
import { HealthClient } from '../features/health/health-client'
import { ApiClient } from '../features/shared/core/api-client'
import { testUser } from './data/user'

/**
 * Playwright test fixtures for API testing
 *
 * Following Playwright best practices:
 * - Fixtures encapsulate setup and teardown in the same place
 * - Fixtures are reusable between test files
 * - Fixtures are on-demand - only setup what's needed
 * - Fixtures are composable - can depend on each other
 * - Fixtures are flexible - tests can use any combination
 *
 * See: https://playwright.dev/docs/test-fixtures#introduction
 *
 * Usage:
 * ```typescript
 * import { test } from '../../fixtures';
 *
 * test('should login', async ({ authClient }) => {
 *   const response = await authClient.login(email, password);
 * });
 * ```
 */

// Declare the types of your fixtures
interface ApiFixtures {
  authClient: AuthClient
  healthClient: HealthClient
  apiClient: ApiClient
  authenticatedAuthClient: AuthClient
}

// Extend base test with API fixtures
export const test = base.extend<ApiFixtures>({
  /**
   * Basic AuthClient fixture
   * Provides an unauthenticated AuthClient instance
   *
   * Usage:
   * ```typescript
   * test('should login', async ({ authClient }) => {
   *   await authClient.login(email, password);
   * });
   * ```
   */
  authClient: async ({ request }, use) => {
    const authClient = new AuthClient(request)
    await use(authClient)
  },

  /**
   * HealthClient fixture
   * Provides a HealthClient instance for health check endpoints
   *
   * Usage:
   * ```typescript
   * test('should check health', async ({ healthClient }) => {
   *   await healthClient.check();
   * });
   * ```
   */
  healthClient: async ({ request }, use) => {
    const healthClient = new HealthClient(request)
    await use(healthClient)
  },

  /**
   * ApiClient fixture (facade)
   * Provides a unified ApiClient instance that aggregates all clients
   *
   * Usage:
   * ```typescript
   * test('should use api client', async ({ apiClient }) => {
   *   await apiClient.auth.login(email, password);
   *   await apiClient.health.check();
   * });
   * ```
   */
  apiClient: async ({ request }, use) => {
    const apiClient = new ApiClient(request)
    await use(apiClient)
  },

  /**
   * Authenticated AuthClient fixture
   * Automatically logs in before the test and provides an authenticated client
   * This fixture depends on authClient, so it will be set up after authClient
   *
   * This demonstrates fixture composition - authenticatedAuthClient depends on authClient
   *
   * Usage:
   * ```typescript
   * test('should get user', async ({ authenticatedAuthClient }) => {
   *   // Already logged in!
   *   await authenticatedAuthClient.getUser();
   * });
   * ```
   */
  authenticatedAuthClient: async ({ authClient }, use) => {
    // Setup: Login before test
    const response = await authClient.login(testUser.email, testUser.password)

    if (response.status() !== 200) {
      throw new Error(
        `Failed to authenticate test user: ${response.status()} - ${await response.text()}`,
      )
    }

    // Use the authenticated client in the test
    await use(authClient)

    // Teardown: Logout after test (optional)
    // Note: This is optional since each test gets a fresh APIRequestContext
    // which provides automatic isolation between tests
  },
})

// Re-export expect for convenience
export { expect } from '@playwright/test'
