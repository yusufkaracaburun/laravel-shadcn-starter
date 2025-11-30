import type { APIRequestContext } from '@playwright/test'
import { expect } from '@playwright/test'
import process from 'node:process'

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

export async function loginUser(request: APIRequestContext, credentials: LoginCredentials, cookies?: string) {
  // If no cookies provided, get CSRF cookies first
  let cookieString = cookies
  if (!cookieString) {
    cookieString = await getCsrfCookieString(request)
  }

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }

  if (cookieString) {
    headers.Cookie = cookieString
  }

  const response = await request.post(`${apiURL}/login`, {
    data: credentials,
    headers,
  })

  return response
}

export async function registerUser(request: APIRequestContext, data: RegisterData, cookies?: string) {
  // If no cookies provided, get CSRF cookies first
  let cookieString = cookies
  if (!cookieString) {
    cookieString = await getCsrfCookieString(request)
  }

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }

  if (cookieString) {
    headers.Cookie = cookieString
  }

  const response = await request.post(`${apiURL}/register`, {
    data,
    headers,
  })

  return response
}

export async function getCsrfCookie(request: APIRequestContext) {
  const response = await request.get(`${apiURL}/sanctum/csrf-cookie`, {
    headers: {
      Accept: 'application/json',
    },
  })
  return response
}

function extractCookieValue(cookieHeader: string | string[] | undefined): string[] {
  let cookies: string[] = []
  if (Array.isArray(cookieHeader)) {
    cookies = cookieHeader
  }
  else if (cookieHeader) {
    cookies = [cookieHeader]
  }

  // Extract just the cookie name=value part (before the first semicolon)
  return cookies.map((cookie) => {
    const match = cookie.match(/^([^;]+)/)
    return match ? match[1].trim() : ''
  }).filter(Boolean)
}

function parseCookieValue(cookieString: string): string | null {
  const match = cookieString.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : null
}

export async function getCsrfCookieString(request: APIRequestContext): Promise<string> {
  const csrfResponse = await getCsrfCookie(request)
  const csrfCookieHeader = csrfResponse.headers()['set-cookie']
  const cookieValues = extractCookieValue(csrfCookieHeader)
  return cookieValues.length > 0 ? cookieValues.join('; ') : ''
}

export async function getCsrfTokenAndCookies(request: APIRequestContext): Promise<{ token: string | null, cookies: string }> {
  const csrfResponse = await getCsrfCookie(request)
  const csrfCookieHeader = csrfResponse.headers()['set-cookie']
  const cookieValues = extractCookieValue(csrfCookieHeader)
  const cookieString = cookieValues.length > 0 ? cookieValues.join('; ') : ''
  
  // Extract XSRF token from cookies - check the full cookie header strings
  let token: string | null = null
  const cookieHeaders = Array.isArray(csrfCookieHeader) ? csrfCookieHeader : (csrfCookieHeader ? [csrfCookieHeader] : [])
  
  for (const cookie of cookieHeaders) {
    const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match) {
      try {
        token = decodeURIComponent(match[1])
        break
      }
      catch {
        // If decoding fails, use raw value
        token = match[1]
        break
      }
    }
  }
  
  return { token, cookies: cookieString }
}

export async function getAuthenticatedContext(request: APIRequestContext, credentials: LoginCredentials) {
  // First get CSRF cookie
  const csrfResponse = await getCsrfCookie(request)

  // Extract cookies from CSRF response
  const csrfCookieHeader = csrfResponse.headers()['set-cookie']
  const csrfCookieValues = extractCookieValue(csrfCookieHeader)
  const csrfCookieString = csrfCookieValues.length > 0 ? csrfCookieValues.join('; ') : ''

  // Then login with CSRF cookies
  const loginResponse = await loginUser(request, credentials, csrfCookieString)
  const loginCookieHeader = loginResponse.headers()['set-cookie']
  const loginCookieValues = extractCookieValue(loginCookieHeader)

  // Combine all cookies
  const allCookieValues = [...csrfCookieValues, ...loginCookieValues]

  return {
    cookies: allCookieValues.length > 0 ? allCookieValues.join('; ') : '',
    loginResponse,
  }
}

export async function logoutUser(request: APIRequestContext, cookies?: string) {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }

  if (cookies) {
    headers.Cookie = cookies
  }

  const response = await request.post(`${apiURL}/logout`, {
    headers,
  })

  return response
}

export async function getCurrentUser(request: APIRequestContext, cookies?: string) {
  const headers: Record<string, string> = {
    Accept: 'application/json',
  }

  if (cookies) {
    headers.Cookie = cookies
  }

  const response = await request.get(`${apiURL}/api/user/current`, {
    headers,
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
