import { defineStore } from 'pinia'
import { computed } from 'vue'

import { useAuth } from '@/composables/use-auth'

/**
 * Auth store - wrapper around useAuth() composable for Pinia integration
 * Used by router guards and plugins that need reactive auth state
 * For login/register/logout, use the useAuth() composable directly instead
 */
export const useAuthStore = defineStore('auth', () => {
  // Use the composable for all auth functionality
  const auth = useAuth()

  return {
    user: computed(() => auth.user.value),
    loading: computed(() => auth.loading.value),
    isAuthenticated: computed(() => auth.isAuthenticated.value),
    isLogin: computed(() => auth.isAuthenticated.value),
    fetchUser: auth.fetchUser,
  }
})
