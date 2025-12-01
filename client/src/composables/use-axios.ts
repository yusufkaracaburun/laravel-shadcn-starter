import type { AxiosError, AxiosRequestConfig } from 'axios'

import axios from 'axios'
import { useRouter } from 'vue-router'

import { RouterPath } from '@/constants/route-path'
import env from '@/utils/env'

export function useAxios() {
  const router = useRouter()
  // Use relative URLs to leverage Vite proxy (same-origin for cookies)
  // This makes requests go through Vite dev server proxy, avoiding cross-site cookie issues
  const axiosWebInstance = initializeAxios({
    baseURL: '', // Relative URL - goes through Vite proxy
  })

  const axiosInstance = initializeAxios({
    baseURL: env.VITE_SERVER_API_PREFIX, // Relative URL - goes through Vite proxy
  })

  axiosInstance.interceptors.request.use((config) => {
    return config
  }, (error) => {
    return Promise.reject(error)
  })

  axiosInstance.interceptors.response.use((response) => {
    return response
  }, (error: AxiosError) => {
    // Handle 401 Unauthorized - redirect to login
    if (error.response?.status === 401) {
      // Only redirect if not already on login page
      if (router.currentRoute.value.path !== RouterPath.LOGIN as string) {
        router.push({
          path: RouterPath.LOGIN as string,
          query: { redirect: router.currentRoute.value.fullPath },
        })
      }
    }
    return Promise.reject(error)
  })

  return {
    axiosInstance,
    axiosWebInstance,
  }
}

function initializeAxios(config: AxiosRequestConfig) {
  return axios.create({
    ...config,
    timeout: env.VITE_SERVER_API_TIMEOUT,
    withCredentials: true,
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json',
    },
  })
}
