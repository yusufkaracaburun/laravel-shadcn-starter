import { expect, test } from '@playwright/test'

import {
  expectSuccessfulLogin,
  expectValidationError,
  getCsrfCookieString,
  loginUser,
  registerUser,
} from '../../helpers/api-helpers'
import { generateLoginCredentials, generateTestUser } from '../../helpers/test-data'

test.describe('Login API', () => {
  test.describe.configure({ mode: 'parallel' })
  test('should login successfully with valid credentials', async ({ request }) => {
    // First register a user
    const testUser = generateTestUser()
    await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    // Then login with those credentials
    const response = await loginUser(request, {
      email: testUser.email,
      password: testUser.password,
    })

    await expectSuccessfulLogin(response)
  })

  test('should fail login with invalid email', async ({ request }) => {
    const response = await loginUser(request, {
      email: 'nonexistent@example.com',
      password: 'password123',
    })

    await expectValidationError(response, 422)
  })

  test('should fail login with invalid password', async ({ request }) => {
    // First register a user
    const testUser = generateTestUser()
    await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    // Then try to login with wrong password
    const response = await loginUser(request, {
      email: testUser.email,
      password: 'wrongpassword',
    })

    await expectValidationError(response, 422)
  })

  test('should fail login with missing email', async ({ request }) => {
    const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'
    const cookies = await getCsrfCookieString(request)
    const response = await request.post(`${apiURL}/login`, {
      data: {
        password: 'password123',
      },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(cookies ? { Cookie: cookies } : {}),
      },
    })

    await expectValidationError(response, 422)
  })

  test('should fail login with missing password', async ({ request }) => {
    const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'
    const cookies = await getCsrfCookieString(request)
    const response = await request.post(`${apiURL}/login`, {
      data: {
        email: 'test@example.com',
      },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(cookies ? { Cookie: cookies } : {}),
      },
    })

    await expectValidationError(response, 422)
  })

  test('should fail login with malformed email', async ({ request }) => {
    const response = await loginUser(request, {
      email: 'not-an-email',
      password: 'password123',
    })

    await expectValidationError(response, 422)
  })

  test('should return two_factor key in response', async ({ request }) => {
    // First register a user
    const testUser = generateTestUser()
    await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    // Then login
    const response = await loginUser(request, {
      email: testUser.email,
      password: testUser.password,
    })

    const body = await expectSuccessfulLogin(response)
    expect(typeof body.two_factor).toBe('boolean')
  })

  test('should fail login with empty email', async ({ request }) => {
    const response = await loginUser(request, {
      email: '',
      password: 'password123',
    })

    // May return 422 (validation error) or 429 (rate limit) depending on rate limiting
    expect([422, 429]).toContain(response.status())
    if (response.status() === 422) {
      const body = await response.json()
      expect(body).toHaveProperty('errors')
    }
  })

  test('should fail login with empty password', async ({ request }) => {
    const response = await loginUser(request, {
      email: 'test@example.com',
      password: '',
    })

    // May return 422 (validation error) or 429 (rate limit) depending on rate limiting
    expect([422, 429]).toContain(response.status())
    if (response.status() === 422) {
      const body = await response.json()
      expect(body).toHaveProperty('errors')
    }
  })
})
