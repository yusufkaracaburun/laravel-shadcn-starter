import { test as base } from '@playwright/test'

import { AuthClient } from '../features/auth/auth-client'
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
   * ApiClient fixture (facade)
   * Provides a unified ApiClient instance that aggregates all clients
   *
   * Usage:
   * ```typescript
   * test('should use api client', async ({ apiClient }) => {
   *   await apiClient.auth.login(email, password);
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
    // Note: login() automatically calls ensureCsrfCookie() internally
    // Retry on rate limiting (429) with exponential backoff
    let response
    let retries = 5
    let delay = 500 // Start with 500ms delay

    while (retries > 0) {
      response = await authClient.login(testUser.email, testUser.password)
      const status = response.status()

      // If rate limited, wait and retry
      if (status === 429) {
        retries--
        if (retries > 0) {
          // Wait longer for rate limiting (exponential backoff)
          await new Promise((resolve) => setTimeout(resolve, delay))
          delay *= 2 // Exponential backoff (500ms, 1s, 2s, 4s, 8s)
          continue
        }
      }

      // Check if login was successful (200-299 status codes)
      if (status < 200 || status >= 300) {
        let errorText = 'Unable to read error response'
        try {
          errorText = await response.text()
        } catch {
          // Ignore text read errors
        }
        throw new Error(
          `Failed to authenticate test user: ${status} - ${errorText.substring(0, 200)}. Make sure the test user exists in the database.`,
        )
      }

      // Success - break out of retry loop
      break
    }

    // Verify login was successful by checking response body
    const loginBody = await response.json().catch(() => null)
    if (!loginBody || !loginBody.id) {
      throw new Error(
        `Login response does not contain user data. Status: ${response.status()}, Body: ${JSON.stringify(loginBody)}`,
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

