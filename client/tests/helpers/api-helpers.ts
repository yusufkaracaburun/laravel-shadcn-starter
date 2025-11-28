import { APIRequestContext, expect } from '@playwright/test'

const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export async function loginUser(request: APIRequestContext, credentials: LoginCredentials) {
  const response = await request.post(`${apiURL}/login`, {
    data: credentials,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })

  return response
}

export async function registerUser(request: APIRequestContext, data: RegisterData) {
  const response = await request.post(`${apiURL}/register`, {
    data,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })

  return response
}

export async function expectSuccessfulLogin(response: any) {
  expect(response.status()).toBe(200)
  const contentType = response.headers()['content-type'] || ''
  if (!contentType.includes('application/json')) {
    const text = await response.text()
    throw new Error(`Expected JSON but got ${contentType}. Response: ${text.substring(0, 200)}`)
  }
  const body = await response.json()
  expect(body).toHaveProperty('two_factor')
  return body
}

export async function expectSuccessfulRegistration(response: any) {
  expect(response.status()).toBe(201)
  return response
}

export async function expectValidationError(response: any, expectedStatus = 422) {
  expect(response.status()).toBe(expectedStatus)
  const body = await response.json()
  expect(body).toHaveProperty('errors')
  return body
}

