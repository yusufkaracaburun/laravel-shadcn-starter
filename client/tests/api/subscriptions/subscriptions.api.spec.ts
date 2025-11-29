import { expect, test } from '@playwright/test'

import {
  getAuthenticatedContext,
  registerUser,
} from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'

const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

test.describe('Subscriptions API', () => {
  test.describe.configure({ mode: 'parallel' })

  test.skip('should get subscriptions when authenticated', async ({ request }) => {
    // Arrange - Register and login first
    const testUser = generateTestUser()
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

    // Act - Get subscriptions
    const response = await request.get(`${apiURL}/api/subscriptions`, {
      headers: {
        Accept: 'application/json',
        Cookie: cookies,
      },
    })

    // Assert
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body).toBeDefined()
    // Response structure may vary - adjust based on actual API
  })

  test.skip('should get available subscription plans when authenticated', async ({ request }) => {
    // Arrange - Register and login first
    const testUser = generateTestUser()
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

    // Act - Get available plans
    const response = await request.get(`${apiURL}/api/subscriptions/available`, {
      headers: {
        Accept: 'application/json',
        Cookie: cookies,
      },
    })

    // Assert
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body).toBeDefined()
  })

  test.skip('should get invoices when authenticated', async ({ request }) => {
    // Arrange - Register and login first
    const testUser = generateTestUser()
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

    // Act - Get invoices
    const response = await request.get(`${apiURL}/api/subscriptions/invoices`, {
      headers: {
        Accept: 'application/json',
        Cookie: cookies,
      },
    })

    // Assert
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body).toBeDefined()
  })

  test.skip('should fail to get subscriptions when not authenticated', async ({ request }) => {
    // Act
    const response = await request.get(`${apiURL}/api/subscriptions`, {
      headers: {
        Accept: 'application/json',
      },
    })

    // Assert
    expect(response.status()).toBe(401)
  })
})
