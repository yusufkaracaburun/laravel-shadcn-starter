import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from './types/response.type'

/**
 * User interface matching backend UserResource exactly
 * @see api/app/Http/Resources/UserResource.php
 */
export interface Role {
  id: number
  name: string
}

export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  current_team_id: number | null
  profile_photo_url: string | null
  created_at: string
  updated_at: string
  roles?: Role[]
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
 * Get available roles
 * @see api/app/Http/Controllers/Api/UserController.php::roles()
 */
export function useGetRolesQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<Role[]>, AxiosError>({
    queryKey: ['roles'],
    queryFn: async (): Promise<IResponse<Role[]>> => {
      const response = await axiosInstance.get('/api/user/roles')
      return response.data
    },
    staleTime: 10 * 60 * 1000, // Consider data fresh for 10 minutes
  })
}

/**
 * Convert TanStack Table sorting format to Spatie QueryBuilder format
 * @param sorting - Array of sorting objects from TanStack Table
 * @returns Sort string for Spatie QueryBuilder (e.g., "name" or "-name" or "name,-email")
 */
function convertSortingToQueryString(sorting: Array<{ id: string, desc: boolean }>): string | undefined {
  if (!sorting || sorting.length === 0) {
    return undefined
  }

  return sorting
    .map((sort) => {
      const prefix = sort.desc ? '-' : ''
      return `${prefix}${sort.id}`
    })
    .join(',')
}

/**
 * List all users with pagination and sorting
 * @see api/app/Http/Controllers/Api/UserController.php::index()
 * @param page - Page number (default: 1) - can be a ref or number
 * @param pageSize - Number of items per page (default: 15) - can be a ref or number
 * @param sorting - Sorting state from TanStack Table (default: []) - can be a ref or array
 */
export function useGetUsersQuery(
  page: MaybeRef<number> = 1,
  pageSize: MaybeRef<number> = 15,
  sorting: MaybeRef<Array<{ id: string, desc: boolean }>> = [],
) {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<PaginatedUsersResponse>, AxiosError>({
    queryKey: [
      'userList',
      computed(() => toValue(page)),
      computed(() => toValue(pageSize)),
      computed(() => JSON.stringify(toValue(sorting))),
    ],
    queryFn: async (): Promise<IResponse<PaginatedUsersResponse>> => {
      const currentPage = toValue(page)
      const currentPageSize = toValue(pageSize)
      const currentSorting = toValue(sorting)
      const sortParam = convertSortingToQueryString(currentSorting)

      const params: Record<string, any> = {
        page: currentPage,
        per_page: currentPageSize,
      }

      // Add sort parameter if sorting is provided
      if (sortParam) {
        params.sort = sortParam
      }

      const response = await axiosInstance.get('/api/user', { params })
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

/**
 * Create user request interface matching backend validation
 * @see api/app/Http/Controllers/Api/UserController.php::store()
 */
export interface CreateUserRequest {
  name: string
  email: string
  password: string
  password_confirmation: string
  profile_photo?: File | null
  role?: string | null
}

/**
 * Update user request interface matching backend validation
 * @see api/app/Http/Controllers/Api/UserController.php::update()
 */
export interface UpdateUserRequest {
  name?: string
  email?: string
  password?: string
  password_confirmation?: string
  profile_photo?: File | null
  role?: string | null
}

/**
 * Create a new user
 * @see api/app/Http/Controllers/Api/UserController.php::store()
 */
export function useCreateUserMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<User>, AxiosError, CreateUserRequest>({
    mutationFn: async (data: CreateUserRequest): Promise<IResponse<User>> => {
      // If profile photo is present, use FormData for multipart/form-data
      if (data.profile_photo) {
        const formData = new FormData()
        formData.append('name', data.name)
        formData.append('email', data.email)
        formData.append('password', data.password)
        formData.append('password_confirmation', data.password_confirmation)
        if (data.role)
          formData.append('role', data.role)
        formData.append('profile_photo', data.profile_photo)

        const response = await axiosInstance.post('/api/user', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        return response.data
      }

      // Otherwise, use JSON
      const response = await axiosInstance.post('/api/user', data)
      return response.data
    },
    onSuccess: () => {
      // Invalidate user list query to refresh the users list
      queryClient.invalidateQueries({ queryKey: ['userList'] })
    },
  })
}

/**
 * Update an existing user
 * @see api/app/Http/Controllers/Api/UserController.php::update()
 */
export function useUpdateUserMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<User>, AxiosError, { userId: number, data: UpdateUserRequest }>({
    mutationFn: async ({ userId, data }): Promise<IResponse<User>> => {
      // If profile photo is present, use FormData for multipart/form-data
      if (data.profile_photo) {
        const formData = new FormData()
        if (data.name)
          formData.append('name', data.name)
        if (data.email)
          formData.append('email', data.email)
        if (data.password)
          formData.append('password', data.password)
        if (data.password_confirmation)
          formData.append('password_confirmation', data.password_confirmation)
        if (data.role)
          formData.append('role', data.role)
        formData.append('profile_photo', data.profile_photo)
        // Use POST with _method=PUT for file uploads (Laravel supports this)
        formData.append('_method', 'PUT')

        const response = await axiosInstance.post(`/api/user/${userId}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        return response.data
      }

      // Otherwise, use JSON with PUT method
      const response = await axiosInstance.put(`/api/user/${userId}`, data)
      return response.data
    },
    onSuccess: () => {
      // Invalidate user list query and current user query to refresh the users list
      queryClient.invalidateQueries({ queryKey: ['userList'] })
      queryClient.invalidateQueries({ queryKey: ['user', 'current'] })
    },
  })
}

/**
 * Delete a user
 * @see api/app/Http/Controllers/Api/UserController.php::destroy()
 */
export function useDeleteUserMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<void, AxiosError, number>({
    mutationFn: async (userId: number): Promise<void> => {
      await axiosInstance.delete(`/api/user/${userId}`)
    },
    onSuccess: () => {
      // Invalidate user list query to refresh the users list
      queryClient.invalidateQueries({ queryKey: ['userList'] })
    },
  })
}
