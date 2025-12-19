import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateInvoiceRequest,
  IInvoice,
  IInvoiceFilters,
  IUpdateInvoiceRequest,
} from '@/pages/invoices/models/invoice'
import type { ISorting } from '@/services/query-utils'
import type {
  IPaginatedResponse,
  IResponse,
} from '@/services/types/response.type'

import { DEFAULT_PAGE, DEFAULT_PAGE_SIZE } from '@/components/data-table/types'
import { useToast } from '@/composables/use-toast'
import { useInvoiceService } from '@/services/invoices.service'
import { useErrorStore } from '@/stores/error.store'
import { downloadBlobFromAxiosResponse } from '@/utils/blob'

const InvoiceContext = {
  FETCH_INVOICE_PREREQUISITES: 'fetchInvoicePrerequisites',
  FETCH_INVOICES: 'fetchInvoices',
  GET_INVOICE: 'getInvoice',
  GET_INVOICE_BY_ID: 'getInvoiceById',
  CREATE_INVOICE: 'createInvoice',
  UPDATE_INVOICE: 'updateInvoice',
  DELETE_INVOICE: 'deleteInvoice',
  DOWNLOAD_INVOICE_PDF: 'downloadInvoicePdf',
}

const InvoiceMessages = {
  FETCH_INVOICES_SUCCESS: 'Invoices fetched successfully!',
  CREATE_INVOICE_SUCCESS: 'Invoice created successfully!',
  UPDATE_INVOICE_SUCCESS: 'Invoice updated successfully!',
  DELETE_INVOICE_SUCCESS: 'Invoice deleted successfully!',
  DOWNLOAD_INVOICE_PDF_SUCCESS: 'Invoice PDF downloaded successfully!',
}

export function useInvoices() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const invoiceService = useInvoiceService()
  const route = useRoute()

  const page = ref<number>(DEFAULT_PAGE)
  const pageSize = ref<TPageSize>(DEFAULT_PAGE_SIZE)
  const sort = ref<ISorting>({ id: 'created_at', desc: true })
  const filter = ref<IInvoiceFilters>({})
  const includes = {
    customer: 'customer',
    items: 'items',
    payments: 'payments',
    activities: 'activities',
    emails: 'emails',
  }

  function onSortingChange(newSorting: ISorting): void {
    sort.value = newSorting
    page.value = DEFAULT_PAGE
  }

  function onFiltersChange(newFilters: IInvoiceFilters): void {
    filter.value = newFilters
    page.value = DEFAULT_PAGE
  }

  function clearFilters() {
    filter.value = {} as IInvoiceFilters
    page.value = DEFAULT_PAGE
  }

  function onPageChange(newPage: number): void {
    page.value = newPage
  }

  function onPageSizeChange(newPageSize: TPageSize): void {
    page.value = DEFAULT_PAGE
    pageSize.value = newPageSize
  }

  const getInvoicePrerequisitesQuery
    = invoiceService.getInvoicePrerequisitesQuery()
  async function fetchInvoicePrerequisites() {
    try {
      const response = await getInvoicePrerequisitesQuery.refetch()
      return response.data
    }
    catch (error: any) {
      errorStore.setError(error, {
        context: InvoiceContext.FETCH_INVOICE_PREREQUISITES,
      })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const getInvoicesQuery = invoiceService.getInvoicesQuery(
    page,
    pageSize,
    sort,
    filter,
    ref([includes.customer]),
  )
  const {
    data: invoicesData,
    isLoading,
    isFetching,
    refetch: fetchInvoices,
  } = getInvoicesQuery
  const invoices = computed(() => invoicesData.value?.data.data ?? [])
  async function fetchInvoicesData(): Promise<IPaginatedResponse<IInvoice>> {
    try {
      const response = await fetchInvoices()
      return response.data as IPaginatedResponse<IInvoice>
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.FETCH_INVOICES })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const invoiceId = computed(() => {
    if (!route) {
      return undefined
    }
    const idParam = (route.params as { id: string }).id as string
    if (!idParam || Number.isNaN(Number(idParam))) {
      return undefined
    }
    return Number(idParam)
  })
  const getInvoiceByIdQuery = invoiceService.getInvoiceByIdQuery(
    invoiceId,
    ref([
      includes.customer,
      includes.items,
      includes.payments,
      includes.activities,
      includes.emails,
    ]),
  )
  const {
    data: invoiceByIdResponse,
    isLoading: isLoadingInvoiceById,
    isError: isErrorInvoiceById,
    error: errorInvoiceById,
    refetch: fetchInvoiceById,
  } = getInvoiceByIdQuery
  async function fetchInvoiceByIdData(): Promise<IResponse<IInvoice>> {
    try {
      const response = await fetchInvoiceById()
      return response.data as IResponse<IInvoice>
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.GET_INVOICE_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const getInvoiceMutation = invoiceService.getInvoiceMutation()
  async function getInvoice(id: number) {
    try {
      const response = await getInvoiceMutation.mutateAsync({
        id,
        includes: [includes.customer, includes.items],
      })
      return response.data
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.GET_INVOICE })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const createInvoiceMutation = invoiceService.createInvoiceMutation()
  async function createInvoice(data: ICreateInvoiceRequest) {
    try {
      const response = await createInvoiceMutation.mutateAsync(data)
      toast.showSuccess(InvoiceMessages.CREATE_INVOICE_SUCCESS)
      return response
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.CREATE_INVOICE })
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)
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

  const updateInvoiceMutation = invoiceService.updateInvoiceMutation()
  async function updateInvoice(id: number, data: IUpdateInvoiceRequest) {
    try {
      const response = await updateInvoiceMutation.mutateAsync({ id, data })
      toast.showSuccess(InvoiceMessages.UPDATE_INVOICE_SUCCESS)
      return response
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.UPDATE_INVOICE })
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)
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

  const deleteInvoiceMutation = invoiceService.deleteInvoiceMutation()
  async function deleteInvoice(id: number) {
    try {
      await deleteInvoiceMutation.mutateAsync(id)
      toast.showSuccess(InvoiceMessages.DELETE_INVOICE_SUCCESS)
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.DELETE_INVOICE })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const downloadInvoicePdfMutation = invoiceService.downloadInvoicePdfMutation()
  async function downloadInvoicePdf(id: number) {
    try {
      const response = await downloadInvoicePdfMutation.mutateAsync(id)
      downloadBlobFromAxiosResponse(response, `factuur_${id}.pdf`)
      toast.showSuccess(InvoiceMessages.DOWNLOAD_INVOICE_PDF_SUCCESS)
    }
    catch (error: any) {
      errorStore.setError(error, {
        context: InvoiceContext.DOWNLOAD_INVOICE_PDF,
      })
      toast.showError(errorStore.getErrorMessage(error))
      throw error
    }
  }

  const loading = computed(() => isLoading.value || isFetching.value)

  const serverPagination = computed(() => {
    const response = invoicesData.value?.data
    return {
      page: response ? response.current_page : page.value,
      pageSize: pageSize.value,
      total: response ? response.total : 0,
      onPageChange,
      onPageSizeChange,
    }
  })

  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    if (oldPage === undefined || oldPageSize === undefined) {
      return
    }
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      console.error('watch', newPage, newPageSize)
      fetchInvoices()
    }
  })

  return {
    sort,
    filter,
    includes,
    invoices,
    onSortingChange,
    onFiltersChange,
    clearFilters,
    onPageChange,
    onPageSizeChange,
    fetchInvoicePrerequisites,
    fetchInvoicesData,
    getInvoice,
    createInvoice,
    updateInvoice,
    deleteInvoice,
    downloadInvoicePdf,
    loading,
    serverPagination,
    invoiceId,
    invoiceByIdResponse,
    isLoadingInvoiceById,
    isErrorInvoiceById,
    errorInvoiceById,
    fetchInvoiceByIdData,
  }
}
