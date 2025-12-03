import { test as base } from '@playwright/test'

import { DashboardPage } from '../pages/DashboardPage.js'
import { LoginPage } from '../pages/LoginPage.js'
import { RegisterPage } from '../pages/RegisterPage.js'

/**
 * Custom fixtures for E2E tests
 * These fixtures provide page objects and test context
 */
export interface TestFixtures {
  loginPage: LoginPage
  registerPage: RegisterPage
  dashboardPage: DashboardPage
  testUser: {
    email: string
    password: string
    name: string
  } | null
}

/**
 * Extend base Playwright test with custom fixtures
 * These fixtures are available in tests via the test context
 */
export const test = base.extend<TestFixtures>({
  /**
   * Login page object fixture
   * Automatically creates a LoginPage instance for each test
   */
  loginPage: async ({ page }, use) => {
    const loginPage = new LoginPage(page)
    await use(loginPage)
  },

  /**
   * Register page object fixture
   * Automatically creates a RegisterPage instance for each test
   */
  registerPage: async ({ page }, use) => {
    const registerPage = new RegisterPage(page)
    await use(registerPage)
  },

  /**
   * Dashboard page object fixture
   * Automatically creates a DashboardPage instance for each test
   */
  dashboardPage: async ({ page }, use) => {
    const dashboardPage = new DashboardPage(page)
    await use(dashboardPage)
  },

  /**
   * Test user context fixture
   * Stores user data between steps within a test
   * This fixture provides a mutable object that can be updated during test execution
   */
  testUser: async ({ page: _page }, use) => {
    // Create a mutable object that can be updated in steps
    const testUserStore: { email: string; password: string; name: string } | null = null
    await use(testUserStore)
  },
})

export { expect } from '@playwright/test'
