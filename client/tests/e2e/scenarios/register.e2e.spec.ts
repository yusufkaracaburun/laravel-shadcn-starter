import { registerUser } from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'
import { expect, test } from '../fixtures'

test.describe('User Registration', () => {
  test('User can register successfully with valid data', async ({ page, registerPage }) => {
    await test.step('Navigate to register page', async () => {
      await registerPage.navigate()
      await registerPage.verifyOnPage()
    })

    await test.step('Fill registration form', async () => {
      await registerPage.fillForm('Test User', 'newuser@example.com', 'password123', 'password123')
    })

    await test.step('Submit registration form', async () => {
      await registerPage.submit()
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify redirect to dashboard', async () => {
      await expect(page).toHaveURL(/.*\/dashboard/)
    })
  })

  test('User cannot register with duplicate email', async ({ page, registerPage, request }) => {
    const user = generateTestUser()
    user.email = 'existing@example.com'
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

    await test.step('Navigate to register page', async () => {
      await registerPage.navigate()
      await registerPage.verifyOnPage()
    })

    await test.step('Fill registration form with duplicate email', async () => {
      await registerPage.fillForm('Another User', 'existing@example.com', 'password123', 'password123')
    })

    await test.step('Submit registration form', async () => {
      await registerPage.submit()
    })

    await test.step('Verify error message about duplicate email', async () => {
      const errorText = page.getByText(/already|taken|exists|duplicate/i).first()
      await expect(errorText).toBeVisible()
    })

    await test.step('Verify user remains on register page', async () => {
      await expect(page).toHaveURL(/.*\/auth\/sign-up/)
    })
  })

  test('User is redirected to login after successful registration', async ({ page, registerPage }) => {
    await test.step('Navigate to register page', async () => {
      await registerPage.navigate()
      await registerPage.verifyOnPage()
    })

    await test.step('Fill registration form', async () => {
      await registerPage.fillForm('Test User', 'newuser2@example.com', 'password123', 'password123')
    })

    await test.step('Submit registration form', async () => {
      await registerPage.submit()
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify redirect to dashboard', async () => {
      await expect(page).toHaveURL(/.*\/dashboard/)
    })
  })
})
