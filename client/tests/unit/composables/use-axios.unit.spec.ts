import type { AxiosError, AxiosRequestConfig, AxiosResponse } from 'axios'
import type axios from 'axios'

import { beforeEach, describe, expect, it, vi } from 'vitest'

import { useAxios } from '@/composables/use-axios'

// Mock router
const mockPush = vi.fn()
const mockCurrentRoute = {
  value: {
    path: '/dashboard',
    fullPath: '/dashboard',
    query: {},
  },
}

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
    currentRoute: mockCurrentRoute,
  }),
  useRoute: () => mockCurrentRoute.value,
}))

// Mock cookie
const mockGetCookieValue = vi.fn()
vi.mock('@/plugins/cookie/setup', () => ({
  getCookieValue: (name: string) => mockGetCookieValue(name),
}))

// Mock error store
const mockSetApiError = vi.fn()
vi.mock('@/stores/error.store', () => ({
  useErrorStore: () => ({
    setApiError: mockSetApiError,
  }),
}))

// Mock toast
const mockShowError = vi.fn()
vi.mock('@/composables/use-toast', () => ({
  useToast: () => ({
    showError: mockShowError,
  }),
}))

// Mock env
vi.mock('@/utils/env', () => ({
  default: {
    VITE_SERVER_API_URL: 'https://api.example.com',
    VITE_SERVER_API_TIMEOUT: 10000,
  },
}))

// Mock RouterPath
vi.mock('@/constants/route-path', () => ({
  RouterPath: {
    LOGIN: '/auth/sign-in',
    HOME: '/dashboard',
  },
}))

describe('useAxios', () => {
  let axiosInstance: ReturnType<typeof axios.create>
  let consoleWarnSpy: ReturnType<typeof vi.spyOn>

  beforeEach(() => {
    vi.clearAllMocks()
    mockGetCookieValue.mockReturnValue('test-csrf-token')
    mockCurrentRoute.value.path = '/dashboard'
    mockCurrentRoute.value.fullPath = '/dashboard'

    // Suppress console.warn output during tests
    consoleWarnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {})

    // Get fresh axios instance
    const { axiosInstance: instance } = useAxios()
    axiosInstance = instance
  })

  afterEach(() => {
    consoleWarnSpy.mockRestore()
  })

  describe('axios instance initialization', () => {
    it('should create axios instance with correct configuration', () => {
      // Arrange & Act
      const { axiosInstance } = useAxios()

      // Assert
      expect(axiosInstance).toBeDefined()
      expect(axiosInstance.defaults.baseURL).toBe('https://api.example.com')
      expect(axiosInstance.defaults.timeout).toBe(10000)
      expect(axiosInstance.defaults.withCredentials).toBe(true)
      expect(axiosInstance.defaults.headers.Accept).toBe('application/json')
      expect(axiosInstance.defaults.headers['X-Requested-With']).toBe('XMLHttpRequest')
    })
  })

  describe('request interceptor', () => {
    it('should not modify GET requests', async () => {
      // Arrange
      const config: AxiosRequestConfig = {
        method: 'GET',
        url: '/api/users',
        headers: {},
      }

      // Act
      const result = await axiosInstance.interceptors.request.handlers[0].fulfilled(config)

      // Assert
      expect(result).toEqual(config)
      expect(result.headers?.['X-XSRF-TOKEN']).toBeUndefined()
    })

    it('should add CSRF token to POST requests', async () => {
      // Arrange
      mockGetCookieValue.mockReturnValue('csrf-token-123')
      const config: AxiosRequestConfig = {
        method: 'POST',
        url: '/api/users',
        headers: {},
      }

      // Act
      const result = await axiosInstance.interceptors.request.handlers[0].fulfilled(config)

      // Assert
      expect(mockGetCookieValue).toHaveBeenCalledWith('XSRF-TOKEN')
      expect(result.headers?.['X-XSRF-TOKEN']).toBe('csrf-token-123')
    })

    it('should add CSRF token to PUT requests', async () => {
      // Arrange
      mockGetCookieValue.mockReturnValue('csrf-token-456')
      const config: AxiosRequestConfig = {
        method: 'PUT',
        url: '/api/users/1',
        headers: {},
      }

      // Act
      const result = await axiosInstance.interceptors.request.handlers[0].fulfilled(config)

      // Assert
      expect(result.headers?.['X-XSRF-TOKEN']).toBe('csrf-token-456')
    })

    it('should add CSRF token to DELETE requests', async () => {
      // Arrange
      mockGetCookieValue.mockReturnValue('csrf-token-789')
      const config: AxiosRequestConfig = {
        method: 'DELETE',
        url: '/api/users/1',
        headers: {},
      }

      // Act
      const result = await axiosInstance.interceptors.request.handlers[0].fulfilled(config)

      // Assert
      expect(result.headers?.['X-XSRF-TOKEN']).toBe('csrf-token-789')
    })

    it('should handle request interceptor errors', async () => {
      // Arrange
      const error = new Error('Request error')

      // Act & Assert
      await expect(
        axiosInstance.interceptors.request.handlers[0].rejected(error),
      ).rejects.toBe(error)
    })
  })

  describe('response interceptor', () => {
    it('should return successful responses unchanged', () => {
      // Arrange
      const response: AxiosResponse = {
        data: { success: true },
        status: 200,
        statusText: 'OK',
        headers: {},
        config: {} as AxiosRequestConfig,
      }

      // Act
      const result = axiosInstance.interceptors.response.handlers[0].fulfilled(response)

      // Assert
      expect(result).toBe(response)
    })

    it('should redirect to login on 401 error when not on login page', async () => {
      // Arrange
      mockCurrentRoute.value.path = '/dashboard'
      const error = {
        response: {
          status: 401,
          data: { message: 'Unauthorized' },
        },
      } as AxiosError

      // Act
      try {
        await axiosInstance.interceptors.response.handlers[0].rejected(error)
      }
      catch (e) {
        // Expected to reject
      }

      // Assert
      expect(mockPush).toHaveBeenCalledWith({
        path: '/auth/sign-in',
        query: { redirect: '/dashboard' },
      })
      expect(mockSetApiError).not.toHaveBeenCalled()
    })

    it('should not redirect on 401 error when already on login page', async () => {
      // Arrange
      mockCurrentRoute.value.path = '/auth/sign-in'
      const error = {
        response: {
          status: 401,
          data: { message: 'Unauthorized' },
        },
      } as AxiosError

      // Act
      try {
        await axiosInstance.interceptors.response.handlers[0].rejected(error)
      }
      catch (e) {
        // Expected to reject
      }

      // Assert
      expect(mockPush).not.toHaveBeenCalled()
      expect(mockSetApiError).not.toHaveBeenCalled()
    })

    it('should store 403 errors and show toast', async () => {
      // Arrange
      const error = {
        response: {
          status: 403,
          data: { message: 'Forbidden' },
        },
      } as AxiosError

      // Act
      try {
        await axiosInstance.interceptors.response.handlers[0].rejected(error)
      }
      catch (e) {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalledWith(error)
      expect(mockShowError).toHaveBeenCalledWith('You are not authorized to access this page')
    })

    it('should store 422 errors and show toast', async () => {
      // Arrange
      const error = {
        response: {
          status: 422,
          data: {
            message: 'Validation failed',
            errors: { email: ['The email is required'] },
          },
        },
      } as AxiosError

      // Act
      try {
        await axiosInstance.interceptors.response.handlers[0].rejected(error)
      }
      catch (e) {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalledWith(error)
      expect(mockShowError).toHaveBeenCalledWith('Validation error')
    })

    it('should store 500 errors and show toast', async () => {
      // Arrange
      const error = {
        response: {
          status: 500,
          data: { message: 'Internal server error' },
        },
      } as AxiosError

      // Act
      try {
        await axiosInstance.interceptors.response.handlers[0].rejected(error)
      }
      catch (e) {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalledWith(error)
      expect(mockShowError).toHaveBeenCalledWith('Internal server error')
    })

    it('should store other errors without showing specific toast', async () => {
      // Arrange
      const error = {
        response: {
          status: 404,
          data: { message: 'Not found' },
        },
      } as AxiosError

      // Act
      try {
        await axiosInstance.interceptors.response.handlers[0].rejected(error)
      }
      catch (e) {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalledWith(error)
      expect(mockShowError).not.toHaveBeenCalled()
    })

    it('should store network errors', async () => {
      // Arrange
      const error = {
        code: 'ERR_NETWORK',
        message: 'Network Error',
      } as AxiosError

      // Act
      try {
        await axiosInstance.interceptors.response.handlers[0].rejected(error)
      }
      catch (e) {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalledWith(error)
    })
  })

  describe('getCsrfCookie', () => {
    it('should call /sanctum/csrf-cookie endpoint', async () => {
      // Arrange
      const { axiosInstance: instance, getCsrfCookie } = useAxios()
      const mockGet = vi.fn().mockResolvedValue({ status: 200, data: {} })
      instance.get = mockGet

      // Act
      await getCsrfCookie()

      // Assert
      expect(mockGet).toHaveBeenCalledWith('/sanctum/csrf-cookie')
    })
  })

  describe('returned values', () => {
    it('should return axiosInstance and getCsrfCookie', () => {
      // Act
      const result = useAxios()

      // Assert
      expect(result).toHaveProperty('axiosInstance')
      expect(result).toHaveProperty('getCsrfCookie')
      expect(typeof result.getCsrfCookie).toBe('function')
      expect(result.axiosInstance).toBeDefined()
    })
  })
})
