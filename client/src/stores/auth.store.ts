import { defineStore } from 'pinia'

import type { User } from '@/services/api/users.api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => user.value !== null)

  function setUser(userData: User | null) {
    user.value = userData
  }

  function clearUser() {
    user.value = null
  }

  return {
    user,
    isAuthenticated,
    setUser,
    clearUser,
  }
})
