import type { AxiosError } from 'axios'

export interface ISorting {
  id: string
  desc: boolean
}

export function defaultAxiosQueryOptions() {
  return {
    retry: (failureCount: number, number: AxiosError) => {
      if ((number as AxiosError).response?.status === 401) {
        return false
      }
      if ((number as AxiosError).response?.status === 404) {
        return false
      }
      return failureCount < 2
    },
  }
}

export function convertSortingToQueryString(sorting: Array<ISorting>): string | undefined {
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
