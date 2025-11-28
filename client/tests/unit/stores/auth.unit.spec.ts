import { describe, expect, it, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should initialize with isLogin false', () => {
    const store = useAuthStore()
    expect(store.isLogin).toBe(false)
  })

  it('should update isLogin', () => {
    const store = useAuthStore()
    store.isLogin = true
    expect(store.isLogin).toBe(true)
  })

  it('should reset isLogin', () => {
    const store = useAuthStore()
    store.isLogin = true
    store.isLogin = false
    expect(store.isLogin).toBe(false)
  })
})

