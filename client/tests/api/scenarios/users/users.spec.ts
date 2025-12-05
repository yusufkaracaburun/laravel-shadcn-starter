import type { Role, User } from '../../features/shared/types'
import type { CreateUserRequest, PaginatedUsersResponse, UpdateUserRequest } from '../../features/users/user-client'

import { HttpStatus } from '../../features/shared/enums'
import {
  expectError,
  expectIResponse,
  expectSuccess,
  expectUnauthenticated,
  expectValidationErrors,
} from '../../features/shared/helpers'
import { UserClient } from '../../features/users/user-client'
import { expect, test } from '../../fixtures'

/**
 * Pure function: Create unique test user data
 * Uses timestamp to ensure uniqueness
 */
function createUniqueTestUser(baseName = 'Test User'): CreateUserRequest {
  const timestamp = Date.now()
  return {
    name: `${baseName} ${timestamp}`,
    email: `test-user-${timestamp}@example.com`,
    password: 'password123',
    password_confirmation: 'password123',
  }
}

/**
 * Pure function: Create test user with role
 */
function createTestUserWithRole(role: string): CreateUserRequest {
  const user = createUniqueTestUser()
  return {
    ...user,
    role,
  }
}

/**
 * Pure function: Create update data
 */
function createUpdateData(updates: Partial<UpdateUserRequest>): UpdateUserRequest {
  return { ...updates }
}

test.describe('Users API', { tag: ['@api', '@users'] }, () => {
  test.describe('Current User', () => {
    test('should get current authenticated user', async ({ request, authenticatedAuthClient }) => {
      // Arrange: authenticatedAuthClient fixture provides pre-authenticated client
      // Create UserClient and copy auth state from authenticated client
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act
      const response = await userClient.getCurrentUser()

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<User>
      const userBody = await expectIResponse<User>(response)

      // Assert - User data structure matches User interface
      expect(userBody.data).toHaveProperty('id')
      expect(userBody.data).toHaveProperty('name')
      expect(userBody.data).toHaveProperty('email')
      expect(userBody.data).toHaveProperty('email_verified_at')
      expect(userBody.data).toHaveProperty('current_team_id')
      expect(userBody.data).toHaveProperty('profile_photo_url')
      expect(userBody.data).toHaveProperty('created_at')
      expect(userBody.data).toHaveProperty('updated_at')
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange: Use unauthenticated request
      const userClient = new UserClient(request)

      // Act
      const response = await userClient.getCurrentUser()

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Roles', () => {
    test('should get available roles', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act
      const response = await userClient.getRoles()

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<Role[]>
      const rolesBody = await expectIResponse<Role[]>(response)

      // Assert - Roles should be an array
      expect(Array.isArray(rolesBody.data)).toBe(true)

      // Assert - Each role should have id and name
      if (rolesBody.data.length > 0) {
        const role = rolesBody.data[0]
        expect(role).toHaveProperty('id')
        expect(role).toHaveProperty('name')
        expect(typeof role.id).toBe('number')
        expect(typeof role.name).toBe('string')
      }
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const userClient = new UserClient(request)

      // Act
      const response = await userClient.getRoles()

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('List Users', () => {
    test('should get paginated list of users', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act
      const response = await userClient.getUsers(1, 15)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<PaginatedUsersResponse>
      const usersBody = await expectIResponse<PaginatedUsersResponse>(response)

      // Assert - Pagination structure
      expect(usersBody.data).toHaveProperty('data')
      expect(usersBody.data).toHaveProperty('current_page')
      expect(usersBody.data).toHaveProperty('per_page')
      expect(usersBody.data).toHaveProperty('total')
      expect(usersBody.data).toHaveProperty('last_page')
      expect(usersBody.data).toHaveProperty('first_page_url')
      expect(usersBody.data).toHaveProperty('last_page_url')
      expect(usersBody.data).toHaveProperty('path')

      // Assert - Data should be an array
      expect(Array.isArray(usersBody.data.data)).toBe(true)

      // Assert - If users exist, verify structure
      if (usersBody.data.data.length > 0) {
        const user = usersBody.data.data[0]
        expect(user).toHaveProperty('id')
        expect(user).toHaveProperty('name')
        expect(user).toHaveProperty('email')
      }
    })

    test('should handle pagination parameters', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act - Request page 1 with 5 items per page
      const response = await userClient.getUsers(1, 5)

      // Assert
      expectSuccess(response)
      const usersBody = await expectIResponse<PaginatedUsersResponse>(response)
      expect(usersBody.data.per_page).toBe(5)
      expect(usersBody.data.current_page).toBe(1)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const userClient = new UserClient(request)

      // Act
      const response = await userClient.getUsers()

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Show User', () => {
    test('should get user by id', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Get current user to use their ID
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const currentUserResponse = await userClient.getCurrentUser()
      const currentUser = await expectIResponse<User>(currentUserResponse)
      const userId = currentUser.data.id

      // Act
      const response = await userClient.getUser(userId)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<User>
      const userBody = await expectIResponse<User>(response)

      // Assert - User data matches
      expect(userBody.data.id).toBe(userId)
      expect(userBody.data).toHaveProperty('name')
      expect(userBody.data).toHaveProperty('email')
      expect(userBody.data).toHaveProperty('created_at')
      expect(userBody.data).toHaveProperty('updated_at')
    })

    test('should return 404 for non-existent user', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentUserId = 999999

      // Act
      const response = await userClient.getUser(nonExistentUserId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const userClient = new UserClient(request)

      // Act
      const response = await userClient.getUser(1)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Create User', () => {
    test('should create user successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create unique test user data
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const testUserData = createUniqueTestUser()

      // Act
      const response = await userClient.createUser(testUserData)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.CREATED)

      // Assert - Response structure matches IResponse<User>
      const userBody = await expectIResponse<User>(response)

      // Assert - User data matches input
      expect(userBody.data).toHaveProperty('id')
      expect(userBody.data.name).toBe(testUserData.name)
      expect(userBody.data.email).toBe(testUserData.email)
      expect(userBody.data).toHaveProperty('created_at')
      expect(userBody.data).toHaveProperty('updated_at')

      // Cleanup - Delete created user
      await userClient.deleteUser(userBody.data.id)
    })

    test('should create user with optional role', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      // First get available roles
      const rolesResponse = await userClient.getRoles()
      const rolesBody = await expectIResponse<Role[]>(rolesResponse)

      if (rolesBody.data.length === 0) {
        test.skip()
        return
      }

      const roleName = rolesBody.data[0].name
      const testUserData = createTestUserWithRole(roleName)

      // Act
      const response = await userClient.createUser(testUserData)

      // Assert
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.CREATED)

      const userBody = await expectIResponse<User>(response)
      expect(userBody.data.email).toBe(testUserData.email)

      // Cleanup
      await userClient.deleteUser(userBody.data.id)
    })

    test('should return validation errors for missing name', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateUserRequest = {
        name: '',
        email: `test-${Date.now()}@example.com`,
        password: 'password123',
        password_confirmation: 'password123',
      }

      // Act
      const response = await userClient.createUser(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid email', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateUserRequest = {
        name: 'Test User',
        email: 'invalid-email',
        password: 'password123',
        password_confirmation: 'password123',
      }

      // Act
      const response = await userClient.createUser(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for weak password', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateUserRequest = {
        name: 'Test User',
        email: `test-${Date.now()}@example.com`,
        password: 'short',
        password_confirmation: 'short',
      }

      // Act
      const response = await userClient.createUser(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for password mismatch', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateUserRequest = {
        name: 'Test User',
        email: `test-${Date.now()}@example.com`,
        password: 'password123',
        password_confirmation: 'different123',
      }

      // Act
      const response = await userClient.createUser(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for duplicate email', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const testUserData = createUniqueTestUser()

      // Create first user
      const createResponse = await userClient.createUser(testUserData)
      expectSuccess(createResponse)
      const createdUser = await expectIResponse<User>(createResponse)

      // Try to create another user with same email
      const duplicateData: CreateUserRequest = {
        ...testUserData,
        name: 'Different Name',
      }

      // Act
      const response = await userClient.createUser(duplicateData)

      // Assert
      await expectValidationErrors(response)

      // Cleanup
      await userClient.deleteUser(createdUser.data.id)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const userClient = new UserClient(request)
      const testUserData = createUniqueTestUser()

      // Act
      const response = await userClient.createUser(testUserData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Update User', () => {
    test('should update user successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create a user first
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const testUserData = createUniqueTestUser()
      const createResponse = await userClient.createUser(testUserData)
      const createdUser = await expectIResponse<User>(createResponse)
      const userId = createdUser.data.id

      // Prepare update data
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await userClient.updateUser(userId, updateData)

      // Assert
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      const userBody = await expectIResponse<User>(response)
      expect(userBody.data.name).toBe('Updated Name')
      expect(userBody.data.email).toBe(testUserData.email) // Email should remain unchanged

      // Cleanup
      await userClient.deleteUser(userId)
    })

    test('should update user email', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const testUserData = createUniqueTestUser()
      const createResponse = await userClient.createUser(testUserData)
      const createdUser = await expectIResponse<User>(createResponse)
      const userId = createdUser.data.id

      const newEmail = `updated-${Date.now()}@example.com`
      const updateData = createUpdateData({
        email: newEmail,
      })

      // Act
      const response = await userClient.updateUser(userId, updateData)

      // Assert
      expectSuccess(response)
      const userBody = await expectIResponse<User>(response)
      expect(userBody.data.email).toBe(newEmail)

      // Cleanup
      await userClient.deleteUser(userId)
    })

    test('should update user password', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const testUserData = createUniqueTestUser()
      const createResponse = await userClient.createUser(testUserData)
      const createdUser = await expectIResponse<User>(createResponse)
      const userId = createdUser.data.id

      const updateData = createUpdateData({
        password: 'newpassword123',
        password_confirmation: 'newpassword123',
      })

      // Act
      const response = await userClient.updateUser(userId, updateData)

      // Assert
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Cleanup
      await userClient.deleteUser(userId)
    })

    test('should return validation errors for invalid email on update', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const testUserData = createUniqueTestUser()
      const createResponse = await userClient.createUser(testUserData)
      const createdUser = await expectIResponse<User>(createResponse)
      const userId = createdUser.data.id

      const updateData = createUpdateData({
        email: 'invalid-email',
      })

      // Act
      const response = await userClient.updateUser(userId, updateData)

      // Assert
      await expectValidationErrors(response)

      // Cleanup
      await userClient.deleteUser(userId)
    })

    test('should return validation errors for weak password on update', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const testUserData = createUniqueTestUser()
      const createResponse = await userClient.createUser(testUserData)
      const createdUser = await expectIResponse<User>(createResponse)
      const userId = createdUser.data.id

      const updateData = createUpdateData({
        password: 'short',
        password_confirmation: 'short',
      })

      // Act
      const response = await userClient.updateUser(userId, updateData)

      // Assert
      await expectValidationErrors(response)

      // Cleanup
      await userClient.deleteUser(userId)
    })

    test('should return 404 for non-existent user on update', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentUserId = 999999
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await userClient.updateUser(nonExistentUserId, updateData)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const userClient = new UserClient(request)
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await userClient.updateUser(1, updateData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Delete User', () => {
    test('should delete user successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create a user first
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const testUserData = createUniqueTestUser()
      const createResponse = await userClient.createUser(testUserData)
      const createdUser = await expectIResponse<User>(createResponse)
      const userId = createdUser.data.id

      // Act
      const response = await userClient.deleteUser(userId)

      // Assert - Should return 204 No Content or 200 OK
      expect([HttpStatus.OK, HttpStatus.NO_CONTENT]).toContain(response.status())

      // Verify user is deleted
      const getResponse = await userClient.getUser(userId)
      expectError(getResponse, HttpStatus.NOT_FOUND)
    })

    test('should return 404 for non-existent user on delete', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const userClient = new UserClient(request)
      userClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentUserId = 999999

      // Act
      const response = await userClient.deleteUser(nonExistentUserId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const userClient = new UserClient(request)

      // Act
      const response = await userClient.deleteUser(1)

      // Assert
      expectUnauthenticated(response)
    })
  })
})

