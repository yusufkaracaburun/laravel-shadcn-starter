import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import type { User } from '@/services/api/auth.api'

import { useAuthStore } from '@/stores/auth'

// Mock TanStack Query before any imports
const mockRefetch = vi.fn()
const mockMutateAsync = vi.fn()

vi.mock('@tanstack/vue-query', () => ({
  useQuery: vi.fn(() => ({
    data: { value: null },
    isLoading: { value: false },
    refetch: mockRefetch,
  })),
  useMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
    isPending: { value: false },
  })),
  useQueryClient: vi.fn(() => ({
    invalidateQueries: vi.fn(),
    setQueryData: vi.fn(),
    removeQueries: vi.fn(),
  })),
}))

// Mock API services before store import
vi.mock('@/services/api/auth.api', () => ({
  useGetCurrentUserQuery: vi.fn(() => ({
    data: { value: null },
    isLoading: { value: false },
    refetch: mockRefetch,
  })),
  useLoginMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
  })),
  useRegisterMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
  })),
  useLogoutMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
  })),
}))

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    mockRefetch.mockResolvedValue({})
    mockMutateAsync.mockResolvedValue({ success: true })
  })

  describe('when initializing', () => {
    it('should initialize with no user', () => {
      // Arrange & Act
      const store = useAuthStore()

      // Assert
      expect(store.user).toBeNull()
      expect(store.isAuthenticated).toBe(false)
      expect(store.isLogin).toBe(false)
    })

    it('should initialize with loading false', () => {
      // Arrange & Act
      const store = useAuthStore()

      // Assert
      expect(store.loading).toBe(false)
    })
  })

  describe('when user is authenticated', () => {
    it('should return isAuthenticated as true when user exists', async () => {
      // Arrange
      const mockUser: User = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        email_verified_at: null,
        current_team_id: null,
        profile_photo_path: null,
        currentTeam: null,
        teams: [],
        oauthConnections: [],
      }

      const mockUserData = {
        value: {
          data: mockUser,
        },
      }

      // Mock the query to return user data
      const { useGetCurrentUserQuery } = await import('@/services/api/auth.api')
      vi.mocked(useGetCurrentUserQuery).mockReturnValue({
        data: mockUserData,
        isLoading: { value: false },
        refetch: mockRefetch,
      } as any)

      // Act - Create new store instance with mocked user data
      setActivePinia(createPinia())
      const store = useAuthStore()

      // Assert
      expect(store.isAuthenticated).toBe(true)
      expect(store.isLogin).toBe(true)
      expect(store.user).not.toBeNull()
      expect(store.user?.email).toBe(mockUser.email)
    })
  })

  describe('when fetching user', () => {
    it('should have fetchUser method', () => {
      // Arrange & Act
      const store = useAuthStore()

      // Assert
      expect(typeof store.fetchUser).toBe('function')
    })
  })

  describe('when logging in', () => {
    it('should have login method', () => {
      // Arrange & Act
      const store = useAuthStore()

      // Assert
      expect(typeof store.login).toBe('function')
    })
  })

  describe('when registering', () => {
    it('should have register method', () => {
      // Arrange & Act
      const store = useAuthStore()

      // Assert
      expect(typeof store.register).toBe('function')
    })
  })

  describe('when logging out', () => {
    it('should have logout method', () => {
      // Arrange & Act
      const store = useAuthStore()

      // Assert
      expect(typeof store.logout).toBe('function')
    })
  })
})
