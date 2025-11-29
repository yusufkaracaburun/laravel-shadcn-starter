import { useQueryClient } from '@tanstack/vue-query'

import type { User } from '@/services/api/auth.api'
import type { IResponse } from '@/services/types/response.type'

import { RouterPath } from '@/constants/route-path'

export function useAuth() {
  const router = useRouter()
  const queryClient = useQueryClient()

  const loading = ref(false)

  function logout() {
    // Clear user data from query cache
    queryClient.setQueryData(['currentUser'], null)
    queryClient.removeQueries({ queryKey: ['currentUser'] })

    router.push({ path: RouterPath.LOGIN as string })
  }

  function toHome() {
    router.push({ path: RouterPath.HOME as string })
  }

  async function login() {
    loading.value = true
    await new Promise(resolve => setTimeout(resolve, 1000))
    // mock login - set a mock user in the query cache
    const mockUser: IResponse<User> = {
      data: {
        id: 1,
        name: 'Mock User',
        email: 'mock@example.com',
        email_verified_at: new Date().toISOString(),
        current_team_id: null,
        profile_photo_path: null,
        currentTeam: null,
        teams: [],
        oauthConnections: [],
      },
      extra: {},
      code: 200,
      message: 'Success',
      success: true,
    }
    queryClient.setQueryData(['currentUser'], mockUser)
    loading.value = false

    const redirect = router.currentRoute.value.query.redirect as string
    if (!redirect || redirect.startsWith('//')) {
      toHome()
    }
    else {
      router.push(redirect)
    }
  }

  return {
    loading,
    logout,
    login,
  }
}
