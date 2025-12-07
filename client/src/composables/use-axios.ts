import type { AxiosError, InternalAxiosRequestConfig } from 'axios'

import axios from 'axios'
import { useRouter } from 'vue-router'

import { RouterPath } from '@/constants/route-path'
import { getCookieValue } from '@/plugins/cookie/setup'
import { useErrorStore } from '@/stores/error.store'
import env from '@/utils/env'

export function useAxios() {
  const router = useRouter()
  const axiosInstance = initializeAxios()

  axiosInstance.interceptors.request.use(
    async (request: InternalAxiosRequestConfig) => {
      if (['GET', 'get'].includes(request.method as string)) {
        return request
      }

      const csrfToken = getCookieValue('XSRF-TOKEN')
      console.warn('[axiosInstance.interceptors.request] - CSRF TOKEN', request.method, csrfToken)

      request.headers['X-XSRF-TOKEN'] = csrfToken

      return request
    },
    (_error) => {
      return Promise.reject(_error)
    },
  )

  axiosInstance.interceptors.response.use(
    (response) => {
      return response
    },
    (error: AxiosError) => {
      const errorStore = useErrorStore()

      // Handle 401 Unauthorized - redirect to login (don't store 401 errors)
      if (error.response?.status === 401) {
        handleUnauthorized(error)
        return Promise.reject(error)
      }

      // Store other errors in error store
      errorStore.setApiError(error)

      // Keep existing toast notifications
      if (error.response?.status === 403) {
        useToast().showError('You are not authorized to access this page')
      }
      if (error.response?.status === 422) {
        useToast().showError('Validation error')
      }
      if (error.response?.status === 500) {
        useToast().showError('Internal server error')
      }
      return Promise.reject(error)
    },
  )

  async function getCsrfCookie() {
    return await axiosInstance.get('/sanctum/csrf-cookie')
  }

  function handleUnauthorized(_error: AxiosError) {
    const currentRoute = router.currentRoute.value
    const requiresAuth = currentRoute.meta.auth === true

    // Only redirect if the current route requires authentication
    // Routes with meta.auth === undefined or false are public and shouldn't redirect
    if (requiresAuth) {
      router.push({
        path: RouterPath.LOGIN as string,
        query: { redirect: currentRoute.fullPath },
      })
    }
  }

  return {
    axiosInstance,
    getCsrfCookie,
  }
}

function initializeAxios() {
  return axios.create({
    baseURL: env.VITE_SERVER_API_URL,
    timeout: env.VITE_SERVER_API_TIMEOUT,
    withCredentials: true,
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
}
