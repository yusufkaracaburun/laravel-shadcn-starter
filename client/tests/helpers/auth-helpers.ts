import type { Page } from '@playwright/test'

export async function fillLoginForm(page: Page, email: string, password: string) {
  await page.getByLabel('Email').fill(email)
  await page.getByLabel('Password').fill(password)
}

export async function submitLoginForm(page: Page) {
  await page.getByRole('button', { name: 'Mock Login' }).click()
}

export async function fillRegisterForm(
  page: Page,
  firstName: string,
  lastName: string,
  email: string,
  password: string,
  passwordConfirmation: string,
) {
  // Use getByPlaceholder for text inputs (user-visible, follows Playwright best practices)
  await page.getByPlaceholder('Max').fill(firstName)
  await page.getByPlaceholder('Robinson').fill(lastName)
  await page.getByPlaceholder('m@example.com').fill(email)

  // For password fields: FormLabel components may not have proper label association
  // Using ID as fallback when getByLabel doesn't work (still better than CSS selectors)
  // Note: This is acceptable when label association isn't properly implemented in the component
  await page.locator('input[id="password"]').fill(password)
  await page.locator('input[id="password-confirmation"]').fill(passwordConfirmation)
}

export async function submitRegisterForm(page: Page) {
  await page.getByRole('button', { name: 'Create Account' }).click()
}

export async function waitForNavigationToDashboard(page: Page) {
  await page.waitForURL('**/dashboard', { timeout: 10000 })
}

export async function waitForNavigationToSignIn(page: Page) {
  await page.waitForURL('**/auth/sign-in', { timeout: 15000, waitUntil: 'networkidle' })
}

export async function navigateToLogin(page: Page) {
  await page.goto('/auth/sign-in')
  await page.waitForLoadState('networkidle')
}

export async function navigateToRegister(page: Page) {
  await page.goto('/auth/sign-up')
  await page.waitForLoadState('networkidle')
}

/**
 * Mock successful registration API response
 * Use this in tests to intercept and mock the registration endpoint
 * Matches any register endpoint regardless of the base URL
 * Also mocks CSRF cookie endpoint for Sanctum
 */
export async function mockSuccessfulRegistration(page: Page) {
  // Mock CSRF cookie endpoint (required for Sanctum) - match any URL pattern
  // This must be set up first before other routes
  await page.route('**/sanctum/csrf-cookie', async (route) => {
    const method = route.request().method()
    const url = route.request().url()
    
    if (method === 'GET') {
      await route.fulfill({
        status: 204,
        headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Credentials': 'true',
          'Set-Cookie': 'XSRF-TOKEN=test-token; Path=/; SameSite=Lax',
        },
      })
    }
    else {
      await route.continue()
    }
  })

  // Mock registration endpoint - match any URL pattern including with baseURL
  // This will match both http://localhost:8000/register and any other base URL
  await page.route('**/register', async (route) => {
    const method = route.request().method()
    const url = route.request().url()
    
    // Log for debugging (can be removed later)
    console.log(`[Mock] Intercepted ${method} request to ${url}`)
    
    if (method === 'OPTIONS') {
      // Handle preflight requests
      await route.fulfill({
        status: 200,
        headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Methods': 'POST, OPTIONS',
          'Access-Control-Allow-Headers': 'Content-Type',
          'Access-Control-Allow-Credentials': 'true',
        },
      })
    }
    else if (method === 'POST') {
      // Handle POST requests
      // Laravel Fortify returns 201 for successful registration
      await route.fulfill({
        status: 201,
        contentType: 'application/json',
        headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Methods': 'POST, OPTIONS',
          'Access-Control-Allow-Headers': 'Content-Type',
          'Access-Control-Allow-Credentials': 'true',
        },
        body: JSON.stringify({
          message: 'User registered successfully',
          user: {
            id: 1,
            name: 'Test User',
            email: 'test@example.com',
          },
        }),
      })
    }
    else {
      await route.continue()
    }
  })
}
