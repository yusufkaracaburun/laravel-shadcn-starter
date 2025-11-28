import type { AxiosError } from 'axios'
import axios from 'axios'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'
import env from '@/utils/env'

import type { IResponse } from '../types/response.type'

export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  current_team_id: number | null
  profile_photo_path: string | null
  currentTeam: Team | null
  teams: Team[]
  oauthConnections: OauthConnection[]
}

export interface Team {
  id: number
  name: string
  personal_team: boolean
  user_id: number
  created_at: string
  updated_at: string
}

export interface OauthConnection {
  id: number
  user_id: number
  provider: string
  provider_id: string
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  terms?: boolean
}

// Get CSRF cookie for Sanctum
export async function getCsrfCookie() {
  const baseURL = env.VITE_SERVER_API_URL
  return await axios.get('/sanctum/csrf-cookie', { baseURL, withCredentials: true })
}

// Get current authenticated user
export function useGetCurrentUserQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<User>, AxiosError>({
    queryKey: ['currentUser'],
    queryFn: async () => {
      const response = await axiosInstance.get('/user/current', { withCredentials: true })
      return response.data
    },
    retry: false,
  })
}

// Login mutation
export function useLoginMutation() {
  const queryClient = useQueryClient()
  const baseURL = env.VITE_SERVER_API_URL

  return useMutation<{ success: boolean }, AxiosError<{ message?: string; errors?: Record<string, string[]> }>, LoginCredentials>({
    mutationKey: ['login'],
    mutationFn: async (credentials: LoginCredentials) => {
      // Get CSRF cookie first
      await getCsrfCookie()

      // Then login
      const response = await axios.post('/login', credentials, {
        baseURL,
        withCredentials: true,
      })

      return { success: response.status === 204 || response.status === 200 }
    },
    onSuccess: () => {
      // Invalidate and refetch user data
      queryClient.invalidateQueries({ queryKey: ['currentUser'] })
    },
  })
}

// Register mutation
export function useRegisterMutation() {
  const queryClient = useQueryClient()
  const baseURL = env.VITE_SERVER_API_URL

  return useMutation<{ success: boolean }, AxiosError<{ message?: string; errors?: Record<string, string[]> }>, RegisterData>({
    mutationKey: ['register'],
    mutationFn: async (data: RegisterData) => {
      // Get CSRF cookie first
      await getCsrfCookie()

      // Then register
      const response = await axios.post('/register', {
        ...data,
        terms: data.terms ?? true,
      }, {
        baseURL,
        withCredentials: true,
      })

      return { success: response.status === 201 || response.status === 200 }
    },
    onSuccess: () => {
      // Invalidate and refetch user data
      queryClient.invalidateQueries({ queryKey: ['currentUser'] })
    },
  })
}

// Logout mutation
export function useLogoutMutation() {
  const queryClient = useQueryClient()
  const baseURL = env.VITE_SERVER_API_URL

  return useMutation<void, AxiosError, void>({
    mutationKey: ['logout'],
    mutationFn: async () => {
      await axios.post('/logout', {}, {
        baseURL,
        withCredentials: true,
      })
    },
    onSuccess: () => {
      // Clear user data
      queryClient.setQueryData(['currentUser'], null)
      queryClient.removeQueries({ queryKey: ['currentUser'] })
    },
  })
}

