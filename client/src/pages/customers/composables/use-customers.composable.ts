import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type {
  ICreateCustomerRequest,
  ICustomer,
  ICustomerFilters,
  ICustomerPrerequisites,
  IUpdateCustomerRequest,
} from '@/pages/customers/models/customers'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base.composable'
import { useToast } from '@/composables/use-toast.composable'
import { ECustomerType } from '@/pages/customers/models/customers'
import { useCustomersService } from '@/pages/customers/services/customers.service'
import { useErrorStore } from '@/stores/error.store'

const CustomerContext = {
  FETCH_PREREQUISITES: 'fetchCustomerPrerequisites',
  FETCH_LIST: 'fetchCustomers',
  GET_CUSTOMER: 'getCustomer',
  GET_CUSTOMER_BY_ID: 'getCustomerById',
  CREATE: 'createCustomer',
  UPDATE: 'updateCustomer',
  DELETE: 'deleteCustomer',
}

const CustomerMessages = {
  CREATE_SUCCESS: 'Customer created successfully!',
  UPDATE_SUCCESS: 'Customer updated successfully!',
  DELETE_SUCCESS: 'Customer deleted successfully!',
}

export function useCustomers() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const customersService = useCustomersService()
  const route = useRoute()

  const includes = {
    invoices: 'invoices',
    contacts: 'contacts',
    primaryContact: 'primaryContact',
    contactsCount: 'contactsCount',
    invoicesCount: 'invoicesCount',
  }

  const base = useResourceBase<
    ICustomer,
    ICustomerFilters,
    ICreateCustomerRequest,
    IUpdateCustomerRequest,
    ICustomerPrerequisites
  >({
    service: {
      getPrerequisitesQuery: () =>
        customersService.getCustomersPrerequisitesQuery(),
      getListQuery: (page, per_page, sort, filter, include) =>
        customersService.getCustomersQuery(
          page,
          per_page,
          sort,
          filter,
          include,
        ),
      createMutation: () => customersService.createCustomersMutation(),
      updateMutation: () => customersService.updateCustomersMutation(),
      deleteMutation: () => customersService.deleteCustomersMutation(),
      getMutation: () => customersService.getCustomersMutation(),
    },
    context: CustomerContext,
    messages: CustomerMessages,
    defaultSort: { id: 'name', desc: false },
    includes,
    defaultIncludeKey: ['primaryContact', 'contactsCount', 'invoicesCount'],
    onFetchList: (refetch) => {
      refetch()
    },
  })

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

  // Default includes for customer by id query
  const customerByIdIncludes = ref<string[]>(['contacts', 'invoices'])

  const getCustomersByIdQuery = customersService.getCustomersByIdQuery(
    customerId,
    customerByIdIncludes,
  )
  const {
    data: customerByIdResponse,
    isLoading: isLoadingCustomerById,
    isError: isErrorCustomerById,
    error: errorCustomerById,
    refetch: refetchCustomerById,
  } = getCustomersByIdQuery

  async function fetchCustomerByIdData(
    includes?: string[],
  ): Promise<IResponse<ICustomer>> {
    try {
      // Update includes if provided
      if (includes) {
        customerByIdIncludes.value = includes
      }
      const response = await refetchCustomerById()
      return response.data as IResponse<ICustomer>
    } catch (error: unknown) {
      errorStore.setError(error, {
        context: CustomerContext.GET_CUSTOMER_BY_ID,
      })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for customer form
   * @param customer - Optional customer object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getCustomerFormInitialValues(customer?: ICustomer | null) {
    // Convert type string to ECustomerType enum
    const type =
      customer?.type &&
      Object.values(ECustomerType).includes(customer.type as ECustomerType) ?
          (customer.type as ECustomerType) :
        ECustomerType.PRIVATE

    return {
      type,
      name: customer?.name || '',
      email: customer?.email || null,
      phone: customer?.phone || null,
      address: customer?.address || null,
      zipcode: customer?.zipcode || null,
      city: customer?.city || null,
      country: customer?.country || null,
      kvk_number: customer?.kvk_number || null,
      vat_number: customer?.vat_number || null,
      iban_number: customer?.iban_number || null,
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    customers: base.items,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    customersPrerequisitesResponse: base.prerequisitesResponse,
    isLoadingCustomersPrerequisites: base.isLoadingPrerequisites,
    isErrorCustomersPrerequisites: base.isErrorPrerequisites,
    errorCustomersPrerequisites: base.errorPrerequisites,
    fetchCustomersPrerequisitesData: base.fetchPrerequisitesData,
    fetchCustomersData: base.fetchListData,
    getCustomers: base.get,
    createCustomers: base.create,
    updateCustomers: base.update,
    deleteCustomers: base.deleteItem,
    isCreating: base.isCreating,
    isUpdating: base.isUpdating,
    isDeleting: base.isDeleting,
    loading: base.loading,
    serverPagination: base.serverPagination,
    customerId,
    customerByIdResponse,
    isLoadingCustomerById,
    isErrorCustomerById,
    errorCustomerById,
    fetchCustomerByIdData,
    getCustomerFormInitialValues,
    // Legacy aliases for backward compatibility
    createCustomer: base.create,
    updateCustomer: (customerId: number, data: IUpdateCustomerRequest) =>
      base.update(customerId, data),
    deleteCustomer: base.deleteItem,
    sorting: base.sort,
    filters: base.filter,
    include: base.include,
  }
}
