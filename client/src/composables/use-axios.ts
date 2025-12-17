import type { AxiosError, InternalAxiosRequestConfig } from 'axios'

import axios from 'axios'
import { useRouter } from 'vue-router'

import { useToast } from '@/composables/use-toast'
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
      const toast = useToast()
      const status = error.response?.status

      // Handle 401 Unauthorized - redirect to login (don't store 401 errors)
      if (status === 401) {
        handleUnauthorized(error)
        return Promise.reject(error)
      }

      // Store error in error store
      errorStore.setApiError(error)

      // Show toast notifications for specific error types
      if (status === 403) {
        toast.showError('You are not authorized to access this page')
      } else if (status === 500) {
        const message = error.response?.data?.message || 'Internal server error'
        toast.showError(message)
      }

      return Promise.reject(error)
    },
  )

  async function getCsrfCookie() {
    return await axiosInstance.get('/sanctum/csrf-cookie')
  }

  function handleUnauthorized(_error: AxiosError) {
    const currentRoute = router.currentRoute.value
    const isLoginPage = currentRoute.path === RouterPath.LOGIN

    // Only redirect if not already on login page
    if (!isLoginPage) {
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
  const isLocal = env.VITE_APP_ENV === 'local'
  const baseURL = isLocal ? '' : env.VITE_SERVER_API_URL

  return axios.create({
    baseURL,
    timeout: env.VITE_SERVER_API_TIMEOUT,
    withCredentials: true,
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
}
