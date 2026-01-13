import { defineStore } from 'pinia'

import type { IUser } from '@/pages/users/models/users'

export const useAuthStore = defineStore('user', () => {
  const user = ref<IUser | null>(null)

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

  function setUser(userData: IUser | null) {
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
