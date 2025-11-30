import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from '../types/response.type'
import type { User } from '@/composables/use-auth'

// Get user by ID
export function useGetUserQuery(id: number) {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<User>, AxiosError>({
    queryKey: ['user', id],
    queryFn: async () => {
      const response = await axiosInstance.get(`/user/${id}`)
      return response.data
    },
    enabled: !!id,
  })
}

// List users
export function useGetUsersQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<User[]>, AxiosError>({
    queryKey: ['users'],
    queryFn: async () => {
      const response = await axiosInstance.get('/user')
      return response.data
    },
  })
}

// Create user mutation
export function useCreateUserMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<User>, AxiosError, Partial<User>>({
    mutationKey: ['createUser'],
    mutationFn: async (data: Partial<User>) => {
      const response = await axiosInstance.post('/user', data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] })
    },
  })
}

// Update user mutation
export function useUpdateUserMutation(id: number) {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<User>, AxiosError, Partial<User>>({
    mutationKey: ['updateUser', id],
    mutationFn: async (data: Partial<User>) => {
      const response = await axiosInstance.put(`/user/${id}`, data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user', id] })
      queryClient.invalidateQueries({ queryKey: ['users'] })
      queryClient.invalidateQueries({ queryKey: ['currentUser'] })
    },
  })
}

// Delete user mutation
export function useDeleteUserMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<void, AxiosError, number>({
    mutationKey: ['deleteUser'],
    mutationFn: async (id: number) => {
      await axiosInstance.delete(`/user/${id}`)
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] })
    },
  })
}
