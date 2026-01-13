import { useQuery } from '@tanstack/vue-query'
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type {
  ICreateProductRequest,
  IProduct,
  IProductFilters,
  IProductPrerequisites,
  IUpdateProductRequest,
} from '@/pages/products/models/products'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base.composable'
import { useToast } from '@/composables/use-toast.composable'
import { useProductService } from '@/pages/products/services/products.service'
import { defaultAxiosQueryOptions } from '@/services/query-utils'
import { useErrorStore } from '@/stores/error.store'

const STALE_TIME = 5 * 60 * 1000

const ProductContext = {
  FETCH_PREREQUISITES: 'fetchProductPrerequisites',
  FETCH_LIST: 'fetchProducts',
  GET_PRODUCT: 'getProduct',
  GET_PRODUCT_BY_ID: 'getProductById',
  CREATE: 'createProduct',
  UPDATE: 'updateProduct',
  DELETE: 'deleteProduct',
}

const ProductMessages = {
  CREATE_SUCCESS: 'Product created successfully!',
  UPDATE_SUCCESS: 'Product updated successfully!',
  DELETE_SUCCESS: 'Product deleted successfully!',
}

export function useProducts() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const productService = useProductService()
  const route = useRoute()

  // Products don't have prerequisites, so we create a simple empty query
  function getProductPrerequisitesQuery() {
    return useQuery<IProductPrerequisites, import('axios').AxiosError>({
      queryKey: ['productPrerequisites'],
      queryFn: async (): Promise<IProductPrerequisites> => {
        return {} as IProductPrerequisites
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  const includes = {}

  const base = useResourceBase<
    IProduct,
    IProductFilters,
    ICreateProductRequest,
    IUpdateProductRequest,
    IProductPrerequisites
  >({
    service: {
      getPrerequisitesQuery: () => getProductPrerequisitesQuery(),
      getListQuery: (page, per_page, sort, filter, include) =>
        productService.getProductsQuery(page, per_page, sort, filter, include),
      createMutation: () => productService.createProductMutation(),
      updateMutation: () => productService.updateProductMutation(),
      deleteMutation: () => productService.deleteProductMutation(),
      getMutation: () => productService.getProductMutation(),
    },
    context: ProductContext,
    messages: ProductMessages,
    defaultSort: { id: 'created_at', desc: true },
    includes,
    defaultIncludeKey: 'default',
    onFetchList: (refetch) => {
      refetch()
    },
  })

  const productId = computed(() => {
    if (!route) {
      return undefined
    }
    const params = route.params as { id?: string | string[] }
    const idParam = Array.isArray(params.id) ? params.id[0] : params.id
    if (
      !idParam
      || typeof idParam !== 'string'
      || Number.isNaN(Number(idParam))
    ) {
      return undefined
    }
    return Number(idParam)
  })

  const getProductByIdQuery = productService.getProductByIdQuery(productId, ref([]))
  const {
    data: productByIdResponse,
    isLoading: isLoadingProductById,
    isError: isErrorProductById,
    error: errorProductById,
    refetch: refetchProductById,
  } = getProductByIdQuery

  async function fetchProductByIdData(): Promise<IResponse<IProduct>> {
    try {
      const response = await refetchProductById()
      return response.data as IResponse<IProduct>
    } catch (error: any) {
      errorStore.setError(error, { context: ProductContext.GET_PRODUCT_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for product form
   * @param product - Optional product object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getProductFormInitialValues(product?: IProduct | null) {
    // Extract unit_price value - handle both Money object and number
    function getUnitPriceValue(product: IProduct | null | undefined): number {
      if (!product?.unit_price) {
        return 0
      }
      // If it's a Money object, extract the decimal value
      if (typeof product.unit_price === 'object' && 'amount' in product.unit_price) {
        // Money object: amount is in cents, convert to decimal
        const amount = Number.parseFloat(product.unit_price.amount)
        return amount / 100
      }
      // If it's already a number, use it directly
      if (typeof product.unit_price === 'number') {
        return product.unit_price
      }
      return 0
    }

    return {
      name: product?.name || '',
      description: product?.description || null,
      unit_price: getUnitPriceValue(product),
      vat_rate: product?.vat_rate || 0,
      unit: product?.unit || null,
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    products: base.items,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    productPrerequisitesResponse: base.prerequisitesResponse,
    isLoadingProductPrerequisites: base.isLoadingPrerequisites,
    isErrorProductPrerequisites: base.isErrorPrerequisites,
    errorProductPrerequisites: base.errorPrerequisites,
    fetchProductPrerequisitesData: base.fetchPrerequisitesData,
    fetchProductsData: base.fetchListData,
    getProduct: base.get,
    createProduct: base.create,
    updateProduct: base.update,
    deleteProduct: base.deleteItem,
    isCreating: base.isCreating,
    isUpdating: base.isUpdating,
    isDeleting: base.isDeleting,
    loading: base.loading,
    serverPagination: base.serverPagination,
    productId,
    productByIdResponse,
    isLoadingProductById,
    isErrorProductById,
    errorProductById,
    fetchProductByIdData,
    getProductFormInitialValues,
  }
}
