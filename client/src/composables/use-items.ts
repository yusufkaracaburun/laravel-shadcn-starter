import type { SortingState } from '@tanstack/vue-table'

import type { ServerPagination } from '@/components/data-table/types'
import type { CreateItemRequest, ItemFilters, UpdateItemRequest } from '@/services/items.service'

import { useToast } from '@/composables/use-toast'
import {
  useCreateItemMutation,
  useDeleteItemMutation,
  useGetItemsQuery,
  useUpdateItemMutation,
} from '@/services/items.service'
import { useErrorStore } from '@/stores/error.store'

export function useItems() {
  const toast = useToast()
  const errorStore = useErrorStore()

  // Pagination state
  const page = ref(1)
  const pageSize = ref(10)

  // Sorting state - managed here and passed to table
  const sorting = ref<SortingState>([])

  // Filters state
  const filters = ref<ItemFilters>({})

  // Handler for sorting changes from table
  function onSortingChange(newSorting: SortingState) {
    sorting.value = newSorting
    // Reset to first page when sorting changes
    page.value = 1
  }

  // Handler for filter changes
  function onFiltersChange(newFilters: ItemFilters) {
    filters.value = newFilters
    // Reset to first page when filters change
    page.value = 1
  }

  // Clear all filters
  function clearFilters() {
    filters.value = {}
    page.value = 1
  }

  const { data: itemsResponse, isLoading, isFetching, refetch: fetchItems } = useGetItemsQuery(
    page,
    pageSize,
    sorting,
    filters,
  )

  // Watch for page and pageSize changes to trigger refetch
  // Vue Query tracks computed refs in queryKey, but explicit watch ensures refetch on changes
  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    // Skip initial trigger
    if (oldPage === undefined || oldPageSize === undefined) {
      return
    }
    // Only refetch if values actually changed
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      fetchItems()
    }
  })

  // Items data from response
  const items = computed(() => {
    return itemsResponse.value?.data?.data ?? []
  })

  const loading = computed(() => isLoading.value || isFetching.value)

  // Extract pagination metadata from Laravel's pagination structure
  const pagination = computed(() => itemsResponse.value?.data ?? {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: null,
    to: null,
  })

  // Pagination handlers
  function onPageChange(newPage: number) {
    page.value = newPage
  }

  function onPageSizeChange(newPageSize: number) {
    pageSize.value = newPageSize
    page.value = 1 // Reset to first page when page size changes
  }

  // Server pagination object for data-table
  // Uses local pageSize value so dropdown updates immediately when changed
  const serverPagination = computed<ServerPagination>(() => ({
    page: pagination.value.current_page,
    pageSize: pageSize.value, // Use local state for immediate UI update
    total: pagination.value.total,
    onPageChange,
    onPageSizeChange,
  }))

  async function fetchItemsData() {
    try {
      const itemsResponse = await fetchItems()
      return itemsResponse.data
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'fetchItems' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // Create item mutation
  const createItemMutation = useCreateItemMutation()
  const updateItemMutation = useUpdateItemMutation()
  const deleteItemMutation = useDeleteItemMutation()

  async function createItem(data: CreateItemRequest) {
    try {
      const response = await createItemMutation.mutateAsync(data)
      toast.showSuccess('Item created successfully!')
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'createItem' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      }
      else {
        toast.showError(message)
      }
      throw error
    }
  }

  async function updateItem(itemId: number, data: UpdateItemRequest) {
    try {
      const response = await updateItemMutation.mutateAsync({ itemId, data })
      toast.showSuccess('Item updated successfully!')
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'updateItem' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      }
      else {
        toast.showError(message)
      }
      throw error
    }
  }

  async function deleteItem(itemId: number) {
    try {
      await deleteItemMutation.mutateAsync(itemId)
      toast.showSuccess('Item deleted successfully!')
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'deleteItem' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  return {
    items,
    loading,
    fetchItemsData,
    itemsResponse,
    serverPagination,
    sorting,
    onSortingChange,
    filters,
    onFiltersChange,
    clearFilters,
    createItem,
    createItemMutation,
    updateItem,
    updateItemMutation,
    deleteItem,
    deleteItemMutation,
  }
}

