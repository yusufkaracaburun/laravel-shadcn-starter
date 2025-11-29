import { expect, test } from '@playwright/test'

import {
  getAuthenticatedContext,
  registerUser,
} from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'

const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

test.describe('Chat API', () => {
  test.describe.configure({ mode: 'parallel' })

  test.skip('should get chat data when authenticated', async ({ request }) => {
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

    // Act - Get chat data
    const response = await request.get(`${apiURL}/api/chat/data`, {
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

  test.skip('should fail to get chat data when not authenticated', async ({ request }) => {
    // Act
    const response = await request.get(`${apiURL}/api/chat/data`, {
      headers: {
        Accept: 'application/json',
      },
    })

    // Assert
    expect(response.status()).toBe(401)
  })
})
