import { expect, test } from '@playwright/test'

import {
  expectSuccessfulRegistration,
  expectValidationError,
  registerUser,
} from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'

test.describe('Register API', () => {
  test.describe.configure({ mode: 'parallel' })
  test('should register successfully with valid data', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    await expectSuccessfulRegistration(response)
  })

  test('should fail registration with duplicate email', async ({ request }) => {
    const testUser = generateTestUser()

    // Register first time
    await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    // Try to register again with same email
    const duplicateUser = generateTestUser()
    const response = await registerUser(request, {
      name: duplicateUser.name,
      email: testUser.email, // Same email
      password: duplicateUser.password,
      password_confirmation: duplicateUser.password_confirmation,
    })

    await expectValidationError(response, 422)
  })

  test('should fail registration with missing name', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await request.post(`${process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'}/register`, {
      data: {
        email: testUser.email,
        password: testUser.password,
        password_confirmation: testUser.password_confirmation,
      },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    })

    await expectValidationError(response, 422)
  })

  test('should fail registration with missing email', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await request.post(`${process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'}/register`, {
      data: {
        name: testUser.name,
        password: testUser.password,
        password_confirmation: testUser.password_confirmation,
      },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    })

    await expectValidationError(response, 422)
  })

  test('should fail registration with missing password', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await request.post(`${process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'}/register`, {
      data: {
        name: testUser.name,
        email: testUser.email,
        password_confirmation: testUser.password_confirmation,
      },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    })

    await expectValidationError(response, 422)
  })

  test('should fail registration with missing password_confirmation', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await request.post(`${process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'}/register`, {
      data: {
        name: testUser.name,
        email: testUser.email,
        password: testUser.password,
      },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    })

    await expectValidationError(response, 422)
  })

  test('should fail registration with password mismatch', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: 'differentpassword',
    })

    await expectValidationError(response, 422)
  })

  test('should fail registration with invalid email format', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await registerUser(request, {
      name: testUser.name,
      email: 'not-an-email',
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    await expectValidationError(response, 422)
  })

  test('should fail registration with password too short', async ({ request }) => {
    const testUser = generateTestUser()
    const shortPassword = 'short'
    const response = await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: shortPassword,
      password_confirmation: shortPassword,
    })

    await expectValidationError(response, 422)
  })

  test('should return 201 status on successful registration', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    expect(response.status()).toBe(201)
  })

  test('should fail registration with empty name', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await registerUser(request, {
      name: '',
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    await expectValidationError(response, 422)
  })

  test('should fail registration with empty email', async ({ request }) => {
    const testUser = generateTestUser()
    const response = await registerUser(request, {
      name: testUser.name,
      email: '',
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    await expectValidationError(response, 422)
  })
})
