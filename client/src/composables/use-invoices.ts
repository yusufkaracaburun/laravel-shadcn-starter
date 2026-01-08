import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type {
  ICreateInvoiceRequest,
  IInvoice,
  IInvoiceFilters,
  IInvoicePrerequisites,
  IUpdateInvoiceRequest,
} from '@/pages/invoices/models/invoice'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base'
import { useToast } from '@/composables/use-toast'
import { useInvoiceService } from '@/services/invoices.service'
import { useErrorStore } from '@/stores/error.store'
import { downloadBlobFromAxiosResponse } from '@/utils/blob'

const InvoiceContext = {
  FETCH_PREREQUISITES: 'fetchInvoicePrerequisites',
  FETCH_LIST: 'fetchInvoices',
  GET_INVOICE: 'getInvoice',
  GET_INVOICE_BY_ID: 'getInvoiceById',
  CREATE: 'createInvoice',
  UPDATE: 'updateInvoice',
  DELETE: 'deleteInvoice',
  DOWNLOAD_INVOICE_PDF: 'downloadInvoicePdf',
}

const InvoiceMessages = {
  CREATE_SUCCESS: 'Invoice created successfully!',
  UPDATE_SUCCESS: 'Invoice updated successfully!',
  DELETE_SUCCESS: 'Invoice deleted successfully!',
  DOWNLOAD_INVOICE_PDF_SUCCESS: 'Invoice PDF downloaded successfully!',
}

export function useInvoices() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const invoiceService = useInvoiceService()
  const route = useRoute()

  const includes = {
    customer: 'customer',
    items: 'items',
    payments: 'payments',
    activities: 'activities',
    emails: 'emails',
  }

  const base = useResourceBase<
    IInvoice,
    IInvoiceFilters,
    ICreateInvoiceRequest,
    IUpdateInvoiceRequest,
    IInvoicePrerequisites
  >({
    service: {
      getPrerequisitesQuery: () =>
        invoiceService.getInvoicePrerequisitesQuery(),
      getListQuery: (page, per_page, sort, filter, include) =>
        invoiceService.getInvoicesQuery(page, per_page, sort, filter, include),
      createMutation: () => invoiceService.createInvoiceMutation(),
      updateMutation: () => invoiceService.updateInvoiceMutation(),
      deleteMutation: () => invoiceService.deleteInvoiceMutation(),
      getMutation: () => invoiceService.getInvoiceMutation(),
    },
    context: InvoiceContext,
    messages: InvoiceMessages,
    defaultSort: { id: 'created_at', desc: true },
    includes,
    defaultIncludeKey: 'customer',
    onFetchList: (refetch) => {
      refetch()
    },
  })

  const invoiceId = computed(() => {
    if (!route) {
      return undefined
    }
    const params = route.params as { id?: string | string[] }
    const idParam = Array.isArray(params.id) ? params.id[0] : params.id
    if (
      !idParam ||
      typeof idParam !== 'string' ||
      Number.isNaN(Number(idParam))
    ) {
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
    refetch: refetchInvoiceById,
  } = getInvoiceByIdQuery

  async function fetchInvoiceByIdData(): Promise<IResponse<IInvoice>> {
    try {
      const response = await refetchInvoiceById()
      return response.data as IResponse<IInvoice>
    } catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.GET_INVOICE_BY_ID })
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
    } catch (error: any) {
      errorStore.setError(error, {
        context: InvoiceContext.DOWNLOAD_INVOICE_PDF,
      })
      toast.showError(errorStore.getErrorMessage(error))
      throw error
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    invoices: base.items,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    invoicePrerequisitesResponse: base.prerequisitesResponse,
    isLoadingInvoicePrerequisites: base.isLoadingPrerequisites,
    isErrorInvoicePrerequisites: base.isErrorPrerequisites,
    errorInvoicePrerequisites: base.errorPrerequisites,
    fetchInvoicePrerequisitesData: base.fetchPrerequisitesData,
    fetchInvoicesData: base.fetchListData,
    getInvoice: base.get,
    createInvoice: base.create,
    updateInvoice: base.update,
    deleteInvoice: base.deleteItem,
    downloadInvoicePdf,
    isMutating: base.isMutating,
    loading: base.loading,
    serverPagination: base.serverPagination,
    invoiceId,
    invoiceByIdResponse,
    isLoadingInvoiceById,
    isErrorInvoiceById,
    errorInvoiceById,
    fetchInvoiceByIdData,
  }
}
