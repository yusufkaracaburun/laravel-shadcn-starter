/**
 * Customer models matching backend Customer model exactly
 * @see api/app/Models/Customer.php
 * @see api/app/Http/Resources/CustomerResource.php
 * @see api/app/Enums/CustomerType.php
 */

import type { IUser } from '@/pages/users/models/users'

export enum ECustomerStatus {
  REGISTERED = 'registered',
  ACTIVE = 'active',
  INACTIVE = 'inactive',
}
export type TCustomerStatus =
  (typeof ECustomerStatus)[keyof typeof ECustomerStatus]

export enum ECustomerType {
  BUSINESS = 'business',
  PRIVATE = 'private',
}
export type TCustomerType = (typeof ECustomerType)[keyof typeof ECustomerType]

/**
 * Contact interface matching backend ContactResource exactly
 * @see api/app/Http/Resources/ContactResource.php
 * @see api/app/Models/Contact.php
 * Note: name is from full_name accessor
 * Note: user is conditionally loaded (via whenLoaded)
 */
export interface IContact {
  id: number
  name: string // full_name accessor
  first_name: string
  last_name: string
  email: string
  phone: string | null
  address: string | null
  zipcode: string | null
  city: string | null
  country: string | null
  created_at: string // Formatted timestamp from formatTimestamp()
  updated_at: string // Formatted timestamp from formatTimestamp()
  user?: IUser | null // UserResource when loaded (via whenLoaded)
}

/**
 * Customer interface matching backend CustomerResource exactly
 * @see api/app/Http/Resources/CustomerResource.php
 * @see api/app/Models/Customer.php
 * Note: number is always present (generated from id, padded to 6 digits)
 * Note: formatted_address is always present (accessor returns array from AddressHelper)
 * Note: primary_contact is always loaded (via $with)
 */
export interface ICustomer {
  id: number
  type: TCustomerType
  status: TCustomerStatus
  name: string
  number: string // Generated from id, padded to 6 digits (e.g., "000001")

  // Address fields
  address: string | null
  formatted_address: string[] // accessor - always returns array from AddressHelper
  zipcode: string | null
  city: string | null
  country: string | null

  // Contact / business info
  email: string | null
  phone: string | null
  kvk_number: string | null
  vat_number: string | null
  iban_number: string | null

  // Timestamps (format: "d-m-Y H:i:s")
  created_at: string
  updated_at: string

  // Primary contact (always loaded via $with)
  primary_contact: IContact | null

  // Collections (when loaded)
  contacts?: IContact[]
  invoices?: unknown[] // Invoice type can be defined later if needed

  // Counts (when counted)
  contacts_count?: number
  invoices_count?: number
}

export interface ICreateCustomerRequest {
  type: TCustomerType
  status: TCustomerStatus
  name: string
  email?: string | null
  phone?: string | null
  address?: string | null
  zipcode?: string | null
  city?: string | null
  country?: string | null
  kvk_number?: string | null
  vat_number?: string | null
  iban_number?: string | null
}

export interface IUpdateCustomerRequest {
  type?: TCustomerType
  name?: string
  email?: string | null
  phone?: string | null
  address?: string | null
  zipcode?: string | null
  city?: string | null
  country?: string | null
  kvk_number?: string | null
  vat_number?: string | null
  iban_number?: string | null
}

export interface ICustomerFilters {
  id?: number
  type?: TCustomerType
  status?: TCustomerStatus
  name?: string
  email?: string
  phone?: string
  city?: string
  country?: string
  kvk_number?: string
  vat_number?: string
  iban_number?: string
  created_at?: string
  between?: string // Format: "YYYY-MM-DD,YYYY-MM-DD"
  search?: string
}
