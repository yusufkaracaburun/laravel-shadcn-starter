import type { SortingState } from '@tanstack/vue-table'

import type { IServerPagination, TPageSize } from '@/components/data-table/types'
import type {
  ICreateInvoiceRequest,
  IInvoiceFilters,
  IUpdateInvoiceRequest,
} from '@/pages/invoices/models/invoice'

import { useToast } from '@/composables/use-toast'
import { useInvoiceService } from '@/services/invoices.service'
import { useErrorStore } from '@/stores/error.store'
import { downloadBlobFromAxiosResponse } from '@/utils/blob'

enum InvoiceInclude {
  CUSTOMER = 'customer',
  ITEMS = 'items',
  PAYMENTS = 'payments',
  ACTIVITIES = 'activities',
  EMAILS = 'emails',
}

const DEFAULT_PAGE = 1
const DEFAULT_PAGE_SIZE: TPageSize = 10

export function useInvoices() {
  const toast = useToast()
  const errorStore = useErrorStore()

  const page = ref(DEFAULT_PAGE)
  const pageSize = ref<TPageSize>(DEFAULT_PAGE_SIZE)
  const sorting = ref<SortingState>([])
  const filters = ref<IInvoiceFilters>({})
  const include = ref<string[]>([InvoiceInclude.CUSTOMER])

  const invoiceService = useInvoiceService()
  const {
    data,
    isLoading,
    isFetching,
    refetch: fetchInvoices,
  } = invoiceService.getInvoicesQuery(page, pageSize, sorting, filters, include)

  function onSortingChange(newSorting: SortingState): void {
    sorting.value = newSorting
    page.value = DEFAULT_PAGE
  }

  function onFiltersChange(newFilters: IInvoiceFilters): void {
    filters.value = newFilters
    page.value = DEFAULT_PAGE
  }

  function clearFilters() {
    filters.value = {} as IInvoiceFilters
    page.value = DEFAULT_PAGE
  }

  function onPageChange(newPage: number): void {
    page.value = newPage
  }

  function onPageSizeChange(newPageSize: TPageSize): void {
    pageSize.value = newPageSize
    page.value = DEFAULT_PAGE
  }

  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    if (oldPage === undefined || oldPageSize === undefined) {
      return
    }
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      fetchInvoices()
    }
  })

  const loading = computed(() => isLoading.value || isFetching.value)

  const pagination = computed(
    () =>
      data.value?.data ?? {
        current_page: 1,
        last_page: 1,
        per_page: DEFAULT_PAGE_SIZE,
        total: 0,
        from: null,
        to: null,
      },
  )

  const serverPagination = computed<IServerPagination>(() => ({
    page: pagination.value.current_page,
    pageSize: pageSize.value,
    total: pagination.value.total,
    onPageChange,
    onPageSizeChange,
  }))

  async function fetchInvoicesData() {
    try {
      const response = await fetchInvoices()
      return response.data
    } catch (error: any) {
      errorStore.setError(error, { context: 'fetchInvoices' })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // Create invoice mutation
  const createInvoiceMutation = invoiceService.createInvoiceMutation()
  const updateInvoiceMutation = invoiceService.updateInvoiceMutation()
  const deleteInvoiceMutation = invoiceService.deleteInvoiceMutation()
  const downloadInvoicePdfMutation = invoiceService.downloadInvoicePdfMutation()

  async function createInvoice(data: ICreateInvoiceRequest) {
    try {
      const response = await createInvoiceMutation.mutateAsync(data)
      toast.showSuccess('Invoice created successfully!')
      return response
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'createInvoice' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
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
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'updateInvoice' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
      }
      throw error
    }
  }

  async function deleteInvoice(invoiceId: number) {
    try {
      await deleteInvoiceMutation.mutateAsync(invoiceId)
      toast.showSuccess('Invoice deleted successfully!')
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'deleteInvoice' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  async function downloadInvoicePdf(invoiceId: number) {
    try {
      const response = await downloadInvoicePdfMutation.mutateAsync(invoiceId)

      downloadBlobFromAxiosResponse(response, `factuur_${invoiceId}.pdf`)

      toast.showSuccess('Invoice PDF downloaded successfully!')
    } catch (error: any) {
      errorStore.setError(error, { context: 'downloadInvoicePdf' })
      toast.showError(errorStore.getErrorMessage(error))
      throw error
    }
  }

  return {
    data,
    loading,
    fetchInvoicesData,
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
    downloadInvoicePdf,
    downloadInvoicePdfMutation,
  }
}
