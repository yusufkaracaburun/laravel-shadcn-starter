import { createBdd } from 'playwright-bdd'

import { registerUser } from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'
import { expect, test } from '../fixtures'

// Create BDD step definitions using the extended test with fixtures
const { Given, When, Then } = createBdd(test)

Given('a user is registered with email {string} and password {string}', async ({ request }, email: string, password: string) => {
  // Arrange - Register user via API
  const user = generateTestUser()
  user.email = email
  user.password = password
  user.password_confirmation = password

  await registerUser(request, {
    name: user.name,
    email: user.email,
    password: user.password,
    password_confirmation: user.password_confirmation,
  })
})

When('the user navigates to the login page', async ({ loginPage }) => {
  // Act - Navigate to login page
  await loginPage.navigate()
  await loginPage.verifyOnPage()
})

When('the user navigates to the register page', async ({ registerPage }) => {
  // Act - Navigate to register page
  await registerPage.navigate()
  await registerPage.verifyOnPage()
})

When('the user fills in email {string} and password {string}', async ({ loginPage }, email: string, password: string) => {
  // Act - Fill login form
  await loginPage.fillForm(email, password)
})

When('the user fills in name {string}, email {string}, password {string}, and password confirmation {string}', async ({ registerPage }, name: string, email: string, password: string, passwordConfirmation: string) => {
  // Act - Fill registration form
  await registerPage.fillForm(name, email, password, passwordConfirmation)
})

When('the user submits the login form', async ({ page, loginPage }) => {
  // Act - Submit login form
  await Promise.all([
    page.waitForURL(/.*\/dashboard/, { timeout: 10000 }).catch(() => {}),
    loginPage.submit(),
  ])
})

When('the user submits the registration form', async ({ page, registerPage }) => {
  // Act - Submit registration form
  await Promise.all([
    page.waitForURL(/.*\/auth\/sign-in/, { timeout: 20000 }).catch(() => {}),
    registerPage.submit(),
  ])
})

When('the user logs out', async ({ page, dashboardPage }) => {
  // Act - Logout
  await Promise.all([
    page.waitForURL(/.*\/auth\/sign-in/, { timeout: 10000 }).catch(() => {}),
    dashboardPage.logout(),
  ])
})

When('the user navigates to the dashboard directly', async ({ dashboardPage }) => {
  // Act - Navigate to dashboard
  await dashboardPage.navigate()
})

When('the user reloads the page', async ({ page }) => {
  // Act - Reload page
  await page.reload()
  await page.waitForLoadState('networkidle')
})

Then('the user should be redirected to the dashboard', async ({ page, dashboardPage }) => {
  // Assert - Verify redirect to dashboard
  await expect(page).toHaveURL(/.*\/dashboard/)
  await dashboardPage.verifyOnPage()
})

Then('the user should be redirected to the login page', async ({ page }) => {
  // Assert - Verify redirect to login
  await expect(page).toHaveURL(/.*\/auth\/sign-in/)
})

Then('the user should be on the dashboard page', async ({ dashboardPage }) => {
  // Assert - Verify on dashboard
  await dashboardPage.verifyOnPage()
})

Then('the user should be on the login page', async ({ loginPage }) => {
  // Assert - Verify on login page
  await loginPage.verifyOnPage()
})

Then('the user should be authenticated', async ({ dashboardPage }) => {
  // Assert - Verify authentication by checking dashboard access
  const isVisible = await dashboardPage.isVisible()
  expect(isVisible).toBe(true)
})

Then('the user should not be authenticated', async ({ page }) => {
  // Assert - Verify not authenticated by checking redirect to login
  await expect(page).toHaveURL(/.*\/auth\/sign-in/)
})

Then('the user should not be able to access the dashboard', async ({ page }) => {
  // Assert - Verify cannot access dashboard
  await expect(page).toHaveURL(/.*\/auth\/sign-in/)
})

Then('the user should see an error message', async ({ page }) => {
  // Assert - Verify error message is visible
  // Look for common error message patterns
  const errorText = page.getByText(/error|invalid|incorrect|failed/i).first()
  await expect(errorText).toBeVisible({ timeout: 5000 })
})

Then('the user should see an error message about duplicate email', async ({ page }) => {
  // Assert - Verify duplicate email error
  const errorText = page.getByText(/already|taken|exists|duplicate/i).first()
  await expect(errorText).toBeVisible({ timeout: 5000 })
})

Then('the user should remain on the login page', async ({ page }) => {
  // Assert - Verify still on login page
  await expect(page).toHaveURL(/.*\/auth\/sign-in/)
})

Then('the user should remain on the register page', async ({ page }) => {
  // Assert - Verify still on register page
  await expect(page).toHaveURL(/.*\/auth\/sign-up/)
})

Then('the user should still be authenticated', async ({ dashboardPage }) => {
  // Assert - Verify still authenticated after reload
  const isVisible = await dashboardPage.isVisible()
  expect(isVisible).toBe(true)
})

Then('the user should still be on the dashboard page', async ({ dashboardPage }) => {
  // Assert - Verify still on dashboard after reload
  await dashboardPage.verifyOnPage()
})

Then('the user should be able to login with email {string} and password {string}', async ({ page, loginPage }, email: string, password: string) => {
  // Assert - Verify can login with registered credentials
  await loginPage.fillForm(email, password)
  await Promise.all([
    page.waitForURL(/.*\/dashboard/, { timeout: 10000 }),
    loginPage.submit(),
  ])
  await expect(page).toHaveURL(/.*\/dashboard/)
})
