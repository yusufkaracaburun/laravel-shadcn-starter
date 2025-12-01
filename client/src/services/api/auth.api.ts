import type { AxiosError } from 'axios'

import { useMutation, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from '../types/response.type'

export interface LoginRequest {
  email: string
  password: string
}

export interface RegisterRequest {
  name: string
  email: string
  password: string
  password_confirmation: string
}

/**
 * Get CSRF cookie from Sanctum
 * This MUST be called before login/register requests
 */
export async function getCsrfCookie(axiosInstance: ReturnType<typeof useAxios>['axiosInstance']): Promise<void> {
  await axiosInstance.get('/sanctum/csrf-cookie')
}

/**
 * Login mutation
 * Follows Sanctum SPA authentication flow:
 * 1. Get CSRF cookie
 * 2. POST /login with credentials
 * 3. Session cookies are automatically set
 */
export interface LoginResponse {
  two_factor: boolean
}

export function useLoginMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<LoginResponse, AxiosError, LoginRequest>({
    mutationFn: async (credentials: LoginRequest): Promise<LoginResponse> => {
      // Step 1: Get CSRF cookie (REQUIRED FIRST)
      await getCsrfCookie(axiosInstance)

      // Step 2: Login with credentials
      const response = await axiosInstance.post('/login', credentials)
      return response.data
    },
    onSuccess: () => {
      // Invalidate user query to refetch current user after login
      queryClient.invalidateQueries({ queryKey: ['user', 'current'] })
    },
  })
}

/**
 * Register mutation
 * Follows Sanctum SPA authentication flow:
 * 1. Get CSRF cookie
 * 2. POST /register with user data
 * 3. Session cookies are automatically set
 */
export function useRegisterMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<null>, AxiosError, RegisterRequest>({
    mutationFn: async (data: RegisterRequest): Promise<IResponse<null>> => {
      // Step 1: Get CSRF cookie (REQUIRED FIRST)
      await getCsrfCookie(axiosInstance)

      // Step 2: Register with user data
      const response = await axiosInstance.post('/register', data)
      return response.data
    },
    onSuccess: () => {
      // Invalidate user query to refetch current user after registration
      queryClient.invalidateQueries({ queryKey: ['user', 'current'] })
    },
  })
}

/**
 * Logout mutation
 * POST /logout to clear session cookies
 */
export function useLogoutMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<null>, AxiosError>({
    mutationFn: async (): Promise<IResponse<null>> => {
      const response = await axiosInstance.post('/logout')
      return response.data
    },
    onSuccess: () => {
      // Clear user data from cache
      queryClient.removeQueries({ queryKey: ['user', 'current'] })
    },
  })
}
