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

export async function loginUser(request: APIRequestContext, credentials: LoginCredentials, cookies?: string, token?: string) {
  // Always get fresh CSRF cookies and token to ensure they're valid
  const csrfData = await getCsrfTokenAndCookies(request)
  const cookieString = cookies || csrfData.cookies
  const xsrfToken = token || csrfData.token

  if (!xsrfToken) {
    throw new Error('Failed to extract XSRF-TOKEN from CSRF cookie response')
  }

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-XSRF-TOKEN': xsrfToken,
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

export async function registerUser(request: APIRequestContext, data: RegisterData, cookies?: string, token?: string) {
  // Always get fresh CSRF cookies and token to ensure they're valid
  const csrfData = await getCsrfTokenAndCookies(request)
  const cookieString = cookies || csrfData.cookies
  const xsrfToken = token || csrfData.token

  if (!xsrfToken) {
    throw new Error('Failed to extract XSRF-TOKEN from CSRF cookie response')
  }

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-XSRF-TOKEN': xsrfToken,
    'Origin': apiURL,
    'Referer': `${apiURL}/`,
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
  // The value may be URL-encoded, which is fine - we'll decode it when extracting the token
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

/**
 * Parse a Set-Cookie header value into cookie name and value
 */
function parseCookie(cookieHeader: string): { name: string; value: string } | null {
  const match = cookieHeader.match(/^([^=]+)=([^;]+)/)
  if (match && match[1] && match[2]) {
    return {
      name: match[1].trim(),
      value: match[2].trim(),
    }
  }
  return null
}

export async function getCsrfTokenAndCookies(request: APIRequestContext): Promise<{ token: string | null, cookies: string }> {
  const csrfResponse = await getCsrfCookie(request)
  const csrfCookieHeader = csrfResponse.headers()['set-cookie']
  
  // Extract XSRF token from cookies
  // Laravel sets the XSRF-TOKEN cookie with a URL-encoded encrypted value
  // The cookie in Set-Cookie header is: XSRF-TOKEN=encrypted_value%3D; expires=...
  // When sending back:
  // - Cookie header: XSRF-TOKEN=encrypted_value%3D (URL-encoded, as received)
  // - X-XSRF-TOKEN header: encrypted_value= (URL-decoded encrypted value)
  // Laravel will decrypt both and compare the decrypted values
  
  let token: string | null = null
  const cookieHeaders = Array.isArray(csrfCookieHeader) ? csrfCookieHeader : (csrfCookieHeader ? [csrfCookieHeader] : [])
  
  // Extract token from full cookie header (value is URL-encoded in Set-Cookie)
  for (const cookie of cookieHeaders) {
    const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match && match[1]) {
      const urlEncodedValue = match[1].trim()
      try {
        // Decode URL-encoding to get the encrypted value for X-XSRF-TOKEN header
        // This is the encrypted session token that Laravel will decrypt
        token = decodeURIComponent(urlEncodedValue)
      }
      catch {
        // If decoding fails, use raw value (shouldn't happen with valid cookies)
        token = urlEncodedValue
      }
      break
    }
  }
  
  // Extract all cookie values for sending in Cookie header
  // These should be kept URL-encoded as they appear in Set-Cookie
  // Format: "name1=value1; name2=value2"
  const cookieValues = extractCookieValue(csrfCookieHeader)
  const cookieString = cookieValues.length > 0 ? cookieValues.join('; ') : ''
  
  // Fallback: if we didn't find token in headers, try extracting from cookie string
  if (!token && cookieString) {
    const match = cookieString.match(/XSRF-TOKEN=([^;]+)/)
    if (match && match[1]) {
      const rawValue = match[1].trim()
      try {
        token = decodeURIComponent(rawValue)
      }
      catch {
        token = rawValue
      }
    }
  }
  
  return { token, cookies: cookieString }
}

export async function getAuthenticatedContext(request: APIRequestContext, credentials: LoginCredentials) {
  // First get CSRF cookie and token
  const csrfResponse = await getCsrfCookie(request)
  const csrfCookieHeader = csrfResponse.headers()['set-cookie']
  const csrfCookieValues = extractCookieValue(csrfCookieHeader)
  
  // Extract token from CSRF cookies
  let xsrfToken: string | null = null
  const cookieHeaders = Array.isArray(csrfCookieHeader) ? csrfCookieHeader : (csrfCookieHeader ? [csrfCookieHeader] : [])
  for (const cookie of cookieHeaders) {
    const match = cookie.match(/XSRF-TOKEN=([^;]+)/)
    if (match) {
      try {
        xsrfToken = decodeURIComponent(match[1])
        break
      }
      catch {
        xsrfToken = match[1]
        break
      }
    }
  }
  
  const csrfCookieString = csrfCookieValues.length > 0 ? csrfCookieValues.join('; ') : ''

  // Then login with CSRF cookies and token
  const loginResponse = await loginUser(request, credentials, csrfCookieString, xsrfToken)
  const loginCookieHeader = loginResponse.headers()['set-cookie']
  const loginCookieValues = extractCookieValue(loginCookieHeader)

  // Merge cookies, avoiding duplicates by cookie name
  const allCookieMap = new Map<string, string>()
  
  // Add CSRF cookies first
  for (const cookie of csrfCookieValues) {
    const [name] = cookie.split('=')
    if (name) {
      allCookieMap.set(name, cookie)
    }
  }
  
  // Add login cookies (these will override CSRF cookies if same name, e.g., updated XSRF-TOKEN)
  for (const cookie of loginCookieValues) {
    const [name] = cookie.split('=')
    if (name) {
      allCookieMap.set(name, cookie)
    }
  }

  const allCookieValues = Array.from(allCookieMap.values())

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
