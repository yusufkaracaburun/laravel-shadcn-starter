import type { AxiosError, AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios'

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

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
  let axiosInstance: AxiosInstance
  let consoleWarnSpy: ReturnType<typeof vi.spyOn>

  beforeEach(() => {
    vi.clearAllMocks()
    mockGetCookieValue.mockReturnValue('test-csrf-token')
    mockCurrentRoute.value.path = '/dashboard'
    mockCurrentRoute.value.fullPath = '/dashboard'

    // Suppress console.warn output during tests
    consoleWarnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {})

    // Get fresh axios instance for each test
    const { axiosInstance: instance } = useAxios()
    axiosInstance = instance
    // Reset adapter to default
    delete axiosInstance.defaults.adapter
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
      // Arrange - Create a fresh instance to ensure clean state
      mockGetCookieValue.mockClear()
      const { axiosInstance: freshInstance } = useAxios()
      let capturedConfig: AxiosRequestConfig | undefined

      freshInstance.defaults.adapter = async (config) => {
        capturedConfig = config
        return {
          data: {},
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
        } as AxiosResponse
      }

      // Act
      await freshInstance.get('/api/users')

      // Assert
      // GET requests should not call getCookieValue because interceptor returns early
      expect(mockGetCookieValue).not.toHaveBeenCalled()
      // GET requests should not have CSRF token in headers
      expect(capturedConfig?.headers?.['X-XSRF-TOKEN']).toBeUndefined()
    })

    it('should add CSRF token to POST requests', async () => {
      // Arrange
      mockGetCookieValue.mockReturnValue('csrf-token-123')
      let capturedConfig: AxiosRequestConfig | undefined
      axiosInstance.defaults.adapter = async (config) => {
        capturedConfig = config
        return {
          data: {},
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
        } as AxiosResponse
      }

      // Act
      await axiosInstance.post('/api/users', { name: 'Test' })

      // Assert
      expect(mockGetCookieValue).toHaveBeenCalledWith('XSRF-TOKEN')
      expect(capturedConfig?.headers?.['X-XSRF-TOKEN']).toBe('csrf-token-123')
    })

    it('should add CSRF token to PUT requests', async () => {
      // Arrange
      mockGetCookieValue.mockReturnValue('csrf-token-456')
      let capturedConfig: AxiosRequestConfig | undefined
      axiosInstance.defaults.adapter = async (config) => {
        capturedConfig = config
        return {
          data: {},
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
        } as AxiosResponse
      }

      // Act
      await axiosInstance.put('/api/users/1', { name: 'Updated' })

      // Assert
      expect(capturedConfig?.headers?.['X-XSRF-TOKEN']).toBe('csrf-token-456')
    })

    it('should add CSRF token to DELETE requests', async () => {
      // Arrange
      mockGetCookieValue.mockReturnValue('csrf-token-789')
      let capturedConfig: AxiosRequestConfig | undefined
      axiosInstance.defaults.adapter = async (config) => {
        capturedConfig = config
        return {
          data: {},
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
        } as AxiosResponse
      }

      // Act
      await axiosInstance.delete('/api/users/1')

      // Assert
      expect(capturedConfig?.headers?.['X-XSRF-TOKEN']).toBe('csrf-token-789')
    })
  })

  describe('response interceptor', () => {
    it('should return successful responses unchanged', async () => {
      // Arrange
      const mockResponse = {
        data: { success: true },
        status: 200,
        statusText: 'OK',
        headers: {},
      }
      axiosInstance.defaults.adapter = async () => mockResponse as AxiosResponse

      // Act
      const result = await axiosInstance.get('/api/test')

      // Assert
      expect(result).toEqual(mockResponse)
    })

    it('should redirect to login on 401 error when not on login page', async () => {
      // Arrange
      mockCurrentRoute.value.path = '/dashboard'
      axiosInstance.defaults.adapter = async () => {
        const error = {
          response: {
            status: 401,
            data: { message: 'Unauthorized' },
          },
        } as AxiosError
        throw error
      }

      // Act
      try {
        await axiosInstance.get('/api/protected')
      }
      catch {
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
      axiosInstance.defaults.adapter = async () => {
        const error = {
          response: {
            status: 401,
            data: { message: 'Unauthorized' },
          },
        } as AxiosError
        throw error
      }

      // Act
      try {
        await axiosInstance.get('/api/protected')
      }
      catch {
        // Expected to reject
      }

      // Assert
      expect(mockPush).not.toHaveBeenCalled()
      expect(mockSetApiError).not.toHaveBeenCalled()
    })

    it('should store 403 errors and show toast', async () => {
      // Arrange
      axiosInstance.defaults.adapter = async () => {
        const error = {
          response: {
            status: 403,
            data: { message: 'Forbidden' },
          },
        } as AxiosError
        throw error
      }

      // Act
      try {
        await axiosInstance.get('/api/protected')
      }
      catch {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalled()
      expect(mockShowError).toHaveBeenCalledWith('You are not authorized to access this page')
    })

    it('should store 422 errors and show toast', async () => {
      // Arrange
      axiosInstance.defaults.adapter = async () => {
        const error = {
          response: {
            status: 422,
            data: {
              message: 'Validation failed',
              errors: { email: ['The email is required'] },
            },
          },
        } as AxiosError
        throw error
      }

      // Act
      try {
        await axiosInstance.post('/api/users', {})
      }
      catch {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalled()
      expect(mockShowError).toHaveBeenCalledWith('Validation error')
    })

    it('should store 500 errors and show toast', async () => {
      // Arrange
      axiosInstance.defaults.adapter = async () => {
        const error = {
          response: {
            status: 500,
            data: { message: 'Internal server error' },
          },
        } as AxiosError
        throw error
      }

      // Act
      try {
        await axiosInstance.get('/api/users')
      }
      catch {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalled()
      expect(mockShowError).toHaveBeenCalledWith('Internal server error')
    })

    it('should store other errors without showing specific toast', async () => {
      // Arrange
      axiosInstance.defaults.adapter = async () => {
        const error = {
          response: {
            status: 404,
            data: { message: 'Not found' },
          },
        } as AxiosError
        throw error
      }

      // Act
      try {
        await axiosInstance.get('/api/users/999')
      }
      catch {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalled()
      expect(mockShowError).not.toHaveBeenCalled()
    })

    it('should store network errors', async () => {
      // Arrange
      axiosInstance.defaults.adapter = async () => {
        const error = {
          code: 'ERR_NETWORK',
          message: 'Network Error',
        } as AxiosError
        throw error
      }

      // Act
      try {
        await axiosInstance.get('/api/users')
      }
      catch {
        // Expected to reject
      }

      // Assert
      expect(mockSetApiError).toHaveBeenCalled()
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
