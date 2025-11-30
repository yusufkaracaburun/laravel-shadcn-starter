import type { AxiosError, InternalAxiosRequestConfig } from 'axios'

import axios from 'axios'

import env from '@/utils/env'

/**
 * Extracts XSRF-TOKEN from cookies and adds it to request headers
 * Laravel's XSRF-TOKEN cookie is URL-encoded, so we need to decode it
 * This follows Laravel Sanctum's SPA authentication pattern:
 * https://laravel.com/docs/12.x/sanctum#logging-in
 */
function addCsrfToken(config: InternalAxiosRequestConfig): InternalAxiosRequestConfig {
  // Only run in browser environment
  if (typeof document === 'undefined') {
    return config
  }

  // Extract XSRF-TOKEN cookie value
  const cookies = document.cookie.split(';')
  const xsrfCookie = cookies.find(cookie => cookie.trim().startsWith('XSRF-TOKEN='))

  if (xsrfCookie) {
    const tokenValue = xsrfCookie.split('=')[1]?.trim()
    if (tokenValue) {
      try {
        // Decode URL-encoded cookie value (Laravel stores it URL-encoded)
        const decodedToken = decodeURIComponent(tokenValue)
        config.headers['X-XSRF-TOKEN'] = decodedToken
      }
      catch {
        // If decoding fails, use raw value as fallback
        config.headers['X-XSRF-TOKEN'] = tokenValue
        console.warn('⚠️ Failed to decode CSRF token, using raw value')
      }
    }
    else {
      console.warn('⚠️ XSRF-TOKEN cookie found but value is empty', { url: config.url })
    }
  }
  else {
    // Only warn for auth endpoints
    const isAuthEndpoint = config.url && ['/login', '/register', '/logout'].some(path => config.url?.includes(path))
    if (isAuthEndpoint) {
      console.warn('⚠️ XSRF-TOKEN cookie not found in document.cookie', {
        url: config.url,
        allCookies: document.cookie,
      })
    }
  }

  return config
}

export function useAxios() {
  // In development with Vite proxy, use relative URLs (same origin)
  // In production, use the full API URL
  const isDevelopment = import.meta.env.DEV
  const apiBaseURL = isDevelopment ? '' : env.VITE_SERVER_API_URL // Empty string = relative URL (uses Vite proxy)
  const apiPrefix = env.VITE_SERVER_API_PREFIX

  const axiosInstance = axios.create({
    baseURL: apiBaseURL + apiPrefix,
    timeout: env.VITE_SERVER_API_TIMEOUT,
    withCredentials: true, // Required for Sanctum stateful SPA authentication
    // Axios will automatically read XSRF-TOKEN cookie and send as X-XSRF-TOKEN header
    // But Laravel's cookie is URL-encoded, so we use a custom interceptor
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
  })

  // Custom interceptor to handle URL-decoded XSRF-TOKEN cookie
  axiosInstance.interceptors.request.use(addCsrfToken)
  axiosInstance.interceptors.response.use(
    response => response,
    (error: AxiosError) => Promise.reject(error),
  )

  // Separate axios instance for auth endpoints (login, register, logout, sanctum/csrf-cookie)
  // These are outside the API prefix, so they need a different baseURL
  // In development, use relative URLs (Vite proxy handles routing)
  const authAxiosInstance = axios.create({
    baseURL: apiBaseURL, // Empty string in dev = relative URL (uses Vite proxy)
    timeout: env.VITE_SERVER_API_TIMEOUT,
    withCredentials: true, // Required for Sanctum stateful SPA authentication
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
  })

  // Custom interceptor to handle URL-decoded XSRF-TOKEN cookie
  authAxiosInstance.interceptors.request.use(addCsrfToken)
  authAxiosInstance.interceptors.response.use(
    response => response,
    (error: AxiosError) => Promise.reject(error),
  )

  return {
    axiosInstance,
    authAxiosInstance, // For login, register, logout endpoints outside API prefix
  }
}
