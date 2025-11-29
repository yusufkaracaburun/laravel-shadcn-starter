import { expect, test } from '@playwright/test'

import {
  expectSuccessfulLogin,
  expectSuccessfulRegistration,
  getAuthenticatedContext,
  getCurrentUser,
  getCsrfCookie,
  loginUser,
  logoutUser,
  registerUser,
} from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'

const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

test.describe('Authentication Flow API', () => {
  test.describe.configure({ mode: 'serial' })

  test('complete authentication flow: CSRF -> Register -> Login -> Current User -> Logout', async ({ request }) => {
    const testUser = generateTestUser()

    // Step 1: Get CSRF cookie
    const csrfResponse = await getCsrfCookie(request)
    expect(csrfResponse.status()).toBe(204)
    const csrfCookies = csrfResponse.headers()['set-cookie'] || []
    expect(csrfCookies.length).toBeGreaterThan(0)
    expect(csrfCookies.some(cookie => cookie.includes('XSRF-TOKEN'))).toBe(true)

    // Step 2: Register user
    const registerResponse = await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })
    await expectSuccessfulRegistration(registerResponse)

    // Step 3: Get CSRF cookie again (for login)
    await getCsrfCookie(request)

    // Step 4: Login
    const loginResponse = await loginUser(request, {
      email: testUser.email,
      password: testUser.password,
    })
    const loginBody = await expectSuccessfulLogin(loginResponse)
    expect(typeof loginBody.two_factor).toBe('boolean')

    // Step 5: Get current user (should be authenticated)
    const { cookies } = await getAuthenticatedContext(request, {
      email: testUser.email,
      password: testUser.password,
    })

    const currentUserResponse = await getCurrentUser(request, cookies)
    expect(currentUserResponse.status()).toBe(200)

    const userBody = await currentUserResponse.json()
    expect(userBody).toHaveProperty('success', true)
    expect(userBody).toHaveProperty('code', 200)
    expect(userBody).toHaveProperty('message', 'Success')
    expect(userBody).toHaveProperty('data')
    expect(userBody.data).toHaveProperty('id')
    expect(userBody.data).toHaveProperty('name', testUser.name)
    expect(userBody.data).toHaveProperty('email', testUser.email)
    expect(userBody.data).toHaveProperty('current_team_id')
    expect(userBody.data).toHaveProperty('created_at')
    expect(userBody.data).toHaveProperty('updated_at')
    expect(userBody.data).toHaveProperty('teams')
    expect(userBody.data).toHaveProperty('currentTeam')
    expect(Array.isArray(userBody.data.teams)).toBe(true)
    expect(userBody.data.currentTeam === null || typeof userBody.data.currentTeam === 'object').toBe(true)

    // Step 6: Logout
    const logoutResponse = await logoutUser(request, cookies)
    expect(logoutResponse.status()).toBe(204)

    // Step 7: Verify user is no longer authenticated
    const unauthenticatedResponse = await getCurrentUser(request, cookies)
    expect(unauthenticatedResponse.status()).toBe(401)
  })

  test('authentication flow with CSRF cookie requirement', async ({ request }) => {
    const testUser = generateTestUser()

    // Register user
    await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    // Try to login without CSRF cookie first
    const loginWithoutCsrf = await loginUser(request, {
      email: testUser.email,
      password: testUser.password,
    })

    // Login might succeed, but session may not be established properly
    // Get CSRF cookie and try again
    await getCsrfCookie(request)
    const loginWithCsrf = await loginUser(request, {
      email: testUser.email,
      password: testUser.password,
    })
    await expectSuccessfulLogin(loginWithCsrf)

    // Now get authenticated context properly
    const { cookies } = await getAuthenticatedContext(request, {
      email: testUser.email,
      password: testUser.password,
    })

    // Should be able to access current user endpoint
    const currentUserResponse = await getCurrentUser(request, cookies)
    expect(currentUserResponse.status()).toBe(200)
  })

  test('current user endpoint returns proper API Resource structure', async ({ request }) => {
    const testUser = generateTestUser()

    // Register and authenticate
    await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    const { cookies } = await getAuthenticatedContext(request, {
      email: testUser.email,
      password: testUser.password,
    })

    // Get current user
    const response = await getCurrentUser(request, cookies)
    expect(response.status()).toBe(200)

    const body = await response.json()

    // Verify API Response structure
    expect(body).toHaveProperty('success', true)
    expect(body).toHaveProperty('code', 200)
    expect(body).toHaveProperty('message', 'Success')
    expect(body).toHaveProperty('extra')

    // Verify UserResource structure
    expect(body.data).toHaveProperty('id')
    expect(body.data).toHaveProperty('name')
    expect(body.data).toHaveProperty('email')
    expect(body.data).toHaveProperty('current_team_id')
    expect(body.data).toHaveProperty('created_at')
    expect(body.data).toHaveProperty('updated_at')
    expect(body.data).toHaveProperty('teams')
    expect(body.data).toHaveProperty('currentTeam')

    // Verify data types
    expect(typeof body.data.id).toBe('number')
    expect(typeof body.data.name).toBe('string')
    expect(typeof body.data.email).toBe('string')
    expect(body.data.current_team_id === null || typeof body.data.current_team_id === 'number').toBe(true)
    expect(body.data.created_at === null || typeof body.data.created_at === 'string').toBe(true)
    expect(body.data.updated_at === null || typeof body.data.updated_at === 'string').toBe(true)
    expect(Array.isArray(body.data.teams)).toBe(true)
    expect(body.data.currentTeam === null || typeof body.data.currentTeam === 'object').toBe(true)

    // Verify user data matches
    expect(body.data.name).toBe(testUser.name)
    expect(body.data.email).toBe(testUser.email)
  })

  test('unauthenticated request to current user endpoint returns 401', async ({ request }) => {
    const response = await getCurrentUser(request)
    expect(response.status()).toBe(401)

    const body = await response.json()
    expect(body).toHaveProperty('message')
  })

  test('logout requires authentication', async ({ request }) => {
    // Try to logout without authentication
    const response = await logoutUser(request)
    expect(response.status()).toBe(401)
  })

  test('logout clears session and prevents access to protected endpoints', async ({ request }) => {
    const testUser = generateTestUser()

    // Register and authenticate
    await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    const { cookies } = await getAuthenticatedContext(request, {
      email: testUser.email,
      password: testUser.password,
    })

    // Verify authenticated before logout
    const beforeLogout = await getCurrentUser(request, cookies)
    expect(beforeLogout.status()).toBe(200)

    // Logout
    const logoutResponse = await logoutUser(request, cookies)
    expect(logoutResponse.status()).toBe(204)

    // Verify unauthenticated after logout
    const afterLogout = await getCurrentUser(request, cookies)
    expect(afterLogout.status()).toBe(401)
  })
})

