import axios from 'axios'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { useGetCurrentUserQuery } from '@/services/users.service'

// Mock axios
vi.mock('axios')
vi.mock('@/composables/use-axios', () => ({
  useAxios: () => ({
    axiosInstance: axios,
  }),
}))

// Mock TanStack Query
vi.mock('@tanstack/vue-query', () => ({
  useQuery: vi.fn((options: any) => ({
    data: { value: null },
    isLoading: { value: false },
    refetch: vi.fn(),
    queryFn: options.queryFn,
  })),
}))

// Mock env
vi.mock('@/utils/env', () => ({
  default: {
    VITE_SERVER_API_URL: 'https://api.skeleton:8890',
    VITE_SERVER_API_TIMEOUT: 10000,
  },
}))

describe('users.service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('useGetCurrentUserQuery', () => {
    it('should query /api/user/current endpoint', async () => {
      // Arrange
      const mockUser = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        email_verified_at: '2024-01-01T00:00:00Z',
        current_team_id: 1,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
      }

      const mockResponse = {
        data: {
          success: true,
          code: 200,
          message: 'Success',
          data: mockUser,
          extra: {},
        },
      }

      const mockGet = vi.fn().mockResolvedValue(mockResponse)
      vi.mocked(axios.get).mockImplementation(mockGet)

      // Act
      const query = useGetCurrentUserQuery()
      const result = await query.queryFn()

      // Assert
      expect(mockGet).toHaveBeenCalledWith('/api/user/current')
      expect(result).toEqual(mockResponse.data)
      expect(result.data).toEqual(mockUser)
    })

    it('should return IResponse<User> structure', async () => {
      // Arrange
      const mockResponse = {
        data: {
          success: true,
          code: 200,
          message: 'Success',
          data: {
            id: 1,
            name: 'Test User',
            email: 'test@example.com',
          },
          extra: {},
        },
      }

      vi.mocked(axios.get).mockResolvedValue(mockResponse)

      // Act
      const query = useGetCurrentUserQuery()
      const result = await query.queryFn()

      // Assert
      expect(result).toHaveProperty('success', true)
      expect(result).toHaveProperty('code', 200)
      expect(result).toHaveProperty('data')
      expect(result.data).toHaveProperty('id')
      expect(result.data).toHaveProperty('name')
      expect(result.data).toHaveProperty('email')
    })

    it('should handle query configuration correctly', () => {
      // Arrange & Act
      const query = useGetCurrentUserQuery()

      // Assert - Query should be configured
      expect(query).toBeDefined()
      expect(query).toHaveProperty('data')
      expect(query).toHaveProperty('isLoading')
      expect(query).toHaveProperty('refetch')
    })
  })
})

