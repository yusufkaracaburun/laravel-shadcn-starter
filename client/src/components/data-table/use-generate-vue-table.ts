import type {
  ColumnFiltersState,
  TableOptionsWithReactiveData,
  VisibilityState,
} from '@tanstack/vue-table'

import {
  getCoreRowModel,
  getFacetedRowModel,
  getFacetedUniqueValues,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from '@tanstack/vue-table'

import type { IDataTableProps } from '@/components/data-table/types'
import type { ISorting } from '@/services/query-utils'

import { valueUpdater } from '@/lib/utils'

export function generateVueTable<T, F>(props: IDataTableProps<T, F>) {
  // Use external sorting state if provided, otherwise create internal one
  const internalSorting = ref<ISorting>({ id: 'created_at', desc: true })
  const sorting = props.sorting ?? internalSorting.value
  const columnFilters = ref<ColumnFiltersState>([])
  const columnVisibility = ref<VisibilityState>({})
  const rowSelection = ref({})

  const useServerPagination = !!props.serverPagination

  const pageIndex = computed(() => {
    if (useServerPagination && props.serverPagination) {
      return props.serverPagination.page - 1
    }
    return 0
  })

  const pageSize = computed(() => {
    if (useServerPagination && props.serverPagination) {
      return props.serverPagination.pageSize
    }
    return 10
  })

  const pageCount = computed(() => {
    if (useServerPagination && props.serverPagination) {
      return Math.ceil(
        props.serverPagination.total / props.serverPagination.pageSize,
      )
    }
    return -1
  })

  const tableConfig: TableOptionsWithReactiveData<T> = {
    get data() {
      return props.data
    },
    get columns() {
      return props.columns
    },
    state: {
      get columnFilters() {
        return columnFilters.value
      },
      get columnVisibility() {
        return columnVisibility.value
      },
      get rowSelection() {
        return rowSelection.value
      },
    },
    enableRowSelection: true,
    onColumnFiltersChange: (updaterOrValue) =>
      valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) =>
      valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) =>
      valueUpdater(updaterOrValue, rowSelection),
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFacetedRowModel: getFacetedRowModel(),
    getFacetedUniqueValues: getFacetedUniqueValues(),
  }

  if (useServerPagination) {
    tableConfig.state!.pagination = {
      get pageIndex() {
        return pageIndex.value
      },
      get pageSize() {
        return pageSize.value
      },
    }
    Object.defineProperty(tableConfig, 'pageCount', {
      get() {
        return pageCount.value
      },
      configurable: true,
      enumerable: true,
    })
    tableConfig.manualPagination = true
  } else {
    tableConfig.getPaginationRowModel = getPaginationRowModel()
  }

  const table = useVueTable<T>(tableConfig)

  return {
    table,
    sorting,
  }
}
