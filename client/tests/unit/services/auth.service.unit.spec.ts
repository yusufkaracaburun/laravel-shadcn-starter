import axios from 'axios'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import {
  useLoginMutation,
  useLogoutMutation,
  useRegisterMutation,
} from '@/services/auth.service'

// Mock axios
vi.mock('axios')
vi.mock('@/composables/use-axios', () => ({
  useAxios: () => ({
    axiosInstance: axios,
    getCsrfCookie: vi.fn(),
  }),
}))

// Mock TanStack Query - need to allow mutations to execute
const mockQueryClient = {
  invalidateQueries: vi.fn(),
  setQueryData: vi.fn(),
  removeQueries: vi.fn(),
}

vi.mock('@tanstack/vue-query', async () => {
  const actual = await vi.importActual('@tanstack/vue-query')
  return {
    ...actual,
    useQuery: vi.fn(() => ({
      data: { value: null },
      isLoading: { value: false },
      refetch: vi.fn(),
    })),
    useMutation: vi.fn((options: any) => ({
      mutateAsync: async (...args: any[]) => {
        // Actually execute the mutationFn if provided
        if (options.mutationFn) {
          return await options.mutationFn(...args)
        }
        return Promise.resolve({})
      },
      isPending: { value: false },
    })),
    useQueryClient: vi.fn(() => mockQueryClient),
  }
})

// Mock env
vi.mock('@/utils/env', () => ({
  default: {
    VITE_SERVER_API_URL: 'https://api.skeleton:8890',
    VITE_SERVER_API_TIMEOUT: 10000,
  },
}))

describe('auth.service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('useLoginMutation', () => {
    it('should call /login endpoint with credentials', async () => {
      // Arrange
      const credentials = {
        email: 'test@example.com',
        password: 'password123',
      }
      const mockPost = vi.fn().mockResolvedValue({
        data: { two_factor: false },
      })
      vi.mocked(axios.post).mockImplementation(mockPost)

      // Act
      const mutation = useLoginMutation()
      const result = await mutation.mutateAsync(credentials)

      // Assert
      expect(mockPost).toHaveBeenCalledWith('/login', credentials)
      expect(result).toEqual({ two_factor: false })
    })

    it('should return LoginResponse structure', async () => {
      // Arrange
      const credentials = {
        email: 'test@example.com',
        password: 'password123',
      }
      const mockResponse = { two_factor: false }
      vi.mocked(axios.post).mockResolvedValue({
        data: mockResponse,
      })

      // Act
      const mutation = useLoginMutation()
      const result = await mutation.mutateAsync(credentials)

      // Assert
      expect(result).toHaveProperty('two_factor')
    })
  })

  describe('useRegisterMutation', () => {
    it('should call /register endpoint with user data', async () => {
      // Arrange
      const registerData = {
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }
      const mockResponse = {
        success: true,
        code: 201,
        message: 'User created',
        data: null,
        extra: {},
      }
      const mockPost = vi.fn().mockResolvedValue({
        data: mockResponse,
      })
      vi.mocked(axios.post).mockImplementation(mockPost)

      // Act
      const mutation = useRegisterMutation()
      const result = await mutation.mutateAsync(registerData)

      // Assert
      expect(mockPost).toHaveBeenCalledWith('/register', registerData)
      expect(result).toEqual(mockResponse)
      expect(result).toHaveProperty('success', true)
    })

    it('should return IResponse<null> structure', async () => {
      // Arrange
      const registerData = {
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }
      const mockResponse = {
        success: true,
        code: 201,
        message: 'Success',
        data: null,
        extra: {},
      }
      vi.mocked(axios.post).mockResolvedValue({
        data: mockResponse,
      })

      // Act
      const mutation = useRegisterMutation()
      const result = await mutation.mutateAsync(registerData)

      // Assert
      expect(result).toHaveProperty('success')
      expect(result).toHaveProperty('code')
      expect(result).toHaveProperty('data', null)
    })
  })

  describe('useLogoutMutation', () => {
    it('should call /logout endpoint', async () => {
      // Arrange
      const mockResponse = {
        success: true,
        code: 200,
        message: 'Success',
        data: null,
        extra: {},
      }
      const mockPost = vi.fn().mockResolvedValue({
        data: mockResponse,
      })
      vi.mocked(axios.post).mockImplementation(mockPost)

      // Act
      const mutation = useLogoutMutation()
      const result = await mutation.mutateAsync()

      // Assert
      expect(mockPost).toHaveBeenCalledWith('/logout')
      expect(result).toEqual(mockResponse)
    })

    it('should return IResponse<null> structure', async () => {
      // Arrange
      const mockResponse = {
        success: true,
        code: 200,
        message: 'Success',
        data: null,
        extra: {},
      }
      vi.mocked(axios.post).mockResolvedValue({
        data: mockResponse,
      })

      // Act
      const mutation = useLogoutMutation()
      const result = await mutation.mutateAsync()

      // Assert
      expect(result).toHaveProperty('success', true)
      expect(result).toHaveProperty('code', 200)
      expect(result).toHaveProperty('data', null)
    })

    it('should not require any parameters', async () => {
      // Arrange
      vi.mocked(axios.post).mockResolvedValue({
        data: { success: true, data: null },
      })

      // Act
      const mutation = useLogoutMutation()
      await mutation.mutateAsync()

      // Assert - Should work without parameters
      expect(axios.post).toHaveBeenCalledWith('/logout')
    })
  })
})
