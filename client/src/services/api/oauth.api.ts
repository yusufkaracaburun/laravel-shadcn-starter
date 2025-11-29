import type { AxiosError } from 'axios'

import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axios from 'axios'

import env from '@/utils/env'

// OAuth redirect - returns URL to redirect to
export function useOAuthRedirectMutation() {
  const queryClient = useQueryClient()
  const baseURL = env.VITE_SERVER_API_URL

  return useMutation<string, AxiosError, string>({
    mutationKey: ['oauthRedirect'],
    mutationFn: async (provider: string) => {
      // OAuth redirect is a GET request that returns a redirect URL
      // For SPA, we need to handle this differently
      // The backend should return the redirect URL in the response
      const response = await axios.get(`/auth/redirect/${provider}`, {
        baseURL,
        withCredentials: true,
        maxRedirects: 0,
        validateStatus: status => status >= 200 && status < 400,
      })

      // If response has a location header or data with URL, return it
      if (response.headers.location) {
        return response.headers.location
      }
      if (response.data?.url) {
        return response.data.url
      }

      // Otherwise construct the URL
      return `${baseURL}/auth/redirect/${provider}`
    },
  })
}

// Remove OAuth connection
export function useRemoveOAuthConnectionMutation() {
  const queryClient = useQueryClient()
  const baseURL = env.VITE_SERVER_API_URL

  return useMutation<void, AxiosError, string>({
    mutationKey: ['removeOAuthConnection'],
    mutationFn: async (provider: string) => {
      await axios.delete(`/auth/destroy/${provider}`, {
        baseURL,
        withCredentials: true,
      })
    },
    onSuccess: () => {
      // Invalidate user data to refresh OAuth connections
      queryClient.invalidateQueries({ queryKey: ['currentUser'] })
    },
  })
}
