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
      console.warn('CSRF TOKEN', request.method, csrfToken)

      // if (!csrfToken) {
      //   await getCsrfCookie()
      //   csrfToken = getCookieValue('XSRF-TOKEN')
      // }

      request.headers['X-XSRF-TOKEN'] = csrfToken

      return request
    },
    (error) => {
      return Promise.reject(error)
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
        // Only redirect if not already on login page
        if (router.currentRoute.value.path !== (RouterPath.LOGIN as string)) {
          router.push({
            path: RouterPath.LOGIN as string,
            query: { redirect: router.currentRoute.value.fullPath },
          })
        }
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
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
}
