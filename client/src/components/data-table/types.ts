import type { ColumnDef, Table } from '@tanstack/vue-table'

import type { ISorting } from '@/services/query-utils'

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

export interface IDataTableProps<T, F> {
  loading: boolean
  columns: ColumnDef<T>[]
  data: T[]
  serverPagination: IServerPagination
  sorting: ISorting
  onSortingChange: (sorting: ISorting) => void
  filter: F
  onFiltersChange: (filter: F) => void
  onClearFilters: () => void
}

export interface IDataTableToolbarProps<T, F> {
  table: Table<T>
  filters: F
  onFiltersChange: (filters: F) => void
  onClearFilters: () => void
}

export interface IDataTableFilterProps<F> {
  filters: F
  onFiltersChange: (filters: F) => void
  onClearFilters: () => void
}
