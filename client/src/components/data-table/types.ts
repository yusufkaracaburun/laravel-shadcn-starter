import type { ColumnDef, SortingState } from '@tanstack/vue-table'

export const DEFAULT_PAGE = 1
export const DEFAULT_PAGE_SIZE: TPageSize = 10

export type TPageSize = 10 | 20 | 30 | 40 | 50
export interface IFacetedFilterOption {
  label: string
  value: string
  icon?: Component
}

export interface IServerPagination {
  page: number
  pageSize: TPageSize
  total: number
  onPageChange: (page: number) => void
  onPageSizeChange: (pageSize: TPageSize) => void
}

export interface IDataTableProps<T> {
  loading?: boolean
  columns: ColumnDef<T, any>[]
  data: T[]
  serverPagination?: IServerPagination
  sorting?: SortingState
  onSortingChange?: (sorting: SortingState) => void
}
