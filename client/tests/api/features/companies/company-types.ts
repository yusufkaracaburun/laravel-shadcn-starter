/**
 * Company types matching backend CompanyResource
 */
export interface Company {
  id: number
  name: string
  email: string
  phone: string | null
  industry: string
  status: string
  employees: string
  team_id: number | null
  created_at: string
  updated_at: string
}

/**
 * Paginated companies response
 */
export interface PaginatedCompaniesResponse {
  data: Company[]
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
 * Create company request
 */
export interface CreateCompanyRequest {
  name: string
  email: string
  phone?: string | null
  industry: string
  status: string
  employees: string
}

/**
 * Update company request
 */
export interface UpdateCompanyRequest {
  name?: string
  email?: string
  phone?: string | null
  industry?: string
  status?: string
  employees?: string
}
