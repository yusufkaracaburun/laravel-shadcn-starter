import type { APIRequestContext } from '@playwright/test'

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

