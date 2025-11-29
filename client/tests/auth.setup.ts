import { expect, test as setup } from '@playwright/test'

import { LoginPage } from './e2e/pages/LoginPage'
import { registerUser } from './helpers/api-helpers'
import { generateTestUser } from './helpers/test-data'

// Path to store authenticated state (relative to project root)
const authFile = 'playwright/.auth/user.json'

/**
 * Authentication setup
 * This runs once before all tests to create an authenticated user state
 * The storage state is then reused by all tests
 */
setup('authenticate', async ({ page, request }) => {
  // Generate a test user
  const testUser = generateTestUser()

  // Register the user via API
  await registerUser(request, {
    name: testUser.name,
    email: testUser.email,
    password: testUser.password,
    password_confirmation: testUser.password_confirmation,
  })

  // Navigate to login page
  const loginPage = new LoginPage(page)
  await loginPage.navigate()

  // Perform login via UI
  await loginPage.fillForm(testUser.email, testUser.password)
  await loginPage.submit()

  // Wait for successful login - verify we're on dashboard
  await expect(page).toHaveURL(/.*\/dashboard/, { timeout: 10000 })

  // Verify authentication by checking dashboard is visible
  const { DashboardPage } = await import('./e2e/pages/DashboardPage')
  const dashboardPage = new DashboardPage(page)
  await dashboardPage.verifyOnPage()

  // Save authenticated state to file
  await page.context().storageState({ path: authFile })
})
