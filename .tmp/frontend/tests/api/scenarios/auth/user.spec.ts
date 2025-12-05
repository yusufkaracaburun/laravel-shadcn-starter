import {
  expectUnauthenticated,
  expectValidUser,
} from '../../features/shared/helpers/test-helpers'
import { test } from '../../fixtures'

test.describe('Get User', { tag: ['@api', '@auth', '@user'] }, () => {
  test('should get authenticated user', async ({ authenticatedAuthClient }) => {
    // Arrange: authenticatedAuthClient fixture provides pre-authenticated client
    // Act
    const userResponse = await authenticatedAuthClient.getUser()

    // Assert
    await expectValidUser(userResponse)
  })

  test('should not get user when not authenticated', async ({ authClient }) => {
    // Arrange: Use unauthenticated client
    // Act
    const response = await authClient.getUser()

    // Assert
    expectUnauthenticated(response)
  })
})
