import { test as base, expect } from '@playwright/test'
import type { APIRequestContext } from '@playwright/test'

import { apiRequest, createAuthApi } from '../../helpers/api-helpers'

/**
 * API Test Fixtures
 * Provides functional API helpers as Playwright fixtures following functional design patterns
 */
export interface ApiTestFixtures {
  api: ReturnType<typeof apiRequest>
  authApi: ReturnType<typeof createAuthApi>
}

export const test = base.extend<ApiTestFixtures>({
  /**
   * General API request functions (curried)
   */
  api: async ({ request }, use) => {
    const api = apiRequest(request)
    await use(api)
  },

  /**
   * Authentication API functions (curried)
   */
  authApi: async ({ request }, use) => {
    const authApi = createAuthApi(request)
    await use(authApi)
  },
})

export { expect }

