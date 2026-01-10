import { computed } from 'vue'
import { useRoute } from 'vue-router'
import type { SortingState } from '@tanstack/vue-table'

import type { ServerPagination } from '@/components/data-table/types'
import type {
  CreateCustomerRequest,
  UpdateCustomerRequest,
} from '@/services/customers.service'
import type { IResponse } from '@/services/types/response.type'
import type {
  ICustomer,
  ICustomerFilters,
} from '@/pages/customers/models/customers'

import { useToast } from '@/composables/use-toast.composable'
import {
  useCreateCustomerMutation,
  useDeleteCustomerMutation,
  useGetCustomersQuery,
  useGetCustomerQuery,
  useUpdateCustomerMutation,
} from '@/services/customers.service'
import { useErrorStore } from '@/stores/error.store'

const CustomerContext = {
  FETCH_LIST: 'fetchCustomers',
  GET_CUSTOMER_BY_ID: 'getCustomerById',
  CREATE: 'createCustomer',
  UPDATE: 'updateCustomer',
  DELETE: 'deleteCustomer',
}

export function useCustomers() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const route = useRoute()

  // Pagination state
  const page = ref(1)
  const pageSize = ref(15)

  // Sorting state - managed here and passed to table
  const sorting = ref<SortingState>([])

  // Filters state
  const filters = ref<ICustomerFilters>({})

  // Include relationships state
  const include = ref<string[]>([])

  // Handler for sorting changes from table
  function onSortingChange(newSorting: SortingState) {
    sorting.value = newSorting
    // Reset to first page when sorting changes
    page.value = 1
  }

  // Handler for filter changes
  function onFiltersChange(newFilters: CustomerFilters) {
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
    data: customersResponse,
    isLoading,
    isFetching,
    refetch: fetchCustomers,
  } = useGetCustomersQuery(page, pageSize, sorting, filters, include)

  // Watch for page and pageSize changes to trigger refetch
  // Vue Query tracks computed refs in queryKey, but explicit watch ensures refetch on changes
  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    // Skip initial trigger
    if (oldPage === undefined || oldPageSize === undefined) {
      return
    }
    // Only refetch if values actually changed
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      fetchCustomers()
    }
  })

  // Customers data from response
  const customers = computed(() => {
    return customersResponse.value?.data?.data ?? []
  })

  const loading = computed(() => isLoading.value || isFetching.value)

  // Extract pagination metadata from Laravel's pagination structure
  const pagination = computed(
    () =>
      customersResponse.value?.data ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
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

  async function fetchCustomersData() {
    try {
      const customersResponse = await fetchCustomers()
      return customersResponse.data
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: CustomerContext.FETCH_LIST })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // Create customer mutation
  const createCustomerMutation = useCreateCustomerMutation()
  const updateCustomerMutation = useUpdateCustomerMutation()
  const deleteCustomerMutation = useDeleteCustomerMutation()

  async function createCustomer(data: CreateCustomerRequest) {
    try {
      const response = await createCustomerMutation.mutateAsync(data)
      toast.showSuccess('Customer created successfully!')
      return response
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: CustomerContext.CREATE })

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

  async function updateCustomer(
    customerId: number,
    data: UpdateCustomerRequest,
  ) {
    try {
      const response = await updateCustomerMutation.mutateAsync({
        customerId,
        data,
      })
      toast.showSuccess('Customer updated successfully!')
      return response
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: CustomerContext.UPDATE })

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

  async function deleteCustomer(customerId: number) {
    try {
      await deleteCustomerMutation.mutateAsync(customerId)
      toast.showSuccess('Customer deleted successfully!')
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: CustomerContext.DELETE })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // Single customer query
  const customerId = computed(() => {
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

  const {
    data: customerByIdResponse,
    isLoading: isLoadingCustomerById,
    isError: isErrorCustomerById,
    error: errorCustomerById,
    refetch: refetchCustomerById,
  } = useGetCustomerQuery(customerId)

  async function fetchCustomerByIdData(): Promise<IResponse<ICustomer>> {
    try {
      const response = await refetchCustomerById()
      return response.data as IResponse<Customer>
    } catch (error: any) {
      errorStore.setError(error, { context: CustomerContext.GET_CUSTOMER_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  return {
    customers,
    loading,
    fetchCustomersData,
    customersResponse,
    serverPagination,
    sorting,
    onSortingChange,
    filters,
    onFiltersChange,
    clearFilters,
    include,
    createCustomer,
    createCustomerMutation,
    updateCustomer,
    updateCustomerMutation,
    deleteCustomer,
    deleteCustomerMutation,
    customerId,
    customerByIdResponse,
    isLoadingCustomerById,
    isErrorCustomerById,
    errorCustomerById,
    fetchCustomerByIdData,
  }
}
