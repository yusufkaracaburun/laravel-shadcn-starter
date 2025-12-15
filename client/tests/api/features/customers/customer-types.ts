/**
 * Contact types matching backend ContactResource
 */
export interface Contact {
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
  created_at: string
  updated_at: string
  user?: {
    id: number
    name: string
    email: string
    profile_photo_url: string | null
    [key: string]: unknown
  } | null
}

/**
 * Customer types matching backend CustomerResource
 */
export interface Customer {
  id: number
  type: 'business' | 'private'
  name: string
  address: string | null
  formatted_address: string[]
  zipcode: string | null
  city: string | null
  country: string | null
  email: string | null
  phone: string | null
  kvk_number: string | null
  vat_number: string | null
  iban_number: string | null
  created_at: string
  updated_at: string
  primary_contact: Contact | null
  contacts?: Contact[]
  invoices?: unknown[]
  contacts_count?: number
  invoices_count?: number
}

/**
 * Paginated customers response
 */
export interface PaginatedCustomersResponse {
  data: Customer[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  first_page_url: string
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  path: string
  from: number | null
  to: number | null
}

/**
 * Create customer request
 */
export interface CreateCustomerRequest {
  type: 'business' | 'private'
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
  password?: string
  password_confirmation?: string
}

/**
 * Update customer request
 */
export interface UpdateCustomerRequest {
  type?: 'business' | 'private'
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

