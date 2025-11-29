import { expect, test } from '@playwright/test'

import {
  getAuthenticatedContext,
  registerUser,
} from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'

const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

test.describe('User API', () => {
  test.describe.configure({ mode: 'parallel' })

  test.skip('should get current user when authenticated', async ({ request }) => {
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

    // Act - Get current user
    const response = await request.get(`${apiURL}/api/user/current`, {
      headers: {
        Accept: 'application/json',
        Cookie: cookies,
      },
    })

    // Assert
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body).toHaveProperty('id')
    expect(body).toHaveProperty('name')
    expect(body).toHaveProperty('email')
    expect(body.email).toBe(testUser.email)
  })

  test.skip('should fail to get current user when not authenticated', async ({ request }) => {
    // Act
    const response = await request.get(`${apiURL}/api/user/current`, {
      headers: {
        Accept: 'application/json',
      },
    })

    // Assert
    expect(response.status()).toBe(401)
  })

  test.skip('should update user profile information', async ({ request }) => {
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

    // Act - Update user profile information
    const updateData = {
      name: 'Updated Name',
      email: testUser.email, // Email cannot be changed
    }

    const response = await request.put(`${apiURL}/api/user/profile-information`, {
      data: updateData,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Cookie': cookies,
      },
    })

    // Assert - Note: This may need adjustment based on actual API implementation
    expect([200, 201, 204]).toContain(response.status())
  })
})
