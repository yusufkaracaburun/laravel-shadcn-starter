import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type {
  ICreateVehicleRequest,
  IUpdateVehicleRequest,
  IVehicle,
  IVehicleFilters,
  IVehiclePrerequisites,
} from '@/pages/vehicles/models/vehicles'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base.composable'
import { useToast } from '@/composables/use-toast.composable'
import { useVehicleService } from '@/pages/vehicles/services/vehicles.service'
import { useErrorStore } from '@/stores/error.store'

const VehicleContext = {
  FETCH_PREREQUISITES: 'fetchVehiclePrerequisites',
  FETCH_LIST: 'fetchVehicles',
  GET_VEHICLE: 'getVehicle',
  GET_VEHICLE_BY_ID: 'getVehicleById',
  CREATE: 'createVehicle',
  UPDATE: 'updateVehicle',
  DELETE: 'deleteVehicle',
}

const VehicleMessages = {
  CREATE_SUCCESS: 'Vehicle created successfully!',
  UPDATE_SUCCESS: 'Vehicle updated successfully!',
  DELETE_SUCCESS: 'Vehicle deleted successfully!',
}

export function useVehicles() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const vehicleService = useVehicleService()
  const route = useRoute()

  const includes = {}

  const base = useResourceBase<
    IVehicle,
    IVehicleFilters,
    ICreateVehicleRequest,
    IUpdateVehicleRequest,
    IVehiclePrerequisites
  >({
    service: {
      getPrerequisitesQuery: () => vehicleService.getVehiclePrerequisitesQuery(),
      getListQuery: (page, per_page, sort, filter, include) =>
        vehicleService.getVehiclesQuery(page, per_page, sort, filter, include),
      createMutation: () => vehicleService.createVehicleMutation(),
      updateMutation: () => vehicleService.updateVehicleMutation(),
      deleteMutation: () => vehicleService.deleteVehicleMutation(),
      getMutation: () => vehicleService.getVehicleMutation(),
    },
    context: VehicleContext,
    messages: VehicleMessages,
    defaultSort: { id: 'created_at', desc: true },
    includes,
    onFetchList: (refetch) => {
      refetch()
    },
  })

  const vehicleId = computed(() => {
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

  const getVehicleByIdQuery = vehicleService.getVehicleByIdQuery(
    vehicleId,
    ref([]),
  )
  const {
    data: vehicleByIdResponse,
    isLoading: isLoadingVehicleById,
    isError: isErrorVehicleById,
    error: errorVehicleById,
    refetch: refetchVehicleById,
  } = getVehicleByIdQuery

  async function fetchVehicleByIdData(): Promise<IResponse<IVehicle>> {
    try {
      const response = await refetchVehicleById()
      return response.data as IResponse<IVehicle>
    } catch (error: any) {
      errorStore.setError(error, { context: VehicleContext.GET_VEHICLE_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for vehicle form
   * @param vehicle - Optional vehicle object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getVehicleFormInitialValues(vehicle?: IVehicle | null) {
    return {
      license_plate: vehicle?.license_plate || '',
      make: vehicle?.make || null,
      model: vehicle?.model || null,
      year: vehicle?.year || null,
      color: vehicle?.color || null,
      vin: vehicle?.vin || null,
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    vehicles: base.items,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    vehiclePrerequisitesResponse: base.prerequisitesResponse,
    isLoadingVehiclePrerequisites: base.isLoadingPrerequisites,
    isErrorVehiclePrerequisites: base.isErrorPrerequisites,
    errorVehiclePrerequisites: base.errorPrerequisites,
    fetchVehiclePrerequisitesData: base.fetchPrerequisitesData,
    fetchVehiclesData: base.fetchListData,
    getVehicle: base.get,
    createVehicle: base.create,
    updateVehicle: base.update,
    deleteVehicle: base.deleteItem,
    isCreating: base.isCreating,
    isUpdating: base.isUpdating,
    isDeleting: base.isDeleting,
    loading: base.loading,
    serverPagination: base.serverPagination,
    vehicleId,
    vehicleByIdResponse,
    isLoadingVehicleById,
    isErrorVehicleById,
    errorVehicleById,
    fetchVehicleByIdData,
    getVehicleFormInitialValues,
  }
}
