import { useQuery } from '@tanstack/vue-query'
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type {
  ICreateItemRequest,
  IItem,
  IItemFilters,
  IItemPrerequisites,
  IUpdateItemRequest,
} from '@/pages/items/models/items'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base.composable'
import { useToast } from '@/composables/use-toast.composable'
import { useItemService } from '@/services/items.service'
import { defaultAxiosQueryOptions } from '@/services/query-utils'
import { useErrorStore } from '@/stores/error.store'

const STALE_TIME = 5 * 60 * 1000

const ItemContext = {
  FETCH_PREREQUISITES: 'fetchItemPrerequisites',
  FETCH_LIST: 'fetchItems',
  GET_ITEM: 'getItem',
  GET_ITEM_BY_ID: 'getItemById',
  CREATE: 'createItem',
  UPDATE: 'updateItem',
  DELETE: 'deleteItem',
}

const ItemMessages = {
  CREATE_SUCCESS: 'Item created successfully!',
  UPDATE_SUCCESS: 'Item updated successfully!',
  DELETE_SUCCESS: 'Item deleted successfully!',
}

export function useItems() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const itemService = useItemService()
  const route = useRoute()

  // Items don't have prerequisites, so we create a simple empty query
  function getItemPrerequisitesQuery() {
    return useQuery<IItemPrerequisites, import('axios').AxiosError>({
      queryKey: ['itemPrerequisites'],
      queryFn: async (): Promise<IItemPrerequisites> => {
        return {} as IItemPrerequisites
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  const includes = {}

  const base = useResourceBase<
    IItem,
    IItemFilters,
    ICreateItemRequest,
    IUpdateItemRequest,
    IItemPrerequisites
  >({
    service: {
      getPrerequisitesQuery: () => getItemPrerequisitesQuery(),
      getListQuery: (page, per_page, sort, filter, include) =>
        itemService.getItemsQuery(page, per_page, sort, filter, include),
      createMutation: () => itemService.createItemMutation(),
      updateMutation: () => itemService.updateItemMutation(),
      deleteMutation: () => itemService.deleteItemMutation(),
      getMutation: () => itemService.getItemMutation(),
    },
    context: ItemContext,
    messages: ItemMessages,
    defaultSort: { id: 'created_at', desc: true },
    includes,
    defaultIncludeKey: 'default',
    onFetchList: (refetch) => {
      refetch()
    },
  })

  const itemId = computed(() => {
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

  const getItemByIdQuery = itemService.getItemByIdQuery(itemId, ref([]))
  const {
    data: itemByIdResponse,
    isLoading: isLoadingItemById,
    isError: isErrorItemById,
    error: errorItemById,
    refetch: refetchItemById,
  } = getItemByIdQuery

  async function fetchItemByIdData(): Promise<IResponse<IItem>> {
    try {
      const response = await refetchItemById()
      return response.data as IResponse<IItem>
    } catch (error: any) {
      errorStore.setError(error, { context: ItemContext.GET_ITEM_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for item form
   * @param item - Optional item object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getItemFormInitialValues(item?: IItem | null) {
    // Extract unit_price value - handle both Money object and number
    function getUnitPriceValue(item: IItem | null | undefined): number {
      if (!item?.unit_price) return 0
      // If it's a Money object, extract the decimal value
      if (typeof item.unit_price === 'object' && 'amount' in item.unit_price) {
        // Money object: amount is in cents, convert to decimal
        const amount = Number.parseFloat(item.unit_price.amount)
        return amount / 100
      }
      // If it's already a number, use it directly
      if (typeof item.unit_price === 'number') {
        return item.unit_price
      }
      return 0
    }

    return {
      name: item?.name || '',
      description: item?.description || null,
      unit_price: getUnitPriceValue(item),
      vat_rate: item?.vat_rate || 0,
      unit: item?.unit || null,
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    items: base.items,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    itemPrerequisitesResponse: base.prerequisitesResponse,
    isLoadingItemPrerequisites: base.isLoadingPrerequisites,
    isErrorItemPrerequisites: base.isErrorPrerequisites,
    errorItemPrerequisites: base.errorPrerequisites,
    fetchItemPrerequisitesData: base.fetchPrerequisitesData,
    fetchItemsData: base.fetchListData,
    getItem: base.get,
    createItem: base.create,
    updateItem: base.update,
    deleteItem: base.deleteItem,
    isCreating: base.isCreating,
    isUpdating: base.isUpdating,
    isDeleting: base.isDeleting,
    loading: base.loading,
    serverPagination: base.serverPagination,
    itemId,
    itemByIdResponse,
    isLoadingItemById,
    isErrorItemById,
    errorItemById,
    fetchItemByIdData,
    getItemFormInitialValues,
  }
}
