import type { AxiosError } from 'axios'

export function defaultAxiosQueryOptions() {
  return {
    retry: (failureCount: number, error: AxiosError) => {
      if (error.response?.status === 401) {
        return false
      }
      return failureCount < 2
    },
  }
}

export function defaultAxiosQueryOptionsWith404() {
  return {
    retry: (failureCount: number, error: AxiosError) => {
      if (error.response?.status === 401) {
        return false
      }
      if (error.response?.status === 404) {
        return false
      }
      return failureCount < 2
    },
  }
}
