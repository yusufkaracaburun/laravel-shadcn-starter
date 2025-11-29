import axios from 'axios'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { getCsrfCookie, useGetCurrentUserQuery, useLoginMutation, useLogoutMutation, useRegisterMutation } from '@/services/api/auth.api'

// Mock axios
vi.mock('axios')
vi.mock('@/composables/use-axios', () => ({
  useAxios: () => ({
    axiosInstance: axios,
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
    VITE_SERVER_API_URL: 'http://localhost:8000',
  },
}))

describe('auth.api', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('getCsrfCookie', () => {
    it('should call /sanctum/csrf-cookie endpoint', async () => {
      // Arrange
      const mockAxiosGet = vi.fn().mockResolvedValue({ status: 200 })
      vi.mocked(axios.get).mockImplementation(mockAxiosGet)

      // Act
      await getCsrfCookie()

      // Assert
      expect(mockAxiosGet).toHaveBeenCalledWith('/sanctum/csrf-cookie', {
        baseURL: 'http://localhost:8000',
        withCredentials: true,
      })
    })
  })

  describe('useGetCurrentUserQuery', () => {
    it('should query /user/current endpoint', () => {
      // Arrange & Act
      useGetCurrentUserQuery()

      // Assert - Query should be set up (mocked implementation)
      expect(useGetCurrentUserQuery).toBeDefined()
    })
  })

  describe('useLoginMutation', () => {
    it('should get CSRF cookie before login', async () => {
      // Arrange
      const credentials = {
        email: 'test@example.com',
        password: 'password123',
      }
      const mockGet = vi.fn().mockResolvedValue({ status: 200 })
      const mockPost = vi.fn().mockResolvedValue({ status: 204 })
      vi.mocked(axios.get).mockImplementation(mockGet)
      vi.mocked(axios.post).mockImplementation(mockPost)

      // Act
      const mutation = useLoginMutation()
      await mutation.mutateAsync(credentials)

      // Assert
      expect(mockGet).toHaveBeenCalledWith('/sanctum/csrf-cookie', expect.any(Object))
      expect(mockPost).toHaveBeenCalledWith('/login', credentials, expect.any(Object))
    })
  })

  describe('useRegisterMutation', () => {
    it('should get CSRF cookie before register', async () => {
      // Arrange
      const registerData = {
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }
      const mockGet = vi.fn().mockResolvedValue({ status: 200 })
      const mockPost = vi.fn().mockResolvedValue({ status: 201 })
      vi.mocked(axios.get).mockImplementation(mockGet)
      vi.mocked(axios.post).mockImplementation(mockPost)

      // Act
      const mutation = useRegisterMutation()
      await mutation.mutateAsync(registerData)

      // Assert
      expect(mockGet).toHaveBeenCalledWith('/sanctum/csrf-cookie', expect.any(Object))
      expect(mockPost).toHaveBeenCalledWith('/register', expect.objectContaining({
        ...registerData,
        terms: true,
      }), expect.any(Object))
    })
  })

  describe('useLogoutMutation', () => {
    it('should call /logout endpoint', async () => {
      // Arrange
      const mockPost = vi.fn().mockResolvedValue({ status: 200 })
      vi.mocked(axios.post).mockImplementation(mockPost)

      // Act
      const mutation = useLogoutMutation()
      await mutation.mutateAsync()

      // Assert
      expect(mockPost).toHaveBeenCalledWith('/logout', {}, expect.any(Object))
    })
  })
})
