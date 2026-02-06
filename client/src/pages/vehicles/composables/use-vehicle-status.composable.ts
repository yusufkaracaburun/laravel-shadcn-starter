import { statuses } from '@/pages/vehicles/data/data'

export function useVehicleStatus() {
  function getStatusInfo(status: string | null | undefined) {
    if (!status) {
      return null
    }

    return statuses.find((statusItem) => {
      return statusItem.value.toLowerCase() === status.toLowerCase()
    })
  }

  function getStatusVariant(status: string | null | undefined) {
    const statusInfo = getStatusInfo(status)
    if (!statusInfo) {
      return 'secondary'
    }

    switch (statusInfo.value) {
      case 'active':
        return 'default'
      case 'inactive':
        return 'destructive'
      case 'maintenance':
        return 'secondary'
      default:
        return 'secondary'
    }
  }

  return {
    getStatusInfo,
    getStatusVariant,
  }
}
