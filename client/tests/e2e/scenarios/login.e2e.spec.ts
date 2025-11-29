import { registerUser } from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'
import { expect, test } from '../fixtures'

test.describe('User Login', () => {
  test('User can login successfully with valid credentials', async ({ page, loginPage, dashboardPage, request }) => {
    const user = generateTestUser()
    user.email = 'test@example.com'
    user.password = 'password123'
    user.password_confirmation = 'password123'

    await test.step('Register user via API', async () => {
      await registerUser(request, {
        name: user.name,
        email: user.email,
        password: user.password,
        password_confirmation: user.password_confirmation,
      })
    })

    await test.step('Navigate to login page', async () => {
      await loginPage.navigate()
      await loginPage.verifyOnPage()
    })

    await test.step('Fill login form', async () => {
      await loginPage.fillForm('test@example.com', 'password123')
    })

    await test.step('Submit login form', async () => {
      await loginPage.submit()
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify redirect to dashboard', async () => {
      await expect(page).toHaveURL(/.*\/dashboard/)
      await dashboardPage.verifyOnPage()
    })

    await test.step('Verify user is authenticated', async () => {
      const isVisible = await dashboardPage.isVisible()
      expect(isVisible).toBe(true)
    })
  })

  test('User cannot login with invalid credentials', async ({ page, loginPage }) => {
    await test.step('Navigate to login page', async () => {
      await loginPage.navigate()
      await loginPage.verifyOnPage()
    })

    await test.step('Fill login form with invalid credentials', async () => {
      await loginPage.fillForm('invalid@example.com', 'wrongpassword')
    })

    await test.step('Submit login form', async () => {
      await loginPage.submit()
    })

    await test.step('Verify error message is displayed', async () => {
      const errorText = page.getByText(/error|invalid|incorrect|failed/i).first()
      await expect(errorText).toBeVisible()
    })

    await test.step('Verify user remains on login page', async () => {
      await expect(page).toHaveURL(/.*\/auth\/sign-in/)
    })
  })

  test('User is redirected to dashboard after successful login', async ({ page, loginPage, dashboardPage, request }) => {
    const user = generateTestUser()
    user.email = 'test@example.com'
    user.password = 'password123'
    user.password_confirmation = 'password123'

    await test.step('Register user via API', async () => {
      await registerUser(request, {
        name: user.name,
        email: user.email,
        password: user.password,
        password_confirmation: user.password_confirmation,
      })
    })

    await test.step('Navigate to login page', async () => {
      await loginPage.navigate()
      await loginPage.verifyOnPage()
    })

    await test.step('Fill login form', async () => {
      await loginPage.fillForm('test@example.com', 'password123')
    })

    await test.step('Submit login form', async () => {
      await loginPage.submit()
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify user is on dashboard page', async () => {
      await dashboardPage.verifyOnPage()
    })
  })
})
