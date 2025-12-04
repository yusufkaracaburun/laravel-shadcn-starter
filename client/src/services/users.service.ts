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
  profile_photo_path: string | null
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
 * Paginated users response interface matching Laravel's pagination JSON structure
 * @see https://laravel.com/docs/12.x/pagination#converting-results-to-json
 * @see api/app/Http/Controllers/Api/UserController.php::index()
 */
export interface PaginatedUsersResponse {
  data: User[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  first_page_url: string
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  path: string
  from: number | null
  to: number | null
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
    retry: (failureCount: number, error: AxiosError) => {
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

/**
 * List all users with pagination
 * @see api/app/Http/Controllers/Api/UserController.php::index()
 * @param page - Page number (default: 1) - can be a ref or number
 * @param pageSize - Number of items per page (default: 15) - can be a ref or number
 */
export function useGetUsersQuery(page: MaybeRef<number> = 1, pageSize: MaybeRef<number> = 15) {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<PaginatedUsersResponse>, AxiosError>({
    queryKey: ['userList', computed(() => toValue(page)), computed(() => toValue(pageSize))],
    queryFn: async (): Promise<IResponse<PaginatedUsersResponse>> => {
      const currentPage = toValue(page)
      const currentPageSize = toValue(pageSize)
      const response = await axiosInstance.get('/api/user', {
        params: {
          page: currentPage,
          per_page: currentPageSize,
        },
      })
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      // Don't retry on 401 (unauthorized) - user is not authenticated
      if (error.response?.status === 401) {
        return false
      }
      // Retry up to 2 times for other errors
      return failureCount < 2
    },
  })
}
