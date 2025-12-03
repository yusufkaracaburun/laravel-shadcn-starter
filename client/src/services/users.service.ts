import type { AxiosError } from 'axios'

import { useQuery } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from './types/response.type'

/**
 * User interface matching backend UserResource exactly
 * @see api/app/Http/Resources/UserResource.php
 */
export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  current_team_id: number | null
  created_at: string
  updated_at: string
  teams?: Team[]
  currentTeam?: Team | null
  [key: string]: unknown
}

export interface Team {
  id: number
  name: string
  user_id: number
  personal_team: boolean
  created_at: string
  updated_at: string
  [key: string]: unknown
}

/**
 * Get current authenticated user
 * Uses session cookies for authentication (Sanctum SPA mode)
 * @see api/app/Http/Controllers/Api/UserController.php::current()
 */
export function useGetCurrentUserQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<User>, AxiosError>({
    queryKey: ['user', 'current'],
    queryFn: async (): Promise<IResponse<User>> => {
      const response = await axiosInstance.get('/api/user/current')
      return response.data
    },
    retry: (failureCount, error) => {
      // Don't retry on 401 (unauthorized) - user is not authenticated
      if (error.response?.status === 401) {
        return false
      }
      // Retry up to 2 times for other errors
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000, // Consider data fresh for 5 minutes
  })
}
