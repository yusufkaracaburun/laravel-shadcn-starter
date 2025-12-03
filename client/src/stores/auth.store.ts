import { defineStore } from 'pinia'

import type { User } from '@/services/users.service'

export const useAuthStore = defineStore('user', () => {
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => user.value !== null)

  // Backward compatibility: maintain isLogin for existing code
  const isLogin = computed({
    get: () => isAuthenticated.value,
    set: (value: boolean) => {
      if (!value) {
        user.value = null
      }
    },
  })

  function setUser(userData: User | null) {
    user.value = userData
  }

  function clearUser() {
    user.value = null
  }

  return {
    user,
    isAuthenticated,
    isLogin,
    setUser,
    clearUser,
  }
})
