import type { IUser } from '@/pages/users/models/users'
import type { IStatus } from '@/services/types/response.type'

/**
 * Vehicle interface matching backend VehicleResource exactly
 * @see api/app/Http/Resources/VehicleResource.php
 */
export interface IVehicle {
  id: number
  license_plate: string
  make: string | null
  model: string | null
  year: number | null
  color: string | null
  vin: string | null
  status: string
  status_formatted: IStatus
  drivers?: IUser[]
  created_at: string
  updated_at: string
}

export interface IVehicleFilters {
  license_plate?: string
  make?: string
  model?: string
  year?: number
  status?: string
  search?: string
  between?: string
}

export interface ICreateVehicleRequest {
  license_plate: string
  make?: string | null
  model?: string | null
  year?: number | null
  color?: string | null
  vin?: string | null
}

export interface IUpdateVehicleRequest {
  id: number
  license_plate?: string
  make?: string | null
  model?: string | null
  year?: number | null
  color?: string | null
  vin?: string | null
}

export interface IVehiclePrerequisites {
  makes?: string[]
  models?: string[]
  statuses?: IStatus[]
}
