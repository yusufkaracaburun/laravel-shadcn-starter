import { expect, test } from '@playwright/test'

import {
  getAuthenticatedContext,
  getCurrentUser,
  registerUser,
} from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'

test.describe('User API', () => {
  test.describe.configure({ mode: 'parallel' })

  test('should get current user when authenticated with proper API Resource structure', async ({ request }) => {
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
    const response = await getCurrentUser(request, cookies)

    // Assert - Verify response structure
    expect(response.status()).toBe(200)
    const body = await response.json()

    // Verify API Response wrapper
    expect(body).toHaveProperty('success', true)
    expect(body).toHaveProperty('code', 200)
    expect(body).toHaveProperty('message', 'Success')
    expect(body).toHaveProperty('data')
    expect(body).toHaveProperty('extra')

    // Verify UserResource structure
    expect(body.data).toHaveProperty('id')
    expect(body.data).toHaveProperty('name', testUser.name)
    expect(body.data).toHaveProperty('email', testUser.email)
    expect(body.data).toHaveProperty('current_team_id')
    expect(body.data).toHaveProperty('created_at')
    expect(body.data).toHaveProperty('updated_at')
    expect(body.data).toHaveProperty('teams')
    expect(body.data).toHaveProperty('currentTeam')

    // Verify data types
    expect(typeof body.data.id).toBe('number')
    expect(typeof body.data.name).toBe('string')
    expect(typeof body.data.email).toBe('string')
    expect(Array.isArray(body.data.teams)).toBe(true)
    expect(body.data.currentTeam === null || typeof body.data.currentTeam === 'object').toBe(true)
  })

  test('should fail to get current user when not authenticated', async ({ request }) => {
    // Act
    const response = await getCurrentUser(request)

    // Assert
    expect(response.status()).toBe(401)
    const body = await response.json()
    expect(body).toHaveProperty('message')
  })

  test('should return user with teams when user has teams', async ({ request }) => {
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
    const response = await getCurrentUser(request, cookies)

    // Assert
    expect(response.status()).toBe(200)
    const body = await response.json()

    // Verify teams structure (even if empty)
    expect(body.data).toHaveProperty('teams')
    expect(Array.isArray(body.data.teams)).toBe(true)
    expect(body.data).toHaveProperty('currentTeam')
    expect(body.data.currentTeam === null || typeof body.data.currentTeam === 'object').toBe(true)

    // If currentTeam exists, verify it has proper structure
    if (body.data.currentTeam !== null) {
      expect(body.data.currentTeam).toHaveProperty('id')
      expect(body.data.currentTeam).toHaveProperty('name')
      expect(body.data.currentTeam).toHaveProperty('personal_team')
      expect(body.data.currentTeam).toHaveProperty('user_id')
      expect(body.data.currentTeam).toHaveProperty('created_at')
      expect(body.data.currentTeam).toHaveProperty('updated_at')
    }
  })
})
