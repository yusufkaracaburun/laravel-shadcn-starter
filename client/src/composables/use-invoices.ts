import type { SortingState } from '@tanstack/vue-table'

import type { ServerPagination } from '@/components/data-table/types'
import type {
  ICreateInvoiceRequest,
  IInvoiceFilters,
  IUpdateInvoiceRequest,
} from '@/services/invoices.service'

import { useToast } from '@/composables/use-toast'
import {
  useCreateInvoiceMutation,
  useDeleteInvoiceMutation,
  useGetInvoicesQuery,
  useUpdateInvoiceMutation,
} from '@/services/invoices.service'
import { useErrorStore } from '@/stores/error.store'

export function useInvoices() {
  const toast = useToast()
  const errorStore = useErrorStore()

  // Pagination state
  const page = ref(1)
  const pageSize = ref(10)

  // Sorting state - managed here and passed to table
  const sorting = ref<SortingState>([])

  // Filters state
  const filters = ref<IInvoiceFilters>({})

  // Include relationships state
  const include = ref<string[]>(['customer'])

  // Handler for sorting changes from table
  function onSortingChange(newSorting: SortingState) {
    sorting.value = newSorting
    // Reset to first page when sorting changes
    page.value = 1
  }

  // Handler for filter changes
  function onFiltersChange(newFilters: IInvoiceFilters) {
    filters.value = newFilters
    // Reset to first page when filters change
    page.value = 1
  }

  // Clear all filters
  function clearFilters() {
    filters.value = {}
    page.value = 1
  }

  const {
    data: invoicesResponse,
    isLoading,
    isFetching,
    refetch: fetchInvoices,
  } = useGetInvoicesQuery(page, pageSize, sorting, filters, include)

  // Watch for page and pageSize changes to trigger refetch
  // Vue Query tracks computed refs in queryKey, but explicit watch ensures refetch on changes
  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    // Skip initial trigger
    if (oldPage === undefined || oldPageSize === undefined) {
      return
    }
    // Only refetch if values actually changed
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      fetchInvoices()
    }
  })

  // Invoices data from response
  const invoices = computed(() => {
    return invoicesResponse.value?.data?.data ?? []
  })

  const loading = computed(() => isLoading.value || isFetching.value)

  // Extract pagination metadata from Laravel's pagination structure
  const pagination = computed(
    () =>
      invoicesResponse.value?.data ?? {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: null,
        to: null,
      },
  )

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

  async function fetchInvoicesData() {
    try {
      const invoicesResponse = await fetchInvoices()
      return invoicesResponse.data
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'fetchInvoices' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // Create invoice mutation
  const createInvoiceMutation = useCreateInvoiceMutation()
  const updateInvoiceMutation = useUpdateInvoiceMutation()
  const deleteInvoiceMutation = useDeleteInvoiceMutation()

  async function createInvoice(data: ICreateInvoiceRequest) {
    try {
      const response = await createInvoiceMutation.mutateAsync(data)
      toast.showSuccess('Invoice created successfully!')
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'createInvoice' })

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

  async function updateInvoice(invoiceId: number, data: IUpdateInvoiceRequest) {
    try {
      const response = await updateInvoiceMutation.mutateAsync({ invoiceId, data })
      toast.showSuccess('Invoice updated successfully!')
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'updateInvoice' })

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

  async function deleteInvoice(invoiceId: number) {
    try {
      await deleteInvoiceMutation.mutateAsync(invoiceId)
      toast.showSuccess('Invoice deleted successfully!')
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'deleteInvoice' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  return {
    invoices,
    loading,
    fetchInvoicesData,
    invoicesResponse,
    serverPagination,
    sorting,
    onSortingChange,
    filters,
    onFiltersChange,
    clearFilters,
    include,
    createInvoice,
    createInvoiceMutation,
    updateInvoice,
    updateInvoiceMutation,
    deleteInvoice,
    deleteInvoiceMutation,
  }
}
