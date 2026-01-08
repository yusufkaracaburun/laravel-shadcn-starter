export interface IResponse<T> {
  data: Array<T> | T
  extra?: any
  code: number
  message: string
  success: boolean
}
export interface IPaginatedResponse<T> {
  data: IPaginatedData<T>
  code: number
  message: string
  success: boolean
}

export interface IPaginationRequestQuery {
  page?: number
  pageSize?: number
}

export type IRequestQuery<T extends Record<string, any>> = {
  page?: number
  pageSize?: number
} & {
  [K in keyof T]?: T[K]
}

export interface IPaginatedData<T> {
  data: T[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  first_page_url: string
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  path: string
  from: number
  to: number
}

export interface IStatus {
  id: string
  value: string
  label: string
  color: string
  style: string
}
