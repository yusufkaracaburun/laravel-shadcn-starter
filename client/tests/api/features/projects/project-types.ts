/**
 * Project types matching backend ProjectResource
 */
export interface Project {
  id: number
  name: string
  description: string | null
  status: 'active' | 'on-hold' | 'completed' | 'cancelled'
  category: 'design' | 'development' | 'marketing' | 'support' | 'other'
  start_date: string | null
  end_date: string | null
  progress: number | null
  team_id: number | null
  created_at: string
  updated_at: string
}

/**
 * Paginated projects response
 */
export interface PaginatedProjectsResponse {
  data: Project[]
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
 * Create project request
 */
export interface CreateProjectRequest {
  name: string
  description?: string | null
  status: 'active' | 'on-hold' | 'completed' | 'cancelled'
  category: 'design' | 'development' | 'marketing' | 'support' | 'other'
  start_date?: string | null
  end_date?: string | null
  progress?: number | null
}

/**
 * Update project request
 */
export interface UpdateProjectRequest {
  name?: string
  description?: string | null
  status?: 'active' | 'on-hold' | 'completed' | 'cancelled'
  category?: 'design' | 'development' | 'marketing' | 'support' | 'other'
  start_date?: string | null
  end_date?: string | null
  progress?: number | null
}

