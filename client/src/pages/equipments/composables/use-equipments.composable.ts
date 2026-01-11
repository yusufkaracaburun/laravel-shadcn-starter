import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type {
  ICreateEquipmentRequest,
  IUpdateEquipmentRequest,
  IEquipment,
  IEquipmentFilters,
  IEquipmentPrerequisites,
} from '@/pages/equipments/models/equipments'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base.composable'
import { useToast } from '@/composables/use-toast.composable'
import { useEquipmentsService } from '@/pages/equipments/services/equipments.service'
import { useErrorStore } from '@/stores/error.store'

const EquipmentContext = {
  FETCH_PREREQUISITES: 'fetchEquipmentPrerequisites',
  FETCH_LIST: 'fetchEquipments',
  GET_EQUIPMENT: 'getEquipment',
  GET_EQUIPMENT_BY_ID: 'getEquipmentById',
  CREATE: 'createEquipment',
  UPDATE: 'updateEquipment',
  DELETE: 'deleteEquipment',
}

const EquipmentMessages = {
  CREATE_SUCCESS: 'Equipment created successfully!',
  UPDATE_SUCCESS: 'Equipment updated successfully!',
  DELETE_SUCCESS: 'Equipment deleted successfully!',
}

export function useEquipments() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const equipmentsService = useEquipmentsService()
  const route = useRoute()

  const includes = {}

  const base = useResourceBase<
    IEquipment,
    IEquipmentFilters,
    ICreateEquipmentRequest,
    IUpdateEquipmentRequest,
    IEquipmentPrerequisites
  >({
    service: {
      getPrerequisitesQuery: () => equipmentsService.getEquipmentsPrerequisitesQuery(),
      getListQuery: (page, per_page, sort, filter, include) =>
        equipmentsService.getEquipmentsQuery(page, per_page, sort, filter, include),
      createMutation: () => equipmentsService.createEquipmentsMutation(),
      updateMutation: () => equipmentsService.updateEquipmentsMutation(),
      deleteMutation: () => equipmentsService.deleteEquipmentsMutation(),
      getMutation: () => equipmentsService.getEquipmentsMutation(),
    },
    context: EquipmentContext,
    messages: EquipmentMessages,
    defaultSort: { id: 'created_at', desc: true },
    includes,
    onFetchList: (refetch) => {
      refetch()
    },
  })

  const equipmentsId = computed(() => {
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

  const getEquipmentsByIdQuery = equipmentsService.getEquipmentsByIdQuery(
    equipmentsId,
    ref([]),
  )
  const {
    data: equipmentsByIdResponse,
    isLoading: isLoadingEquipmentsById,
    isError: isErrorEquipmentsById,
    error: errorEquipmentsById,
    refetch: refetchEquipmentsById,
  } = getEquipmentsByIdQuery

  async function fetchEquipmentsByIdData(): Promise<IResponse<IEquipment>> {
    try {
      const response = await refetchEquipmentsById()
      return response.data as IResponse<IEquipment>
    } catch (error: any) {
      errorStore.setError(error, { context: EquipmentContext.GET_EQUIPMENT_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for equipments form
   * @param equipment - Optional equipment object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getEquipmentsFormInitialValues(equipment?: IEquipment | null) {
    return {
      name: equipment?.name || '',
      type: equipment?.type || null,
      model: equipment?.model || null,
      serial_number: equipment?.serial_number || null,
      status: equipment?.status || null,
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    equipments: base.items,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    equipmentsPrerequisitesResponse: base.prerequisitesResponse,
    isLoadingEquipmentsPrerequisites: base.isLoadingPrerequisites,
    isErrorEquipmentsPrerequisites: base.isErrorPrerequisites,
    errorEquipmentsPrerequisites: base.errorPrerequisites,
    fetchEquipmentsPrerequisitesData: base.fetchPrerequisitesData,
    fetchEquipmentsData: base.fetchListData,
    getEquipments: base.get,
    createEquipments: base.create,
    updateEquipments: base.update,
    deleteEquipments: base.deleteItem,
    isCreating: base.isCreating,
    isUpdating: base.isUpdating,
    isDeleting: base.isDeleting,
    loading: base.loading,
    serverPagination: base.serverPagination,
    equipmentsId,
    equipmentsByIdResponse,
    isLoadingEquipmentsById,
    isErrorEquipmentsById,
    errorEquipmentsById,
    fetchEquipmentsByIdData,
    getEquipmentsFormInitialValues,
  }
}
