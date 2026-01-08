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

export function convertSortingToQueryString(sorting: ISorting): string {
  const prefix = sorting.desc ? '-' : ''
  return `${prefix}${sorting.id}`
}

export function objectToQueryStringDeep(
  obj: Record<string, any>,
  prefix = '',
): string {
  const pairs: string[] = []

  for (const [key, value] of Object.entries(obj)) {
    if (value === null || value === undefined) {
      continue
    }

    if (Array.isArray(value) && value.length === 0) {
      continue
    }

    if (
      typeof value === 'object'
      && !Array.isArray(value)
      && Object.keys(value).length === 0
    ) {
      continue
    }

    const paramKey = prefix
      ? `${prefix}[${encodeURIComponent(key)}]`
      : encodeURIComponent(key)

    if (key === 'sort') {
      pairs.push(`sort=${convertSortingToQueryString(value)}`)
      continue
    }

    if (key === 'include' && Array.isArray(value)) {
      pairs.push(
        `${paramKey}=${value.map(v => encodeURIComponent(String(v))).join(',')}`,
      )
      continue
    }

    if (Array.isArray(value)) {
      value.forEach((v) => {
        pairs.push(`${paramKey}[]=${encodeURIComponent(String(v))}`)
      })
    } else if (typeof value === 'object') {
      const nested = objectToQueryStringDeep(value, paramKey)
      if (nested) {
        pairs.push(nested)
      }
    } else {
      pairs.push(`${paramKey}=${encodeURIComponent(String(value))}`)
    }
  }

  return pairs.join('&')
}

export function buildQueryString(params: Record<string, any>): string {
  const qs = objectToQueryStringDeep(params)
  return qs ? `?${qs}` : ''
}
