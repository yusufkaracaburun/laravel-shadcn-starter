import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it } from 'vitest'

import type { User } from '@/services/api/users.api'

import { useAuthStore } from '@/stores/auth.store'

function createMockUser(overrides: Partial<User> = {}): User {
  return {
    id: 1,
    name: 'Test User',
    email: 'test@example.com',
    email_verified_at: null,
    current_team_id: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
    teams: [],
    currentTeam: null,
    ...overrides,
  }
}

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('initializes with no user', () => {
    const store = useAuthStore()

    expect(store.user).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('sets user and updates authentication state', () => {
    const store = useAuthStore()
    const mockUser = createMockUser()

    store.setUser(mockUser)

    expect(store.user).toEqual(mockUser)
    expect(store.isAuthenticated).toBe(true)
  })

  it('clears user and resets authentication state', () => {
    const store = useAuthStore()
    const mockUser = createMockUser()

    store.setUser(mockUser)
    store.clearUser()

    expect(store.user).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })
})
